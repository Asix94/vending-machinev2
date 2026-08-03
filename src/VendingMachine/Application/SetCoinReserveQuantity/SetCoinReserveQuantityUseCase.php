<?php

declare(strict_types=1);

namespace App\VendingMachine\Application\SetCoinReserveQuantity;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Repository\VendingMachineRepository;

final readonly class SetCoinReserveQuantityUseCase
{
    public function __construct(
        private VendingMachineRepository $repository,
    ) {
    }

    public function execute(int $denominationInCents, int $quantity): void
    {
        $coin = Coin::fromCents($denominationInCents);
        $machine = $this->repository->load();

        $machine->setCoinReserveQuantity($coin, $quantity);
        $this->repository->save($machine);
    }
}
