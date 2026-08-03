# ADR 0002: Model Coin as a Value Object

- Status: Accepted
- Date: 2026-08-03

## Context

The machine accepts only 5, 10, 25, and 100-cent coins. Business behavior does
not need to distinguish two physical coins with the same denomination. The
domain must prevent unsupported denominations without relying on a controller
or database lookup.

The quantity of each denomination changes over time, but that quantity belongs
to inserted-coin collections and the machine's coin reserve, not to the
definition of an individual `Coin`.

## Decision

`Coin` is modeled as a final, immutable Value Object.

- It is created through `Coin::fromCents()`.
- Its constructor is private.
- It exposes its denomination through `cents()`.
- It accepts only 5, 10, 25, and 100 cents.
- It raises `InvalidCoinDenomination` when its invariant is violated.
- It has no dependency on frameworks, persistence, or transport mechanisms.

Coin reserve quantities may later be persisted by denomination. Persistence
does not replace validation in the domain.

## Alternatives Considered

### Primitive integers throughout the codebase

Rejected because any integer could be passed as a coin, spreading validation
across callers and creating primitive obsession.

### PHP backed enum

A valid and concise representation for a closed denomination set. A Value
Object was selected to provide an explicit named constructor, a domain-specific
exception, and a natural place for future coin behavior without moving input
validation into callers.

### Configurable denomination table in PostgreSQL

Rejected for the current scope because accepted denominations are a fixed
business rule. It would make domain validation depend on infrastructure and add
configuration behavior that is not required.

## Consequences

- Invalid coins cannot exist in the domain model.
- Equal denominations are interchangeable and compared by value.
- Accepted denominations are changed through code and tests.
- The model remains usable in unit tests without infrastructure.
- If accepted denominations become machine-specific configuration, this
  decision must be revisited.
