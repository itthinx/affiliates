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

	const REFERRAL_TYPE = 'user';

	/**
	 * Hooks on user_register to record a referral when new users register.
	 * (only when the built-in User Registration integration is enabled).
	 */
	public static function init() {
		if ( get_option( 'aff_user_registration_enabled', 'no' ) == 'yes' ) {
			add_action( 'user_register', array( __CLASS__, 'user_register' ) );
		}
	}

	/**
	 * Record a referral when a new user has been referred by an affilaite.
	 * 
	 * @param int $user_id
	 */
	public static function user_register( $user_id ) {

		if ( $user = get_user_by( 'id', $user_id ) ) {

			$post_id = get_the_ID();
			$description = sprintf( 'User Registration %s', esc_html( $user->user_login ) );
			$base_amount = null;
			if ( AFFILIATES_PLUGIN_NAME != 'affiliates' ) {
				$base_amount = get_option( 'aff_user_registration_base_amount', null );
			}
			$amount = null;
			if ( empty( $base_amount ) ) {
				$amount = get_option( 'aff_user_registration_amount', '0' );
			}
			$currency = get_option( 'aff_user_registration_currency', Affiliates::DEFAULT_CURRENCY );
			$user_registration_referral_status = get_option(
				'aff_user_registration_referral_status',
				get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED )
			);

			$data = array(
				'user_login' => array(
					'title'  => 'Username',
					'domain' => AFFILIATES_PLUGIN_DOMAIN,
					'value'  => $user->user_login,
				),
				'user_email' => array(
					'title'  => 'Email',
					'domain' => AFFILIATES_PLUGIN_DOMAIN,
					'value'  => $user->user_email,
				),
				'first_name' => array(
					'title'  => 'First Name',
					'domain' => AFFILIATES_PLUGIN_DOMAIN,
					'value'  => $user->first_name,
				),
				'last_name' => array(
					'title'  => 'Last Name',
					'domain' => AFFILIATES_PLUGIN_DOMAIN,
					'value'  => $user->last_name,
				),
				'base_amount' => array(
					'title'  => 'Base Amount',
					'domain' => AFFILIATES_PLUGIN_DOMAIN,
					'value'  => $base_amount
				)
			);

			if ( class_exists( 'Affiliates_Referral_WordPress' ) ) {
				$r = new Affiliates_Referral_WordPress();
				$affiliate_id = $r->evaluate( $post_id, $description, $data, $base_amount, $amount, $currency, $user_registration_referral_status, self::REFERRAL_TYPE );
			} else {
				$affiliate_id = affiliates_suggest_referral( $post_id, $description, $data, $amount, $currency, $user_registration_referral_status, self::REFERRAL_TYPE );
			}
		}
	}
}
Affiliates_User_Registration::init();
