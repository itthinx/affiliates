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
	 * Second place after login.
	 *
	 * @var integer
	 */
	protected static $section_order = 200;

	/**
	 * Whether to show the login link after registration.
	 *
	 * @var boolean
	 */
	private $show_login = true;

	/**
	 * Can be used to provide an alternative login URL. This is shown after registration if $show_login is true.
	 *
	 * @var string login URL
	 */
	private $login_url = null;

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
		$this->template = 'dashboard/registration.php';
		$this->require_user_id = false;
		parent::__construct( $params );
		if ( isset( $params['show_login'] ) ) {
			$show_login = $params['show_login'];
			switch ( $show_login ) {
				case true :
				case 'true' :
				case 'yes' :
					$show_login = true;
					break;
				default :
					$show_login = false;
			}
			$this->show_login = $show_login;
		}
	}

	public static function get_name() {
		return __( 'Registration', 'affiliates' );
	}

	public static function get_key() {
		return 'registration';
	}

	public function get_show_login() {
		return $this->show_login;
	}

	public function get_login_url() {
		return $this->login_url;
	}
}
Affiliates_Dashboard_Registration::init();
