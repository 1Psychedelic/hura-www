<?php

namespace VCD2\Applications;

use VCD2\Entity;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property string $name
 * @property string $iconUrl
 * @property int $position
 * @property string|NULL $gopayPaymentInstrument
 *
 *
 **** Příznaky
 * @property bool $isGopay
 * @property bool $isEnabled
 *
 *
 */
class PaymentMethod extends Entity {

    const ID_BANK_TRANSFER = 4;

}
