<?php

declare(strict_types=1);

namespace App\Tests\Application\VendingMachine\InsertCoin;

use App\VendingMachine\Application\InsertCoin\InsertCoinUseCase;
use App\VendingMachine\Domain\Exception\InvalidCoinDenomination;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\ProductSlot;
use App\VendingMachine\Domain\Repository\VendingMachineRepository;
use App\VendingMachine\Domain\VendingMachine;
use App\VendingMachine\Infrastructure\Persistence\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class InsertCoinUseCaseTest extends TestCase
{
    public function testItInsertsACoinAndReturnsCurrentBalance(): void
    {
        $repository = new InMemoryVendingMachineRepository(
            $this->createMachine(),
        );

        $useCase = new InsertCoinUseCase($repository);

        $balanceInCents = $useCase->execute(25);

        self::assertSame(25, $balanceInCents);
        self::assertSame(
            25,
            $repository->load()->insertedBalance()->cents(),
        );
    }

    public function testItReturnsAccumulatedBalanceAcrossExecutions(): void
    {
        $repository = new InMemoryVendingMachineRepository(
            $this->createMachine(),
        );

        $useCase = new InsertCoinUseCase($repository);

        $useCase->execute(25);
        $balanceInCents = $useCase->execute(10);

        self::assertSame(35, $balanceInCents);
        self::assertSame(
            35,
            $repository->load()->insertedBalance()->cents(),
        );
    }

    public function testItPreservesStateWhenDenominationIsInvalid(): void
    {
        $repository = new InMemoryVendingMachineRepository(
            $this->createMachine(),
        );

        $useCase = new InsertCoinUseCase($repository);

        try {
            $useCase->execute(20);
            self::fail('Expected InvalidCoinDenomination to be thrown.');
        } catch (InvalidCoinDenomination) {
            self::assertSame(
                0,
                $repository->load()->insertedBalance()->cents(),
            );
        }
    }

    public function testItSavesTheUpdatedMachine(): void
    {
        $machine = $this->createMachine();

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

        $useCase = new InsertCoinUseCase($repository);

        $balanceInCents = $useCase->execute(25);

        self::assertSame(25, $balanceInCents);
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
