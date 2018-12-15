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
class Affiliates_Dashboard_Earnings {

	/**
	 * @var int
	 */
	private $user_id = null;

	/**
	 * Initialization - adds the shortcode.
	 */
	public static function init() {
		add_shortcode( 'affiliates_dashboard_earnings', array( __CLASS__, 'affiliates_dashboard_section_shortcode' ) );
	}

	/**
	 * Shortcode handler for the section shortcode.
	 *
	 * @param array $atts shortcode attributes
	 * @param string $content not used
	 *
	 * @return string
	 */
	public static function affiliates_dashboard_section_shortcode( $atts, $content = '' ) {
		$section = new Affiliates_Dashboard_Earnings();
		ob_start();
		$section->render();
		$output = ob_get_clean();
		return $output;
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
	 * Outputs the dashboard earnings.
	 */
	public function render() {
		global $affiliates_dashboard_section;

		$affiliates_dashboard_section = $this;
		if ( $this->user_id !== null ) {
			Affiliates_Templates::include_template( 'dashboard/earnings.php' );
		}
	}

	/**
	 * Returns the user ID related to this instance.
	 *
	 * @return int or null
	 */
	public function get_user_id() {
		return $this->user_id;
	}

}
Affiliates_Dashboard_Earnings::init();
