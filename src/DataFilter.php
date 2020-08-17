<?php

declare(strict_types=1);

namespace Kuvardin\DataFilter;

use Error;
use Throwable;
use DateTime;
use function BA\scan_fields;

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
     */
    public static function getInt($var, bool $zero_to_null = false): ?int
    {
        $result = null;
        if (is_int($var)) {
            $result = $var;
        } elseif (is_string($var) && preg_match('/^(-|\+)?\d+$/', $var)) {
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
        return trim(preg_replace("/[  \t]+/u", ' ', $var));
    }

    /**
     * @param $var
     * @param bool $filter
     * @return string|null
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
     * @param array $data
     * @param array $known_keys
     */
    public static function searchUnknownFields(array &$data, array $known_keys): void
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

    /**
     * @param $var
     * @return DateTime|null
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
     * @param $var
     * @return float|null
     */
    public static function getFloat($var): ?float
    {
        if ($var === null) {
            return null;
        }

        if (is_int($var) || is_float($var) || (is_string($var) && is_numeric($var))) {
            return (float)$var;
        }

        $type = gettype($var);
        throw new Error("Unexpected var type: $type (expected float or string)");
    }

    /**
     * @param $var
     * @return float
     */
    public static function requireFloat($var): float
    {
        if (is_int($var) || is_float($var) || (is_string($var) && is_numeric($var))) {
            return (float)$var;
        }

        $type = gettype($var);
        throw new Error("Unexpected var type: $type (expected float or string)");
    }


    /**
     * @param array $fields
     * @param array $base
     * @return array
     */
    public static function scanFields(array $fields, array $base): array
    {
        foreach ($fields as $field_key => $field_value) {
            $field_type = gettype($field_value);
            if ($field_value === 0) {
                $field_type = 'zero';
            } elseif ($field_type === '0') {
                $field_type = 'zero_in_string';
                $field_value = 0;
            } elseif (is_string($field_value)) {
                if (preg_match('|^\d+$|', $field_value)) {
                    $field_type = 'integer_in_string';
                    if ((string)((int)$field_value) === $field_value) {
                        $field_value = (int)$field_value;
                    }
                } elseif (preg_match('|^(\d+)\.(\d+)$|', $field_value)) {
                    $field_type = 'double_in_string';
                } elseif ($field_value === '') {
                    $field_type = 'empty_string';
                }
            }

            if (!isset($base[$field_key]['types'][$field_type])) {
                $base[$field_key]['types'][$field_type] = 1;
            } else {
                $base[$field_key]['types'][$field_type]++;
            }

            if (is_array($field_value)) {
                $base[$field_key]['inners'] = self::scanFields($field_value, $base[$field_key]['inners'] ?? []);
            } else {
                if (!array_key_exists($field_type, $base[$field_key])) {
                    $base[$field_key][$field_type] = [];
                }

                switch ($field_type) {
                    case 'integer':
                    case 'double':
                    case 'integer_in_string':
                    case 'double_in_string':
                        if (!isset($base[$field_key][$field_type]['max_value'])) {
                            $base[$field_key][$field_type]['max_value'] = $field_value;
                        } elseif ($base[$field_key][$field_type]['max_value'] < $field_value) {
                            $base[$field_key][$field_type]['max_value'] = $field_value;
                        }

                        if (!isset($base[$field_key][$field_type]['min_value'])) {
                            $base[$field_key][$field_type]['min_value'] = $field_value;
                        } else if ($base[$field_key][$field_type]['min_value'] > $field_value) {
                            $base[$field_key][$field_type]['min_value'] = $field_value;
                        }
                        break;

                    case 'string':
                        $string_len = mb_strlen($field_value);

                        if (!isset($base[$field_key][$field_type]['max_length'])) {
                            $base[$field_key][$field_type]['max_length'] = $string_len;
                        } else if ($base[$field_key][$field_type]['max_length'] < $string_len) {
                            $base[$field_key][$field_type]['max_length'] = $string_len;
                        }

                        if (!isset($base[$field_key][$field_type]['min_length'])) {
                            $base[$field_key][$field_type]['min_length'] = $string_len;
                        } elseif ($base[$field_key][$field_type]['min_length'] > $string_len) {
                            $base[$field_key][$field_type]['min_length'] = $string_len;
                        }
                        break;
                }

                if (!isset($base[$field_key]['examples'][$field_type])) {
                    $base[$field_key]['examples'][$field_type] = [];
                }

                if ($field_value === null) {
                    $field_value_name = 'NULL';
                } elseif (is_bool($field_value)) {
                    $field_value_name = $field_value ? 'TRUE' : 'FALSE';
                } elseif (is_float($field_value)) {
                    $field_value_name = (string)$field_value;
                } elseif (!is_string($field_value) && !is_int($field_value)) {
                    $field_value_name = gettype($field_value) . ': ' . $field_value;
                } else {
                    $field_value_name = $field_value;
                }

                if (!array_key_exists($field_value_name, $base[$field_key]['examples'][$field_type])) {
                    if (count($base[$field_key]['examples'][$field_type]) < 10) {
                        $base[$field_key]['examples'][$field_type][$field_value_name] = 1;
                    }
                } else {
                    $base[$field_key]['examples'][$field_type][$field_value_name]++;
                }
            }
        }

        foreach ($base as $key => $value) {
            if (is_string($key)) {
                $key_parts = explode('.', $key);
                if (count($key_parts) > 1) {
                    array_pop($key_parts);
                    if ($key_parts !== []) {
                        $parent = implode('.', $key_parts);
                        $base[$key]['parent'] = $parent;
                        $another_not_exists = true;

                        foreach ($base as $field_key => $field_value) {
                            if ($field_key !== $key &&
                                ($field_key === $parent || mb_strpos($field_key, trim($parent, '.') . '.') === 0)) {
                                $another_not_exists = false;
                                break;
                            }
                        }

                        if (!array_key_exists($key, $fields)) {
                            $field_type = $another_not_exists
                                ? 'not_exists_if_another_not_exists'
                                : 'not_exists_with_another_exists';
                            if (!in_array($field_type, $base[$key]['types'], true)) {
                                $base[$key]['types'][] = $field_type;
                            }
                        }

                        continue;
                    }
                }
            }

            if (!array_key_exists($key, $fields)) {
                $field_type = 'not_exists';
                if (!in_array($field_type, $base[$key]['types'], true)) {
                    $base[$key]['types'][] = $field_type;
                }
            }
        }

        return $base;
    }
}
