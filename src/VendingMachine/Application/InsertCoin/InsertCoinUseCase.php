<?php

declare(strict_types=1);

namespace App\VendingMachine\Application\InsertCoin;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Repository\VendingMachineRepository;

final readonly class InsertCoinUseCase
{
    public function __construct(
        private VendingMachineRepository $repository,
    ) {
    }

    public function execute(int $denominationInCents): int
    {
        $coin = Coin::fromCents($denominationInCents);
        $machine = $this->repository->load();

        $machine->insertCoin($coin);
        $this->repository->save($machine);

        return $machine->insertedBalance()->cents();
    }
}
