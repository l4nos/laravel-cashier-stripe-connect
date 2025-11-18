# Upgrade Guide

## Upgrading to 1.3.0 (Cashier 16.x)

### Major Changes

Laravel Cashier Stripe Connect 1.3.0 introduces compatibility with Laravel Cashier 16.x, which brings support for Stripe's new metered billing APIs (Stripe Billing Meters).

### Upgrade Steps

#### 1. Update Your Dependencies

Update your `composer.json` file:

```bash
composer update
```

#### 2. Publish and Run Migrations

Two new columns have been added to the `connected_subscription_items` table:
- `meter_event_name` (nullable) - The meter event name for metered billing
- `meter_id` (nullable) - The Stripe meter identifier

Publish the new migrations:

```bash
php artisan vendor:publish --tag="cashier-connect-migrations" --force
```

Run the migrations:

```bash
php artisan migrate
```

#### 3. Update Your Stripe API Version

After deploying this update to production, log in to your Stripe dashboard and update your API version to `2025-07-30.basil` to take full advantage of the new features.

**Important:** Test in a staging environment first. Older Stripe accounts may encounter compatibility issues with the new Basil APIs or require manual API key upgrades.

### Database Changes

The `connected_subscription_items` table now has two new columns:

| Column | Type | Description |
|---------|------|-------------|
| `meter_event_name` | string (nullable) | Event name for metered billing |
| `meter_id` | string (nullable) | Stripe meter ID |

### Compatibility

- Laravel Cashier: ^16.0
- Stripe API: 2025-07-30.basil
- PHP: ^7.4\|^8.1\|^8.2\|^8.3\|^8.4
- Laravel: ^9.0\|^10.0\|^11.0\|^12.0

### Notes

- Changes primarily concern metered billing
- If you don't use metered billing, the new columns will remain NULL
- Backward compatibility with previous Cashier versions (12.x, 13.x, 14.x, 15.x) is maintained
- No existing code modifications are required if you don't use metered billing

