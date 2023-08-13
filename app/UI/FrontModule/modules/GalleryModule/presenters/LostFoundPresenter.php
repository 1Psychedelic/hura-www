<?php

namespace VCD\UI\FrontModule\GalleryModule;

class LostFoundPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Gallery:LostFound:default';

    function actionDefault($id = NULL, $photo = NULL) {
        $this->addComponent($this->container->get(LostFoundControlFactory::class)->create($id, $photo), 'gallery');
        $this->template->titlePrefix = 'Ztráty a nálezy';
    }

}
