<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\EmptyProductSelector;

final readonly class ProductSelector
{
    private function __construct(private string $value)
    {
        if ($value === '') {
            throw new EmptyProductSelector();
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
