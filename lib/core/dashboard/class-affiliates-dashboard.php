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
class Affiliates_Dashboard {

	/**
	 * @var int
	 */
	private $user_id = null;

	/**
	 * @var array
	 */
	private $sections = null;

	/**
	 * Initialization - adds the shortcode.
	 */
	public static function init() {
		add_shortcode( 'affiliates_dashboard', array( __CLASS__, 'affiliates_dashboard_shortcode' ) );
	}

	/**
	 * Shortcode handler for the [affiliates_dashboard] shortcode.
	 *
	 * @param array $atts shortcode attributes
	 * @param string $content not used
	 *
	 * @return string
	 */
	public static function affiliates_dashboard_shortcode( $atts, $content = '' ) {
		$dashboard = new Affiliates_Dashboard();
		ob_start();
		$dashboard->render();
		$output = ob_get_clean();
		return $output;
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
		global $affiliates_dashboard;

		$affiliates_dashboard = $this;
		$this->setup();
		Affiliates_Templates::include_template( 'dashboard/dashboard.php' );
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
				'affiliates-dashboard-login'        => new Affiliates_Dashboard_Login(),
				'affiliates-dashboard-registration' => new Affiliates_Dashboard_Registration()
			);
		} else {
			if ( !affiliates_user_is_affiliate() ) {
				$sections = array(
					'affiliates-dashboard-registration' => new Affiliates_Dashboard_Registration( array( 'user_id' => $this->user_id ) )
				);
			} else {
				$sections = array(
					'affiliates-dashboard-overview' => new Affiliates_Dashboard_Overview( array( 'user_id' => $this->user_id ) ),
					'affiliates-dashboard-earnings' => new Affiliates_Dashboard_Earnings( array( 'user_id' => $this->user_id ) )
				);
			}
		}

		$this->sections = apply_filters( 'affiliates_dashboard_setup_sections', $sections, $this );
	}
}
Affiliates_Dashboard::init();
