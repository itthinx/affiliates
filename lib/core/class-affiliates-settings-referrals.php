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

				// auto
				delete_option( 'aff_allow_auto' );
				add_option( 'aff_allow_auto', !empty( $_POST['allow_auto'] ) ? 'yes' : 'no', '', 'no' );

				delete_option( 'aff_allow_auto_coupons' );
				add_option( 'aff_allow_auto_coupons', !empty( $_POST['allow_auto_coupons'] ) ? 'yes' : 'no', '', 'no' );

				self::settings_saved_notice();
			}
		}

		$timeout         = get_option( 'aff_cookie_timeout_days', AFFILIATES_COOKIE_TIMEOUT_DAYS );
		$use_direct      = get_option( 'aff_use_direct', false );
		$duplicates      = get_option( 'aff_duplicates', false );
		$allow_auto      = get_option( 'aff_allow_auto', 'no' ) == 'yes';
		$allow_auto_coupons = get_option( 'aff_allow_auto_coupons', 'no' ) == 'yes';
		$default_status  = get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );
		$status_descriptions = array(
			AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', 'affiliates' ),
			AFFILIATES_REFERRAL_STATUS_CLOSED   => __( 'Closed', 'affiliates' ),
			AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', 'affiliates' ),
			AFFILIATES_REFERRAL_STATUS_REJECTED => __( 'Rejected', 'affiliates' ),
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
				'<h3>' . __( 'Referral timeout', 'affiliates' ) . '</h3>' .
				'<p>' .
				'<label>' .
				'<input class="timeout" name="timeout" type="text" value="' . esc_attr( intval( $timeout ) ) . '" />' .
				' ' .
				__( 'Days', 'affiliates' ) .
				'</label>' .
				'</p>' .
				'<p class="description">' .
				__( 'This is the number of days since a visitor accessed your site via an affiliate link, for which a suggested referral will be valid.', 'affiliates' ) .
				'</p>' .
				'<p>' .
				__( 'If you enter 0, referrals will only be valid until the visitor closes the browser (session).', 'affiliates' ) .
				'</p>' .
				'<p>' .
				sprintf(
					__( 'The default value is %d. In this case, if a visitor comes to your site via an affiliate link, a suggested referral will be valid until %d days after she or he clicked that affiliate link.', 'affiliates' ),
					AFFILIATES_COOKIE_TIMEOUT_DAYS,
					AFFILIATES_COOKIE_TIMEOUT_DAYS
				) .
				'</p>';

		echo
			'<h3>' . __( 'Direct referrals', 'affiliates' ) . '</h3>' .
			'<p>' .
			'<label>' .
			'<input name="use-direct" type="checkbox" ' . ( $use_direct ? 'checked="checked"' : '' ) . '/>' .
			' ' .
			__( 'Store direct referrals', 'affiliates' ) .
			'</label>' .
			'</p>' .
			'<p class="description">' .
			__( 'If this option is enabled, whenever a referral is suggested and no affiliate is attributable to it, the referral will be attributed to Direct.', 'affiliates' ) .
			'</p>';

		echo
			'<h3>' . __( 'Default referral status', 'affiliates' ) . '</h3>' .
			'<p>' .
			$status_select .
			'</p>';

		echo
			'<h3>' . __( 'Duplicate referrals', 'affiliates' ) . '</h3>' .
			'<p>' .
			'<label>' .
			'<input name="duplicates" type="checkbox" ' . ( $duplicates ? 'checked="checked"' : '' ) . '/>' .
			' ' .
			__( 'Allow duplicate referrals', 'affiliates' ) .
			'</label>' .
			'</p>' .
			'<p class="description">' .
			__( 'Allow to record duplicate referrals for the same affiliate (based on amount, currency, internal type and reference).', 'affiliates' ) .
			'</p>';

		echo
			'<h3>' . __( 'Auto-referrals', 'affiliates' ) . '</h3>' .
			'<p>' .
			'<label>' .
			sprintf( '<input type="checkbox" name="allow_auto" %s" />', $allow_auto == 'yes' ? ' checked="checked" ' : '' ) .
			' ' .
			__( 'Allow auto-referrals', 'affiliates' ) .
			'</label>' .
			'</p>' .
			'<p class="description">' .
			__( 'If this option is enabled, affiliates are allowed to refer themselves.', 'affiliates' ) .
			' ' .
			__( 'This option allows an affiliate to earn a commission on a transaction that involves the affiliate as a customer or lead.', 'affiliates' ) .
			' ' .
			__( 'Auto-referrals are identified as such, when a transaction is processed for the same user or user email as the affiliate’s, or when it involves the use of a coupon assigned to the affiliate.', 'affiliates' ) .
			'</p>' .
			'<p>' .
			'<label>' .
			sprintf( '<input type="checkbox" name="allow_auto_coupons" %s" />', $allow_auto_coupons ? ' checked="checked" ' : '' ) .
			' ' .
			__( 'Allow auto-coupons', 'affiliates' ) .
			'</label>' .
			'</p>' .
			'<p class="description">' .
			__( 'Allow affiliates to apply coupons that are assigned to them.', 'affiliates' ) .
			' ' .
			__( 'Verification is supported for coupons managed through WooCommerce.', 'affiliates' ) .
			'</p>';

		echo
			'<p>' .
			wp_nonce_field( 'admin', AFFILIATES_ADMIN_SETTINGS_NONCE, true, false ) .
			'<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', 'affiliates' ) . '"/>' .
			'</p>' .
			'</div>' .
			'</form>';

		affiliates_footer();
	}
}
