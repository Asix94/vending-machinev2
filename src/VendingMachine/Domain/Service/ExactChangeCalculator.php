<?php

declare(strict_types=1);

namespace App\VendingMachine\Domain\Service;

use App\VendingMachine\Domain\Coin;
use App\VendingMachine\Domain\CoinReserve;
use App\VendingMachine\Domain\Exception\ExactChangeUnavailable;
use App\VendingMachine\Domain\Money;

final class ExactChangeCalculator
{
    /**
     * @return list<Coin>
     */
    public function calculate(Money $amount, CoinReserve $reserve): array
    {
        $denominations = $reserve->availableDenominations();

        usort(
            $denominations,
            static fn (Coin $left, Coin $right): int =>
                $right->cents() <=> $left->cents(),
        );

        $change = $this->findBestChange(
            $amount->cents(),
            $denominations,
            $reserve,
            0,
        );

        if ($change === null) {
            throw new ExactChangeUnavailable($amount->cents());
        }

        return $change;
    }

    /**
     * @param list<Coin> $denominations
     *
     * @return list<Coin>|null
     */
    private function findBestChange(
        int $remaining,
        array $denominations,
        CoinReserve $reserve,
        int $index,
    ): ?array {
        if ($remaining === 0) {
            return [];
        }

        if (!isset($denominations[$index])) {
            return null;
        }

        $coin = $denominations[$index];
        $maximumQuantity = min(
            $reserve->quantityOf($coin),
            intdiv($remaining, $coin->cents()),
        );

        $bestChange = null;

        for ($quantity = $maximumQuantity; $quantity >= 0; $quantity--) {
            $nextRemaining = $remaining - ($quantity * $coin->cents());

            $remainingChange = $this->findBestChange(
                $nextRemaining,
                $denominations,
                $reserve,
                $index + 1,
            );

            if ($remainingChange === null) {
                continue;
            }

            $candidate = array_merge(
                array_fill(0, $quantity, $coin),
                $remainingChange,
            );

            if (
                $bestChange === null
                || count($candidate) < count($bestChange)
            ) {
                $bestChange = $candidate;
            }
        }

        return $bestChange;
    }
}
