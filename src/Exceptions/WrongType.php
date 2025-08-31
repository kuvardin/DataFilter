<?php

declare(strict_types=1);

namespace Kuvardin\DataFilter\Exceptions;

use Throwable;

class WrongType extends DataFilterException
{
    protected string $received_type;

    public function __construct(
        protected string $expected_type,
        protected mixed $received_value,
        ?Throwable $previous = null,
    )
    {
        $this->received_type = is_object($this->received_value)
            ? get_class($this->received_value)
            : gettype($this->received_value);

        $value_string = print_r($received_value, true);

        $message = sprintf(
            "Expected value typed %s but received %s with value: %s",
            $expected_type,
            $this->received_type,
            mb_strlen($value_string) > 200
                ? (rtrim(mb_substr($value_string, 0, 200)) . '...')
                : $value_string,
        );

        parent::__construct($message, 0, $previous);
    }

    public function getReceivedType(): string
    {
        return $this->received_type;
    }

    public function getExpectedType(): string
    {
        return $this->expected_type;
    }

    public function getReceivedValue(): mixed
    {
        return $this->received_value;
    }
}