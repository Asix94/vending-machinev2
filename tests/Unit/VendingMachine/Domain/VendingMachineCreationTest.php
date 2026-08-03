<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Exception\DuplicateProductSelector;
use App\VendingMachine\Domain\Exception\EmptyProductCatalog;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\ProductSlot;
use App\VendingMachine\Domain\VendingMachine;
use PHPUnit\Framework\TestCase;

final class VendingMachineCreationTest extends TestCase
{
    public function testItStartsWithEmptyCustomerAndReserveState(): void
    {
        $machine = VendingMachine::create(
            ProductSlot::create(
                ProductSelector::fromString('WATER'),
                'Water',
                Money::fromCents(65),
            ),
        );

        self::assertSame(0, $machine->insertedBalance()->cents());
        self::assertSame(
            0,
            $machine->coinReserveQuantity(Coin::fromCents(25)),
        );
    }

    public function testItRejectsAnEmptyProductCatalog(): void
    {
        $this->expectException(EmptyProductCatalog::class);

        VendingMachine::create();
    }

    public function testItRejectsDuplicateProductSelectors(): void
    {
        $this->expectException(DuplicateProductSelector::class);

        VendingMachine::create(
            ProductSlot::create(
                ProductSelector::fromString('WATER'),
                'Water',
                Money::fromCents(65),
            ),
            ProductSlot::create(
                ProductSelector::fromString('WATER'),
                'Sparkling Water',
                Money::fromCents(75),
            ),
        );
    }
}
