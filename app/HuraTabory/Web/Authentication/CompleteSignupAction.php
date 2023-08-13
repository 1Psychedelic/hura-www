<?php
declare(strict_types=1);

namespace HuraTabory\Web\Authentication;

use Hafo\DI\Container;
use HuraTabory\API\V1\Authentication\Service\JwtService;
use HuraTabory\DataProvider\Authentication\TokenUserDataProvider;
use HuraTabory\DataProvider\InitialStateDataProvider;
use HuraTabory\Domain\FlashMessage\FlashMessageRepository;
use HuraTabory\Http\HeadersFactory;
use Latte\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Orm;
use VCD2\Users\Service\UserSessions;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class CompleteSignupAction implements RequestHandlerInterface
{
    /** @var TokenUserDataProvider */
    private $tokenUserDataProvider;

    /** @var Orm */
    private $orm;

    /** @var FlashMessageRepository */
    private $flashMessageRepository;

    /** @var UserSessions */
    private $userSessions;

    /** @var JwtService */
    private $jwtService;

    /** @var string */
    private $refreshTokenCookieTemplate;

    /** @var Engine */
    private $latte;

    /** @var InitialStateDataProvider */
    private $initialStateDataProvider;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUserDataProvider $tokenUserDataProvider,
        Orm $orm,
        FlashMessageRepository $flashMessageRepository,
        UserSessions $userSessions,
        JwtService $jwtService,
        Container $container,
        Engine $latte,
        InitialStateDataProvider $initialStateDataProvider,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUserDataProvider = $tokenUserDataProvider;
        $this->orm = $orm;
        $this->flashMessageRepository = $flashMessageRepository;
        $this->userSessions = $userSessions;
        $this->jwtService = $jwtService;
        $this->refreshTokenCookieTemplate = $container->get('jwt.refreshToken.cookie');
        $this->latte = $latte;
        $this->initialStateDataProvider = $initialStateDataProvider;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $hash = (string)($request->getQueryParams()['hash'] ?? '');

        $user = $this->tokenUserDataProvider->getUser($request);
        if ($user === null && $hash === '') {
            return new RedirectResponse('/', 302, $headers->toArray());
        }

        if ($user !== null && $user->canLogin) {
            return new RedirectResponse('/', 302, $headers->toArray());
        }

        if ($user === null && $hash !== '') {
            $user = $this->orm->users->getBy(['loginHash' => $hash]);
            if ($user === null) {
                $fid = $this->flashMessageRepository->create('danger', 'Použitý jednorázový odkaz pro přihlášení je neplatný.');

                $html = $this->latte->renderToString(__DIR__ . '/complete-signup-redirect.latte', [
                    'url' => '/?fid=' . $fid,
                ]);

                return new HtmlResponse($html, 200, $headers->toArray());
            }
            if ($user->canLogin) {
                $fid = $this->flashMessageRepository->create('danger', 'Použitý jednorázový odkaz pro přihlášení je neplatný, Váš účet již má nastavený způsob přihlášení. Pro pokračování do účtu se přihlašte.');

                $html = $this->latte->renderToString(__DIR__ . '/complete-signup-redirect.latte', [
                    'url' => '/?fid=' . $fid,
                ]);

                return new HtmlResponse($html, 200, $headers->toArray());
            }

            $session = $this->userSessions->createSession($user, $request);
            $refreshToken = $this->jwtService->buildRefreshToken($user->id, $session->id);

            $headers = $headers->withCookie($this->headersFactory->getRefreshTokenCookieTemplate(), $refreshToken);

            $fid = $this->flashMessageRepository->create('success', 'Přihlásili jsme vás jednorázovým odkazem. Nastavte si prosím způsob přihlašování k účtu.');

            $html = $this->latte->renderToString(__DIR__ . '/complete-signup-redirect.latte', [
                'url' => '/aktivovat-ucet?fid=' . $fid,
            ]);

            return new HtmlResponse($html, 200, $headers->toArray());
        }

        if ($user !== null && $hash !== '') {
            $requestedUser = $this->orm->users->getBy(['loginHash' => $hash]);
            if ($requestedUser === null) {
                return new RedirectResponse('/', 302, $headers->toArray());
            }

            if ($user->id === $requestedUser->id && !$user->canLogin) {
                $fid = $this->flashMessageRepository->create('success', 'Přihlásili jsme vás jednorázovým odkazem. Nastavte si prosím způsob přihlašování k účtu.');

                return new RedirectResponse('/aktivovat-ucet?fid=' . $fid, 302, $headers->toArray());
            }

            return new RedirectResponse('/', 302, $headers->toArray());
        }

        if ($user !== null && $hash === '') {
            $data = $this->initialStateDataProvider->getData($request);

            $html = $this->latte->renderToString(__DIR__ . '/complete-signup.latte', [
                'reactState' => $data,
            ]);

            return new HtmlResponse($html, 200, $headers->toArray());
        }

        return new RedirectResponse('/', 302, $headers->toArray());
    }
}
