# Changelog

All notable changes to this project will be documented in this file.

## [1.3.0] - 2025-11-18

### Added
- Support for Laravel Cashier 16.x
- New columns in `connected_subscription_items` table:
  - `meter_event_name` (nullable) - For metered billing support
  - `meter_id` (nullable) - For metered billing support
- Compatibility with Stripe API version `2025-07-30.basil`
- `UPGRADE.md` file with detailed upgrade instructions

### Changed
- Updated `laravel/cashier` version constraint to include `^16.0`
- Added `meter_id` cast as `string` in `ConnectSubscriptionItem` model

## [1.2.3]

### Added
- Payment Links functionality for connected accounts
- Creation of both Direct and Destination payment links, including "on behalf of" support
- Support for percentage and fixed application fees on payment links
- Retrieval of all direct payment links for a connected account

## [1.2.2]

### Added
- Functionality for physical terminals and Apple/Android tap to pay
- Adding terminal locations
- Adding a reader and associating it with a terminal
- Handling connection token requests

## [1.1.0]

### Changed
- Compatibility with Cashier 15
- Migrations are no longer auto-published, must now be published using the `vendor:publish` command
- Updated to Stripe API version 2023-10-16

### Removed
- Support for `ignoreMigrations()` - can be safely removed from code

## Earlier Versions

See Git history for changes in versions prior to 1.1.0.

