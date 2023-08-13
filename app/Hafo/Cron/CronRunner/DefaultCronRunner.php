<?php

namespace Hafo\Cron\CronRunner;

use Hafo\Cron;
use Psr\Container\ContainerInterface;
use Nette\Database\Context;
use Nette\Utils\DateTime;

class DefaultCronRunner implements Cron\CronRunner {

    private $database;

    private $container;

    function __construct(Context $database, ContainerInterface $container) {
        $this->database = $database;
        $this->container = $container;
    }

    function findTask(\DateTime $since, $tag, $category) {
        $row = $this->database->table('cron_task')
            ->where('planned_to > ? AND tag = ? AND category = ? AND executed_at IS NULL', [$since, $tag, $category])
            ->fetch();
        if($row) {
            return $row;
        }
        return NULL;
    }

    function addTask($category, \DateTime $when, $service, $method, $args = [], $description = NULL, $tag = NULL, $repeat = NULL) {
        $this->database->table('cron_task')->insert([
            'category' => $category,
            'created_at' => new \DateTime,
            'planned_to' => $when,
            'service' => $service,
            'method' => $method,
            'args' => json_encode($args),
            'description' => $description,
            'tag' => $tag,
            'repeat' => $repeat,
        ]);
        return $this;
    }

    function run($category) {
        $s = 'tasks_succeeded';
        $f = 'tasks_failed';
        $data = [
            'started_at' => new \DateTime,
            $s => 0,
            $f => 0
        ];

        //$this->database->beginTransaction();

        $ids = $this->database->table('cron_task')
            ->where('executed_at IS NULL AND locked_at IS NULL AND planned_to <= NOW() AND category = ?', $category)
            ->fetchPairs(NULL, 'id');
        if(count($ids)) {
            $this->database->table('cron_task')->where('id IN (?)', $ids)->update(['locked_at' => new \DateTime]);

            $tasks = $this->database->table('cron_task')->where('id IN (?)', $ids)->order('planned_to ASC, id ASC')->fetchAll();
            foreach($tasks as $task) {
                $this->tryExecuteTask($task, $data);
            }
        }

        $data['finished_at'] = new \DateTime;
        $this->database->table('cron_log')->insert($data);

        //$this->database->commit();
    }

    private function executeTask($task) {
        if(!$task || ($task['executed_at'] !== NULL && $task['locked_at'] !== NULL)) {
            throw new \InvalidArgumentException;
        }

        $this->database->table('cron_task')->wherePrimary($task['id'])->update([
            'executed_at' => new \DateTime
        ]);
        $service = $this->container->get($task['service']);
        $result = call_user_func_array([$service, $task['method']], $task['args'] === NULL ? [] : json_decode($task['args'], \JSON_OBJECT_AS_ARRAY));
        $this->database->table('cron_task')->wherePrimary($task['id'])->update([
            'result_code' => 0,
            'result_info' => is_scalar($result) ? $result : (is_array($result) ? json_encode($result) : print_r($result, TRUE))
        ]);
    }

    /** @internal */
    function tryExecuteTask($task, array &$data) {
        try {
            $this->executeTask($task);
            $data['tasks_succeeded']++;
        } catch (\Exception $e) {
            $suffix = '';
            if(class_exists(\Tracy\Debugger::class)) {
                $filename = \Tracy\Debugger::log($e, \Tracy\ILogger::EXCEPTION);
                $suffix = ': ' . $filename;
            }
            $this->database->table('cron_task')->wherePrimary($task['id'])->update([
                'result_code' => $e->getCode() === 0 ? 1 : $e->getCode(),
                'result_info' => get_class($e) . $suffix
            ]);
            $data['tasks_failed']++;
        }
        if($task['repeat'] !== NULL) {
            $this->addTask(
                $task['category'],
                DateTime::from($task['planned_to'])->modifyClone($task['repeat']),
                $task['service'],
                $task['method'],
                $task['args'] === NULL ? [] : json_decode($task['args'], \JSON_OBJECT_AS_ARRAY),
                $task['description'],
                $task['tag'],
                $task['repeat']
            );
        }
    }

}
