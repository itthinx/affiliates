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
interface I_Affiliates_Dashboard_Section_Table extends I_Affiliates_Dashboard_Section {

	/**
	 * Used for entry pagination. Returns the number of section entries per page.
	 *
	 * @return int
	 */
	public function get_per_page();

	/**
	 * Used for entry pagination. Returns the current page of section entries.
	 *
	 * @return int
	 */
	public function get_current_page();

	/**
	 * Used for entry pagination. Returns the total number of available pages of section entries.
	 * This is always derived based on the entries count and on how many entries are displayed per page.
	 * If there are no entries, this should return 0. If there are entries, it should return:
	 * ceiling( entries / entries per page).
	 *
	 * @return int
	 */
	public function get_pages();

	/**
	 * Returns the number of available entries for the section.
	 *
	 * @return int
	 */
	public function get_count();

	/**
	 * Used to filter by from date.
	 *
	 * @return string
	 */
	public function get_from_date();

	/**
	 * Used to filter by thru date.
	 *
	 * @return string
	 */
	public function get_thru_date();

	/**
	 * Returns the current sort order.
	 *
	 * @return string
	 */
	public function get_sort_order();

	/**
	 * Returns the inverted sort order.
	 *
	 * @return string
	 */
	public function get_switch_sort_order();

	/**
	 * Returns the key by which entries are sorted.
	 *
	 * @return string
	 */
	public function get_orderby();

	/**
	 * Returns column keys mapped to translated column heading labels and descriptions.
	 *
	 * @return string[string]
	 */
	public function get_columns();

	/**
	 * Provides the entries to display for the current page.
	 *
	 * @return object[]
	 */
	public function get_entries();
}
