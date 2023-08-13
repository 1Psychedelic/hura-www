<?php

namespace VCD2\Ebooks\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Ebooks\EbookDownload;
use VCD2\Ebooks\Mapper\EbookDownloadMapper;

/**
 * @method EbookDownloadMapper getMapper()
 *
 * @method EbookDownload|NULL get($primaryKey)
 * @method EbookDownload|NULL getBy(array $conds)
 *
 * @method EbookDownload[]|ICollection find($ids)
 * @method EbookDownload[]|ICollection findAll()
 * @method EbookDownload[]|ICollection findBy(array $where)
 *
 * @method EbookDownload hydrateEntity(array $data)
*/
class EbookDownloadRepository extends Repository {

    static function getEntityClassNames() : array {
        return [EbookDownload::class];
    }

}
