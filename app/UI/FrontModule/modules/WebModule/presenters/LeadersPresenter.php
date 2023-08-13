<?php

namespace VCD\UI\FrontModule\WebModule;

use Nette\Database\Context;

class LeadersPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Web:Leaders:default';

    function actionDefault() {
        $db = $this->container->get(Context::class);
        $this->template->leaders = $db->table('vcd_leader')->where('visible = 1')->order('position ASC');
        $this->template->titlePrefix = 'Vedoucí a lektoři';
    }

}
