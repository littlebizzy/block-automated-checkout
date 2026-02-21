<?php
/*
Plugin Name: Block Automated Checkout
Plugin URI: https://www.littlebizzy.com/plugins/block-automated-checkout
Description: Stops checkout abuse in Woo
Version: 1.4.0
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

// validate checkout request and block automation patterns
add_action( 'woocommerce_checkout_process', function() {

	// ensure a valid woo session and cart exist
	if ( empty( WC()->session ) || empty( WC()->cart ) ) {
		wc_add_notice(
			__( 'Invalid checkout request.', 'block-automated-checkout' ),
			'error'
		);
		return;
	}

	// ensure woo cart contains items
	if ( WC()->cart->is_empty() ) {
		wc_add_notice(
			__( 'Invalid checkout request.', 'block-automated-checkout' ),
			'error'
		);
		return;
	}

	// apply restrictions only to logged in users
	if ( is_user_logged_in() ) {

		// get current user object
		$user = wp_get_current_user();

		// minimum account age in seconds
		$min_age = (int) apply_filters( 'block_automated_checkout_min_account_age', 300 );

		// parse registration timestamp as utc
		$registered_timestamp = strtotime( $user->user_registered . ' UTC' );

		// continue only if registration timestamp is valid
		if ( false !== $registered_timestamp ) {

			// calculate account age in utc
			$current_timestamp = time();
			$account_age = $current_timestamp - $registered_timestamp;

			// block checkout if account is too new
			if ( $account_age < $min_age ) {
				wc_add_notice(
					__( 'Please wait a few minutes before placing an order.', 'block-automated-checkout' ),
					'error'
				);
				return;
			}
		}

		// get up to 3 recent failed orders for this customer
		$failed_orders = wc_get_orders( array(
			'customer_id' => $user->ID,
			'status' => array( 'wc-failed' ),
			'limit' => 3,
			'orderby' => 'date',
			'order' => 'DESC',
			'return' => 'ids',
		) );

		if ( ! empty( $failed_orders ) ) {

			// count failed orders within the last 24 hours
			$failed_order_count = 0;
			$current_timestamp = time();

			foreach ( $failed_orders as $failed_order_id ) {

				// load failed order object
				$failed_order = wc_get_order( $failed_order_id );

				if ( $failed_order ) {

					// get failed order creation datetime object
					$failed_order_time = $failed_order->get_date_created();

					if ( null !== $failed_order_time ) {

						// compare failed order timestamp in utc
						$failed_timestamp = $failed_order_time->getTimestamp();

						// count only failed orders from the last 24 hours
						if ( ( $current_timestamp - $failed_timestamp ) < 86400 ) {
							$failed_order_count++;
						}
					}
				}
			}

			// block checkout after 3 failed orders in 24 hours
			if ( $failed_order_count >= 3 ) {
				wc_add_notice(
					__( 'Please wait 24 hours before placing another order.', 'block-automated-checkout' ),
					'error'
				);
				return;
			}
		}

		// enforce minimum time between orders
		$min_interval = (int) apply_filters( 'block_automated_checkout_min_order_interval', 1800 );

		// get most recent order id for this customer
		$orders = wc_get_orders( array(
			'customer_id' => $user->ID,
			'limit' => 1,
			'orderby' => 'date',
			'order' => 'DESC',
			'return' => 'ids',
		) );

		if ( ! empty( $orders ) ) {

			// first result is most recent order id
			$last_order_id = $orders[0];
			$last_order = wc_get_order( $last_order_id );

			if ( $last_order ) {

				// get order creation datetime object
				$last_order_time = $last_order->get_date_created();

				if ( null !== $last_order_time ) {

					// compare unix timestamps in utc
					$last_timestamp = $last_order_time->getTimestamp();
					$current_timestamp = time();

					// block checkout if last order was too recent
					if ( ( $current_timestamp - $last_timestamp ) < $min_interval ) {
						wc_add_notice(
							__( 'Please wait a while before placing another order.', 'block-automated-checkout' ),
							'error'
						);
						return;
					}
				}
			}
		}
	}

} );

// Ref: ChatGPT
