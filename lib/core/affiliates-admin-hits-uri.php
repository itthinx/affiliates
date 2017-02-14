<?php
/**
 * affiliates-admin-hits-uri.php
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

// Shows traffics section

include_once( AFFILIATES_CORE_LIB . '/class-affiliates-date-helper.php');

function affiliates_admin_hits_uri() {
	render_affiliates_admin_hits_uri();
}

/**
 * Render the traffics table & filter.
 * Used from dashboard section and shortcode.
 * @param array $columns Columns to display.
 * @param boolean $display Echo the result or return as string.
 * @return string if $display is false.
 */
function render_affiliates_admin_hits_uri( $columns = null, $display = true ) {
	global $wpdb, $affiliates_options;

	$output = '';

	if ( !current_user_can( AFFILIATES_ACCESS_AFFILIATES ) ) {
		wp_die( __( 'Access denied.', 'affiliates' ) );
	}

	if (
		isset( $_POST['from_date'] ) ||
		isset( $_POST['thru_date'] ) ||
		isset( $_POST['clear_filters'] ) ||
		isset( $_POST['affiliate_id'] ) ||
		isset( $_POST['expanded_hits'] ) ||
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
	$expanded_hits      = $affiliates_options->get_option( 'hits_expanded_hits', null );

	if ( isset( $_POST['clear_filters'] ) ) {
		$affiliates_options->delete_option( 'hits_uri_from_date' );
		$affiliates_options->delete_option( 'hits_uri_thru_date' );
		$affiliates_options->delete_option( 'hits_uri_affiliate_id' );
		$affiliates_options->delete_option( 'hits_uri_src_uri' );
		$affiliates_options->delete_option( 'hits_uri_dest_uri' );
		$affiliates_options->delete_option( 'hits_uri_group_src_uri' );
		$affiliates_options->delete_option( 'hits_uri_group_dest_uri' );
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
		if ( !empty( $_POST['src_uri'] ) ) {
			$src_uri = trim( $_POST['src_uri'] );
			$affiliates_options->update_option( 'hits_uri_src_uri', $src_uri );
		} else if ( isset( $_POST['src_uri'] ) ) {  // empty
			$src_uri = null;
			$affiliates_options->delete_option( 'hits_uri_src_uri' );
		}
		// dest_uri
		if ( !empty( $_POST['dest_uri'] ) ) {
			$dest_uri = trim( $_POST['dest_uri'] );
			$affiliates_options->update_option( 'hits_uri_dest_uri', '' );
		} else if ( isset( $_POST['dest_uri'] ) ) {  // empty
			$dest_uri = null;
			$affiliates_options->delete_option( 'hits_uri_src_uri' );
		}
	}

	if ( isset( $_POST['row_count'] ) ) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_NONCE_1], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	if ( isset( $_POST['paged'] ) ) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_NONCE_2], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'paged', $current_url );

	$affiliates_table = _affiliates_get_tablename( 'affiliates' );
	$referrals_table = _affiliates_get_tablename( 'referrals' );
	$hits_table = _affiliates_get_tablename( 'hits' );
	$uris_table = _affiliates_get_tablename( 'uris' );

	$output .=
		'<div>' .
			'<h1>' .
				__( "Traffics", 'affiliates' ) .
			'</h1>' .
		'</div>';

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
	$paged = isset( $_REQUEST['paged'] ) ? intval( $_REQUEST['paged'] ) : 0;
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
		$filter_params[] = $src_uri;
	}
	if ( $dest_uri ) {
		$filters .= " AND du.uri LIKE '%%%s%%' ";
		$filter_params[] = $dest_uri;
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

	$query = $wpdb->prepare("
			SELECT
			*,
			su.uri src_uri,
			du.uri dest_uri,
			count(distinct ip) visits,
			sum(count) hits
			FROM $hits_table h
			LEFT JOIN $affiliates_table a ON h.affiliate_id = a.affiliate_id
			LEFT JOIN $uris_table su ON h.src_uri_id = su.uri_id
			LEFT JOIN $uris_table du ON h.dest_uri_id = du.uri_id
			$filters
			GROUP BY date, su.uri, du.uri
			ORDER BY $orderby $order
			LIMIT $row_count OFFSET $offset
			",
			$filter_params
			);

	$results = $wpdb->get_results( $query, OBJECT );

	if ( isset( $columns ) && ( sizeof( $columns ) > 0 ) ) {
		$column_display_names = $columns;
	} else {
		$column_display_names = array(
			'date'      => __( 'Date', 'affiliates' ) . '*',
			'name'      => __( 'Affiliate', 'affiliates' ),
			'visits'    => __( 'Visits', 'affiliates' ),
			'hits'      => __( 'Hits', 'affiliates' ),
			'referrals' => __( 'Referrals', 'affiliates' ),
			'src_uri'   => __( 'Source URI', 'affiliates' ),
			'dest_uri'  => __( 'Landing URI', 'affiliates' )
		);
	}

	$output .= '<div id="" class="hits-overview">';

	$affiliates_select = '';
	if ( isset( $column_display_names['name'] ) ) {
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
				'<input class="src-uri-filter" name="src_uri" type="text" value="' . esc_attr( $src_uri ) . '"/>'.
				'</label>' .
				' ' .
				'<label class="dest-uri-filter">' .
				__( 'Landing URI', 'affiliates' ) .
				' ' .
				'<input class="dest-uri-filter" name="dest_uri" type="text" value="' . esc_attr( $dest_uri ) . '"/>'.
				'</label>' .
				'</div>' .

				'<div class="filter-buttons">' .
				wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_FILTER_NONCE, true, false ) .
				'<input class="button" type="submit" value="' . __( 'Apply', 'affiliates' ) . '"/>' .
				'<input class="button" type="submit" name="clear_filters" value="' . __( 'Clear', 'affiliates' ) . '"/>' .
				'<input type="hidden" value="submitted" name="submitted"/>' .
				'</div>' .
			'</form>' .
		'</div>';

	$output .= '
		<div class="page-options">
			<form id="setrowcount" action="" method="post">
				<div>
					<label for="row_count">' . __('Results per page', 'affiliates' ) . '</label>' .
					//<input name="page" type="hidden" value="' . esc_attr( $page ) . '"/>
					'<input name="row_count" type="text" size="2" value="' . esc_attr( $row_count ) .'" />
					' . wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_NONCE_1, true, false ) . '
					<input class="button" type="submit" value="' . __( 'Apply', 'affiliates' ) . '"/>
				</div>
			</form>
		</div>
		';

	if ( $paginate ) {
	  require_once( AFFILIATES_CORE_LIB . '/class-affiliates-pagination.php' );
		$pagination = new Affiliates_Pagination($count, null, $row_count);
		$output .= '<form id="posts-filter" method="post" action="">';
		$output .= '<div>';
		$output .= wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_NONCE_2, true, false );
		$output .= '</div>';
		$output .= '<div class="tablenav top">';
		$output .= $pagination->pagination( 'top' );
		$output .= '</div>';
		$output .= '</form>';
	}

	$output .= '
		<table id="" class="wp-list-table widefat fixed" cellspacing="0">
		<thead>
			<tr>
			';

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
			$class = "$key manage-column sortable";
		}
		$column_display_name = '<a href="' . esc_url( add_query_arg( $options, $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
		$output .= "<th scope='col' class='$class'>$column_display_name</th>";
	}

	$output .= '</tr>
		</thead>
		<tbody>
		';

	if ( count( $results ) > 0 ) {
		for ( $i = 0; $i < count( $results ); $i++ ) {

			$result = $results[$i];

			$referrals = affiliates_get_referrals_by_hits( $result->date, $result->src_uri_id, $result->dest_uri_id );

			$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
			$output .= isset( $column_display_names['date'] ) ? "<td class='date'>$result->date</td>" : '';
			$affiliate = affiliates_get_affiliate( $result->affiliate_id );
			$output .= isset( $column_display_names['name'] ) ? "<td class='affiliate-name'>" . stripslashes( wp_filter_nohtml_kses( $affiliate['name'] ) ) . "</td>" : '';
			$output .= isset( $column_display_names['visits'] ) ? "<td class='visits'>$result->visits</td>" : '';
			$output .= isset( $column_display_names['hits'] ) ? "<td class='hits'>$result->hits</td>" : '';
			$output .= isset( $column_display_names['referrals'] ) ? "<td class='referrals'>$referrals</td>" : '';
			$output .= isset( $column_display_names['src_uri'] ) ? "<td class='src-uri'>$result->src_uri</td>" : '';
			$output .= isset( $column_display_names['dest_uri'] ) ? "<td class='dest-uri'>$result->dest_uri</td>" : '';
			$output .= '</tr>';

			/*
			if ( $expanded || $expanded_referrals || $expanded_hits ) {

				//
				// expanded : referrals ----------------------------------------
				//
				if ( $expanded_referrals ) {
					$referrals_filters = " WHERE date(datetime) = %s ";
					$referrals_filter_params = array( $result->date );
					if ( $affiliate_id ) {
						$referrals_filters .= " AND r.affiliate_id = %d ";
						$referrals_filter_params[] = $affiliate_id;
					}
					$referrals_orderby = "datetime $order";

					$referrals_query = $wpdb->prepare(
						"SELECT *
						FROM $referrals_table r
						LEFT JOIN $affiliates_table a ON r.affiliate_id = a.affiliate_id
						$referrals_filters
						ORDER BY $referrals_orderby
						",
						$referrals_filter_params
					);
					$referrals = $wpdb->get_results( $referrals_query, OBJECT );
					if ( count($referrals) > 0 ) {
						$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
						$output .= '<td colspan="5">';
						$output .= '<div class="details-referrals">';
						$output .= '<p class="description">' . __( 'Referrals', 'affiliates' ) . '</p>';
						$output .= '
							<table id="details-referrals-' . esc_attr( $result->date ) . '" class="details-referrals" cellspacing="0">
							<thead>
							<tr>
							<th scope="col" class="datetime">' . __( 'Time', 'affiliates' ) . '</th>
							<th scope="col" class="post-id">' . __( 'Post', 'affiliates' ) . '</th>
							<th scope="col" class="affiliate-id">' . __( 'Affiliate', 'affiliates' ) . '</th>
							</tr>
							</thead>
							<tbody>
							';
						foreach ( $referrals as $referral ) {
							$output .= '<tr class="details-referrals ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
//							$output .= "<td class='datetime'>$referral->datetime</td>";
							$output .= '<td class="datetime">' . DateHelper::s2u( $referral->datetime ) . '</td>';
							$link = get_permalink( $referral->post_id );
							$title = get_the_title( $referral->post_id );
							$output .= '<td class="post-id"><a href="' . esc_attr( $link ) . '" target="_blank">' . wp_filter_nohtml_kses( $title ) . '</a></td>';
							$output .= "<td class='affiliate-id'>" . stripslashes( wp_filter_nohtml_kses( $referral->name ) ) . "</td>";
							$output .= '</tr>';
						}
						$output .= '</tbody></table>';
						$output .= '</div>'; // .details-referrals
						$output .= '</td></tr>';
					}
				} // if $expanded_referrals

				//
				// expanded : hits ----------------------------------------
				//

				if ( $expanded_hits ) {
					// get the detailed results for hits
					$details_orderby = "date $order, time $order";

					$details_filters = " WHERE h.date = %s ";
					$details_filter_params = array( $result->date );
					if ( $affiliate_id ) {
						$details_filters .= " AND h.affiliate_id = %d ";
						$details_filter_params[] = $affiliate_id;
					}

					$details_query = $wpdb->prepare(
						"SELECT *
						FROM $hits_table h
						LEFT JOIN $affiliates_table a ON h.affiliate_id = a.affiliate_id
						$details_filters
						ORDER BY $details_orderby
						",
						$details_filter_params
					);
					$hits = $wpdb->get_results( $details_query, OBJECT );
					$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
					$output .= '<td colspan="5">';
					$output .= '<div class="details-hits">';
					$output .= '<p class="description">' . __( 'Hits', 'affiliates' ) . '</p>';
					$output .= '
						<table id="details-hits-' . esc_attr( $result->date ) . '" class="details-hits" cellspacing="0">
						<thead>
						<tr>
						<th scope="col" class="time">' . __( 'Time', 'affiliates' ) . '</th>
						<th scope="col" class="ip">' . __( 'IP', 'affiliates' ) . '</th>
						<th scope="col" class="affiliate-id">' . __( 'Affiliate', 'affiliates' ) . '</th>
						</tr>
						</thead>
						<tbody>
						';
					foreach ( $hits as $hit ) {
						$output .= '<tr class=" details ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
//						$output .= "<td class='time'>$hit->time</td>";
						$output .= '<td class="time">' . DateHelper::s2u( $hit->datetime ) . '</td>';
						$output .= "<td class='ip'>" . long2ip( $hit->ip ) . "</td>";
						$output .= "<td class='affiliate-id'>" . stripslashes( wp_filter_nohtml_kses( $hit->name ) ) . "</td>";
						$output .= '</tr>';
					}
					$output .= '</tbody></table>';
					$output .= '</div>'; // .details-hits
					$output .= '</td></tr>';
				} // if $expanded_hits

			} // expanded
			*/
		}
	} else {
		$output .= '<tr><td colspan="5">' . __('There are no results.', 'affiliates' ) . '</td></tr>';
	}

	$output .= '</tbody>';
	$output .= '</table>';

	if ( $paginate ) {
	  require_once( AFFILIATES_CORE_LIB . '/class-affiliates-pagination.php' );
		$pagination = new Affiliates_Pagination( $count, null, $row_count );
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

	if ( $display ) {
		echo $output;
		affiliates_footer();
	} else {
		return $output;
	}
} // function render_affiliates_admin_hits_uri()

/**
 * Counts the referrals generated from a (date, src_uri_id, dest_uri_id) combination (a row in uri's table)
 * A referral is associated to a visit, if this visit is the last one from the affiliate_id and ip, and the referral was generated after that.
 * @param string $date
 * @param int $src_uri_id
 * @param int $dest_uri_id
 * @return int The number of referrals
 */
function affiliates_get_referrals_by_hits( $date = null, $src_uri_id = null, $dest_uri_id = null ) {
	global $wpdb;

	$total_referrals = 0;

	$referrals_table = _affiliates_get_tablename( 'referrals' );
	$hits_table = _affiliates_get_tablename( 'hits' );

	$filters = " WHERE 1=%d ";
	$filter_params = array( 1 );

	if ( $date !== null ) {
		$filters .= " AND date = %s ";
		$filter_params[] = $date;
	}

	if ( $src_uri_id !== null ) {
		$filters .= " AND src_uri_id = %d ";
		$filter_params[] = $src_uri_id;
	} else {
		$filters .= " AND ( src_uri_id IS NULL ) ";
	}

	if ( $dest_uri_id !== null ) {
		$filters .= " AND dest_uri_id = %d ";
		$filter_params[] = $dest_uri_id;
	} else {
		$filters .= " AND ( dest_uri_id IS NULL ) ";
	}

	// select the hits in this row ( date - src-uri - dest-uri )
	$query = $wpdb->prepare("
			SELECT
			*
			FROM $hits_table h
			$filters
			",
			$filter_params
			);
	$hits = $wpdb->get_results( $query, OBJECT );

	if ( $hits && sizeof( $hits ) > 0 ) {
		foreach ( $hits as $hit ) {
			// for every hit, check if this is the last hit from this affiliate_id and ip
			$query = "
			SELECT
			datetime
			FROM $hits_table h
			WHERE ip = $hit->ip AND affiliate_id = $hit->affiliate_id
			ORDER BY datetime DESC
			";
			$last_hit = $wpdb->get_row( $query, OBJECT );

			if ( $last_hit && ( $last_hit->datetime == $hit->datetime ) ) {

				// if this is the last one ...
				// Search if this generated referrals
				$query = "
				SELECT
				count(DISTINCT referral_id) num_referrals
				FROM $referrals_table r
				LEFT JOIN $hits_table h ON ( h.affiliate_id = r.affiliate_id ) AND ( h.ip = r.ip ) AND ( date(h.datetime) <= date(r.datetime) )
				WHERE ( h.ip = $hit->ip ) AND ( h.affiliate_id = $hit->affiliate_id ) AND ( date( h.datetime ) <= date('$hit->datetime') ) and ( date('$hit->datetime') <= date(r.datetime) )
				";

				$result = $wpdb->get_row( $query, OBJECT );

				if ( sizeof( $result ) > 0 ){
					$total_referrals += $result->num_referrals;
				}
			}
		}
	}

	return $total_referrals;
}