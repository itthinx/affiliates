<?php
/**
 * affiliates-admin-affiliates-remove.php
 * 
 * Copyright (c) 2010, 2011 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 1.1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Show form to remove an affiliate.
 * @param int $affiliate_id affiliate id
 */
function affiliates_admin_affiliates_remove( $affiliate_id ) {
	
	global $wpdb;
	
	if ( !current_user_can( AFFILIATES_ADMINISTER_AFFILIATES ) ) {
		wp_die( __( 'Access denied.', AFFILIATES_PLUGIN_DOMAIN ) );
	}
	
	$affiliate = affiliates_get_affiliate( intval( $affiliate_id ) );
	
	if ( empty( $affiliate ) ) {
		wp_die( __( 'No such affiliate.', AFFILIATES_PLUGIN_DOMAIN ) );
	}
	
	$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
	
	$affiliate_user = null;
	$affiliate_user_edit = '';
	$affiliate_user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $affiliates_users_table WHERE affiliate_id = %d", intval( $affiliate_id ) ) );
	if ( $affiliate_user_id !== null ) {
		$affiliate_user = get_user_by( 'id', intval( $affiliate_user_id ) );
		if ( $affiliate_user ) {
			if ( current_user_can( 'edit_user',  $affiliate_user->ID ) ) {
				$affiliate_user_edit = sprintf( __( 'Edit %s', AFFILIATES_PLUGIN_DOMAIN ) , '<a target="_blank" href="' . esc_url( "user-edit.php?user_id=$affiliate_user->ID" ) . '">' . $affiliate_user->user_login . '</a>' );
			} else {
				$affiliate_user_edit = $affiliate_user->user_login;
			}
		}
	}
	
	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = remove_query_arg( 'affiliate_id', $current_url );
	
	$output =
		'<div class="manage-affiliates">' .
		'<div>' .
			'<h2>' .
				__( 'Remove an affiliate', AFFILIATES_PLUGIN_DOMAIN ) .
			'</h2>' .
		'</div>' .
		'<form id="remove-affiliate" action="' . $current_url . '" method="post">' .
		'<div class="affiliate remove">' .
		'<input id="affiliate-id-field" name="affiliate-id-field" type="hidden" value="' . esc_attr( intval( $affiliate_id ) ) . '"/>' .
		'<ul>' .
		'<li>' . sprintf( __( 'Name : %s', AFFILIATES_PLUGIN_DOMAIN ), wp_filter_kses( $affiliate['name'] ) ) . '</li>' .
		'<li>' . sprintf( __( 'Email : %s', AFFILIATES_PLUGIN_DOMAIN ), wp_filter_kses( $affiliate['email'] ) ) . '</li>' .
		'<li>' . sprintf( __( 'Username : %s', AFFILIATES_PLUGIN_DOMAIN ), wp_filter_kses( $affiliate_user_edit ) ) . '</li>' .
		'<li>' . sprintf( __( 'From : %s', AFFILIATES_PLUGIN_DOMAIN ), wp_filter_kses( $affiliate['from_date'] ) ) . '</li>' .
		'<li>' . sprintf( __( 'Until : %s', AFFILIATES_PLUGIN_DOMAIN ), wp_filter_kses( $affiliate['from_date'] ) ) . '</li>' .
		'</ul> ' .
		wp_nonce_field( 'affiliates-remove', AFFILIATES_ADMIN_AFFILIATES_NONCE, true, false ) .
		'<input class="button" type="submit" value="' . __( 'Remove', AFFILIATES_PLUGIN_DOMAIN ) . '"/>' .
		'<input type="hidden" value="remove" name="action"/>' .
		'<a class="cancel" href="' . $current_url . '">' . __( 'Cancel', AFFILIATES_PLUGIN_DOMAIN ) . '</a>' .
		'</div>' .
		'</div>' . // .affiliate.remove
		'</form>' .
		'</div>'; // .manage-affiliates
	
	echo $output;
	
	affiliates_footer();
} // function affiliates_admin_affiliates_remove

/**
 * Handle remove form submission.
 */
function affiliates_admin_affiliates_remove_submit() {
	
	global $wpdb;
	$result = false;
	
	if ( !current_user_can( AFFILIATES_ADMINISTER_AFFILIATES ) ) {
		wp_die( __( 'Access denied.', AFFILIATES_PLUGIN_DOMAIN ) );
	}
	
	if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_AFFILIATES_NONCE], 'affiliates-remove' ) ) {
		wp_die( __( 'Access denied.', AFFILIATES_PLUGIN_DOMAIN ) );
	}
	
	$affiliates_table = _affiliates_get_tablename( 'affiliates' );
	
	$affiliate_id = isset( $_POST['affiliate-id-field'] ) ? $_POST['affiliate-id-field'] : null;
	if ( $affiliate_id ) {
		$valid_affiliate = false;
		// do not mark the pseudo-affiliate as deleted: type != ...
		$check = $wpdb->prepare(
			"SELECT affiliate_id FROM $affiliates_table WHERE affiliate_id = %d AND (type IS NULL OR type != '" . AFFILIATES_DIRECT_TYPE . "')",
			intval( $affiliate_id ) );
		if ( $wpdb->query( $check ) ) {
			$valid_affiliate = true;
		}
		
		if ( $valid_affiliate ) {
			$result = false !== $wpdb->query(
				$query = $wpdb->prepare(
					"UPDATE $affiliates_table SET status = 'deleted' WHERE affiliate_id = %d",
					intval( $affiliate_id )
				)
			);
			do_action( 'affiliates_deleted_affiliate', intval( $affiliate_id ) );
		}
	}
	
	return $result;
	
} // function affiliates_admin_affiliates_remove_submit
?>