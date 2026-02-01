<?php
/*
Plugin Name: Block Automated Checkout
Plugin URI: https://www.littlebizzy.com/plugins/block-automated-checkout
Description: Blocks checkout abuse in Woo
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
	$overrides[] = 'verified-customers/verified-customers.php';
	return $overrides;
}, 999 );
