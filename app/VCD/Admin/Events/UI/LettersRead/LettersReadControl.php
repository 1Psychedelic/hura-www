<?php

namespace VCD\Admin\Events\UI;

use Hafo\DI\Container;
use Hafo\Persona\HumanAge;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Control;
use Nette\Http\IResponse;
use VCD2\Events\Event;
use VCD2\Orm;
use VCD2\PostOffice\Letter;
use VCD2\PostOffice\Service\PostOfficeAdmin;
use VCD2\Users\User;

class LettersReadControl extends Control {

    private $container;

    private $postOffice;

    /** @var Event */
    private $event;

    /** @var User|NULL */
    private $user;

    function __construct(Container $container, $event, $user = NULL) {
        $this->container = $container;
        $this->postOffice = $container->get(PostOfficeAdmin::class);

        /** @var Event $eventEntity */
        $eventEntity = $this->container->get(Orm::class)->events->get($event);
        if($eventEntity === NULL) {
            throw new BadRequestException;
        }
        $this->event = $eventEntity;

        if($user !== NULL) {
            $this->user = $this->container->get(Orm::class)->users->get($user);
        }
    }

    function handleRead() {
        $this->postOffice->markRead($this->event);
        $this->redirect('this');
    }
    
    function handlePdf() {
        $template = $this->createTemplate();
        $template->setFile(__DIR__ . '/pdf.latte');

        //$template->render();die;

        ob_start();
        $template->render();
        $html = ob_get_clean();

        $mpdf = new Mpdf();
        //$mpdf->SetColumns(2);
        $mpdf->SetHTMLFooter('<div align="center"><small>{PAGENO}/{nbpg}</small></div>');
        $mpdf->SetHTMLHeader('<div align="center"><small>{PAGENO}/{nbpg}</small></div>');
        $mpdf->WriteHTML($html);
        $pdf = $mpdf->Output(NULL, Destination::STRING_RETURN);

        $this->container->get(IResponse::class)->setContentType('application/pdf');
        $this->presenter->sendResponse(new TextResponse($pdf));
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->countUnreadLetters = $this->postOffice->countUnread($this->event);
        $this->template->render();
    }

    protected function createTemplate()
    {
        $template = parent::createTemplate();
        $template->event = $this->event;

        $template->users = $users = $this->event->acceptedUsers;
        $template->getLetters = function(User $user) {
            return $this->postOffice->findUnreadLetters($this->event, $user);
        };

        $template->age = $age = function($dateBorn) {
            return (new HumanAge($dateBorn))->yearsAt(new \DateTime);
        };

        $userOptions = [];
        foreach($users as $user) {
            $userOption = sprintf('#%s %s [', $user->id, $user->name);
            foreach($user->findAcceptedApplicationsForEvent($this->event) as $application) {
                foreach($application->children as $child) {
                    $userOption .= sprintf('%s %s, ', $child->name, $age($child->dateBorn));
                }
            }
            $userOptions[$user->id] = substr($userOption, 0, -2) . ']';
        }
        $template->userOptions = $userOptions;

        $template->children = function($userOption) {
            preg_match('#\[(.*?)\]#', $userOption, $match);
            return $match[1];
        };

        $template->directionParentToChild = Letter::DIRECTION_PARENT_TO_CHILD;
        $template->directionChildToParent = Letter::DIRECTION_CHILD_TO_PARENT;
        return $template;
    }

}
