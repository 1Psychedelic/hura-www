<?php

namespace VCD2\Ebooks\Mapper;

use Hafo\Orm\Mapper\Mapper;

class EbookDownloadLinkMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_ebook_download_link';
    }

}
