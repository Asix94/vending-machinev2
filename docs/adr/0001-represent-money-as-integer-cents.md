# ADR 0001: Represent Monetary Values as Integer Cents

- Status: Accepted
- Date: 2026-08-03

## Context

The domain handles coin denominations, product prices, inserted balances, and
change. Decimal values such as `0.10` and `0.65` cannot be represented exactly
by binary floating-point numbers. Rounding errors in equality and arithmetic
would directly affect business decisions such as sufficient balance and exact
change.

## Decision

All monetary values inside the domain are represented as integer cents.

Examples:

| External value | Domain value |
| ---: | ---: |
| `0.05` | `5` |
| `0.65` | `65` |
| `1.00` | `100` |
| `1.50` | `150` |

Parsing and formatting decimal currency values belong at system boundaries.
Domain arithmetic uses integers exclusively.

## Alternatives Considered

### Floating-point numbers

Rejected because they introduce precision and comparison errors for monetary
operations.

### Decimal or arbitrary-precision library

Valid for currencies with complex fractional precision, calculations across
currencies, or advanced financial rules. It is unnecessary for a single
currency whose smallest supported unit is one cent.

### Decimal database type as the domain representation

A database decimal type may be used for persistence where appropriate, but it
cannot define the in-memory domain model and would couple business behavior to
persistence concerns.

## Consequences

- Addition, subtraction, and equality are deterministic.
- Exact-change calculations avoid rounding logic.
- Method names must make the unit explicit, such as `fromCents()` and
  `cents()`.
- Delivery adapters are responsible for converting external decimal values.
- Supporting currencies with different minor units would require revisiting
  this decision.
