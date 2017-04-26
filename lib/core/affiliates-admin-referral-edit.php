<?php
/**
 * affiliates-admin-referral-edit.php
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
 * Create/Edit referral form.
 */
function affiliates_admin_referral_edit( $referral_id = null ) {

	global $wpdb, $affiliates_options;

	$output = '';

	if ( !current_user_can( AFFILIATES_ADMINISTER_AFFILIATES ) ) {
		wp_die( __( 'Access denied.', 'affiliates' ) );
	}

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$cancel_url  = remove_query_arg( 'referral_id', remove_query_arg( 'action', $current_url ) );
	$current_url = remove_query_arg( 'paged', $current_url );
	$current_url = remove_query_arg( 'affiliate_id', $current_url );

	if ( $referral_id === null ) {
		$referral_id  = isset( $_POST['referral_id'] ) ? intval( $_POST['referral_id'] ) : null;
	}
	$affiliate_id = isset( $_POST['affiliate_id'] ) ? intval( $_POST['affiliate_id'] ) : null;
	$datetime     = isset( $_POST['datetime'] ) ? date( 'Y-m-d H:i:s', strtotime( $_POST['datetime'] ) ) : date( 'Y-m-d H:i:s', time() );
	$description  = isset( $_POST['description'] ) ? wp_strip_all_tags( $_POST['description'] ) : '';
	$reference_amount = !empty( $_POST['reference_amount'] ) ? affiliates_format_referral_amount( $_POST['reference_amount'] ) : null;
	$amount       = !empty( $_POST['amount'] ) ? affiliates_format_referral_amount( $_POST['amount'] ) : null;
	$currency_id  = substr( strtoupper( isset( $_POST['currency_id'] ) ? wp_strip_all_tags( $_POST['currency_id'] ) : '' ), 0, 3 );
	$status       = $affiliates_options->get_option( 'referrals_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );
	if ( isset( $_POST['status'] ) ) {
		switch ( $_POST['status'] ) {
			case AFFILIATES_REFERRAL_STATUS_ACCEPTED :
			case AFFILIATES_REFERRAL_STATUS_CLOSED :
			case AFFILIATES_REFERRAL_STATUS_PENDING :
			case AFFILIATES_REFERRAL_STATUS_REJECTED :
				$status = $_POST['status'];
				break;
		}
	}
	$reference  = isset( $_POST['reference'] ) ? wp_strip_all_tags( $_POST['reference'] ) : '';

	$saved = false;
	if ( isset( $_POST['save'] ) ) {
		if ( !wp_verify_nonce( $_POST['referral-nonce'], 'save' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		} else {
			if ( !empty( $affiliate_id ) ) {
				if ( empty( $referral_id ) ) {
					$action = apply_filters(
						'affiliates_admin_referral_edit_add_referral',
						array( 'add_referral' => true, 'output' => '' ),
						compact( 'referral_id', 'affiliate_id', 'datetime', 'description', 'reference_amount', 'amount', 'currency_id', 'status', 'reference' )
					);
					$output .= !empty( $action['output'] ) ? $action['output'] : '';
					if ( !empty( $action['referral_id'] ) ) {
						$referral_id = $action['referral_id'];
					}
					if ( isset( $action['add_referral'] ) && $action['add_referral'] ) {
						global $captured_referral_id;
						add_action( 'affiliates_referral', 'affiliates_admin_referral_capture_id' );
						affiliates_add_referral( $affiliate_id, null, $description, null, $amount, $currency_id, $status, 'manual', $reference );
						remove_action( 'affiliates_referral', 'affiliates_admin_referral_capture_id' );
						if ( !empty( $captured_referral_id ) ) {
							$referral_id = $captured_referral_id;
						}
					}
					if ( !empty( $referral_id ) ) {
						$output .= '<br/>';
						$output .= '<div class="info">' . __( 'The referral has been created.', 'affiliates' ) . '</div>';
						$saved = true;
					} else {
						$output .= '<br/>';
						$output .= '<div class="warning">' . __( 'The referral has not been created. Duplicate?', 'affiliates' ) . '</div>';
					}
				} else {
					$action = apply_filters(
						'affiliates_admin_referral_edit_update_referral',
						array( 'update_referral' => true, 'output' => '' ),
						compact( 'referral_id', 'affiliate_id', 'datetime', 'description', 'amount', 'currency_id', 'status', 'reference' )
					);
					$output .= !empty( $action['output'] ) ? $action['output'] : '';
					if ( isset( $action['update_referral'] ) && $action['update_referral'] ) {
						if ( affiliates_update_referral( $referral_id, array(
							'affiliate_id' => intval( $affiliate_id ),
							'datetime'     => $datetime,
							'description'  => $description,
							'reference_amount' => $reference_amount,
							'amount'       => $amount,
							'currency_id'  => $currency_id,
							'status'       => $status,
							'reference'    => $reference
						) ) ) {
							$output .= '<br/>';
							$output .= '<div class="info">' . __( 'The referral has been saved.', 'affiliates' ) . '</div>';
							$saved = true;
						}
					}
				}
			}
		}
	}

	if ( $referral_id !== null ) {
		$referrals_table = _affiliates_get_tablename( 'referrals' );
		if ( $referrals = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $referrals_table WHERE referral_id = %d", $referral_id ) ) ) {
			if ( count( $referrals ) > 0 ) {
				$referral = $referrals[0];
				$affiliate_id = $referral->affiliate_id;
				$datetime     = $referral->datetime;
				$description  = $referral->description;
				$reference_amount = $referral->reference_amount;
				$amount       = $referral->amount;
				$currency_id  = $referral->currency_id;
				$status       = $referral->status;
				$reference    = $referral->reference;
			}
		}
	}

	$output .= sprintf( '<div class="referral %s referrals-overview">', empty( $referral_id ) ? 'new-referral' : 'edit-referral' );
	$output .= '<h1>';
	if ( empty( $referral_id ) ) {
		$output .= __( 'New Referral', 'affiliates' );
	} else {
		$output .= __( 'Edit Referral', 'affiliates' );
	}
	$output .= '</h1>';

	$output .= '<form id="referral" action="' . esc_url( $current_url ) . '" method="post">';
	$output .= '<div>';

	if ( $referral_id ) {
		$output .= sprintf( '<input type="hidden" name="referral_id" value="%d" />', intval( $referral_id ) );
	}

	$output .= '<input type="hidden" name="action" value="edit" />';

	$output .= '<p>';
	$output .= '<label>';
	$output .= '<span class="title">' . __( 'Affiliate', 'affiliates' ) . '</span>';
	$output .= ' ';
	$affiliates = affiliates_get_affiliates( true, true );
	$output .= '<select name="affiliate_id" class="affiliates-uie" >';
	foreach ( $affiliates as $affiliate ) {
		if ( $affiliate_id == $affiliate['affiliate_id']) {
			$selected = ' selected="selected" ';
		} else {
			$selected = '';
		}
		$output .= '<option ' . $selected . ' value="' . esc_attr( $affiliate['affiliate_id'] ) . '">' . esc_attr( stripslashes( $affiliate['name'] ) ) . '</option>';
	}
	$output .= '</select>';
	$output .= '</label>';
	$output .= '</p>';
	$output .= Affiliates_UI_Elements::render_select( 'select.affiliates-uie' );

	$output .= '<p>';
	$output .= '<label>';
	$output .= '<span class="title">' . __( 'Date & Time', 'affiliates' ) . '</span>';
	$output .= ' ';
	$output .= sprintf( '<input type="text" name="datetime" value="%s" />', esc_attr( $datetime ) );
	$output .= ' ';
	$output .= '<span class="description">' . __( 'Format : YYYY-MM-DD HH:MM:SS', 'affiliates' ) . '</span>';
	$output .= '</label>';
	$output .= '</p>';

	$output .= '<p>';
	$output .= '<label>';
	$output .= '<span class="title">' . __( 'Description', 'affiliates' ) . '</span>';
	$output .= ' ';
	$output .= '<textarea name="description">';
	$output .= stripslashes( $description );
	$output .= '</textarea>';
	$output .= '</label>';
	$output .= '</p>';

	$output .= '<p>';
	$output .= '<label>';
	$output .= '<span class="title">' . __( 'Amount', 'affiliates' ) . '</span>';
	$output .= ' ';
	$output .= sprintf( '<input type="text" name="amount" value="%s" />', esc_attr( $amount ) );
	$output .= '</label>';
	$output .= '</p>';

	$output .= '<p>';
	$output .= '<label>';
	$output .= '<span class="title">' . __( 'Currency ID', 'affiliates' ) . '</span>';
	$output .= ' ';
	$output .= sprintf( '<input type="text" name="currency_id" value="%s" />', esc_attr( $currency_id ) );
	$output .= ' ';
	$output .= '<span class="description">' . __( '* Required when an amount is provided. Examples: USD, GBP, EUR, ...', 'affiliates' ) . '</span>';
	$output .= '</label>';
	$output .= '</p>';

	$status_descriptions = array(
		AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', 'affiliates' ),
		AFFILIATES_REFERRAL_STATUS_CLOSED   => __( 'Closed', 'affiliates' ),
		AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', 'affiliates' ),
		AFFILIATES_REFERRAL_STATUS_REJECTED => __( 'Rejected', 'affiliates' ),
	);
	$output .= '<p>';
	$output .= '<label>';
	$output .= '<span class="title">' . __( 'Status', 'affiliates' ) . '</span>';
	$output .= ' ';
	$output .= '<select name="status">';
	foreach ( $status_descriptions as $key => $label ) {
		$selected = $key == $status ? ' selected="selected" ' : '';
		$output .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . $label . '</option>';
	}
	$output .= '</select>';
	$output .= '</label>';
	$output .= '</p>';

	$output .= '<p>';
	$output .= '<label>';
	$output .= '<span class="title">' . __( 'Reference', 'affiliates' ) . '</span>';
	$output .= ' ';
	$output .= sprintf( '<input type="text" name="reference" value="%s" />', esc_attr( $reference ) );
	$output .= '</label>';
	$output .= '</p>';

	$output .= '<p>';
	$output .= '<label>';
	$output .= '<span class="title">' . __( 'Reference Amount', 'affiliates' ) . '</span>';
	$output .= ' ';
	$output .= sprintf( '<input type="text" name="reference_amount" value="%s" />', esc_attr( $reference_amount ) );
	$output .= '</label>';
	$output .= '</p>';

	$output .= apply_filters(
		'affiliates_admin_referral_edit_form_suffix',
		'',
		compact( 'referral_id', 'affiliate_id', 'datetime', 'description', 'reference_amount', 'amount', 'currency_id', 'status', 'reference' )
	);

	$output .= wp_nonce_field( 'save', 'referral-nonce', true, false );

	$output .= sprintf( '<input class="button button-primary" type="submit" name="save" value="%s"/>', __( 'Save', 'affiliates' ) );
	$output .= ' ';
	$output .= sprintf( '<a class="cancel button" href="%s">%s</a>', $cancel_url, $saved ? __( 'Back', 'affiliates' ) : __( 'Cancel', 'affiliates' ) );

	$output .= '</div>';
	$output .= '</form>';

	$output .= '</div>';

	echo $output;

	affiliates_footer();
}

/**
 * Captures the referral ID for a new referral.
 * @param int $referral_id
 */
function affiliates_admin_referral_capture_id( $referral_id ) {
	global $captured_referral_id;
	$captured_referral_id = $referral_id;
}
