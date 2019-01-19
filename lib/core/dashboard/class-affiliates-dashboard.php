<?php
/**
 * class-affiliates-dashboard.php
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
 * Dashboard view.
 */
class Affiliates_Dashboard implements I_Affiliates_Dashboard {

	/**
	 * @var int
	 */
	private $user_id = null;

	/**
	 * @var array
	 */
	private $sections = null;

	/**
	 * @var string URL parameter used to identify the current section
	 */
	const SECTION_URL_PARAMETER = 'affiliates-dashboard-section';

	/**
	 * Initialization.
	 */
	public static function init() {
		Affiliates_Dashboard_Factory::set_dashboard_class( __CLASS__ );
	}

	/**
	 * Create a new dashboard instance.
	 *
	 * Parameters :
	 * - user_id : if not provided, will obtain it from the current user
	 *
	 * @param array $params
	 */
	public function __construct( $params = array() ) {
		if ( isset( $params['user_id'] ) ) {
			$this->user_id = intval( $params['user_id'] );
		} else {
			$this->user_id = get_current_user_id();
		}
		if ( $this->user_id === 0 ) {
			$this->user_id = null;
		} else {
			$user = get_user_by( 'id', $this->user_id );
			if ( $user === false || $user->ID === 0 ) {
				$this->user_id = null;
			}
		}
	}

	/**
	 * Outputs the dashboard for a connected user or the login form if not logged in.
	 */
	public function render() {
		$this->setup();
		Affiliates_Templates::include_template( 'dashboard/dashboard.php', array( 'dashboard' => $this ) );
	}

	/**
	 * Returns the dashboard's sections.
	 *
	 * @return array or null
	 */
	public function get_sections() {
		return $this->sections;
	}

	/**
	 * Returns the section instance for the given key.
	 *
	 * @param string $key
	 *
	 * @return Affiliates_Dashboard_Section or null
	 */
	public function get_section( $key ) {
		$section = null;
		if ( key_exists( $key, $this->sections ) ) {
			if ( !isset( $this->section_objects[$key] ) ) {
				$section = new $this->sections[$key]['class']( $this->sections[$key]['parameters'] );
				$this->section_objects[$key] = $section;
			} else {
				$section = $this->section_objects[$key];
			}
		}
		return $section;
	}

	/**
	 * Returns the current section.
	 *
	 * @return string current section or null
	 */
	public function get_current_section() {
		$section = null;
		if ( $this->sections !== null ) {
			if ( isset( $_REQUEST[self::SECTION_URL_PARAMETER] ) ) {
				$key = $_REQUEST[self::SECTION_URL_PARAMETER];
				if ( isset( $this->sections[$key] ) ) {
					$section = $this->get_section( $key );
				}
			}
			if ( $section === null ) {
				if ( count( $this->sections ) > 0 ) {
					$section_keys = array_keys( $this->sections );
					$first_key = array_shift( $section_keys );
					$section = $this->get_section( $first_key );
				}
			}
		}
		return $section;
	}

	/**
	 * Returns the URL to the dashboard with all section-specific parameters removed
	 * and the parameters in $params added or replaced.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public function get_url( $params = array() ) {
		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		// Common filter parameters ...
		$url_parameters = array( 'clear_filters', 'apply_filters' );
		// Section-specific parameters ...
		if ( $this->sections !== null ) {
			foreach ( array_keys( $this->sections ) as $key ) {
				$url_parameters = array_merge( $url_parameters, $this->get_section( $key )->get_url_parameters() );
			}
		}
		// Remove all those parameters to obtain a clear URL for the dashboard that can still contain
		// other parameters that are not related.
		foreach ( $url_parameters as $parameter ) {
			$current_url = remove_query_arg( $parameter, $current_url );
		}
		// Add/replace the requested parameters ...
		foreach ( $params as $key => $value ) {
			$current_url = remove_query_arg( $key, $current_url );
			if ( $value !== null ) {
				$current_url = add_query_arg( $key, $value, $current_url );
			}
		}
		return $current_url;
	}

	/**
	 * Returns the user ID related to this instance.
	 *
	 * @return int or null
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Adds sections.
	 */
	public function setup() {

		if ( $this->user_id === null ) {
			$sections = array(
				Affiliates_Dashboard_Login::get_key() => array(
					'class' => 'Affiliates_Dashboard_Login',
					'parameters' => array()
				),
				Affiliates_Dashboard_Registration::get_key() => array(
					'class' => 'Affiliates_Dashboard_Registration',
					'parameters' => array()
				)
			);
		} else {
			if ( !affiliates_user_is_affiliate( $this->user_id ) ) {
				$sections = array(
					Affiliates_Dashboard_Registration::get_key() => array(
						'class' => 'Affiliates_Dashboard_Registration',
						'parameters' => array( 'user_id' => $this->user_id )
					)
				);
			} else {
				$sections = array(
					Affiliates_Dashboard_Overview::get_key() => array(
						'class' => 'Affiliates_Dashboard_Overview',
						'parameters' => array( 'user_id' => $this->user_id )
					),
					Affiliates_Dashboard_Earnings::get_key() => array(
						'class' => 'Affiliates_Dashboard_Earnings',
						'parameters' => array( 'user_id' => $this->user_id )
					),
					Affiliates_Dashboard_Profile::get_key()  => array(
						'class' => 'Affiliates_Dashboard_Profile',
						'parameters' => array( 'user_id' => $this->user_id )
					)
				);
			}
		}

		$this->sections = apply_filters( 'affiliates_dashboard_setup_sections', $sections, $this );
		$this->sort_sections();
	}

	/**
	 * Sorts the associated dashboard sections based on their order.
	 */
	protected function sort_sections() {
		uasort( $this->sections, array( __CLASS__, 'compare_sections' ) );
	}

	/**
	 * Comparison callback.
	 *
	 * @param array $s1
	 * @param array $s2
	 *
	 * @return int
	 */
	public static function compare_sections( $s1, $s2 ) {
		$order1 = isset( $s1['parameters']['order'] ) ? $s1['parameters']['order'] : $s1['class']::get_section_order();
		$order2 = isset( $s2['parameters']['order'] ) ? $s2['parameters']['order'] : $s2['class']::get_section_order();
		return $order1 - $order2;
	}
}
Affiliates_Dashboard::init();
