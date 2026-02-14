<?php
/*
Plugin Name: Block Automated Checkout
Plugin URI: https://www.littlebizzy.com/plugins/block-automated-checkout
Description: Stops checkout abuse in Woo
Version: 1.1.0
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

// block checkout if no valid woo session or cart exists
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

} );

// Ref: ChatGPT
