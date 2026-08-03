<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use DomainException;

final class EmptyProductSelector extends DomainException
{
    public function __construct()
    {
        parent::__construct('Product selector cannot be empty.');
    }
}
