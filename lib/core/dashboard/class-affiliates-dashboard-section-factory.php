<?php
/**
 * class-affiliates-dashboard-section-factory.php
 *
 * Copyright (c) 2010 - 2019 "kento" Karim Rahimpur www.itthinx.com
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
 * Section factory.
 */
class Affiliates_Dashboard_Section_Factory {

	/**
	 * Relates section keys to classes.
	 *
	 * @var string[Affiliates_Dashboard_Section]
	 */
	private static $sections = array();

	/**
	 * Based on key-classname pairs, will set or replace key to class relations.
	 *
	 * @param string[Affiliates_Dashboard_Section] $sections
	 */
	public static function set_section_classes( $sections ) {
		foreach ( $sections as $key => $class ) {
			self::$sections[$key] = $class;
		}
	}

	/**
	 * Set the class used to instantiate a section based on the section key.
	 *
	 * @param string $key section key
	 * @param string $class classname
	 */
	public static function set_section_class( $key, $class ) {
		self::$sections[$key] = $class;
	}

	/**
	 * Create an instance of a section based on the section key, providing the
	 * $parameters to instantiate it.
	 *
	 * @param string $key section key
	 * @param array $parameters parameters used to instantiate the section object
	 *
	 * @return Affiliates_Dashboard_Section new section instance or null
	 */
	public static function get_section_instance( $key, $parameters = null ) {
		$section = null;
		if ( isset( self::$sections[$key] ) ) {
			if ( $parameters === null ) {
				$section = new self::$sections[$key]();
			} else {
				$section = new self::$sections[$key]( $parameters );
			}
		}
		return $section;
	}

}
