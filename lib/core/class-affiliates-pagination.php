<?php
/**
 * class-affiliates-options.php
 *
 * Copyright (c) 2010, 2011 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pagination unscrupulously borrowed from WP_List_Table.
 */
class Affiliates_Pagination {

	/**
	 * @var array
	 */
	private $_pagination_args = array();

	/**
	 * @var string
	 */
	private $_pagination = '';

	/**
	 * Constructor
	 *
	 * @param int $total_items how many items there are to display
	 * @param int $total_pages how many pages there are, normally leave set to null
	 * @param int $per_page how many results to show on each page
	 * @param string $paged_var name used on pagination. 'paged' can not be used in frontend, it is filtered.
	 */
	function __construct($total_items, $total_pages, $per_page, $paged_var = 'paged' ) {
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'total_pages' => $total_pages,
				'per_page'    => $per_page,
				'paged_var'   => $paged_var
			)
		);
	}

	/**
	 * Get the current page number
	 *
	 * @return int the current page number
	 */
	function get_pagenum() {
		$pagenum = isset( $_REQUEST[$this->_pagination_args['paged_var']] ) ? absint( $_REQUEST[$this->_pagination_args['paged_var']] ) : 0;
		if ( !isset( $_REQUEST[$this->_pagination_args['paged_var']] ) ) { // needed with rewritten page added
			if ( preg_match( "/(\/page\/)(\d+)/", $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $matches ) ) {
				$pagenum = absint( $matches[2] );
			}
		}

		if( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] ) {
			$pagenum = $this->_pagination_args['total_pages'];
		}
		return max( 1, $pagenum );
	}

	/**
	 * An internal method that sets all the necessary pagination arguments
	 *
	 * @param array $args An associative array with information about the pagination
	 *
	 * @access protected
	 */
	function set_pagination_args( $args ) {
		$args = wp_parse_args( $args, array(
			'total_items' => 0,
			'total_pages' => 0,
			'per_page' => 0,
			'paged_var' => 'paged'
		) );

		if ( !$args['total_pages'] && $args['per_page'] > 0 )
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );

		$this->_pagination_args = $args;
	}

	/**
	 * Returns or displays the pagination.
	 *
	 * @param string $which  where it's displayed
	 * @param boolean $echo displays if true, otherwise returns
	 */
	function pagination( $which, $echo = false ) {

		if ( empty( $this->_pagination_args ) )
			return;

		extract( $this->_pagination_args );

		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		// needs to remove rewritten added page
		$current_url = preg_replace( "/\/page\/\d+/", "", $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 )
			$disable_first = ' disabled';
		if ( $current == $total_pages )
			$disable_last = ' disabled';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page button ' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( $this->_pagination_args['paged_var'], $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page button ' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( $this->_pagination_args['paged_var'], max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which )
			$html_current_page = $current;
		else
			$html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='%s' value='%s' size='%d' />",
				esc_attr__( 'Current page' ),
				esc_attr( $this->_pagination_args['paged_var'] ),
				$current,
				strlen( $total_pages )
			);

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging', 'affiliates' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page button ' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( $this->_pagination_args['paged_var'], min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page button ' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( $this->_pagination_args['paged_var'], $total_pages, $current_url ) ),
			'&raquo;'
		);

		$output .= "\n" . join( "\n", $page_links );

		$page_class = $total_pages < 2 ? ' one-page' : '';

		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		if ( $echo ) {
			echo $this->_pagination;
		} else {
			return $this->_pagination;
		}
	}
}
