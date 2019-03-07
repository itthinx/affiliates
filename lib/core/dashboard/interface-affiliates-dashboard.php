<?php
/**
 * interface-affiliates-dashboard.php
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
 * Common interface for dashboards.
 */
interface I_Affiliates_Dashboard {

	/**
	 * Outputs the dashboard for a connected user or the login form if not logged in.
	 */
	public function render();

	/**
	 * Returns the dashboard's sections.
	 *
	 * @return array or null
	 */
	public function get_sections();

	/**
	 * Returns the section instance for the given key.
	 *
	 * @param string $key
	 *
	 * @return Affiliates_Dashboard_Section or null
	 */
	public function get_section( $key );

	/**
	 * Returns the current section.
	 *
	 * @return string current section or null
	 */
	public function get_current_section();

	/**
	 * Returns the user ID related to this instance.
	 *
	 * @return int or null
	 */
	public function get_user_id();
}
