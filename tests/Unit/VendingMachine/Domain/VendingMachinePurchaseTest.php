<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Exception\ExactChangeUnavailable;
use App\VendingMachine\Domain\Exception\InsufficientBalance;
use App\VendingMachine\Domain\Exception\ProductNotFound;
use App\VendingMachine\Domain\Exception\ProductOutOfStock;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\ProductSlot;
use App\VendingMachine\Domain\Service\ExactChangeCalculator;
use App\VendingMachine\Domain\VendingMachine;
use PHPUnit\Framework\TestCase;

final class VendingMachinePurchaseTest extends TestCase
{
    public function testItPurchasesAProductWithTheExactInsertedAmount(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $machine = VendingMachine::create(
            ProductSlot::create(
                $selector,
                'Water',
                Money::fromCents(100),
            ),
        );

        $machine->setProductStock($selector, 1);
        $machine->insertCoin(Coin::fromCents(100));

        $result = $machine->buyProduct(
            $selector,
            new ExactChangeCalculator(),
        );

        self::assertSame($selector, $result->productSelector());
        self::assertSame([], $result->change());
        self::assertSame(0, $machine->productStock($selector));
        self::assertSame(0, $machine->insertedBalance()->cents());
        self::assertSame(
            1,
            $machine->coinReserveQuantity(Coin::fromCents(100)),
        );
    }

    public function testItPreservesStateWhenBalanceIsInsufficient(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $coin = Coin::fromCents(25);

        $machine = VendingMachine::create(
            ProductSlot::create(
                $selector,
                'Water',
                Money::fromCents(100),
            ),
        );

        $machine->setProductStock($selector, 2);
        $machine->setCoinReserveQuantity($coin, 3);
        $machine->insertCoin($coin);

        try {
            $machine->buyProduct(
                $selector,
                new ExactChangeCalculator(),
            );

            self::fail('Expected InsufficientBalance to be thrown.');
        } catch (InsufficientBalance) {
            self::assertSame(2, $machine->productStock($selector));
            self::assertSame(25, $machine->insertedBalance()->cents());
            self::assertSame(
                3,
                $machine->coinReserveQuantity($coin),
            );
        }
    }

    public function testItDoesNotUseInsertedCoinsAsChange(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $coin = Coin::fromCents(25);

        $machine = VendingMachine::create(
            ProductSlot::create(
                $selector,
                'Water',
                Money::fromCents(25),
            ),
        );

        $machine->setProductStock($selector, 1);
        $machine->insertCoin($coin);
        $machine->insertCoin($coin);

        try {
            $machine->buyProduct(
                $selector,
                new ExactChangeCalculator(),
            );

            self::fail('Expected ExactChangeUnavailable to be thrown.');
        } catch (ExactChangeUnavailable) {
            self::assertSame(1, $machine->productStock($selector));
            self::assertSame(50, $machine->insertedBalance()->cents());
            self::assertSame(
                0,
                $machine->coinReserveQuantity($coin),
            );
        }
    }

    public function testItPurchasesAProductAndReturnsExactChange(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $twentyFive = Coin::fromCents(25);
        $ten = Coin::fromCents(10);
        $hundred = Coin::fromCents(100);

        $machine = VendingMachine::create(
            ProductSlot::create(
                $selector,
                'Water',
                Money::fromCents(65),
            ),
        );

        $machine->setProductStock($selector, 1);
        $machine->setCoinReserveQuantity($twentyFive, 1);
        $machine->setCoinReserveQuantity($ten, 1);
        $machine->insertCoin($hundred);

        $result = $machine->buyProduct(
            $selector,
            new ExactChangeCalculator(),
        );

        self::assertSame(
            [25, 10],
            array_map(
                static fn (Coin $coin): int => $coin->cents(),
                $result->change(),
            ),
        );

        self::assertSame(0, $machine->productStock($selector));
        self::assertSame(0, $machine->insertedBalance()->cents());
        self::assertSame(0, $machine->coinReserveQuantity($twentyFive));
        self::assertSame(0, $machine->coinReserveQuantity($ten));
        self::assertSame(1, $machine->coinReserveQuantity($hundred));
    }

    public function testItPrioritizesUnknownProductAndPreservesState(): void
    {
        $water = ProductSelector::fromString('WATER');
        $coin = Coin::fromCents(25);

        $machine = VendingMachine::create(
            ProductSlot::create(
                $water,
                'Water',
                Money::fromCents(100),
            ),
        );

        $machine->setProductStock($water, 1);
        $machine->insertCoin($coin);

        try {
            $machine->buyProduct(
                ProductSelector::fromString('UNKNOWN'),
                new ExactChangeCalculator(),
            );

            self::fail('Expected ProductNotFound to be thrown.');
        } catch (ProductNotFound) {
            self::assertSame(1, $machine->productStock($water));
            self::assertSame(25, $machine->insertedBalance()->cents());
            self::assertSame(
                0,
                $machine->coinReserveQuantity($coin),
            );
        }
    }

    public function testItPrioritizesOutOfStockAndPreservesState(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $coin = Coin::fromCents(25);

        $machine = VendingMachine::create(
            ProductSlot::create(
                $selector,
                'Water',
                Money::fromCents(100),
            ),
        );

        $machine->insertCoin($coin);

        try {
            $machine->buyProduct(
                $selector,
                new ExactChangeCalculator(),
            );

            self::fail('Expected ProductOutOfStock to be thrown.');
        } catch (ProductOutOfStock) {
            self::assertSame(0, $machine->productStock($selector));
            self::assertSame(25, $machine->insertedBalance()->cents());
            self::assertSame(
                0,
                $machine->coinReserveQuantity($coin),
            );
        }
    }
}
