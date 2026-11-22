<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class Validation
{
    public static function text(mixed $value, string $field, int $max): string
    {
        if (!is_string($value) || trim($value) === '' || preg_match('/^.{1,' . $max . '}$/usD', trim($value)) !== 1) {
            throw new InvalidArgumentException("$field must be nonempty text of at most $max characters.");
        }

        return trim($value);
    }

    public static function optionalText(mixed $value, string $field, int $max): ?string
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return null;
        }

        return self::text($value, $field, $max);
    }

    public static function integer(mixed $value, string $field, int $min = 1, int $max = 4294967295): int
    {
        $number = (is_int($value) || is_string($value)) ? filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $min, 'max_range' => $max],
        ]) : false;
        if ($number === false) {
            throw new InvalidArgumentException("$field must be an integer between $min and $max.");
        }

        return $number;
    }

    public static function price(mixed $value): string
    {
        if ((!is_string($value) && !is_int($value) && !is_float($value))
            || preg_match('/^[0-9]{1,8}(?:\.[0-9]{1,2})?$/D', (string) $value) !== 1) {
            throw new InvalidArgumentException('Price must be between 0 and 99999999.99 with at most two decimal places.');
        }

        return (string) $value;
    }
}
