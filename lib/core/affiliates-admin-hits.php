<?php
/**
 * affiliates-admin-hits.php
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

// Shows hits by date

require_once AFFILIATES_CORE_LIB . '/class-affiliates-date-helper.php';

function affiliates_admin_hits() {

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
		isset( $_POST['expanded'] ) ||
		isset( $_POST['expanded_hits'] ) ||
		isset( $_POST['expanded_referrals'] ) ||
		isset( $_POST['show_inoperative'] )
	) {
		if (
			!isset( $_POST[AFFILIATES_ADMIN_HITS_FILTER_NONCE] ) ||
			!wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_FILTER_NONCE], 'admin' )
		) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	// filters
	$from_date          = $affiliates_options->get_option( 'hits_from_date', null );
	$thru_date          = $affiliates_options->get_option( 'hits_thru_date', null );
	$affiliate_id       = $affiliates_options->get_option( 'hits_affiliate_id', null );
	$expanded           = $affiliates_options->get_option( 'hits_expanded', null ); // @todo input ist not shown, eventually remove unless ...
	$expanded_referrals = $affiliates_options->get_option( 'hits_expanded_referrals', null );
	$expanded_hits      = $affiliates_options->get_option( 'hits_expanded_hits', null );
	$show_inoperative   = $affiliates_options->get_option( 'hits_show_inoperative', null );

	if ( isset( $_POST['clear_filters'] ) ) {
		$affiliates_options->delete_option( 'hits_from_date' );
		$affiliates_options->delete_option( 'hits_thru_date' );
		$affiliates_options->delete_option( 'hits_affiliate_id' );
		$affiliates_options->delete_option( 'hits_expanded' );
		$affiliates_options->delete_option( 'hits_expanded_referrals' );
		$affiliates_options->delete_option( 'hits_expanded_hits' );
		$affiliates_options->delete_option( 'hits_show_inoperative' );
		$from_date = null;
		$thru_date = null;
		$affiliate_id = null;
		$expanded = null;
		$expanded_hits = null;
		$expanded_referrals = null;
		$show_inoperative = null;
	} else if ( isset( $_POST['submitted'] ) ) {
		// filter by date(s)
		if ( !empty( $_POST['from_date'] ) ) {
			$from_date = date( 'Y-m-d', strtotime( $_POST['from_date'] ) );
			$affiliates_options->update_option( 'hits_from_date', $from_date );
		} else {
			$from_date = null;
			$affiliates_options->delete_option( 'hits_from_date' );
		}
		if ( !empty( $_POST['thru_date'] ) ) {
			$thru_date = date( 'Y-m-d', strtotime( $_POST['thru_date'] ) );
			$affiliates_options->update_option( 'hits_thru_date', $thru_date );
		} else {
			$thru_date = null;
			$affiliates_options->delete_option( 'hits_thru_date' );
		}
		if ( $from_date && $thru_date ) {
			if ( strtotime( $from_date ) > strtotime( $thru_date ) ) {
				$thru_date = null;
				$affiliates_options->delete_option( 'hits_thru_date' );
			}
		}

		// filter by affiliate id
		if ( !empty( $_POST['affiliate_id'] ) ) {
			$affiliate_id = affiliates_check_affiliate_id( $_POST['affiliate_id'] );
			if ( $affiliate_id ) {
				$affiliates_options->update_option( 'hits_affiliate_id', $affiliate_id );
			}
		} else if ( isset( $_POST['affiliate_id'] ) ) { // empty && isset => '' => all
			$affiliate_id = null;
			$affiliates_options->delete_option( 'hits_affiliate_id' );
		}

		// expanded details?
		if ( !empty( $_POST['expanded'] ) ) {
			$expanded = true;
			$affiliates_options->update_option( 'hits_expanded', true );
		} else {
			$expanded = false;
			$affiliates_options->delete_option( 'hits_expanded' );
		}
		if ( !empty( $_POST['expanded_hits'] ) ) {
			$expanded_hits = true;
			$affiliates_options->update_option( 'hits_expanded_hits', true );
		} else {
			$expanded_hits = false;
			$affiliates_options->delete_option( 'hits_expanded_hits' );
		}
		if ( !empty( $_POST['expanded_referrals'] ) ) {
			$expanded_referrals = true;
			$affiliates_options->update_option( 'hits_expanded_referrals', true );
		} else {
			$expanded_referrals = false;
			$affiliates_options->delete_option( 'hits_expanded_referrals' );
		}
		if ( !empty( $_POST['show_inoperative'] ) ) {
			$show_inoperative = true;
			$affiliates_options->update_option( 'hits_show_inoperative', true );
		} else {
			$show_inoperative = false;
			$affiliates_options->delete_option( 'hits_show_inoperative' );
		}
	}

	if ( isset( $_POST['row_count'] ) ) {
		if (
			!isset( $_POST[AFFILIATES_ADMIN_HITS_NONCE_1] ) ||
			!wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_NONCE_1], 'admin' )
		) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	if ( isset( $_POST['paged'] ) ) {
		if (
			!isset( $_POST[AFFILIATES_ADMIN_HITS_NONCE_2] ) ||
			!wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_NONCE_2], 'admin' )
		) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'paged', $current_url );

	$affiliates_table = _affiliates_get_tablename( 'affiliates' );
	$referrals_table = _affiliates_get_tablename( 'referrals' );
	$hits_table = _affiliates_get_tablename( 'hits' );

	$output .= '<h1>';
	$output .= __( 'Visits & Referrals', 'affiliates' );
	$output .= '</h1>';

	$row_count = isset( $_POST['row_count'] ) ? intval( $_POST['row_count'] ) : 0;

	if ($row_count <= 0) {
		$row_count = $affiliates_options->get_option( 'affiliates_hits_per_page', AFFILIATES_HITS_PER_PAGE );
	} else {
		$affiliates_options->update_option('affiliates_hits_per_page', $row_count );
	}
	// current page
	$paged = isset( $_REQUEST['paged'] ) ? intval( $_REQUEST['paged'] ) : 1;
	if ( $paged < 1 ) {
		$paged = 1;
	}

	$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : null;
	switch ( $orderby ) {
		case 'date' :
		case 'visits' :
		case 'hits' :
		case 'referrals' :
		case 'ratio' :
			break;
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
	$u2s_from_date = $from_date ? date( 'Y-m-d', strtotime( DateHelper::u2s( $from_date ) ) ) : null;
	$u2s_thru_date = $thru_date ? date( 'Y-m-d', strtotime( DateHelper::u2s( $thru_date ) ) ) : null;
	if ( $u2s_from_date && $u2s_thru_date ) {
		$filters .= " AND date >= %s AND date <= %s ";
		$filter_params[] = $u2s_from_date;
		$filter_params[] = $u2s_thru_date;
	} else if ( $u2s_from_date ) {
		$filters .= " AND date >= %s ";
		$filter_params[] = $u2s_from_date;
	} else if ( $u2s_thru_date ) {
		$filters .= " AND date <= %s ";
		$filter_params[] = $u2s_thru_date;
	}

	if ( $affiliate_id ) {
		$filters .= " AND affiliate_id = %d ";
		$filter_params[] = $affiliate_id;
	}

	// @since 4.10.0 correct referrals count requires filters to be taken into account
	$referrals_subquery_where = '';
	$referrals_subquery_conditions = array();
	$referrals_subquery_params = array();
	if ( $affiliate_id ) {
		$referrals_subquery_conditions[] = "affiliate_id = %d";
		$referrals_subquery_params[] = $affiliate_id;
	}
	if ( $u2s_from_date && $u2s_thru_date ) {
		$referrals_subquery_conditions[] = " datetime >= %s AND datetime <= %s ";
		$referrals_subquery_params[] = $u2s_from_date . ' 00:00:00';
		$referrals_subquery_params[] = $u2s_thru_date . ' 23:59:59';
	} else if ( $u2s_from_date ) {
		$referrals_subquery_conditions[] = " datetime >= %s ";
		$referrals_subquery_params[] = $u2s_from_date . ' 00:00:00';
	} else if ( $u2s_thru_date ) {
		$referrals_subquery_conditions[] = " datetime <= %s ";
		$referrals_subquery_params[] = $u2s_thru_date . ' 23:59:59';
	}
	if ( count( $referrals_subquery_conditions ) > 0 ) {
		$referrals_subquery_where = ' WHERE ' . implode( ' AND ', $referrals_subquery_conditions );
		$referrals_subquery_where = $wpdb->prepare( $referrals_subquery_where, $referrals_subquery_params );
	}

	do {
		$repeat = false;
		$offset = ( $paged - 1 ) * $row_count;

		// Get the summarized results, these are grouped by date.
		// If there were any referral on a date without a hit, it would not be included:
		// Example conditions:
		// - 2011-02-01 23:59:59 hit recorded
		// - 2011-02-02 00:10:05 referral recorded
		// - no hits recorded on 2011-02-02
		// =>
		// - the referral will not show up
		// So, for ratio calculation, only the date with actual visits and referrals will show up.
		// Referrals on dates without visits would give an infinite ratio (x referrals / 0 visits).
		// We have a separate page which shows all referrals.
		$query = $wpdb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS " .
			"hits.date, " .
			"hits.hits, " .
			"hits.visits, " .
			"IF ( referrals.count IS NOT NULL, referrals.count, 0 ) AS referrals, " .
			"IF ( referrals.count IS NOT NULL AND hits.visits > 0, referrals.count / hits.visits, 0 ) AS ratio " .
			"FROM " .
			"( SELECT date, COUNT(DISTINCT ip) AS visits, COUNT(*) AS hits FROM $hits_table $filters GROUP BY date ) AS hits " .
			"LEFT JOIN ( SELECT COUNT(*) AS count, date(datetime) AS date FROM $referrals_table $referrals_subquery_where GROUP BY date(datetime) ) AS referrals ON hits.date = referrals.date " .
			"ORDER BY $orderby $order " .
			"LIMIT $row_count OFFSET $offset",
			$filter_params
		);
		$results = $wpdb->get_results( $query, OBJECT );

		$count = intval( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );
		if ( $count > $row_count ) {
			$paginate = true;
		} else {
			$paginate = false;
		}
		$pages = max( array( 1, ceil( $count / $row_count ) ) );
		if ( $paged > $pages ) {
			$paged = $pages;
			$repeat = true;
		}
	} while ( $repeat );

	$column_display_names = array(
		'date'      => __( 'Date', 'affiliates' ) . '*',
		'visits'    => __( 'Visits', 'affiliates' ),
		'hits'      => __( 'Hits', 'affiliates' ),
		'referrals' => __( 'Referrals', 'affiliates' ),
		'ratio'     => __( 'Ratio', 'affiliates' )
	);

	$output .= '<div id="" class="hits-overview">';

	$affiliates_select = Affiliates_UI_Elements::affiliates_select(
		array(
			'name'             => 'affiliate_id',
			'class'            => 'affiliate-id-filter',
			'label-class'      => 'affiliate-id-filter',
			'affiliate_id'     => $affiliate_id,
			'show_inoperative' => $show_inoperative
		)
	);

	$output .=
		'<div class="filters">' .
			'<label class="description" for="setfilters">' . __( 'Filters', 'affiliates' ) . '</label>' .
			'<form id="setfilters" action="" method="post">' .

				'<div class="filter-section">' .
				$affiliates_select .
				Affiliates_UI_Elements::render_select( 'select.affiliate-id-filter' ) .
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
				'<label class="expanded-filter">' .
				'<input class="expanded-filter" name="expanded_referrals" type="checkbox" ' . ( $expanded_referrals ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Expand referrals', 'affiliates' ) .
				'</label>' .
				' ' .
				'<label class="expanded-filter">' .
				'<input class="expanded-filter" name="expanded_hits" type="checkbox" ' . ( $expanded_hits ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Expand hits', 'affiliates' ) .
				'</label>' .
				' ' .
				'<label class="show-inoperative-filter">' .
				'<input class="show-inoperative-filter" name="show_inoperative" type="checkbox" ' . ( $show_inoperative ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Include inoperative affiliates', 'affiliates' ) .
				'</label>' .
//				'<label class="expanded-filter" for="expanded">' . __( 'Expand details', 'affiliates' ) . '</label>' .
//				'<input class="expanded-filter" name="expanded" type="checkbox" ' . ( $expanded ? 'checked="checked"' : '' ) . '/>' .
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
		$pagination = new Affiliates_Pagination( $count, null, $row_count );
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
			$class = "$key manage-column sortable";
		}
		$column_display_name = '<a href="' . esc_url( add_query_arg( $options, $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
		$output .= "<th scope='col' class='$class'>$column_display_name</th>";
	}

	$output .= '</tr>';
	$output .= '</thead>';
	$output .= '<tbody>';

	if ( count( $results ) > 0 ) {
		for ( $i = 0; $i < count( $results ); $i++ ) {

			$result = $results[$i];
			$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
			$output .= '<td class="date">'. esc_html( $result->date ) . '</td>';
			$output .= '<td class="visits">' . esc_html( $result->visits ) . '</td>';
			$output .= '<td class="hits">' . esc_html( $result->hits ) . '</td>';
			$output .= '<td class="referrals">' . esc_html( $result->referrals ) . '</td>';
			$output .= '<td class="ratio">' . esc_html( $result->ratio ) . '</td>';
			$output .= '</tr>';

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
					if ( count( $referrals) > 0 ) {
						$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
						$output .= '<td colspan="5">';
						$output .= '<div class="details-referrals">';
						$output .= '<p class="description">' . esc_html__( 'Referrals', 'affiliates' ) . '</p>';
						$output .= '<table id="details-referrals-' . esc_attr( $result->date ) . '" class="details-referrals" cellspacing="0">';
						$output .= '<thead>';
						$output .= '<tr>';
						$output .= '<th scope="col" class="datetime">' . esc_html__( 'Date', 'affiliates' ) . '</th>';
						$output .= '<th scope="col" class="post-id">' . esc_html__( 'Post', 'affiliates' ) . '</th>';
						$output .= '<th scope="col" class="affiliate-id">' . esc_html__( 'Affiliate', 'affiliates' ) . '</th>';
						$output .= '</tr>';
						$output .= '</thead>';
						$output .= '<tbody>';
						foreach ( $referrals as $referral ) {
							$output .= '<tr class="details-referrals ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
							$output .= '<td class="datetime">' . esc_html( $referral->datetime ) . '</td>';
							$link = affiliates_get_referral_post_permalink( $referral );
							$title = affiliates_get_referral_post_title( $referral );
							$output .= '<td class="post-id">';
							if ( !empty( $link ) ) {
								$output .= sprintf(
									'<a href="%s" target="_blank">%s</a>',
									esc_url( $link ),
									stripslashes( wp_filter_nohtml_kses( $title ) )
								);
							} else {
								$output .= stripslashes( wp_filter_nohtml_kses( $title ) );
							}
							$output .= '</td>';
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
					$details_orderby = "date $order";

					$details_filters = " WHERE h.date = %s ";
					$details_filter_params = array( $result->date );
					if ( $affiliate_id ) {
						$details_filters .= " AND h.affiliate_id = %d ";
						$details_filter_params[] = $affiliate_id;
					}

					$user_agents_table = _affiliates_get_tablename( 'user_agents' );
					$uris_table = _affiliates_get_tablename( 'uris' );
					$details_query = $wpdb->prepare(
						"SELECT h.*, a.*, ua.*, src.uri src_uri, dest.uri dest_uri
						FROM $hits_table h
						LEFT JOIN $affiliates_table a ON h.affiliate_id = a.affiliate_id
						LEFT JOIN $user_agents_table ua ON h.user_agent_id = ua.user_agent_id
						LEFT JOIN $uris_table src ON h.src_uri_id = src.uri_id
						LEFT JOIN $uris_table dest ON h.dest_uri_id = dest.uri_id
						$details_filters
						ORDER BY $details_orderby
						",
						$details_filter_params
					);
					$hits = $wpdb->get_results( $details_query, OBJECT );
					$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
					$output .= '<td colspan="5">';
					$output .= '<div class="details-hits">';
					$output .= '<p class="description">' . esc_html__( 'Hits', 'affiliates' ) . '</p>';
					$output .= '<table id="details-hits-' . esc_attr( $result->date ) . '" class="details-hits" cellspacing="0">';
					$output .= '<thead>';
					$output .= '<tr>';
					$output .= '<th scope="col" class="datetime">' . esc_html__( 'Date', 'affiliates' ) . '</th>';
					$output .= '<th scope="col" class="ip">' . esc_html__( 'IP', 'affiliates' ) . '</th>';
					$output .= '<th scope="col" class="affiliate-id">' . esc_html__( 'Affiliate', 'affiliates' ) . '</th>';
					$output .= '<th scrope="col" class="src-uri">' . esc_html__( 'Source URI', 'affiliates' ) . '</th>';
					$output .= '<th scrope="col" class="src-uri">' . esc_html__( 'Landing URI', 'affiliates' ) . '</th>';
					$output .= '<th scope="col" class="hit-user-agent">' . esc_html__( 'User Agent', 'affiliates' ) . '</th>';
					$output .= '</tr>';
					$output .= '</thead>';
					$output .= '<tbody>';
					foreach ( $hits as $hit ) {
						$output .= '<tr class="details ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
						$output .= '<td class="datetime">' . esc_html( $hit->datetime ) . '</td>';
						$output .= "<td class='ip'>" . long2ip( $hit->ip ) . "</td>";
						$output .= "<td class='affiliate-id'>" . stripslashes( wp_filter_nohtml_kses( $hit->name ) ) . "</td>";
						$output .= "<td class='src-uri'>" . esc_html( $hit->src_uri ) . "</td>";
						$output .= "<td class='dest-uri'>" . esc_html( $hit->dest_uri ) . "</td>";
						$output .= "<td class='hit-user-agent'>" . esc_html( $hit->user_agent ) . "</td>";
						$output .= '</tr>';
					}
					$output .= '</tbody></table>';
					$output .= '</div>'; // .details-hits
					$output .= '</td></tr>';
				} // if $expanded_hits

			} // expanded
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
	$output .= '<p>';
	$output .= sprintf(
		__( "* Date is given for the server's time zone : %s, which has an offset of %s hours with respect to GMT.", 'affiliates' ),
		$server_dtz->getName(),
		$server_dtz->getOffset( new DateTime() ) / 3600.0
	);
	$output .= '</p>';
	$output .= '</div>'; // .visits-overview
	echo $output;
	affiliates_footer();
} // function affiliates_admin_hits()
