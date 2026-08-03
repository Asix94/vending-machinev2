<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\DuplicateProductSelector;
use App\VendingMachine\Domain\Exception\EmptyProductCatalog;

final class VendingMachine
{
    /** @var list<Coin> */
    private array $insertedCoins = [];

    private CoinReserve $coinReserve;

    /**
     * @param array<string, ProductSlot> $products
     */
    private function __construct(private readonly array $products)
    {
        $this->coinReserve = CoinReserve::empty();
    }

    public static function create(ProductSlot ...$products): self
    {
        if ($products === []) {
            throw new EmptyProductCatalog();
        }

        $productsBySelector = [];

        foreach ($products as $product) {
            $selector = $product->selector();
            $key = $selector->value();

            if (isset($productsBySelector[$key])) {
                throw new DuplicateProductSelector($selector);
            }

            $productsBySelector[$key] = $product;
        }

        return new self($productsBySelector);
    }

    public function insertedBalance(): Money
    {
        $cents = 0;

        foreach ($this->insertedCoins as $coin) {
            $cents += $coin->cents();
        }

        return Money::fromCents($cents);
    }

    public function coinReserveQuantity(Coin $coin): int
    {
        return $this->coinReserve->quantityOf($coin);
    }

    public function insertCoin(Coin $coin): void
    {
        $this->insertedCoins[] = $coin;
    }

    /**
     * @return list<Coin>
     */
    public function returnInsertedCoins(): array
    {
        $coins = $this->insertedCoins;
        $this->insertedCoins = [];

        return $coins;
    }
}
