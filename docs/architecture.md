# Architecture

## Purpose

This document describes the architectural direction of the project. The design
is evolutionary: abstractions are introduced when required by implemented use
cases, not created in advance to fill a predefined folder structure.

## Architectural Style

The project follows Domain-Driven Design with a hexagonal architecture. The
domain model is at the center and remains independent from frameworks,
databases, delivery mechanisms, and external services.

```text
                 Inbound adapters
                HTTP, CLI, tests
                       |
                       v
             Application use cases
                       |
                       v
                  Domain model
                       ^
                       |
              Output port interfaces
                       ^
                       |
                Outbound adapters
             PostgreSQL, external APIs
```

Dependencies point inward. Inner layers never import outer layers.

## Domain Layer

The Domain layer contains business concepts and rules:

- Value Objects;
- Entities;
- Aggregates;
- Domain Services;
- Domain Exceptions.

It contains no Symfony, HTTP, PostgreSQL, or PHPUnit dependencies. `Coin` and
`InvalidCoinDenomination` are the first implemented domain types.

## Application Layer

The Application layer will coordinate use cases such as inserting a coin,
returning inserted coins, servicing stock, and purchasing a product.

Application code may:

- load domain state through output ports;
- invoke domain behavior;
- persist resulting state;
- define input commands and application responses;
- establish transaction boundaries.

It must not contain the business rules owned by the domain model.

## Ports

Ports are interfaces that define how the application communicates across its
boundaries.

- Inbound ports expose application use cases to delivery adapters.
- Outbound ports describe capabilities required from persistence or external
  systems.

Ports are introduced from concrete use-case needs. Generic repositories,
service interfaces, and buses are not created without an actual consumer.

## Adapters

Inbound adapters translate an external request into an application call. An
HTTP controller, for example, validates transport syntax, invokes a use case,
and maps its outcome to an HTTP response.

Outbound adapters implement output ports. A PostgreSQL repository will map
persisted records to domain objects and save aggregate state without leaking
database concepts into the domain.

## Dependency Direction

Allowed dependency direction:

```text
Adapters -> Application -> Domain
```

An outbound adapter may also depend on an output port declared by the
Application layer:

```text
PostgreSQL adapter -> Repository port -> Application and Domain
```

Forbidden dependencies include:

- Domain importing Symfony classes;
- Domain querying PostgreSQL;
- Application constructing concrete database clients;
- Controllers implementing purchase rules;
- Repositories deciding whether a coin denomination is valid.

## SOLID Principles

### Single Responsibility Principle

Each type has one reason to change. `Coin` protects coin denomination rules; it
does not calculate change, persist itself, or map HTTP requests.

### Open/Closed Principle

Purchase behavior will operate on generic product slots rather than branching
on Water, Juice, or Soda. New catalog entries should not require rewriting the
purchase algorithm.

### Liskov Substitution Principle

Implementations of an output port must honor the same behavioral contract. An
in-memory repository and a PostgreSQL repository must be interchangeable from
the application's perspective.

### Interface Segregation Principle

Ports should expose only the operations required by their consumers. Large
generic service interfaces are avoided.

### Dependency Inversion Principle

Application policy depends on abstractions it owns, while infrastructure
depends on and implements those abstractions.

## Design Patterns

Patterns are used to solve demonstrated design problems, not as implementation
goals.

| Pattern | Intended use | Status |
| --- | --- | --- |
| Value Object | Money, coins, selectors, and immutable coin composition | `Coin`, `Money`, `ProductSelector`, and `CoinReserve` implemented |
| Entity | Track a product slot by stable selector while stock changes | `ProductSlot` implemented |
| Aggregate | Protect machine consistency and atomic purchases | Planned |
| Repository | Persist and restore aggregate state | Planned |
| Command/Handler | Represent and execute application actions | Planned |
| Strategy | Allow change-calculation policies to vary if needed | Candidate |
| Adapter | Connect HTTP and PostgreSQL to application ports | Planned |

## Current Structure

Only implemented concepts have directories:

```text
src/
└── VendingMachine/
    └── Domain/
        ├── Coin.php
        ├── CoinReserve.php
        ├── Money.php
        ├── ProductSelector.php
        ├── ProductSlot.php
        └── Exception/
            ├── EmptyProductSelector.php
            ├── InvalidCoinDenomination.php
            ├── InvalidProductPrice.php
            ├── NegativeCoinQuantity.php
            ├── NegativeMoneyAmount.php
            └── NegativeProductStock.php
```

Application and adapter structures will be added with their first concrete use
cases.

## Deferred Concerns

The initial version processes commands sequentially for one machine. Multiple
machines, concurrent sessions, authentication, and distributed coordination are
outside the current scope. Domain operations remain atomic so concurrency
controls can be added later at application and persistence boundaries.
