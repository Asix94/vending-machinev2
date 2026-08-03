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
- Domain Exceptions;
- repository interfaces for Aggregate Roots.

It contains no Symfony, HTTP, PostgreSQL, or PHPUnit dependencies. Repository
interfaces express aggregate retrieval and persistence contracts without
describing a database or mapping strategy.

## Application Layer

The Application layer coordinates use cases such as inserting a coin, returning
inserted coins, servicing stock, and purchasing a product. `InsertCoinUseCase`
and `ReturnInsertedCoinsUseCase` coordinate customer coin operations.
`SetProductStockUseCase` and `SetCoinReserveQuantityUseCase` coordinate the
individual SERVICE operations without duplicating the business rules protected
by `VendingMachine`.

Application code may:

- load domain state through repository interfaces defined by the Domain;
- invoke domain behavior;
- persist resulting state;
- define use-case inputs and application responses;
- establish transaction boundaries.

It must not contain the business rules owned by the domain model.

## Ports

Ports are interfaces that define how the system communicates across its
boundaries.

- Inbound ports expose application use cases to delivery adapters.
- Repository interfaces for Aggregate Roots belong to the Domain because they
  express access to domain aggregates.
- Other outbound ports belong to the layer whose policy requires the external
  capability, normally Application.

Ports are introduced from concrete use-case needs. Generic repositories,
service interfaces, and buses are not created without an actual consumer.

## Adapters

Inbound adapters translate an external request into an application call. An
HTTP controller, for example, validates transport syntax, invokes a use case,
and maps its outcome to an HTTP response.

Outbound adapters implement repository interfaces or other output ports.
`InMemoryVendingMachineRepository` is the first local persistence adapter. It
keeps state for the lifetime of its PHP object and can later be replaced by a
PostgreSQL adapter without changing Application or Domain.

## Dependency Direction

Allowed dependency direction:

```text
Adapters -> Application -> Domain
Infrastructure ------------> Domain
```

Application consumes the repository abstraction defined beside the Aggregate
Root, while Infrastructure provides the concrete adapter:

```text
Application -> Domain repository interface <- Infrastructure
                       |
                       v
                Domain Aggregate Root
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

Purchase behavior operates on generic product slots rather than branching
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

Application depends on domain abstractions rather than concrete persistence.
Infrastructure depends on and implements those abstractions. Application owns
output ports only when they do not represent domain aggregate repositories.

## Design Patterns

Patterns are used to solve demonstrated design problems, not as implementation
goals.

| Pattern | Intended use | Status |
| --- | --- | --- |
| Value Object | Money, coins, selectors, immutable coin composition, and purchase outcomes | `Coin`, `Money`, `ProductSelector`, `CoinReserve`, and `PurchaseResult` implemented |
| Entity | Track a product slot by stable selector while stock changes | `ProductSlot` implemented |
| Domain Service | Calculate optimal exact change from a limited reserve | `ExactChangeCalculator` implemented |
| Aggregate | Protect catalog, customer operation, reserve, service, and atomic purchases | `VendingMachine` implemented |
| Repository | Retrieve and persist Aggregate Roots through a Domain-owned interface | `VendingMachineRepository` implemented; durable implementation planned |
| Use Case | Coordinate application actions without owning domain rules | Customer coin and individual SERVICE use cases implemented |
| Strategy | Allow change-calculation policies to vary if needed | Deferred until a second policy exists |
| Adapter | Connect external mechanisms to inward-facing interfaces | In-memory persistence implemented; HTTP and PostgreSQL planned |

## Current Structure

Only implemented concepts have directories:

```text
src/
└── VendingMachine/
    ├── Application/
    │   ├── InsertCoin/
    │   │   └── InsertCoinUseCase.php
    │   ├── ReturnInsertedCoins/
    │   │   └── ReturnInsertedCoinsUseCase.php
    │   ├── SetCoinReserveQuantity/
    │   │   └── SetCoinReserveQuantityUseCase.php
    │   └── SetProductStock/
    │       └── SetProductStockUseCase.php
    ├── Domain/
    │   ├── Coin.php
    │   ├── CoinReserve.php
    │   ├── Money.php
    │   ├── ProductSelector.php
    │   ├── ProductSlot.php
    │   ├── PurchaseResult.php
    │   ├── VendingMachine.php
    │   ├── Exception/
    │   │   ├── DuplicateProductSelector.php
    │   │   ├── EmptyProductCatalog.php
    │   │   ├── EmptyProductSelector.php
    │   │   ├── ExactChangeUnavailable.php
    │   │   ├── InsufficientBalance.php
    │   │   ├── InsufficientCoinQuantity.php
    │   │   ├── InvalidCoinDenomination.php
    │   │   ├── InvalidProductPrice.php
    │   │   ├── NegativeCoinQuantity.php
    │   │   ├── NegativeMoneyAmount.php
    │   │   ├── NegativeProductStock.php
    │   │   ├── ProductNotFound.php
    │   │   ├── ProductOutOfStock.php
    │   │   └── ServiceUnavailableDuringOperation.php
    │   ├── Repository/
    │   │   └── VendingMachineRepository.php
    │   └── Service/
    │       └── ExactChangeCalculator.php
    └── Infrastructure/
        └── Persistence/
            └── InMemoryVendingMachineRepository.php
```

HTTP delivery and durable persistence structures will be added with their first
concrete use cases.

## Deferred Concerns

The initial version processes commands sequentially for one machine. Multiple
machines, concurrent sessions, authentication, and distributed coordination are
outside the current scope. Domain operations remain atomic so concurrency
controls can be added later at application and persistence boundaries.
