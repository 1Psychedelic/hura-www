<?php
declare(strict_types=1);

namespace HuraTabory\Http;

use Hafo\DI\Container;

class HeadersFactory
{
    /** @var string */
    private $refreshTokenCookieTemplate;

    public function __construct(Container $container)
    {
        $this->refreshTokenCookieTemplate = (string)$container->get('jwt.refreshToken.cookie');
    }

    public function createDefault(): Headers
    {
        return (new Headers())->withoutCache();
    }

    public function getRefreshTokenCookieTemplate(): string
    {
        return $this->refreshTokenCookieTemplate;
    }
}
