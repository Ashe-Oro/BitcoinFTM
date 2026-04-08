# BitcoinFTM

BitcoinFTM (Bitcoin Financial Trade Manager) is a legacy PHP application for monitoring exchange data, estimating cross-exchange arbitrage opportunities, and simulating trading activity per client account.

The project appears to have been built around 2013-2014 for the now-defunct GetDemCoins/Lit2Bit environment.

## Status

This repository is a historical codebase, not a production-ready modern trading system.

- Language/runtime is legacy PHP with `mysql_*` APIs.
- Several exchange adapters target APIs that are now deprecated or offline.
- "Live" trading paths are incomplete or intentionally disabled in multiple adapters.
- Sensitive credentials are currently committed in tracked files and should be rotated immediately.

## Core Capabilities

- Session-based login and dashboard (`btcftm/index.php`, `btcftm/controls.php`)
- Multi-panel UI for:
  - market snapshots
  - order books
  - spread matrix
  - charting
  - buy/sell simulation
  - transfer simulation
  - arbitrage simulation
  - portfolio/account views
- Arbitrage engine that:
  - loads active markets from DB
  - builds a market order book (MOB)
  - computes best bid/ask spread opportunities
  - fans opportunities to observers/bots
- JSON endpoints used by frontend polling:
  - `btcftm/master-json.php`
  - `btcftm/test-chart-json.php`
  - `btcftm/ajax-orderbooks.php`
  - `btcftm/ajax-market-buysell.php`
  - `btcftm/ajax-transfer.php`
  - `btcftm/ajax-arbitrage.php`
- Cron scripts for market ingestion and history resampling:
  - `cron/add-live-data.php`
  - `cron/add-orderbook-data.php`
  - `cron/resample-live-data.php`

## High-Level Architecture

1. Browser loads `btcftm/controls.php` after login.
2. `controls.php` bootstraps `Arbitrage` with historical market mode and emits initial JSON.
3. Frontend (`btcftm/js/*.js`) polls `master-json.php` and updates panels every ~15s.
4. Order books are refreshed via `ajax-orderbooks.php` and weighted depth calculations are performed in JS.
5. Trade actions call AJAX endpoints, which mutate balances in `privatemarkets` and append to `transactions`.
6. Background cron jobs ingest ticker/orderbook data into exchange tables and resample into period tables.

## Directory Guide

- `btcftm/`: main web app
  - `core/`: arbitrage engine, market adapters, observers, DB-driven domain objects
  - `partials/`: panel templates
  - `js/`, `css/`, `jquery/`: frontend code and vendor libraries
  - `json/`: JSON writers/helpers
- `cron/`: ingestion + resampling entrypoints and sample task wrappers
- `classes/`: exchange ticker/orderbook fetch helpers used by cron utilities
- `utils/`: DB + exchange utility classes used by cron scripts
- `archive/`: older experiments and historical scripts

## Data Model Expectations

There are no migrations in this repository. The application expects an existing MySQL schema with at least:

- `clients`
- `traderbots`
- `markets`
- `currencies`
- `privatemarkets`
- `transactions`
- `honeypot`

Market history tables are exchange-specific and convention-based, e.g.:

- `<exchange>_ticker`
- `<exchange>_orderbook`
- `<exchange>_history_<scale>`

Examples of scales used by cron/task files:

- `half_hours`
- `hours`
- `days`
- `weeks`
- `biweeks`
- `months`

Examples of exchange prefixes used in code:

- `mtgox`
- `bitstamp`
- `btce_btcusd`
- `bitfinex_btcusd`
- `kraken_btcusd`
- `cryptotrade_btcusd`
- `campbx_btcusd`
- `bitfinex_ltcbtc`
- `btce_ltcbtc`

## Local Development (Legacy)

### Prerequisites

- PHP 5.6 era runtime (or older PHP that still supports `mysql_*` extension)
- MySQL/MariaDB with pre-seeded schema/data
- PHP extensions: curl + mysql

Modern PHP 7/8 will fail without significant refactoring because `mysql_*` APIs were removed.

### Configure

1. Update DB credentials in `btcftm/core/config/db_config.php`.
2. Review runtime flags in `btcftm/core/config/config.php`:
   - `live` (keep `0` for simulation)
   - `observers`
   - logging flags
   - honeypot rate
3. Ensure market/currency/client rows exist and are marked active where needed.

### Run

From repository root:

```bash
php -S 127.0.0.1:8000 -t btcftm
```

Open:

- `http://127.0.0.1:8000/index.php`

Use a valid row in `clients` to sign in.

## Cron / Data Pipeline

The project includes shell wrappers in `cron/tasks/` that call hosted cron endpoints.

Typical flow:

1. `add-live-data.php` writes latest tickers to `<exchange>_ticker`.
2. `add-orderbook-data.php` writes compact order book snapshots to `<exchange>_orderbook`.
3. `resample-live-data.php` rolls raw ticker data into period tables (`history_hours`, `history_days`, etc.).

`cron/tasks/calls.txt` documents intended schedule frequencies.

## Detailed Review Notes

### Security and Ops Risks

- Hardcoded credentials and API secrets exist in tracked files (`btcftm/config.php`, `btcftm/core/config/db_config.php`).
- Login uses MD5 password checks and direct SQL interpolation.
- Most SQL queries are string-concatenated with minimal sanitization.
- "Live" trading code paths are inconsistent and in places intentionally blocked.

### Technical Debt

- Uses deprecated `mysql_*` API and global mutable state.
- Missing schema migrations and test automation.
- Several classes contain TODOs or incomplete methods.
- Many exchange integrations target legacy endpoints that may no longer respond.

## Suggested Modernization Path

1. Remove and rotate all exposed secrets immediately.
2. Migrate DB layer to PDO or MySQLi with prepared statements.
3. Add reproducible schema migrations and seed data.
4. Split simulation and live-trading responsibilities.
5. Add integration tests around pricing math and transaction writes.
6. Re-validate every exchange adapter against current APIs.

## License

No explicit project license file is present in this repository.

If you intend to reuse or distribute this code, add a license first.
