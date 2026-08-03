<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Repository;

use App\VendingMachine\Domain\VendingMachine;

interface VendingMachineRepository
{
    public function load(): VendingMachine;

    public function save(VendingMachine $machine): void;
}
