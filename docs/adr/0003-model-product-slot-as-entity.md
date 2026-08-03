# ADR 0003: Model Product Slot as an Entity

- Status: Accepted
- Date: 2026-08-03

## Context

The machine contains predefined product slots. Each slot has a unique selector,
a product name, a price, and an available stock quantity. The stock changes when
the machine is serviced or sells a product, but these changes do not create a
different slot.

The model must distinguish two slots even if they temporarily have the same
name, price, and stock. It must also preserve the rule that stock cannot become
negative and that a product price must be greater than zero.

## Decision

`ProductSlot` is modeled as an Entity identified by `ProductSelector`.

- `ProductSelector` provides its stable identity value.
- Name and price are immutable in the current scope.
- New slots start with zero stock.
- `setStock()` establishes an absolute quantity instead of incrementing it.
- A negative stock is rejected before state is modified.
- A product price must be greater than zero.
- Product creation, removal, and price changes are outside the current scope.

The Entity is final but not readonly. Its identity and descriptive attributes
are readonly, while stock is mutable through controlled domain behavior.

## Alternatives Considered

### Model Product Slot as a Value Object

Rejected because stock changes over the slot lifecycle while the selector must
continue identifying the same conceptual object. Replacing the entire slot for
every stock change would hide its identity semantics.

### Use Product Name as Identity

Rejected because display names may not be unique or stable. A dedicated
`ProductSelector` communicates the identity rule explicitly.

### Store Product Data as Primitive Arrays

Rejected because arrays cannot protect positive-price and non-negative-stock
invariants and would spread validation throughout callers.

### Increment Stock During Service

Rejected because service operations were defined to set absolute quantities.
Absolute updates are deterministic and idempotent when the same command is
repeated.

## Consequences

- Stock can change without changing slot identity.
- Invalid stock and price states are rejected by the Entity.
- The future aggregate can locate slots by `ProductSelector`.
- Persistence must restore the same selector and current stock for a slot.
- Supporting price changes later will require an explicit domain operation and
  its associated business rules.
