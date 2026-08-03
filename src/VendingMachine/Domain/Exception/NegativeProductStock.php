<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use DomainException;

final class NegativeProductStock extends DomainException
{
    public function __construct(int $stock)
    {
        parent::__construct(
            sprintf('Product stock cannot be negative: %d.', $stock),
        );
    }
}
