<?php

namespace VCD\UI\FrontModule;

use Hafo\Security\Authentication\EmailAlreadyVerifiedException;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\Authentication\Unauthenticator;
use Hafo\Security\SecurityException;
use Hafo\Security\Storage\Emails;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Context;
use Nette\Utils\Strings;
use VCD\UI\FrontModule\EventsModule\EventPresenter;
use VCD2\FacebookImages\Service\FacebookImages;
use VCD2\Users\Consent;
use VCD2\Users\Service\AutomaticSignup;

abstract class BasePresenter extends \VCD\UI\BasePresenter {

    function beforeRender() {
        $db = $this->container->get(Context::class);
        /** @var Context $db */
        $selection = $db->table('facebook_image')->order('id DESC');
        $imgs = [];
        foreach($selection as $row) {
            $imgs[$this->template->baseUri . '/www/' . $row['path']] = [
                'width' => $row['width'],
                'height' => $row['height'],
            ];
        }

        if($this instanceof EventPresenter && !empty($this->event->bannerSmall)) {
            $imgs[$this->template->baseUri . '/www/' . $this->event->bannerSmall] = [
                'width' => 500,
                'height' => 200,
            ];
        }

        /*$imgs = array_map(function($val) {
            return $this->template->baseUri . '/www/' . $val;
        }, $db->table('facebook_image')->select('path')->order('id DESC')->fetchPairs(NULL, 'path'));
        if($this instanceof EventPresenter && !empty($this->event->bannerSmall)) {
            $imgs[] = $this->template->baseUri . '/www/' . $this->event->bannerSmall;
        }
        $imgs[] = $this->template->baseUri . '/www/assets/img/vcd.png';
        $imgs[] = $this->template->baseUri . '/www/assets/img/univerzal.png';*/

        $this->template->og = [
            'title'       => (isset($this->template->titlePrefix) ? $this->template->titlePrefix . ' - ' : '') . 'Volný čas dětí, z.s.' . (isset($this->template->titleSuffix) ? ' - ' . $this->template->titleSuffix : ''),
            'type'        => 'article',
            'url'         => $this->link('//this'),
            'description' => ($this instanceof EventPresenter ? $this->getOgMetaDescription() : ''),
            'author'      => $this->container->get('facebook.authorId')
        ];
        $this->template->ogImages = $imgs;

        $path = $this->getHttpRequest()->getUrl()->getPath();
        $this->template->facebookImages = $this->container->get(FacebookImages::class)->getImages(empty($path) ? '/' : $path);

        $this->setLayout(__DIR__ . '/@layout.latte');
    }

    protected function getOgMetaDescription() {
        return '';
    }

    protected function isBot() {
        $remoteHost = $this->getHttpRequest()->getRemoteHost();
        if(Strings::endsWith($remoteHost, '.googlebot.com')) {
            return TRUE;
        }
        if(Strings::endsWith($remoteHost, '.seznam.cz')) {
            return TRUE;
        }
        return FALSE;
    }

    function startup() {
        throw new ForbiddenRequestException();

        parent::startup();

        $this->processEmailSignIn();
    }

    protected function processEmailSignIn() {
        $verifyEmailHash = $this->request->getParameter('verifyEmailHash');
        $email = $this->request->getParameter('verifyEmail');

        if($verifyEmailHash !== NULL && $email !== NULL) {
            try {
                $this->container->get(Emails::class)->verify($email, $verifyEmailHash);
            } catch (EmailAlreadyVerifiedException $e) { // this is ok
            } catch (SecurityException $e) {
                $this->flashMessage('E-mail se nepodařilo ověřit.', 'danger');
                return;
            }

            $user = $this->orm->users->getByEmail($email);
            $accountCreated = FALSE;
            if($user === NULL) {
                $this->container->get(AutomaticSignup::class)->createAccount($email);
                $user = $this->orm->users->getByEmail($email);
                $accountCreated = TRUE;
            }

            if($this->userContext->getEntity() !== $user) {
                $this->container->get(Unauthenticator::class)->logout();
                $this->container->get(IdAuthenticator::class)->login($user->id);

                if($accountCreated) {
                    $this->flashMessage('Děkujeme za ověření e-mailové adresy. Založili jsme vám účet, přihlásili vás a rovnou i napárovali všechny minulé přihlášky.', 'success');
                } else {
                    $this->flashMessage('Přihlášení bylo úspěšné.', 'success');
                }
            }

            $this->redirect('this');
        }
    }

}
