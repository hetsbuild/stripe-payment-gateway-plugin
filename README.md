# Stripe Payments — WordPress Payment Gateway Plugin

A WordPress plugin that adds Stripe Checkout to a site. It provides an admin
screen for storing Stripe API credentials, a `[pg_payment]` shortcode that
renders a pay button, and result pages that verify the payment against Stripe
before reporting success.

Built on the official [Stripe PHP SDK](https://github.com/stripe/stripe-php)
(`stripe/stripe-php ^21.3`), which is vendored in `vendor/`.

## Requirements

- WordPress 6.0 or newer
- PHP 7.4 or newer
- A Stripe account

## Installation

1. Copy this repository into your plugins directory as `pg`:

   ```bash
   cd wp-content/plugins
   git clone https://github.com/hetsbuild/payment-gateway-using-stripe.git pg
   ```

   The folder **must** be named `pg`, because the plugin's text domain, class
   prefix (`Pg_*`) and main file (`pg.php`) all match it.

2. Activate **Stripe Payments** from *Plugins* in wp-admin.

3. Go to **Payment Gateway** in the admin menu and fill in your Stripe keys.

The `vendor/` directory is committed, so no `composer install` step is needed.
If you change `composer.json`, run `composer install` and commit the updated
`vendor/` directory.

## Configuration

*Payment Gateway → Stripe Settings* stores three values in the
`pg_stripe_settings` WordPress option:

| Setting           | Description                                                        |
| ----------------- | ------------------------------------------------------------------ |
| **Test Mode**     | Display flag indicating you are using Stripe test keys.            |
| **Publishable Key** | Your Stripe publishable key (`pk_test_…` or `pk_live_…`).         |
| **Secret Key**    | Your Stripe secret key (`sk_test_…` or `sk_live_…`).               |

Use **Test Stripe Connection** to call `Account::retrieve()` and confirm the
secret key is valid before going live.

> The keys are stored as a plain option in `wp_options` — they are not
> encrypted at rest. Keep database access restricted and never commit
> `wp-config.php` or a database dump to a public repository.

## Usage

Create three pages.

### 1. The pay button

Put the shortcode on any page or post:

```
[pg_payment]
```

It renders a "Pay ₹500" button for a test product. Clicking it posts to
`admin-post.php?action=pg_create_checkout`, which creates a Stripe Checkout
Session and redirects the customer to Stripe's hosted page.

### 2. The success page

Create a page at `/payment-success/` containing:

```
[pg_payment_result]
```

The shortcode reads `session_id` from the query string, retrieves the session
from Stripe, and reports one of four states: `paid`, `unpaid`, `invalid` or
`error`. Caching is disabled for this page so a stale response can never
report the wrong outcome.

### 3. The cancellation page

Create a page at `/payment-cancelled/` containing:

```
[pg_payment_cancelled]
```

Stripe redirects here when the customer abandons checkout. The path is set by
the `cancel_url` passed when the session is created.

## How the checkout session is created

`Pg_Public::create_checkout()` verifies the request with
`check_admin_referer()`, reads the secret key from `pg_stripe_settings`, then
calls `Stripe\Checkout\Session::create()` in `payment` mode with a single line
item. Amounts are sent in the currency's minor unit — ₹500 is `50000` paise.

## Project layout

```
pg.php                  Plugin bootstrap, activation/deactivation hooks
uninstall.php            Cleanup on plugin deletion
includes/                Core runtime, hook loader, i18n, activator
admin/                   Settings screen, "Test Stripe Connection" handler
public/                  Shortcodes, checkout creation, result views
languages/               Translation template
vendor/                  Stripe PHP SDK (committed)
```

## Current limitations

The product and price are hardcoded in `Pg_Public::payment_shortcode()` and
`create_checkout()` (Test Product, ₹500 INR) as a working example. There is no
webhook endpoint yet, so orders are only verified when a customer opens the
result page — a reliable production implementation should confirm payment via
a signed Stripe webhook instead.

## License

GPL-2.0-or-later. See [LICENSE.txt](LICENSE.txt).
