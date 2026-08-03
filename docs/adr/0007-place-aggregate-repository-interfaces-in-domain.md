# ADR 0007: Place Aggregate Repository Interfaces in Domain

- Status: Accepted
- Date: 2026-08-03

## Context

The first Application use case must retrieve a `VendingMachine`, invoke domain
behavior, and persist the resulting aggregate. The initial implementation keeps
state in memory, and a durable mechanism may eventually use PostgreSQL. Neither
Application nor Domain should depend on PDO, database schemas, or mapping
details.

The design must decide which layer owns the repository interface implemented by
Infrastructure and consumed by Application.

## Decision

Repository interfaces for Aggregate Roots belong to the Domain layer. The
`VendingMachineRepository` interface will therefore live under:

```text
src/VendingMachine/Domain/Repository/VendingMachineRepository.php
```

Application use cases depend on this interface to retrieve and save the
aggregate. Concrete implementations belong to Infrastructure. The first local
adapter lives under:

```text
src/VendingMachine/Infrastructure/Persistence/InMemoryVendingMachineRepository.php
```

It preserves the aggregate only for the lifetime of the repository object. It
does not provide durable state between independent PHP processes or requests.
A future PostgreSQL implementation can replace it through the same interface.

The repository contract remains specific to `VendingMachine`. It will expose
only operations required by implemented use cases and will not inherit from a
generic CRUD repository. The current single-machine scope does not require a
machine identifier; this decision can be revisited if multiple machines enter
the domain.

The dependency direction is:

```text
Application -> Domain repository interface <- Infrastructure
```

Domain remains independent from frameworks and persistence technologies.

## Alternatives Considered

### Place the repository interface in Application

Valid in ports-and-adapters architectures, particularly when the abstraction
exists only for one use case. Rejected here because aggregate repositories are
treated as domain contracts and the project follows the DDD convention of
placing their interfaces beside the model they retrieve.

### Place the interface and implementation in Infrastructure

Rejected because Application would depend on a concrete outer layer, violating
Dependency Inversion and making use-case tests depend on persistence details.

### Introduce a generic repository interface

Rejected because generic CRUD operations would not express the aggregate's
actual lifecycle and would add methods with no current consumer.

## Consequences

- Domain owns the abstraction for retrieving and saving `VendingMachine`.
- Application can coordinate use cases without importing Infrastructure.
- Infrastructure depends inward and can provide in-memory, PostgreSQL, or other
  adapters.
- The first local adapter supports Application development without introducing
  database mapping prematurely.
- In-memory state is process-local and is not durable across requests.
- Repository contracts remain focused on Aggregate Roots rather than storage
  operations.
- Non-repository output ports may still be owned by Application when its policy
  requires an external capability.
