<?php

declare(strict_types=1);

namespace App\VendingMachine\Application\BuyProduct;

final readonly class BuyProductResult
{
    /**
     * @param list<int> $change
     */
    public function __construct(
        private string $productSelector,
        private array $change,
    ) {
    }

    public function productSelector(): string
    {
        return $this->productSelector;
    }

    /**
     * @return list<int>
     */
    public function change(): array
    {
        return $this->change;
    }
}
