<?php

namespace VCD\UI\AdminModule;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Hafo\Exceptionless\Monolog\ExceptionlessHandler;
use Hafo\Logger\ExceptionLogger;
use Hafo\NetteBridge\UI\DropzoneControl;
use Hafo\Persona\HumanAge;
use Hafo\Security\Authentication\Authenticator\PasswordLogin;
use Hafo\Security\Authentication\EmailNotVerifiedException;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\Authentication\LoginException;
use Hafo\Security\Authentication\Unauthenticator;
use Hafo\Security\SecurityException;
use Monolog\Logger;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Http\IResponse;
use Nette\Http\Session;
use Nette\Utils\DateTime;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;
use Throwable;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD\Admin\Applications\UI\ApplicationInternalNoteControl;
use VCD\Admin\Applications\UI\ApplicationsFiltersControl;
use VCD\Admin\Applications\UI\ChangeEventControl;
use VCD\Admin\Blog\UI\ArticlePageControl;
use VCD\Admin\Blog\UI\ArticlePagesControl;
use VCD\Admin\Blog\UI\CategoriesControl;
use VCD\Admin\Blog\UI\CategoryControl;
use VCD\Admin\Clicks\UI\ClickControl;
use VCD\Admin\Clicks\UI\ClicksControl;
use VCD\Admin\Cron\UI\CronLogsControl;
use VCD\Admin\Cron\UI\CronTasksControl;
use VCD\Admin\DevTools\UI\ClassFactoryControl;
use VCD\Admin\DevTools\UI\EncryptionControl;
use VCD\Admin\DevTools\UI\ScriptsControl;
use VCD\Admin\Ebooks\UI\EbookControl;
use VCD\Admin\Ebooks\UI\EbooksControl;
use VCD\Admin\Events\UI\EventAddonControl;
use VCD\Admin\Events\UI\EventAddonsControl;
use VCD\Admin\Events\UI\EventDiscountControl;
use VCD\Admin\Events\UI\EventDiscountsControl;
use VCD\Admin\Events\UI\EventStepControl;
use VCD\Admin\Events\UI\EventStepOptionControl;
use VCD\Admin\Events\UI\EventStepOptionsControl;
use VCD\Admin\Events\UI\EventStepsControl;
use VCD\Admin\Events\UI\LettersControl;
use VCD\Admin\Events\UI\LettersReadControl;
use VCD\Admin\FacebookImages\UI\FacebookImagesControl;
use VCD\Admin\Files\UI\FilesControl;
use VCD\Admin\Games\UI\GameControl;
use VCD\Admin\Games\UI\GamesControl;
use VCD\Admin\Homepage\UI\HomepageControl;
use VCD\Admin\Invoices\UI\InvoiceControl;
use VCD\Admin\Invoices\UI\InvoicesControl;
use VCD\Admin\MobileApp\UI\MobileAppPointsControl;
use VCD\Admin\Monolog\UI\MonologConfigControl;
use VCD\Admin\Monolog\UI\MonologControl;
use VCD\Admin\Newsletter\UI\AttendantsControl;
use VCD\Admin\Newsletter\UI\NewsletterBlacklistControl;
use VCD\Admin\Newsletter\UI\VipUsersControl;
use VCD\Admin\Pages\UI\PagesControl;
use VCD\Admin\Payments\UI\FioPaymentsControl;
use VCD\Admin\Payments\UI\PaymentsControl;
use VCD\Admin\Recruitment\UI\RecruitmentControl;
use VCD\Admin\Recruitment\UI\RecruitmentsControl;
use VCD\Admin\Stats\UI\StatsControl;
use VCD\Admin\UrlShortener\UI\UrlControl;
use VCD\Admin\UrlShortener\UI\UrlsControl;
use VCD\Admin\Users\UI\ConsentsControl;
use VCD\Admin\Users\UI\CreditsControl;
use VCD\Admin\Users\UI\CreditsGiveControl;
use VCD\Admin\Users\UI\SmsControl;
use VCD\Admin\Users\UI\UsersWithoutAccountControl;
use VCD\Admin\Website\UI\AdControl;
use VCD\Admin\Applications\UI\AcceptedChildrenControl;
use VCD\Admin\Applications\UI\ApplicationChildControl;
use VCD\Admin\Applications\UI\ApplicationControl;
use VCD\Admin\Applications\UI\ApplicationsControl;
use VCD\Admin\Blog\UI\ArticleControl;
use VCD\Admin\Blog\UI\ArticlesControl;
use VCD\Admin\Carousel\UI\CarouselControl;
use VCD\Admin\Changelog\UI\ChangelogControl;
use VCD\Admin\Website\UI\CodeControl;
use VCD\Admin\Website\UI\CodesControl;
use VCD\Admin\Discounts\UI\DiscountControl;
use VCD\Admin\Discounts\UI\DiscountsControl;
use VCD\Admin\Emails\UI\EmailControl;
use VCD\Admin\Emails\UI\EmailsControl;
use VCD\Admin\Events\UI\DiplomasControl;
use VCD\Admin\Events\UI\EventControl;
use VCD\Admin\Events\UI\EventDesignControl;
use VCD\Admin\Events\UI\EventsControl;
use VCD\Admin\Events\UI\EventTabsControl;
use VCD\Admin\Website\UI\EmailConfigControl;
use VCD\Admin\Website\UI\FacebookConfigControl;
use VCD\Admin\Gallery\UI\GalleryControl;
use VCD\Admin\Website\UI\FioConfigControl;
use VCD\Admin\Website\UI\GoogleConfigControl;
use VCD\Admin\Index\UI\IndexControl;
use VCD\Admin\Leaders\UI\LeaderControl;
use VCD\Admin\Leaders\UI\LeadersControl;
use VCD\Admin\Website\UI\LogsControl;
use VCD\Admin\LostFound\UI\LostFoundControl;
use VCD\Admin\NameDays\UI\NameDayControl;
use VCD\Admin\NameDays\UI\NameDaysControl;
use VCD\Admin\Newsletter\UI\NewsletterControl;
use VCD\Admin\Notifications\UI\NotificationsControl;
use VCD\Admin\Pages\UI\PageControl;
use VCD\Admin\Users\UI\ChildControl;
use VCD\Admin\Users\UI\ChildrenControl;
use VCD\Admin\Users\UI\DiplomaControl;
use VCD\Admin\Users\UI\UserControl;
use VCD\Admin\Users\UI\UsersControl;
use VCD\Admin\Website\UI\MenuControl;
use VCD\Admin\Website\UI\MenuItemControl;
use VCD\Admin\Website\UI\WebsiteConfigControl;
use VCD\UI\FrontModule\HomepageModule\HomepagePresenter;
use VCD\UI\FrontModule\UserModule\LoginBoxControl;
use VCD2\Applications\AgeOutOfRangeException;
use VCD2\Applications\Service\InvoiceGenerator;

class AdminPresenter extends BasePresenter
{
    const LINK_DEFAULT = ':Admin:Admin:default';
    const LINK_NOTIFICATIONS = ':Admin:Admin:notifications';
    const LINK_LOGIN = ':Admin:Admin:login';

    /** @persistent */
    public $print = false;

    public function processSignal()
    {
        try {
            parent::processSignal();
        } catch (Throwable $e) {
            if ($e instanceof AbortException) {
                throw $e;
            }
            $logger = $this->container->get(Logger::class)->withName('vcd.error');
            (new ExceptionLogger($logger))->log($e);
            $this->container->get(\Hafo\Exceptionless\Client::class)->logException($e);

            $this->flashMessage(get_class($e) . ': ' . $e->getMessage(), 'danger');
        }
    }

    public function actionDefault()
    {
        $this->addComponent(new IndexControl($this->container), 'index');
    }

    public function actionError500() {
        try {
            throw new \Exception('500');
        } catch (\Throwable $e) {
            throw new AgeOutOfRangeException('hmm', 123, $e);
        }
    }

    public function handleLogout() {
        $this->container->get(Unauthenticator::class)->logout();
        $this->redirect('login');
    }

    public function actionLogin()
    {
        $loginBox = $this->container->get(LoginBoxControl::class);
        $loginBox['loginForm']->onSuccess = [
            function (Form $f) {
                $passwordLogin = $this->container->get(PasswordLogin::class);
                if($f->isSubmitted() === $f['login']) {
                    $data = $f->getValues(TRUE);
                    try {
                        $passwordLogin->login($data);
                        $this->presenter->flashMessage('Přihlášení bylo úspěšné.', 'success');
                        $this->presenter->redirect('default');
                    } catch (LoginException $e) {
                        $this->presenter->flashMessage('Nesprávné přihlašovací údaje.', 'danger');
                        $this->presenter->redirect('this');
                    } catch (SecurityException $e) {
                        $this->presenter->flashMessage('Během pokusu o přihlášení došlo k chybě.', 'danger');
                        $this->presenter->redirect('this');
                    }
                }
            }
        ];
        $this->addComponent($loginBox, 'login');
    }

    public function actionChangelog()
    {
        $this->addComponent(new ChangelogControl($this->container), 'changelog');
    }

    public function actionCarousel($carousel = 'homepage', $page = null, $type = 0)
    {
        $this->addComponent(new CarouselControl($this->container, $carousel, $page, $type), 'carousel');
    }

    public function actionLostFound()
    {
        $this->addComponent(new LostFoundControl($this->container), 'lostfound');
    }

    public function actionPhotos($event = null, $type = 0)
    {
        $this->template->event = $event;
        $this->addComponent(new GalleryControl($this->container, $event, $type), 'gallery');
    }

    public function actionFioConfig()
    {
        $this->addComponent(new FioConfigControl($this->container), 'fioConfig');
    }

    public function actionGoogleConfig()
    {
        $this->addComponent(new GoogleConfigControl($this->container), 'googleConfig');
    }

    public function actionFacebookConfig()
    {
        $this->addComponent(new FacebookConfigControl($this->container), 'facebookConfig');
    }

    public function actionEmailConfig()
    {
        $this->addComponent(new EmailConfigControl($this->container), 'emailConfig');
    }

    public function actionEventDesign($event)
    {
        $this->template->event = $event;
        $this->addComponent(new EventDesignControl($this->container, $event), 'eventDesign');
    }

    public function actionEventTabs($event, $tab = null)
    {
        $this->template->event = $event;
        $this->addComponent(new EventTabsControl($this->container, $event, $tab), 'eventTabs');
    }

    public function handleGotoEvents()
    {
        $this->redirect('events');
    }

    public function actionEvent($id = null)
    {
        $this->template->id = $id;
        $this->addComponent(new EventControl($this->container, $id), 'event');
    }

    public function actionEvents(array $filters = [], $past = false)
    {
        $this->addComponent(new EventsControl($this->container, $filters, $past), 'events');
    }

    public function actionEventSteps($event)
    {
        $this->template->event = $event;
        $this->addComponent(new EventStepsControl($this->container, $event), 'eventSteps');
    }

    public function actionEventStep($event, $id = null)
    {
        $this->template->event = $event;
        $this->template->id = $id;
        $this->addComponent(new EventStepControl($this->container, $event, $id), 'eventStep');
    }

    public function actionEventStepOptions($event, $step)
    {
        $this->template->event = $event;
        $this->template->step = $step;
        $this->addComponent(new EventStepOptionsControl($this->container, $event, $step), 'eventStepOptions');
    }

    public function actionEventStepOption($event, $step, $id = null)
    {
        $this->template->event = $event;
        $this->template->step = $step;
        $this->template->id = $id;
        $this->addComponent(new EventStepOptionControl($this->container, $event, $step, $id), 'eventStepOption');
    }

    public function actionEventDiscounts($event)
    {
        $this->template->event = $event;
        $this->addComponent(new EventDiscountsControl($this->container, $event), 'eventDiscounts');
    }

    public function actionEventDiscount($event, $id = null)
    {
        $this->template->event = $event;
        $this->template->id = $id;
        $this->addComponent(new EventDiscountControl($this->container, $event, $id), 'eventDiscount');
    }

    public function actionEventAddons($event)
    {
        $this->template->event = $event;
        $this->addComponent(new EventAddonsControl($this->container, $event), 'eventAddons');
    }

    public function actionEventAddon($event, $id = null)
    {
        $this->template->event = $event;
        $this->template->id = $id;
        $this->addComponent(new EventAddonControl($this->container, $event, $id), 'eventAddon');
    }

    public function actionAcceptedChildren($event)
    {
        $this->addComponent(new AcceptedChildrenControl($this->container, $event), 'acceptedChildren');
        $this->template->event = $event;
    }

    public function actionAcceptedChildrenJson($event)
    {
        $data = [];
        $eventEntity = $this->orm->events->get($event);

        foreach ($eventEntity->acceptedChildren as $child) {
            list($name, $surname) = explode(' ', $child->name);
            $data[] = (object)[
                'id' => $child->id,
                'name' => $name,
                'surname' => $surname,
                'age' => (new HumanAge($child->dateBorn))->yearsAt($eventEntity->ends),
                'gender' => $child->gender,
            ];
        }

        $this->sendJson($data);
    }

    public function actionApplicationChild($application, $id = null)
    {
        $this->addComponent(new ApplicationChildControl($this->container, $application, $id), 'applicationChild');
    }

    public function actionApplication($id = null, $duplicate = null)
    {
        $this->addComponent(new ApplicationControl($this->container, $id, $duplicate), 'application');
    }

    public function actionApplicationInternalNote($id)
    {
        $this->addComponent(new ApplicationInternalNoteControl($this->container, $id), 'applicationInternalNote');
    }

    public function handleGotoApplications($event = null)
    {
        $filters = [];
        if ($event === null) {
            $filters['status'] = ApplicationsFiltersControl::STATUS_NEW;
        }
        $this->redirect('applications', ['filters' => $filters, 'savedFilter' => null, 'event' => $event]);
    }

    public function actionApplications(array $filters = [], ?int $savedFilter = null, $pinnedFilters = false)
    {
        if ($savedFilter === null && $filters === []) {
            $this->redirect('applications', ['filters' => ['status' => ApplicationsFiltersControl::STATUS_NEW]]);
        }

        $this->addComponent(new ApplicationsControl($this->container, $filters, $savedFilter, $pinnedFilters), 'applications');
        $this->template->fluid = true;
        $this->template->filters = $filters;

        if ($this->isAjax()) {
            $this['applications']->redrawControl('filters');
        }
    }

    public function actionApplicationChangeEvent(int $applicationId, ?int $eventId)
    {
        $this->addComponent(new ChangeEventControl($this->container, $applicationId, $eventId), 'changeEvent');
    }

    public function actionPayments($page = 1, $application = null)
    {
        $this->addComponent(new PaymentsControl($this->container, $page, $application), 'payments');
    }

    public function actionFioPayments($page = 1)
    {
        $this->addComponent(new FioPaymentsControl($this->container, $page), 'fioPayments');
    }

    public function actionInvoices($page = 1, $filters = [])
    {
        $this->addComponent(new InvoicesControl($this->container, $page, $filters), 'invoices');
    }

    public function actionInvoice($id = null)
    {
        $this->addComponent(new InvoiceControl($this->container, $id), 'invoice');
    }

    public function actionLeaders()
    {
        $this->addComponent(new LeadersControl($this->container), 'leaders');
    }

    public function actionLeader($id = null)
    {
        $this->addComponent(new LeaderControl($this->container, $id), 'leader');
    }

    public function actionEmails()
    {
        $this->addComponent(new EmailsControl($this->container), 'emails');
    }

    public function actionEmail($id = null)
    {
        $this->addComponent(new EmailControl($this->container, $id), 'email');
    }

    public function actionDiscounts($expired = false)
    {
        $this->addComponent(new DiscountsControl($this->container, $expired), 'discounts');
    }

    public function actionDiscount($id = null)
    {
        $this->addComponent(new DiscountControl($this->container, $id), 'discount');
    }

    public function actionNewsletter($divider = ';')
    {
        $this->addComponent(new NewsletterControl($this->container, $divider), 'newsletter');
    }

    public function actionNewsletterBlacklist()
    {
        $this->addComponent(new NewsletterBlacklistControl($this->container), 'blacklist');
    }

    public function actionNewsletterAttendants($divider = ';')
    {
        $this->addComponent(new AttendantsControl($this->container, $divider), 'attendants');
    }

    public function actionNewsletterVip($divider = ';')
    {
        $this->addComponent(new VipUsersControl($this->container, $divider), 'vipusers');
    }

    public function actionChildren(array $filters = [], $page = 1, $q = null)
    {
        $this->addComponent(new ChildrenControl($this->container, $filters, $page, $q), 'children');
        $this->template->fluid = true;
    }

    public function actionChild($id = null)
    {
        $this->addComponent(new ChildControl($this->container, $id), 'child');
    }

    public function actionUsers(array $filters = [], array $extra = [], $page = 1, $q = null)
    {
        $this->addComponent(new UsersControl($this->container, $filters, $extra, $page, $q), 'users');
        $this->template->fluid = true;
    }

    public function actionUser($id = null)
    {
        $this->addComponent(new UserControl($this->container, $id), 'usr');
    }

    public function actionUsersWithoutAccount(array $filters = [], array $extra = [])
    {
        $this->addComponent(new UsersWithoutAccountControl($this->container, $filters, $extra), 'users');
        $this->template->fluid = true;
    }

    public function actionCredits($showAll = false)
    {
        $this->addComponent(new CreditsControl($this->container, $showAll), 'credits');
    }

    public function actionCreditsGive()
    {
        $this->addComponent(new CreditsGiveControl($this->container), 'creditsGive');
    }

    public function actionConsents(array $filters = [], array $extra = [], $page = 1)
    {
        $this->addComponent(new ConsentsControl($this->container, $filters, $extra, $page), 'consents');
    }

    public function actionSms($users = null, $message = null)
    {
        $this->addComponent(new SmsControl($this->container, $users === null ? [] : explode(' ', $users), $message), 'sms');
    }

    public function actionDiploma($id)
    {
        $this->addComponent(new DiplomaControl($this->container, $id), 'diploma');
    }

    public function actionDiplomas($id)
    {
        $this->template->id = $id;
        $this->addComponent(new DiplomasControl($this->container, $id), 'diplomas');
    }

    public function actionLetters($id)
    {
        $this->template->id = $id;
        $this->addComponent(new LettersControl($this->container, $id), 'letters');
    }

    public function actionLettersRead($id)
    {
        $this->template->id = $id;
        $this->addComponent(new LettersReadControl($this->container, $id), 'lettersRead');
    }

    public function actionNameDays()
    {
        $this->addComponent(new NameDaysControl($this->container), 'nameDays');
    }

    public function actionNameDay($id = null)
    {
        $this->addComponent(new NameDayControl($this->container, $id), 'nameDay');
    }

    public function actionWebsite()
    {
        $this->addComponent(new WebsiteConfigControl($this->container), 'websiteConfig');
    }

    public function actionWebsiteMenu()
    {
        $this->addComponent(new MenuControl($this->container), 'websiteMenu');
    }

    public function actionWebsiteMenuItem($menu, $id = null)
    {
        $this->addComponent(new MenuItemControl($this->container, $menu, $id), 'websiteMenuItem');
    }

    public function actionPages()
    {
        $this->addComponent(new PagesControl($this->container), 'pages');
    }

    public function actionPage($id = null, $html = false)
    {
        $this->addComponent(new PageControl($this->container, $id, $html), 'page');
    }

    public function actionNotifications()
    {
        $this->addComponent(new NotificationsControl($this->container), 'notifications');
    }

    public function actionCodes()
    {
        $this->addComponent(new CodesControl($this->container), 'codes');
    }

    public function actionCode($id = null)
    {
        $this->addComponent(new CodeControl($this->container, $id), 'code');
    }

    public function actionAd($id)
    {
        $this->addComponent(new AdControl($this->container, $id), 'ad');
    }

    public function actionBlogArticles()
    {
        $this->addComponent(new ArticlesControl($this->container), 'articles');
    }

    public function actionBlogArticle($id = null)
    {
        $this->template->id = $id;
        $this->addComponent(new ArticleControl($this->container, $id), 'article');
    }

    public function actionBlogArticlePages($article)
    {
        $this->template->article = $article;
        $this->addComponent(new ArticlePagesControl($this->container, $article), 'articlePages');
    }

    public function actionBlogArticlePage($article, $id = null)
    {
        $this->template->article = $article;
        $this->template->id = $id;
        $this->addComponent(new ArticlePageControl($this->container, $article, $id), 'articlePage');
    }

    public function actionBlogCategories()
    {
        $this->addComponent(new CategoriesControl($this->container), 'categories');
    }

    public function actionBlogCategory($id = null)
    {
        $this->addComponent(new CategoryControl($this->container, $id), 'category');
    }

    public function actionFiles($dir = null)
    {
        $this->addComponent(new FilesControl($this->container, $this->container->get('www'), $dir), 'files');
    }

    public function actionEbooks()
    {
        $this->addComponent(new EbooksControl($this->container), 'ebooks');
    }

    public function actionEbook($id = null)
    {
        $this->addComponent(new EbookControl($this->container, $id), 'ebook');
    }

    public function actionGames()
    {
        $this->addComponent(new GamesControl($this->container), 'games');
    }

    public function actionGame($id = null)
    {
        $this->addComponent(new GameControl($this->container, $id), 'game');
    }

    public function actionRecruitments()
    {
        $this->addComponent(new RecruitmentsControl($this->container), 'recruitments');
    }

    public function actionRecruitment($id = null)
    {
        $this->addComponent(new RecruitmentControl($this->container, $id), 'recruitment');
    }

    public function actionCronLogs()
    {
        $this->addComponent(new CronLogsControl($this->container), 'cronLogs');
    }

    public function actionCronTasks($completed = false)
    {
        $this->addComponent(new CronTasksControl($this->container, $completed), 'cronTasks');
        $this->template->fluid = true;
    }

    public function actionFakeLogin($id)
    {
        $section = $this->container->get(Session::class)->getSection('vcd.security.fakeLogin');
        $section['originalUser'] = $this->user->id;
        $this->container->get(Unauthenticator::class)->logout();
        $this->container->get(IdAuthenticator::class)->login($id);
        $this->redirect(HomepagePresenter::LINK_DEFAULT);
    }

    public function actionClicks($url = null)
    {
        $this->addComponent(new ClicksControl($this->container, $url), 'clicks');
    }

    public function actionClick($url)
    {
        $this->addComponent(new ClickControl($this->container, $url), 'click');
    }

    public function actionStats($year = null, $tab = StatsControl::TAB_ATTENDATION)
    {
        $this->addComponent(new StatsControl($this->container, $year, $tab), 'stats');
    }

    public function actionShortUrls()
    {
        $this->addComponent(new UrlsControl($this->container), 'shortUrls');
    }

    public function actionShortUrl($id = null)
    {
        $this->addComponent(new UrlControl($this->container, $id), 'shortUrl');
    }

    public function actionQrCode($text, $label, $size = 10)
    {
        $qr = new QrCode;
        $qr->setText($text);
        $qr->setSize(300);
        $qr->setMargin(10);
        $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $qr->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qr->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $qr->setLabel($label);
        $qr->setLabelFontSize($size);
        $qr->setWriterByName('png');
        header('Content-Type: ' . $qr->getContentType());
        echo $qr->writeString();
        $this->terminate();
    }

    public function actionQr($text = null, $label = null, $size = 10)
    {
        $f = new Form;
        $f->setRenderer(new BootstrapRenderer);
        $f->addText('text', 'Odkaz nebo text');
        $f->addText('label', 'Popisek pod QR kódem');
        $f->addText('size', 'Velikost popisku');
        $f->addProtection();
        $f->addSubmit('go', 'Vygenerovat QR');
        $f->onSuccess[] = function (Form $f) {
            if ($f->isSubmitted() === $f['go']) {
                $data = $f->getValues(true);
                $this->redirect('this', ['text' => $data['text'], 'label' => $data['label'], 'size' => $data['size']]);
            }
        };
        $f->setValues([
            'text' => $text,
            'label' => $label,
            'size' => $size,
        ]);
        $this->addComponent($f, 'form');
        $this->template->text = $text;
        $this->template->label = $label;
        $this->template->size = $size;
    }

    public function actionHomepage()
    {
        $this->addComponent(new HomepageControl($this->container), 'homepage');
    }

    public function actionEventQrCode($id)
    {
        $row = $this->db()->table('vcd_event')->wherePrimary($id)->fetch();

        $qr = new QrCode;
        $qr->setText($this->link('//Front:event', ['id' => $row['slug']]));
        $qr->setSize(300);
        $qr->setMargin(10);
        $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $qr->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qr->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        //$qr->setLabel($label);
        //$qr->setLabelFontSize($size);
        $qr->setWriterByName('png');
        header('Content-Type: ' . $qr->getContentType());
        echo $qr->writeString();
        $this->terminate();
    }

    public function actionUserQrCode($id)
    {
        $row = $this->db()->table('system_user')->wherePrimary($id)->fetch();

        $qr = new QrCode;
        $qr->setText($this->link('//Front:homepage', ['do' => 'qrLogin', 'hash' => $row['qr_hash']]));
        $qr->setSize(300);
        $qr->setMargin(10);
        $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $qr->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qr->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        //$qr->setLabel($label);
        //$qr->setLabelFontSize($size);
        $qr->setWriterByName('png');
        header('Content-Type: ' . $qr->getContentType());
        echo $qr->writeString();
        $this->terminate();
    }

    public function actionDiscountQrCode($id)
    {
        $row = $this->db()->table('vcd_discount')->wherePrimary($id)->fetch();

        $qr = new QrCode;
        $qr->setText($row['code']);
        $qr->setSize(300);
        $qr->setMargin(10);
        $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $qr->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qr->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        //$qr->setLabel($label);
        //$qr->setLabelFontSize($size);
        $qr->setWriterByName('png');
        header('Content-Type: ' . $qr->getContentType());
        echo $qr->writeString();
        $this->terminate();
    }

    public function actionMobileAppPoints()
    {
        $this->addComponent(new MobileAppPointsControl($this->container), 'mobileAppPoints');
    }

    public function actionDev()
    {
        $this->addComponent(new ClassFactoryControl($this->container), 'dev');
    }

    public function actionEncryption()
    {
        $this->addComponent(new EncryptionControl($this->container), 'encryption');
    }

    public function actionLogs()
    {
        $this->addComponent(new LogsControl($this->container), 'logs');
    }

    public function actionScripts()
    {
        $this->addComponent(new ScriptsControl($this->container), 'scripts');
    }

    public function actionMonolog($id = null, $since = null, $till = null, $user = null, $minLevel = null, $maxLevel = null, $request = null, $q = null, $channel = null, $ip = null, $order = 'DESC')
    {
        $this->template->fluid = true;
        if ($since !== null) {
            try {
                $since = DateTime::from($since);
            } catch (\Exception $e) {
                $since = null;
            }
        }
        if ($till !== null) {
            try {
                $till = DateTime::from($till);
            } catch (\Exception $e) {
                $till = null;
            }
        }
        $this->addComponent(new MonologControl($this->container, $id, $since, $till, $user, $minLevel, $maxLevel, $request, $q, $channel, $ip, $order), 'monolog');
    }

    public function actionViewLog($file)
    {
        $filename = $this->container->get('app') . '/../log/' . $file;
        if (file_exists($filename)) {
            header('Content-type: text/html');
            echo file_get_contents($filename);
            die;
        }
    }

    public function actionMonologConfig()
    {
        $this->addComponent(new MonologConfigControl($this->container), 'monologConfig');
    }

    public function actionLog($filename)
    {
        $path = $this->container->get('app') . '/../log/' . $filename;
        if (file_exists($path)) {
            $this->template->content = file_get_contents($path);
            $fileinfo = new \SplFileInfo($path);
            $types = [
                'txt' => 'text/plain',
                'log' => 'text/plain',
            ];
            $ext = $fileinfo->getExtension();
            if (array_key_exists($ext, $types)) {
                $this->container->get(IResponse::class)->setContentType($types[$ext]);
            }
        } else {
            throw new BadRequestException;
        }
    }

    public function actionTest()
    {
        $this->template->groups = $groups = $this->db()->query('SELECT DISTINCT category FROM test ORDER BY position ASC')->fetchPairs(null, 'category');
        $this->template->groupTests = function ($group) {
            return $this->db()->table('test')->where('category', $group)->order('position ASC');
        };
        $this->template->userResults = function ($id) {
            $data = [];
            foreach ($this->db()->table('test_result')->where('test', $id) as $result) {
                $data[$result->ref('user')['name']] = $result['result'];
            }

            return $data;
        };
        $this->template->tests = $tests = $this->db()->table('test')->order('position ASC');

        $results = [
            0 => 'Netestováno',
            1 => 'Zdá se OK',
            2 => 'Chyba!',
        ];
        $f = new Form;
        foreach ($tests as $test) {
            $result = $this->db()->table('test_result')->where('test', $test['id'])->where('user', $this->user->id)->fetch();
            $f->addRadioList($test['id'] . '_result', '', $results)->setValue($result ? $result['result'] : 0);
            $f->addTextArea($test['id'] . '_notes', '')
                ->setValue($result ? $result['notes'] : null);
        }
        $f->addSubmit('save', 'Uložit');
        $f->onSuccess[] = function (Form $f) use ($tests) {
            if ($f->isSubmitted() === $f['save']) {
                $data = $f->getValues(true);
                foreach ($tests as $test) {
                    $row = $this->db()->table('test_result')->where('test', $test['id'])->where('user', $this->user->id)->fetch();
                    if ($row) {
                        $this->db()->table('test_result')->wherePrimary($row['id'])->update([
                            'result' => $data[$test['id'] . '_result'],
                            'notes' => $data[$test['id'] . '_notes'],
                        ]);
                    } else {
                        $this->db()->table('test_result')->insert([
                            'test' => $test['id'],
                            'user' => $this->user->id,
                            'result' => $data[$test['id'] . '_result'],
                            'notes' => $data[$test['id'] . '_notes'],
                        ]);
                    }
                }
                $this->flashMessage('Uloženo', 'success');
                $this->redirect('this');
            }
        };
        $this->addComponent($f, 'form');
        $this->template->results = $results;
    }

    public function actionFacebookImages($url = '/')
    {
        $this->addComponent(new FacebookImagesControl($this->container, $url), 'facebookImages');
    }

    public function handleInvoice($id)
    {
        $invoice = $this->orm->invoices->getByInvoiceId($id);
        if ($invoice === null) {
            throw new ForbiddenRequestException;
        }

        $raw = $this->container->get(InvoiceGenerator::class)->generate($invoice);
        $this->container->get(IResponse::class)->setContentType('application/pdf');
        $this->presenter->sendResponse(new TextResponse($raw));
    }

    public function handlePhpinfo()
    {
        phpinfo();
        $this->terminate();
    }

    private function dropzoneTemplate($dir, DropzoneControl $control)
    {
        return function ($name) use ($dir, $control) {
            return Html::el()->addHtml(
                Html::el('a')->href($this->template->baseUri . str_replace($this->container->get('www'), '', $dir . '/' . $name))->target('blank')->setText($name)
            )->addHtml(
                Html::el('br')
            )->addHtml(
                Html::el('a')->href($control->link('delete!', ['file' => $name]))->setText('Smazat')
            );
        };
    }

    /**
     * @return Context
     */
    private function db()
    {
        return $this->container->get(Context::class);
    }
}
