<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use DomainException;

final class ExactChangeUnavailable extends DomainException
{
    public function __construct(int $cents)
    {
        parent::__construct(
            sprintf('Exact change is unavailable for %d cents.', $cents),
        );
    }
}
