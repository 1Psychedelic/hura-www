<?php
declare(strict_types=1);

namespace Hafo\Google\Analytics;

class Event
{
    /** @var string */
    private $category;

    /** @var string */
    private $action;

    /** @var string|null */
    private $label;

    /** @var int|null */
    private $value;

    public function __construct(string $category, string $action, ?string $label, ?int $value)
    {
        $this->category = $category;
        $this->action = $action;
        $this->label = $label;
        $this->value = $value;
    }

    public function getHtml(): string
    {
        $values = array_filter([
            $this->category,
            $this->action,
            $this->label,
            $this->value,
        ], function ($val) {
            return $val !== null;
        });

        $quoted = array_map(function ($val) {
            if (is_int($val)) {
                return $val;
            }

            return "'{$val}'";
        }, $values);

        $imploded = implode(',', $quoted);

        return "ga('send','event',{$imploded});";
    }
}
