<?php
declare(strict_types=1);

namespace VCD\Admin\Applications\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Form;
use VCD\UI\BaseControl;
use VCD2\Orm;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

class ApplicationEventControl extends BaseControl
{
    /** @var Container */
    private $container;

    /** @var Orm */
    private $orm;

    public function __construct(Container $container, int $id, ?int $selectedEventId = null)
    {
        $this->container = $container;
        $this->orm = $this->container->get(Orm::class);

        $this->onAnchor[] = function () use ($id, $selectedEventId) {
            $application = $this->orm->applications->get($id);

            if ($selectedEventId === null) {
                $f = new Form;
                $f->setRenderer(new AdminFormRenderer);
            }
        };
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }
}
