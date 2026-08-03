# ADR 0004: Model Coin Reserve as an Immutable Value Object

- Status: Accepted
- Date: 2026-08-03

## Context

The machine must track how many coins of each accepted denomination are
available to return as change. A total monetary amount is insufficient because
different physical coin combinations with the same value have different exact
change capabilities.

Service operations set absolute coin quantities and must reject negative
values. The model should avoid partially changing reserve state when an update
is invalid or when a later purchase operation cannot be completed.

## Decision

`CoinReserve` is modeled as an immutable Value Object and domain collection.

- `empty()` creates a reserve with zero coins of every denomination.
- `quantityOf()` returns the available quantity for a `Coin`.
- `withQuantity()` sets an absolute quantity and returns a new reserve.
- Previous reserve instances remain unchanged.
- Negative quantities raise `NegativeCoinQuantity` before new state is created.
- A quantity of zero is valid and represents an unavailable denomination.
- The reserve accepts `Coin` objects rather than arbitrary denomination
  integers.

Internally, quantities are indexed by denomination in integer cents. This is an
encapsulated implementation detail and is not exposed to consumers.

## Alternatives Considered

### Represent the reserve as Money

Rejected because `Money(35)` does not reveal whether the machine owns one
25-cent and one 10-cent coin, seven 5-cent coins, or another combination. Exact
change depends on composition.

### Use a mutable coin reserve

Rejected because in-place updates make rollback and atomic purchase behavior
harder to reason about. An immutable reserve allows a complete candidate state
to be calculated before replacing the aggregate's current reserve.

### Model each reserve entry as an Entity

Rejected because reserve entries have no independent lifecycle or identity in
the current domain. They are completely described by denomination and quantity
inside the reserve.

### Store raw denomination arrays in the aggregate

Rejected because arrays would expose representation details and spread
non-negative quantity validation across callers.

## Consequences

- Reserve updates are predictable and cannot mutate earlier state.
- Absolute service operations remain deterministic and idempotent.
- The future aggregate must replace its reserve with the instance returned by
  `withQuantity()`.
- Change calculation can inspect quantities through domain methods without
  depending on persistence.
- Additional operations for adding or removing coins will also need to return
  new reserve instances.
