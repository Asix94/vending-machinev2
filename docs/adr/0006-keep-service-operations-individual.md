# ADR 0006: Keep Service Operations Individual in the Domain

- Status: Accepted
- Date: 2026-08-03

## Context

A service operator must set product stock and available coin quantities. The
requirements do not state that every product and denomination must be updated
as one atomic domain command. Customer coins may already be inserted when a
service request arrives, and service operations must not interfere with that
active customer operation.

The design must decide whether the aggregate accepts one large batch structure
or exposes focused operations for each product and coin denomination.

## Decision

`VendingMachine` exposes individual service operations:

```text
setProductStock(selector, quantity)
setCoinReserveQuantity(coin, quantity)
```

Both operations:

- establish absolute quantities rather than incrementing them;
- call a shared service-availability guard before any specific validation;
- preserve existing state when validation fails;
- delegate quantity invariants to `ProductSlot` and `CoinReserve`;
- remain unavailable while customer coins are inserted.

Product service locates an existing slot and raises `ProductNotFound` for an
unknown selector. It does not create products. Coin service replaces the
immutable reserve with the new instance returned by `withQuantity()`.

Read-only queries remain available during a customer operation. Returning all
inserted coins ends that operation and enables service again.

A future Application use case may coordinate multiple individual operations and
save the aggregate once. Transaction and batch orchestration belong to the
Application and persistence boundaries unless a new business rule explicitly
requires an atomic service batch inside the aggregate.

## Error Precedence

Service availability is checked first:

```text
active customer operation
    -> ServiceUnavailableDuringOperation
    -> do not inspect selector or quantity
```

This rule gives deterministic behavior and prevents a blocked operation from
evaluating details it cannot apply.

## Alternatives Considered

### One batch method in VendingMachine

Rejected because no current business rule requires all service updates to
succeed or fail as one aggregate command. A batch DTO would add validation,
mapping, and rollback concerns before the Application layer exists.

### Update ProductSlot and CoinReserve directly

Rejected because it would bypass the Aggregate Root and allow service changes
during an active customer operation.

### Validate selector and quantity before service availability

Rejected because blocked service commands should not evaluate command-specific
details. The result would depend on incidental validation order and could expose
catalog information unnecessarily.

## Consequences

- Domain operations remain focused and independently testable.
- Product and reserve invariants are reused instead of duplicated.
- Error precedence is deterministic.
- The Aggregate Root remains the only mutation entry point.
- A batch request requires Application-level orchestration.
- If a future requirement demands atomic service batches as a business rule,
  the aggregate API must be extended deliberately.
