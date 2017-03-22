<?php
/**
 * affiliates-admin-referrals.php
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

include_once( AFFILIATES_CORE_LIB . '/class-affiliates-date-helper.php');

/**
 * Referrals screen.
 */
function affiliates_admin_referrals() {

	global $wpdb, $affiliates_options;

	$output = '';

	if ( !current_user_can( AFFILIATES_ACCESS_AFFILIATES ) ) {
		wp_die( __( 'Access denied.', 'affiliates' ) );
	}

	// $_GET actions
	if ( isset( $_GET['action'] ) ) {
		switch ( $_GET['action'] ) {
			case 'edit' :
				require_once( AFFILIATES_CORE_LIB . '/affiliates-admin-referral-edit.php');
				if ( isset( $_GET['referral_id'] ) ) {
					return affiliates_admin_referral_edit( intval( $_GET['referral_id'] ) );
				} else {
					return affiliates_admin_referral_edit();
				}
				break;
			case 'remove' :
				if ( isset( $_GET['referral_id'] ) ) {
					require_once( AFFILIATES_CORE_LIB . '/affiliates-admin-referral-remove.php');
					return affiliates_admin_referral_remove( $_GET['referral_id'] );
				}
				break;
		}
	}

	if (
		isset( $_POST['from_date'] ) ||
		isset( $_POST['thru_date'] ) ||
		isset( $_POST['clear_filters'] ) ||
		isset( $_POST['affiliate_id'] ) ||
		isset( $_POST['status'] ) ||
		isset( $_POST['search'] ) ||
		isset( $_POST['expanded'] ) ||
		isset( $_POST['expanded_data'] ) ||
		isset( $_POST['expanded_description'] ) ||
		isset( $_POST['show_inoperative'] )
	) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_HITS_FILTER_NONCE], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	$affiliates_table = _affiliates_get_tablename( 'affiliates' );
	$referrals_table = _affiliates_get_tablename( 'referrals' );
	$hits_table = _affiliates_get_tablename( 'hits' );
	$posts_table = $wpdb->prefix . 'posts';

	// actions
// 	if ( isset( $_POST['affiliate_id'] ) && isset( $_POST['post_id'] ) && isset( $_POST['datetime'] ) && isset( $_POST['action'] ) ) {

// 		if ( isset( $_POST['status'] ) ) {
// 			$referral = $wpdb->get_row(
// 				$wpdb->prepare(
// 					"SELECT * FROM $referrals_table WHERE affiliate_id = %d AND post_id = %d AND datetime = %s",
// 					intval( $_POST['affiliate_id'] ),
// 					intval( $_POST['post_id'] ),
// 					$_POST['datetime']
// 				)
// 			);
// 			if ( $referral ) {
// 				if ( Affiliates_Utility::verify_referral_status_transition( $referral->status, $_POST['status'] ) ) {
// 					$wpdb->query(
// 						$wpdb->prepare(
// 							"UPDATE $referrals_table SET status = %s WHERE affiliate_id = %d AND post_id = %d AND datetime = %s AND status = %s",
// 							$_POST['status'],
// 							intval( $referral->affiliate_id ),
// 							intval( $referral->post_id ),
// 							$referral->datetime,
// 							$referral->status
// 						)
// 					);
// 				}
// 			}
// 		}
// 	}

	// filters
	$from_date            = $affiliates_options->get_option( 'referrals_from_date', null );
	$thru_date            = $affiliates_options->get_option( 'referrals_thru_date', null );
	$affiliate_id         = $affiliates_options->get_option( 'referrals_affiliate_id', null );
	$status               = $affiliates_options->get_option( 'referrals_status', null );
	$search               = $affiliates_options->get_option( 'referrals_search', null );
	$search_description   = $affiliates_options->get_option( 'referrals_search_description', null );
	$expanded             = $affiliates_options->get_option( 'referrals_expanded', null );
	$expanded_description = $affiliates_options->get_option( 'referrals_expanded_description', null );
	$expanded_data        = $affiliates_options->get_option( 'referrals_expanded_data', null );
	$expanded_items       = $affiliates_options->get_option( 'referrals_expanded_items', null );
	$show_inoperative     = $affiliates_options->get_option( 'referrals_show_inoperative', null );

	if ( !isset( $_POST['action'] ) && isset( $_POST['clear_filters'] ) ) {
		$affiliates_options->delete_option( 'referrals_from_date' );
		$affiliates_options->delete_option( 'referrals_thru_date' );
		$affiliates_options->delete_option( 'referrals_affiliate_id' );
		$affiliates_options->delete_option( 'referrals_status' );
		$affiliates_options->delete_option( 'referrals_search' );
		$affiliates_options->delete_option( 'referrals_expanded' );
		$affiliates_options->delete_option( 'referrals_expanded_description' );
		$affiliates_options->delete_option( 'referrals_expanded_data' );
		$affiliates_options->delete_option( 'referrals_expanded_items' );
		$affiliates_options->delete_option( 'referrals_show_inoperative' );
		$from_date = null;
		$thru_date = null;
		$affiliate_id = null;
		$status = null;
		$search = null;
		$search_description = null;
		$expanded = null;
		$expanded_data = null;
		$expanded_description = null;
		$expanded_items = null;
		$show_inoperative = null;
	} else if ( !isset( $_POST['action'] ) && isset( $_POST['submitted'] ) ) {

		// filter by date(s)
		if ( !empty( $_POST['from_date'] ) ) {
			$from_date = date( 'Y-m-d', strtotime( $_POST['from_date'] ) );
			$affiliates_options->update_option( 'referrals_from_date', $from_date );
		} else {
			$from_date = null;
			$affiliates_options->delete_option( 'referrals_from_date' );
		}
		if ( !empty( $_POST['thru_date'] ) ) {
			$thru_date = date( 'Y-m-d', strtotime( $_POST['thru_date'] ) );
			$affiliates_options->update_option( 'referrals_thru_date', $thru_date );
		} else {
			$thru_date = null;
			$affiliates_options->delete_option( 'referrals_thru_date' );
		}
		if ( $from_date && $thru_date ) {
			if ( strtotime( $from_date ) > strtotime( $thru_date ) ) {
				$thru_date = null;
				$affiliates_options->delete_option( 'referrals_thru_date' );
			}
		}

		// filter by affiliate id
		if ( !empty( $_POST['affiliate_id'] ) ) {
			$affiliate_id = affiliates_check_affiliate_id( $_POST['affiliate_id'] );
			if ( $affiliate_id ) {
				$affiliates_options->update_option( 'referrals_affiliate_id', $affiliate_id );
			}
		} else if ( isset( $_POST['affiliate_id'] ) ) { // empty && isset => '' => all
			$affiliate_id = null;
			$affiliates_options->delete_option( 'referrals_affiliate_id' );
		}

		if ( !empty( $_POST['status'] ) ) {
			if ( $status = Affiliates_Utility::verify_referral_status_transition( $_POST['status'], $_POST['status'] ) ) {
				$affiliates_options->update_option( 'referrals_status', $status );
			} else {
				$status = null;
				$affiliates_options->delete_option( 'referrals_status' );
			}
		} else {
			$status = null;
			$affiliates_options->delete_option( 'referrals_status' );
		}

		if ( !empty( $_POST['search'] ) ) {
			$search = $_POST['search'];
			$affiliates_options->update_option( 'referrals_search', $_POST['search'] );
		} else {
			$search = null;
			$affiliates_options->delete_option( 'referrals_search' );
		}
		if ( !empty( $_POST['search_description'] ) ) {
			$search_description = true;
			$affiliates_options->update_option( 'referrals_search_description', true );
		} else {
			$search_description = false;
			$affiliates_options->delete_option( 'referrals_search_description' );
		}

		// expanded details?
		if ( !empty( $_POST['expanded'] ) ) {
			$expanded = true;
			$affiliates_options->update_option( 'referrals_expanded', true );
		} else {
			$expanded = false;
			$affiliates_options->delete_option( 'referrals_expanded' );
		}
		if ( !empty( $_POST['expanded_data'] ) ) {
			$expanded_data = true;
			$affiliates_options->update_option( 'referrals_expanded_data', true );
		} else {
			$expanded_data = false;
			$affiliates_options->delete_option( 'referrals_expanded_data' );
		}
		if ( !empty( $_POST['expanded_description'] ) ) {
			$expanded_description = true;
			$affiliates_options->update_option( 'referrals_expanded_description', true );
		} else {
			$expanded_description = false;
			$affiliates_options->delete_option( 'referrals_expanded_description' );
		}
		if ( !empty( $_POST['expanded_items'] ) ) {
			$expanded_items = true;
			$affiliates_options->update_option( 'referrals_expanded_items', true );
		} else {
			$expanded_items = false;
			$affiliates_options->delete_option( 'referrals_expanded_items' );
		}
		if ( !empty( $_POST['show_inoperative'] ) ) {
			$show_inoperative = true;
			$affiliates_options->update_option( 'referrals_show_inoperative', true );
		} else {
			$show_inoperative = false;
			$affiliates_options->delete_option( 'referrals_show_inoperative' );
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

	$output .=
		'<div>' .
		'<h1>' .
		__( 'Referrals', 'affiliates' ) .
		'</h1>' .
		'</div>';

	$output .= '<div class="manage add">';
	$output .= sprintf(
		'<a title="%s" class="add button" href="%s"><img class="icon" alt="%s" src="%s" /><span class="label">%s</span></a>',
		__( 'Click to add a referral manually', 'affiliates' ),
		esc_url( add_query_arg( 'action', 'edit', $current_url ) ),
		__( 'Add', 'affiliates' ),
		AFFILIATES_PLUGIN_URL .'images/add.png',
		__( 'Add', 'affiliates')
	);
	$output .= '<div style="float:right">';
	$output .= apply_filters( 'affiliates_admin_referrals_secondary_actions', '' );
	$output .= '</div>'; // floating right
	$output .= '</div>';

	$row_count = isset( $_POST['row_count'] ) ? intval( $_POST['row_count'] ) : 0;

	if ($row_count <= 0) {
		$row_count = $affiliates_options->get_option( 'referrals_per_page', AFFILIATES_HITS_PER_PAGE );
	} else {
		$affiliates_options->update_option('referrals_per_page', $row_count );
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
		case 'datetime' :
		case 'name' :
		case 'post_title' :
		case 'amount' :
		case 'currency_id' :
		case 'status' :
			break;
		default :
			$orderby = 'datetime';
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
	// We have the desired dates from the user's point of view, i.e. in her timezone.
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
		$filters .= " AND r.affiliate_id = %d ";
		$filter_params[] = $affiliate_id;
	}
	if ( $status ) {
		$filters .= " AND r.status = %s ";
		$filter_params[] = $status;
	}
	if ( $search ) {
		if ( $search_description ) {
			$filters .= " AND ( r.data LIKE '%%%s%%' OR r.description LIKE '%%%s%%' ) ";
			$filter_params[] = $search;
			$filter_params[] = $search;
		} else {
			$filters .= " AND r.data LIKE '%%%s%%' ";
			$filter_params[] = $search;
		}
	}

	// how many are there ?
	$count_query = $wpdb->prepare(
		"SELECT count(*) FROM $referrals_table r
		$filters
		",
		$filter_params
	);
	$count = $wpdb->get_var( $count_query );

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
		SELECT r.*, a.affiliate_id, a.name 
		FROM $referrals_table r
		LEFT JOIN $affiliates_table a ON r.affiliate_id = a.affiliate_id
		LEFT JOIN $posts_table p ON r.post_id = p.ID
		$filters
		ORDER BY $orderby $order
		LIMIT $row_count OFFSET $offset
		",
		$filter_params + $filter_params
	);

	$results = $wpdb->get_results( $query, OBJECT );

	$column_display_names = array(
		'datetime'    => __( 'Date', 'affiliates' ),
		'post_title'  => __( 'Post', 'affiliates' ),
		'name'        => __( 'Affiliate', 'affiliates' ),
		'amount'      => __( 'Amount', 'affiliates' ),
		'currency_id' => __( 'Currency', 'affiliates' ),
		'status'      => __( 'Status', 'affiliates' ),
		'edit'        => '',
		'remove'      => '',
	);

	$column_count = count( $column_display_names );

	$output .= '<div id="referrals-overview" class="referrals-overview">';

	$affiliates = affiliates_get_affiliates( true, !$show_inoperative );
	$affiliates_select = '';
	if ( !empty( $affiliates ) ) {
		$affiliates_select .= '<label class="affiliate-id-filter"">';
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

	$status_select = '<label class="status-filter">';
	$status_select .= __( 'Status', 'affiliates' );
	$status_select .= ' ';
	$status_select .= '<select name="status">';
	$status_select .= '<option value="" ' . ( empty( $status ) ? ' selected="selected" ' : '' ) . '>--</option>';
	foreach ( $status_descriptions as $key => $label ) {
		$selected = $key == $status ? ' selected="selected" ' : ''; 
		$status_select .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . $label . '</option>';
	}
	$status_select .= '</select>';
	$status_select .= '</label>';

	$output .=
		'<div class="filters">' .
			'<label class="description" for="setfilters">' . __( 'Filters', 'affiliates' ) . '</label>' .
			'<form id="setfilters" action="" method="post">' .

				'<div class="filter-section">' .
				$affiliates_select .
				' ' .
				$status_select .
				' ' .
				' <label class="search-filter" title="Search in data">' .
				__( 'Search', 'affiliates' ) .
				' ' .
				' <input class="search-filter" name="search" type="text" value="' . esc_attr( $search ) . '"/>' .
				'</label>' .
				' ' .
				sprintf( '<label class="search-description-filter" title="%s">', __( 'Also search in descriptions', 'affiliates' ) ) .
				'<input class="search-description-filter" name="search_description" type="checkbox" ' . ( $search_description ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Descriptions', 'affiliates' ) .
				'</label>' .
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
				'<input class="expanded-filter" name="expanded" type="checkbox" ' . ( $expanded ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Expand details', 'affiliates' ) .
				'</label>' .
				' ' .
				'<label class="expanded-filter">' .
				'<input class="expanded-filter" name="expanded_description" type="checkbox" ' . ( $expanded_description ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Expand descriptions', 'affiliates' ) .
				'</label>' .
				' ' .
				'<label class="expanded-filter">' .
				'<input class="expanded-filter" name="expanded_data" type="checkbox" ' . ( $expanded_data ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Expand data', 'affiliates' ) .
				'</label>' .
				' ' .
				'<label class="expanded-filter">' .
				'<input class="expanded-filter" name="expanded_items" type="checkbox" ' . ( $expanded_items ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Expand items', 'affiliates' ) .
				'</label>' .
				' ' .
				'<label class="show-inoperative-filter">' .
				'<input class="show-inoperative-filter" name="show_inoperative" type="checkbox" ' . ( $show_inoperative ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Include inoperative affiliates', 'affiliates' ) .
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
		<table id="referrals" class="referrals wp-list-table widefat fixed" cellspacing="0">
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

			$output .= '<tr class="details-referrals ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
			$output .= '<td class="datetime">' . DateHelper::s2u( $result->datetime ) . '</td>';
			$link = get_permalink( $result->post_id );
			$title = get_the_title( $result->post_id );
			$output .= '<td class="post_title"><a href="' . esc_attr( $link ) . '" target="_blank">' . wp_filter_nohtml_kses( $title ) . '</a></td>';
			$output .= "<td class='name'>" . stripslashes( wp_filter_nohtml_kses( $result->name ) ) . "</td>";
			$output .= "<td class='amount'>" . stripslashes( wp_filter_nohtml_kses( $result->amount ) ) . "</td>";
			$output .= "<td class='currency_id'>" . stripslashes( wp_filter_nohtml_kses( $result->currency_id ) ) . "</td>";

			$output .= "<td class='status'>";
			$output .= isset( $status_icons[$result->status] ) ? $status_icons[$result->status] : '';
			$output .= ' ';
			$output .= isset( $status_descriptions[$result->status] ) ? $status_descriptions[$result->status] : '';
// 			$output .= "<form method='post' action=''>";
// 			$output .= "<div>";
// 			$output .= "<select name='status'>";
// 			foreach ( $status_descriptions as $status_key => $status_value ) {
// 				if ( $status_key == $result->status ) {
// 					$selected = "selected='selected'";
// 				} else {
// 					$selected = "";
// 				}
// 				$output .= "<option value='$status_key' $selected>$status_value</option>";
// 			}
// 			$output .= "</select>";
// 			$output .= '<input class="button" type="submit" value="' . __( 'Set', 'affiliates' ) . '"/>';
// 			$output .= '<input name="affiliate_id" type="hidden" value="' . esc_attr( $result->affiliate_id ) . '"/>';
// 			$output .= '<input name="post_id" type="hidden" value="' . esc_attr( $result->post_id ) . '"/>';
// 			$output .= '<input name="datetime" type="hidden" value="' . esc_attr( $result->datetime ) . '"/>';
// 			$output .= '<input name="action" type="hidden" value="set_status"/>';
// 			$output .= wp_nonce_field( 'admin', AFFILIATES_ADMIN_HITS_FILTER_NONCE, true, false );
// 			$output .= "</div>";
// 			$output .= "</form>";
			$output .= "</td>";

			$output .= '<td class="edit">';
			$edit_url = add_query_arg( 'referral_id', $result->referral_id, add_query_arg( 'action', 'edit', $current_url ) );
			$output .= sprintf( '<a href="%s">', esc_url( add_query_arg( 'paged', $paged, $edit_url ) ) );
			$output .= sprintf( '<img src="%s" alt="%s"/>', AFFILIATES_PLUGIN_URL . 'images/edit.png', __( 'Edit', 'affiliates' ) );
			$output .= '</a>';
			$output .= '</td>';

			$output .= '<td class="remove">';
			$remove_url = add_query_arg( 'referral_id', $result->referral_id, add_query_arg( 'action', 'remove', $current_url ) );
			$output .= sprintf( '<a href="%s">', esc_url( add_query_arg( 'paged', $paged, $remove_url ) ) );
			$output .= sprintf( '<img src="%s" alt="%s"/>', AFFILIATES_PLUGIN_URL . 'images/remove.png', __( 'Remove', 'affiliates' ) );
			$output .= '</a>';
			$output .= '</td>';

			$output .= '</tr>';

			$data = $result->data;
			if ( !empty( $data )  && $expanded ) {
				if ( $expanded_data ) {
					$data_view_style = '';
					$expander = AFFILIATES_EXPANDER_RETRACT;
				} else {
					$data_view_style = ' style="display:none;" ';
					$expander = AFFILIATES_EXPANDER_EXPAND;
				}
				$data = unserialize( $data );
				if ( $data ) {
					$output .= '<tr class="data ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
					$output .= "<td colspan='$column_count'>";
					$output .= '<div class="view-toggle">';
					$output .= "<div class='expander'>$expander</div>";
					$output .= '<div class="view-toggle-label">' . __( 'Data', 'affiliates' ) . '</div>';
					$output .= "<div class='view' $data_view_style>";
					$output .= '<table class="referral-data wp-list-table widefat fixed" cellspacing="0">';
					if ( is_array( $data ) ) {
						foreach ( $data as $key => $info ) {
							$title = __( $info['title'], $info['domain'] );
							$value = $info['value'];
							$output .= "<tr id='referral-data-$i'>";
							$output .= '<td class="referral-data-title">';
							$output .= stripslashes( wp_filter_nohtml_kses( $title ) );
							$output .= '</td>';
							$output .= '<td class="referral-data-value">';
							$output .= stripslashes( wp_filter_kses( $value ) );
							$output .= '</td>';
							$output .= '</tr>';
						}
					} else {
						$output .= "<tr id='referral-data-$i'>";
						$output .= '<td class="referral-data-title">';
						$output .= __( 'Data', 'affiliates' );
						$output .= '</td>';
						$output .= '<td class="referral-data-value">';
						if ( is_string( $data ) ) {
							$output .= stripslashes( wp_filter_kses( $data ) );
						}
						$output .= '</td>';
						$output .= '</tr>';
					}
					$output .= '</table>';
					$output .= '</div>'; // .view
					$output .= '</div>'; // .view-toggle
					$output .= '</td>';
					$output .= '</tr>';
				}
			}

			if ( $expanded && class_exists( 'Affiliates_Referral_WordPress' ) ) {
				if ( $expanded_items ) {
					$items_view_style = '';
					$expander = AFFILIATES_EXPANDER_RETRACT;
				} else {
					$items_view_style = ' style="display:none;" ';
					$expander = AFFILIATES_EXPANDER_EXPAND;
				}
				$referral = new Affiliates_Referral_WordPress();
				$referral->read( $result->referral_id );
				$items = $referral->referral_items;
				if ( $items ) {
					$output .= '<tr class="items ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
					$output .= "<td colspan='$column_count'>";
					$output .= '<div class="view-toggle">';
					$output .= "<div class='expander'>$expander</div>";
					$output .= '<div class="view-toggle-label">' . __( 'Items', 'affiliates' ) . '</div>';
					$output .= "<div class='view' $items_view_style>";
					$output .= '<table class="referral-items wp-list-table widefat fixed" cellspacing="0">';
					$output .= '<thead>';
					$output .= '<tr>';
					$output .= '<th scope="col" class="reference">' . __( 'Reference', 'affiliates' ) . '</th>';
					$output .= '<th scope="col" class="amount">' . __( 'Amount', 'affiliates' ) . '</th>';
					$output .= '<th scope="col" class="currency_id">' . __( 'Currency', 'affiliates' ) . '</th>';
					$output .= '<th scope="col" class="rate_id">' . __( 'Rate ID', 'affiliates' ) . '</th>';
					$output .= '</tr>';
					$output .= '</thead>';
					$output .= '<tbody>';
					if ( is_array( $items ) ) {
						foreach ( $items as $item ) {
							$item_reference = $item->reference;
							$item_amount = $item->amount;
							$item_currency_id = $item->currency_id;
							$item_rate_id = $item->rate_id;

							$output .= "<tr id='referral-item-$i'>";
							$output .= '<td class="referral-item-reference">';
							$output .= stripslashes( wp_filter_nohtml_kses( $item_reference ) );
							$output .= '</td>';
							$output .= '<td class="referral-item-amount">';
							$output .= stripslashes( wp_filter_nohtml_kses( $item_amount ) );
							$output .= '</td>';
							$output .= '<td class="referral-item-currency">';
							$output .= stripslashes( wp_filter_kses( $item_currency_id ) );
							$output .= '</td>';
							$output .= '<td class="referral-item-rate_id">';
							$output .= intval( $item_rate_id );
							$output .= '</td>';
							$output .= '</tr>';
						}
					}
					$output .= '</tbody>';
					$output .= '</table>';
					$output .= '</div>'; // .view
					$output .= '</div>'; // .view-toggle
					$output .= '</td>';
					$output .= '</tr>';
				}
			}

			if ( !empty( $result->description ) && $expanded ) {
				if ( $expanded_description ) {
					$description_view_style = '';
					$expander = AFFILIATES_EXPANDER_RETRACT;
				} else {
					$description_view_style = ' style="display:none;" ';
					$expander = AFFILIATES_EXPANDER_EXPAND;
				}
				$output .= sprintf( "<tr id='referral-description-%d' class='%s'>", $i, $i % 2 == 0 ? 'even' : 'odd' ) .
					'<td colspan="' . $column_count . '">' .
						'<div class="view-toggle">' .
							"<div class='expander'>$expander</div>" .
							'<div class="view-toggle-label">' . __('Description', 'affiliates' ) . '</div>' .
							"<div class='view' $description_view_style>" .
								wp_filter_kses( addslashes( $result->description ) ) .
							'</div>' .
						'</div>' .
					'</td>' .
				'</tr>';
			}
		}
	} else {
		$output .= '<tr><td colspan="' . $column_count . '">' . __('There are no results.', 'affiliates' ) . '</td></tr>';
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

	$output .= '</div>'; // .referrals-overview
	echo $output;
	affiliates_footer();
} // function affiliates_admin_referrals()
