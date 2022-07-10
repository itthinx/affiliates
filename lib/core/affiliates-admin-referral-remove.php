<?php
/**
 * affiliates-admin-referral-remove.php
 * 
 * Copyright (c) 2010-2013 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 2.2.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Delete a referral.
 */
function affiliates_admin_referral_remove( $referral_id = null ) {

	global $wpdb;

	$output = '';

	if ( !current_user_can( AFFILIATES_ADMINISTER_AFFILIATES ) ) {
		wp_die( __( 'Access denied.', 'affiliates' ) );
	}

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$cancel_url  = remove_query_arg( 'referral_id', remove_query_arg( 'action', $current_url ) );
	$current_url = remove_query_arg( 'paged', $current_url );

	$output .= '<div class="referral remove">';
	$output .= '<h1>';
	$output .= __( 'Remove a Referral', 'affiliates' );
	$output .= '</h1>';

	if ( isset( $_POST['submit'] ) ) {
		if (
			!isset( $_POST['referral-nonce'] ) ||
			!wp_verify_nonce( $_POST['referral-nonce'], 'remove' )
		) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		} else {			
			if ( !empty( $_POST['referral_id'] ) ) {
				// remove the referral
				$referrals_table = _affiliates_get_tablename( 'referrals' );
				if ( $wpdb->query( $wpdb->prepare(
					"DELETE FROM $referrals_table WHERE referral_id = %d",
					intval( $_POST['referral_id'] )
				) ) ) {
					do_action( 'affiliates_deleted_referral', intval( $_POST['referral_id'] ) );
					$output .= '<br/>';
					$output .= '<div class="info">';
					$output .= __( 'The referral has been removed.', 'affiliates' );
					$output .= ' ';
					$output .= sprintf( '<a href="%s">%s</a>', $cancel_url, __( 'Return', 'affiliates' ) );
					$output .= '</div>';
					$output .= '<br/>';
					
				} else {
					$output .= '<div class="error">' . __( 'I do not know how to delete what does not exist.', 'affiliates' ) . '</div>';
				}
			}
		}
	} else {
		if ( $referral_id !== null ) {
			$referrals_table = _affiliates_get_tablename( 'referrals' );
			if ( $referrals = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $referrals_table WHERE referral_id = %d", $referral_id ) ) ) {
				if ( count( $referrals ) > 0 ) {
					$referral = $referrals[0];
					$affiliate_id = $referral->affiliate_id;
					$datetime     = $referral->datetime;
					$description  = wp_strip_all_tags( $referral->description );
					$amount       = $referral->amount;
					$currency_id  = $referral->currency_id;
					$status       = $referral->status;
					$reference    = wp_strip_all_tags( $referral->reference );

					$output .= '<form id="referral" action="' . esc_url( $current_url ) . '" method="post">';
					$output .= '<div>';
					
					$output .= sprintf( '<input type="hidden" name="referral_id" value="%d" />', intval( $referral_id ) );
					
					$output .= '<input type="hidden" name="action" value="edit" />';
					
					$output .= '<p>';
					$output .= '<span class="title">' . __( 'Affiliate', 'affiliates' ) . '</span>';
					$output .= ' ';
					$affiliate = affiliates_get_affiliate( $affiliate_id );
					$output .= stripslashes( $affiliate['name'] );
					$output .= '</p>';
					
					$output .= '<p>';
					$output .= '<span class="title">' . __( 'Date & Time', 'affiliates' ) . '</span>';
					$output .= ' ';
					$output .= $datetime;
					$output .= '</p>';
					
					$output .= '<p>';
					$output .= '<span class="title">' . __( 'Description', 'affiliates' ) . '</span>';
					$output .= ' ';
					$output .= $description;
					$output .= '</p>';
					
					$output .= '<p>';
					$output .= '<span class="title">' . __( 'Amount', 'affiliates' ) . '</span>';
					$output .= ' ';
					$output .= $amount;
					$output .= '</p>';
					
					$output .= '<p>';
					$output .= '<span class="title">' . __( 'Currency ID', 'affiliates' ) . '</span>';
					$output .= ' ';
					$output .= $currency_id;
					$output .= '</p>';
					
					$status_descriptions = array(
						AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', 'affiliates' ),
						AFFILIATES_REFERRAL_STATUS_CLOSED   => __( 'Closed', 'affiliates' ),
						AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', 'affiliates' ),
						AFFILIATES_REFERRAL_STATUS_REJECTED => __( 'Rejected', 'affiliates' ),
					);
					$output .= '<p>';
					$output .= '<span class="title">' . __( 'Status', 'affiliates' ) . '</span>';
					$output .= ' ';
					$output .= $status_descriptions[$status];
					$output .= '</p>';
					
					$output .= '<p>';
					$output .= '<span class="title">' . __( 'Reference', 'affiliates' ) . '</span>';
					$output .= ' ';
					$output .= $reference;
					$output .= '</p>';
					
					$output .= wp_nonce_field( 'remove', 'referral-nonce', true, false );
					
					$output .= '<p class="description">';
					$output .= __( 'Remove this referral? This action can not be undone.', 'affiliates' );
					$output .= '</p>';
					
					$output .= sprintf( '<input class="button button-primary" type="submit" name="submit" value="%s"/>', __( 'Remove', 'affiliates' ) );
					$output .= ' ';
					$output .= sprintf( '<a class="cancel button" href="%s">%s</a>', $cancel_url, __( 'Cancel', 'affiliates' ) );
					
					$output .= '</div>';
					$output .= '</form>';
				} else {
					$output .= '<div class="error">' . __( 'This referral does not exist.', 'affiliates' ) . '</div>';
				}
			} else {
				$output .= '<div class="error">' . __( 'This referral does not exist.', 'affiliates' ) . '</div>';
			}
		} else {
			$output .= '<div class="error">' . __( 'Pretty pointless ...', 'affiliates' ) . '</div>';
		}
	}

	$output .= '</div>';

	echo $output;

	affiliates_footer();
}
