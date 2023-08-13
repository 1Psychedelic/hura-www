<?php

namespace VCD2\Discounts;

use VCD2\FlashMessageException;

class DiscountCodeException extends FlashMessageException {}

class DiscountCodeRejectedException extends DiscountCodeException {}

class DiscountCodeExpiredException extends DiscountCodeRejectedException {}

class DiscountException extends FlashMessageException {}

class DiscountRejectedException extends DiscountException {}

class DiscountExpiredException extends DiscountRejectedException {}