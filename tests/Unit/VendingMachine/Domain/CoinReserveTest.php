<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\CoinReserve;
use App\VendingMachine\Domain\Exception\NegativeCoinQuantity;
use PHPUnit\Framework\TestCase;

final class CoinReserveTest extends TestCase
{
    public function testItStartsEmpty(): void
    {
        $reserve = CoinReserve::empty();

        self::assertSame(
            0,
            $reserve->quantityOf(Coin::fromCents(25)),
        );
    }

    public function testItSetsAnAbsoluteQuantityWithoutMutatingPreviousState(): void
    {
        $coin = Coin::fromCents(25);
        $emptyReserve = CoinReserve::empty();

        $reserveWithTen = $emptyReserve->withQuantity($coin, 10);
        $reserveWithFour = $reserveWithTen->withQuantity($coin, 4);

        self::assertSame(0, $emptyReserve->quantityOf($coin));
        self::assertSame(10, $reserveWithTen->quantityOf($coin));
        self::assertSame(4, $reserveWithFour->quantityOf($coin));
    }

    public function testItRejectsANegativeQuantity(): void
    {
        $this->expectException(NegativeCoinQuantity::class);

        CoinReserve::empty()->withQuantity(
            Coin::fromCents(25),
            -1,
        );
    }

    public function testItAllowsSettingQuantityToZero(): void
    {
        $coin = Coin::fromCents(25);
        $reserve = CoinReserve::empty()->withQuantity($coin, 10);

        $emptyReserve = $reserve->withQuantity($coin, 0);

        self::assertSame(0, $emptyReserve->quantityOf($coin));
    }
}
