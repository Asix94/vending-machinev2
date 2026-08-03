<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\Exception\NegativeMoneyAmount;
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
}
