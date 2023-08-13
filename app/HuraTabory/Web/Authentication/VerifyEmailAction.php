<?php
declare(strict_types=1);

namespace HuraTabory\Web\Authentication;

use Hafo\Security\Authentication\EmailAlreadyVerifiedException;
use Hafo\Security\SecurityException;
use Hafo\Security\Storage\Emails;
use HuraTabory\Domain\FlashMessage\FlashMessageRepository;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD\UI\AuthModule\LoginPresenter;
use Zend\Diactoros\Response\RedirectResponse;

class VerifyEmailAction implements RequestHandlerInterface
{
    /** @var Emails */
    private $emails;

    /** @var FlashMessageRepository */
    private $flashMessageRepository;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        Emails $emails,
        FlashMessageRepository $flashMessageRepository,
        HeadersFactory $headersFactory
    ) {
        $this->emails = $emails;
        $this->flashMessageRepository = $flashMessageRepository;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $email = (string)($request->getQueryParams()['email'] ?? '');
        $hash = (string)($request->getQueryParams()['hash'] ?? '');
        $fid = null;

        try {
            $this->emails->verify($email, $hash);
            $fid = $this->flashMessageRepository->create('success', 'Váš e-mail je ověřen, můžete se přihlásit.');
        } catch (EmailAlreadyVerifiedException $e) {
            $fid = $this->flashMessageRepository->create('success', 'Váš e-mail je ověřen, můžete se přihlásit.');
        } catch (SecurityException $e) {
            $fid = $this->flashMessageRepository->create('danger', 'Váš e-mail se nepodařilo ověřit. Pokud máte potíže, neváhejte nás kontaktovat.');
        }

        return new RedirectResponse('/' . ($fid === null ? '' : '?fid=' . $fid), 302, $headers->toArray());
    }
}
