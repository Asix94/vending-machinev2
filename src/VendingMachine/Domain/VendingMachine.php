<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\DuplicateProductSelector;
use App\VendingMachine\Domain\Exception\EmptyProductCatalog;
use App\VendingMachine\Domain\Exception\InsufficientBalance;
use App\VendingMachine\Domain\Exception\ProductNotFound;
use App\VendingMachine\Domain\Exception\ServiceUnavailableDuringOperation;
use App\VendingMachine\Domain\Service\ExactChangeCalculator;

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

    public function setProductStock(
        ProductSelector $selector,
        int $quantity,
    ): void {
        $this->ensureServiceIsAvailable();

        $this->findProduct($selector)->setStock($quantity);
    }

    public function productStock(ProductSelector $selector): int
    {
        return $this->findProduct($selector)->stock();
    }

    private function findProduct(
        ProductSelector $selector,
    ): ProductSlot {
        $product = $this->products[$selector->value()] ?? null;

        if ($product === null) {
            throw new ProductNotFound($selector);
        }

        return $product;
    }

    public function setCoinReserveQuantity(
        Coin $coin,
        int $quantity,
    ): void {
        $this->ensureServiceIsAvailable();

        $this->coinReserve = $this->coinReserve
            ->withQuantity($coin, $quantity);
    }

    private function ensureServiceIsAvailable(): void
    {
        if ($this->insertedCoins !== []) {
            throw new ServiceUnavailableDuringOperation();
        }
    }

    public function buyProduct(
        ProductSelector $selector,
        ExactChangeCalculator $changeCalculator,
    ): PurchaseResult {
        $product = $this->findProduct($selector);
        $product->ensureAvailable();

        $balance = $this->insertedBalance();

        if ($balance->isLessThan($product->price())) {
            throw new InsufficientBalance(
                $balance,
                $product->price(),
            );
        }

        $changeAmount = $balance->subtract($product->price());

        $change = $changeCalculator->calculate(
            $changeAmount,
            $this->coinReserve,
        );

        $updatedReserve = $this->coinReserve
            ->withoutCoins(...$change)
            ->withAddedCoins(...$this->insertedCoins);

        $result = PurchaseResult::create(
            $product->selector(),
            ...$change,
        );

        $product->dispenseOne();
        $this->coinReserve = $updatedReserve;
        $this->insertedCoins = [];

        return $result;
    }
}
