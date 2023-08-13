<?php

namespace VCD2\Applications\Mapper;

use Hafo\Orm\Mapper\Mapper;
use VCD2\Applications\Application;
use VCD2\Applications\Repository\ApplicationRepository;

/**
 * @see ApplicationRepository
 */
class ApplicationMapper extends Mapper {

    protected $encrypted = [
        'encryptedName',
        'encryptedEmail',
        'encryptedPhone',
        'encryptedCity',
        'encryptedStreet',
        'encryptedZip',

        'encryptedInvoiceName',
        'encryptedInvoiceIco',
        'encryptedInvoiceDic',
        'encryptedInvoiceCity',
        'encryptedInvoiceStreet',
        'encryptedInvoiceZip',
    ];

    public function getTableName() : string {
        return 'vcd_application';
    }

    public function findAllForAdmin() {
        return $this->toCollection($this->builder()->addOrderBy('IF(is_applied = 1 AND is_accepted =0 AND is_rejected = 0 AND is_canceled = 0, 1, 0) DESC, applied_at DESC, updated_at DESC'));
    }

    public function findGroupedByEmail(array $excludeEmails = NULL) {
        $builder = $this->builder()
            ->groupBy('encrypted_email')
            ->andWhere('encrypted_email IS NOT NULL');

        if(!empty($excludeEmails)) {
            $excludeEmailsHashed = array_map(function($email) {
                return Application::hashForSearch($email, 'hashedEmail');
            }, $excludeEmails);
            $builder->andWhere('hashed_email NOT IN %s[]', $excludeEmailsHashed);
        }

        return $this->toCollection($builder);
    }

    public function findApplicationUserPairs($eventId) {
        $builder = $this->builder()
            ->select('id, user')
            ->where('user IS NOT NULL AND event = %i AND applied_at IS NOT NULL AND accepted_at IS NOT NULL AND rejected_at IS NULL AND canceled_at IS NULL', $eventId);
        return $this->connection->queryArgs($builder->getQuerySql(), $builder->getQueryParameters())->fetchPairs('id', 'user');
    }
}
