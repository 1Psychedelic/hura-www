<?php

namespace VCD2\Emails\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Emails\Attachment;
use VCD2\Emails\Mapper\AttachmentMapper;

/**
* @method AttachmentMapper getMapper()
*
* @method Attachment|NULL get($primaryKey)
* @method Attachment|NULL getBy(array $conds)
*
* @method Attachment[]|ICollection find($ids)
* @method Attachment[]|ICollection findAll()
* @method Attachment[]|ICollection findBy(array $where)
*
* @method Attachment hydrateEntity(array $data)
*/
class AttachmentRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Attachment::class];
    }

}
