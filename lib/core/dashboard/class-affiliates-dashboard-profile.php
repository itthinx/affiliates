<?php
/**
 * class-affiliates-dashboard-profile.php
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
 * Dashboard section: Profile
 */
class Affiliates_Dashboard_Profile extends Affiliates_Dashboard_Section {

	/**
	 * Using a high order to have the profile usually shown last.
	 *
	 * @var integer
	 */
	protected static $section_order = 1000;

	/**
	 * Initialization - nothing done here at current.
	 */
	public static function init() {
	}

	/**
	 * {@inheritDoc}
	 * @see I_Affiliates_Dashboard_Section::get_section_order()
	 */
	public static function get_section_order() {
		return self::$section_order;
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
		$this->template = 'dashboard/profile.php';
		$this->require_user_id = true;
		parent::__construct( $params );
	}

	public static function get_name() {
		return __( 'Profile', 'affiliates' );
	}

	public static function get_key() {
		return 'profile';
	}


}
Affiliates_Dashboard_Profile::init();
