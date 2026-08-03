<?php

declare(strict_types=1);

namespace App\VendingMachine\Application\SetProductStock;

use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\Repository\VendingMachineRepository;

final readonly class SetProductStockUseCase
{
    public function __construct(
        private VendingMachineRepository $repository,
    ) {
    }

    public function execute(string $selector, int $quantity): void
    {
        $productSelector = ProductSelector::fromString($selector);
        $machine = $this->repository->load();

        $machine->setProductStock($productSelector, $quantity);
        $this->repository->save($machine);
    }
}
