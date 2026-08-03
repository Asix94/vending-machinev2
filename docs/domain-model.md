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

## Entity

An Entity represents a domain concept with a stable identity and a lifecycle.
Its attributes may change while it remains the same conceptual object. Entity
equality is based on identity rather than all current attributes.

A `ProductSlot` will be an Entity because its stock may change while its unique
selector continues to identify the same slot.

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

`VendingMachine` is planned as the Aggregate Root responsible for inserted
coins, product stock, the coin reserve, and atomic purchases. This decision will
be validated as the model evolves rather than implemented in advance.

## Domain Service

A Domain Service contains domain behavior that does not naturally belong to a
single Entity or Value Object. It must remain free of application and
infrastructure concerns.

The exact-change algorithm is a candidate Domain Service because it calculates
a result from an amount and a limited coin reserve. The abstraction will only
be introduced when that behavior is implemented.

## Model Status

| Concept | DDD role | Status |
| --- | --- | --- |
| `Coin` | Value Object | Implemented |
| `InvalidCoinDenomination` | Domain Exception | Implemented |
| `Money` | Value Object | Implemented |
| `NegativeMoneyAmount` | Domain Exception | Implemented |
| `ProductSelector` | Value Object | Implemented |
| `EmptyProductSelector` | Domain Exception | Implemented |
| `ProductSlot` | Entity | Planned |
| `VendingMachine` | Aggregate Root | Planned |
| Exact-change calculator | Domain Service candidate | Planned |

## Domain Independence

Domain code must not depend on Symfony, PostgreSQL, HTTP, or PHPUnit. Framework
and persistence code may depend on the domain, but dependency direction must
never point from the domain toward infrastructure.
