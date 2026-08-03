<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\InvalidCoinDenomination;

final readonly class Coin
{
    private const ACCEPTED_DENOMINATIONS = [5, 10, 25, 100];

    private function __construct(private int $cents)
    {
        if (!in_array($cents, self::ACCEPTED_DENOMINATIONS, true)) {
            throw new InvalidCoinDenomination($cents);
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
