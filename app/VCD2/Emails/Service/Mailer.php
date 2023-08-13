<?php

namespace VCD2\Emails\Service;

use HuraTabory\Domain\Website\WebsiteRepository;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\ITemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Throwable;

class Mailer {

    /** @var IMailer */
    private $mailer;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var ITemplateFactory */
    private $templateFactory;

    /** @var LinkGenerator */
    private $linkGenerator;

    /** @var WebsiteRepository */
    private $websiteRepository;

    /** @var string */
    private $basePath;

    /** @var string */
    private $baseUri;

    function __construct(
        IMailer $mailer,
        MessageFactory $messageFactory,
        ITemplateFactory $templateFactory,
        LinkGenerator $linkGenerator,
        WebsiteRepository $websiteRepository,
        $basePath,
        $baseUri
    ) {
        $this->mailer = $mailer;
        $this->messageFactory = $messageFactory;
        $this->templateFactory = $templateFactory;
        $this->linkGenerator = $linkGenerator;
        $this->websiteRepository = $websiteRepository;
        $this->basePath = $basePath;
        $this->baseUri = $baseUri;
    }

    /** @return ITemplate|Template */
    function createTemplate() {
        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/Emails/generic.latte');
        return $template;
    }

    /** @return Message */
    function createMessage() {
        return $this->messageFactory->create();
    }

    function send(Message $message, ITemplate $template, IMailer $mailer = NULL) {
        if($template instanceof Template) {
            $params['basePath'] = $this->basePath;
            $params['baseUri'] = $this->baseUri; // 'https://vcd.lukasklika.cz/';
            $params['subject'] = $message->getSubject();
            $params['link'] = $this->linkGenerator;

            $websiteConfig = $this->websiteRepository->getWebsiteConfig();
            $params['facebook'] = $websiteConfig->getFacebookLink();
            $params['instagram'] = $websiteConfig->getInstagramLink();
            $params['pinterest'] = $websiteConfig->getPinterestLink();

            $template->setParameters($params);
        }

        ob_start();
        try {
            $template->render();
        } catch (Throwable $e) {
            // todo log
        }
        $html = ob_get_clean();
//header('Content-type: text/html');die($html);
        $message->setHtmlBody($html, $this->basePath);

        if($mailer === NULL) {
            $this->mailer->send($message);
        } else {
            $mailer->send($message);
        }
    }

}
