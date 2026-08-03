<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\NegativeCoinQuantity;

final readonly class CoinReserve
{
    /**
     * @param array<int, int> $quantities
     */
    private function __construct(private array $quantities)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function quantityOf(Coin $coin): int
    {
        return $this->quantities[$coin->cents()] ?? 0;
    }

    public function withQuantity(Coin $coin, int $quantity): self
    {
        if ($quantity < 0) {
            throw new NegativeCoinQuantity($quantity);
        }

        $quantities = $this->quantities;
        $quantities[$coin->cents()] = $quantity;

        return new self($quantities);
    }
}
