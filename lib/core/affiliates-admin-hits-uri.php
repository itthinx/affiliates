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

/**
 * Render the traffic table & filter.
 */
function affiliates_admin_hits_uri() {
	global $wpdb, $affiliates_options;

	require_once AFFILIATES_CORE_LIB . '/class-affiliates-date-helper.php';

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
		isset( $_POST['dest_uri'] ) ||
		isset( $_POST['user_agent'] ) ||
		isset( $_POST['status'] ) ||
		isset( $_POST['min_referrals'] )
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
	$user_agent         = $affiliates_options->get_option( 'hits_uri_user_agent', null );
	$status             = $affiliates_options->get_option( 'hits_uri_status', null );
	$min_referrals      = $affiliates_options->get_option( 'hits_uri_min_referrals', null );

	if ( isset( $_POST['clear_filters'] ) ) {
		$affiliates_options->delete_option( 'hits_uri_from_date' );
		$affiliates_options->delete_option( 'hits_uri_thru_date' );
		$affiliates_options->delete_option( 'hits_uri_affiliate_id' );
		$affiliates_options->delete_option( 'hits_uri_src_uri' );
		$affiliates_options->delete_option( 'hits_uri_dest_uri' );
		$affiliates_options->delete_option( 'hits_uri_user_agent' );
		$affiliates_options->delete_option( 'hits_uri_status' );
		$affiliates_options->delete_option( 'hits_uri_min_referrals' );
		$from_date = null;
		$thru_date = null;
		$affiliate_id = null;
		$src_uri = null;
		$dest_uri = null;
		$user_agent = null;
		$status = null;
		$min_referrals = null;
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

		// user_agent
		$_POST['user_agent'] = trim( $_POST['user_agent'] );
		if ( !empty( $_POST['user_agent'] ) ) {
			$user_agent = $_POST['user_agent'];
			$affiliates_options->update_option( 'hits_uri_user_agent', $user_agent );
		} else if ( isset( $_POST['user_agent'] ) )  { // empty
			$user_agent = null;
			$affiliates_options->delete_option( 'hits_uri_user_agent' );
		}

		// referrals status
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
					$affiliates_options->update_option( 'hits_uri_status', $stati );
				} else {
					$status = null;
					$affiliates_options->delete_option( 'hits_uri_status' );
				}
			}
		} else {
			$status = null;
			$affiliates_options->delete_option( 'hits_uri_status' );
		}

		// minimum number of referrals
		if ( !empty( $_POST['min_referrals'] ) ) {
			$min_referrals = max( 0, intval( $_POST['min_referrals'] ) );
		} else if ( isset( $_POST['min_referrals'] ) ) { // empty && isset => '' => all
			$min_referrals = null;
			$affiliates_options->delete_option( 'hits_uri_min_referrals' );
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

	$campaigns = class_exists( 'Affiliates_Campaign' ) && method_exists( 'Affiliates_Campaign', 'is_affiliate_campaign' );

	$affiliates_table  = _affiliates_get_tablename( 'affiliates' );
	$referrals_table   = _affiliates_get_tablename( 'referrals' );
	$hits_table        = _affiliates_get_tablename( 'hits' );
	$uris_table        = _affiliates_get_tablename( 'uris' );
	$user_agents_table = _affiliates_get_tablename( 'user_agents');
	$campaigns_table   = _affiliates_get_tablename( 'campaigns' );

	$output .= '<h1>';
	$output .= __( 'Traffic', 'affiliates' );
	$output .= '</h1>';

	$row_count = isset( $_POST['row_count'] ) ? intval( $_POST['row_count'] ) : 0;

	if ($row_count <= 0) {
		$row_count = $affiliates_options->get_option( 'affiliates_hits_uri_per_page', AFFILIATES_HITS_PER_PAGE );
	} else {
		$affiliates_options->update_option('affiliates_hits_uri_per_page', $row_count );
	}
	// current page
	$paged = isset( $_REQUEST['uris_paged'] ) ? intval( $_REQUEST['uris_paged'] ) : 1;
	if ( $paged < 1 ) {
		$paged = 1;
	}

	$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : null;
	switch ( $orderby ) {
		case 'date' :
		case 'name' :
		case 'ip' :
		case 'src_uri' :
		case 'dest_uri' :
		case 'user_agent' :
		case 'campaign' :
			break;
		case 'referrals' :
			if ( $min_referrals < 1 ) {
				$min_referrals = 1;
			}
			break;
		case 'affiliate_id' :
			$orderby = 'name';
			break;
		case 'campaign_id' :
			$orderby = 'campaign';
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
		$filters .= " AND h.date >= %s AND h.date <= %s ";
		$filter_params[] = $u2s_from_date;
		$filter_params[] = $u2s_thru_date;
	} else if ( $u2s_from_date ) {
		$filters .= " AND h.date >= %s ";
		$filter_params[] = $u2s_from_date;
	} else if ( $u2s_thru_date ) {
		$filters .= " AND h.date <= %s ";
		$filter_params[] = $u2s_thru_date;
	}

	if ( $affiliate_id ) {
		$filters .= " AND h.affiliate_id = %d ";
		$filter_params[] = $affiliate_id;
	}

	// Source URI
	if ( $src_uri ) {
		$strings = preg_split( '/\s+(AND|OR)\s+/', $src_uri, null, PREG_SPLIT_DELIM_CAPTURE );
		if ( is_array( $strings ) && count( $strings ) > 0 ) {
			$filters .= ' AND ( ';
			foreach ( $strings as $string ) {
				switch ( $string ) {
					case 'AND' :
					case 'OR' :
						$filters .= " $string ";
						break;
					default :
						$filters .= " su.uri LIKE '%%%s%%' ";
						$filter_params[] = $wpdb->esc_like( $string );
				}
			}
			$filters .= ' ) ';
		}
	}

	// Desintation URI
	if ( $dest_uri ) {
		$strings = preg_split( '/\s+(AND|OR)\s+/', $dest_uri, null, PREG_SPLIT_DELIM_CAPTURE );
		if ( is_array( $strings ) && count( $strings ) > 0 ) {
			$filters .= ' AND ( ';
			foreach ( $strings as $string ) {
				switch ( $string ) {
					case 'AND' :
					case 'OR' :
						$filters .= " $string ";
						break;
					default :
						$filters .= " du.uri LIKE '%%%s%%' ";
						$filter_params[] = $wpdb->esc_like( $string );
				}
			}
			$filters .= ' ) ';
		}
	}

	// User Agent
	if ( $user_agent ) {
		$strings = preg_split( '/\s+(AND|OR)\s+/', $user_agent, null, PREG_SPLIT_DELIM_CAPTURE );
		if ( is_array( $strings ) && count( $strings ) > 0 ) {
			$filters .= ' AND ( ';
			foreach ( $strings as $string ) {
				switch ( $string ) {
					case 'AND' :
					case 'OR' :
						$filters .= " $string ";
						break;
					default :
						$filters .= " ua.user_agent LIKE '%%%s%%' ";
						$filter_params[] = $wpdb->esc_like( $string );
				}
			}
			$filters .= ' ) ';
		}
	}

	// minimum number of related referrals, if orderby is 'referrals' then a minimum of 1 is enforced
	if ( $min_referrals ) {
		$filters .= " AND referrals.count >= %d ";
		$filter_params[] = intval( $min_referrals );
	}

	$status_condition = '';
	if ( is_array( $status ) && count( $status ) > 0 && count( $status ) < 4 ) { // 4 any referral status
		$status_condition = " WHERE status IN ('" . implode( "','", $status ) . "') ";
		//$filters .= $status_condition;
	}

	do {
		$repeat = false;
		$offset = ( $paged - 1 ) * $row_count;

		$query = $wpdb->prepare(
			"SELECT " .
			// "SQL_CALC_FOUND_ROWS" . // degrades the performance of this query substantially => using COUNT(*) instead
			"h.date, " .
			"h.datetime, " .
			"h.hit_id, " .
			"h.campaign_id, " .
			( $campaigns ? "c.name AS campaign, " : '' ) .
			"h.ip, " .
			// "h.ipv6, " .
			"h.affiliate_id, " .
			"a.name, " .
			"h.src_uri_id, " .
			"su.uri src_uri, " .
			"h.dest_uri_id, " .
			"du.uri dest_uri, " .
			"h.user_agent_id, " .
			"ua.user_agent, " .
			"referrals.count AS referrals " .
			"FROM $hits_table h " .
			"LEFT JOIN $affiliates_table a ON h.affiliate_id = a.affiliate_id " .
			"LEFT JOIN $uris_table su ON h.src_uri_id = su.uri_id " .
			"LEFT JOIN $uris_table du ON h.dest_uri_id = du.uri_id " .
			"LEFT JOIN $user_agents_table ua ON h.user_agent_id = ua.user_agent_id " .
			"LEFT JOIN (SELECT COUNT(*) AS count, hit_id FROM $referrals_table $status_condition GROUP BY hit_id) AS referrals ON referrals.hit_id = h.hit_id " .
			( $campaigns ? "LEFT JOIN $campaigns_table c ON h.campaign_id = c.campaign_id " : '' ) .
			"$filters " .
			"ORDER BY $orderby $order " .
			"LIMIT $row_count OFFSET $offset",
			$filter_params
		);

		$results = $wpdb->get_results( $query, OBJECT );

		$count = intval( $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $hits_table h " .
			"LEFT JOIN $affiliates_table a ON h.affiliate_id = a.affiliate_id " .
			"LEFT JOIN $uris_table su ON h.src_uri_id = su.uri_id " .
			"LEFT JOIN $uris_table du ON h.dest_uri_id = du.uri_id " .
			"LEFT JOIN $user_agents_table ua ON h.user_agent_id = ua.user_agent_id " .
			"LEFT JOIN (SELECT COUNT(*) AS count, hit_id FROM $referrals_table GROUP BY hit_id) AS referrals ON referrals.hit_id = h.hit_id " .
			( $campaigns ? "LEFT JOIN $campaigns_table c ON h.campaign_id = c.campaign_id " : '' ) .
			"$filters ",
			$filter_params
		) ) );
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
		'date'       => __( 'Date', 'affiliates' ) . '*',
		'name'       => __( 'Affiliate', 'affiliates' ),
		'ip'         => __( 'IP', 'affiliates' ),
		'referrals'  => __( 'Referrals', 'affiliates' ),
		'src_uri'    => __( 'Source URI', 'affiliates' ),
		'dest_uri'   => __( 'Landing URI', 'affiliates' ),
		'user_agent' => __( 'User Agent', 'affiliates' )
	);
	if ( $campaigns ) {
		$column_display_names['campaign'] = __( 'Campaign', 'affiliates' );
	}

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

	$use_and_or = sprintf( __( 'You can use %s and %s to search for multiple terms in combination.', 'affiliates' ), 'AND', 'OR' );

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
					sprintf( '<label style="cursor:help" title="%s" class="src-uri-filter">', esc_html( $use_and_or ) ) .
					__( 'Source URI', 'affiliates' ) .
					' ' .
					'<input class="src-uri-filter" name="src_uri" type="text" value="' . esc_attr( stripslashes( $src_uri ) ) . '"/>' .
					'</label>' .
					' ' .
					sprintf( '<label style="cursor:help" title="%s" class="dest-uri-filter">', esc_html( $use_and_or ) ) .
					__( 'Landing URI', 'affiliates' ) .
					' ' .
					'<input class="dest-uri-filter" name="dest_uri" type="text" value="' . esc_attr( stripslashes( $dest_uri ) ) . '"/>' .
					'</label>' .
				'</div>' .
				'<div class="filter-section">' .
					sprintf( '<label style="cursor:help" title="%s" class="user-agent-filter">', esc_html( $use_and_or ) ) .
					__( 'User Agent', 'affiliates' ) .
					' ' .
					'<input class="user-agent-filter" name="user_agent" type="text" value="' . esc_attr( stripslashes( $user_agent ) ) . '"/>' .
					'</label>' .
				'</div>' .
				'<div class="filter-section">' .
				'<span style="padding-right:1em">' . __( 'Referral Status', 'affiliates' ) . '</span>' .
				' ' .
				$status_checkboxes .
				' ' .
				'<label class="min-referrals-filter">' .
				__( 'Minimum', 'affiliates' ) .
				' ' .
				sprintf( '<input class="min-referrals-filter" title="%s" name="min_referrals" type="number" value="%d" min="0"/>', esc_attr( __( 'Minimum number of referrals', 'affiliates' ) ), esc_attr( $min_referrals ) ) .
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
		$class = '';
		if ( strcmp( $key, $orderby ) == 0 ) {
			$lorder = strtolower( $order );
			$class = "$key manage-column sorted $lorder";
		} else {
			$class = "$key manage-column";
			if ( $key !== '' ) { // empty because we have no unsortable column right now *
				$class .= ' sortable';
			}
		}
		if ( $key !== '' ) { // * see above
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

			$output .= '<tr class=" ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
			$output .= "<td class='date'>$result->date</td>";
			$affiliate = affiliates_get_affiliate( $result->affiliate_id );
			$output .= "<td class='affiliate-name'>" . stripslashes( wp_filter_nohtml_kses( $affiliate['name'] ) ) . "</td>";
			$output .= sprintf( '<td class="ip">%s</td>', esc_html( long2ip( sprintf( "%d", $result->ip ) ) ) );
			$output .= "<td class='referrals'>$result->referrals</td>";
			$output .= sprintf( "<td class='src-uri'>%s</td>", esc_html( $result->src_uri ) ); // stored with esc_url_raw(), shown with esc_html()
			$output .= sprintf( "<td class='dest-uri'>%s</td>", esc_html( $result->dest_uri ) ); // stored with esc_url_raw(), shown with esc_html()
			$output .= sprintf( "<td class='user-agent'>%s</td>", esc_html( $result->user_agent ) );
			if ( $campaigns ) {
				if ( !empty( $result->campaign_id ) ) {
					if ( $campaign = Affiliates_Campaign::get_affiliate_campaign( $result->affiliate_id, $result->campaign_id ) ) {
						$output .= printf(
							'<td class="campaign">%s [%d]</td>',
							esc_html( $campaign->name ),
							intval( $result->campaign_id )
						);
					} else {
						$output .= '<td class="campaign">&mdash; ? &mdash;</td>';
					}
				} else {
					$output .= '<td class="campaign"></td>';
				}
			}
			$output .= '</tr>';
		}
	} else {
		$output .= '<tr><td colspan="5">';
		$output .= __( 'There are no results.', 'affiliates' );
		$output .= '</td></tr>';
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
	$output .= '<p>';
	$output .= sprintf(
		__( "* Date is given for the server's time zone : %s, which has an offset of %s hours with respect to GMT.", 'affiliates' ),
		$server_dtz->getName(),
		$server_dtz->getOffset( new DateTime() ) / 3600.0
	);
	$output .= '</p>';
	$output .= '</div>'; // .hits-uris-overview

	echo $output;
	affiliates_footer();
} // function affiliates_admin_hits_uri()
