<?php
/**
 * affiliates-admin-hits-uri.php
 * 
 * Copyright (c) 2010-2017 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 2.17.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// Shows traffic section

include_once( AFFILIATES_CORE_LIB . '/class-affiliates-date-helper.php');

/**
 * Render the traffic table & filter.
 */
function affiliates_admin_hits_uri() {
	global $wpdb, $affiliates_options;

	$output = '';

	if ( is_admin() && !current_user_can( AFFILIATES_ACCESS_AFFILIATES ) ) {
		wp_die( __( 'Access denied.', 'affiliates' ) );
	}

	if (
		isset( $_POST['from_date'] ) ||
		isset( $_POST['thru_date'] ) ||
		isset( $_POST['clear_filters'] ) ||
		isset( $_POST['affiliate_id'] ) ||
		isset( $_POST['src_uri'] ) ||
		isset( $_POST['dest_uri'] )
	) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_FILTER_NONCE], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	// filters
	$from_date          = $affiliates_options->get_option( 'hits_uri_from_date', null );
	$thru_date          = $affiliates_options->get_option( 'hits_uri_thru_date', null );
	$affiliate_id       = $affiliates_options->get_option( 'hits_uri_affiliate_id', null );
	$src_uri            = $affiliates_options->get_option( 'hits_uri_src_uri', null );
	$dest_uri           = $affiliates_options->get_option( 'hits_uri_dest_uri', null );

	if ( isset( $_POST['clear_filters'] ) ) {
		$affiliates_options->delete_option( 'hits_uri_from_date' );
		$affiliates_options->delete_option( 'hits_uri_thru_date' );
		$affiliates_options->delete_option( 'hits_uri_affiliate_id' );
		$affiliates_options->delete_option( 'hits_uri_src_uri' );
		$affiliates_options->delete_option( 'hits_uri_dest_uri' );
		$from_date = null;
		$thru_date = null;
		$affiliate_id = null;
		$src_uri = null;
		$dest_uri = null;
	} else if ( isset( $_POST['submitted'] ) ) {
		// filter by date(s)
		if ( !empty( $_POST['from_date'] ) ) {
			$from_date = date( 'Y-m-d', strtotime( $_POST['from_date'] ) );
			$affiliates_options->update_option( 'hits_uri_from_date', $from_date );
		} else {
			$from_date = null;
			$affiliates_options->delete_option( 'hits_uri_from_date' );
		}
		if ( !empty( $_POST['thru_date'] ) ) {
			$thru_date = date( 'Y-m-d', strtotime( $_POST['thru_date'] ) );
			$affiliates_options->update_option( 'hits_uri_thru_date', $thru_date );
		} else {
			$thru_date = null;
			$affiliates_options->delete_option( 'hits_uri_thru_date' );
		}
		if ( $from_date && $thru_date ) {
			if ( strtotime( $from_date ) > strtotime( $thru_date ) ) {
				$thru_date = null;
				$affiliates_options->delete_option( 'hits_uri_thru_date' );
			}
		}

		// filter by affiliate id
		if ( !empty( $_POST['affiliate_id'] ) ) {
			$affiliate_id = affiliates_check_affiliate_id( $_POST['affiliate_id'] );
			if ( $affiliate_id ) {
				$affiliates_options->update_option( 'hits_uri_affiliate_id', $affiliate_id );
			}
		} else if ( isset( $_POST['affiliate_id'] ) ) { // empty && isset => '' => all
			$affiliate_id = null;
			$affiliates_options->delete_option( 'hits_uri_affiliate_id' );
		}

		// src_uri
		$_POST['src_uri'] = trim( $_POST['src_uri'] );
		if ( !empty( $_POST['src_uri'] ) ) {
			$src_uri = $_POST['src_uri'];
			$affiliates_options->update_option( 'hits_uri_src_uri', $src_uri );
		} else if ( isset( $_POST['src_uri'] ) ) { // empty
			$src_uri = null;
			$affiliates_options->delete_option( 'hits_uri_src_uri' );
		}
		// dest_uri
		$_POST['dest_uri'] = trim( $_POST['dest_uri'] );
		if ( !empty( $_POST['dest_uri'] ) ) {
			$dest_uri = $_POST['dest_uri'];
			$affiliates_options->update_option( 'hits_uri_dest_uri', $dest_uri );
		} else if ( isset( $_POST['dest_uri'] ) )  { // empty
			$dest_uri = null;
			$affiliates_options->delete_option( 'hits_uri_dest_uri' );
		}
	}

	if ( isset( $_POST['row_count'] ) ) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_NONCE_1], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	if ( isset( $_POST['uris_paged'] ) ) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_NONCE_2], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'uris_paged', $current_url );

	$affiliates_table  = _affiliates_get_tablename( 'affiliates' );
	$referrals_table   = _affiliates_get_tablename( 'referrals' );
	$hits_table        = _affiliates_get_tablename( 'hits' );
	$uris_table        = _affiliates_get_tablename( 'uris' );

	$output .= '<h1>';
	$output .= __( 'Traffic', 'affiliates' );
	$output .= '</h1>';

	$row_count = isset( $_POST['row_count'] ) ? intval( $_POST['row_count'] ) : 0;

	if ($row_count <= 0) {
		$row_count = $affiliates_options->get_option( 'affiliates_hits_uri_per_page', AFFILIATES_HITS_PER_PAGE );
	} else {
		$affiliates_options->update_option('affiliates_hits_uri_per_page', $row_count );
	}
	$offset = isset( $_GET['offset'] ) ? intval( $_GET['offset'] ) : 0;
	if ( $offset < 0 ) {
		$offset = 0;
	}
	$paged = isset( $_REQUEST['uris_paged'] ) ? intval( $_REQUEST['uris_paged'] ) : 0;
	if ( $paged < 0 ) {
		$paged = 0;
	}

	$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : null;
	switch ( $orderby ) {
		case 'date' :
		case 'visits' :
		case 'hits' :
		case 'referrals' :
		case 'src_uri' :
		case 'dest_uri' :
			break;
		case 'affiliate_id' :
			$orderby = 'name';
		default:
			$orderby = 'date';
	}

	$order = isset( $_GET['order'] ) ? $_GET['order'] : null;
	switch ( $order ) {
		case 'asc' :
		case 'ASC' :
			$switch_order = 'DESC';
			break;
		case 'desc' :
		case 'DESC' :
			$switch_order = 'ASC';
			break;
		default:
			$order = 'DESC';
			$switch_order = 'ASC';
	}

	$filters = " WHERE 1=%d ";
	$filter_params = array( 1 );
	// We now have the desired dates from the user's point of view, i.e. in her timezone.
	// If supported, adjust the dates for the site's timezone:
	if ( $from_date ) {
		$from_datetime = DateHelper::u2s( $from_date );
	}
	if ( $thru_date ) {
		$thru_datetime = DateHelper::u2s( $thru_date, 24*3600 );
	}
	if ( $from_date && $thru_date ) {
		$filters .= " AND h.datetime >= %s AND h.datetime <= %s ";
		$filter_params[] = $from_datetime;
		$filter_params[] = $thru_datetime;
	} else if ( $from_date ) {
		$filters .= " AND h.datetime >= %s ";
		$filter_params[] = $from_datetime;
	} else if ( $thru_date ) {
		$filters .= " AND h.datetime < %s ";
		$filter_params[] = $thru_datetime;
	}
	if ( $affiliate_id ) {
		$filters .= " AND h.affiliate_id = %d ";
		$filter_params[] = $affiliate_id;
	}

	if ( $src_uri ) {
		$filters .= " AND su.uri LIKE '%%%s%%' ";
		$filter_params[] = $wpdb->esc_like( $src_uri );
	}
	if ( $dest_uri ) {
		$filters .= " AND du.uri LIKE '%%%s%%' ";
		$filter_params[] = $wpdb->esc_like( $dest_uri );
	}

	// how many are there ?
	$count_query = $wpdb->prepare(
		"SELECT date FROM $hits_table h
		LEFT JOIN $uris_table su ON h.src_uri_id = su.uri_id
		LEFT JOIN $uris_table du ON h.dest_uri_id = du.uri_id
		$filters
		GROUP BY date, su.uri, du.uri",
		$filter_params
	);

	$wpdb->query( $count_query );
	$count = $wpdb->num_rows;

	if ( $count > $row_count ) {
		$paginate = true;
	} else {
		$paginate = false;
	}
	$pages = ceil ( $count / $row_count );
	if ( $paged > $pages ) {
		$paged = $pages;
	}
	if ( $paged != 0 ) {
		$offset = ( $paged - 1 ) * $row_count;
	}

	// @todo remove old query
// 	$query = $wpdb->prepare(
// 		"SELECT
// 		*,
// 		su.uri src_uri,
// 		du.uri dest_uri,
// 		COUNT(distinct ip) visits,
// 		SUM(count) hits
// 		FROM $hits_table h
// 		LEFT JOIN $affiliates_table a ON h.affiliate_id = a.affiliate_id
// 		LEFT JOIN $uris_table su ON h.src_uri_id = su.uri_id
// 		LEFT JOIN $uris_table du ON h.dest_uri_id = du.uri_id
// 		$filters
// 		GROUP BY date, su.uri, du.uri
// 		ORDER BY $orderby $order
// 		LIMIT $row_count OFFSET $offset",
// 		$filter_params
// 	);

	$query = $wpdb->prepare(
		"SELECT
		*,
		su.uri src_uri,
		du.uri dest_uri,
		COUNT(distinct ip) visits,
		SUM(count) hits,
		COUNT(r.referral_id) referrals
		FROM (SELECT h1.affiliate_id, h1.count, h1.date, h1.datetime, h1.src_uri_id, h1.dest_uri_id, (SELECT MIN(datetime) FROM $hits_table h2 WHERE h2.affiliate_id = h1.affiliate_id AND h2.datetime > h1.datetime) next_datetime FROM $hits_table h1) AS h
		LEFT JOIN $affiliates_table a ON h.affiliate_id = a.affiliate_id
		LEFT JOIN $uris_table su ON h.src_uri_id = su.uri_id
		LEFT JOIN $uris_table du ON h.dest_uri_id = du.uri_id
		LEFT JOIN $referrals_table r ON r.affiliate_id = h.affiliate_id AND r.datetime >= h.datetime AND (h.next_datetime IS NULL OR r.datetime < h.next_datetime)
		$filters
		GROUP BY date, su.uri, du.uri
		ORDER BY $orderby $order
		LIMIT $row_count OFFSET $offset",
		$filter_params
	);

	$results = $wpdb->get_results( $query, OBJECT );

	$column_display_names = array(
		'date'      => __( 'Date', 'affiliates' ) . '*',
		'name'      => __( 'Affiliate', 'affiliates' ),
		'visits'    => __( 'Visits', 'affiliates' ),
		'hits'      => __( 'Hits', 'affiliates' ),
		'referrals' => __( 'Referrals', 'affiliates' ),
		'src_uri'   => __( 'Source URI', 'affiliates' ),
		'dest_uri'  => __( 'Landing URI', 'affiliates' )
	);

	$output .= '<div class="hits-uris-overview">';

	$affiliates_select = '';
	$affiliates = affiliates_get_affiliates( true );
	if ( !empty( $affiliates ) ) {
		$affiliates_select .= '<label class="affiliate-id-filter">';
		$affiliates_select .= __( 'Affiliate', 'affiliates' );
		$affiliates_select .= ' ';
		$affiliates_select .= '<select class="affiliate-id-filter" name="affiliate_id">';
		$affiliates_select .= '<option value="">--</option>';
		foreach ( $affiliates as $affiliate ) {
			if ( $affiliate_id == $affiliate['affiliate_id']) {
				$selected = ' selected="selected" ';
			} else {
				$selected = '';
			}
			$affiliates_select .= '<option ' . $selected . ' value="' . esc_attr( $affiliate['affiliate_id'] ) . '">' . esc_attr( stripslashes( $affiliate['name'] ) ) . '</option>';
		}
		$affiliates_select .= '</select>';
		$affiliates_select .= '</label>';
	}

	$output .=
		'<div class="filters">' .
			'<label class="description" for="setfilters">' . __( 'Filters', 'affiliates' ) . '</label>' .
			'<form id="setfilters" action="" method="post">' .

				'<div class="filter-section">' .
					$affiliates_select .
				'</div>' .
				'<div class="filter-section">' .
					'<label class="from-date-filter">' .
					__( 'From', 'affiliates' ) .
					' ' .
					'<input class="datefield from-date-filter" name="from_date" type="text" value="' . esc_attr( $from_date ) . '"/>'.
					'</label>' .
					' ' .
					'<label class="thru-date-filter">' .
					__( 'Until', 'affiliates' ) .
					' ' .
					'<input class="datefield thru-date-filter" name="thru_date" type="text" class="datefield" value="' . esc_attr( $thru_date ) . '"/>'.
					'</label>' .
				'</div>' .
				'<div class="filter-section">' .
					'<label class="src-uri-filter">' .
					__( 'Source URI', 'affiliates' ) .
					' ' .
					'<input class="src-uri-filter" name="src_uri" type="text" value="' . esc_attr( stripslashes( $src_uri ) ) . '"/>'.
					'</label>' .
					' ' .
					'<label class="dest-uri-filter">' .
					__( 'Landing URI', 'affiliates' ) .
					' ' .
					'<input class="dest-uri-filter" name="dest_uri" type="text" value="' . esc_attr( stripslashes( $dest_uri ) ) . '"/>'.
					'</label>' .
				'</div>' .
				'<div class="filter-buttons">' .
				wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_FILTER_NONCE, true, false ) .
				'<input class="button" type="submit" value="' . __( 'Apply', 'affiliates' ) . '"/>' .
				'<input class="button" type="submit" name="clear_filters" value="' . __( 'Clear', 'affiliates' ) . '"/>' .
				'<input type="hidden" value="submitted" name="submitted"/>' .
				'</div>' .
			'</form>' .
		'</div>' .
		'<div class="page-options">' .
			'<form id="setrowcount" action="" method="post">' .
				'<div>' .
					'<label for="row_count">' . __('Results per page', 'affiliates' ) . '</label>' .
					'<input name="row_count" type="text" size="2" value="' . esc_attr( $row_count ) .'" />' .
					wp_nonce_field( "admin", AFFILIATES_ADMIN_HITS_NONCE_1, true, false ) .
					'<input class="button" type="submit" value="' . __( 'Apply', 'affiliates' ) . '"/>' .
				'</div>' .
			'</form>' .
		'</div>
	';

	if ( $paginate ) {
		require_once( AFFILIATES_CORE_LIB . '/class-affiliates-pagination.php' );
		$pagination = new Affiliates_Pagination( $count, null, $row_count, 'uris_paged' );
		$output .= '<form id="posts-filter" method="post" action="">';
		$output .= '<div>';
		$output .= wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_NONCE_2, true, false );
		$output .= '</div>';
		$output .= '<div class="tablenav top">';
		$output .= $pagination->pagination( 'top' );
		$output .= '</div>';
		$output .= '</form>';
	}

	$output .= '<table id="" class="wp-list-table widefat fixed" cellspacing="0">';
	$output .= '<thead>';
	$output .= '<tr>';

	foreach ( $column_display_names as $key => $column_display_name ) {
		$options = array(
			'orderby' => $key,
			'order' => $switch_order
		);
		$class = "";
		if ( strcmp( $key, $orderby ) == 0 ) {
			$lorder = strtolower( $order );
			$class = "$key manage-column sorted $lorder";
		} else {
			$class = "$key manage-column";
			if ( $key !== 'referrals') {
				$class .= ' sortable';
			}
		}
		if ( $key !== 'referrals' ) {
			$column_display_name = '<a href="' . esc_url( add_query_arg( $options, $current_url ) ) . '"><span>' . esc_html( $column_display_name ) . '</span><span class="sorting-indicator"></span></a>';
		} else {
			$column_display_name = esc_html( $column_display_name );
		}
		$output .= "<th scope='col' class='$class'>$column_display_name</th>";
	}

	$output .= '</tr>';
	$output .= '</thead>';
	$output .= '<tbody>';

	if ( count( $results ) > 0 ) {
		for ( $i = 0; $i < count( $results ); $i++ ) {

			$result = $results[$i];

			$referrals = affiliates_get_referrals_by_hits( $result->date, $result->src_uri_id, $result->dest_uri_id );

			$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
			$output .= "<td class='date'>$result->date</td>";
			$affiliate = affiliates_get_affiliate( $result->affiliate_id );
			$output .= "<td class='affiliate-name'>" . stripslashes( wp_filter_nohtml_kses( $affiliate['name'] ) ) . "</td>";
			$output .= "<td class='visits'>$result->visits</td>";
			$output .= "<td class='hits'>$result->hits</td>";
			$output .= "<td class='referrals'>$result->referrals || $referrals</td>";
			$output .= sprintf( "<td class='src-uri'>%s</td>", esc_html( $result->src_uri ) ); // stored with esc_url_raw(), shown with esc_html()
			$output .= sprintf( "<td class='dest-uri'>%s</td>", esc_html( $result->dest_uri ) ); // stored with esc_url_raw(), shown with esc_html()
			$output .= '</tr>';
		}
	} else {
		$output .= '<tr><td colspan="5">' . __('There are no results.', 'affiliates' ) . '</td></tr>';
	}

	$output .= '</tbody>';
	$output .= '</table>';

	if ( $paginate ) {
		require_once( AFFILIATES_CORE_LIB . '/class-affiliates-pagination.php' );
		$pagination = new Affiliates_Pagination( $count, null, $row_count, 'uris_paged' );
		$output .= '<div class="tablenav bottom">';
		$output .= $pagination->pagination( 'bottom' );
		$output .= '</div>';
	}

	$server_dtz = DateHelper::getServerDateTimeZone();
	$output .=
		'<p>' .
			sprintf(
				__( "* Date is given for the server's time zone : %s, which has an offset of %s hours with respect to GMT.", 'affiliates' ),
				$server_dtz->getName(),
				$server_dtz->getOffset( new DateTime() ) / 3600.0
			) .
			'</p>';
	$output .= '</div>'; // .visits-overview

	echo $output;
	affiliates_footer();
} // function affiliates_admin_hits_uri()
