<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Event\Transformer;


use DateTimeImmutable;

class EventTermTransformer
{
    public function transform(DateTimeImmutable $starts, DateTimeImmutable $ends, bool $monthAsNumber): string
    {
        if ($monthAsNumber) {
            $format = 'j.';
            if ($starts->format('n') !== $ends->format('n')) {
                $format .= ' n.';
            }
            if ($starts->format('Y') !== $ends->format('Y')) {
                $format .= ' Y';
            }
            if ($starts->format('j n Y') !== $ends->format('j n Y')) {
                return $starts->format($format) . ' – ' . $ends->format('j. n. Y');
            }
            return $ends->format('j. n. Y');
        }

        $format = '%e.';
        if ($starts->format('n') !== $ends->format('n')) {
            $format .= ' %B';
        }
        if ($starts->format('Y') !== $ends->format('Y')) {
            $format .= ' %Y';
        }
        if ($starts->format('j n Y') !== $ends->format('j n Y')) {
            return $starts->format($format) . ' – ' . $ends->format('%e. %B %Y');
        }
        return $ends->format('%e. %B %Y');
    }
}
