<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Exception\NegativeCoinQuantity;
use App\VendingMachine\Domain\Exception\NegativeProductStock;
use App\VendingMachine\Domain\Exception\ProductNotFound;
use App\VendingMachine\Domain\Exception\ServiceUnavailableDuringOperation;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\ProductSlot;
use App\VendingMachine\Domain\VendingMachine;
use PHPUnit\Framework\TestCase;

final class VendingMachineServiceTest extends TestCase
{
    public function testItSetsAnAbsoluteProductStock(): void
    {
        $machine = $this->createMachine();
        $selector = ProductSelector::fromString('WATER');

        $machine->setProductStock($selector, 10);
        $machine->setProductStock($selector, 4);

        self::assertSame(4, $machine->productStock($selector));
    }

    private function createMachine(): VendingMachine
    {
        return VendingMachine::create(
            ProductSlot::create(
                ProductSelector::fromString('WATER'),
                'Water',
                Money::fromCents(65),
            ),
        );
    }

    public function testItRejectsAnUnknownProductSelector(): void
    {
        $machine = $this->createMachine();
        $unknownSelector = ProductSelector::fromString('UNKNOWN');

        $this->expectException(ProductNotFound::class);

        $machine->setProductStock($unknownSelector, 10);
    }

    public function testItSetsAnAbsoluteCoinReserveQuantity(): void
    {
        $machine = $this->createMachine();
        $coin = Coin::fromCents(25);

        $machine->setCoinReserveQuantity($coin, 10);
        $machine->setCoinReserveQuantity($coin, 4);

        self::assertSame(
            4,
            $machine->coinReserveQuantity($coin),
        );
    }

    public function testItPrioritizesActiveOperationWhenProductServiceIsRequested(): void
    {
        $machine = $this->createMachine();
        $water = ProductSelector::fromString('WATER');

        $machine->setProductStock($water, 5);
        $machine->insertCoin(Coin::fromCents(25));

        try {
            $machine->setProductStock(
                ProductSelector::fromString('UNKNOWN'),
                -1,
            );

            self::fail(
                'Expected ServiceUnavailableDuringOperation to be thrown.',
            );
        } catch (ServiceUnavailableDuringOperation) {
            self::assertSame(5, $machine->productStock($water));
            self::assertSame(25, $machine->insertedBalance()->cents());
        }
    }

    public function testItPrioritizesActiveOperationWhenCoinServiceIsRequested(): void
    {
        $machine = $this->createMachine();
        $coin = Coin::fromCents(25);

        $machine->setCoinReserveQuantity($coin, 5);
        $machine->insertCoin(Coin::fromCents(10));

        try {
            $machine->setCoinReserveQuantity($coin, -1);

            self::fail(
                'Expected ServiceUnavailableDuringOperation to be thrown.',
            );
        } catch (ServiceUnavailableDuringOperation) {
            self::assertSame(
                5,
                $machine->coinReserveQuantity($coin),
            );
            self::assertSame(10, $machine->insertedBalance()->cents());
        }
    }

    public function testItAllowsServiceAfterReturningInsertedCoins(): void
    {
        $machine = $this->createMachine();
        $water = ProductSelector::fromString('WATER');
        $coin = Coin::fromCents(25);

        $machine->insertCoin($coin);
        $machine->returnInsertedCoins();

        $machine->setProductStock($water, 7);
        $machine->setCoinReserveQuantity($coin, 3);

        self::assertSame(7, $machine->productStock($water));
        self::assertSame(
            3,
            $machine->coinReserveQuantity($coin),
        );
    }

    public function testItPreservesProductStockWhenNegativeQuantityIsRejected(): void
    {
        $machine = $this->createMachine();
        $water = ProductSelector::fromString('WATER');

        $machine->setProductStock($water, 5);

        try {
            $machine->setProductStock($water, -1);
            self::fail('Expected NegativeProductStock to be thrown.');
        } catch (NegativeProductStock) {
            self::assertSame(5, $machine->productStock($water));
        }
    }

    public function testItPreservesCoinReserveWhenNegativeQuantityIsRejected(): void
    {
        $machine = $this->createMachine();
        $coin = Coin::fromCents(25);

        $machine->setCoinReserveQuantity($coin, 5);

        try {
            $machine->setCoinReserveQuantity($coin, -1);
            self::fail('Expected NegativeCoinQuantity to be thrown.');
        } catch (NegativeCoinQuantity) {
            self::assertSame(
                5,
                $machine->coinReserveQuantity($coin),
            );
        }
    }
}
