<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Exception\NegativeMoneyAmount;
use App\VendingMachine\Domain\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testItCanBeCreatedFromCents(): void
    {
        $money = Money::fromCents(65);

        self::assertSame(65, $money->cents());
    }

    public function testItRejectsANegativeAmount(): void
    {
        $this->expectException(NegativeMoneyAmount::class);

        Money::fromCents(-1);
    }

    public function testItCanRepresentZero(): void
    {
        $money = Money::fromCents(0);

        self::assertSame(0, $money->cents());
    }

    public function testItDeterminesWhetherAnAmountIsLessThanAnother(): void
    {
        self::assertTrue(
            Money::fromCents(25)->isLessThan(Money::fromCents(65)),
        );

        self::assertFalse(
            Money::fromCents(65)->isLessThan(Money::fromCents(25)),
        );

        self::assertFalse(
            Money::fromCents(65)->isLessThan(Money::fromCents(65)),
        );
    }

    public function testItSubtractsAnAmountWithoutMutatingTheOriginal(): void
    {
        $money = Money::fromCents(100);

        $result = $money->subtract(Money::fromCents(65));

        self::assertSame(35, $result->cents());
        self::assertSame(100, $money->cents());
    }

    public function testItRejectsASubtractionThatWouldCreateANegativeAmount(): void
    {
        $this->expectException(NegativeMoneyAmount::class);

        Money::fromCents(25)->subtract(
            Money::fromCents(65),
        );
    }
}
