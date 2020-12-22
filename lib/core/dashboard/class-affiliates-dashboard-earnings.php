<?php
/**
 * class-affiliates-dashboard-earnings.php
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
 * Dashboard section: Earnings
 */
class Affiliates_Dashboard_Earnings extends Affiliates_Dashboard_Section_Table {

	/**
	 * @var int Second place after overview.
	 */
	protected static $section_order = 200;

	/**
	 * @var array holds default values for options
	 */
	protected static $defaults = array(
		// filter attributes
		'from_date'          => null,
		'thru_date'          => null,
		// view attributes
		'per_page'           => self::PER_PAGE_DEFAULT,
		'status'             => array( AFFILIATES_REFERRAL_STATUS_ACCEPTED, AFFILIATES_REFERRAL_STATUS_CLOSED )
	);

	/**
	 * {@inheritDoc}
	 * @see I_Affiliates_Dashboard_Section::get_section_order()
	 */
	public static function get_section_order() {
		return self::$section_order;
	}

	/**
	 * Obtain the URL to the section maintaining current settings.
	 * Specify an array of key-value pairs in $params for parameters to add or replace.
	 * A key-value pair with value null in $params will remove a parameter.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public function get_url( $params = array() ) {

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$current_url = remove_query_arg( 'clear_filters', $current_url );
		$current_url = remove_query_arg( 'apply_filters', $current_url );

		foreach ( $this->url_parameters as $parameter ) {
			$current_url = remove_query_arg( $parameter, $current_url );
			$value = null;
			switch ( $parameter ) {
				case 'per_page' :
					$value = $this->get_per_page();
					break;
				case 'from_date' :
					$value = $this->get_from_date();
					break;
				case 'thru_date' :
					$value = $this->get_thru_date();
					break;
				case 'orderby' :
					$value = $this->get_orderby();
					break;
				case 'order' :
					$value = $this->get_sort_order();
					break;
			}
			if ( $value !== null ) {
				$current_url = add_query_arg( $parameter, $value, $current_url );
			}
		}

		foreach ( $params as $key => $value ) {
			$current_url = remove_query_arg( $key, $current_url );
			if ( $value !== null ) {
				$current_url = add_query_arg( $key, $value, $current_url );
			}
		}

		return $current_url;
	}

	/**
	 * Pull in the DateHelper class used in the render() method.
	 */
	public static function init() {
		require_once( AFFILIATES_CORE_LIB . '/class-affiliates-date-helper.php' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name() {
		return __( 'Earnings', 'affiliates' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_key() {
		return 'earnings';
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
		$this->template = 'dashboard/earnings.php';
		$this->require_user_id = true;
		parent::__construct( $params );

		$this->url_parameters = array(
			'earnings-page',
			'per_page',
			'from_date',
			'thru_date',
			'orderby',
			'order'
		);

		$params = shortcode_atts( self::$defaults, $params );
		foreach ( $params as $key => $value ) {
			switch( $key ) {
				case 'per_page' :
					$value = intval( $value );
					if ( $value < 0 ) {
						$value = self::$defaults['per_page'];
					}
					break;
				case 'status' :
					if ( !is_array( $value ) ) {
						if ( is_string( $value ) ) {
							$value = array_map( 'trim', explode( ',', $value ) );
						} else {
							$value = self::$defaults['status'];
						}
					}
					$values = array();
					foreach ( $value as $status ) {
						switch ( $status ) {
							case AFFILIATES_REFERRAL_STATUS_ACCEPTED :
							case AFFILIATES_REFERRAL_STATUS_CLOSED :
							case AFFILIATES_REFERRAL_STATUS_PENDING :
							case AFFILIATES_REFERRAL_STATUS_REJECTED :
								$values[] = $status;
								break;
						}
					}
					$value = $values;
					break;
			}
			$params[$key] = $value;
		}
		$this->per_page = $params['per_page'];
		$this->status = $params['status'];

		$this->column_display_names = array();

		$this->columns['period'] = array(
			'title'       => __( 'Period', 'affiliates' ),
			'description' => __( 'The earnings period for the amounts earned.', 'affiliates' )
		);
		$this->columns['earnings'] = array(
			'title'       => __( 'Earnings', 'affiliates' ),
			'description' => __( 'The earnings for the period covered.', 'affiliates' )
		);
		$this->columns['paid'] = array(
			'title'       => __( 'Paid', 'affiliates' ),
			'description' => __( 'The earnings paid for the period covered.', 'affiliates' )
		);
	}

	/**
	 * Prepares data for the current page and invokes the parent's render method.
	 *
	 * {@inheritDoc}
	 * @see Affiliates_Dashboard_Section::render()
	 */
	public function render() {
		global $wpdb, $affiliates_options, $affiliates_version;

		wp_enqueue_script( 'datepicker', AFFILIATES_PLUGIN_URL . 'js/jquery-ui.min.js', array( 'jquery', 'jquery-ui-core' ), $affiliates_version );
		wp_enqueue_script( 'datepickers', AFFILIATES_PLUGIN_URL . 'js/datepickers.js', array( 'jquery', 'jquery-ui-core', 'datepicker' ), $affiliates_version );
		wp_enqueue_style( 'smoothness', AFFILIATES_PLUGIN_URL . 'css/smoothness/jquery-ui.min.css', array(), $affiliates_version );

		$affiliate_id = $this->get_affiliate_id();

		$per_page = !empty( $_REQUEST['per_page'] ) ?
			min( max( 1, intval( trim( $_REQUEST['per_page'] ) ) ), self::MAX_PER_PAGE ) :
			null;
		if ( $per_page !== null ) {
			$this->per_page = intval( $per_page );
		} else {
			$per_page = self::PER_PAGE_DEFAULT;
		}
		$current_page = isset( $_REQUEST['earnings-page'] ) ? max( 0, intval( $_REQUEST['earnings-page'] ) ) : 0;

		// filters
		$from_date = isset( $_REQUEST['from_date'] ) ? trim( $_REQUEST['from_date'] ) : null;
		$thru_date = isset( $_REQUEST['thru_date'] ) ? trim( $_REQUEST['thru_date'] ) : null;

		if ( isset( $_REQUEST['clear_filters'] ) ) {
			unset( $_REQUEST['from_date'] );
			unset( $_REQUEST['thru_date'] );
			$from_date     = null;
			$thru_date     = null;
		} else {
			// filter by date(s)
			if ( !empty( $_REQUEST['from_date'] ) ) {
				$from_date = date( 'Y-m-d', strtotime( $_REQUEST['from_date'] ) );
			} else {
				$from_date = null;
			}
			if ( !empty( $_REQUEST['thru_date'] ) ) {
				$thru_date = date( 'Y-m-d', strtotime( $_REQUEST['thru_date'] ) );
			} else {
				$thru_date = null;
			}
			if ( $from_date && $thru_date ) {
				if ( strtotime( $from_date ) > strtotime( $thru_date ) ) {
					$thru_date = null;
				}
			}
		}
		$this->from_date = $from_date;
		$this->thru_date = $thru_date;
		$this->current_page = $current_page;

		$this->orderby = isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : $this->orderby;
		switch ( $this->orderby ) {
			case 'period' :
				break;
			default :
				$this->orderby = 'period';
		}

		$this->sort_order = isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : $this->sort_order;
		switch ( $this->sort_order ) {
			case 'asc' :
			case 'ASC' :
				$this->switch_sort_order = 'DESC';
				break;
			case 'desc' :
			case 'DESC' :
				$this->switch_sort_order = 'ASC';
				break;
			default:
				$this->sort_order = 'DESC';
				$this->switch_sort_order = 'ASC';
		}

		$filters = " WHERE 1=%d ";
		$filter_params = array( 1 );
		// We now have the desired dates from the user's point of view, i.e. in her timezone.
		// If supported, adjust the dates for the site's timezone:
		if ( $from_date ) {
			$from_datetime = DateHelper::u2s( $from_date );
		}
		if ( $thru_date ) {
			$thru_datetime = DateHelper::u2s( $thru_date, 24*3600 );
		}
		if ( $from_date && $thru_date ) {
			$filters .= " AND r.datetime >= %s AND r.datetime <= %s ";
			$filter_params[] = $from_datetime;
			$filter_params[] = $thru_datetime;
		} else if ( $from_date ) {
			$filters .= " AND r.datetime >= %s ";
			$filter_params[] = $from_datetime;
		} else if ( $thru_date ) {
			$filters .= " AND r.datetime < %s ";
			$filter_params[] = $thru_datetime;
		}

		$filters .= " AND r.affiliate_id = %d ";
		$filter_params[] = $affiliate_id;

		$status_condition = '';
		if ( is_array( $this->status ) && count( $this->status ) > 0 ) {
			$status_condition = " AND ( r.status IS NULL OR r.status IN ('" . implode( "','", array_map( 'esc_sql',  $this->status ) ) . "') ) ";
			$filters .= $status_condition;
		}

		$offset = $this->per_page * $this->current_page;

		$referrals_table  = _affiliates_get_tablename( 'referrals' );

		$query_base =
			"SELECT SQL_CALC_FOUND_ROWS " .
			"YEAR(datetime) year, " .
			"MONTH(datetime) month, " .
			"SUM(amount) total, " .
			"SUM(IF(status='" . esc_sql( AFFILIATES_REFERRAL_STATUS_ACCEPTED ) . "',amount,0)) total_accepted, " .
			"SUM(IF(status='" . esc_sql( AFFILIATES_REFERRAL_STATUS_CLOSED ) . "',amount,0)) total_closed, " .
			"SUM(IF(status='" . esc_sql( AFFILIATES_REFERRAL_STATUS_PENDING ) . "',amount,0)) total_pending, " .
			"SUM(IF(status='" . esc_sql( AFFILIATES_REFERRAL_STATUS_REJECTED ) . "',amount,0)) total_rejected, " .
			"currency_id " .
			"FROM $referrals_table r " .
			"$filters " . 
			"GROUP BY YEAR(datetime), MONTH(datetime), currency_id ";

		$query_suffix = "ORDER BY YEAR(datetime) %s, MONTH(datetime) %s LIMIT %d OFFSET %d";

		$query = $query_base . sprintf( $query_suffix, $this->sort_order, $this->sort_order, intval( $this->per_page ), intval( $offset ) );

		$this->entries = $wpdb->get_results(
			$wpdb->prepare(
				$query,
				$filter_params
			),
			OBJECT
		);

		$this->count = intval( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );

		// Was this a query for a page beyond the last page?
		// If yes, reset and retrieve results for the first page.
		if ( count( $this->entries ) === 0 && $this->count > 0 ) {
			$this->current_page = 0;
			$query = $query_base . sprintf( $query_suffix, $this->sort_order, $this->sort_order, intval( $this->per_page ), 0 ); // OFFSET 0
			$this->entries = $wpdb->get_results(
				$wpdb->prepare(
					$query,
					$filter_params
				),
				OBJECT
			);
			$this->count = intval( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );
		}

		parent::render();
	}
}
Affiliates_Dashboard_Earnings::init();
