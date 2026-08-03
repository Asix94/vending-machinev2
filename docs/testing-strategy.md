# Testing Strategy

## Purpose

Tests provide executable evidence of business behavior and allow the design to
evolve safely. The strategy favors fast domain tests and adds infrastructure
tests only when infrastructure exists.

## Test-Driven Development

Domain behavior is developed using the Red-Green-Refactor cycle:

1. **Red**: write a small test that fails for the expected reason.
2. **Green**: implement the minimum behavior required for the test to pass.
3. **Refactor**: improve the design while keeping all tests green.

A red test is only useful when it fails because the requested behavior is
missing. Namespace mistakes, syntax errors, and broken test configuration must
be fixed before treating a failure as the Red phase.

## Test Design

Tests should:

- describe observable behavior and domain rules;
- avoid coupling to private implementation details;
- contain a clear action and assertion;
- verify that rejected operations do not produce invalid state;
- use Data Providers when the same rule applies to multiple inputs;
- remain deterministic and independent from execution order.

Tests should not exist only to execute lines of code. Coverage is useful as a
diagnostic metric, not as a replacement for meaningful assertions.

## Test Levels

### Unit tests

Unit tests exercise domain objects without Symfony, a database, the network, or
the filesystem. They are the primary feedback loop while building the model.

Current example:

- `CoinTest` verifies every accepted denomination and rejects unsupported
  denominations.
- `MoneyTest` verifies positive and zero amounts and rejects negative amounts.
- `ProductSelectorTest` verifies selector creation and rejects an empty value.
- `ProductSlotTest` verifies entity creation, positive prices, absolute stock
  updates, and state preservation when negative stock is rejected.
- `CoinReserveTest` verifies an empty initial reserve, immutable absolute
  quantity updates, zero quantities, available denominations, and rejection of
  negative quantities.
- `ExactChangeCalculatorTest` verifies zero change, exact denominations,
  multiple-coin combinations, bounded inventory where greedy selection fails,
  minimum coin count, deterministic descending order, and unavailable exact
  change.
- `VendingMachineCreationTest` verifies catalog creation, empty-catalog
  rejection, duplicate selectors, and initial customer and reserve state.
- `VendingMachineCoinOperationTest` verifies inserted balance, insertion order,
  exact coin return, customer-operation cleanup, and reserve isolation.
- `VendingMachineServiceTest` verifies absolute product and reserve updates,
  unknown products, service availability precedence, state preservation, and
  service reactivation after returning inserted coins.

### Application tests

Application tests will exercise use cases through their public interfaces. They
will use in-memory adapters for output ports, allowing orchestration and error
handling to be tested without PostgreSQL.

### Integration tests

Integration tests will verify concrete adapters, such as repository behavior
against PostgreSQL. They will focus on mapping, constraints, and transaction
behavior rather than repeating every domain rule.

### End-to-end tests

End-to-end tests will cover a small number of critical flows through the final
delivery mechanism. They provide confidence that container communication,
request mapping, application orchestration, persistence, and responses work
together.

## Directory Structure

The test suite will evolve toward:

```text
tests/
├── Unit/
├── Application/
├── Integration/
└── EndToEnd/
```

Only directories with actual tests should be added. Empty layers are not
created in advance.

## Running Tests

Run the complete suite inside the PHP container:

```bash
docker compose exec php vendor/bin/phpunit
```

Run a focused test during a TDD cycle:

```bash
docker compose exec php vendor/bin/phpunit --filter CoinTest
```

The complete suite must pass without warnings before committing an increment.
