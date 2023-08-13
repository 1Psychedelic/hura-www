<?php

namespace VCD\UI\FrontModule\WebModule;

use Hafo\NetteBridge\UI\HtmlControl;
use Nette\Application\BadRequestException;
use Nette\Database\Context;
use Nette\Http\IResponse;

class PagePresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Web:Page:default';

    function actionDefault($id) {
        if($id === 'e-booky-pro-deti-ke-stazeni-zdarma') {
            $this->redirect(IResponse::S301_MOVED_PERMANENTLY, EbooksPresenter::LINK_DEFAULT);
            return;
        }

        $db = $this->container->get(Context::class);
        $row = $db->table('vcd_page')->where('slug = ? AND special = 0', $id)->fetch();
        if(!$row) {
            throw new BadRequestException;
        }
        if($row['keywords']) {
            $this->template->keywords = $row['keywords'];
        }
        $this->addComponent(new HtmlControl($row['content']), 'page');
        $this->template->titlePrefix = $row['name'];
    }

}
