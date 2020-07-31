<?php

declare(strict_types=1);

namespace Kuvardin\DataFilter;

use Error;
use Throwable;
use DateTime;

/**
 * Class DataFilter
 *
 * @package Kuvardin\DataFilter
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class DataFilter
{
    /**
     * Filter constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param $var
     * @return int
     * @throws Error
     */
    public static function requireInt($var): int
    {
        $result = self::getInt($var);
        if ($result === null) {
            $type = gettype($var);
            throw new Error("Unexpected var type: $type (expected int)");
        }
        return $result;
    }

    /**
     * @param $var
     * @param bool $zero_to_null
     * @return int|null
     * @throws Error
     */
    public static function getInt($var, bool $zero_to_null = false): ?int
    {
        $result = null;
        if (is_int($var)) {
            $result = $var;
        } elseif (is_string($var) && preg_match('/^\d+$/', $var)) {
            $result = (int)$var;
        } else {
            $type = gettype($var);
            throw new Error("Unexpected var type: $type (expected int or numeric string)");
        }

        if ($result !== null && !($zero_to_null && $result === 0)) {
            return $result;
        }

        return null;
    }

    /**
     * @param $var
     * @return int|null
     * @throws Error
     */
    public static function requireIntZeroToNull($var): ?int
    {
        $result = self::getInt($var);
        if ($result === null) {
            $type = gettype($var);
            throw new Error("Unexpected var type: $type (expected int)");
        }
        return $result === 0 ? null : $result;
    }

    /**
     * @param $var
     * @param $true_value
     * @param $false_value
     * @return bool|null
     * @throws Error
     */
    public static function getBoolByValues($var, $true_value, $false_value): ?bool
    {
        if ($var !== $true_value && $var !== $false_value) {
            if ($var === null) {
                return null;
            }

            throw new Error("Unknown value: $var (must be $true_value or $false_value)");
        }

        return $var === $true_value;
    }

    /**
     * @param $var
     * @param $true_value
     * @param $false_value
     * @return bool
     * @throws Error
     */
    public static function requireBoolByValues($var, $true_value, $false_value): bool
    {
        if ($var !== $true_value && $var !== $false_value) {
            throw new Error("Unknown value: $var (must be $true_value or $false_value)");
        }

        return $var === $true_value;
    }

    /**
     * @param $var
     * @return bool|null
     * @throws Error
     */
    public static function getBool($var): ?bool
    {
        if ($var === null) {
            return null;
        }

        if (is_bool($var)) {
            return $var;
        }

        $type = gettype($var);
        throw new Error("Unexpected var type: $type (expected bool)");
    }

    /**
     * @param $var
     * @return bool
     * @throws Error
     */
    public static function requireBool($var): bool
    {
        if (is_bool($var)) {
            return $var;
        }

        $type = gettype($var);
        throw new Error("Unexpected var type: $type (expected bool)");
    }

    /**
     * @param $var
     * @param bool $filter
     * @return string|null
     * @throws Error
     */
    public static function getString($var, bool $filter = false): ?string
    {
        if ($var === null) {
            return null;
        }

        if (is_string($var)) {
            return $filter ? self::filterString($var) : $var;
        }

        $type = gettype($var);
        throw new Error("Unexpected var type: $type (expected string)");
    }

    /**
     * @param string $var
     * @return string
     */
    public static function filterString(string $var): string
    {
        return trim(preg_replace("/[   \t]+/u", ' ', $var));
    }

    /**
     * @param $var
     * @param bool $filter
     * @return string|null
     * @throws Error
     */
    public static function getStringEmptyToNull($var, bool $filter = false): ?string
    {
        if ($var === null) {
            return null;
        }

        if (is_string($var)) {
            if ($filter) {
                $var = self::filterString($var);
            }

            return $var === '' ? null : $var;
        }

        $type = gettype($var);
        throw new Error("Unexpected var type: $type (expected string)");
    }

    /**
     * @param $var
     * @param bool $filter
     * @return string
     * @throws Error
     */
    public static function requireString($var, bool $filter = false): string
    {
        if (is_string($var)) {
            return $filter ? self::filterString($var) : $var;
        }

        $type = gettype($var);
        throw new Error("Unexpected var type: $type (expected string)");
    }

    /**
     * @param $var
     * @param bool $filter
     * @return string
     * @throws Error
     */
    public static function requireNotEmptyString($var, bool $filter = false): string
    {
        if (is_string($var)) {
            $var = $filter ? self::filterString($var) : $var;
            if ($var === '') {
                throw new Error('The string is empty');
            }
            return $var;
        }

        $type = gettype($var);
        throw new Error("Unexpected var type: $type (expected string)");
    }

    /**
     * @param $var
     * @param bool $filter
     * @return string|null
     * @throws Error
     */
    public static function requireStringEmptyToNull($var, bool $filter = false): ?string
    {
        if (is_string($var)) {
            if ($filter) {
                $var = self::filterString($var);
            }
            return $var === '' ? null : $var;
        }

        $type = gettype($var);
        throw new Error("Unexpected var type: $type (expected string)");
    }

    /**
     * @param $var
     * @return DateTime
     */
    public static function requireDateTime($var): DateTime
    {
        try {
            if (is_string($var)) {
                return new DateTime($var);
            }

            if (is_int($var)) {
                return new DateTime('@' . $var);
            }
        } catch (Throwable $e) {
            throw new Error($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        $type = gettype($var);
        throw new Error("Unexpected var type: $type (expected int or string)");
    }

    /**
     * @param $var
     * @return DateTime|null
     * @throws Error
     */
    public static function getDateTime($var): ?DateTime
    {
        if ($var === null || $var === '' || $var === '0' || $var === 0) {
            return null;
        }

        try {
            return new DateTime($var);
        } catch (Throwable $e) {
            throw new Error($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param array $data
     * @param array $known_keys
     */
    public function searchUnknownFields(array &$data, array $known_keys): void
    {
        $exception = null;
        $keys = array_keys($data);
        $unknown_keys = array_diff($keys, $known_keys);
        if ($unknown_keys !== []) {
            foreach ($unknown_keys as $unknown_key) {
                $type = gettype($data[$unknown_key]);
                $message = "Unknown field $unknown_key typed $type with value " . print_r($data[$unknown_key], true);
                $exception = new Error($message, 0, $exception);
            }
            throw $exception;
        }
    }
}
