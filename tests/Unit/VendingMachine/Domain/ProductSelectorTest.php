<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\EmptyProductSelector;
use App\VendingMachine\Domain\ProductSelector;
use PHPUnit\Framework\TestCase;

final class ProductSelectorTest extends TestCase
{
    public function testItCanBeCreatedFromString(): void
    {
        $selector = ProductSelector::fromString('WATER');

        self::assertSame('WATER', $selector->value());
    }

    public function testItRejectsAnEmptyValue(): void
    {
        $this->expectException(EmptyProductSelector::class);

        ProductSelector::fromString('');
    }
}
