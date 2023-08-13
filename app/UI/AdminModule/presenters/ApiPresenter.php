<?php

namespace VCD\UI\AdminModule;

use Hafo\DI\Container;
use Monolog\Logger;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Presenter;
use Nette\Database\Context;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Utils\Json;

class ApiPresenter extends Presenter {

    const VERSION = 1;

    const KEY = 'HWjtzTFTS9NUJcQTseL1ha1bFYcND1FVFsye5Q6Rr8L';

    /** @persistent */
    public $_key;

    protected $container;


    function __construct(Container $container) {
        parent::__construct();
        $this->container = $container;
    }

    function startup() {
        parent::startup();

        if($this->_key !== self::KEY) {
            throw new BadRequestException;
        }
    }

    function actionDefault() {
        $this->sendJson((object)['version' => self::VERSION]);
    }

    function actionMonolog($minLevel = Logger::CRITICAL) {
        $db = $this->container->get(Context::class);

        $result = $db->table('monolog')->where('level >= ?', $minLevel)->order('created_at DESC')->limit(100);

        $data = [];
        foreach($result as $row) {
            $data[] = (object)$row->toArray();
        }

        $this->sendNiceJson($data);
    }

    protected function sendNiceJson($data) {
        $this->sendResponse(new CallbackResponse(function(IRequest $httpRequest, IResponse $httpResponse) use ($data) {
            $httpResponse->setContentType('application/json', 'utf-8');
            echo Json::encode($data, Json::PRETTY);
        }));
    }

}
