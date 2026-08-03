<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\ProductSlot;
use App\VendingMachine\Domain\VendingMachine;
use PHPUnit\Framework\TestCase;

final class VendingMachineCoinOperationTest extends TestCase
{
    public function testItInsertsCoinsAndDerivesBalance(): void
    {
        $machine = $this->createMachine();

        $machine->insertCoin(Coin::fromCents(25));
        $machine->insertCoin(Coin::fromCents(10));

        self::assertSame(35, $machine->insertedBalance()->cents());
        self::assertSame(
            0,
            $machine->coinReserveQuantity(Coin::fromCents(25)),
        );
    }

    public function testItReturnsInsertedCoinsInOrderAndClearsOperation(): void
    {
        $machine = $this->createMachine();
        $machine->insertCoin(Coin::fromCents(25));
        $machine->insertCoin(Coin::fromCents(10));
        $machine->insertCoin(Coin::fromCents(100));

        $returnedCoins = $machine->returnInsertedCoins();

        self::assertSame(
            [25, 10, 100],
            array_map(
                static fn (Coin $coin): int => $coin->cents(),
                $returnedCoins,
            ),
        );
        self::assertSame(0, $machine->insertedBalance()->cents());
        self::assertSame(
            0,
            $machine->coinReserveQuantity(Coin::fromCents(25)),
        );
    }

    public function testItReturnsAnEmptyListWhenNoCoinsWereInserted(): void
    {
        self::assertSame(
            [],
            $this->createMachine()->returnInsertedCoins(),
        );
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
}
