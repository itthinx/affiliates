<?php
/**
 * class-affiliates-dashboard-registration.php
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
 * Dashboard section: Registration
 */
class Affiliates_Dashboard_Registration extends Affiliates_Dashboard_Section {

	/**
	 * Initialization - adds the shortcode.
	 */
	public static function init() {
		add_shortcode( 'affiliates_dashboard_registration', array( __CLASS__, 'affiliates_dashboard_section_shortcode' ) );
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
		$section = new Affiliates_Dashboard_Registration();
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
		$this->template = 'dashboard/registration.php';
		$this->require_user_id = false;
		parent::__construct( $params );
	}

}
Affiliates_Dashboard_Registration::init();
