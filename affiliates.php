<?php
/**
 * affiliates.php
 *
 * Copyright (c) 2010-2026 "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package affiliates
 * @since affiliates 1.0.0
 *
 * Plugin Name: Affiliates
 * Plugin URI: https://www.itthinx.com/plugins/affiliates
 * Description: The Affiliates plugin provides the right tools to maintain a partner referral program.
 * Version: 5.4.1
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: itthinx
 * Author URI: https://www.itthinx.com
 * Donate-Link: https://www.itthinx.com/shop
 * Text Domain: affiliates
 * Domain Path: /lib/core/languages
 * License: GPLv3
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !defined( 'AFFILIATES_CORE_VERSION' ) ) {
	define( 'AFFILIATES_CORE_VERSION', '5.4.1' );
	define( 'AFFILIATES_PLUGIN_NAME', 'affiliates' );
	define( 'AFFILIATES_FILE', __FILE__ );
	define( 'AFFILIATES_PLUGIN_BASENAME', plugin_basename( AFFILIATES_FILE ) );
	if ( !defined( 'AFFILIATES_CORE_DIR' ) ) {
		define( 'AFFILIATES_CORE_DIR', WP_PLUGIN_DIR . '/affiliates' );
	}
	if ( !defined( 'AFFILIATES_CORE_LIB' ) ) {
		define( 'AFFILIATES_CORE_LIB', AFFILIATES_CORE_DIR . '/lib/core' );
	}
	if ( !defined( 'AFFILIATES_CORE_URL' ) ) {
		define( 'AFFILIATES_CORE_URL', WP_PLUGIN_URL . '/affiliates' );
	}
	if ( !defined( 'AFFILIATES_WPML' ) ) {
		define( 'AFFILIATES_WPML', true );
	}
	require_once AFFILIATES_CORE_LIB . '/constants.php';
	require_once AFFILIATES_CORE_LIB . '/wp-init.php';
}
