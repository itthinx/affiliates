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
	private $sections = null;

	/**
	 * Create a new factory instance with a set of section classes.
	 *
	 * @param string[Affiliates_Dashboard_Section] $sections
	 */
	public function __construct( $sections ) {
		$this->sections = $sections;
	}

	/**
	 * Set the class used to instantiate a section based on the section key.
	 *
	 * @param string $key section key
	 * @param string $class classname
	 */
	public function set_section_class( $key, $class ) {
		$this->sections[$key] = $class;
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
	public function get_section_instance( $key, $parameters ) {
		$section = null;
		if ( isset( $this->sections[$key] ) ) {
			$section = new $this->sections[$key]( $parameters );
		}
		return $section;
	}

}
