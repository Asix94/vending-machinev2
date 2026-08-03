# Domain Rules

## Purpose

This document is the source of truth for the vending machine domain. It records
the business rules agreed during development without prescribing technical
implementation details.

## Scope

The system models one physical vending machine with one active customer
operation at a time. Actions are processed sequentially in the initial version.

The initial product catalog contains:

| Product | Price |
| --- | ---: |
| Water | 65 cents |
| Juice | 100 cents |
| Soda | 150 cents |

## Ubiquitous Language

- **Coin**: A physical coin accepted by the machine.
- **Accepted denomination**: A coin value the machine accepts: 5, 10, 25, or
  100 cents.
- **Inserted coins**: Coins held temporarily during the current customer
  operation.
- **Coin reserve**: Coins owned by the machine and available to return as
  change.
- **Product slot**: A predefined product identified by a unique selector, with
  a name, price, and available quantity.
- **Inserted balance**: The total value of all currently inserted coins.
- **Change**: An exact combination of coins returned after a successful
  purchase.
- **Service operation**: An operation that sets product stock or coin reserve
  quantities.

## Accepted Rules

### Money and coins

- Monetary values are represented as integer cents. Floating-point values are
  not used.
- The accepted denominations are 5, 10, 25, and 100 cents.
- An unsupported denomination is rejected without changing the machine state.
- Every accepted denomination, including 100 cents, can be returned.
- Inserted coins are kept separate from the coin reserve.

### Products

- Product behavior is generic and must not contain product-specific purchase
  conditions.
- Water, Juice, and Soda are predefined products in the initial catalog.
- A product selector is unique.
- A product price must be greater than zero.
- Product stock cannot be negative.
- Products start with zero stock.
- Creating or removing products through a service operation is not supported.

### Service operations

- A service operation sets an absolute quantity; it does not increment the
  existing quantity.
- Repeating the same service operation produces the same resulting state.
- Product stock can only be set for an existing product selector.
- The coin reserve can only be set for an accepted denomination.
- The coin reserve starts empty.
- Service operations are rejected while there are inserted coins.
- Service availability is checked before product lookup or quantity validation.
- Product and coin quantities are updated through individual domain operations.

### Returning inserted coins

- Returning coins returns exactly the coins inserted during the current
  operation.
- Returning coins clears the inserted coins and inserted balance.
- Returning coins when no money has been inserted returns an empty collection
  and does not modify the state.
- Returning inserted coins does not use or modify the coin reserve.

### Purchasing

- If the inserted balance is lower than the product price, the purchase is
  rejected and the inserted coins are preserved.
- If the selected product does not exist, the purchase is rejected and the
  inserted coins are preserved.
- If the selected product is out of stock, the purchase is rejected and the
  inserted coins are preserved.
- If exact change cannot be returned, the purchase is rejected and the
  inserted coins are preserved.
- Change is calculated only from the coin reserve that existed before the
  purchase. Inserted coins cannot be used to make change for that purchase.
- Change must be exact and use the smallest possible number of coins.
- If multiple minimum-coin combinations exist, higher denominations are
  preferred.
- The domain returns selected change coins ordered from highest to lowest
  denomination.
- A successful purchase decreases product stock by one.
- After a successful purchase, inserted coins are transferred to the coin
  reserve, returned change is removed from the reserve, and the inserted
  balance is cleared.
- A purchase is atomic: either all successful state changes are applied or no
  state is changed.

## Initial Constraints

- The initial version models one machine.
- The machine has one shared set of inserted coins.
- Commands are assumed to be processed sequentially.

## Out of Scope

- Concurrent command handling.
- Multiple simultaneous customer sessions.
- Users or customer accounts.
- Authentication and authorization.
- Creating, deleting, or changing the price of products through service mode.
- Dynamically configuring accepted coin denominations.

## Pending Decisions

- Delivery mechanism, such as an HTTP API or command-line interface.
- Persistence strategy and repository implementation.
- Application command and response models.
- Error representation and transport-level status mapping.
- Ordering of coins in external responses.
- Detailed architecture and package boundaries beyond the domain layer.

## Decision Log

1. Failed purchases preserve inserted coins so the customer can continue the
   operation or request a refund.
2. All accepted denominations can be returned, including the 100-cent coin.
3. Service operations set absolute quantities to remain idempotent.
4. Service operations update existing products but do not create new ones, in
   accordance with the current requirements.
5. Change uses only the pre-existing coin reserve, keeping inserted coins in
   temporary custody until a purchase succeeds.
6. Exact change minimizes the number of returned coins and uses a deterministic
   higher-denomination preference for ties.
7. Concurrency is deferred from the initial version while domain operations
   remain atomic to allow future concurrency controls.
8. Exact change uses a bounded recursive search because the domain has four
   fixed denominations and greedy selection can miss valid combinations.
9. Service operations are individual aggregate methods; a future Application
   use case may coordinate multiple updates as a transactional batch.
10. An active customer operation rejects SERVICE before selector and quantity
    validation, giving service availability deterministic error precedence.
