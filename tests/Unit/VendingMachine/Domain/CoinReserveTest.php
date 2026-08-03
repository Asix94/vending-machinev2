<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\InsufficientCoinQuantity;
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

    public function testItExposesOnlyAvailableDenominations(): void
    {
        $reserve = CoinReserve::empty()
            ->withQuantity(Coin::fromCents(25), 0)
            ->withQuantity(Coin::fromCents(10), 2);

        $denominations = array_map(
            static fn (Coin $coin): int => $coin->cents(),
            $reserve->availableDenominations(),
        );

        self::assertSame([10], $denominations);
    }

    public function testItAddsCoinsWithoutMutatingPreviousState(): void
    {
        $twentyFive = Coin::fromCents(25);
        $ten = Coin::fromCents(10);

        $original = CoinReserve::empty()
            ->withQuantity($twentyFive, 2);

        $updated = $original->withAddedCoins(
            $twentyFive,
            $twentyFive,
            $ten,
        );

        self::assertSame(2, $original->quantityOf($twentyFive));
        self::assertSame(4, $updated->quantityOf($twentyFive));
        self::assertSame(1, $updated->quantityOf($ten));
    }

    public function testItRemovesCoinsWithoutMutatingPreviousState(): void
    {
        $twentyFive = Coin::fromCents(25);
        $ten = Coin::fromCents(10);

        $original = CoinReserve::empty()
            ->withQuantity($twentyFive, 3)
            ->withQuantity($ten, 1);

        $updated = $original->withoutCoins(
            $twentyFive,
            $twentyFive,
            $ten,
        );

        self::assertSame(3, $original->quantityOf($twentyFive));
        self::assertSame(1, $original->quantityOf($ten));
        self::assertSame(1, $updated->quantityOf($twentyFive));
        self::assertSame(0, $updated->quantityOf($ten));
    }

    public function testItPreservesStateWhenRemovingUnavailableCoinsIsRejected(): void
    {
        $coin = Coin::fromCents(25);
        $reserve = CoinReserve::empty()
            ->withQuantity($coin, 1);

        try {
            $reserve->withoutCoins($coin, $coin);
            self::fail('Expected InsufficientCoinQuantity to be thrown.');
        } catch (InsufficientCoinQuantity) {
            self::assertSame(1, $reserve->quantityOf($coin));
        }
    }
}
