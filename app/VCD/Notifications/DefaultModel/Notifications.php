<?php

namespace VCD\Notifications\DefaultModel;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hafo\NameDays\NameDays;
use Hafo\Security\Storage\Roles;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Nette\Database\Context;
use Nette\Security\User;
use Throwable;
use VCD2\Orm;

class Notifications implements \VCD\Notifications\Notifications {

    /** @var Context */
    private $database;

    /** @var User */
    private $user;

    /** @var Roles */
    private $roles;

    /** @var Orm */
    private $orm;

    /** @var Factory */
    private $firebaseFactory;

    function __construct(Context $database, User $user, Roles $roles, Orm $orm, Factory $firebaseFactory) {
        $this->database = $database;
        $this->user = $user;
        $this->roles = $roles;
        $this->orm = $orm;
        $this->firebaseFactory = $firebaseFactory;
    }

    function add($message, $user = NULL, $related = NULL, $type = self::TYPE_MESSAGE, $push = false) {
        $userIds = $this->roles->getUserIds('notify');

        foreach($userIds as $uid) {
            $this->database->table('vcd_notification')->insert([
                'message' => $message,
                'type' => $type,
                'user' => $user,
                'recipient' => $uid,
                'added_at' => new \DateTime,
                'related_id' => $related
            ]);
        }

        if (!$push) {
            return;
        }

        $userEntity = null;
        if ($user !== null) {
            $userEntity = $this->orm->users->get($user);
        }

        $tokens = [];
        foreach ($userIds as $userId) {
            foreach ($this->orm->firebasePushTokens->findBy(['user' => $userId]) as $token) {
                $tokens[$token->token] = $userId;
            }
        }

        try {
            $messaging = $this->firebaseFactory->createMessaging();
            $message = Messaging\CloudMessage::new()
                ->withNotification(Messaging\Notification::create($message, $userEntity === null ? null : $userEntity->name));

            $messaging->sendMulticast($message, array_keys($tokens));

            // todo delete tokens

        } catch (Throwable $e) {
            // silent...
        }
    }

    function load() {
        if(!$this->user->isInRole('admin')) {
            return [];
        }
        $notifications = $this->database->table('vcd_notification')
            ->where('is_read = 0 AND recipient = ?', $this->user->id)
            ->order('id DESC')
            ->fetchAll();

        /*$regChildren = $this->database->table('vcd_child')->select('id,name,personal_id,parent')->fetchAll();
        $unregChildren = $this->database->table('vcd_application_child')->select('id,name,personal_id,application')->fetchAll();

        $pidFound = [];
        $allChildren = [];
        foreach($regChildren as $child) {
            if(array_key_exists($child['personal_id'], $pidFound)) {
                continue;
            }
            $tmp = $child->toArray();
            $tmp['email'] = $this->database->table('system_user')->select('email')->wherePrimary($child['parent'])->fetchField();
            $allChildren[] = $tmp;
            $pidFound[$child['personal_id']] = TRUE;
        }
        foreach($unregChildren as $child) {
            if(array_key_exists($child['personal_id'], $pidFound)) {
                continue;
            }
            $tmp = $child->toArray();
            $tmp['email'] = $this->database->table('vcd_application')->select('email')->wherePrimary($child['application'])->fetchField();
            $allChildren[] = $tmp;
            $pidFound[$child['personal_id']] = TRUE;
        }

        foreach($allChildren as $child) {
            try {
                $age = (new CzechPersonalId($child['personal_id']))->age();
                if($age->yearsAt((new \DateTime)->modify('-1 day')) < $age->yearsAt((new \DateTime)->modify('+1 month'))) {
                    $notifications[] = [
                        'user' => NULL,
                        'related_id' => NULL,
                        'added_at' => $age->nextBirthday((new \DateTime)->modify('-1 day')),
                        'message' => $child['name'] . ' slaví narozeniny ' . $age->dateBorn()->format('d. n.'),
                        'type' => self::TYPE_BIRTHDAY,
                        'is_read' => 0
                    ];
                }
            } catch (InvalidPersonalIdException $e) {

            }

            $name = explode(' ', $child['name'])[0];
            try {
                $nameDay = $this->nameDays->nameDayOf($name, (new \DateTime)->modify('-1 day'));
                if((new \DateTime)->modify('+1 month')->diff($nameDay)->invert) {
                    $notifications[] = [
                        'user' => NULL,
                        'related_id' => NULL,
                        'added_at' => $nameDay,
                        'message' => $child['name'] . ' má svátek ' . $nameDay->format('d. n.'),
                        'type' => self::TYPE_NAMEDAY,
                        'is_read' => 0
                    ];
                }
            } catch (UnrecognizedNameException $e) {
                Debugger::log('Name not found: ' . $name, ILogger::INFO);
            }
        }*/

        usort($notifications, function($a, $b) {
            return $a['added_at'] > $b['added_at'] ? -1 : 1;
        });

        return $notifications;
    }

    function count() {
        if(!$this->user->isInRole('admin')) {
            return 0;
        }
        return $this->database->table('vcd_notification')
            ->where('is_read = 0 AND recipient = ?', $this->user->id)
            ->order('id DESC')
            ->select('COUNT(id)')
            ->fetchField();
    }

}
