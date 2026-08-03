<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\InvalidProductPrice;
use App\VendingMachine\Domain\Exception\NegativeProductStock;

final class ProductSlot
{
    private int $stock = 0;

    private function __construct(
        private readonly ProductSelector $selector,
        private readonly string $name,
        private readonly Money $price,
    ) {
        if ($price->cents() <= 0) {
            throw new InvalidProductPrice($price->cents());
        }
    }

    public static function create(
        ProductSelector $selector,
        string $name,
        Money $price,
    ): self {
        return new self($selector, $name, $price);
    }

    public function selector(): ProductSelector
    {
        return $this->selector;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        if ($stock < 0) {
            throw new NegativeProductStock($stock);
        }

        $this->stock = $stock;
    }
}
