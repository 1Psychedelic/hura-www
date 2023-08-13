<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\DI\Container;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\FakeLogin;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Http\Session;
use Nette\Security\User;
use Hafo\Facebook\UI\FacebookLoginComponent;
use Hafo\Google\UI\GoogleLoginComponent;
use Hafo\Security\Authentication\Unauthenticator;
use VCD\Notifications\Notifications;
use VCD2\Orm;
use VCD2\PostOffice\Service\PostOffice;
use VCD2\Users\Service\UserContext;

class UserBoxControl extends Control {

    private $user;

    private $userEntity;

    private $unauthenticator;

    private $notifications;
    
    private $originalUser;

    private $authenticator;

    private $session;

    private $wrapper = TRUE;

    private $userContext;

    private $orm;

    private $fakeLogin;

    private $postOffice;

    function __construct(Container $container) {
        $this->user = $container->get(User::class);
        $this->fakeLogin = $container->get(FakeLogin::class);
        $this->userContext = $container->get(UserContext::class);
        $this->userEntity = $this->userContext->getEntity();
        $this->orm = $container->get(Orm::class);
        $this->unauthenticator = $container->get(Unauthenticator::class);
        $this->notifications = $container->get(Notifications::class);
        $this->authenticator = $container->get(IdAuthenticator::class);
        $this->session = $container->get(Session::class)->getSection('vcd.security.fakeLogin');
        $this->notifications = $container->get(Notifications::class);
        $this->postOffice = $container->get(PostOffice::class);

        $facebook = $container->create(FacebookLoginComponent::class);
        $google = $container->create(GoogleLoginComponent::class);

        if(isset($this->session['originalUser'])) {
            $this->originalUser = $this->orm->users->get($this->session['originalUser']);
        }
        $facebook->onAuthorize[] = function() {
            $this->presenter->flashMessage('Propojení bylo úspěšné, nyní můžete k přihlašování využívat Facebook tlačítko.', 'success');
            $this->presenter->realRedirect('this');
        };
        $facebook->onError[] = function() {
            $this->presenter->flashMessage('Propojení s Facebook účtem selhalo. Zkuste to znovu později.', 'danger');
            $this->presenter->realRedirect('this');
        };
        $facebook->onDeauthorize[] = function() {
            $this->user->logout(TRUE);
            $this->presenter->realRedirect('this');
        };
        $this->addComponent($facebook, 'fb');

        $google->onAuthorize[] = function() {
            $this->presenter->flashMessage('Propojení bylo úspěšné, nyní můžete k přihlašování využívat Google tlačítko.', 'success');
            $this->presenter->realRedirect('this');
        };
        $google->onError[] = function() {
            $this->presenter->flashMessage('Propojení s Google účtem selhalo. Zkuste to znovu později.', 'danger');
            $this->presenter->realRedirect('this');
        };
        $this->addComponent($google, 'google');
    }

    function handleRemoteLogout() {
        if($this->user->isLoggedIn()) {
            $this->unauthenticator->remoteLogout();
            $this->presenter->flashMessage('Odhlásili jsme Vás ze všech zařízení kromě tohoto.', 'success');
            $this->redirect('this');
        } else {
            throw new ForbiddenRequestException;
        }
    }

    function handleFakeLogout() {
        if($this->originalUser) {
            $this->unauthenticator->logout();
            $this->authenticator->login($this->originalUser->id);
            unset($this->session['originalUser']);
            $this->presenter->redirect('this');
        }
    }

    function noWrapper() {
        $this->wrapper = FALSE;
        return $this;
    }

    function render() {

        $this->template->setFile(__DIR__ . '/default.latte');

        $this->template->wrapper = $this->wrapper;

        $this->template->credit = $this->userEntity->creditBalance;

        $this->template->admin = $this->userEntity->isAdmin();
        if($this->template->admin) {
            $this->template->notifications = $this->notifications->count();
        }
        $this->template->userEntity = $this->userEntity;
        $this->template->facebookAuthorize = $this->presenter->link('this', ['do' => $this['fb']->getParameterId('authorize')]);
        $this->template->facebookDeauthorize = $this->presenter->link('this', ['do' => $this['fb']->getParameterId('deauthorize')]);
        $this->template->googleAuthorize = $this->presenter->link('this', ['do' => $this['google']->getParameterId('authorize')]);
        $name = $this->userEntity->name;
        $this->template->name = strpos($name, ' ') === FALSE ? $name : explode(' ', $name)[0];
        $this->template->originalUser = $this->originalUser;

        $creditWithNearestExpiration = $this->userEntity->creditWithNearestExpiration;
        if($creditWithNearestExpiration !== NULL && $creditWithNearestExpiration->expiresAt !== NULL) {
            if($creditWithNearestExpiration->amount === $this->userEntity->creditBalance) {
                $this->template->allCreditsExpireAt = $creditWithNearestExpiration->expiresAt;
            } else {
                $this->template->someCreditsExpireAt = $creditWithNearestExpiration->expiresAt;
            }
        }

        $this->template->linkProfile = ProfilePresenter::LINK_DEFAULT;
        $this->template->linkLetters = PostOfficePresenter::LINK_DEFAULT;
        $this->template->showLettersLink = $this->userEntity->acceptedApplications->count() > 0;
        $this->template->countUnreadLetters = $this->postOffice->countUnreadLetters();
        $this->template->render();
    }

}
