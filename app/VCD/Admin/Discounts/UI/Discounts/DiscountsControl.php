<?php

namespace VCD\Admin\Discounts\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use VCD2\Discounts\DiscountCode;
use VCD2\Orm;

class DiscountsControl extends Control {

    private $container;

    private $expired;

    function __construct(ContainerInterface $container, $expired = FALSE) {
        $this->container = $container;
        $this->expired = $expired;
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $orm = $this->container->get(Orm::class);
        $this->template->discounts = $this->expired ? $orm->discountCodes->findAll() : $orm->discountCodes->findUsable();
        $this->template->expired = $this->expired;
        $this->template->render();
    }
    
    function handleRecalculateUsagesLeft($id) {
        $orm = $this->container->get(Orm::class);
        /** @var DiscountCode|NULL $discount */
        $discount = $orm->discountCodes->get($id);
        if($discount !== NULL) {
            $discount->recalculateUsagesLeft();
            $orm->persistAndFlush($discount);
            $this->presenter->flashMessage('Přepočítáno.', 'success');
        }
        $this->presenter->redirect('this');
    }

}
