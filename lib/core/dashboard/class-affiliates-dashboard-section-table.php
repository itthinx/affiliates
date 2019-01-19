<?php
/**
 * class-affiliates-dashboard-section-table.php
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

/**
 * Implements common methods to all dashboard sections.
 */
abstract class Affiliates_Dashboard_Section_Table extends Affiliates_Dashboard_Section implements I_Affiliates_Dashboard_Section_Table {

	/**
	 * @var int default number of entries shown per page
	 */
	const PER_PAGE_DEFAULT = 20;

	/**
	 * @var int maximum number of entries shown per page
	 */
	const MAX_PER_PAGE = 1000;

	/**
	 * Key-value pairs with default values used for filter and view attributes.
	 *
	 * @var string[int|array|object|null]
	 */
	protected static $defaults = array();

	/**
	 * @var int how many entries there are in total (including and beyond those shown on the current page)
	 */
	protected $count = 0;
	
	/**
	 * @var int number of entries to show per page
	 */
	protected $per_page = self::PER_PAGE_DEFAULT;

	/**
	 * @var int the current page index: 0, 1, 2, ...
	 */
	protected $current_page = 0;

	/**
	 * @var string used as date filter if not null
	 */
	protected $from_date = null;

	/**
	 * @var string used as date filter if not null
	 */
	protected $thru_date = null;

	/**
	 * @var string indicates sort order 'ASC' or 'DESC'
	 */
	protected $sort_order = 'DESC';

	/**
	 * @var string indicates inverted sort order 'ASC' or 'DESC'
	 */
	protected $switch_sort_order = 'ASC';

	/**
	 * @var string sort entries by ...
	 */
	protected $orderby = 'date';

	/**
	 * @var array maps column keys to translated column heading titles and descriptions
	 */
	protected $columns = array();

	/**
	 * @var array holds entries to show for the current results page
	 */
	protected $entries = null;

	/**
	 * @return int
	 */
	public function get_per_page() {
		return $this->per_page;
	}

	/**
	 * @return int
	 */
	public function get_current_page() {
		return $this->current_page;
	}

	/**
	 * @return int
	 */
	public function get_pages() {
		$n = 0;
		if ( $this->count > 0 && $this->per_page > 0 ) {
			$n = ceil( $this->count / $this->per_page );
		}
		return $n;
	}

	/**
	 * Provides the total number of entries available.
	 *
	 * @return int
	 */
	public function get_count() {
		return $this->count;
	}

	/**
	 * Filter by from date.
	 *
	 * @return string
	 */
	public function get_from_date() {
		return $this->from_date;
	}

	/**
	 * Filter by thru date.
	 *
	 * @return string
	 */
	public function get_thru_date() {
		return $this->thru_date;
	}

	/**
	 * @return int
	 */
	public function get_sort_order() {
		return $this->sort_order;
	}

	/**
	 * @return string
	 */
	public function get_switch_sort_order() {
		return $this->switch_sort_order;
	}

	/**
	 * @return string
	 */
	public function get_orderby() {
		return $this->orderby;
	}

	/**
	 * @return array column keys mapped to translated column heading labels and descriptions
	 */
	public function get_columns() {
		return $this->columns;
	}

	/**
	 * Provides the entries to display for the current page.
	 *
	 * @return array of entries
	 */
	public function get_entries() {
		return $this->entries;
	}
}
