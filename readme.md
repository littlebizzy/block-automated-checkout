# Block Automated Checkout

Stops checkout abuse in Woo

## Changelog

### 1.2.0
- added minimum account age requirement before checkout (default 300 seconds)
- blocks immediate first-order attempts from newly registered accounts
- improves protection against register-and-test card validation abuse

### 1.1.0
- removed strict WooCommerce checkout nonce enforcement
- relies on WooCommerce core nonce validation
- retains session and cart validation safeguards
- improves compatibility with cached or stale checkout sessions

### 1.0.0
- initial release
- supports PHP 7.0 to 8.3
- supports Git Updater
- `Tested up to: 6.9`
- blocks checkout requests without a valid WooCommerce checkout nonce
- blocks checkout requests without an active WooCommerce session or cart
- mitigates scripted, stateless, non-interactive and non-browser checkout attempts
- uses native WooCommerce notices for failed requests
