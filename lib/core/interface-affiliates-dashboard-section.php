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
	 * Returns the user ID related to the section instance (for whom it is to be rendered).
	 *
	 * @return int or null
	 */
	public function get_user_id();

	/**
	 * Outputs the dashboard section.
	 */
	public function render();

}
