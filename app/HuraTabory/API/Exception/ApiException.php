<?php
declare(strict_types=1);

namespace HuraTabory\API\Exception;

use RuntimeException;
use Throwable;

class ApiException extends RuntimeException
{
    /** @var string */
    private $userMessage;

    public function __construct(string $message = '', int $code = 0, Throwable $previous = null, string $userMessage = '')
    {
        parent::__construct($message, $code, $previous);

        $this->userMessage = $userMessage;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }
}
