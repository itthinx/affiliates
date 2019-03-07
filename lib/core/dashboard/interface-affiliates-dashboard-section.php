<?php
/**
 * interface-affiliates-dashboard-section.php
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
 * Common interface for dashboard sections.
 */
interface I_Affiliates_Dashboard_Section {

	/**
	 * @var integer the default value for the order property
	 */
	const DEFAULT_SECTION_ORDER = 100;

	/**
	 * Returns the key of the section.
	 *
	 * @return string section key
	 */
	public static function get_key();

	/**
	 * Returns the (maybe translated) name of the section.
	 *
	 * @return string section name
	 */
	public static function get_name();

	/**
	 * Returns the section order.
	 *
	 * @return int
	 */
	public static function get_section_order();

	/**
	 * Outputs the dashboard section.
	 */
	public function render();

	/**
	 * Returns the ID of the affiliate related to the user of the section instance.
	 *
	 * @return int or null
	 */
	public function get_affiliate_id();

	/**
	 * Returns an array of URL parameter keys that are specific for the section.
	 *
	 * @return string[]
	 */
	public function get_url_parameters();

	/**
	 * Returns the user ID related to the section instance (for whom it is to be rendered).
	 *
	 * @return int or null
	 */
	public function get_user_id();
}
