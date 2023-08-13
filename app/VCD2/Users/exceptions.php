<?php

namespace VCD2\Users;

use VCD2\FlashMessageException;

class InsufficientCreditException extends FlashMessageException {}

class DuplicateChildException extends FlashMessageException {}

class InvalidChildException extends FlashMessageException {}
