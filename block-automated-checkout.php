<?php
/*
Plugin Name: Block Automated Checkout
Plugin URI: https://www.littlebizzy.com/plugins/block-automated-checkout
Description: Stops checkout abuse in Woo
Version: 1.0.0
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

// block checkout if woo checkout nonce is missing or invalid
add_action( 'woocommerce_checkout_process', function() {

	// ensure the woo checkout nonce field exists in the request
	if ( empty( $_POST['woocommerce-process-checkout-nonce'] ) ) {
		wc_add_notice(
			__( 'Invalid checkout request.', 'block-automated-checkout' ),
			'error'
		);
		return;
	}

	// sanitize the submitted nonce value before validation
	$nonce = sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) );

	// verify the nonce against expected woo checkout action
	if ( ! wp_verify_nonce( $nonce, 'woocommerce-process-checkout' ) ) {
		wc_add_notice(
			__( 'Invalid checkout request.', 'block-automated-checkout' ),
			'error'
		);
		return;
	}

} );

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
