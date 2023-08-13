<?php

namespace VCD\UI\FrontModule\ApplicationsModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Psr\Container\ContainerInterface;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;

class FeedbackControl extends Control
{

    private $database;

    private $application;

    function __construct(Context $database, FormFactory $formFactory, $application)
    {
        $this->database = $database;
        $this->application = $application;

        $row = $database->table('vcd_application')->wherePrimary($application)->fetch();
        if (!$row) {
            throw new ForbiddenRequestException;
        }
        $gaveFeedback = FALSE;
        if ($row['user'] !== NULL) {
            $gaveFeedback = $database->table('vcd_application')->where('user = ? AND (feedback IS NOT NULL OR feedback_score IS NOT NULL)', $row['user'])->count() > 0;
        } else {
            $gaveFeedback = $row['feedback'] !== NULL || $row['feedback_score'] !== NULL;
        }
        if (!$gaveFeedback) {
            $f = $formFactory->create();
            $f->setRenderer(new BootstrapRenderer);
            $f->addStarRating('feedback_score', 'Hodnocení');
            $f->addTextArea('feedback', 'Zde nám můžete dát zpětnou vazbu, nebojte se rozepsat, počet znaků není omezen', NULL, 5);
            $f->addSubmit('send', 'Odeslat zpětnou vazbu');
            $f->onSuccess[] = function (Form $f) use ($database, $application) {
                if ($f->isSubmitted() === $f['send']) {
                    $data = $f->getValues(TRUE);
                    if (in_array($data['feedback_score'], [0, 1, 2, 3, 4, 5]) || !empty($data['feedback'])) {
                        $database->table('vcd_application')->wherePrimary($application)->update($data);
                        $this->presenter->flashMessage('Vaše zpětná vazba byla odeslána. Děkujeme!', 'success');
                        $this->presenter->redirect('this');
                    }
                }
            };
            $this->addComponent($f, 'form');
        }
    }

    function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->content = $this->database->table('vcd_page')->where('slug = "feedback-prihlaska" AND special = 1')->fetch()['content'];
        $this->template->render();
    }

}

class FeedbackControlFactory {

    private $c;

    function __construct(ContainerInterface $c) {
        $this->c = $c;
    }

    function create($application) {
        return new FeedbackControl($this->c->get(Context::class), $this->c->get(FormFactory::class), $application);
    }

}
