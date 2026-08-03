<?php

declare(strict_types=1);

namespace App\VendingMachine\Application\BuyProduct;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\ProductSelector;
use App\VendingMachine\Domain\Repository\VendingMachineRepository;
use App\VendingMachine\Domain\Service\ExactChangeCalculator;

final readonly class BuyProductUseCase
{
    public function __construct(
        private VendingMachineRepository $repository,
        private ExactChangeCalculator $changeCalculator,
    ) {
    }

    public function execute(string $selector): BuyProductResult
    {
        $productSelector = ProductSelector::fromString($selector);
        $machine = $this->repository->load();

        $purchaseResult = $machine->buyProduct(
            $productSelector,
            $this->changeCalculator,
        );

        $this->repository->save($machine);

        return new BuyProductResult(
            $purchaseResult->productSelector()->value(),
            array_map(
                static fn (Coin $coin): int => $coin->cents(),
                $purchaseResult->change(),
            ),
        );
    }
}
