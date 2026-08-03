<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain\Service;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\CoinReserve;
use App\VendingMachine\Domain\Exception\ExactChangeUnavailable;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\Service\ExactChangeCalculator;
use PHPUnit\Framework\TestCase;

final class ExactChangeCalculatorTest extends TestCase
{
    public function testItReturnsNoCoinsForZeroAmount(): void
    {
        $calculator = new ExactChangeCalculator();

        $change = $calculator->calculate(
            Money::fromCents(0),
            CoinReserve::empty(),
        );

        self::assertSame([], $change);
    }

    public function testItReturnsOneCoinForAnExactDenomination(): void
    {
        $coin = Coin::fromCents(25);
        $reserve = CoinReserve::empty()->withQuantity($coin, 1);
        $calculator = new ExactChangeCalculator();

        $change = $calculator->calculate(
            Money::fromCents(25),
            $reserve,
        );

        self::assertSame(
            [25],
            array_map(
                static fn (Coin $coin): int => $coin->cents(),
                $change,
            ),
        );
    }

    public function testItCombinesMultipleCoinsForExactChange(): void
    {
        $reserve = CoinReserve::empty()
            ->withQuantity(Coin::fromCents(25), 1)
            ->withQuantity(Coin::fromCents(10), 1);

        $change = (new ExactChangeCalculator())->calculate(
            Money::fromCents(35),
            $reserve,
        );

        self::assertSame(
            [25, 10],
            array_map(
                static fn (Coin $coin): int => $coin->cents(),
                $change,
            ),
        );
    }

    public function testItFindsExactChangeWhenGreedyChoiceWouldFail(): void
    {
        $reserve = CoinReserve::empty()
            ->withQuantity(Coin::fromCents(25), 1)
            ->withQuantity(Coin::fromCents(10), 3);

        $change = (new ExactChangeCalculator())->calculate(
            Money::fromCents(30),
            $reserve,
        );

        self::assertSame(
            [10, 10, 10],
            array_map(
                static fn (Coin $coin): int => $coin->cents(),
                $change,
            ),
        );
    }

    public function testItUsesTheSmallestPossibleNumberOfCoins(): void
    {
        $reserve = CoinReserve::empty()
            ->withQuantity(Coin::fromCents(25), 2)
            ->withQuantity(Coin::fromCents(10), 5)
            ->withQuantity(Coin::fromCents(5), 10);

        $change = (new ExactChangeCalculator())->calculate(
            Money::fromCents(50),
            $reserve,
        );

        self::assertSame(
            [25, 25],
            array_map(
                static fn (Coin $coin): int => $coin->cents(),
                $change,
            ),
        );
    }

    public function testItRejectsAnAmountWhenExactChangeIsUnavailable(): void
    {
        $reserve = CoinReserve::empty()
            ->withQuantity(Coin::fromCents(25), 1);

        $this->expectException(ExactChangeUnavailable::class);

        (new ExactChangeCalculator())->calculate(
            Money::fromCents(30),
            $reserve,
        );
    }
}
