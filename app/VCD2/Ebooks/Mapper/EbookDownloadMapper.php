<?php

namespace VCD2\Ebooks\Mapper;

use Hafo\Orm\Mapper\Mapper;

class EbookDownloadMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_ebook_download';
    }

}
