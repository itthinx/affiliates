<?php
/**
 * affiliates-admin-hits-affiliate.php
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

// Shows hits by affiliate

define( 'AFFILIATES_HITS_AFFILIATE_PER_PAGE', 10 );

define( 'AFFILIATES_ADMIN_HITS_AFF_NONCE_1', 'affiliates-admin-hits-nonce-1' );
define( 'AFFILIATES_ADMIN_HITS_AFF_NONCE_2', 'affiliates-admin-hits-nonce-2' );
define( 'AFFILIATES_ADMIN_HITS_AFF_FILTER_NONCE', 'affiliates-admin-hits-filter-nonce' );

include_once( AFFILIATES_CORE_LIB . '/class-affiliates-date-helper.php');

function affiliates_admin_hits_affiliate() {

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
		isset( $_POST['expanded_referrals'] ) ||
		isset( $_POST['show_inoperative'] )
	) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_AFF_FILTER_NONCE], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	// filters
	$from_date          = $affiliates_options->get_option( 'hits_affiliate_from_date', null );
	$thru_date          = $affiliates_options->get_option( 'hits_affiliate_thru_date', null );
	$affiliate_id       = $affiliates_options->get_option( 'hits_affiliate_affiliate_id', null );
	$status             = $affiliates_options->get_option( 'hits_affiliate_status', null );
	$expanded_referrals = $affiliates_options->get_option( 'hits_affiliate_expanded_referrals', null );
	$expanded_hits      = $affiliates_options->get_option( 'hits_affiliate_expanded_hits', null );
	$show_inoperative   = $affiliates_options->get_option( 'hits_affiliate_show_inoperative', null );

	if ( isset( $_POST['clear_filters'] ) ) {
		$affiliates_options->delete_option( 'hits_affiliate_from_date' );
		$affiliates_options->delete_option( 'hits_affiliate_thru_date' );
		$affiliates_options->delete_option( 'hits_affiliate_affiliate_id' );
		$affiliates_options->delete_option( 'hits_affiliate_status' );
		$affiliates_options->delete_option( 'hits_affiliate_expanded' );
		$affiliates_options->delete_option( 'hits_affiliate_expanded_referrals' );
		$affiliates_options->delete_option( 'hits_affiliate_expanded_hits' );
		$affiliates_options->delete_option( 'hits_affiliate_show_inoperative' );
		$from_date = null;
		$thru_date = null;
		$affiliate_id = null;
		$status = null;
		$expanded_hits = null;
		$expanded_referrals = null;
		$show_inoperative = null;
	} else if ( isset( $_POST['submitted'] ) ) {
		// filter by date(s)
		if ( !empty( $_POST['from_date'] ) ) {
			$from_date = date( 'Y-m-d', strtotime( $_POST['from_date'] ) );
			$affiliates_options->update_option( 'hits_affiliate_from_date', $from_date );
		} else {
			$from_date = null;
			$affiliates_options->delete_option( 'hits_affiliate_from_date' );
		}
		if ( !empty( $_POST['thru_date'] ) ) {
			$thru_date = date( 'Y-m-d', strtotime( $_POST['thru_date'] ) );
			$affiliates_options->update_option( 'hits_affiliate_thru_date', $thru_date );
		} else {
			$thru_date = null;
			$affiliates_options->delete_option( 'hits_affiliate_thru_date' );
		}
		if ( $from_date && $thru_date ) {
			if ( strtotime( $from_date ) > strtotime( $thru_date ) ) {
				$thru_date = null;
				$affiliates_options->delete_option( 'hits_affiliate_thru_date' );
			}
		}

		// filter by affiliate id
		if ( !empty( $_POST['affiliate_id'] ) ) {
			$affiliate_id = affiliates_check_affiliate_id( $_POST['affiliate_id'] );
			if ( $affiliate_id ) {
				$affiliates_options->update_option( 'hits_affiliate_affiliate_id', $affiliate_id );
			}
		} else if ( isset( $_POST['affiliate_id'] ) ) { // empty && isset => '' => all
			$affiliate_id = null;
			$affiliates_options->delete_option( 'hits_affiliate_affiliate_id' );
		}

		if ( !empty( $_POST['status'] ) ) {
			if ( is_array( $_POST['status'] ) ) {
				$stati = array();
				foreach( $_POST['status'] as $status ) {
					if ( $status = Affiliates_Utility::verify_referral_status_transition( $status, $status ) ) {
						$stati[] = $status;
					}
				}
				if ( count( $stati ) > 0 ) {
					$status = $stati;
					$affiliates_options->update_option( 'hits_affiliate_status', $stati );
				} else {
					$status = null;
					$affiliates_options->delete_option( 'hits_affiliate_status' );
				}
			}
		} else {
			$status = null;
			$affiliates_options->delete_option( 'hits_affiliate_status' );
		}

		// expand details on hits
		if ( !empty( $_POST['expanded_hits'] ) ) {
			$expanded_hits = true;
			$affiliates_options->update_option( 'hits_affiliate_expanded_hits', true );
		} else {
			$expanded_hits = false;
			$affiliates_options->delete_option( 'hits_affiliate_expanded_hits' );
		}
		// expand details on referrals
		if ( !empty( $_POST['expanded_referrals'] ) ) {
			$expanded_referrals = true;
			$affiliates_options->update_option( 'hits_affiliate_expanded_referrals', true );
		} else {
			$expanded_referrals = false;
			$affiliates_options->delete_option( 'hits_affiliate_expanded_referrals' );
		}
		// show results related to inoperative affiliates
		if ( !empty( $_POST['show_inoperative'] ) ) {
			$show_inoperative = true;
			$affiliates_options->update_option( 'hits_affiliate_show_inoperative', true );
		} else {
			$show_inoperative = false;
			$affiliates_options->delete_option( 'hits_affiliate_show_inoperative' );
		}
	}

	if ( isset( $_POST['row_count'] ) ) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_AFF_NONCE_1], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	if ( isset( $_POST['paged'] ) ) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_AFF_NONCE_2], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'paged', $current_url );

	$affiliates_table = _affiliates_get_tablename( 'affiliates' );
	$referrals_table = _affiliates_get_tablename( 'referrals' );
	$hits_table = _affiliates_get_tablename( 'hits' );

	$output .=
		'<div>' .
			'<h1>' .
				__( 'Affiliates & Referrals', 'affiliates' ) .
			'</h1>' .
		'</div>';

	$row_count = isset( $_POST['row_count'] ) ? intval( $_POST['row_count'] ) : 0;

	if ($row_count <= 0) {
		$row_count = $affiliates_options->get_option( 'hits_affiliate_per_page', AFFILIATES_HITS_AFFILIATE_PER_PAGE );
	} else {
		$affiliates_options->update_option('hits_affiliate_per_page', $row_count );
	}
	// current page
	$paged = isset( $_REQUEST['paged'] ) ? intval( $_REQUEST['paged'] ) : 1;
	if ( $paged < 1 ) {
		$paged = 1;
	} 

	$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : null;
	switch ( $orderby ) {
		case 'hits' :
		case 'visits' :
		case 'referrals' :
		case 'ratio' :
		case 'name' :
			break;
		default:
			$orderby = 'name';
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
			$order = 'ASC';
			$switch_order = 'DESC';
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
	if ( !$show_inoperative ) {
		$filters .= " AND status = '" . AFFILIATES_AFFILIATE_STATUS_ACTIVE . "' "; 
	}

	// Get the summarized results, these are grouped by date.
	// Note: Referrals on dates without a hit will not be included.
	// @see notes about this in affiliates_admin_hits()
	$datetime_condition = "";
	if ( $from_date && $thru_date ) {
		$datetime_condition = " AND datetime >= '" . $from_datetime . "' AND datetime < '" . $thru_datetime ."' ";
	} else if ( $from_date ) {
		$datetime_condition = " AND datetime >= '" . $from_datetime . "' ";
	} else if ( $thru_date ) {
		$datetime_condition = " AND datetime < '" . $thru_datetime . "' ";
	}

	$status_condition = "";
	if ( is_array( $status ) && count( $status ) > 0 && count( $status ) < 4 ) { // 4 = total number of valid statuses
		$status_condition = " AND status IN ('" . implode( "','", $status ) . "') ";
	}

	$u2s_from_date = $from_date ? date( 'Y-m-d', strtotime( DateHelper::u2s( $from_date ) ) ) : null;
	$u2s_thru_date = $thru_date ? date( 'Y-m-d', strtotime( DateHelper::u2s( $thru_date ) ) ) : null;

	$date_condition = "";
	if ( $u2s_from_date && $u2s_thru_date ) {
		$date_condition = " AND date >= '$u2s_from_date' AND date <= '$u2s_thru_date' ";
	} else if ( $u2s_from_date ) {
		$date_condition = " AND date >= '$u2s_from_date' ";
	} else if ( $u2s_thru_date ) {
		$date_condition = " AND date <= '$u2s_thru_date' ";
	}

	$hits_subquery_where = '';
	if ( strlen( $date_condition ) > 0 ) {
		$hits_subquery_where = ' WHERE 1=1 ' . $date_condition;
	}

	$referrals_subquery_where = '';
	if ( strlen( $date_condition ) > 0 || strlen( $status_condition ) > 0 ) {
		$referrals_subquery_where = ' WHERE 1=1 ' . $datetime_condition . ' ' . $status_condition;
	}

	do {
		$repeat = false;
		$offset = ( $paged - 1 ) * $row_count;

		$query = $wpdb->prepare(
			"SELECT " .
			"SQL_CALC_FOUND_ROWS " .
			"a.affiliate_id AS affiliate_id, " .
			"a.name AS name, " .
			"IF ( hits.hits IS NOT NULL, hits.hits, 0 ) AS hits, " .
			"IF ( hits.visits IS NOT NULL, hits.visits, 0 ) AS visits, " .
			"IF ( referrals.count IS NOT NULL, referrals.count, 0 ) AS referrals, " .
			"IF ( hits.visits > 0 AND referrals.count IS NOT NULL, referrals.count / hits.visits, 0 ) AS ratio " .
			"FROM " .
			"$affiliates_table a " .
			"LEFT JOIN (SELECT affiliate_id, COUNT(DISTINCT ip) AS visits, SUM(count) AS hits FROM $hits_table $hits_subquery_where GROUP BY affiliate_id ) AS hits ON hits.affiliate_id = a.affiliate_id " .
			"LEFT JOIN (SELECT affiliate_id, COUNT(*) AS count FROM $referrals_table r $referrals_subquery_where GROUP BY affiliate_id ) AS referrals ON  referrals.affiliate_id = a.affiliate_id " .
			$filters . " " .
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
		'name'         => __( 'Affiliate', 'affiliates' ),
		'visits'       => __( 'Visitors', 'affiliates' ),
		'hits'         => __( 'Hits', 'affiliates' ),
		'referrals'    => __( 'Referrals', 'affiliates' ),
		'ratio'        => __( 'Ratio', 'affiliates' )
	);

	$output .= '<div id="" class="hits-affiliates-overview">';

	$affiliates_select = Affiliates_UI_Elements::affiliates_select(
		array(
			'name'             => 'affiliate_id',
			'class'            => 'affiliate-id-filter',
			'label-class'      => 'affiliate-id-filter',
			'affiliate_id'     => $affiliate_id,
			'show_inoperative' => $show_inoperative
		)
	);

	$status_descriptions = array(
		AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', 'affiliates' ),
		AFFILIATES_REFERRAL_STATUS_CLOSED   => __( 'Closed', 'affiliates' ),
		AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', 'affiliates' ),
		AFFILIATES_REFERRAL_STATUS_REJECTED => __( 'Rejected', 'affiliates' ),
	);
	$status_icons = array(
		AFFILIATES_REFERRAL_STATUS_ACCEPTED => "<img class='icon' alt='" . __( 'Accepted', 'affiliates') . "' src='" . AFFILIATES_PLUGIN_URL . "images/accepted.png'/>",
		AFFILIATES_REFERRAL_STATUS_CLOSED   => "<img class='icon' alt='" . __( 'Closed', 'affiliates') . "' src='" . AFFILIATES_PLUGIN_URL . "images/closed.png'/>",
		AFFILIATES_REFERRAL_STATUS_PENDING  => "<img class='icon' alt='" . __( 'Pending', 'affiliates') . "' src='" . AFFILIATES_PLUGIN_URL . "images/pending.png'/>",
		AFFILIATES_REFERRAL_STATUS_REJECTED => "<img class='icon' alt='" . __( 'Rejected', 'affiliates') . "' src='" . AFFILIATES_PLUGIN_URL . "images/rejected.png'/>",
	);
	$status_checkboxes = '';
	foreach ( $status_descriptions as $key => $label ) {
		$checked = empty( $status ) || is_array( $status ) && in_array( $key, $status ) ? ' checked="checked" ' : ''; 
		$status_checkboxes .= '<label style="padding-right:1em;">';
		$status_checkboxes .= sprintf( '<input type="checkbox" name="status[]" value="%s" %s />',  esc_attr( $key ), $checked );
		$status_checkboxes .= $status_icons[$key] . ' ' . $label;
		$status_checkboxes .= '</label>';
	}

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
				__('From', 'affiliates' ) .
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
				'<span style="padding-right:1em">' . __( 'Status', 'affiliates' ) . '</span>' .
				' ' .
				$status_checkboxes .
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
				'</div>' .

				'<div class="filter-buttons">' .
				wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_AFF_FILTER_NONCE, true, false ) .
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
					'<input name="row_count" type="text" size="2" value="' . esc_attr( $row_count ) .'" />
					' . wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_AFF_NONCE_1, true, false ) . '
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
		$output .= wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_AFF_NONCE_2, true, false );
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
			$output .= '<td class="affiliate-name">' . stripslashes( wp_filter_nohtml_kses( $result->name ) ) . ' [' . intval($result->affiliate_id ) . ']' . '</td>';
			$output .= "<td class='visits'>$result->visits</td>";
			$output .= "<td class='hits'>$result->hits</td>";
			$output .= "<td class='referrals'>$result->referrals</td>";
			$output .= "<td class='ratio'>$result->ratio</td>";
			$output .= '</tr>';

			if ( $expanded_referrals || $expanded_hits ) {

				//
				// expanded : referrals ----------------------------------------
				//
				if ( $expanded_referrals ) {

					$maximum_referrals = max( array( 0, intval( apply_filters( 'affiliates_admin_hits_affiliate_maximum_referrals', 20 ) ) ) );

					// get the detailed results for referrals
					$referrals_filters = " WHERE affiliate_id = %d ";
					$referrals_filter_params = array( $result->affiliate_id );
					if ( $from_date && $thru_date ) {
						$referrals_filters .= " AND datetime >= %s AND datetime < %s ";
						$referrals_filter_params[] = $from_datetime;
						$referrals_filter_params[] = $thru_datetime;
					} else if ( $from_date ) {
						$referrals_filters .= " AND datetime >= %s ";
						$referrals_filter_params[] = $from_datetime;
					} else if ( $thru_date ) {
						$referrals_filters .= " AND datetime < %s ";
						$referrals_filter_params[] = $thru_datetime;
					}
					$referrals_orderby = "datetime $order";
					$referrals_query = $wpdb->prepare(
						"SELECT SQL_CALC_FOUND_ROWS * " .
						"FROM $referrals_table " .
						"$referrals_filters " .
						"$status_condition " .
						"ORDER BY $referrals_orderby " .
						"LIMIT $maximum_referrals", // maximum most recent referrals displayed
						$referrals_filter_params
					);
					$referrals = $wpdb->get_results( $referrals_query, OBJECT );
					$referrals_count = intval( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );
					if ( count($referrals) > 0 ) {
						$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
						$output .= '<td colspan="5">';
						$output .= '<div class="details-referrals">';
						$output .= '<p class="description">' . __( 'Referrals', 'affiliates' ) .  sprintf( ' (%d/%d)', count( $referrals ), $referrals_count ) . '</p>';
						$output .= '<table id="details-referrals-' . esc_attr( $result->affiliate_id ) . '" class="details-referrals" cellspacing="0">';
						$output .= '<thead>';
						$output .= '<tr>';
						$output .= '<th scope="col" class="datetime">' . __( 'Date', 'affiliates' ) . '</th>';
						$output .= '<th scope="col" class="post-id">' . __( 'Post', 'affiliates' ) . '</th>';
						$output .= '</tr>';
						$output .= '</thead>';
						$output .= '<tbody>';
						foreach ( $referrals as $referral ) {
							$output .= '<tr class="details-referrals ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
							$output .= "<td class='datetime'>" . DateHelper::s2u( $referral->datetime ) . "</td>";
							$link = get_permalink( $referral->post_id );
							$title = get_the_title( $referral->post_id );
							$output .= '<td class="post-id"><a href="' . esc_attr( $link ) . '" target="_blank">' . stripslashes( wp_filter_nohtml_kses( $title ) ) . '</a></td>';
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
					$maximum_hits = max( array( 0, intval( apply_filters( 'affiliates_admin_hits_affiliate_maximum_hits', 20 ) ) ) );
					// get the detailed results for hits
					$details_orderby = "date $order, time $order";
					$details_filters = " WHERE h.affiliate_id = %d ";
					$details_filter_params = array( $result->affiliate_id );
					if ( $u2s_from_date && $u2s_thru_date ) {
						$details_filters .= " AND date >= %s AND date <= %s ";
						$details_filter_params[] = $u2s_from_date;
						$details_filter_params[] = $u2s_thru_date;
					} else if ( $u2s_from_date ) {
						$details_filters .= " AND date >= %s ";
						$details_filter_params[] = $u2s_from_date;
					} else if ( $u2s_thru_date ) {
						$details_filters .= " AND date <= %s ";
						$details_filter_params[] = $u2s_thru_date;
					}
					$user_agents_table = _affiliates_get_tablename( 'user_agents' );
					$uris_table = _affiliates_get_tablename( 'uris' );
					$details_query = $wpdb->prepare(
						"SELECT SQL_CALC_FOUND_ROWS h.*, a.*, ua.*, src.uri src_uri, dest.uri dest_uri " .
						"FROM $hits_table h " .
						"LEFT JOIN $affiliates_table a ON h.affiliate_id = a.affiliate_id " .
						"LEFT JOIN $user_agents_table ua ON h.user_agent_id = ua.user_agent_id " .
						"LEFT JOIN $uris_table src ON h.src_uri_id = src.uri_id " .
						"LEFT JOIN $uris_table dest ON h.dest_uri_id = dest.uri_id " .
						"$details_filters " .
						"ORDER BY $details_orderby " .
						"LIMIT $maximum_hits", // maximum most recent hits displayed
						$details_filter_params
					);
					$hits = $wpdb->get_results( $details_query, OBJECT );
					$hits_count = intval( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );
					if ( count( $hits ) > 0 ) { 
						$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
						$output .= '<td colspan="5">';
						$output .= '<div class="details-hits">';
						$output .= '<p class="description">' . __( 'Hits', 'affiliates' ) . sprintf( ' (%d/%d)', count( $hits ), $hits_count ) . '</p>';
						$output .= '<table id="details-hits-' . esc_attr( $result->affiliate_id ) . '" class="details-hits" cellspacing="0">';
						$output .= '<thead>';
						$output .= '<tr>';
						$output .= '<th scope="col" class="datetime">' . __( 'Date', 'affiliates' ) . '</th>';
						$output .= '<th scope="col" class="ip">' . __( 'IP', 'affiliates' ) . '</th>';
						$output .= '<th scope="col" class="count">' . __( 'Count', 'affiliates' ) . '</th>';
						$output .= '<th scope="col" class="affiliate-id">' . __( 'Affiliate', 'affiliates' ) . '</th>';
						$output .= '<th scrope="col" class="src-uri">' . __( 'Source URI', 'affiliates' ) . '</th>';
						$output .= '<th scrope="col" class="src-uri">' . __( 'Landing URI', 'affiliates' ) . '</th>';
						$output .= '<th scope="col" class="hit-user-agent">' . __( 'User Agent', 'affiliates' ) . '</th>';
						$output .= '</tr>';
						$output .= '</thead>';
						$output .= '<tbody>';
						foreach ( $hits as $hit ) {
							$output .= '<tr class="details ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
							$output .= '<td class="datetime">' . DateHelper::s2u( $hit->datetime ) . '</td>';
							$output .= "<td class='ip'>" . long2ip( $hit->ip ) . "</td>";
							$output .= "<td class='count'>$hit->count</td>";
							$output .= "<td class='affiliate-id'>" . stripslashes( wp_filter_nohtml_kses( $hit->name ) ) . "</td>";
							$output .= "<td class='src-uri'>" . esc_html( $hit->src_uri ) . "</td>";
							$output .= "<td class='dest-uri'>" . esc_html( $hit->dest_uri ) . "</td>";
							$output .= "<td class='hit-user-agent'>" . esc_html( $hit->user_agent ) . "</td>";
							$output .= '</tr>';
						}
						$output .= '</tbody></table>';
						$output .= '</div>'; // .details-hits
						$output .= '</td></tr>';
					}
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
		$pagination = new Affiliates_Pagination($count, null, $row_count);
		$output .= '<div class="tablenav bottom">';
		$output .= $pagination->pagination( 'bottom' );
		$output .= '</div>';
	}

	$output .= '</div>'; // .visits-overview
	echo $output;
	affiliates_footer();
} // function affiliates_admin_hits()
