<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\PurchaseResult;
use PHPUnit\Framework\TestCase;

final class PurchaseResultTest extends TestCase
{
    public function testItContainsTheDispensedProductAndChange(): void
    {
        $selector = ProductSelector::fromString('WATER');

        $result = PurchaseResult::create(
            $selector,
            Coin::fromCents(25),
            Coin::fromCents(10),
        );

        self::assertSame($selector, $result->productSelector());
        self::assertSame(
            [25, 10],
            array_map(
                static fn (Coin $coin): int => $coin->cents(),
                $result->change(),
            ),
        );
    }
}
