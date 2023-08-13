<?php

namespace VCD\Admin\DevTools\UI;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Orm\Encryption\KeysFileGenerator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use VCD2\Orm;
use VCD2\Users\Migration\EncryptionMigration;

class EncryptionControl extends Control {

    private $container;

    private $orm;

    function __construct(Container $container) {
        $this->container = $container;
        $this->orm = $container->get(Orm::class);

        $this->onAnchor[] = function() {

            /** @var Form $f */
            $f = $this->container->get(FormFactory::class)->create();

            $f->addSubmit('refreshAll', 'Vygenerovat nové klíče a přešifrovat všechna data');

            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['refreshAll']) {
                    $keys = $this->container->get(KeysFileGenerator::class);
                    $migration = $this->container->get(EncryptionMigration::class);

                    $keys->initialGenerate(__DIR__ . '/../../../../../config/crypto.php', TRUE);
                    $migration->run();

                    $this->presenter->flashMessage('OK.', 'success');
                    $this->redirect('this');
                }
            };

            $this->addComponent($f, 'refreshAllForm');

        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
