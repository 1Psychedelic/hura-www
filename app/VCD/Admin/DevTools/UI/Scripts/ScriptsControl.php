<?php

namespace VCD\Admin\DevTools\UI;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\Utils\Json;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Tracy\Debugger;

class ScriptsControl extends Control {

    function __construct(Container $container) {

        $this->onAnchor[] = function () use ($container) {

            /** @var Form $f */
            $f = $container->get(FormFactory::class)->create();

            $f->setRenderer(new BootstrapRenderer);

            $f->addXSelect('preset', 'Rychlý výběr', [
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Users\Migration\EncryptionMigration',
                        'method' => 'run',
                        'args' => '',
                    ]
                ])->setText('EncryptionMigration::run()'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Utils\EncryptionMigrationTool',
                        'method' => 'clearEncryptedData',
                        'args' => ''
                    ]
                ])->setText('EncryptionMigrationTool::clearEncryptedData'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Utils\EncryptionMigrationTool',
                        'method' => 'partialToFull',
                        'args' => ''
                    ]
                ])->setText('EncryptionMigrationTool::partialToFull'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Utils\EncryptionMigrationTool',
                        'method' => 'fullToPartial',
                        'args' => ''
                    ]
                ])->setText('EncryptionMigrationTool::fullToPartial'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Utils\EncryptionMigrationTool',
                        'method' => 'partialToNone',
                        'args' => ''
                    ]
                ])->setText('EncryptionMigrationTool::partialToNone'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Utils\EncryptionMigrationTool',
                        'method' => 'noneToPartial',
                        'args' => ''
                    ]
                ])->setText('EncryptionMigrationTool::noneToPartial'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Utils\EncryptionMigrationTool',
                        'method' => 'fixHashes',
                        'args' => ''
                    ]
                ])->setText('EncryptionMigrationTool::fixHashes'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Utils\EncryptionMigrationTool',
                        'method' => 'fixNullEmptyValues',
                        'args' => '[false]'
                    ]
                ])->setText('EncryptionMigrationTool::fixNullEmptyValues'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Emails\Service\Emails\ApplicationAppliedMail',
                        'method' => 'send',
                        'args' => '[30772]',
                    ]
                ])->setText('VCD2\Emails\Service\Emails\ApplicationAppliedMail::send'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Emails\Service\Emails\AccountCreatedMail',
                        'method' => 'send',
                        'args' => '["lukas@volnycasdeti.cz", true]',
                    ]
                ])->setText('VCD2\Emails\Service\Emails\AccountCreatedMail::send'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Emails\Service\Emails\ApplicationAcceptedMail',
                        'method' => 'send',
                        'args' => '[30772]',
                    ]
                ])->setText('VCD2\Emails\Service\Emails\ApplicationAcceptedMail::send'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Emails\Service\Emails\ApplicationPaidMail',
                        'method' => 'send',
                        'args' => '[30772, 200]',
                    ]
                ])->setText('VCD2\Emails\Service\Emails\ApplicationPaidMail::send'),
                Html::el(NULL, [
                    'data-replace' => [
                        'class' => '\VCD2\Emails\Service\Emails\ApplicationRejectedMail',
                        'method' => 'send',
                        'args' => '[30772, "Testovací důvod"]',
                    ]
                ])->setText('VCD2\Emails\Service\Emails\ApplicationRejectedMail::send')
            ])->setPrompt('Rychlý výběr');

            $f->addText('class', 'Třída');
            $f->addText('method', 'Metoda');
            $f->addTextArea('args', 'Parametry')->setAttribute('placeholder', "Formát JSON");
            $f->addRadioList('output', 'Výstup', [
                'json' => 'Json::encode',
                'dump' => 'Debugger::dump',
                'bardump' => 'Debugger::barDump',
                'echo' => 'echo',
            ]);
            $f->addSubmit('run', 'Spustit');

            $f->onSuccess[] = function (Form $f) use ($container) {
                if($f->isSubmitted() === $f['run']) {
                    $data = $f->getValues(TRUE);

                    $class = $data['class'];
                    $method = $data['method'];
                    $args = $data['args'];

                    if (strlen($method) === 0 && strpos($class, '::') !== false) {
                        list($class, $method) = explode('::', $class);
                    }

                    try {
                        $service = $container->get($class);

                        if(empty($args)) {
                            $returned = $service->$method();
                        } else {
                            $params = Json::decode($args);
                            $returned = $service->$method(...$params);
                        }

                        $output = '';
                        switch($data['output']) {
                            case 'json':
                                $output = Html::el('textarea')->addAttributes(['readonly' => 'readonly'])->addText(Json::encode($returned, Json::PRETTY));
                                break;
                            case 'dump':
                                $output = Html::el()->setHtml(Debugger::dump($returned, TRUE));
                                break;
                            case 'bardump':
                                $output = '(output in Tracy bar)';
                                Debugger::barDump($returned);
                                break;
                            case 'echo':
                                $output = Html::el()->setHtml($returned);
                                break;
                        }

                        $this->flashMessage($output, 'html');
                    } catch (\Exception $e) {
                        $file = Debugger::log($e);
                        $error = Html::el('div')->setHtml(
                            get_class($e) . '(' . $e->getCode() . '): ' . $e->getMessage() .
                            ' - <a href="' . $file . '">log</a>'
                        );
                        $this->presenter->flashMessage($error, 'html');
                    }

                    $this->redirect('this');
                }
            };

            $this->addComponent($f, 'form');
        };

    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
