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

	/**
	 * Adds actions.
	 */
	public static function init() {
		add_action( 'admin_footer', array( __CLASS__, 'admin_footer' ) );
		add_action(
			'wp_ajax_affiliates_set_option',
			array( __CLASS__, 'affiliates_set_option' )
		);
	}

	/**
	 * Initializes the ajax nonce in the footer.
	 */
	public static function admin_footer() {
		$output = '';
		// only on own relevant admin screens
		$screen = get_current_screen();
		switch( $screen->id ) {
			case 'affiliates_page_affiliates-admin-affiliates' :
				$output .= '<script type="text/javascript">';
				$output .= 'affiliates_ajax_nonce = \'' . wp_create_nonce( 'affiliates-ajax-nonce' ) . '\';';
				$output .= '</script>';
				break;
		}
		echo $output;
	}

	/**
	 * Ajax affiliates_set_option handler.
	 */
	public static function affiliates_set_option() {
		global $affiliates_options;
		if ( check_ajax_referer( 'affiliates-ajax-nonce', 'affiliates_ajax_nonce' ) ) {
			$key   = $_REQUEST['key'];
			$value = json_decode( stripslashes( $_REQUEST['value'] ) );
			switch( $_REQUEST['key'] ) {
				case 'show_filters' :
					$affiliates_options->update_option( 'show_filters', $value === true );
					break;
				case 'show_columns' :
					$affiliates_options->update_option( 'show_columns', $value === true );
					break;
				case 'affiliates_overview_columns' :
					if ( is_object( $value ) ) {
						$value = (array) $value;
					}
					if ( is_array( $value ) ) {
						$affiliates_options->update_option( 'affiliates_overview_columns', $value );
					}
					break;
			}
		}
		wp_die();
	}
}
Affiliates_Ajax::init();
