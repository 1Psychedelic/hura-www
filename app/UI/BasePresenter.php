<?php

namespace VCD\UI;

use Hafo\Ads\UI\AdControl;
use Hafo\DI\Container;
use Hafo\Facebook\FacebookPixel\FacebookPixel;
use Hafo\Facebook\FacebookSDK;
use Hafo\Google\Analytics\Analytics;
use Hafo\Google\ConversionTracking\Tracker;
use Hafo\Google\GoogleSDK;
use Hafo\Google\UI\AdSense\AdSenseControlFactory;
use Hafo\Orm\Encryption\KeysFileGenerator;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\Authentication\Unauthenticator;
use Hafo\Security\JsSDK;
use Hafo\UI\FlashMessage;
use Monolog\Logger;
use Nette\Application\AbortException;
use Nette\Application\Application;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Database\Context;
use Nette\Http\Session;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nextras\Dbal\Connection;
use VCD\Notifications\Notifications;
use VCD\UI\AdminModule\AdminPresenter;
use VCD\UI\FrontModule\BlogModule\BlogPresenter;
use VCD\UI\FrontModule\UserModule\PostOfficePresenter;
use VCD\UI\FrontModule\WebModule\EbooksPresenter;
use VCD\UI\FrontModule\WebModule\FlashMessageControl;
use VCD\Users\DefaultModel\Newsletter;
use VCD\UI\FrontModule\UserModule\LoginBoxControl;
use VCD2\FlashMessageException;
use VCD2\Orm;
use VCD2\PostOffice\Service\PostOffice;
use VCD2\Users\Consent;
use VCD2\Users\Migration\EncryptionMigration;
use VCD2\Users\Service\UserContext;

abstract class BasePresenter extends Presenter
{
    protected $container;

    protected $orm;

    protected $userContext;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
        $this->orm = $container->get(Orm::class);
        $this->userContext = $container->get(UserContext::class);
        $this->logger = $container->get(Logger::class)->withName('vcd.ui');
    }

    public function startup()
    {
        parent::startup();

        // optimized notifications loading
        if ($this->user->isInRole('admin') && $this->isSignalReceiver('', 'refreshNotifications')) {
            $this->payload->notifications = $this->container->get(Notifications::class)->count();
            $this->sendPayload();
        }

        // flash messages
        $this->template->flashMessagesEnabled = true;
        $this->addComponent(new FlashMessageControl($this->template->flashes), 'flashes');

        if ($this instanceof AdminPresenter) {
            return;
        }

        // db encryption keys generator
        //(new KeysFileGenerator(__DIR__ . '/../config/crypto.php'))->initialGenerate(__DIR__ . '/../VCD2');

        // migration
        //(new EncryptionMigration($this->container))->run();

        // private backdoor
        //$this->container->get(IdAuthenticator::class)->login(1);

        // login
        $this->addComponent($this->container->create($this->user->isLoggedIn() ? FrontModule\UserModule\UserBoxControl::class : LoginBoxControl::class), 'user');
        $this->addComponent($this->container->create($this->user->isLoggedIn() ? FrontModule\UserModule\UserBoxControl::class : FrontModule\UserModule\LoginBoxControl::class)->noWrapper(), 'userMobile');

        // SDKs
        $this->template->fbAppId = $this->container->get('facebook.appId');
        $this->template->googleAppId = $this->container->get('google.appId');
        $this->template->googleSDK = $this->container->get(GoogleSDK::class);
        $this->template->facebookSDK = $this->container->get(FacebookSDK::class);
        $this->template->userSDK = $this->container->get(JsSDK::class);
        $this->template->og = [];

        $session = $this->container->get(Session::class)->getSection('vcd.applications');

        $this->template->googleConversion = false;

        $this->template->fbPixelUserData = [];
        $this->template->showFbPixel = false;
        if ($this->user->isLoggedIn()) {

            // update user's last_active
            if (!isset($this->container->get(Session::class)->getSection('vcd.security.fakeLogin')['originalUser'])) {
                $this->container->get(Connection::class)->queryArgs('UPDATE system_user SET last_active = NOW(), ip = %s, host = %s WHERE id = %i', [
                    $this->getHttpRequest()->getRemoteAddress(),
                    $this->getHttpRequest()->getRemoteHost(),
                    $this->user->id,
                ]);
            }

            // being fb pixel data
            $user = $this->userContext->getEntity();
            $names = explode(' ', $user->name);
            $phone = $user->phone;
            if (empty($phone)) {
                $phone = null;
            } else {
                $phone = str_replace(' ', '', $phone);
                if (Strings::startsWith($phone, '+420')) {
                    $phone = str_replace('+', '00', $phone);
                } else {
                    $phone = '00420' . $phone;
                }
            }
            $gender = null;
            if ($user->facebookGender === 'male') {
                $gender = 'm';
            } elseif ($user->facebookGender === 'female') {
                $gender = 'f';
            }
            $this->template->fbPixelUserData = $fbPixelUserData = (object)array_filter([
                'em' => $user->email, // email
                'fn' => Strings::lower($names[0]), // first name
                'ln' => isset($names[1]) ? Strings::lower($names[1]) : null, // last name
                'ph' => $phone, // phone
                //'ge' => $gender, // gender m/f
                //'ct' => empty($user->city) ? NULL : $user->city, // city
                //'st' => 'cz', // state
                //'zp' => empty($user->zip) ? NULL : $user->zip, // zip
            ]);
            $fbPixelUserDataHashed = [];
            foreach ($fbPixelUserData as $key => $val) {
                $fbPixelUserDataHashed[$key] = hash('sha256', $val);
            }
            $this->template->fbPixelUserDataHashed = urldecode(http_build_query(['ud' => $fbPixelUserDataHashed]));

            // fb pixel just finished order
            if (isset($session['justFinished'])) {
                $application = $this->container->get(Context::class)->table('vcd_application')->wherePrimary($session['justFinished'])->fetch();
                $this->template->fbPixelPurchase = (object)[
                    //'value' => $application['price'],
                    //'currency' => 'CZK',
                    'countChildren' => $application->related('vcd_application_child', 'application')->count(),
                ];
            }
            // end fb pixel data

            unset($session['justFinished']);
        }

        // google conversions + fb pixel
        if (!$this->user->isInRole('admin')) {
            $this->template->googleConversion = function () {
                return Html::el()->setHtml($this->container->get(Tracker::class)->getTrackingHtml());
            };
            $this->template->facebookPixel = function () {
                return Html::el()->setHtml($this->container->get(FacebookPixel::class)->getTrackingHtml());
            };
        }

        // letters
        $this->template->postOfficeLink = PostOfficePresenter::LINK_DEFAULT;
        $this->template->countUnreadLetters = 0;
        if ($this->user->isLoggedIn()) {
            $this->template->countUnreadLetters = $this->container->get(PostOffice::class)->countUnreadLetters();
            if ($this->user->isInRole('admin')) {
                $this->template->currentEvent = $this->orm->events->getCurrentEvent();
            }
        }

        // analytics
        $this->template->googleAnalytics = function () {
            return Html::el()->setHtml($this->container->get(Analytics::class)->getAnalyticsHtml());
        };

        // load common stuff from DB
        $db = $this->container->get(Context::class);
        $this->template->web = $web = $db->table('system_website')->fetch();
        $this->template->keywords = $web['keywords'];
        $this->template->description = $web['description'];
        $this->template->phone = $web['phone'];
        $this->template->email = $web['email'];
        $this->template->bankAccount = $web['bank_account'];
        $this->template->newNotifications = $this->container->get(Notifications::class)->count();
        $this->template->admin = $admin = $this->user->isInRole('admin');
        $this->template->isErrorPage = false;
        $this->template->ad = $db->table('vcd_ad')->fetch();
        $this->template->consentDocumentUrl = Consent::DOCUMENT_URL;
        $this->template->codes = [];
        $this->template->codesEbookBlog = $this instanceof EbooksPresenter || $this instanceof BlogPresenter;
        if ($admin) {
            $this->template->codes = $db->table('vcd_web_code')->where('visible = 1')->order('position ASC');
        } elseif ($this->user->isLoggedIn()) {
            $this->template->codes = $db->table('vcd_web_code')->where('visible = 1 OR visible = 2 OR visible = 4')->order('position ASC');
        } else {
            $this->template->codes = $db->table('vcd_web_code')->where('visible = 1 OR visible = 2 OR visible = 3 OR visible = 4')->order('position ASC');
        }

        // newsletter link in footer
        $this->template->showNewsletterLink = !$this->user->isLoggedIn() || !$this->container->get(Newsletter::class)->isAdded($this->user->identity->data['email']);

        // hash for invalidating CSS&JS files
        $hashFile = $this->container->get('app') . '/.hash';
        $this->template->hash = file_exists($hashFile) ? file_get_contents($hashFile) : '';

        //$this->template->baseUrl = $this->template->baseUri = 'https://vcd.lukasklika.cz';
    }

    public function handle500()
    {
        throw new \Exception;
    }

    public function handleLogout()
    {
        $this->container->get(Unauthenticator::class)->logout();
        $this->presenter->flashMessage('Odhlášení bylo úspěšné.', 'success');
        $this->redirect('this');
    }

    public function handleRefreshNotifications()
    {
        if (!$this->user->isInRole('admin')) {
            throw new BadRequestException;
        }
        if ($this->presenter->isAjax()) {
            $this->template->newNotifications = $this->container->get(Notifications::class)->count();
            $this->redrawControl('pageTitle');
            $this->redrawControl('notifications');
            $this->redrawControl('notificationsTop');
        }
    }

    public function realRedirect($destination = null, $args = [])
    {
        try {
            $this->redirect($destination, $args);
        } catch (AbortException $e) {
            if ($this->presenter->isAjax() && $this->presenter->payload->redirect) {
                $this->presenter->payload->realRedirect = $this->presenter->payload->redirect;
                $this->sendPayload();
            } else {
                throw $e;
            }
        }
    }

    public function realRedirectUrl($url, $code = null)
    {
        try {
            $this->redirectUrl($url, $code);
        } catch (AbortException $e) {
            if ($this->presenter->isAjax() && $this->presenter->payload->redirect) {
                $this->presenter->payload->realRedirect = $this->presenter->payload->redirect;
                $this->sendPayload();
            } else {
                throw $e;
            }
        }
    }

    public function createAd($adSlot, $width, $height, $classes)
    {
        $ad = $this->container->get(AdSenseControlFactory::class)->create($adSlot, $width, $height, $classes);
        $this->addComponent($ad, 'adsense' . $adSlot);

        return $ad;
    }

    public function createOurAd($name)
    {
        $row = $this->container->get(Context::class)->table('vcd_ad')->where('name', $name)->fetch();
        if ($row) {
            $ad = new AdControl($row['enabled'], $row['url'], $row['image'], $row['classes_img'], $row['classes_a']);
            $this->addComponent($ad, 'ad' . $name);

            return $ad;
        }

        return null;
    }

    public function tryExecute($callback, $onError = null)
    {
        try {
            $callback();
        } catch (FlashMessageException $e) {
            $e->flashMessage($this);
            $this->logger->alert(sprintf('FlashMessage(%s): %s', get_class($e), $e->getFlashMessage()->getMessage()));
            if (is_callable($onError)) {
                $onError($e);
            }
        }
    }

    /**
     * @param string|FlashMessage $message
     * @param string $type
     * @return \stdClass
     */
    public function flashMessage($message, $type = 'info')
    {
        if ($message === null) {
            return;
        }
        $flash = $message instanceof FlashMessage ? $message : new FlashMessage($type, $message);
        $this['flashes']->addFlashMessage($flash);

        return parent::flashMessage($flash->getMessage(), $flash->getType());
    }
}
