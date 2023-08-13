<?php
declare(strict_types=1);

namespace VCD2\Users\Service;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Client\Browser;
use DeviceDetector\Parser\OperatingSystem;
use Psr\Http\Message\ServerRequestInterface;
use VCD2\Orm;
use VCD2\Users\User;
use VCD2\Users\UserSession;

class UserSessions
{
    /** @var Orm */
    private $orm;

    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }

    public function createSession(User $user, ServerRequestInterface $request): UserSession
    {
        $detector = new DeviceDetector($request->getServerParams()['HTTP_USER_AGENT']);
        $detector->skipBotDetection(true);
        $detector->parse();
        $os = OperatingSystem::getOsFamily($detector->getOs('name'));
        $browser = Browser::getBrowserFamily($detector->getClient('name'));
        $deviceDescription = $os . ' ' . $browser;

        $session = new UserSession();
        $session->user = $user;
        $session->enabled = true;
        $session->ip = $request->getServerParams()['REMOTE_ADDR'];
        $session->deviceDescription = $deviceDescription;

        $this->orm->persistAndFlush($session);

        return $session;
    }
}
