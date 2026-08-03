<?php

declare(strict_types=1);

namespace App\Tests\Application\VendingMachine\SetProductStock;

use App\VendingMachine\Application\SetProductStock\SetProductStockUseCase;
use App\VendingMachine\Domain\Exception\ProductNotFound;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\ProductSlot;
use App\VendingMachine\Domain\Repository\VendingMachineRepository;
use App\VendingMachine\Domain\VendingMachine;
use PHPUnit\Framework\TestCase;

final class SetProductStockUseCaseTest extends TestCase
{
    public function testItSetsProductStockAndSavesTheMachine(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $machine = VendingMachine::create(
            ProductSlot::create(
                $selector,
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

        $useCase = new SetProductStockUseCase($repository);

        $useCase->execute('WATER', 4);

        self::assertSame(4, $machine->productStock($selector));
    }

    public function testItDoesNotSaveWhenProductIsUnknown(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $machine = VendingMachine::create(
            ProductSlot::create(
                $selector,
                'Water',
                Money::fromCents(65),
            ),
        );
        $machine->setProductStock($selector, 2);

        $repository = $this->createMock(
            VendingMachineRepository::class,
        );

        $repository
            ->method('load')
            ->willReturn($machine);

        $repository
            ->expects(self::never())
            ->method('save');

        $useCase = new SetProductStockUseCase($repository);

        try {
            $useCase->execute('UNKNOWN', 4);
            self::fail('Expected ProductNotFound to be thrown.');
        } catch (ProductNotFound) {
            self::assertSame(2, $machine->productStock($selector));
        }
    }
}
