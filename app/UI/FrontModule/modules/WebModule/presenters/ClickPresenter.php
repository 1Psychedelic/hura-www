<?php

namespace VCD\UI\FrontModule\WebModule;

use Nette\Application\BadRequestException;
use Nette\Database\Context;
use Nette\Utils\Strings;

class ClickPresenter extends BasePresenter {

    private const WHITELIST = [
        '//volnycasdeti.cz',
        '//www.volnycasdeti.cz',
        'http://volnycasdeti.cz',
        'http://www.volnycasdeti.cz',
        'https://volnycasdeti.cz',
        'https://www.volnycasdeti.cz',
        '//včd.eu',
        '//www.včd.eu',
        'http://včd.eu',
        'http://www.včd.eu',
        'https://včd.eu',
        'https://www.včd.eu',
        '//xn--vd-ema.eu',
        '//www.xn--vd-ema.eu',
        'http://xn--vd-ema.eu',
        'http://www.xn--vd-ema.eu',
        'https://xn--vd-ema.eu',
        'https://www.xn--vd-ema.eu',
    ];

    const LINK_DEFAULT = ':Front:Web:Click:default';

    function actionDefault($url) {
        $this->validateUrl($url);

        $db = $this->container->get(Context::class);

        if(!$this->isBot()) {
            $db->table('vcd_click')->insert([
                'url' => $url,
                'ip' => $this->getHttpRequest()->getRemoteAddress(),
                'host' => $this->getHttpRequest()->getRemoteHost(),
                'created_at' => new \DateTime,
                'user' => $this->user->id
            ]);
        }
        $this->redirectUrl($url);
    }

    private function validateUrl($url) {
        if(strlen($url) === 0) {
            throw new BadRequestException;
        }

        foreach (self::WHITELIST as $whitelistUrl) {
            if (Strings::startsWith($url, $whitelistUrl)) {
                return;
            }
        }

        if(Strings::startsWith($url, 'http://') || Strings::startsWith($url, 'https://') || Strings::startsWith($url, '//')) {
            throw new BadRequestException;
        }
    }

}
