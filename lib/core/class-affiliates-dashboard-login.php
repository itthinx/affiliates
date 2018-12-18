<?php
/**
 * class-affiliates-dashboard-login.php
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
 * Dashboard section: Login
 */
class Affiliates_Dashboard_Login extends Affiliates_Dashboard_Section {

	/**
	 * Initialization - nothing done here at current.
	 */
	public static function init() {
	}

	/**
	 * Create a new dashboard section instance.
	 *
	 * @param array $params
	 */
	public function __construct( $params = array() ) {
		$this->template = 'dashboard/login.php';
		$this->require_user_id = false;
		parent::__construct( $params );
	}

}
Affiliates_Dashboard_Login::init();
