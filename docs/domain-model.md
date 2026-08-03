# Domain Model

## Purpose

This document describes the domain modeling concepts used in the project and
how they apply to the current model. Business behavior remains defined in
[`domain-rules.md`](domain-rules.md).

## Value Object

A Value Object represents a domain concept whose identity is entirely defined
by its attributes. It has no independent identifier and two instances with the
same values are equivalent.

A Value Object should:

- be immutable;
- protect its own invariants;
- be compared by value;
- expose behavior meaningful to the domain;
- be replaced, rather than modified, when a different value is needed.

`Coin` is a Value Object because individual coins of the same denomination are
interchangeable in this domain. Tracking a physical coin identity or serial
number would not affect any business decision.

`Money` is a Value Object that represents an arbitrary non-negative monetary
amount in integer cents. Unlike `Coin`, it is not restricted to physical coin
denominations.

`ProductSelector` is a Value Object that represents the non-empty selector used
to address a product slot. Two selectors with the same value are equivalent.
The selector will provide the stable identity value of `ProductSlot`, but it is
not an Entity by itself.

`CoinReserve` is an immutable domain collection modeled as a Value Object. It
represents the quantity available for each accepted coin denomination. Unlike
`Money`, it preserves physical composition because an amount alone cannot prove
that exact change can be returned. Absolute quantity updates create a new
reserve and leave previous instances unchanged.

## Entity

An Entity represents a domain concept with a stable identity and a lifecycle.
Its attributes may change while it remains the same conceptual object. Entity
equality is based on identity rather than all current attributes.

`ProductSlot` is an Entity identified by `ProductSelector`. Its stock may change
while the selector continues to identify the same slot. Its name and price are
immutable in the current scope, while stock changes through controlled domain
behavior.

## Immutability

Immutability means that an object's state cannot change after creation. An
operation that represents a different value creates a new object instead of
modifying the existing instance.

`Coin` is declared as `readonly`, so its denomination cannot be reassigned after
construction. It is also `final`, preventing subclasses from changing its
creation and validation behavior.

## Encapsulation

Encapsulation hides internal state and implementation details behind a
controlled public interface. It is more than using private properties: the
object decides how it can be created and which operations are valid.

`Coin` has a private constructor and is created through `Coin::fromCents()`. Its
denomination can be observed through `cents()`, but it cannot be changed or
assigned directly.

## Invariants

An invariant is a business rule that must always remain true for a domain object
or aggregate to be valid. The domain model protects invariants during creation
and every state-changing operation.

The current `Coin` invariant is:

> A coin denomination must be 5, 10, 25, or 100 cents.

Because this validation happens during construction, an invalid `Coin` instance
cannot exist.

## Domain Exception

A Domain Exception represents the violation of a specific business rule. It
uses domain language and remains independent of HTTP, frameworks, persistence,
and other infrastructure concerns.

`InvalidCoinDenomination` is raised when attempting to create a coin with an
unsupported denomination. A future delivery adapter may translate it into a
transport-specific response, but the domain does not know that mapping.

Database failures, malformed JSON, and network timeouts are technical errors,
not Domain Exceptions.

## Aggregate

An Aggregate is a consistency boundary containing Entities and Value Objects
that must change together. Its Aggregate Root is the only entry point for
operations that can modify the aggregate, and it protects all invariants inside
that boundary.

`VendingMachine` is the Aggregate Root responsible for the product catalog,
inserted coins, and the coin reserve. It currently protects catalog creation,
customer coin operations, and service operations. Purchase behavior remains in
progress.

The aggregate keeps inserted coins as the source of truth and derives the
inserted balance from them. It does not expose mutable `ProductSlot` or
`CoinReserve` instances. Service mutations are rejected while customer coins
are inserted, and that availability rule is evaluated before selector or
quantity validation.

## Domain Service

A Domain Service contains domain behavior that does not naturally belong to a
single Entity or Value Object. It must remain free of application and
infrastructure concerns.

`ExactChangeCalculator` is a stateless Domain Service because it combines a
monetary amount, the physical composition of a limited coin reserve, and the
business policy for selecting optimal change. This behavior does not belong
naturally to a single Entity or Value Object.

The service returns coins ordered from highest to lowest denomination, minimizes
the total number of coins, and raises `ExactChangeUnavailable` when no exact
combination exists. It does not modify the reserve.

## Model Status

| Concept | DDD role | Status |
| --- | --- | --- |
| `Coin` | Value Object | Implemented |
| `InvalidCoinDenomination` | Domain Exception | Implemented |
| `Money` | Value Object | Implemented |
| `NegativeMoneyAmount` | Domain Exception | Implemented |
| `ProductSelector` | Value Object | Implemented |
| `EmptyProductSelector` | Domain Exception | Implemented |
| `ProductSlot` | Entity | Implemented |
| `InvalidProductPrice` | Domain Exception | Implemented |
| `NegativeProductStock` | Domain Exception | Implemented |
| `CoinReserve` | Value Object | Implemented |
| `NegativeCoinQuantity` | Domain Exception | Implemented |
| `ExactChangeCalculator` | Domain Service | Implemented |
| `ExactChangeUnavailable` | Domain Exception | Implemented |
| `VendingMachine` | Aggregate Root | In progress |
| `DuplicateProductSelector` | Domain Exception | Implemented |
| `EmptyProductCatalog` | Domain Exception | Implemented |
| `ProductNotFound` | Domain Exception | Implemented |
| `ServiceUnavailableDuringOperation` | Domain Exception | Implemented |

## Domain Independence

Domain code must not depend on Symfony, PostgreSQL, HTTP, or PHPUnit. Framework
and persistence code may depend on the domain, but dependency direction must
never point from the domain toward infrastructure.
