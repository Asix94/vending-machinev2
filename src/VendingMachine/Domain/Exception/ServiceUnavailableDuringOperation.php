<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use DomainException;

final class ServiceUnavailableDuringOperation extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            'Service operations are unavailable while customer coins are inserted.',
        );
    }
}
