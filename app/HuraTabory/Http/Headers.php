<?php
declare(strict_types=1);

namespace HuraTabory\Http;

class Headers
{
    /** @var mixed[] */
    private $headers;

    /**
     * @param mixed[] $headers
     */
    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    public function withoutCache(): self
    {
        $headers = $this->toArray();
        $headers['Cache-Control'] = 'no-cache, no-store, must-revalidate, max-age=0';
        $headers['Pragma'] = 'no-cache';
        $headers['Expires'] = 0;

        return new self($headers);
    }

    public function withCookie(string $template, string $value): self
    {
        $headers = $this->toArray();
        $headers['Set-Cookie'] = sprintf($template, $value);

        return new self($headers);
    }

    public function withoutCookie(string $template): self
    {
        $headers = $this->toArray();
        $headers['Set-Cookie'] = sprintf($template, '') . '; expires=Thu, 01 Jan 1970 00:00:00 GMT';

        return new self($headers);
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->headers;
    }
}
