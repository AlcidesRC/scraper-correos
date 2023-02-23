<?php

declare(strict_types=1);

namespace App\Misc;

use Closure;

final class IntRange
{
    public readonly int $max;
    public readonly int $min;

    public function __construct(int $min, int $max)
    {
        if ($min > $max) {
            $aux = $max;
            $max = $min;
            $min = $aux;
            unset($aux);
        }

        $this->min = $min;
        $this->max = $max;
    }

    public function isInRange(int $value): bool
    {
        return $this->min <= $value && $value <= $this->max;
    }

    /**
     * @return array<int>
     */
    public function values(): array
    {
        return array_map(static function ($value) {
            return $value;
        }, range($this->min, $this->max));
    }

    /**
     * @return array<int>
     */
    public function each(Closure $closure): array
    {
        return array_map(static function ($value) use ($closure) {
            return $closure($value);
        }, range($this->min, $this->max));
    }
}
