<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use DomainException;


final class NegativeMoneyAmount extends DomainException
{
    public function __construct(int $cents)
    {
        parent::__construct(
            sprintf('Money amount cannot be negative: %d cents.', $cents),
        );
    }
}
