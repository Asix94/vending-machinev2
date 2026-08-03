<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use App\VendingMachine\Domain\Money;
use DomainException;

final class InsufficientBalance extends DomainException
{
    public function __construct(
        Money $available,
        Money $required,
    ) {
        parent::__construct(
            sprintf(
                'Insufficient balance: %d cents available, %d cents required.',
                $available->cents(),
                $required->cents(),
            ),
        );
    }
}
