<?php

namespace VCD\UI\FrontModule\ApplicationsModule;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Security\Storage\Profiles;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Applications\Application;
use VCD2\Applications\Service\Drafts;
use VCD2\Applications\StepChoice;
use VCD2\Events\ApplicationStep;
use VCD2\Orm;

/**
 * @method onSave()
 */
class DraftStepControl extends Control {

    public $onSave = [];


    function __construct(Container $container, Application $draft, $id) {

        $orm = $container->get(Orm::class);
        $drafts = $container->get(Drafts::class);

        /**
         * @var ApplicationStep $step
         * @var StepChoice|NULL $choice
         */
        $step = $draft->event->steps->get()->getBy(['slug' => $id]);
        $choice = $draft->stepChoices->get()->getBy(['step' => $step]);
        $options = [];
        
        foreach($step->options as $option) {
            $label = '<strong>';
            if($option->absolutePrice) {
                $label .= 'Cena ' . $option->price . ' Kč';
            } else {
                if($option->price > 0) {
                    $label .= 'Příplatek ' . $option->price . ' Kč';
                } else if($option->price < 0) {
                    $label .= 'Sleva ' . abs($option->price) . ' Kč';
                }
            }
            if($option->multiplyByChildren && $option->price !== 0) {
                $label .= ' / dítě';
            }
            $label .= '</strong>';
            $showSibling = !$option->allowSiblingDiscount && $draft->event->siblingDiscount > 0;
            $showDiscount = !$option->allowDiscountCodes;
            if($option->maxUsages !== NULL) {
                $label .= '<br>Použito <strong>' . $option->timesUsed . ' z ' . $option->maxUsages . '</strong>';
            }
            if($showDiscount && $showSibling) {
                $label .= '<br><small class="text-muted">Tuto možnost není možné zkombinovat se sourozeneckou slevou a slevovými kódy.</small>';
            } else if($showDiscount) {
                $label .= '<br><small class="text-muted">Tuto možnost není možné zkombinovat se slevovými kódy.</small>';
            } else if($showSibling) {
                $label .= '<br><small class="text-muted">Tuto možnost není možné zkombinovat se sourozeneckou slevou.</small>';
            }
            $options[$option->id] = Html::el()->addHtml('' . $option->option . ' - ' .  $label . '<br>&nbsp;');
        }

        $f = $container->get(FormFactory::class)->create();
        $f->setRenderer(new BootstrapRenderer);
        $f->addRadioList('option', $step->prompt, $options)->setRequired();
        $f->addButtonSubmit('save', Html::el()->addHtml('Uložit a pokračovat <span class="glyphicon glyphicon-arrow-right"></span>'))
            ->getControlPrototype()->setClass('btn btn-lg btn-success');
        $f->onSuccess[] = function(Form $f) use ($draft, $choice, $step, $orm, $drafts) {
            if($f->isSubmitted() === $f['save']) {
                $data = $f->getValues(TRUE);
                $option = $step->options->get()->getBy(['id' => $data['option']]);
                if($choice === NULL) {
                    $choice = new StepChoice($draft, $option);
                } else {
                    $choice->option = $option;
                }

                $orm->persist($choice);
                $drafts->saveDraft($draft);

                $this->onSave();
            }
        };
        if($choice !== NULL) {
            $f['option']->setValue($choice->option->id);
        }
        $this->addComponent($f, 'form');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}

