<?php

declare(strict_types=1);

namespace App\Tests\Application\VendingMachine\ReturnInsertedCoins;

use App\VendingMachine\Application\ReturnInsertedCoins\ReturnInsertedCoinsUseCase;
use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\ProductSlot;
use App\VendingMachine\Domain\Repository\VendingMachineRepository;
use App\VendingMachine\Domain\VendingMachine;
use App\VendingMachine\Infrastructure\Persistence\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class ReturnInsertedCoinsUseCaseTest extends TestCase
{
    public function testItReturnsInsertedCoinsInOrderAndClearsBalance(): void
    {
        $machine = $this->createMachine();
        $machine->insertCoin(Coin::fromCents(25));
        $machine->insertCoin(Coin::fromCents(10));
        $machine->insertCoin(Coin::fromCents(100));

        $repository = new InMemoryVendingMachineRepository($machine);
        $useCase = new ReturnInsertedCoinsUseCase($repository);

        $returnedCoins = $useCase->execute();

        self::assertSame([25, 10, 100], $returnedCoins);
        self::assertSame(
            0,
            $repository->load()->insertedBalance()->cents(),
        );
    }

    public function testItReturnsAnEmptyListWhenNoCoinsWereInserted(): void
    {
        $repository = new InMemoryVendingMachineRepository(
            $this->createMachine(),
        );

        $useCase = new ReturnInsertedCoinsUseCase($repository);

        $returnedCoins = $useCase->execute();

        self::assertSame([], $returnedCoins);
        self::assertSame(
            0,
            $repository->load()->insertedBalance()->cents(),
        );
    }

    public function testItSavesTheUpdatedMachine(): void
    {
        $machine = $this->createMachine();
        $machine->insertCoin(Coin::fromCents(25));

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

        $useCase = new ReturnInsertedCoinsUseCase($repository);

        $returnedCoins = $useCase->execute();

        self::assertSame([25], $returnedCoins);
    }

    private function createMachine(): VendingMachine
    {
        return VendingMachine::create(
            ProductSlot::create(
                ProductSelector::fromString('WATER'),
                'Water',
                Money::fromCents(65),
            ),
        );
    }
}
