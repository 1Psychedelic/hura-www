<?php

namespace VCD\Admin\Cron\UI;

use Hafo\Cron\CronRunner\DefaultCronRunner;
use Hafo\NetteBridge\Forms\FormFactory;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Multiplier;
use Nette\Database\Context;
use Nette\Forms\Form;
use Nette\Utils\DateTime;
use Nette\Utils\Html;

class CronTasksControl extends Control {

    private $container;

    private $completed;

    function __construct(ContainerInterface $container, $completed = FALSE) {
        $this->container = $container;
        $this->completed = $completed;
        
        $this->onAnchor[] = function() {

        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $list = $this->container->get(Context::class)->table('cron_task')->order('planned_to DESC')->limit(500);
        if(!$this->completed) {
            $list->where('executed_at IS NULL OR locked_at IS NULL');
        }
        $this->template->list = $list;
        $this->template->completed = $this->completed;
        $this->template->args = function($args) {
            if($args === NULL) {
                return '';
            }
            $values = json_decode($args, \JSON_OBJECT_AS_ARRAY);
            $tmp = [];
            foreach($values as $key => $val) {
                $s = is_numeric($val) ? $val : (is_bool($val) ? ($val ? 'TRUE' : 'FALSE') : '"' . $val . '"');
                $tmp[$key] = Html::el('span')->addText($s)->addAttributes(['title' => $key]);
            }
            return implode(', ', $tmp);
        };
        $this->template->render();
    }

    protected function createComponentExecuteForm() {
        return new Multiplier(function($id) {
            $f = $this->container->get(FormFactory::class)->create();
            $f->addHidden('task_id', $id);
            $f->addSubmit('execute', 'Spustit nyní');
            $f->addSubmit('delete', 'Smazat z fronty');
            $f->addText('time');
            $f->addSubmit('plan', 'Přeplánovat');
            $f->onSuccess[] = function(Form $f) {
                $data = $f->getValues(TRUE);
                $id = $data['task_id'];
                $db = $this->container->get(Context::class);
                $task = $db->table('cron_task')->wherePrimary($id)->fetch();
                if(!$task) {
                    $this->presenter->flashMessage('Úkol nebyl nalezen.', 'danger');
                    $this->presenter->redirect('this');
                }

                if($f->isSubmitted() === $f['execute']) {
                    $foo = ['tasks_succeeded' => 0, 'tasks_failed' => 0];
                    $db->beginTransaction();
                    $db->table('cron_task')->wherePrimary($id)->update(['locked_at' => new \DateTime]);
                    $this->container->get(DefaultCronRunner::class)->tryExecuteTask($task, $foo);
                    $db->commit();

                    if($foo['tasks_failed'] > 0) {
                        $this->presenter->flashMessage(sprintf('Provedení úkolu %s selhalo.', $id), 'danger');
                    } else if($foo['tasks_succeeded'] > 0) {
                        $this->presenter->flashMessage(sprintf('Úkol %s byl proveden úspěšně.', $id), 'success');
                    } else {
                        $this->presenter->flashMessage(sprintf('Úkol %s se neprovedl (timeout?)', $id), 'warning');
                    }
                    $this->presenter->redirect('this');
                } else if($f->isSubmitted() === $f['delete']) {
                    $db->table('cron_task')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage(sprintf('Úkol %s byl odstraněn.', $id), 'success');
                    $this->presenter->redirect('this');
                } else if($f->isSubmitted() === $f['plan']) {
                    $time = DateTime::from($data['time']);
                    $db->table('cron_task')->wherePrimary($id)->update([
                        'planned_to' => $time,
                        'executed_at' => NULL,
                        'locked_at' => NULL,
                    ]);
                    $this->presenter->flashMessage(sprintf('Úkol %s naplánován na %s.', $id, (string)$time), 'success');
                    $this->presenter->redirect('this');
                }
            };
            return $f;
        });
    }

}
