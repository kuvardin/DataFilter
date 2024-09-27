<?php

declare(strict_types=1);

namespace Kuvardin\DataFilter;

use DateTimeImmutable;
use Exception;
use Kuvardin\DataFilter\Exceptions\WrongType;
use Kuvardin\DataFilter\Exceptions\UnknownField;
use DateTimeZone;

/**
 * Class DataFilter
 *
 * @package Kuvardin\DataFilter
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class DataFilter
{
    private function __construct()
    {
    }

    /**
     * @throws WrongType
     */
    public static function requireInt(
        mixed $var,
        bool $non_zero = false,
        bool $positive = null,
    ): int
    {
        $result = self::getInt($var, $non_zero, $positive);

        if ($result === null) {
            throw new WrongType($non_zero ? 'non-zero integer' : 'integer', $var);
        }

        return $result;
    }

    public static function getInt(
        mixed $var,
        bool $zero_to_null = false,
        bool $positive = null,
    ): ?int
    {
        if ($var === null) {
            return null;
        }

        $result = null;

        if (is_int($var)) {
            $result = $var;
        } elseif (is_string($var)) {
            $var_int = (int)$var;
            if ((string)$var_int === $var) {
                $result = $var_int;
            }
        } elseif (is_float($var)) {
            $var_int = (int)$var;
            if ($var_int == $var) {
                $result = $var_int;
            }
        }

        if ($result === null) {
            throw new WrongType('integer', $var);
        }

        if ($positive !== null && ($result >= 0) !== $positive) {
            throw new WrongType('positive integer', $var);
        }

        return $zero_to_null && $result === 0 ? null : $result;
    }

    /**
     * @throws WrongType
     */
    public static function requireIntZeroToNull(mixed $var, bool $positive = null): ?int
    {
        $result = self::getInt($var, false, $positive);

        if ($result === null) {
            throw new WrongType('integer', $var);
        }

        return $result === 0 ? null : $result;
    }

    /**
     * @throws WrongType
     */
    public static function getBoolByValues(
        mixed $var,
        mixed $true_value,
        mixed $false_value,
    ): ?bool
    {
        if (is_bool($var)) {
            return $var;
        }

        if ($var !== $true_value && $var !== $false_value) {
            if ($var === null) {
                return null;
            }

            throw new WrongType('boolean by values', $var);
        }

        return $var === $true_value;
    }

    /**
     * @throws WrongType
     */
    public static function requireBoolByValues(
        mixed &$var,
        mixed $true_value,
        mixed $false_value,
    ): bool
    {
        if (is_bool($var)) {
            return $var;
        }

        if ($var !== $true_value && $var !== $false_value) {
            throw new WrongType('boolean by values', $var);
        }

        return $var === $true_value;
    }

    /**
     * @throws WrongType
     */
    public static function getBool(mixed &$var): ?bool
    {
        if ($var === null) {
            return null;
        }

        return self::requireBool($var);
    }

    /**
     * @throws WrongType
     */
    public static function requireBool(mixed &$var): bool
    {
        if (is_bool($var)) {
            return $var;
        }

        throw new WrongType('boolean', $var);
    }

    /**
     * @throws WrongType
     */
    public static function getString(
        mixed $var,
        bool $filter = false,
        bool $empty_to_null = false,
    ): ?string
    {
        if ($var === null) {
            return null;
        }

        if (is_string($var)) {
            $result = $filter ? self::filterString($var) : $var;
            return $empty_to_null && $result === '' ? null : $result;
        }

        throw new WrongType('string or null', $var);
    }

    /**
     * @param string $var
     * @return string
     */
    public static function filterString(string $var): string
    {
        return trim(preg_replace("/[  \t]+/u", ' ', $var));
    }

    /**
     * @deprecated Use ::getString() instead
     * @throws WrongType
     */
    public static function getStringEmptyToNull(
        mixed $var,
        bool $filter = false,
    ): ?string
    {
        return self::getString($var, $filter, true);
    }

    /**
     * @throws WrongType
     */
    public static function requireString(
        mixed $var,
        bool $filter = false,
        bool $not_empty = false,
    ): string
    {
        if (is_string($var)) {
            $result = $filter ? self::filterString($var) : $var;
            if (!$not_empty || $result !== '') {
                return $result;
            }
        }

        throw new WrongType($not_empty ? 'not empty string' : 'string', $var);
    }

    /**
     * @deprecated Use ::requireString() instead
     * @throws WrongType
     */
    public static function requireNotEmptyString(
        mixed $var,
        bool $filter = false,
    ): string
    {
        return self::requireString($var, $filter, true);
    }

    /**
     * @throws WrongType
     * @deprecated
     */
    public static function requireStringEmptyToNull(
        mixed $var,
        bool $filter = false,
    ): ?string
    {
        $result = self::requireString($var, $filter);
        return $result === '' ? null : $result;
    }

    /**
     * @throws WrongType
     */
    public static function requireDateTime(
        mixed $var,
        string $format = null,
        DateTimeZone $timezone = null,
    ): DateTimeImmutable
    {
        $result = null;

        try {
            if (is_string($var)) {
                if ($var !== '' && $var !== '0' && $var !== 'now') {
                    $result = $format === null
                        ? new DateTimeImmutable($var, $timezone)
                        : DateTimeImmutable::createFromFormat($format, $var);
                }
            } elseif ($format === null && is_int($var)) {
                $result = new DateTimeImmutable('@' . $var, $timezone);
            }
        } catch (Exception) {

        }

        if ($result === null) {
            throw new WrongType($format === null ? 'datetime string' : 'datetime (int or string)', $var);
        }

        if ($timezone !== null) {
            $result->setTimezone($timezone);
        }

        return $result;
    }

    /**
     * @throws WrongType
     */
    public static function getDateTime(
        mixed $var,
        string $format = null,
        DateTimeZone $timezone = null,
    ): ?DateTimeImmutable
    {
        if ($var === null || $var === '' || $var === '0' || $var === 0) {
            return null;
        }

        return self::requireDateTime($var, $format, $timezone);
    }

    /**
     * @throws WrongType
     */
    public static function getFloat(
        mixed $var,
        bool $positive = null,
    ): ?float
    {
        if ($var === null) {
            return null;
        }

        return self::requireFloat($var, $positive);
    }

    /**
     * @throws WrongType
     */
    public static function requireFloat(
        mixed $var,
        bool $positive = null,
    ): float
    {
        if (is_int($var) || is_float($var) || (is_string($var) && is_numeric($var))) {
            $result = (float)$var;

            if ($positive !== null && ($result >= 0) !== $positive) {
                throw new WrongType($positive ? 'positive float' : 'negative float', $var);
            }

            return $result;
        }

        throw new WrongType('float', $var);
    }

    /**
     * @throws UnknownField
     */
    public static function searchUnknownFields(array &$data, array $known_keys): void
    {
        $exception = null;
        $keys = array_keys($data);
        $unknown_keys = array_diff($keys, $known_keys);

        if ($unknown_keys !== []) {

            foreach ($unknown_keys as $unknown_key) {
                $exception = new UnknownField($unknown_key, $data[$unknown_key], $exception);
            }

            throw $exception;
        }
    }
}
