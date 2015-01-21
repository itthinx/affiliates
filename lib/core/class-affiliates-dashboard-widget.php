<?php
/**
 * class-affiliates-dashboard-widget.php
 * 
 * Copyright (c) 2010 - 2013 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 2.5.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shows referral totals on the dashboard.
 */
class Affiliates_Dashboard_Widget {

	const NONCE             = 'affiliates-dashboard-widget-nonce';
	const MIN_DAYS_BACK     = 0;
	const DEFAULT_DAYS_BACK = 0;

	/**
	 * Initialize the dashboard setup hook. 
	 */
	public static function init() {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'wp_dashboard_setup' ) );
	}

	/**
	 * Dashboard setup hook.
	 */
	public static function wp_dashboard_setup() {
		if ( current_user_can( AFFILIATES_ACCESS_AFFILIATES ) ) {
			wp_add_dashboard_widget( 'affiliates_dashboard_widget', 'Affiliates', array( __CLASS__, 'render' ) );
		}
	}

	/**
	 * Renders the dashboard widget.
	 */
	public static function render() {

		global $affiliates_options;

		$days_back = $affiliates_options->get_option( 'dashboard_days_back', self::DEFAULT_DAYS_BACK );
		if ( isset( $_POST['affiliates-dashboard-widget-submitted'] ) ) {
			if ( wp_verify_nonce( $_POST[self::NONCE], 'admin' ) ) {
				if ( !empty( $_POST['days_back'] ) ) {
					$days_back = abs( intval( $_POST['days_back'] ) );
					if ( $days_back < self::MIN_DAYS_BACK ) {
						$days_back = self::MIN_DAYS_BACK;
					}
					$affiliates_options->update_option( 'dashboard_days_back', $days_back );
				} else {
					$days_back = null;
					$affiliates_options->delete_option( 'dashboard_days_back' );
				}
			}
		}

		$from_date = null;
		$thru_date = null;
		if ( $days_back > self::MIN_DAYS_BACK ) {
			$thru_date = date( 'Y-m-d', time() ); // today
			$from_date = date( 'Y-m-d', strtotime( $thru_date ) - $days_back * 3600 * 24 );
		}

		$totals = array();
		$totals[AFFILIATES_REFERRAL_STATUS_CLOSED]   = self::get_totals( $from_date, $thru_date, AFFILIATES_REFERRAL_STATUS_CLOSED );
		$totals[AFFILIATES_REFERRAL_STATUS_ACCEPTED] = self::get_totals( $from_date, $thru_date, AFFILIATES_REFERRAL_STATUS_ACCEPTED );
		$totals[AFFILIATES_REFERRAL_STATUS_PENDING]  = self::get_totals( $from_date, $thru_date, AFFILIATES_REFERRAL_STATUS_PENDING );
		$totals[AFFILIATES_REFERRAL_STATUS_REJECTED] = self::get_totals( $from_date, $thru_date, AFFILIATES_REFERRAL_STATUS_REJECTED );

		$output = '';
		$output .= '<table class="affiliate-referral-totals">';
		$output .= '<thead>';
		$output .= '<tr>';
		foreach( $totals as $status => $total ) {
			$output .= '<th>';
			$output .= '<strong>';
			switch( $status ) {
				case AFFILIATES_REFERRAL_STATUS_CLOSED :
					$output .= sprintf( __( '<span style="cursor:help" title="%s">Closed</span>', AFFILIATES_PLUGIN_DOMAIN ), esc_attr( __( 'Accumulated total for closed referrals (commissions paid).', AFFILIATES_PLUGIN_DOMAIN ) ) );
					break;
				case AFFILIATES_REFERRAL_STATUS_ACCEPTED :
					$output .= sprintf( __( '<span style="cursor:help" title="%s">Accepted</span>', AFFILIATES_PLUGIN_DOMAIN ), esc_attr( __( 'Accumulated total for accepted referrals (commissions unpaid).', AFFILIATES_PLUGIN_DOMAIN ) ) );
					break;
				case AFFILIATES_REFERRAL_STATUS_PENDING :
					$output .= sprintf( __( '<span style="cursor:help" title="%s">Pending</span>', AFFILIATES_PLUGIN_DOMAIN ), esc_attr( __( 'Accumulated total for pending referrals.', AFFILIATES_PLUGIN_DOMAIN ) ) );
					break;
				case AFFILIATES_REFERRAL_STATUS_REJECTED :
					$output .= sprintf( __( '<span style="cursor:help" title="%s">Rejected</span>', AFFILIATES_PLUGIN_DOMAIN ), esc_attr( __( 'Accumulated total for rejected referrals.', AFFILIATES_PLUGIN_DOMAIN ) ) );
					break;
			}
			$output .= '</strong>';
			$output .= '</th>';
		}
		$output .= '</thead>';
		$output .= '</tr>';
		$output .= '<tbody>';
		$output .= '<tr>';
		foreach( $totals as $status => $total ) {
			$output .= '<td style="vertical-align:top">';
			$output .= '<ul>';
			if ( $total ) {
				foreach( $total as $currency => $amount ) {
					$output .= '<li>';
					$output .= sprintf( __( '%1$s %2$s', AFFILIATES_PLUGIN_DOMAIN ), $currency, $amount ); // translators: first is a three-letter currency code, second is a monetary amount
					$output .= '</li>';
				}
			} else {
				$output .= '<li>';
				$output .= __( 'None', AFFILIATES_PLUGIN_DOMAIN );
				$output .= '</li>';
			}
			$output .= '</ul>';
			$output .= '</td>';
		}
		$output .= '</tr>';
		$output .= '<tbody>';
		$output .= '</table>';

		$output .= '<form id="affiliates-dashboard-widget-form" action="" method="post">';
		$output .= '<div>';
		$output .= '<label>';
		$output .= __( 'Days back', AFFILIATES_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= '<input name="days_back" style="width:5em" type="text" value="' . esc_attr( $days_back > self::MIN_DAYS_BACK ? $days_back : '' ) . '"/>';
		$output .= '</label>';
		$output .= wp_nonce_field( 'admin', self::NONCE, true, false );
		$output .= ' ';
		$output .= '<input class="button button-primary" type="submit" value="Update" name="update">';
		$output .= '<input type="hidden" value="submitted" name="affiliates-dashboard-widget-submitted"/>';
		$output .= '</div>';
		$output .= '</form>';

		$output .= '<p class="description">';
		$output .= __( 'Shows accumulated referral totals for all time when left empty, or for the last number of days set.', AFFILIATES_PLUGIN_DOMAIN );
		$output .= '</p>';

		echo $output;
	}

	/**
	 * Returns totals for the given period or for all time.
	 * @param string $from_date
	 * @param string $thru_date
	 * @param string $status
	 */
	private static function get_totals( $from_date = null , $thru_date = null, $status = AFFILIATES_REFERRAL_STATUS_ACCEPTED ) {
		global $wpdb;
		$referrals_table = _affiliates_get_tablename( 'referrals' );
		$where = " WHERE TRUE ";
		$values = array();
		if ( $from_date ) {
			$from_date = date( 'Y-m-d', strtotime( $from_date ) );
		}
		if ( $thru_date ) {
			$thru_date = date( 'Y-m-d', strtotime( $thru_date ) + 24*3600 );
		}
		if ( $from_date && $thru_date ) {
			$where .= " AND datetime >= %s AND datetime < %s ";
			$values[] = $from_date;
			$values[] = $thru_date;
		} else if ( $from_date ) {
			$where .= " AND datetime >= %s ";
			$values[] = $from_date;
		} else if ( $thru_date ) {
			$where .= " AND datetime < %s ";
			$values[] = $thru_date;
		}
		if ( !empty( $status ) ) {
			$where .= " AND status = %s ";
			$values[] = $status;
		}
		$totals = $wpdb->get_results( $wpdb->prepare(
			"SELECT SUM(amount) total, currency_id FROM $referrals_table
			$where
			GROUP BY currency_id
			",
			$values
		) );
		if ( $totals ) {
			$result = array();
			foreach( $totals as $total ) {
				if ( ( $total->currency_id !== null ) && ( $total->total !== null ) ) {
					$result[$total->currency_id] = $total->total;
				}
			}
			return $result;
		} else {
			return false;
		}
	}
}
Affiliates_Dashboard_Widget::init();
