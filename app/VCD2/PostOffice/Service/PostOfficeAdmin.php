<?php

namespace VCD2\PostOffice\Service;

use Nextras\Dbal\Connection;
use VCD2\Emails\Service\Emails\NewLetterMail;
use VCD2\Events\Event;
use VCD2\Orm;
use VCD2\PostOffice\Letter;
use VCD2\Users\User;

class PostOfficeAdmin {

    private $orm;

    private $connection;

    private $newLetterMail;

    function __construct(Orm $orm, Connection $connection, NewLetterMail $newLetterMail) {
        $this->orm = $orm;
        $this->connection = $connection;
        $this->newLetterMail = $newLetterMail;
    }

    /**
     * @param Event $event
     * @return int[] array of userId => countUnread
     */
    function countUnread(Event $event) {
        $unread = $this->connection->query(
            'SELECT user, COUNT(id) AS count_unread FROM vcd_letter WHERE event = %i AND is_read = 0 AND direction = %i GROUP BY user',
            $event->id,
            Letter::DIRECTION_PARENT_TO_CHILD
        )->fetchPairs('user', 'count_unread');
        return array_filter($unread);
    }

    /**
     * @param Event $event
     * @param User $user
     * @return \Nextras\Orm\Collection\ICollection|Letter[]
     */
    function findUnreadLetters(Event $event, User $user) {
        $where = [
            'event' => $event->id,
            'user' => $user->id,
            'direction' => Letter::DIRECTION_PARENT_TO_CHILD,
            'isRead' => FALSE,
        ];
        return $this->orm->letters->findBy($where);
    }

    function markRead(Event $event) {
        $this->connection->query(
            'UPDATE vcd_letter SET is_read = 1 WHERE event = %i AND direction = %i',
            $event->id,
            Letter::DIRECTION_PARENT_TO_CHILD
        );
    }

    function publish(Event $event) {
        $userIds = $this->connection->query(
            'SELECT user FROM vcd_letter WHERE event = %i AND visible = 0 AND direction = %i GROUP BY user',
            $event->id,
            Letter::DIRECTION_CHILD_TO_PARENT
        )->fetchPairs(NULL, 'user');

        $this->connection->queryArgs('UPDATE vcd_letter SET visible = 1 WHERE event = %i', [$event->id]);

        $users = $this->orm->users->findBy(['id' => $userIds]);
        $emails = [];
        foreach($users as $user) {
            $emails[$user->email] = TRUE;
        }

        if(count($emails) > 0) {
            $this->newLetterMail->send(array_keys($emails));
        }
    }

}
