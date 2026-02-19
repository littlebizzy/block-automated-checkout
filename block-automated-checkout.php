<?php
/*
Plugin Name: Block Automated Checkout
Plugin URI: https://www.littlebizzy.com/plugins/block-automated-checkout
Description: Stops checkout abuse in Woo
Version: 1.2.0
Requires PHP: 7.0
Tested up to: 6.9
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Update URI: false
GitHub Plugin URI: littlebizzy/block-automated-checkout
Primary Branch: master
Text Domain: block-automated-checkout
*/

// prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// override wordpress.org with git updater
add_filter( 'gu_override_dot_org', function( $overrides ) {
	$overrides[] = 'block-automated-checkout/block-automated-checkout.php';
	return $overrides;
}, 999 );

// validate checkout request and block common automation patterns
add_action( 'woocommerce_checkout_process', function() {

	// ensure woocommerce is loaded
	if ( ! function_exists( 'WC' ) ) {
		return;
	}

	// ensure a session and cart object are available
	if ( ! WC()->session || ! WC()->cart ) {
		wc_add_notice(
			__( 'Invalid checkout request.', 'block-automated-checkout' ),
			'error'
		);
		return;
	}

	// ensure the cart is not empty
	if ( WC()->cart->is_empty() ) {
		wc_add_notice(
			__( 'Invalid checkout request.', 'block-automated-checkout' ),
			'error'
		);
		return;
	}

	// block newly created accounts from ordering immediately
	if ( is_user_logged_in() ) {

		// get current user object
		$user = wp_get_current_user();

		if ( $user && ! empty( $user->user_registered ) ) {

			// minimum account age in seconds
			$min_age = (int) apply_filters( 'block_automated_checkout_min_account_age', 300 );

			// calculate account age using site timezone
			$registered_timestamp = strtotime( $user->user_registered );
			$current_timestamp = current_time( 'timestamp' );
			$account_age = $current_timestamp - $registered_timestamp;

			// block checkout if account is too new
			if ( $account_age < $min_age ) {
				wc_add_notice(
					__( 'Please wait a few minutes before placing your first order.', 'block-automated-checkout' ),
					'error'
				);
				return;
			}
		}
	}

} );

// Ref: ChatGPT
