<?php

declare(strict_types=1);

namespace App\Tests\Unit\VendingMachine\Domain;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Exception\InvalidCoinDenomination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CoinTest extends TestCase
{
    #[DataProvider('acceptedDenominations')]
    public function testItCanBeCreatedFromCents(int $cents): void
    {
        $coin = Coin::fromCents($cents);

        self::assertSame($cents, $coin->cents());
    }

    public function testItRejectsAnInvalidDenomination(): void
    {
        $this->expectException(InvalidCoinDenomination::class);

        Coin::fromCents(20);
    }

    public static function acceptedDenominations(): iterable
    {
        yield 'five cents' => [5];
        yield 'ten cents' => [10];
        yield 'twenty-five cents' => [25];
        yield 'one hundred cents' => [100];
    }
}
