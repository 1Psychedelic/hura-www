<?php

namespace VCD2\Users\Service;

use Hafo\Security\SecurityException;
use Monolog\Logger;
use Nette\SmartObject;
use Nette\Utils\Random;
use VCD2\Applications\Application;
use VCD2\Orm;
use VCD2\Users\Child;
use VCD2\Users\User;

/**
 * @method onSignup(User $user)
 */
class AutomaticSignup {

    use SmartObject;

    public $onSignup = [];

    private $orm;

    /** @var Logger */
    private $logger;

    function __construct(Orm $orm, Logger $logger) {
        $this->orm = $orm;
        $this->logger = $logger->withName('vcd.automaticsignup');
    }

    function check($email) {
        $applications = $this->orm->applications->findBy([
            'isApplied!=' => false,
            'email' => $email
        ]);
        if(count($applications) > 0) {
            throw new SecurityException('This e-mail already has some applications in database.');
        }
    }

    /**
     * @param string $email
     * @param string $name
     * @return User
     * @throws SecurityException
     */
    function signup(string $email, string $name): User
    {
        $user = $this->orm->users->getByEmail($email);
        if($user !== NULL) {
            throw new SecurityException('E-mail already registered');
        }

        $user = new User($email, $name);
        $user->loginHash = Random::generate(40);
        $this->orm->persistAndFlush($user);

        $this->onSignup($user);

        return $user;
    }

    /**
     * @param string $email
     * @throws SecurityException
     * @deprecated
     */
    function createAccount($email) {
        $user = $this->orm->users->getByEmail($email);
        if($user !== NULL) {
            throw new SecurityException('E-mail already registered');
        }

        $applications = $this->orm->applications->findByEmail($email);

        // find template application & children
        /** @var Application|NULL $templateApplication */
        $templateApplication = NULL;
        foreach($applications as $application) {
            if($templateApplication === NULL || ($application->hasValidParentInfo && !$templateApplication->hasValidParentInfo)) {
                $templateApplication = $application;
            }
        }
        if($templateApplication === NULL) {
            $this->logger->error(sprintf('Pro e-mail %s nebyla nalezena žádná vyplněná přihláška.', $email));
            return; //throw new SecurityException('No valid application found');
        }

        // fill user data from template application
        $user = new User($email, $templateApplication->name);
        $templateApplication->copyPropertiesTo($user, [
            'phone',
            'city',
            'street',
            'zip',
            'agreedPersonalData',
            'agreedTermsAndConditions',
            'agreedPhotography',
            'isPayingOnInvoice',
            'invoiceName',
            'invoiceIco',
            'invoiceDic',
            'invoiceStreet',
            'invoiceCity',
            'invoiceZip',
        ]);
        $user->loginHash = Random::generate(40);
        $this->orm->persist($user);
        $this->orm->flush();

        $this->pairApplications($email, $applications);

        $this->onSignup($user);
    }

    /**
     * @param string $email
     * @param Application[]|NULL $loadedApplications
     * @throws SecurityException
     */
    function pairApplications($email, $loadedApplications = NULL) {
        $user = $this->orm->users->getByEmail($email);
        if($user === NULL) {
            throw new SecurityException('User not found.');
        }

        $applications = $loadedApplications === NULL ? $this->orm->applications->findByEmail($email) : $loadedApplications;

        // nothing to pair
        if(count($applications) === 0) {
            return;
        }

        /** @var Child[] $registeredChildren */
        $registeredChildren = [];
        foreach($user->children as $child) {
            $registeredChildren[$child->name] = $child;
        }

        $templateChildren = [];
        foreach($applications as $application) {
            foreach($application->children as $child) {
                if(!isset($templateChildren[$child->name]) && !isset($registeredChildren[$child->name])) {
                    if($child->hasValidInfo) {
                        $templateChildren[$child->name] = $child;
                    } else {
                        $this->logger->debug(sprintf('Dítě %s v přihlášce %s nemá vyplněné údaje, přeskakuji.', (string)$child, (string)$application));
                    }
                }
            }
        }

        // register children
        foreach($templateChildren as $name => $templateChild) {
            $child = Child::createFromApplicationChild($templateChild, $user);
            $this->orm->persist($child);
            $registeredChildren[$child->name] = $child;
            $this->logger->debug(sprintf('Registruji dítě %s z přihlášky %s.', (string)$templateChild, (string)$templateChild->application));
        }

        // assign applications
        foreach($applications as $application) {
            $application->user = $user;
            $this->orm->persist($application);
            $this->logger->debug(sprintf('Přiřazuji přihlášku %s uživateli %s.', (string)$application, (string)$user));
        }

        // assign application children
        foreach($applications as $application) {
            foreach($application->children as $child) {
                if(array_key_exists($child->name, $registeredChildren)) {
                    $child->child = $registeredChildren[$child->name];
                    $this->orm->persist($child);
                    $this->logger->debug(sprintf('Přiřazuji dítě %s v přihlášce %s registrovanému dítěti %s.', (string)$child, (string)$child->application, (string)$child->child));
                } else {
                    $this->logger->error(sprintf('Dítě %s nebylo nalezeno.', $child->name));
                }
            }
        }

        // done
        $this->orm->flush();
    }

}
