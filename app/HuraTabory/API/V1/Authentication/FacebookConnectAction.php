<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;

use GuzzleHttp\Client;
use Hafo\DI\Container;
use Hafo\Security\Storage\Avatars;
use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Authentication\Transformer\UserToAuthenticationArrayTransformer;
use HuraTabory\Http\HeadersFactory;
use Nette\Utils\Image;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class FacebookConnectAction implements RequestHandlerInterface
{
    /** @var Orm */
    private $orm;

    /** @var TokenUser */
    private $tokenUser;

    /** @var UserToAuthenticationArrayTransformer */
    private $userToAuthenticationArrayTransformer;

    /** @var Avatars */
    private $avatars;

    /** @var string */
    private $applicationId;

    /** @var string */
    private $applicationSecret;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        Orm $orm,
        TokenUser $tokenUser,
        UserToAuthenticationArrayTransformer $userToAuthenticationArrayTransformer,
        Avatars $avatars,
        Container $container,
        HeadersFactory $headersFactory
    ) {
        $this->orm = $orm;
        $this->tokenUser = $tokenUser;
        $this->userToAuthenticationArrayTransformer = $userToAuthenticationArrayTransformer;
        $this->avatars = $avatars;
        $this->applicationId = $container->get('facebook.appId');
        $this->applicationSecret = $container->get('facebook.appSecret');
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        try {
            $userSession = $this->tokenUser->getUserSession();
        } catch (InvalidTokenException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 401, $headers->toArray());
        }

        if ($userSession === null) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $body = (array)json_decode((string)$request->getBody(), true);

        if (!isset($body['signedRequest'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $signedRequest = (string)$body['signedRequest'];

        if (!strpos($signedRequest, '.')) {
            return new EmptyResponse(400, $headers->toArray());
        }

        // parse signed request
        [$encodedSignature, $payloadData] = explode('.', $signedRequest, 2);
        $signature = base64_decode(strtr($encodedSignature, '-_', '+/'));
        $expected = hash_hmac('sha256', $payloadData, $this->applicationSecret, true);

        // verify signature
        if ($signature !== $expected) {
            return new EmptyResponse(401, $headers->toArray());
        }

        // get short-lived access token
        $payload = (array)json_decode(base64_decode(strtr($payloadData, '-_', '+/')), true);

        if (!isset($payload['code'])) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $client = new Client();
        $response = $client->request(
            'POST',
            'https://graph.facebook.com/v12.0/oauth/access_token',
            [
                'form_params' => [
                    'client_id' => $this->applicationId,
                    'client_secret' => $this->applicationSecret,
                    'redirect_uri' => '',
                    'code' => $payload['code'],
                ],
            ]
        );

        $responseData = (array)json_decode($response->getBody()->getContents(), true);
        if (!isset($responseData['access_token'])) {
            return new EmptyResponse(401, $headers->toArray());
        }

        // exchange short-lived access token with long-lived one
        $response = $client->request(
            'POST',
            'https://graph.facebook.com/v12.0/oauth/access_token',
            [
                'form_params' => [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => $this->applicationId,
                    'client_secret' => $this->applicationSecret,
                    'redirect_uri' => '',
                    'fb_exchange_token' => $responseData['access_token'],
                ],
            ]
        );

        $responseData = (array)json_decode($response->getBody()->getContents(), true);
        if (!isset($responseData['access_token'])) {
            return new EmptyResponse(401, $headers->toArray());
        }

        // fetch user data
        $params = [
            'access_token' => $responseData['access_token'],
            'fields' => 'id,email,name,picture',
        ];
        $response = $client->request(
            'GET',
            'https://graph.facebook.com/v12.0/me?' . http_build_query($params)
        );

        $responseData = (array)json_decode($response->getBody()->getContents(), true);
        if (!isset($responseData['id'], $responseData['email'], $responseData['name'])) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $existingUser = $this->orm->users->getBy(['facebookId' => $responseData['id']]);

        if ($existingUser !== null) {
            return new EmptyResponse(401, $headers->toArray()); // todo maybe merge accounts?
        }

        $user = $userSession->user;

        $user->facebookId = $responseData['id'];
        $user->facebookEmail = $responseData['email'];
        $user->facebookName = $responseData['email'];
        $this->orm->persistAndFlush($user);

        if (isset($responseData['picture'])) {
            try {
                $isFromFacebook = $this->avatars->isFromSource($user->id, [Avatars::SOURCE_UNKNOWN, Avatars::SOURCE_FACEBOOK]);
                $isOld = $this->avatars->isOlderThan($user->id, '-1 month');

                if ($isFromFacebook && $isOld) {
                    $imageData = file_get_contents($responseData['picture']);
                    if ($imageData) {
                        $avatar = Image::fromString((string)$imageData);
                        $this->avatars->setAvatar($user->id, $avatar, Avatars::SOURCE_FACEBOOK);

                        $user = $this->orm->users->get($user->id); // reload with avatar
                    }
                }
            } catch (Throwable $e) {
                // nevermind
            }
        }

        return new JsonResponse([
            'authentication' => $this->userToAuthenticationArrayTransformer->transform($userSession, $this->tokenUser->getJwt()),
        ], 200, $headers->toArray());
    }
}
