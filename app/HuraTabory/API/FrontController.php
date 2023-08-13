<?php
declare(strict_types=1);

namespace HuraTabory\API;

use Hafo\DI\Container;
use Hafo\Exceptionless\Client;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HuraTabory\API;
use Throwable;
use Zend\Diactoros\Response;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

class FrontController
{
    private const ROUTES = [
        '/api/v1/authentication/user' => [
            'GET' => API\V1\Authentication\UserAction::class,
        ],
        '/api/v1/authentication/login' => [
            'POST' => API\V1\Authentication\LoginAction::class,
        ],
        '/api/v1/authentication/logout' => [
            'POST' => API\V1\Authentication\LogoutAction::class,
        ],
        '/api/v1/authentication/google-login' => [
            'POST' => API\V1\Authentication\GoogleLoginAction::class,
        ],
        '/api/v1/authentication/facebook-login' => [
            'POST' => API\V1\Authentication\FacebookLoginAction::class,
        ],
        '/api/v1/authentication/google-connect' => [
            'POST' => API\V1\Authentication\GoogleConnectAction::class,
        ],
        '/api/v1/authentication/facebook-connect' => [
            'POST' => API\V1\Authentication\FacebookConnectAction::class,
        ],
        '/api/v1/authentication/set-password' => [
            'POST' => API\V1\Authentication\SetPasswordAction::class,
        ],
        '/api/v1/authentication/change-password' => [
            'POST' => API\V1\Authentication\ChangePasswordAction::class,
        ],
        '/api/v1/authentication/request-reset-password-link' => [
            'POST' => API\V1\Authentication\RequestResetPasswordLinkAction::class,
        ],
        '/api/v1/authentication/reset-password' => [
            'POST' => API\V1\Authentication\ResetPasswordAction::class,
        ],
        '/api/v1/authentication/register' => [
            'POST' => API\V1\Authentication\RegisterAction::class,
        ],
        '/api/v1/authentication/disable-session' => [
            'POST' => API\V1\Authentication\DisableSessionAction::class,
        ],
        '/api/v1/authentication/refresh-token' => [
            'GET' => API\V1\Authentication\RefreshTokenAction::class,
        ],
        '/api/v1/home/view' => [
            'GET' => API\V1\Home\ViewHomeAction::class,
        ],
        '/api/v1/event/view' => [
            'GET' => API\V1\Event\ViewEventAction::class,
        ],
        '/api/v1/reservation/check-email' => [
            'POST' => API\V1\Reservation\CheckEmailAction::class,
        ],
        '/api/v1/reservation/parent' => [
            'POST' => API\V1\Reservation\ParentAction::class,
        ],
        '/api/v1/reservation/child-add-to-reservation' => [
            'POST' => API\V1\Reservation\ChildAddToReservationAction::class,
        ],
        '/api/v1/reservation/child-remove-from-reservation' => [
            'POST' => API\V1\Reservation\ChildRemoveFromReservationAction::class,
        ],
        '/api/v1/reservation/children' => [
            'POST' => API\V1\Reservation\ChildrenAction::class,
        ],
        '/api/v1/reservation/child' => [
            'POST' => API\V1\Reservation\ChildAction::class,
        ],
        '/api/v1/reservation/view' => [
            'GET' => API\V1\Reservation\ViewReservationAction::class,
        ],
        '/api/v1/reservation/addons' => [
            'POST' => API\V1\Reservation\AddonsAction::class,
        ],
        '/api/v1/reservation/set-discount-code' => [
            'POST' => API\V1\Reservation\SetDiscountCodeAction::class,
        ],
        '/api/v1/reservation/set-pay-by-credit' => [
            'POST' => API\V1\Reservation\SetPayByCreditAction::class,
        ],
        '/api/v1/reservation/paying-on-invoice' => [
            'POST' => API\V1\Reservation\PayingOnInvoiceAction::class,
        ],
        '/api/v1/reservation/finish' => [
            'POST' => API\V1\Reservation\FinishReservationAction::class,
        ],
        '/api/v1/user-profile/reservation/list' => [
            'GET' => API\V1\UserProfile\ListReservationsAction::class,
        ],
        '/api/v1/user-profile/reservation/view' => [
            'GET' => API\V1\UserProfile\ViewReservationAction::class,
        ],
        '/api/v1/push-notification/register-token' => [
            'POST' => API\V1\PushNotification\RegisterTokenAction::class,
        ],
        '/api/v1/dev/test' => [
            'GET' => API\V1\Dev\TestAction::class,
        ],
        '/api/v1/flash-message' => [
            'GET' => API\FlashMessage\ViewFlashMessageAction::class,
        ],
        '/api/v1/static-page' => [
            'GET' => API\V1\StaticPage\ViewStaticPageAction::class,
        ],
        '/api/v1/email-subscribe' => [
            'POST' => API\Subscribe\EmailSubscribeAction::class,
        ],
        '/api/v1/contact-form/submit' => [
            'POST' => API\V1\ContactForm\ContactFormSubmitAction::class,
        ],
        '/api/v1/notifications/view' => [
            'GET' => API\V1\Notification\ViewNotificationsAction::class,
        ],
        '/api/v1/ares/subject-by-ico' => [
            'GET' => API\V1\Ares\SubjectByIcoAction::class,
        ],
    ];

    /** @var Container */
    private $container;

    /** @var ServerRequestInterface */
    private $request;

    /** @var EmitterInterface */
    private $emitter;

    /** @var Client */
    private $exceptionlessClient;

    public function __construct(
        Container $container,
        ServerRequestInterface $request,
        EmitterInterface $emitter,
        Client $exceptionlessClient
    ) {
        $this->container = $container;
        $this->request = $request;
        $this->emitter = $emitter;
        $this->exceptionlessClient = $exceptionlessClient;
    }

    public function run(): void
    {
        try {
            $path = $this->request->getUri()->getPath();
            $method = $this->request->getMethod();

            if (!isset(self::ROUTES[$path])) {
                $this->emitter->emit(new Response\EmptyResponse(404));

                return;
            }

            if ($method === 'HEAD' && !isset(self::ROUTES[$path]['HEAD']) && isset(self::ROUTES[$path]['GET'])) {
                /** @var RequestHandlerInterface $handler */
                $handler = $this->container->get(self::ROUTES[$path]['GET']);
                $response = $handler->handle($this->request);

                $headers = $response->getHeaders();
                $contentSize = $response->getBody()->getSize();
                if ($contentSize !== null) {
                    $headers['Content-Length'] = $contentSize;
                }

                $this->emitter->emit(new Response\EmptyResponse($response->getStatusCode(), $headers));

                return;
            }

            if (!isset(self::ROUTES[$path][$method])) {
                $allow = array_keys(self::ROUTES[$path]);
                $allow[] = 'OPTIONS';
                if (isset(self::ROUTES[$path]['GET'])) {
                    $allow[] = 'HEAD';
                }

                $response = new Response\EmptyResponse(
                    $method === 'OPTIONS' ? 200 : 405,
                    ['Allow' => implode(',', array_unique($allow))]
                );
                $this->emitter->emit($response);

                return;
            }

            /** @var RequestHandlerInterface $handler */
            $handler = $this->container->get(self::ROUTES[$path][$method]);
            $response = $handler->handle($this->request);
            $this->emitter->emit($response);
        } catch (Throwable $e) {
            if (DEV) {
                http_response_code(500);
                throw $e;
            }

            try {
                $this->exceptionlessClient->logException($e);
            } catch (Throwable $e2) {
            }

            if ($e instanceof API\Exception\ApiException) {
                $this->emitter->emit(new Response\JsonResponse([
                    'message' => $e->getUserMessage(),
                ], 500, [
                    'Cache-Control' => 'no-store, max-age=0',
                ]));

                return;
            }


            $this->emitter->emit(new Response\JsonResponse([
                'message' => 'Něco se pokazilo :(',
            ], 500, [
                'Cache-Control' => 'no-store, max-age=0',
            ]));

            return;
        }
    }
}
