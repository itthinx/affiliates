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

			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				delete_option( 'aff_customer_registration_enabled' );
				if ( !empty( $_POST['enabled'] ) ) {
					add_option( 'aff_customer_registration_enabled', 'yes', '', 'no' );
				}
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
	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$customer_registration_enabled = get_option( 'aff_customer_registration_enabled', 'no' );
	}
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

	// enable customer
	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		echo '<div class="field customer-registration-enabled">';
		echo '<label>';
		printf( '<input type="checkbox" name="enabled" value="1" %s />', $customer_registration_enabled == 'yes' ? ' checked="checked" ' : '' );
		echo ' ';
		echo __( 'Enable the WooCommerce customer registration integration', AFFILIATES_PLUGIN_DOMAIN );
		echo '</label>';
		echo ' ';
		echo '<span class="description">';
		echo __( 'If the user registration integration should create referrals for new customers that register at checkout, this option should be enabled.', AFFILIATES_PLUGIN_DOMAIN );
		echo '</span>';
		echo '</div>';
	}

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
		echo __( 'When an affiliate refers a new user, a referral is recorded, granting the affiliate an amount in the chosen currency. The amount is calculated taking this base amount into account. For example, if a general referral rate is set, the referral amount equals this base amount multipied by the referral rate.', AFFILIATES_PLUGIN_DOMAIN );
		echo ' ';
		echo __( 'If set, this <strong>Base Amount</strong> takes precedence over the <strong>Amount</strong>.', AFFILIATES_PLUGIN_DOMAIN );
		if ( AFFILIATES_PLUGIN_NAME == 'affiliates-enterprise' ) {
			echo ' ';
			echo __( 'If multi-tiered referrals are enabled and level rates are not relative, this <strong>Base Amount</strong> must be used instead of the <strong>Amount</strong>.', AFFILIATES_PLUGIN_DOMAIN );
		}
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

	if ( !( class_exists( 'Groups_Group' ) && method_exists( 'Groups_Group', 'get_groups' ) ) ) {
		echo '<p>';
		echo __( 'If you would like to grant commissions for group memberships, please install <a href="http://wordpress.org/plugins/groups/">Groups</a>.', AFFILIATES_PLUGIN_DOMAIN );
		echo '</p>';
	}

	echo '<div class="buttons">';
	wp_nonce_field( 'save', 'affiliates-user-registraton-admin', true, true );
	echo '<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', AFFILIATES_PLUGIN_DOMAIN ) . '"/>';
	echo '<input type="hidden" name="action" value="save"/>';
	echo '</div>';

	if ( ( class_exists( 'Groups_Group' ) && method_exists( 'Groups_Group', 'get_groups' ) ) ) {

		echo '<br/>';

		echo '<h2>';
		echo __( 'Groups', AFFILIATES_PLUGIN_DOMAIN );
		echo '</h2>';

		echo '<h3>';
		echo __( 'Affiliates Group', AFFILIATES_PLUGIN_DOMAIN );
		echo '</h3>';

		echo __( 'New affiliates can be assigned to a group.', AFFILIATES_PLUGIN_DOMAIN );

		// @todo

		echo '<h3>';
		echo __( 'Groups PayPal Integration', AFFILIATES_PLUGIN_DOMAIN );
		echo '</h3>';

		echo __( 'Enable the Groups PayPal integration.', AFFILIATES_PLUGIN_DOMAIN );

		// @todo we can act on:
		// do_action( "groups_created_subscription", $result );
		// do_action( "groups_updated_subscription", $result );
		// do_action( "groups_deleted_subscription", $result );

		echo '<h3>';
		echo __( 'Membership Commissions', AFFILIATES_PLUGIN_DOMAIN );
		echo '</h3>';

		echo '<p class="description">';
		echo __( 'Here you can enable commissions per group with the built-in <a href="http://wordpress.org/plugins/groups/">Groups</a> integration.', AFFILIATES_PLUGIN_DOMAIN );
		echo '</p>';

		$aff_user_groups = get_option( 'aff_user_groups', array() );

		$groups = Groups_Group::get_groups( array( 'order_by' => 'name', 'order' => 'ASC' ) );

		echo '<style type="text/css">';
		echo 'td.field { padding: 0 1em 1em 0; }';
		echo 'td.field.group-base-amount input { width: 5em; text-align: right;}';
		echo 'td.field.group-amount input { width: 5em; text-align: right;}';
		echo '</style>';

		echo '<table>';
		echo '<thead>';
		echo '<tr>';
		echo '<td>';
		echo __( 'Group', AFFILIATES_PLUGIN_DOMAIN );
		echo '</td>';
		echo '<td>';
		echo __( 'Amount', AFFILIATES_PLUGIN_DOMAIN );
		echo '</td>';
		if ( AFFILIATES_PLUGIN_NAME != 'affiliates' ) {
			echo '<td>';
			echo __( 'Base Amount', AFFILIATES_PLUGIN_DOMAIN );
			echo '</td>';
		}
		echo '<td>';
		echo __( 'Currency', AFFILIATES_PLUGIN_DOMAIN );
		echo '</td>';
		echo '<td>';
		echo __( 'Referral Status', AFFILIATES_PLUGIN_DOMAIN );
		echo '</td>';
		echo '<tr/>';
		echo '</thead>';
		echo '<tbody>';
		foreach( $groups as $group ) {
			$group_enabled = !empty( $aff_user_groups[$group->group_id] ) && !empty( $aff_user_groups[$group->group_id]['enabled'] ) ? $aff_user_groups[$group->group_id]['enabled'] : 'no';
			$group_amount = !empty( $aff_user_groups[$group->group_id] ) && !empty( $aff_user_groups[$group->group_id]['amount'] ) ? $aff_user_groups[$group->group_id]['amount'] : '0';
			$group_base_amount = !empty( $aff_user_groups[$group->group_id] ) && !empty( $aff_user_groups[$group->group_id]['base_amount'] ) ? $aff_user_groups[$group->group_id]['base_amount'] : '';
			$group_currency = !empty( $aff_user_groups[$group->group_id] ) && !empty( $aff_user_groups[$group->group_id]['currency'] ) ? $aff_user_groups[$group->group_id]['currency'] : Affiliates::DEFAULT_CURRENCY;
			$group_referral_status = !empty( $aff_user_groups[$group->group_id] ) && !empty( $aff_user_groups[$group->group_id]['referral_status'] ) ? $aff_user_groups[$group->group_id]['referral_status'] : get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );
			echo '<tr>';

			echo '<td class="field">';
			echo '<label>';
			printf( '<input type="checkbox" name="group" value="1" %s />', esc_attr( $group->group_id ), $group_enabled == 'yes' ? ' checked="checked" ' : '' );
			echo ' ';
			echo esc_html( $group->name );
			echo '</label>';
			echo '</td>';

			echo '<td class="field group-amount">';
			printf( '<input type="number" name="group_amount[%d]" value="%s" />', esc_attr( $group->group_id ), $group_amount );
			echo '</td>';

			if ( AFFILIATES_PLUGIN_NAME != 'affiliates' ) {
				echo '<td class="field group-base-amount">';
				printf( '<input type="number" name="group_base_amount[%d]" value="%s" />', esc_attr( $group->group_id ), $group_base_amount );
				echo '</td>';
			}

			echo '<td class="field group-currency">';
			printf( '<select name="group_currency[%s]">', esc_attr( $group->group_id ) );
			foreach( apply_filters( 'affiliates_supported_currencies', Affiliates::$supported_currencies ) as $cid ) {
				$selected = ( $group_currency == $cid ) ? ' selected="selected" ' : '';
				echo '<option ' . $selected . ' value="' .esc_attr( $cid ).'">' . $cid . '</option>';
			}
			echo '</select>';
			echo '</td>';

			echo '<td class="field group-referral-status">';
			printf( '<select name="group_status[%s]">', esc_attr( $group->group_id ) );
			foreach ( $status_descriptions as $status_key => $status_value ) {
				if ( $status_key == $group_referral_status ) {
					$selected = "selected='selected'";
				} else {
					$selected = "";
				}
				echo "<option value='$status_key' $selected>$status_value</option>";
			}
			echo "</select>";
			echo '</td>';

			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';

		echo '<p class="description">';
		echo __( 'This allows to grant commissions to affiliates when they refer group memberships.', AFFILIATES_PLUGIN_DOMAIN );
		echo ' ';
		echo __( 'For enabled groups, a commission will be granted to the referring affiliate as soon as the user becomes a member of the group.', AFFILIATES_PLUGIN_DOMAIN );
		echo '</p>';

		echo '<div class="buttons">';
		wp_nonce_field( 'save', 'affiliates-user-registraton-admin', true, true );
		echo '<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', AFFILIATES_PLUGIN_DOMAIN ) . '"/>';
		echo '<input type="hidden" name="action" value="save"/>';
		echo '</div>';
	}

	echo '</div>';
	echo '</form>';

	affiliates_footer();
}
