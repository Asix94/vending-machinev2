<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use DomainException;

final class EmptyProductCatalog extends DomainException
{
    public function __construct()
    {
        parent::__construct('A vending machine requires at least one product.');
    }
}
