<?php
/**
 * class-affiliates-user-registration.php
 * 
 * Copyright (c) 2010 - 2014 "kento" Karim Rahimpur www.itthinx.com
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
 * @since 2.7.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User registration integration.
 */
class Affiliates_User_Registration {

	/**
	 * Hooks on user_register to record a referral when new users register.
	 * (only when the built-in User Registration integration is enabled).
	 */
	public static function init() {
		if ( get_option( 'aff_user_registration_enabled', 'no' ) ) {
			add_action( 'user_register', array( __CLASS__, 'user_register' ) );
		}
	}

	/**
	 * May record a referral when a new user has been referred by an affilaite.
	 * 
	 * @param int $user_id
	 */
	public static function user_register( $user_id ) {

		// @todo
		//if pro / enterprise {
		// $r->...
		//} else {
		//affiliates_suggest_referral(...);
		//}
	}
}
Affiliates_User_Registration::init();

