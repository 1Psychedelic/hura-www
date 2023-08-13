<?php

namespace VCD\Admin\Monolog\UI;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Monolog\Logger;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;
use VCD2\Orm;

class MonologControl extends Control {

    const EXCEPTION_LOG_PREFIX = '/data/web/virtuals/140514/virtual/www/app/../log';

    const LIMIT = 200;

    private $container;

    function __construct(
        Container $container,
        $id = NULL,
        \DateTimeInterface $since = NULL,
        \DateTimeInterface $till = NULL,
        $user = NULL,
        $minLevel = NULL,
        $maxLevel = NULL,
        $request = NULL,
        $q = NULL,
        $channel = NULL,
        $ip = NULL,
        $order = 'DESC'
    ) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id, $since, $till, $user, $minLevel, $maxLevel, $request, $q, $channel, $ip, $order) {
            $db = $this->container->get(Context::class);

            $channels = $db->table('monolog')->select('DISTINCT channel')->fetchPairs('channel', 'channel');

            $selection = $db->table('monolog')->order('id ' . $order)->limit(self::LIMIT);

            if($id !== NULL) {
                $selection->wherePrimary($id);
            }
            if($q !== NULL) {
                $selection->where('message LIKE ?', '%' . $q . '%');
            }
            if($channel !== NULL) {
                $selection->where('channel', $channel);
            }
            if($since !== NULL) {
                $selection->where('created_at >= ?', $since);
            }
            if($till !== NULL) {
                $selection->where('created_at <= ?', $till);
            }
            if($user !== NULL) {
                $selection->where('user', $user);
            }
            if($minLevel !== NULL) {
                $selection->where('level >= ?', $minLevel);
            }
            if($maxLevel !== NULL) {
                $selection->where('level <= ?', $maxLevel);
            }
            if($request !== NULL) {
                $selection->where('request_uuid LIKE ?', $request . '%');
            }
            if($ip !== NULL) {
                $selection->where('ip LIKE ?', $ip . '%');
            }

            $this->template->monolog = $selection;
            $this->template->actionLink = function ($action, $params) {
                if(empty($action) && empty($params)) {
                    return '#';
                }
                $params = Json::decode($params, Json::FORCE_ARRAY);
                return $this->presenter->link(':' . $action, $params);
            };

            /** @var Form $f */
            $f = $this->container->get(FormFactory::class)->create();

            $users = $this->container->get(Orm::class)->users->findSelectOptions();

            $f->setRenderer(new BootstrapInlineRenderer);
            $f->addText('q', 'Zpráva')->setNullable();
            $f->addText('since', 'Čas od')->setNullable();
            $f->addText('till', 'Čas do')->setNullable();
            $f->addText('request', 'ID požadavku')->setNullable();
            $f->addText('ip', 'IP')->setNullable();
            $f->addXSelect('channel', 'Kanál', $channels)->setPrompt('(Vše)');
            $f->addXSelect('user', 'Uživatel', $users)->setPrompt('(Kdokoliv)');
            $f->addText('minLevel', 'Úroveň od');
            $f->addText('maxLevel', 'Úroveň do');
            $f->addSubmit('filter', 'Filtrovat');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['filter']) {
                    $data = $f->getValues(TRUE);
                    $this->presenter->redirect('this', $data);
                }
            };
            $f->setValues($this->presenter->getParameters());
            $this->addComponent($f, 'filter');

            $this->template->users = $this->container->get(Orm::class)->users->findIdNamePairs();
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->getExceptionUrl = function($exceptionLog) {
            if(Strings::startsWith($exceptionLog, self::EXCEPTION_LOG_PREFIX)) {
                $filename = str_replace(self::EXCEPTION_LOG_PREFIX, '', $exceptionLog);
                return $this->presenter->link('log', ['filename' => $filename]);
            }
            return NULL;
        };
        $this->template->render();
    }

}
