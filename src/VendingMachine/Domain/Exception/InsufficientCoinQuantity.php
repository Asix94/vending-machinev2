<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use App\VendingMachine\Domain\Coin;
use DomainException;

final class InsufficientCoinQuantity extends DomainException
{
    public function __construct(
        Coin $coin,
        int $available,
        int $required,
    ) {
        parent::__construct(
            sprintf(
                'Insufficient quantity of %d-cent coins: %d available, %d required.',
                $coin->cents(),
                $available,
                $required,
            ),
        );
    }
}
