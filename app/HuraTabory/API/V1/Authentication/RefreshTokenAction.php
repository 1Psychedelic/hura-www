<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;

use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HuraTabory\API\V1\Authentication\Service\JwtService;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class RefreshTokenAction implements RequestHandlerInterface
{
    /** @var JwtService */
    private $jwtService;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        JwtService $jwtService,
        HeadersFactory $headersFactory
    ) {
        $this->jwtService = $jwtService;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $cookies = $request->getCookieParams();

        if (!isset($cookies['refreshToken'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $refreshToken = (string)$cookies['refreshToken'];
        try {
            $accessToken = $this->jwtService->refreshAccessToken($refreshToken);
        } catch (InvalidTokenException $e) {
            return new EmptyResponse(401, $headers->toArray());
        }

        return new JsonResponse([
            'accessToken' => $accessToken,
        ], 200, $headers->toArray());
    }
}
