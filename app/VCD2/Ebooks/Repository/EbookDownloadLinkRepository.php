<?php

namespace VCD2\Ebooks\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Ebooks\EbookDownloadLink;
use VCD2\Ebooks\Mapper\EbookDownloadLinkMapper;

/**
 * @method EbookDownloadLinkMapper getMapper()
 *
 * @method EbookDownloadLink|NULL get($primaryKey)
 * @method EbookDownloadLink|NULL getBy(array $conds)
 *
 * @method EbookDownloadLink[]|ICollection find($ids)
 * @method EbookDownloadLink[]|ICollection findAll()
 * @method EbookDownloadLink[]|ICollection findBy(array $where)
 *
 * @method EbookDownloadLink hydrateEntity(array $data)
*/
class EbookDownloadLinkRepository extends Repository {

    static function getEntityClassNames() : array {
        return [EbookDownloadLink::class];
    }

}
