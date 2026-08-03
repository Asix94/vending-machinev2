<?php

declare(strict_types=1);

namespace App\Tests\Application\VendingMachine\BuyProduct;

use App\VendingMachine\Application\BuyProduct\BuyProductUseCase;
use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Exception\EmptyProductSelector;
use App\VendingMachine\Domain\Exception\InsufficientBalance;
use App\VendingMachine\Domain\Money;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\ProductSlot;
use App\VendingMachine\Domain\Repository\VendingMachineRepository;
use App\VendingMachine\Domain\Service\ExactChangeCalculator;
use App\VendingMachine\Domain\VendingMachine;
use App\VendingMachine\Infrastructure\Persistence\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class BuyProductUseCaseTest extends TestCase
{
    public function testItPurchasesAProductWithTheExactAmount(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $machine = VendingMachine::create(
            ProductSlot::create(
                $selector,
                'Water',
                Money::fromCents(100),
            ),
        );

        $machine->setProductStock($selector, 1);
        $machine->insertCoin(Coin::fromCents(100));

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

        $useCase = new BuyProductUseCase(
            $repository,
            new ExactChangeCalculator(),
        );

        $result = $useCase->execute('WATER');

        self::assertSame('WATER', $result->productSelector());
        self::assertSame([], $result->change());
        self::assertSame(0, $machine->productStock($selector));
        self::assertSame(0, $machine->insertedBalance()->cents());
        self::assertSame(
            1,
            $machine->coinReserveQuantity(Coin::fromCents(100)),
        );
    }

    public function testItReturnsExactChangeAsIntegerDenominations(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $twentyFive = Coin::fromCents(25);
        $ten = Coin::fromCents(10);

        $machine = VendingMachine::create(
            ProductSlot::create(
                $selector,
                'Water',
                Money::fromCents(65),
            ),
        );

        $machine->setProductStock($selector, 1);
        $machine->setCoinReserveQuantity($twentyFive, 1);
        $machine->setCoinReserveQuantity($ten, 1);
        $machine->insertCoin(Coin::fromCents(100));

        $repository = new InMemoryVendingMachineRepository($machine);
        $useCase = new BuyProductUseCase(
            $repository,
            new ExactChangeCalculator(),
        );

        $result = $useCase->execute('WATER');

        self::assertSame([25, 10], $result->change());
    }

    public function testItDoesNotSaveWhenPurchaseIsRejected(): void
    {
        $selector = ProductSelector::fromString('WATER');
        $machine = VendingMachine::create(
            ProductSlot::create(
                $selector,
                'Water',
                Money::fromCents(100),
            ),
        );

        $machine->setProductStock($selector, 1);
        $machine->insertCoin(Coin::fromCents(25));

        $repository = $this->createMock(
            VendingMachineRepository::class,
        );

        $repository
            ->method('load')
            ->willReturn($machine);

        $repository
            ->expects(self::never())
            ->method('save');

        $useCase = new BuyProductUseCase(
            $repository,
            new ExactChangeCalculator(),
        );

        try {
            $useCase->execute('WATER');
            self::fail('Expected InsufficientBalance to be thrown.');
        } catch (InsufficientBalance) {
            self::assertSame(1, $machine->productStock($selector));
            self::assertSame(25, $machine->insertedBalance()->cents());
        }
    }

    public function testItDoesNotAccessRepositoryForAnEmptySelector(): void
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

        $useCase = new BuyProductUseCase(
            $repository,
            new ExactChangeCalculator(),
        );

        $this->expectException(EmptyProductSelector::class);

        $useCase->execute('');
    }
}
