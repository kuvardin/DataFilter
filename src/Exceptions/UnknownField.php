<?php

declare(strict_types=1);

namespace Kuvardin\DataFilter\Exceptions;

use Throwable;

class UnknownField extends DataFilterException
{
    protected string $field_type;

    public function __construct(
        protected string $field_name,
        protected mixed $field_value,
        ?Throwable $previous = null,
    )
    {
        $this->field_type = is_object($this->field_value)
            ? get_class($this->field_value)
            : gettype($this->field_value);

        $value_string = print_r($this->field_value, true);

        $message = sprintf(
            'Unknown field named %s typed %s with value: %s',
            $this->field_name,
            gettype($this->field_value),
            mb_strlen($value_string) > 200
                ? (rtrim(mb_substr($value_string, 0, 200)) . '...')
                : $value_string,
        );

        parent::__construct($message, 0, $previous);
    }

    public function getFieldName(): string
    {
        return $this->field_name;
    }

    public function getFieldType(): string
    {
        return $this->field_type;
    }

    public function getFieldValue(): mixed
    {
        return $this->field_value;
    }
}