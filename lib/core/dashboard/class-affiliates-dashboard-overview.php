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

	/**
	 * @var int time period for recent stats, "days back"
	 */
	const PERIOD = 91;

	/**
	 * @var int trailing padding days
	 */
	const TRAIL = 1;

	/**
	 * @var int leading padding days
	 */
	const LEAD = 1;

	/**
	 * @var array time series
	 */
	private $series = array();

	/**
	 * @var array per currency totals
	 */
	private $totals = array();

	/**
	 * @var int affiliate ID
	 */
	private $affiliate_id = null;

	/**
	 * Initialization - nothing done here at current.
	 */
	public static function init() {
	}

	public function __construct( $params = array() ) {
		$this->template = 'dashboard/overview.php';
		$this->require_user_id = true;
		parent::__construct( $params );
	}

	/**
	 * Create a new dashboard section instance.
	 *
	 * Parameters :
	 * - user_id : if not provided, will obtain it from the current user
	 *
	 * @param array $params
	 */
	public function render() {

		global $wpdb, $affiliates_version;

		if ( !affiliates_user_is_affiliate( $this->user_id ) ) {
			return;
		}

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

		$affiliate_ids = affiliates_get_user_affiliate( $this->user_id );
		$affiliate_id = array_shift( $affiliate_ids );
		$this->affiliate_id = $affiliate_id;

		$hits_total = 0;
		$visits_total = 0;
		$referrals_total = 0;
		$amounts_by_currency_total = array();

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

		// Build the time series represented in the graph.
		$referrals_series = array();
		$hits_series      = array();
		$visits_series    = array();
		$ticks            = array();
		$dates            = array();
		$amounts_by_currency_series = array();
		for ( $day = -self::PERIOD-self::TRAIL; $day <= self::LEAD; $day++ ) {
			$date = date( 'Y-m-d', strtotime( $thru_date ) + $day * 3600 * 24 );
			$dates[$day] = $date;
			// For referrals we add an item with accumulated daily value.
			if ( isset( $referrals[$date] ) ) {
				$referrals_series[] = array( $day, intval( $referrals[$date] ) );
			} else {
				$referrals_series[] = array( $day, 0 );
			}
			// For hits we add daily hits where there are any, otherwise we add a zero entry.
			if ( isset( $hits[$date] ) ) {
				$hits_series[] = array( $day, intval( $hits[$date] ) );
			} else {
				$hits_series[] = array( $day, 0 );
			}
			// ... same thing for visits.
			if ( isset( $visits[$date] ) ) {
				$visits_series[] = array( $day, intval( $visits[$date] ) );
			} else {
				$visits_series[] = array( $day, 0 );
			}
			// Where we have a value for a date (accumulated daily earnings), use it,
			// otherwise add a 0 entry for the date.
			foreach ( $amounts_by_currency as $currency_id => $amounts ) {
				if ( isset( $amounts_by_currency[$currency_id][$date] ) ) {
					$amounts_by_currency_series[$currency_id][] = array( $day, floatval( $amounts_by_currency[$currency_id][$date] ) );
				} else {
					$amounts_by_currency_series[$currency_id][] = array( $day, 0.0 );
				}
			}
			// Add a tick per month at day 1
			$d = intval( date( 'd', strtotime( $date ) ) );
			if ( $d === 1 ) {
				// Note that the date_format option based format is just to wide :
				// esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) )
				$label = esc_html( date_i18n( 'M Y', strtotime( $date ) ) );
				$ticks[] = array( $day, $label );
			}
		}

		// The span series is used to add trailing and leading padding.
		$span_series = array( array( intval( -self::PERIOD-self::TRAIL ), self::LEAD ), array( 0, 0 ) );

		$this->series = array(
			'hits'      => $hits_series,
			'visits'    => $visits_series,
			'referrals' => $referrals_series,
			'span'      => $span_series,
			'ticks'     => $ticks,
			'dates'     => $dates,
			'amounts_by_currency' => $amounts_by_currency_series
		);

		$referrals_series_json = json_encode( $referrals_series );
		$hits_series_json      = json_encode( $hits_series );
		$visits_series_json    = json_encode( $visits_series );
		$span_series_json      = json_encode( $span_series );
		$ticks_json            = json_encode( $ticks );
		$dates_json            = json_encode( $dates );
		$amounts_by_currency_series_json = json_encode( $amounts_by_currency_series );

		// Draw the graph ...
		echo '<script type="text/javascript">';
		echo 'if ( typeof jQuery !== "undefined" ) {';
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
		echo '}';
		echo '</script>';

		parent::render();
	}

	/**
	 * Provides the per-currency totals
	 *
	 * @return array
	 */
	public function get_totals() {
		return $this->totals;
	}

	/**
	 * Provides the affiliate ID.
	 *
	 * @return int
	 */
	public function get_affiliate_id() {
		return $this->affiliate_id;
	}

	public static function get_name() {
		return __( 'Overview', 'affiliates' );
	}

	public static function get_key() {
		return 'overview';
	}

}
Affiliates_Dashboard_Overview::init();
