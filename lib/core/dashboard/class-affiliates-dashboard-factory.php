<?php
/**
 * class-affiliates-dashboard-factory.php
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
 * Dashboard factory.
 */
class Affiliates_Dashboard_Factory {

	private static $dashboard_class = null;

	/**
	 * Set the dashboard class used to provide dashboard instances.
	 * @param string $class
	 */
	public static function set_dashboard_class( $class ) {
		if ( class_exists( $class ) ) {
			if ( $implements = class_implements( $class ) ) {
				if ( is_array( $implements ) && in_array( 'I_Affiliates_Dashboard', $implements ) ) {
					self::$dashboard_class = $class;
				}
			}
		}
	}

	/**
	 * Provide a dashboard instance.
	 *
	 * @param array $params
	 *
	 * @return I_Affiliates_Dashboard
	 */
	public static function get_dashboard_instance( $params = array() ) {
		$instance = null;
		if ( self::$dashboard_class !== null ) {
			$instance = new self::$dashboard_class( $params );
		}
		return $instance;
	}
}
