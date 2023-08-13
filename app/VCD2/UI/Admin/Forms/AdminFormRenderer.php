<?php

namespace VCD2\UI\Admin\Forms;

use Tomaj\Form\Renderer\BootstrapRenderer;

class AdminFormRenderer extends BootstrapRenderer
{
    public function __construct()
    {
        $this->wrappers['group']['container'] = 'fieldset class="list-group-item"';
    }
}
