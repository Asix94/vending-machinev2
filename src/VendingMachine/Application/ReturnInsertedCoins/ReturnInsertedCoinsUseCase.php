<?php

declare(strict_types=1);

namespace App\VendingMachine\Application\ReturnInsertedCoins;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\Repository\VendingMachineRepository;

final readonly class ReturnInsertedCoinsUseCase
{
    public function __construct(
        private VendingMachineRepository $repository,
    ) {
    }

    /**
     * @return list<int>
     */
    public function execute(): array
    {
        $machine = $this->repository->load();
        $returnedCoins = $machine->returnInsertedCoins();

        $this->repository->save($machine);

        return array_map(
            static fn (Coin $coin): int => $coin->cents(),
            $returnedCoins,
        );
    }
}
