<?php
/**
 * class-affiliates-dashboard-overview.php
 *
 * Copyright (c) 2010 - 2018 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 4.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard section: Overview
 */
class Affiliates_Dashboard_Overview extends Affiliates_Dashboard_Section {

	const PERIOD = 91;

	private $series = array();

	private $totals = array();

	/**
	 * Initialization - nothing done here at current.
	 */
	public static function init() {
	}

	/**
	 * Create a new dashboard section instance.
	 *
	 * Parameters :
	 * - user_id : if not provided, will obtain it from the current user
	 *
	 * @param array $params
	 */
	public function __construct( $params = array() ) {

		global $wpdb, $affiliates_version;

		$this->template = 'dashboard/overview.php';
		$this->require_user_id = true;
		parent::__construct( $params );

		if ( !affiliates_user_is_affiliate() ) {
			return;
		}

		// @todo register these and enqueue, also in wp-init.php
		wp_enqueue_script( 'excanvas', AFFILIATES_PLUGIN_URL . 'js/graph/flot/excanvas.min.js', array( 'jquery' ), $affiliates_version );
		wp_enqueue_script( 'flot', AFFILIATES_PLUGIN_URL . 'js/graph/flot/jquery.flot.min.js', array( 'jquery' ), $affiliates_version );
		wp_enqueue_script( 'flot-resize', AFFILIATES_PLUGIN_URL . 'js/graph/flot/jquery.flot.resize.min.js', array( 'jquery', 'flot' ), $affiliates_version );
		wp_enqueue_script( 'affiliates-dashboard-overview-graph', AFFILIATES_PLUGIN_URL . 'js/dashboard-overview-graph.js', array( 'jquery', 'flot', 'flot-resize' ), $affiliates_version );

		wp_localize_script( 'affiliates-dashboard-overview-graph', 'affiliates_dashboard_overview_graph_l12n', array(
			'hits' => __( 'Hits', 'affiliates' ),
			'visits' => __( 'Visits', 'affiliates' ),
			'referrals' => __( 'Referrals', 'affiliates' )
		) );

		// Prepare graph data ...
		$thru_date = date( 'Y-m-d', time() );
		$from_date = date( 'Y-m-d', strtotime( $thru_date ) - self::PERIOD * 3600 * 24 );

		$affiliates_table = _affiliates_get_tablename( 'affiliates' );
		$hits_table = _affiliates_get_tablename( 'hits' );
		$referrals_table = _affiliates_get_tablename( 'referrals' );

		$affiliate_ids = affiliates_get_user_affiliate( get_current_user_id() );
		$affiliate_id = array_shift( $affiliate_ids );

		$hits_total = 0;
		$visits_total = 0;
		$referrals_total = 0;
		$amount_by_currency_total = array();

		// hits per day
		$query = "SELECT date, sum(count) as hits FROM $hits_table WHERE date >= %s AND date <= %s AND affiliate_id = %d GROUP BY date";
		$hit_results = $wpdb->get_results( $wpdb->prepare( $query, $from_date, $thru_date, intval( $affiliate_id ) ) );
		$hits = array();
		foreach ( $hit_results as $hit_result ) {
			$hits[$hit_result->date] = $hit_result->hits;
			$hits_total += $hit_result->hits;
		}

		// visits per day
		$query = "SELECT count(DISTINCT IP) visits, date FROM $hits_table WHERE date >= %s AND date <= %s AND affiliate_id = %d GROUP BY date";
		$visit_results = $wpdb->get_results( $wpdb->prepare( $query, $from_date, $thru_date, intval( $affiliate_id ) ) );
		$visits = array();
		foreach ( $visit_results as $visit_result ) {
			$visits[$visit_result->date] = $visit_result->visits;
			$visits_total += $visit_result->visits;
		}

		// accepted and closed referrals per day
		$query = "SELECT count(referral_id) referrals, date(datetime) date FROM $referrals_table WHERE status IN (%s,%s) AND date(datetime) >= %s AND date(datetime) <= %s AND affiliate_id = %d GROUP BY date";
		$query = $wpdb->prepare( $query, AFFILIATES_REFERRAL_STATUS_ACCEPTED, AFFILIATES_REFERRAL_STATUS_CLOSED, $from_date, $thru_date, intval( $affiliate_id ) );
		$results = $wpdb->get_results( $query );
		$referrals = array();
		foreach ( $results as $result ) {
			$referrals[$result->date] = $result->referrals;
			$referrals_total += $result->referrals;
		}

		// amounts by currency
		$query = "SELECT sum(amount) amount, currency_id, date(datetime) date FROM $referrals_table WHERE status IN (%s,%s) AND date(datetime) >= %s AND date(datetime) <= %s AND affiliate_id = %d GROUP BY date, currency_id";
		$query = $wpdb->prepare( $query, AFFILIATES_REFERRAL_STATUS_ACCEPTED, AFFILIATES_REFERRAL_STATUS_CLOSED, $from_date, $thru_date, intval( $affiliate_id ) );
		$results = $wpdb->get_results( $query );
		$amounts_by_currency = array();
		foreach ( $results as $result ) {
			$amounts_by_currency[$result->currency_id][$result->date] = $result->amount;
			if ( !isset( $amounts_by_currency_total[$result->currency_id] ) ) {
				$amounts_by_currency_total[$result->currency_id] = 0;
			}
			$amounts_by_currency_total[$result->currency_id] += $result->amount;
		}

		$this->totals['hits'] = $hits_total;
		$this->totals['visits'] = $visits_total;
		$this->totals['referrals'] = $referrals_total;
		$this->totals['amounts_by_currency'] = $amounts_by_currency_total;

		$referrals_series = array();
		$hits_series      = array();
		$visits_series    = array();
		$ticks            = array();
		$dates            = array();
		$amounts_by_currency_series = array();

		$days_back = self::PERIOD;
		$min_days_back = 14;
		$day_interval = 7;

		$trail = 1;
		$ahead = 1;

		for ( $day = -$days_back-$trail; $day <= $ahead; $day++ ) {
			$date = date( 'Y-m-d', strtotime( $thru_date ) + $day * 3600 * 24 );
			$dates[$day] = $date;
			if ( isset( $referrals[$date] ) ) {
				$referrals_series[] = array( $day, intval( $referrals[$date] ) );
			}
			if ( isset( $hits[$date] ) ) {
				$hits_series[]   = array( $day, intval( $hits[$date] ) );
			}
			if ( isset( $visits[$date] ) ) {
				$visits_series[]   = array( $day, intval( $visits[$date] ) );
			}
			foreach ( $amounts_by_currency as $currency_id => $amounts ) {
				if ( isset( $amounts_by_currency[$currency_id][$date] ) ) {
					$amounts_by_currency_series[$currency_id][] = array( $day, floatval( $amounts_by_currency[$currency_id][$date] ) );
				} else {
					$amounts_by_currency_series[$currency_id][] = array( $day, 0.0 );
				}
			}

			// @todo review this as we don't actually need $min_days_back here
			if ( $days_back <= ( $day_interval + $min_days_back ) ) {
				$label   = date( 'm-d', strtotime( $date ) );
				$ticks[] = array( $day, $label );
			} else if ( $days_back <= 91 ) {
				$d = date( 'd', strtotime( $date ) );
				if (  $d == '1' || $d == '15' ) {
					$label   = date( 'm-d', strtotime( $date ) );
					$ticks[] = array( $day, $label );
				}
			} else {
				if ( date( 'd', strtotime( $date ) ) == '1' ) {
					if ( date( 'm', strtotime( $date ) ) == '1' ) {
						$label   = '<strong>' . date( 'Y', strtotime( $date ) ) . '</strong>';
					} else {
						$label   = date( 'm-d', strtotime( $date ) );
					}
					$ticks[] = array( $day, $label );
				}
			}
		}

		$referrals_series_json = json_encode( $referrals_series );
		$hits_series_json      = json_encode( $hits_series );
		$visits_series_json    = json_encode( $visits_series );
		$span_series_json      = json_encode( array( array( intval( -$days_back-$trail ), $ahead ), array( 0, 0 ) ) );
		$ticks_json            = json_encode( $ticks );
		$dates_json            = json_encode( $dates );
		$amounts_by_currency_series_json = json_encode( $amounts_by_currency_series );

		// Draw the graph ...
		echo '<script type="text/javascript">';
		echo 'jQuery( document ).ready( function() {';
		echo 'if ( typeof affiliates_dashboard_overview_graph !== "undefined" ) {';
		printf(
			'affiliates_dashboard_overview_graph.render( "%s", "%s", %s, %s, %s, %s, %s, %s, %s );',
			'affiliates-dashboard-overview-graph',
			'affiliates-dashboard-overview-legend',
			$hits_series_json,
			$visits_series_json,
			$referrals_series_json,
			$amounts_by_currency_series_json,
			$span_series_json,
			$ticks_json,
			$dates_json
		);
		echo '}';
		echo '} );';
		echo '</script>';
	}

	public function get_totals() {
		return $this->totals;
	}
}
Affiliates_Dashboard_Overview::init();
