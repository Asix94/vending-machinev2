<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain;

final readonly class PurchaseResult
{
    /**
     * @param list<Coin> $change
     */
    private function __construct(
        private ProductSelector $productSelector,
        private array $change,
    ) {
    }

    public static function create(
        ProductSelector $productSelector,
        Coin ...$change,
    ): self {
        return new self($productSelector, $change);
    }

    public function productSelector(): ProductSelector
    {
        return $this->productSelector;
    }

    /**
     * @return list<Coin>
     */
    public function change(): array
    {
        return $this->change;
    }
}
