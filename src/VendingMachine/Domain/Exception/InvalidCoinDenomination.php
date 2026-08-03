<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use DomainException;

final class InvalidCoinDenomination extends DomainException
{
    public function __construct(int $cents)
    {
        parent::__construct(
            sprintf('Coin denomination of %d cents is not accepted.', $cents),
        );
    }
}
