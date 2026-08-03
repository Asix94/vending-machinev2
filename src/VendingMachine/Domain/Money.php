<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\NegativeMoneyAmount;

final readonly class Money
{
    private function __construct(private int $cents)
    {
        if ($cents < 0) {
            throw new NegativeMoneyAmount($cents);
        }
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public function cents(): int
    {
        return $this->cents;
    }
}
