# ADR 0005: Use Bounded Search for Exact Change

- Status: Accepted
- Date: 2026-08-03

## Context

The machine must return exact change from a limited physical coin reserve. A
valid result must use no more coins than the reserve contains, minimize the
number of returned coins, prefer higher denominations when minimum-size
solutions tie, and remain deterministic.

A greedy algorithm that always selects the largest available coin is not
correct with limited inventory. For example, when returning 30 cents from one
25-cent coin and three 10-cent coins, greedy selects 25 and becomes unable to
return the remaining 5 cents. The valid solution is three 10-cent coins.

The domain currently supports four fixed denominations: 5, 10, 25, and 100
cents.

## Decision

Exact change is calculated by the stateless Domain Service
`ExactChangeCalculator` using bounded recursive search.

The algorithm:

1. Obtains available denominations from `CoinReserve`.
2. Orders denominations from highest to lowest.
3. For each denomination, tries quantities from the maximum usable amount down
   to zero.
4. Recursively explores the remaining amount with lower denominations.
5. Discards branches that cannot reach an exact zero remainder.
6. Keeps the candidate with the smallest total number of coins.
7. Keeps the first candidate on equal coin count, preserving the preference for
   higher denominations.

An empty list is a valid result for a zero amount. Internally, `null` represents
a search branch without an exact solution. At the public boundary, no solution
raises `ExactChangeUnavailable`.

The service reads `CoinReserve` but never modifies it. Returned coins are
ordered from highest to lowest denomination.

## Alternatives Considered

### Greedy selection

Rejected because it can fail even when an exact combination exists with the
limited available inventory.

### Dynamic programming

Capable of finding an optimal result with predictable complexity, but it
requires additional memory and a more complex implementation. It is unnecessary
for four fixed denominations and the expected small transaction amounts.

### Put the algorithm inside CoinReserve

Defensible, but rejected to keep reserve composition and change-selection policy
separate. `CoinReserve` exposes domain-level availability while the Domain
Service owns optimization behavior.

### Introduce a Strategy interface

Deferred because there is only one required policy and one consumer. An
interface without an alternative implementation would add abstraction without
solving a current problem.

## Consequences

- The algorithm finds valid combinations that greedy misses.
- Limited inventory is respected.
- Results minimize coin count and are deterministic.
- The worst-case search grows with denomination count and available quantities.
- The current complexity is acceptable because denomination count is fixed at
  four and quantities are bounded by the requested amount.
- If denominations become configurable or transaction amounts grow
  substantially, dynamic programming or memoization should be reconsidered.
