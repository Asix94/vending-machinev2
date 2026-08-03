<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use DomainException;

final class InvalidProductPrice extends DomainException
{
    public function __construct(int $cents)
    {
        parent::__construct(
            sprintf('Product price must be greater than zero: %d cents.', $cents),
        );
    }
}
