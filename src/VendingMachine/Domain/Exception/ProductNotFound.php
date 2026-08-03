<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Exception;

use App\VendingMachine\Domain\ProductSelector;
use DomainException;

final class ProductNotFound extends DomainException
{
    public function __construct(ProductSelector $selector)
    {
        parent::__construct(
            sprintf(
                'Product with selector "%s" was not found.',
                $selector->value(),
            ),
        );
    }
}
