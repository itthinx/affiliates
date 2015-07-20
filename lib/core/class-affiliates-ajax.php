<?php
/**
 * class-affiliates-ajax.php
 *
 * Copyright (c) 2010 - 2015 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 2.11.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax handler.
 */
class Affiliates_Ajax {

	public static function init() {
		// @todo only on own admin screens
		add_action('admin_footer', array( __CLASS__, 'admin_footer' ) );
		add_action(
			'wp_ajax_affiliates_admin_user_screen_settings',
			array( __CLASS__, 'affiliates_admin_user_screen_settings' )
		);
	}

	public static function admin_footer() {
		$output = '<script type="text/javascript">';
		$output .= 'affiliates_ajax_nonce = \'' . wp_create_nonce( 'affiliates-ajax-nonce' ) . '\';';
		$output .= '</script>';
		echo $output;
	}

	public static function affiliates_admin_user_screen_settings() {
		if ( check_ajax_referer( 'affiliates-ajax-nonce', 'affiliates_ajax_nonce' ) ) {
			// @todo processing
			error_log(__METHOD__. ' passed nonce ok' ); // @todo remove
		}
		wp_die();
	}
}
Affiliates_Ajax::init();
