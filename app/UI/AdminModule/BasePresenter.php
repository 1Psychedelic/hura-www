<?php

namespace VCD\UI\AdminModule;

use Endroid\QrCode\Factory\QrCodeFactory;
use Endroid\QrCode\QrCode;
use Nette\Application\ForbiddenRequestException;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use VCD\Admin\Menu\UI\MenuControl;
use VCD\Notifications\Notifications;
use VCD\UI\AuthModule\LoginPresenter;

/**
 * @property bool $print
 */
class BasePresenter extends \VCD\UI\BasePresenter {

    function startup() {
        parent::startup();
        if(!$this->user->isLoggedIn() && $this->getAction() !== 'login') {
            $this->redirect(AdminPresenter::LINK_LOGIN);
            return;
        }

        if ($this->getAction() === 'login') {
            return;
        }

        if(!$this->user->isInRole('admin')) {
            throw new ForbiddenRequestException;
        }
        $this->template->newNotifications = $this->container->get(Notifications::class)->count();
        $this->template->print = $this->print;
        $this->template->titlePrefix = 'Administrace';
        $this->template->fluid = FALSE;

        $this->addComponent(new MenuControl($this->container), 'menu');

        $hashFile = $this->container->get('app') . '/.hash';
        $this->template->hash = file_exists($hashFile) ? file_get_contents($hashFile) : '';

        $signal = $this->getSignal();
        if($signal !== NULL) {
            list($receiver, $signal) = $signal;
            $this->logger->withName('vcd.admin')->addInfo(sprintf('Provádím akci %s-%s', $receiver, $signal));
        }

        //$this->template->baseUrl = $this->template->baseUri = 'https://vcd.lukasklika.cz';

        /*$qr = new QrCode();
        $qr->setText('');
        $qr->setSize(300)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel('')
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        header('Content-Type: ' . $qr->getContentType());
        $qr->render();
        die;*/
    }

}
