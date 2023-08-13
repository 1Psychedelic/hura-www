<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\DI\Container;
use Hafo\Security\Storage\Profiles;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\User;
use VCD\UI\FrontModule\WebModule\ReviewsPresenter;
use VCD2\Credits\Credit;
use VCD2\Orm;
use VCD2\Reviews\Service\Reviews;
use VCD2\Users\Service\UserContext;

class ProfileControl extends Control
{

    private $user;

    private $orm;
    
    private $canceled;

    private $canPostReview;

    function __construct(Container $container, $canceled = FALSE)
    {
        if (!$container->get(User::class)->isLoggedIn()) {
            throw new ForbiddenRequestException;
        }
        $this->user = $container->get(UserContext::class)->getEntity();
        $this->orm = $container->get(Orm::class);
        $this->canPostReview = $container->get(Reviews::class)->canPostReview($this->user);
        $this->canceled = $canceled;

        $this->onAnchor[] = function() {
            $this->template->applications = $applications = $this->canceled ? $this->user->appliedApplications : $this->user->appliedApplications->findBy(['canceledAt' => NULL, 'rejectedAt' => NULL]);

            /*foreach($applications as $application) {
                $this->addComponent(new EventTermControl($application->event->starts, $application->event->ends, TRUE), 'term' . $application->id);
            }*/
        };
    }

    function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');

        // orm
        $this->template->userEntity = $this->user;
        $this->template->canceled = $this->canceled;

        $this->template->canPostReview = $this->canPostReview;
        $this->template->reviewReward = Credit::AMOUNT_REVIEW_REWARD;
        $this->template->reviewLink = ReviewsPresenter::LINK_DEFAULT;
        $this->template->consentsLink = ConsentsPresenter::LINK_DEFAULT;
        $credits = [];
        foreach($this->user->credits as $credit) {
            if($credit->amount === 0 || ($credit->expiresAt !== NULL && $credit->expiresAt < new \DateTimeImmutable)) {
                continue;
            }
            $key = $credit->expiresAt === NULL ? NULL : $credit->expiresAt->format('j.n.Y');
            if(!array_key_exists($key, $credits)) {
                $credits[$key] = 0;
            }
            $credits[$key] += $credit->amount;
        }
        $this->template->credits = $credits;
        $this->template->render();
    }

}
