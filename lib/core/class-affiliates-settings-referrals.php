<?php
/**
 * class-affiliates-settings-general.php
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
 * Referral settings section.
 */
class Affiliates_Settings_Referrals extends Affiliates_Settings {

	/**
	 * Renders the referrals section.
	 */
	public static function section() {

		if ( isset( $_POST['submit'] ) ) {

			if ( wp_verify_nonce( $_POST[AFFILIATES_ADMIN_SETTINGS_NONCE], 'admin' ) ) {

				// timeout
				$timeout = intval ( $_POST['timeout'] );
				if ( $timeout < 0 ) {
					$timeout = 0;
				}
				update_option( 'aff_cookie_timeout_days', $timeout );

				// direct referrals?
				delete_option( 'aff_use_direct' );
				add_option( 'aff_use_direct', !empty( $_POST['use-direct'] ), '', 'no' );

				// default status
				if ( !empty( $_POST['status'] ) && ( Affiliates_Utility::verify_referral_status_transition( $_POST['status'], $_POST['status'] ) ) ) {
					update_option( 'aff_default_referral_status', $_POST['status'] );
				} else {
					update_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );
				}

				// allow duplicates?
				delete_option( 'aff_duplicates' );
				add_option( 'aff_duplicates', !empty( $_POST['duplicates'] ), '', 'no' );

				delete_option( 'aff_excluded' );
				add_option( 'aff_excluded', !empty( $_POST['excluded'] ), '', 'no' );

				delete_option( 'aff_excluded_coupons_allowed' );
				add_option( 'aff_excluded_coupons_allowed', !empty( $_POST['coupons_allowed'] ), '', 'no' );

			}
		}

		$timeout         = get_option( 'aff_cookie_timeout_days', AFFILIATES_COOKIE_TIMEOUT_DAYS );
		$use_direct      = get_option( 'aff_use_direct', false );
		$duplicates      = get_option( 'aff_duplicates', false );
		$excluded        = get_option( 'aff_excluded', false );
		$coupons_allowed = get_option( 'aff_excluded_coupons_allowed', false );
		$default_status  = get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );
		$status_descriptions = array(
			AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', AFFILIATES_PLUGIN_DOMAIN ),
			AFFILIATES_REFERRAL_STATUS_CLOSED   => __( 'Closed', AFFILIATES_PLUGIN_DOMAIN ),
			AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', AFFILIATES_PLUGIN_DOMAIN ),
			AFFILIATES_REFERRAL_STATUS_REJECTED => __( 'Rejected', AFFILIATES_PLUGIN_DOMAIN ),
		);
		$status_select = "<select name='status'>";
		foreach ( $status_descriptions as $status_key => $status_value ) {
			if ( $status_key == $default_status ) {
				$selected = "selected='selected'";
			} else {
				$selected = "";
			}
			$status_select .= "<option value='$status_key' $selected>$status_value</option>";
		}
		$status_select .= "</select>";

		echo
			'<form action="" name="options" method="post">' .
				'<div>' .
				'<h3>' . __( 'Referral timeout', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
				'<p>' .
				'<label>' .
				'<input class="timeout" name="timeout" type="text" value="' . esc_attr( intval( $timeout ) ) . '" />' .
				' ' .
				__( 'Days', AFFILIATES_PLUGIN_DOMAIN ) .
				'</label>' .
				'</p>' .
				'<p class="description">' .
				__( 'This is the number of days since a visitor accessed your site via an affiliate link, for which a suggested referral will be valid.', AFFILIATES_PLUGIN_DOMAIN ) .
				'</p>' .
				'<p>' .
				__( 'If you enter 0, referrals will only be valid until the visitor closes the browser (session).', AFFILIATES_PLUGIN_DOMAIN ) .
				'</p>' .
				'<p>' .
				sprintf(
					__( 'The default value is %d. In this case, if a visitor comes to your site via an affiliate link, a suggested referral will be valid until %d days after she or he clicked that affiliate link.', AFFILIATES_PLUGIN_DOMAIN ),
					AFFILIATES_COOKIE_TIMEOUT_DAYS,
					AFFILIATES_COOKIE_TIMEOUT_DAYS
				) .
				'</p>';

		echo
			'<h3>' . __( 'Direct referrals', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			'<label>' .
			'<input name="use-direct" type="checkbox" ' . ( $use_direct ? 'checked="checked"' : '' ) . '/>' .
			' ' .
			__( 'Store direct referrals', AFFILIATES_PLUGIN_DOMAIN ) .
			'</label>' .
			'</p>' .
			'<p class="description">' .
			__( 'If this option is enabled, whenever a referral is suggested and no affiliate is attributable to it, the referral will be attributed to Direct.', AFFILIATES_PLUGIN_DOMAIN ) .
			'</p>';

		echo
			'<h3>' . __( 'Default referral status', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			$status_select .
			'</p>';

		echo
			'<h3>' . __( 'Duplicate referrals', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			'<label>' .
			'<input name="duplicates" type="checkbox" ' . ( $duplicates ? 'checked="checked"' : '' ) . '/>' .
			' ' .
			__( 'Allow duplicate referrals', AFFILIATES_PLUGIN_DOMAIN ) .
			'</label>' .
			'</p>' .
			'<p class="description">' .
			__( 'Allow to record duplicate referrals for the same affiliate (based on amount, currency, internal type and reference).', AFFILIATES_PLUGIN_DOMAIN ) .
			'</p>';

		echo
			'<h3>' . __( 'Excluded referrals', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			'<label>' .
			sprintf( '<input type="checkbox" name="excluded" %s" />', $excluded ? ' checked="checked" ' : '' ) .
			' ' .
			__( 'Allow affiliates to be credited with auto-referrals.', AFFILIATES_PLUGIN_DOMAIN ) .
			' ' .
			__( 'If this option is enabled, affiliates can visit their own affiliate link and earn commissions on their own purchases.', AFFILIATES_PLUGIN_DOMAIN ) .
			' ' .
			__( 'Auto-referrals are identified when the purchase is made through the same user or if the user email is the same as the affiliate’s email.', AFFILIATES_PLUGIN_DOMAIN ) .
			'</label>' .
			'</p>' .
			'<p>' .
			'<label>' .
			sprintf( '<input type="checkbox" name="coupons_allowed" %s" />', $coupons_allowed ? ' checked="checked" ' : '' ) .
			' ' .
			__( 'Allow affiliates to apply their own coupons.', AFFILIATES_PLUGIN_DOMAIN ) .
			' ' .
			__( 'Verification is supported for coupons managed through WooCommerce.', AFFILIATES_PLUGIN_DOMAIN ) .
			'</label>' .
			'</p>';

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
