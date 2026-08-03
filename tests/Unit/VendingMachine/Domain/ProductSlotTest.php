<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\InvalidProductPrice;
use App\VendingMachine\Domain\Exception\NegativeProductStock;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\ProductSlot;
use PHPUnit\Framework\TestCase;

final class ProductSlotTest extends TestCase
{
    public function testItCanBeCreated(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $price = Money::fromCents(65);

        $slot = ProductSlot::create($selector, 'Water', $price);

        self::assertSame($selector, $slot->selector());
        self::assertSame('Water', $slot->name());
        self::assertSame($price, $slot->price());
        self::assertSame(0, $slot->stock());
    }

    public function testItRejectsAZeroPrice(): void
    {
        $this->expectException(InvalidProductPrice::class);

        ProductSlot::create(
            ProductSelector::fromString('WATER'),
            'Water',
            Money::fromCents(0),
        );
    }

    public function testItSetsAnAbsoluteStockQuantity(): void
    {
        $slot = ProductSlot::create(
            ProductSelector::fromString('WATER'),
            'Water',
            Money::fromCents(65),
        );

        $slot->setStock(10);
        $slot->setStock(4);

        self::assertSame(4, $slot->stock());
    }

    public function testItPreservesStockWhenNegativeStockIsRejected(): void
    {
        $slot = ProductSlot::create(
            ProductSelector::fromString('WATER'),
            'Water',
            Money::fromCents(65),
        );
        $slot->setStock(10);

        try {
            $slot->setStock(-1);
            self::fail('Expected NegativeProductStock to be thrown.');
        } catch (NegativeProductStock) {
            self::assertSame(10, $slot->stock());
        }
    }
}
