<?php

namespace VCD2\Utils;

use Hafo\DI\Container;
use Hafo\Orm\Encryption\DatabaseCryptoHelper;
use Hafo\Utils\Strings;
use Nette\Database\Context;
use Nette\Database\DriverException;
use VCD2\Applications\Application;
use VCD2\Applications\Child;
use VCD2\Applications\Invoice;
use VCD2\Orm;
use VCD2\Users\Consent;
use VCD2\Users\User;


class EncryptionMigrationTool
{
    const ENCRYPTED_FIELDS = [
        'vcd_application' => [
            'name' => 'varchar(255) COLLATE utf8_czech_ci NOT NULL',
            'email' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
            'city' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'phone' => 'varchar(20) COLLATE utf8_czech_ci DEFAULT NULL',
            'street' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'zip' => 'varchar(6) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_name' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_ico' => 'varchar(32) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_dic' => 'varchar(32) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_city' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_street' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_zip' => 'varchar(6) COLLATE utf8_czech_ci DEFAULT NULL',
        ],
        'vcd_application_child' => [
            'name' => 'varchar(256) COLLATE utf8_czech_ci NOT NULL',
            //'personal_id' => 'varchar(32) COLLATE utf8_czech_ci NOT NULL',
            'health' => 'text COLLATE utf8_czech_ci',
            'allergy' => 'text COLLATE utf8_czech_ci',
            'notes' => 'text COLLATE utf8_czech_ci',
        ],
        'vcd_child' => [
            'name' => 'varchar(256) COLLATE utf8_czech_ci NOT NULL',
            //'personal_id' => 'varchar(32) COLLATE utf8_czech_ci NOT NULL',
            'health' => 'text COLLATE utf8_czech_ci',
            'allergy' => 'text COLLATE utf8_czech_ci',
            'notes' => 'text COLLATE utf8_czech_ci',
        ],
        'system_user' => [
            'name' => 'varchar(255) COLLATE utf8_czech_ci NOT NULL',
            'email' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
            'phone' => 'varchar(20) COLLATE utf8_czech_ci DEFAULT NULL',
            'city' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'street' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'state' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'zip' => 'varchar(6) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_name' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_ico' => 'varchar(32) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_dic' => 'varchar(32) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_city' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_street' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'invoice_zip' => 'varchar(6) COLLATE utf8_czech_ci DEFAULT NULL',
            'facebook_id' => 'varchar(50) COLLATE utf8_czech_ci DEFAULT NULL',
            'facebook_name' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
            'facebook_first_name' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'facebook_middle_name' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'facebook_last_name' => 'varchar(127) COLLATE utf8_czech_ci DEFAULT NULL',
            'facebook_link' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
            'facebook_email' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
            'facebook_third_party_id' => 'varchar(50) COLLATE utf8_czech_ci DEFAULT NULL',
            'google_id' => 'varchar(50) COLLATE utf8_czech_ci DEFAULT NULL',
            'google_name' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
            'google_email' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
            'google_link' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
        ],
        'vcd_consent' => [
            'email' => 'varchar(255) COLLATE utf8_czech_ci DEFAULT NULL',
            'ip' => 'varchar(63) COLLATE utf8_czech_ci NOT NULL',
        ],
        'vcd_invoice' => [
            'name' => 'varchar(255) COLLATE utf8_czech_ci NOT NULL',
            'ico' => 'varchar(32) COLLATE utf8_czech_ci DEFAULT NULL',
            'dic' => 'varchar(32) COLLATE utf8_czech_ci DEFAULT NULL',
            'city' => 'varchar(127) COLLATE utf8_czech_ci NOT NULL',
            'street' => 'varchar(127) COLLATE utf8_czech_ci NOT NULL',
            'zip' => 'varchar(6) COLLATE utf8_czech_ci NOT NULL',
        ],
    ];

    const HASHED_FIELDS = [
        'vcd_application' => [
            'hashed_email' => 'encrypted_email',
        ],
        'vcd_application_child' => [
            'hashed_personal_id' => 'encrypted_personal_id',
        ],
        'vcd_child' => [
            'hashed_personal_id' => 'encrypted_personal_id',
        ],
        'system_user' => [
            'hashed_email' => 'encrypted_email',
            'hashed_facebook_id' => 'encrypted_facebook_id',
            'hashed_google_id' => 'encrypted_google_id',
        ],
    ];

    const TABLE_ENTITY_MAP = [
        'vcd_application' => Application::class,
        'vcd_application_child' => Child::class,
        'vcd_child' => \VCD2\Users\Child::class,
        'system_user' => User::class,
        'vcd_consent' => Consent::class,
        'vcd_invoice' => Invoice::class,
    ];

    const EMPTY_VS_NULL = [
        'system_user' => [
            'facebook_id',
            'google_id',
        ],
    ];

    private $container;

    private $orm;

    /** @var Context */
    private $db;

    private $crypto;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->orm = $container->get(Orm::class);
        $this->db = $this->container->get('db.fullAccess');
        $this->crypto = $this->container->get(DatabaseCryptoHelper::class);
    }

    public function clearEncryptedData()
    {
        foreach (self::ENCRYPTED_FIELDS as $table => $fields) {
            $this->db->query('UPDATE `' . $table . '` SET ' . implode(', ', array_map(function ($val) {
                return '`encrypted_' . $val . '` = NULL';
            }, array_keys($fields))));
        }
    }

    public function partialToNone()
    {
        foreach (self::ENCRYPTED_FIELDS as $table => $fields) {
            $joinedFields = implode(', ', array_map(function ($val) {
                return 'DROP `encrypted_' . $val . '`';
            }, array_keys($fields)));
            $this->db->query('ALTER TABLE `' . $table . '` ' . $joinedFields);
        }
    }

    public function noneToPartial()
    {
        try {
            foreach (self::ENCRYPTED_FIELDS as $table => $fields) {
                $joinedFields = implode(', ', array_map(function ($val) {
                    return 'ADD `encrypted_' . $val . '` TEXT NULL DEFAULT NULL AFTER `' . $val . '`';
                }, array_keys($fields)));
                $this->db->query('ALTER TABLE `' . $table . '` ' . $joinedFields);
            }
        } catch (DriverException $e) {
            if ($e->getCode() !== '42S21') { // duplicate column
                throw $e;
            }
        }

        foreach (self::ENCRYPTED_FIELDS as $table => $fields) {
            $selectFields = implode(', ', array_map(function ($val) {
                return '`' . $val . '`';
            }, array_keys($fields)));

            $ids = $this->db->table($table)->fetchPairs(null, 'id');
            foreach ($ids as $id) {
                $data = $this->db->table($table)->select($selectFields)->wherePrimary($id)->fetch()->toArray();
                $values = [];
                foreach ($data as $key => $val) {
                    $values['encrypted_' . $key] = $this->crypto->encrypt($table, Strings::camelize($key), $val);
                }
                $this->db->table($table)->wherePrimary($id)->update($values);
            }
        }
    }

    public function partialToFull()
    {
        foreach (self::ENCRYPTED_FIELDS as $table => $fields) {
            $joinedFields = implode(', ', array_map(function ($val) {
                return 'DROP `' . $val . '`';
            }, array_keys($fields)));
            $this->db->query('ALTER TABLE `' . $table . '` ' . $joinedFields);
        }
    }

    public function fullToPartial()
    {
        try {
            foreach (self::ENCRYPTED_FIELDS as $table => $fields) {
                $joinedFields = implode(', ', array_map(function ($val) use ($table) {
                    return 'ADD `' . $val . '` ' . self::ENCRYPTED_FIELDS[$table][$val] . ' AFTER `encrypted_' . $val . '`';
                }, array_keys($fields)));
                $this->db->query('ALTER TABLE `' . $table . '` ' . $joinedFields);
            }
        } catch (DriverException $e) {
            if ($e->getCode() !== '42S21') { // duplicate column
                throw $e;
            }
        }

        foreach (self::ENCRYPTED_FIELDS as $table => $fields) {
            $selectFields = implode(', ', array_map(function ($val) {
                return '`encrypted_' . $val . '`';
            }, array_keys($fields)));

            $ids = $this->db->table($table)->fetchPairs(null, 'id');
            foreach ($ids as $id) {
                $data = $this->db->table($table)->select($selectFields)->wherePrimary($id)->fetch()->toArray();
                $values = [];
                foreach ($data as $key => $val) {
                    $values[substr($key, strlen('encrypted_'))] = $this->crypto->decrypt($table, Strings::camelize($key), $val);
                }
                $this->db->table($table)->wherePrimary($id)->update($values);
            }
        }
    }

    public function fixHashes()
    {
        foreach (self::HASHED_FIELDS as $table => $fields) {
            $entityClass = self::TABLE_ENTITY_MAP[$table];
            $ids = $this->db->table($table)->fetchPairs(null, 'id');
            foreach ($ids as $id) {
                $encryptedFields = implode(',', array_values($fields));
                $data = $this->db->table($table)->select($encryptedFields)->wherePrimary($id)->fetch()->toArray();
                $values = [];

                foreach ($fields as $hashedField => $encryptedField) {
                    $decrypted = $this->crypto->decrypt($table, Strings::camelize($encryptedField), $data[$encryptedField]);
                    if ($decrypted === null) {
                        $values[$hashedField] = null;
                    } else {
                        $values[$hashedField] = call_user_func_array($entityClass . '::hashForSearch', [$decrypted, Strings::camelize($hashedField)]);
                    }
                }
                $this->db->table($table)->wherePrimary($id)->update($values);
            }
        }
    }

    public function fixNullEmptyValues($isPartial = false)
    {
        foreach (self::EMPTY_VS_NULL as $table => $fields) {
            $ids = $this->db->table($table)->fetchPairs(null, 'id');
            foreach ($ids as $id) {
                $encryptedFields = implode(',', array_map(function ($field) {
                    return 'encrypted_' . $field;
                }, $fields));
                $data = $this->db->table($table)->select($encryptedFields)->wherePrimary($id)->fetch()->toArray();
                $values = [];

                foreach ($fields as $field) {
                    $decrypted = $this->crypto->decrypt($table, Strings::camelize('encrypted_' . $field), $data['encrypted_' . $field]);
                    if (strlen($decrypted) === 0) {
                        $values['encrypted_' . $field] = null;

                        if ($isPartial) {
                            $values[$field] = null;
                        }

                        // all fields have hashed_ variant atm, be cautious when adding fields!
                        $values['hashed_' . $field] = null;
                    }
                }

                if (!empty($values)) {
                    $this->db->table($table)->wherePrimary($id)->update($values);
                }
            }
        }
    }
}
