<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\ContactForm;

use HuraTabory\Http\HeadersFactory;
use Nette\Utils\Validators;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Emails\Service\Emails\ContactFormMail;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ContactFormSubmitAction implements RequestHandlerInterface
{
    /** @var HeadersFactory */
    private $headersFactory;

    /** @var ContactFormMail */
    private $contactFormMail;

    public function __construct(HeadersFactory $headersFactory, ContactFormMail $contactFormMail)
    {
        $this->headersFactory = $headersFactory;
        $this->contactFormMail = $contactFormMail;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $body = (array)json_decode((string)$request->getBody(), true);
        if (!isset($body['firstName'], $body['lastName'], $body['email'], $body['subject'], $body['message'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        if (!Validators::isEmail((string)$body['email'])) {
            return new JsonResponse(
                ['error' => 'Prosím zadejte platnou e-mailovou adresu, na které Vás můžeme kontaktovat.'],
                400,
                $headers->toArray()
            );
        }

        $this->contactFormMail->send(
            $body['firstName'] . ' ' . $body['lastName'],
            (string)$body['email'],
            (string)$body['subject'],
            (string)$body['message']
        );

        return new EmptyResponse(201, $headers->toArray());
    }
}
