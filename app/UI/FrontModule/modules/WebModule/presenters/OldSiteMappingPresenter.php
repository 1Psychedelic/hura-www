<?php

namespace VCD\UI\FrontModule\WebModule;

use Nette\Application\BadRequestException;
use Nette\Database\Context;
use Nette\Http\IResponse;
use VCD\UI\FrontModule\EventsModule\EventPresenter;
use VCD\UI\FrontModule\GalleryModule\PhotosPresenter;

class OldSiteMappingPresenter extends BasePresenter {

    function actionDefault($page, $id = NULL) {
        $action = NULL;
        $args = [];
        $data = [];
        if($page === 'vylet') {
            $data = [
                '1' => 8,
                '2' => 9,
                '3' => 10,
                '4' => 11,
                '6' => 12,
                '9' => 13,
                '10' => 14,
                '11' => 15,
                '13' => 17,
                '14' => 18,
                '15' => 19,
                '17' => 21,
                '18' => 22,
                '21' => 24
            ];
            $action = EventPresenter::LINK_DEFAULT;
        } else if($page === 'tabor') {
            $data = [
                '1' => 1,
                '5' => 4,
                '6' => 5,
                '7' => 26,
                '8' => 7
            ];
            $action = EventPresenter::LINK_DEFAULT;
        } else if($page === 'fotky') {
            $data = [
                '2' => 8,
                '3' => 9,
                '5' => 10,
                '7' => 1,
                '8' => 1,
                '9' => 12,
                '10' => 14,
                '11' => 15,
                '12' => 17,
                '13' => 18,
                '15' => 19,
                '17' => 5,
                '18' => 4,
                '19' => 4,
                '20' => 21,
                '21' => 22,
                '22' => 23,
                '23' => 24,
                '24' => 26
            ];
            $action = PhotosPresenter::LINK_DEFAULT;
            $args['type'] = 0;
        }

        if($action !== NULL && isset($data[$id])) {
            $db = $this->container->get(Context::class);
            /** @var Context $db */
            $slug = $db->table('vcd_event')->wherePrimary($data[$id])->select('slug')->fetchField();
            if($slug) {
                $this->redirect(IResponse::S301_MOVED_PERMANENTLY, $action, array_merge($args, ['id' => $slug]));
                return;
            }
        }
        throw new BadRequestException;
    }

}
