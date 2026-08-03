<?php

declare(strict_types=1);

namespace App\Tests\Application\VendingMachine\SetCoinReserveQuantity;

use App\VendingMachine\Application\SetCoinReserveQuantity\SetCoinReserveQuantityUseCase;
use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Exception\InvalidCoinDenomination;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\ProductSlot;
use App\VendingMachine\Domain\Repository\VendingMachineRepository;
use App\VendingMachine\Domain\VendingMachine;
use PHPUnit\Framework\TestCase;

final class SetCoinReserveQuantityUseCaseTest extends TestCase
{
    public function testItSetsCoinQuantityAndSavesTheMachine(): void
    {
        $machine = VendingMachine::create(
            ProductSlot::create(
                ProductSelector::fromString('WATER'),
                'Water',
                Money::fromCents(65),
            ),
        );

        $repository = $this->createMock(
            VendingMachineRepository::class,
        );

        $repository
            ->method('load')
            ->willReturn($machine);

        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::identicalTo($machine));

        $useCase = new SetCoinReserveQuantityUseCase($repository);

        $useCase->execute(25, 4);

        self::assertSame(
            4,
            $machine->coinReserveQuantity(Coin::fromCents(25)),
        );
    }

    public function testItDoesNotLoadOrSaveForAnInvalidDenomination(): void
    {
        $repository = $this->createMock(
            VendingMachineRepository::class,
        );

        $repository
            ->expects(self::never())
            ->method('load');

        $repository
            ->expects(self::never())
            ->method('save');

        $useCase = new SetCoinReserveQuantityUseCase($repository);

        $this->expectException(InvalidCoinDenomination::class);

        $useCase->execute(20, 4);
    }
}
