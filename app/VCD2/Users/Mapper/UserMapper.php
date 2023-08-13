<?php

namespace VCD2\Users\Mapper;


use Hafo\Orm\Mapper\Mapper;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;
use Nextras\Orm\Mapper\IMapper;
use VCD2\Events\Mapper\EventMapper;

class UserMapper extends Mapper {

    protected $encrypted = [
        'encryptedName',
        'encryptedEmail',
        'encryptedPhone',
        'encryptedCity',
        'encryptedStreet',
        'encryptedState',
        'encryptedZip',

        'encryptedInvoiceName',
        'encryptedInvoiceIco',
        'encryptedInvoiceDic',
        'encryptedInvoiceCity',
        'encryptedInvoiceStreet',
        'encryptedInvoiceZip',

        'encryptedFacebookId',
        'encryptedFacebookName',
        'encryptedFacebookFirstName',
        'encryptedFacebookMiddleName',
        'encryptedFacebookLastName',
        'encryptedFacebookLink',
        'encryptedFacebookEmail',
        'encryptedFacebookThirdPartyId',

        'encryptedGoogleId',
        'encryptedGoogleName',
        'encryptedGoogleEmail',
        'encryptedGoogleLink',
    ];

    public function getTableName() : string {
        return 'system_user';
    }

    public function getManyHasManyParameters(PropertyMetadata $sourceProperty, DbalMapper $targetMapper) {
        switch(TRUE) {
            case $targetMapper instanceof ChildMapper:
                return ['vcd_child_parent', ['parent', 'child']];
                break;
            case $targetMapper instanceof EventMapper:
                return ['vcd_event_user', ['user', 'event']];
                break;
        }
        return parent::getManyHasManyParameters($sourceProperty, $targetMapper);
    }

    public function findEmails() {
        $builder = $this->builder()
            ->select('DISTINCT encrypted_email')
            ->where('encrypted_email IS NOT NULL')
            ->addGroupBy('encrypted_email');
        
        $emails = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        )->fetchPairs(NULL, 'encrypted_email');

        return array_map(function($email) {
            return $this->cryptoHelper->decrypt($this->getTableName(), 'encryptedEmail', $email);
        }, $emails);
    }

}
