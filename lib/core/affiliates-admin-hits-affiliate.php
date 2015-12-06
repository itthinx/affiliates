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
		wp_die( __( 'Access denied.', AFFILIATES_PLUGIN_DOMAIN ) );
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
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_AFF_FILTER_NONCE], 'admin' ) ) {
			wp_die( __( 'Access denied.', AFFILIATES_PLUGIN_DOMAIN ) );
		}
	}
	
	// filters
	$from_date          = $affiliates_options->get_option( 'hits_affiliate_from_date', null );
	$thru_date          = $affiliates_options->get_option( 'hits_affiliate_thru_date', null );
	$affiliate_id       = $affiliates_options->get_option( 'hits_affiliate_affiliate_id', null );
	$status             = $affiliates_options->get_option( 'hits_affiliate_status', null );
	$expanded           = $affiliates_options->get_option( 'hits_affiliate_expanded', null ); // @todo input ist not shown, eventually remove unless ...
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
		$expanded = null;
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

		// expanded details?
		if ( !empty( $_POST['expanded'] ) ) {
			$expanded = true;
			$affiliates_options->update_option( 'hits_affiliate_expanded', true );
		} else {
			$expanded = false;
			$affiliates_options->delete_option( 'hits_affiliate_expanded' );
		}
		if ( !empty( $_POST['expanded_hits'] ) ) {
			$expanded_hits = true;
			$affiliates_options->update_option( 'hits_affiliate_expanded_hits', true );
		} else {
			$expanded_hits = false;
			$affiliates_options->delete_option( 'hits_affiliate_expanded_hits' );
		}
		if ( !empty( $_POST['expanded_referrals'] ) ) {
			$expanded_referrals = true;
			$affiliates_options->update_option( 'hits_affiliate_expanded_referrals', true );
		} else {
			$expanded_referrals = false;
			$affiliates_options->delete_option( 'hits_affiliate_expanded_referrals' );
		}
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
			wp_die( __( 'Access denied.', AFFILIATES_PLUGIN_DOMAIN ) );
		}
	}
	
	if ( isset( $_POST['paged'] ) ) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_AFF_NONCE_2], 'admin' ) ) {
			wp_die( __( 'Access denied.', AFFILIATES_PLUGIN_DOMAIN ) );
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
				__( 'Affiliates & Referrals', AFFILIATES_PLUGIN_DOMAIN ) .
			'</h1>' .
		'</div>';

	$row_count = isset( $_POST['row_count'] ) ? intval( $_POST['row_count'] ) : 0;
	
	if ($row_count <= 0) {
		$row_count = $affiliates_options->get_option( 'hits_affiliate_per_page', AFFILIATES_HITS_AFFILIATE_PER_PAGE );
	} else {
		$affiliates_options->update_option('hits_affiliate_per_page', $row_count );
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
	if ( $from_date && $thru_date ) {
		$filters .= " AND datetime >= %s AND datetime < %s ";
		$filter_params[] = $from_datetime;
		$filter_params[] = $thru_datetime;
	} else if ( $from_date ) {
		$filters .= " AND datetime >= %s ";
		$filter_params[] = $from_datetime;
	} else if ( $thru_date ) {
		$filters .= " AND datetime < %s ";
		$filter_params[] = $thru_datetime;
	}
	if ( $affiliate_id ) {
		$filters .= " AND h.affiliate_id = %d ";
		$filter_params[] = $affiliate_id;
	}
	
	// how many are there ?
	$count_query = $wpdb->prepare(
		"SELECT affiliate_id FROM $hits_table h
		$filters
		GROUP BY affiliate_id
		",
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
				
	// Get the summarized results, these are grouped by date.
	// Note: Referrals on dates without a hit will not be included.
	// @see notes about this in affiliates_admin_hits()
	$date_condition = "";
	if ( $from_date && $thru_date ) {
		$date_condition = " AND datetime >= '" . $from_datetime . "' AND datetime < '" . $thru_datetime ."' ";
	} else if ( $from_date ) {
		$date_condition = " AND datetime >= '" . $from_datetime . "' ";
	} else if ( $thru_date ) {
		$date_condition = " AND datetime < '" . $thru_datetime . "' ";
	}
	$status_condition = "";
	if ( is_array( $status ) && count( $status ) > 0 ) {
		$status_condition = " AND status IN ('" . implode( "','", $status ) . "') ";
	}
	$query = $wpdb->prepare("
			SELECT
				*,
				count(distinct ip) visits,
				sum(count) hits,
				(SELECT COUNT(*) FROM $referrals_table WHERE affiliate_id = h.affiliate_id $date_condition $status_condition ) referrals,
				((SELECT COUNT(*) FROM $referrals_table WHERE affiliate_id = h.affiliate_id $date_condition $status_condition )/COUNT(DISTINCT ip)) ratio
			FROM $hits_table h
			LEFT JOIN $affiliates_table a ON h.affiliate_id = a.affiliate_id
			$filters
			GROUP BY h.affiliate_id
			ORDER BY $orderby $order
			LIMIT $row_count OFFSET $offset
			",
			$filter_params
	);

	$results = $wpdb->get_results( $query, OBJECT );

	$column_display_names = array(
		'name'         => __( 'Affiliate', AFFILIATES_PLUGIN_DOMAIN ),
		'visits'       => __( 'Visitors', AFFILIATES_PLUGIN_DOMAIN ),
		'hits'         => __( 'Hits', AFFILIATES_PLUGIN_DOMAIN ),
		'referrals'    => __( 'Referrals', AFFILIATES_PLUGIN_DOMAIN ),
		'ratio'        => __( 'Ratio', AFFILIATES_PLUGIN_DOMAIN )
	);
	
	$output .= '<div id="" class="hits-affiliates-overview">';

	$affiliates = affiliates_get_affiliates( true, !$show_inoperative );
	$affiliates_select = '';
	if ( !empty( $affiliates ) ) {
		$affiliates_select .= '<label class="affiliate-id-filter">';
		$affiliates_select .= __( 'Affiliate', AFFILIATES_PLUGIN_DOMAIN );
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

	$status_descriptions = array(
		AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', AFFILIATES_PLUGIN_DOMAIN ),
		AFFILIATES_REFERRAL_STATUS_CLOSED   => __( 'Closed', AFFILIATES_PLUGIN_DOMAIN ),
		AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', AFFILIATES_PLUGIN_DOMAIN ),
		AFFILIATES_REFERRAL_STATUS_REJECTED => __( 'Rejected', AFFILIATES_PLUGIN_DOMAIN ),
	);
	$status_icons = array(
		AFFILIATES_REFERRAL_STATUS_ACCEPTED => "<img class='icon' alt='" . __( 'Accepted', AFFILIATES_PLUGIN_DOMAIN) . "' src='" . AFFILIATES_PLUGIN_URL . "images/accepted.png'/>",
		AFFILIATES_REFERRAL_STATUS_CLOSED   => "<img class='icon' alt='" . __( 'Closed', AFFILIATES_PLUGIN_DOMAIN) . "' src='" . AFFILIATES_PLUGIN_URL . "images/closed.png'/>",
		AFFILIATES_REFERRAL_STATUS_PENDING  => "<img class='icon' alt='" . __( 'Pending', AFFILIATES_PLUGIN_DOMAIN) . "' src='" . AFFILIATES_PLUGIN_URL . "images/pending.png'/>",
		AFFILIATES_REFERRAL_STATUS_REJECTED => "<img class='icon' alt='" . __( 'Rejected', AFFILIATES_PLUGIN_DOMAIN) . "' src='" . AFFILIATES_PLUGIN_URL . "images/rejected.png'/>",
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
			'<label class="description" for="setfilters">' . __( 'Filters', AFFILIATES_PLUGIN_DOMAIN ) . '</label>' .
			'<form id="setfilters" action="" method="post">' .

				'<div class="filter-section">' .
				$affiliates_select .
				'</div>' .

				'<div class="filter-section">' .
				'<label class="from-date-filter">' .
				__('From', AFFILIATES_PLUGIN_DOMAIN ) .
				' ' .
				'<input class="datefield from-date-filter" name="from_date" type="text" value="' . esc_attr( $from_date ) . '"/>'.
				'</label>' .
				' ' .
				'<label class="thru-date-filter">' .
				__( 'Until', AFFILIATES_PLUGIN_DOMAIN ) .
				' ' .
				'<input class="datefield thru-date-filter" name="thru_date" type="text" class="datefield" value="' . esc_attr( $thru_date ) . '"/>'.
				'</label>' .
				'</div>' .
				
				'<div class="filter-section">' .
				'<span style="padding-right:1em">' . __( 'Status', AFFILIATES_PLUGIN_DOMAIN ) . '</span>' .
				' ' .
				$status_checkboxes .
				'</div>' .

				'<div class="filter-section">' .
//				'<label class="expanded-filter" for="expanded">' . __( 'Expand details', AFFILIATES_PLUGIN_DOMAIN ) . '</label>' .
//				'<input class="expanded-filter" name="expanded" type="checkbox" ' . ( $expanded ? 'checked="checked"' : '' ) . '/>' .

				'<label class="expanded-filter">' .
				'<input class="expanded-filter" name="expanded_referrals" type="checkbox" ' . ( $expanded_referrals ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Expand referrals', AFFILIATES_PLUGIN_DOMAIN ) .
				'</label>' .
				' ' .
				'<label class="expanded-filter">' .
				'<input class="expanded-filter" name="expanded_hits" type="checkbox" ' . ( $expanded_hits ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Expand hits', AFFILIATES_PLUGIN_DOMAIN ) .
				'</label>' .
				' ' .
				'<label class="show-inoperative-filter">' .
				'<input class="show-inoperative-filter" name="show_inoperative" type="checkbox" ' . ( $show_inoperative ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Include inoperative affiliates', AFFILIATES_PLUGIN_DOMAIN ) .
				'</label>' .
				'</div>' .

				'<div class="filter-buttons">' .
				wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_AFF_FILTER_NONCE, true, false ) .
				'<input class="button" type="submit" value="' . __( 'Apply', AFFILIATES_PLUGIN_DOMAIN ) . '"/>' .
				'<input class="button" type="submit" name="clear_filters" value="' . __( 'Clear', AFFILIATES_PLUGIN_DOMAIN ) . '"/>' .
				'<input type="hidden" value="submitted" name="submitted"/>' .
				'</div>' .
			'</form>' .
		'</div>';
							
	$output .= '
		<div class="page-options">
			<form id="setrowcount" action="" method="post">
				<div>
					<label for="row_count">' . __('Results per page', AFFILIATES_PLUGIN_DOMAIN ) . '</label>' .
					//<input name="page" type="hidden" value="' . esc_attr( $page ) . '"/>
					'<input name="row_count" type="text" size="2" value="' . esc_attr( $row_count ) .'" />
					' . wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_AFF_NONCE_1, true, false ) . '
					<input class="button" type="submit" value="' . __( 'Apply', AFFILIATES_PLUGIN_DOMAIN ) . '"/>
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
			$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
			$affiliate = affiliates_get_affiliate( $result->affiliate_id );
			$output .= "<td class='affiliate-name'>" . stripslashes( wp_filter_nohtml_kses( $affiliate['name'] ) ) . "</td>";
			$output .= "<td class='visits'>$result->visits</td>";
			$output .= "<td class='hits'>$result->hits</td>";
			$output .= "<td class='referrals'>$result->referrals</td>";
			$output .= "<td class='ratio'>$result->ratio</td>";
			$output .= '</tr>';
			
			if ( $expanded || $expanded_referrals || $expanded_hits ) {
				
				//
				// expanded : referrals ----------------------------------------
				//
				if ( $expanded_referrals ) {
					// get the detailed results for referrals
					$referrals_filters = " WHERE r.affiliate_id = %d ";
					$referrals_filter_params = array( $result->affiliate_id );
					if ( $from_date && $thru_date ) {
						$referrals_filters .= " AND datetime >= %s AND datetime < %s ";
						$referrals_filter_params[] = $from_datetime;
						$referrals_filter_params[] = $thru_datetime;
					} else if ( $from_date ) {
						$referrals_filters .= " AND datetime >= %s ";
						$referrals_filter_params[] = $from_datetime;
					} else if ( $thru_date ) {
						$referrals_filters .= " datetime < %s ";
						$referrals_filter_params[] = $thru_datetime;
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
						$output .= '<p class="description">' . __( 'Referrals', AFFILIATES_PLUGIN_DOMAIN ) . '</p>';
						$output .= '
							<table id="details-referrals-' . esc_attr( $result->date ) . '" class="details-referrals" cellspacing="0">
							<thead>
							<tr>
							<th scope="col" class="datetime">' . __( 'Time', AFFILIATES_PLUGIN_DOMAIN ) . '</th>
							<th scope="col" class="post-id">' . __( 'Post', AFFILIATES_PLUGIN_DOMAIN ) . '</th>
							<th scope="col" class="affiliate-id">' . __( 'Affiliate', AFFILIATES_PLUGIN_DOMAIN ) . '</th>
							</tr>
							</thead>
							<tbody>
							';
						foreach ( $referrals as $referral ) {
							$output .= '<tr class="details-referrals ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
							$output .= "<td class='datetime'>" . DateHelper::s2u( $referral->datetime ) . "</td>";
							$link = get_permalink( $referral->post_id );
							$title = get_the_title( $referral->post_id );
							$output .= '<td class="post-id"><a href="' . esc_attr( $link ) . '" target="_blank">' . stripslashes( wp_filter_nohtml_kses( $title ) ) . '</a></td>';
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
					$details_filters = " WHERE h.affiliate_id = %d ";
					$details_filter_params = array( $result->affiliate_id );
					if ( $from_date && $thru_date ) {
						$details_filters .= " AND datetime >= %s AND datetime < %s ";
						$details_filter_params[] = $from_datetime;
						$details_filter_params[] = $thru_datetime;
					} else if ( $from_date ) {
						$details_filters .= " AND datetime >= %s ";
						$details_filter_params[] = $from_datetime;
					} else if ( $thru_date ) {
						$details_filters .= " datetime < %s ";
						$details_filter_params[] = $thru_datetime;
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
					$output .= '<p class="description">' . __( 'Hits', AFFILIATES_PLUGIN_DOMAIN ) . '</p>';
					$output .= '
						<table id="details-hits-' . esc_attr( $result->date ) . '" class="details-hits" cellspacing="0">
						<thead>
						<tr>
						<th scope="col" class="date">' . __( 'Date', AFFILIATES_PLUGIN_DOMAIN ) . '</th>
						<th scope="col" class="time">' . __( 'Time', AFFILIATES_PLUGIN_DOMAIN ) . '</th>
						<th scope="col" class="ip">' . __( 'IP', AFFILIATES_PLUGIN_DOMAIN ) . '</th>
						<th scope="col" class="count">' . __( 'Count', AFFILIATES_PLUGIN_DOMAIN ) . '</th>
						<th scope="col" class="affiliate-id">' . __( 'Affiliate', AFFILIATES_PLUGIN_DOMAIN ) . '</th>
						</tr>
						</thead>
						<tbody>
						';
					foreach ( $hits as $hit ) {
						$output .= '<tr class="details ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
//						$output .= "<td class='date'>$hit->date</td>";
						$output .= '<td class="date">' . DateHelper::formatDate( DateHelper::s2u( $hit->datetime ) ) . '</td>';
//						$output .= "<td class='time'>$hit->time</td>";
						$output .= '<td class="time">' . DateHelper::formatTime( DateHelper::s2u( $hit->datetime ) ) . '</td>';
						$output .= "<td class='ip'>" . long2ip( $hit->ip ) . "</td>";
						$output .= "<td class='count'>$hit->count</td>";
						$output .= "<td class='affiliate-id'>" . stripslashes( wp_filter_nohtml_kses( $hit->name ) ) . "</td>";
						$output .= '</tr>';
					}
					$output .= '</tbody></table>';
					$output .= '</div>'; // .details-hits
					$output .= '</td></tr>';
				} // if $expanded_hits
			} // expanded
		}
	} else {
		$output .= '<tr><td colspan="5">' . __('There are no results.', AFFILIATES_PLUGIN_DOMAIN ) . '</td></tr>';
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
