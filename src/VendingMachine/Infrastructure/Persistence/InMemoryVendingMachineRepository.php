<?php

declare(strict_types=1);

namespace App\VendingMachine\Infrastructure\Persistence;

use App\VendingMachine\Domain\Repository\VendingMachineRepository;
use App\VendingMachine\Domain\VendingMachine;

final class InMemoryVendingMachineRepository implements VendingMachineRepository
{
    public function __construct(
        private VendingMachine $machine,
    ) {
    }

    public function load(): VendingMachine
    {
        return $this->machine;
    }

    public function save(VendingMachine $machine): void
    {
        $this->machine = $machine;
    }
}
