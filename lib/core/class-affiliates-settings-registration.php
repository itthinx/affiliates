<?php
/**
 * class-affiliates-settings-registration.php
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
 * @since affiliates 2.8.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registration settings section.
 */
class Affiliates_Settings_Registration extends Affiliates_Settings {

	/**
	 * Registration settings.
	 */
	public static function section() {

		if ( isset( $_POST['submit'] ) ) {
			if ( wp_verify_nonce( $_POST[AFFILIATES_ADMIN_SETTINGS_NONCE], 'admin' ) ) {

				delete_option( 'aff_registration' );
				add_option( 'aff_registration', !empty( $_POST['registration'] ), '', 'no' );

				delete_option( 'aff_notify_admin' );
				add_option( 'aff_notify_admin', !empty( $_POST['notify_admin'] ), '', 'no' );

				self::settings_saved_notice();

			}
		}

		$registration = get_option( 'aff_registration', get_option( 'users_can_register', false ) );
		$notify_admin = get_option( 'aff_notify_admin', get_option( 'aff_notify_admin', true ) );

		echo
			'<form action="" name="options" method="post">' .
			'<div>' .
			'<h3>' . __( 'Affiliate registration', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			'<label>' .
			'<input name="registration" type="checkbox" ' . ( $registration ? 'checked="checked"' : '' ) . '/>' .
			' ' .
			__( 'Allow affiliate registration', AFFILIATES_PLUGIN_DOMAIN ) .
			'</label>' .
			'</p>';

		echo
			'<p>' .
			'<label>' .
			'<input name="notify_admin" type="checkbox" ' . ( $notify_admin ? 'checked="checked"' : '' ) . '/>' .
			' ' .
			__( 'Notify the site admin when a new affiliate is registered', AFFILIATES_PLUGIN_DOMAIN ) .
			'</label>' .
			'</p>';

		// @todo registration field customization

		echo
			'<p>' .
			wp_nonce_field( 'admin', AFFILIATES_ADMIN_SETTINGS_NONCE, true, false ) .
			'<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', AFFILIATES_PLUGIN_DOMAIN ) . '"/>' .
			'</p>' .
			'</div>' .
			'</form>';

			affiliates_footer();
	}
}
