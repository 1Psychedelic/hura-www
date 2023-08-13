<?php

namespace VCD\UI\FrontModule\GalleryModule;

class PhotosPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Gallery:Photos:default';

    function actionDefault($id = NULL, $photo = NULL) {
        $this->addComponent($this->container->get(GalleryControlFactory::class)->create($id, $photo), 'gallery');
        $this->template->titlePrefix = 'Fotky';
    }

}
