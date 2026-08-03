<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use DomainException;

final class NegativeCoinQuantity extends DomainException
{
    public function __construct(int $quantity)
    {
        parent::__construct(
            sprintf('Coin quantity cannot be negative: %d.', $quantity),
        );
    }
}
