<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\InsufficientCoinQuantity;
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

    public function withAddedCoins(Coin ...$coins): self
    {
        $quantities = $this->quantities;

        foreach ($coins as $coin) {
            $cents = $coin->cents();
            $quantities[$cents] = ($quantities[$cents] ?? 0) + 1;
        }

        return new self($quantities);
    }

    public function withoutCoins(Coin ...$coins): self
    {
        $requiredQuantities = [];

        foreach ($coins as $coin) {
            $cents = $coin->cents();
            $requiredQuantities[$cents] =
                ($requiredQuantities[$cents] ?? 0) + 1;
        }

        foreach ($requiredQuantities as $cents => $required) {
            $available = $this->quantities[$cents] ?? 0;

            if ($required > $available) {
                throw new InsufficientCoinQuantity(
                    Coin::fromCents($cents),
                    $available,
                    $required,
                );
            }
        }

        $quantities = $this->quantities;

        foreach ($requiredQuantities as $cents => $required) {
            $quantities[$cents] -= $required;
        }

        return new self($quantities);
    }

    /**
     * @return list<Coin>
     */
    public function availableDenominations(): array
    {
        $denominations = [];

        foreach ($this->quantities as $cents => $quantity) {
            if ($quantity > 0) {
                $denominations[] = Coin::fromCents($cents);
            }
        }

        return $denominations;
    }
}
