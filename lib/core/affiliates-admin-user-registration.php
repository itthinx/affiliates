<?php
/**
 * affiliates-admin-user-registration.php
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

require_once AFFILIATES_CORE_LIB . '/class-affiliates-user-registration.php';

function affiliates_admin_user_registration() {

	if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
		wp_die( __( 'Access denied.', AFFILIATES_PLUGIN_DOMAIN ) );
	}

	echo '<h1>';
	echo __( 'User Registration', AFFILIATES_PLUGIN_DOMAIN );
	echo '</h1>';

	echo '<p class="description">';
	echo __( 'Here you can enable the built-in User Registration integration which allows to grant commissions to affiliates when they refer new users.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</p>';

	// save
	if ( isset( $_POST['action'] ) && $_POST['action'] == 'save' ) {
		if ( isset( $_POST['affiliates-user-registraton-admin'] ) && wp_verify_nonce( $_POST['affiliates-user-registraton-admin'], 'save' ) ) {

			delete_option( 'aff_user_registration_enabled' );
			if ( !empty( $_POST['enabled'] ) ) {
				add_option( 'aff_user_registration_enabled', 'yes', '', 'no' );
			}

			if ( AFFILIATES_PLUGIN_NAME != 'affiliates' ) {
				delete_option( 'aff_user_registration_base_amount' );
				if ( !empty( $_POST['base_amount'] ) ) {
					$base_amount = floatval( $_POST['base_amount'] );
					if ( $base_amount < 0 ) {
						$base_amount = 0;
					}
					add_option( 'aff_user_registration_base_amount', $base_amount, '', 'no' );
				}
			}

			delete_option( 'aff_user_registration_amount' );
			if ( !empty( $_POST['amount'] ) ) {
				$amount = floatval( $_POST['amount'] );
				if ( $amount < 0 ) {
					$amount = 0;
				}
				add_option( 'aff_user_registration_amount', $amount, '', 'no' );
			}

			delete_option( 'aff_user_registration_currency' );
			if ( !empty( $_POST['currency'] ) ) {
				add_option( 'aff_user_registration_currency', $_POST['currency'], '', 'no' );
			}

			delete_option( 'aff_user_registration_referral_status' );
			if ( !empty( $_POST['status'] ) ) {
				add_option( 'aff_user_registration_referral_status', $_POST['status'], '', 'no' );
			}
		}
	}

	$user_registration_enabled     = get_option( 'aff_user_registration_enabled', 'no' );
	if ( AFFILIATES_PLUGIN_NAME != 'affiliates' ) {
		$user_registration_base_amount = get_option( 'aff_user_registration_base_amount', '' );
	}
	$user_registration_amount      = get_option( 'aff_user_registration_amount', '0' );
	$user_registration_currency    = get_option( 'aff_user_registration_currency', Affiliates::DEFAULT_CURRENCY );
	$user_registration_referral_status = get_option(
		'aff_user_registration_referral_status',
		get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED )
	);

	echo '<style type="text/css">';
	echo 'div.field { padding: 0 1em 1em 0; }';
	echo 'div.field.user-registration-base-amount input { width: 5em; text-align: right;}';
	echo 'div.field.user-registration-amount input { width: 5em; text-align: right;}';
	echo 'div.field span.label { display: inline-block; width: 20%; }';
	echo 'div.field span.description { display: block; }';
	echo 'div.buttons { padding-top: 1em; }';
	echo '</style>';

	echo '<form action="" name="user_registration" method="post">';
	echo '<div>';

	// enable
	echo '<div class="field user-registration-enabled">';
	echo '<label>';
	printf( '<input type="checkbox" name="enabled" value="1" %s />', $user_registration_enabled == 'yes' ? ' checked="checked" ' : '' );
	echo ' ';
	echo __( 'Enable the user registration integration', AFFILIATES_PLUGIN_DOMAIN );
	echo '</label>';
	echo '</div>';

	// base amount
	if ( AFFILIATES_PLUGIN_NAME != 'affiliates' ) {
		echo '<div class="field user-registration-base-amount">';
		echo '<label>';
		echo '<span class="label">';
		echo __( 'Base Amount', AFFILIATES_PLUGIN_DOMAIN );
		echo '</span>';
		echo ' ';
		printf( '<input type="text" name="base_amount" value="%s"/>', esc_attr( $user_registration_base_amount ) );
		echo '</label>';
		echo '<span class="description">';
		echo __( 'When an affiliate refers a new user, a referral is recorded, granting the affiliate an amount in the chosen currency. The amount is calculated taking this base amount into account. For example, if a general referral rate is set, the referral amount equals this base amount multipied by the referral rate. If set, the <em>base amount</em> takes pecedence over the <em<amount</em> set below.', AFFILIATES_PLUGIN_DOMAIN );
		echo '</span>';
		echo '</div>';
	}

	// amount
	echo '<div class="field user-registration-amount">';
	echo '<label>';
	echo '<span class="label">';
	echo __( 'Amount', AFFILIATES_PLUGIN_DOMAIN );
	echo '</span>';
	echo ' ';
	printf( '<input type="text" name="amount" value="%s"/>', esc_attr( $user_registration_amount ) );
	echo '</label>';
	echo '<span class="description">';
	echo __( 'When an affiliate refers a new user, a referral is recorded, granting the affiliate this amount in the chosen currency.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</span>';
	echo '</div>';

	// currency
	$currency_select = '<select name="currency">';
	foreach( apply_filters( 'affiliates_supported_currencies', Affiliates::$supported_currencies ) as $cid ) {
		$selected = ( $user_registration_currency == $cid ) ? ' selected="selected" ' : '';
		$currency_select .= '<option ' . $selected . ' value="' .esc_attr( $cid ).'">' . $cid . '</option>';
	}
	$currency_select .= '</select>';
	echo '<div class="field user-registration-currency">';
	echo '<label>';
	echo '<span class="label">';
	echo __( 'Currency', AFFILIATES_PLUGIN_DOMAIN );
	echo '</span>';
	echo ' ';
	echo $currency_select;
	echo '</label>';
	echo '</div>';

	$status_descriptions = array(
		AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', AFFILIATES_PLUGIN_DOMAIN ),
		AFFILIATES_REFERRAL_STATUS_CLOSED   => __( 'Closed', AFFILIATES_PLUGIN_DOMAIN ),
		AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', AFFILIATES_PLUGIN_DOMAIN ),
		AFFILIATES_REFERRAL_STATUS_REJECTED => __( 'Rejected', AFFILIATES_PLUGIN_DOMAIN ),
	);
	$status_select = "<select name='status'>";
	foreach ( $status_descriptions as $status_key => $status_value ) {
		if ( $status_key == $user_registration_referral_status ) {
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}
		$status_select .= "<option value='$status_key' $selected>$status_value</option>";
	}
	$status_select .= "</select>";
	echo '<div class="field user-registration-referral-status">';
	echo '<label>';
	echo '<span class="label">';
	echo __( 'Referral Status', AFFILIATES_PLUGIN_DOMAIN );
	echo '</span>';
	echo ' ';
	echo $status_select;
	echo '</label>';
	echo '<p class="description">';
	echo __( 'The status for referrals that record commissions when affiliates refer new users.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</p>';
	echo '</div>';

	echo '<p>';
	echo __( 'Recommended choices for the referral status are <em>Accepted</em> and <em>Pending</em>.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</p>';

	echo '<ul>';
	echo '<li>';
	echo __( '<strong>Accepted</strong> if these referrals should grant payable commissions to affiliates without the need for further review.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( '<strong>Pending</strong> if these referrals are to be reviewed before the commissions should be taken into account for affiliate payouts.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';
	echo '</ul>';

	echo '<div class="buttons">';
	wp_nonce_field( 'save', 'affiliates-user-registraton-admin', true, true );
	echo '<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', AFFILIATES_PLUGIN_DOMAIN ) . '"/>';
	echo '<input type="hidden" name="action" value="save"/>';
	echo '</div>';

	echo '</div>';
	echo '</form>';

	affiliates_footer();
}
