<?php
/**
 * affiliates-admin-affiliates.php
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

define( 'AFFILIATES_AFFILIATES_PER_PAGE', 10 );

define( 'AFFILIATES_ADMIN_AFFILIATES_NONCE_1', 'affiliates-nonce-1');
define( 'AFFILIATES_ADMIN_AFFILIATES_NONCE_2', 'affiliates-nonce-2');
define( 'AFFILIATES_ADMIN_AFFILIATES_FILTER_NONCE', 'affiliates-filter-nonce' );
define( 'AFFILIATES_ADMIN_AFFILIATES_ACTION_NONCE', 'affiliates-action-nonce' );

require_once( AFFILIATES_CORE_LIB . '/class-affiliates-date-helper.php');
require_once( AFFILIATES_CORE_LIB . '/affiliates-admin-affiliates-add.php');
require_once( AFFILIATES_CORE_LIB . '/affiliates-admin-affiliates-edit.php');
require_once( AFFILIATES_CORE_LIB . '/affiliates-admin-affiliates-remove.php');

/**
 * Affiliate table and action handling.
 */
function affiliates_admin_affiliates() {

	global $wpdb, $wp_rewrite, $affiliates_options;

	$output = '';
	$today = date( 'Y-m-d', time() );

	$pname = get_option( 'aff_pname', AFFILIATES_PNAME );

	if ( !current_user_can( AFFILIATES_ADMINISTER_AFFILIATES ) ) {
		wp_die( __( 'Access denied.', 'affiliates' ) );
	}

	// @deprecated
// 	if ( !$wp_rewrite->using_permalinks() ) {
// 		$output .= '<p class="warning">' .
// 			'* ' .
// 			sprintf( __( 'Your site is not using pretty <a href="%s">permalinks</a>. You will only be able to use URL parameter based <span class="affiliate-link">affiliate links</span> but not pretty <span class="affiliate-permalink">affiliate permalinks</span>, unless you change your permalink settings.', 'affiliates' ), esc_url( get_admin_url( null, 'options-permalink.php') ) ) .
// 			'</p>';
// 	}

	//
	// handle actions
	//
	if ( isset( $_POST['action'] ) ) {
		//  handle action submit - do it
		switch( $_POST['action'] ) {
			case 'add' :
				if ( !affiliates_admin_affiliates_add_submit() ) {
					return affiliates_admin_affiliates_add();
				}
				break;
			case 'edit' :
				if ( !affiliates_admin_affiliates_edit_submit() ) {
					return affiliates_admin_affiliates_edit( $_POST['affiliate-id-field'] );
				}
				break;
			case 'remove' :
				affiliates_admin_affiliates_remove_submit();
				break;
			// bulk actions on affiliates: remove affiliates
			case 'affiliate-action' :
				if ( wp_verify_nonce( $_POST[AFFILIATES_ADMIN_AFFILIATES_ACTION_NONCE], 'admin' ) ) {
					$affiliate_ids = isset( $_POST['affiliate_ids'] ) ? $_POST['affiliate_ids'] : null;
					$bulk_action = null;
					if ( isset( $_POST['bulk'] ) ) {
						$bulk_action = $_POST['bulk-action'];
					}
					if ( is_array( $affiliate_ids ) && ( $bulk_action !== null ) ) {
						foreach ( $affiliate_ids as $affiliate_id ) {
							switch ( $bulk_action ) {
								case 'remove-affiliate' :
									$bulk_confirm = isset( $_POST['confirm'] ) ? true : false;
									if ( $bulk_confirm ) {
										affiliates_admin_affiliates_bulk_remove_submit();
									} else {
										return affiliates_admin_affiliates_bulk_remove();
									}
									break;
								case 'status-active' :
									affiliates_admin_affiliates_bulk_status_active_submit();
									break;
								case 'status-pending' :
									affiliates_admin_affiliates_bulk_status_pending_submit();
									break;
								default :
									break;
							}
						}
					}
				}
				break;
		}
	} else if ( isset ( $_GET['action'] ) ) {
		// handle action request - show form
		switch( $_GET['action'] ) {
			case 'add' :
				return affiliates_admin_affiliates_add();
				break;
			case 'edit' :
				if ( isset( $_GET['affiliate_id'] ) ) {
					return affiliates_admin_affiliates_edit( $_GET['affiliate_id'] );
				}
				break;
			case 'remove' :
				if ( isset( $_GET['affiliate_id'] ) ) {
					return affiliates_admin_affiliates_remove( $_GET['affiliate_id'] );
				}
				break;
		}
	}

	//
	// affiliate table
	//
	if ( isset( $_POST['clear_filters'] ) || isset( $_POST['submitted'] ) ) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_AFFILIATES_FILTER_NONCE], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	// filters
	$from_date            = $affiliates_options->get_option( 'affiliates_from_date', null );
	$from_datetime        = null;
	$thru_date            = $affiliates_options->get_option( 'affiliates_thru_date', null );
	$thru_datetime        = null;
	$affiliate_id         = $affiliates_options->get_option( 'affiliates_affiliate_id', null );
	$affiliate_name       = $affiliates_options->get_option( 'affiliates_affiliate_name', null );
	$affiliate_email      = $affiliates_options->get_option( 'affiliates_affiliate_email', null );
	$affiliate_user_login = $affiliates_options->get_option( 'affiliates_affiliate_user_login', null );
	$show_active          = $affiliates_options->get_option( 'affiliates_show_active', true );
	$show_pending         = $affiliates_options->get_option( 'affiliates_show_pending', true );
	$show_deleted         = $affiliates_options->get_option( 'affiliates_show_deleted', false );
	$show_inoperative     = $affiliates_options->get_option( 'affiliates_show_inoperative', false );
	$show_totals          = $affiliates_options->get_option( 'affiliates_show_totals', true );

	if ( isset( $_POST['clear_filters'] ) ) {
		$affiliates_options->delete_option( 'affiliates_from_date' );
		$affiliates_options->delete_option( 'affiliates_thru_date' );
		$affiliates_options->delete_option( 'affiliates_affiliate_id' );
		$affiliates_options->delete_option( 'affiliates_affiliate_name' );
		$affiliates_options->delete_option( 'affiliates_affiliate_email' );
		$affiliates_options->delete_option( 'affiliates_affiliate_user_login' );
		$affiliates_options->delete_option( 'affiliates_show_active' );
		$affiliates_options->delete_option( 'affiliates_show_pending' );
		$affiliates_options->delete_option( 'affiliates_show_deleted' );
		$affiliates_options->delete_option( 'affiliates_show_inoperative' );
		$affiliates_options->delete_option( 'affiliates_show_totals' );
		$from_date = null;
		$from_datetime = null;
		$thru_date = null;
		$thru_datetime = null;
		$affiliate_id = null;
		$affiliate_name = null;
		$affiliate_email = null;
		$affiliate_user_login = null;
		$show_active = true;
		$show_pending = true;
		$show_deleted = false;
		$show_inoperative = false;
		$show_totals = true;
	} else if ( isset( $_POST['submitted'] ) ) {
		if ( !empty( $_POST['affiliate_name'] ) ) {
			$affiliate_name = trim( $_POST['affiliate_name'] );
			if ( strlen( $affiliate_name ) > 0 ) {
				$affiliates_options->update_option( 'affiliates_affiliate_name', $affiliate_name );
			} else {
				$affiliate_name = null;
				$affiliates_options->delete_option( 'affiliates_affiliate_name' );
			}
		} else {
			$affiliate_name = null;
			$affiliates_options->delete_option( 'affiliates_affiliate_name' );
		}
		if ( !empty( $_POST['affiliate_email'] ) ) {
			$affiliate_email = trim( $_POST['affiliate_email'] );
			if ( strlen( $affiliate_email ) > 0 ) {
				$affiliates_options->update_option( 'affiliates_affiliate_email', $affiliate_email );
			} else {
				$affiliate_email = null;
				$affiliates_options->delete_option( 'affiliates_affiliate_email' );
			}
		} else {
			$affiliate_email = null;
			$affiliates_options->delete_option( 'affiliates_affiliate_email' );
		}
		if ( !empty( $_POST['affiliate_user_login'] ) ) {
			$affiliate_user_login = trim( $_POST['affiliate_user_login'] );
			if ( strlen( $affiliate_user_login ) > 0 ) {
				$affiliates_options->update_option( 'affiliates_affiliate_user_login', $affiliate_user_login );
			} else {
				$affiliate_user_login = null;
				$affiliates_options->delete_option( 'affiliates_affiliate_user_login' );
			}
		} else {
			$affiliate_user_login = null;
			$affiliates_options->delete_option( 'affiliates_affiliate_user_login' );
		}
		$show_active = isset( $_POST['show_active'] );
		$affiliates_options->update_option( 'affiliates_show_active', $show_active );
		$show_pending = isset( $_POST['show_pending'] );
		$affiliates_options->update_option( 'affiliates_show_pending', $show_pending );
		$show_deleted = isset( $_POST['show_deleted'] );
		$affiliates_options->update_option( 'affiliates_show_deleted', $show_deleted );
		$show_inoperative = isset( $_POST['show_inoperative'] );
		$affiliates_options->update_option( 'affiliates_show_inoperative', $show_inoperative );
		$show_totals = isset( $_POST['show_totals'] );
		$affiliates_options->update_option( 'affiliates_show_totals', $show_totals );
		// filter by date(s)
		if ( !empty( $_POST['from_date'] ) ) {
			$from_date = date( 'Y-m-d', strtotime( $_POST['from_date'] ) );
			$affiliates_options->update_option( 'affiliates_from_date', $from_date );
		} else {
			$from_date = null;
			$affiliates_options->delete_option( 'affiliates_from_date' );
		}
		if ( !empty( $_POST['thru_date'] ) ) {
			$thru_date = date( 'Y-m-d', strtotime( $_POST['thru_date'] ) );
			$affiliates_options->update_option( 'affiliates_thru_date', $thru_date );
		} else {
			$thru_date = null;
			$affiliates_options->delete_option( 'affiliates_thru_date' );
		}
		if ( $from_date && $thru_date ) {
			if ( strtotime( $from_date ) > strtotime( $thru_date ) ) {
				$thru_date = null;
				$affiliates_options->delete_option( 'affiliates_thru_date' );
			}
		}
		// We now have the desired dates from the user's point of view, i.e. in her timezone.
		// If supported, adjust the dates for the site's timezone:
		if ( $from_date ) {
			$from_datetime = DateHelper::u2s( $from_date );
		}
		if ( $thru_date ) {
			$thru_datetime = DateHelper::u2s( $thru_date, 24*3600 );
		}
		// filter by affiliate id
		if ( !empty( $_POST['affiliate_id'] ) ) {
			$affiliate_id = affiliates_check_affiliate_id( $_POST['affiliate_id'] );
			if ( $affiliate_id ) {
				$affiliates_options->update_option( 'affiliates_affiliate_id', $affiliate_id );
			}
		} else if ( isset( $_POST['affiliate_id'] ) ) { // empty && isset => '' => all
			$affiliate_id = null;
			$affiliates_options->delete_option( 'affiliates_affiliate_id' );
		}
	}

	if ( isset( $_POST['row_count'] ) ) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_AFFILIATES_NONCE_1], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	if ( isset( $_POST['paged'] ) ) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_AFFILIATES_NONCE_2], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_url = remove_query_arg( 'paged', $current_url );
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = remove_query_arg( 'affiliate_id', $current_url );

	$affiliates_table = _affiliates_get_tablename( 'affiliates' );
	$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );

	$output .=
		'<div class="manage-affiliates">' .
		'<h1>' .
		__( 'Manage Affiliates', 'affiliates' ) .
		'</h1>';

	$show_filters = $affiliates_options->get_option( 'show_filters', true );

	$output .= '<div class="manage">';
	$output .= "<a title='" . __( 'Click to add a new affiliate', 'affiliates' ) . "' class='button add' href='" . esc_url( $current_url ) . "&action=add'><img class='icon' alt='" . __( 'Add', 'affiliates') . "' src='". AFFILIATES_PLUGIN_URL ."images/add.png'/><span class='label'>" . __( 'New Affiliate', 'affiliates') . "</span></a>";
	$output .= '<div style="float:right">';
	$output .= sprintf( '<div class="button toggle-button %s" id="filters-toggle">', ( $show_filters ? 'on' : 'off' ) );
	$output .= __( 'Filters', 'affiliates' );
	$output .= '</div>'; // #filters-toggle
	$output .= '</div>'; // floating right
	$output .= '</div>'; // .manage

	$row_count = isset( $_POST['row_count'] ) ? intval( $_POST['row_count'] ) : 0;

	if ($row_count <= 0) {
		$row_count = $affiliates_options->get_option( 'affiliates_per_page', AFFILIATES_AFFILIATES_PER_PAGE );
	} else {
		$affiliates_options->update_option('affiliates_per_page', $row_count );
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
		case 'from_date' :
		case 'thru_date' :
		case 'email' :
		case 'affiliate_id' :
		case 'name' :
		case 'user_login' :
		case 'status' :
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

	$filters = array( " 1=%d " );
	$filter_params = array( 1 );
	if ( $affiliate_id ) {
		$filters[] = " $affiliates_table.affiliate_id = %d ";
		$filter_params[] = $affiliate_id;
	}
	if ( $affiliate_name ) {
		$filters[] = " $affiliates_table.name LIKE '%%%s%%' ";
		$filter_params[] = $affiliate_name;
	}
	if ( $affiliate_email ) {
		$filters[] = " $affiliates_table.email LIKE '%%%s%%' ";
		$filter_params[] = $affiliate_email;
	}
	if ( $affiliate_user_login ) {
		$filters[] = " $wpdb->users.user_login LIKE '%%%s%%' ";
		$filter_params[] = $affiliate_user_login;
	}
	$statuses = array( '' ); // need at least one entry for the IN clause
	if ( $show_active ) {
		$statuses[] = 'active';
	}
	if ( $show_pending ) {
		$statuses[] = 'pending';
	}
	if ( $show_deleted ) {
		$statuses[] = 'deleted';
	}
	$filters[] = sprintf( " $affiliates_table.status IN (%s) ", !empty( $statuses ) ? "'" .  implode( "','", $statuses ) . "'" : '' );
	if ( !$show_inoperative ) {
		$filters[] = " $affiliates_table.from_date <= %s AND ( $affiliates_table.thru_date IS NULL OR $affiliates_table.thru_date >= %s ) ";
		$filter_params[] = $today;
		$filter_params[] = $today;
	}
	if ( $from_datetime && $thru_datetime ) {
		$filters[] = " $affiliates_table.from_date >= %s AND ( $affiliates_table.thru_date IS NULL OR $affiliates_table.thru_date < %s ) ";
		$filter_params[] = $from_datetime;
		$filter_params[] = $thru_datetime;
	} else if ( $from_datetime ) {
		$filters[] = " $affiliates_table.from_date >= %s ";
		$filter_params[] = $from_datetime;
	} else if ( $thru_datetime ) {
		$filters[] = " $affiliates_table.thru_date < %s ";
		$filter_params[] = $thru_datetime;
	}

	if ( !empty( $filters ) ) {
		$filters = " WHERE " . implode( " AND ", $filters );
	} else {
		$filters = '';
	}

	$count_query = $wpdb->prepare( "SELECT COUNT(*) FROM $affiliates_table LEFT JOIN $affiliates_users_table ON $affiliates_table.affiliate_id = $affiliates_users_table.affiliate_id LEFT JOIN $wpdb->users on $affiliates_users_table.user_id = $wpdb->users.ID $filters", $filter_params );
	$count  = $wpdb->get_var( $count_query );
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

	$query = $wpdb->prepare( "SELECT $affiliates_table.*, $wpdb->users.* FROM $affiliates_table LEFT JOIN $affiliates_users_table ON $affiliates_table.affiliate_id = $affiliates_users_table.affiliate_id LEFT JOIN $wpdb->users on $affiliates_users_table.user_id = $wpdb->users.ID $filters ORDER BY $orderby $order LIMIT $row_count OFFSET $offset", $filter_params );
	$results = $wpdb->get_results( $query, OBJECT );

	$column_display_names = array(
		'affiliate_id' => __( 'Id', 'affiliates' ),
		'name'         => __( 'Affiliate', 'affiliates' ),
		'email'        => __( 'Email', 'affiliates' ),
		'user_login'   => __( 'Username', 'affiliates' ),
		'from_date'    => __( 'From', 'affiliates' ),
		'thru_date'    => __( 'Until', 'affiliates' ),
		'status'       => __( 'Status', 'affiliates' ),
		'edit'         => __( 'Edit', 'affiliates' ),
		'remove'       => __( 'Remove', 'affiliates' ),
		'links'        => __( 'Links', 'affiliates' ),
	);

	$output .= '<div class="affiliates-overview">';

	$output .= sprintf( '<div id="filters-container" class="filters" style="%s">', $show_filters ? '' : 'display:none' );
	$output .=
			'<label class="description" for="setfilters">' . __( 'Filters', 'affiliates' ) . '</label>' .
			'<form id="setfilters" action="" method="post">' .
				'<div class="filter-section">' .
				'<label class="affiliate-id-filter">' .
					__( 'Id', 'affiliates' ) .
					' ' .
					'<input class="affiliate-id-filter" name="affiliate_id" type="text" value="' . esc_attr( $affiliate_id ) . '"/>' .
				'</label>' .
				' ' .
				'<label class="affiliate-name-filter">' .
				__( 'Name', 'affiliates' ) .
				' ' .
				'<input class="affiliate-name-filter" name="affiliate_name" type="text" value="' . $affiliate_name . '"/>' .
				'</label>' .
				' ' .
				'<label class="affiliate-email-filter">' .
				__( 'Email', 'affiliates' ) .
				' ' .
				'<input class="affiliate-email-filter" name="affiliate_email" type="text" value="' . $affiliate_email . '"/>' .
				'</label>' .
				' ' .
				'<label class="affiliate-user-login-filter">' .
				__( 'Username', 'affiliates' ) .
				' ' .
				'<input class="affiliate-user-login-filter" name="affiliate_user_login" type="text" value="' . $affiliate_user_login . '" />' .
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
				' ' .
				'<label class="show-inoperative-filter">' .
					'<input class="show-inoperative-filter" name="show_inoperative" type="checkbox" ' . ( $show_inoperative ? 'checked="checked"' : '' ) . '/>' .
					' ' .
					__( 'Include inoperative affiliates', 'affiliates' ) .
				'</label>' .
				' ' .
				'<label class="show-active-filter">' .
				'<input class="show-active-filter" name="show_active" type="checkbox" ' . ( $show_active ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Include active affiliates', AFFILIATES_PLUGIN_DOMAIN ) .
				'</label>' .
				' ' .
				'<label class="show-pending-filter">' .
				'<input class="show-pending-filter" name="show_pending" type="checkbox" ' . ( $show_pending ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Include pending affiliates', AFFILIATES_PLUGIN_DOMAIN ) .
				'</label>' .
				' ' .
				'<label class="show-deleted-filter">' .
					'<input class="show-deleted-filter" name="show_deleted" type="checkbox" ' . ( $show_deleted ? 'checked="checked"' : '' ) . '/>' .
					' ' .
					__( 'Include removed affiliates', 'affiliates' ) .
				'</label>' .
				' ' .
				'<label class="show-totals-filter">' .
					'<input class="show-totals-filter" name="show_totals" type="checkbox" ' . ( $show_totals ? 'checked="checked"' : '' ) . '/>' .
					' ' .
					__( 'Show accumulated referral totals', 'affiliates' ) .
				'</label>' .
				'</div>
				<div class="filter-buttons">' .
				wp_nonce_field( 'admin', AFFILIATES_ADMIN_AFFILIATES_FILTER_NONCE, true, false ) .
				'<input class="button" type="submit" value="' . __( 'Apply', 'affiliates' ) . '"/>' .
				'<input class="button" type="submit" name="clear_filters" value="' . __( 'Clear', 'affiliates' ) . '"/>' .
				'<input type="hidden" value="submitted" name="submitted"/>' .
				'</div>' .
			'</form>' .
		'</div>';

	$output .= '
		<div class="page-options right">
			<form id="setrowcount" action="" method="post">
				<div>
					<label for="row_count">' . __('Results per page', 'affiliates' ) . '</label>' .
					'<input name="row_count" type="text" size="2" value="' . esc_attr( $row_count ) .'" />
					' . wp_nonce_field( 'admin', AFFILIATES_ADMIN_AFFILIATES_NONCE_1, true, false ) . '
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
		$output .= wp_nonce_field( 'admin', AFFILIATES_ADMIN_AFFILIATES_NONCE_2, true, false );
		$output .= '</div>';
		$output .= '<div class="tablenav top">';
		$output .= $pagination->pagination( 'top' );
		$output .= '</div>';
		$output .= '</form>';
	}

	$output .= '<form id="affiliates-action" method="post" action="">';

	$output .= wp_nonce_field( 'admin', AFFILIATES_ADMIN_AFFILIATES_ACTION_NONCE, true, false );
	$output .= '<div class="affiliates-bulk-container">';
	$output .= '<select class="bulk-action" name="bulk-action">';
	$output .= '<option selected="selected" value="-1">' . esc_html( __( 'Bulk Actions', 'affiliates' ) ) . '</option>';
	$output .= '<option value="remove-affiliate">' . esc_html( __( 'Remove affiliate', 'affiliates' ) ) . '</option>';
	$output .= '<option value="status-pending">' . esc_html( __( 'Set status to Pending', 'affiliates' ) ) . '</option>';
	$output .= '<option value="status-active">' . esc_html( __( 'Set status to Active', 'affiliates' ) ) . '</option>';
	$output .= '</select>';
	$output .= sprintf( '<input class="button" type="submit" name="bulk" value="%s" />', esc_attr( __( 'Apply', 'affiliates' ) ) );
	$output .= '<input type="hidden" name="action" value="affiliate-action"/>';
	$output .= '</div>';

	$output .= '<table id="" class="wp-list-table widefat fixed" cellspacing="0">';
	$output .= '<thead>';
	$output .= '<tr>';

	$output .= '<th id="cb" class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>';

	$num_columns = 0;
	foreach ( $column_display_names as $key => $column_display_name ) {
		$options = array(
			'orderby' => $key,
			'order' => $switch_order
		);
		$class = $key;
		if ( !in_array($key, array( 'edit', 'remove', 'links' ) ) ) {
			if ( strcmp( $key, $orderby ) == 0 ) {
				$lorder = strtolower( $order );
				$class = "$key manage-column sorted $lorder";
			} else {
				$class = "$key manage-column sortable";
			}
			$column_display_name = '<a href="' . esc_url( add_query_arg( $options, $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
		}
		$output .= "<th scope='col' class='$class'>$column_display_name</th>";
		$num_columns++;
	}
	$num_columns++; // ID

	$output .= '</tr>
		</thead>
		<tbody>
		';

	if ( count( $results ) > 0 ) {
		for ( $i = 0; $i < count( $results ); $i++ ) {

			$result = $results[$i];

			$name_suffix = '';
			$class_status = '';
			if ( $is_deleted = ( strcmp( $result->status, 'deleted' ) == 0 ) ) {
				$class_status = ' deleted ';
				$name_suffix .= " " . __( '(removed)', 'affiliates' );
			} else if ( strcmp( $result->status, 'pending' ) == 0 ) {
				$class_status .= ' pending ';
			}

			$class_inoperative = '';
			if ( $is_inoperative = ! ( ( $result->from_date <= $today ) && ( $result->thru_date == null || $result->thru_date >= $today ) ) ) {
				$class_inoperative = ' inoperative ';
				$name_suffix .= " " . __( '(inoperative)', 'affiliates' );
			}

			$output .= '<tr class="' . $class_status . $class_inoperative . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';

			$output .= '<th class="check-column">';
			$output .= '<input type="checkbox" value="' . esc_attr( $result->affiliate_id ) . '" name="affiliate_ids[]"/>';
			$output .= '</th>';

			$output .= "<td class='affiliate-id'>";
			if ( affiliates_encode_affiliate_id( $result->affiliate_id ) != $result->affiliate_id ) {
				$output .= '<span class="encoded-hint" title="' . affiliates_encode_affiliate_id( $result->affiliate_id ) . '">' . $result->affiliate_id . '</span>';
			} else {
				$output .= $result->affiliate_id;
			}
			$output .= "</td>";
			$output .= "<td class='affiliate-name'>" . stripslashes( wp_filter_nohtml_kses( $result->name ) ) . $name_suffix . "</td>";
			$output .= "<td class='affiliate-email'>" . $result->email;
			if ( isset( $result->email ) && isset( $result->user_email ) && strcmp( $result->email, $result->user_email ) !== 0 ) {
				$output .= '<span title="' . sprintf( __( 'There are different email addresses on record for the affiliate and the associated user. This might be ok, but if in doubt please check. The email address on file for the user is %s', 'affiliates' ), $result->user_email ) . '" class="warning"> [&nbsp;!&nbsp]</span>';
			}
			$output .= "</td>";
			$output .= "<td class='affiliate-user-login'>";
			if ( !empty( $result->ID ) ) {
				if ( current_user_can( 'edit_user',  $result->ID ) ) {
					$output .= '<a target="_blank" href="' . esc_url( "user-edit.php?user_id=$result->ID" ) . '">' . $result->user_login . '</a>';
				} else {
					$output .= $result->user_login;
				}
			}

			$output .= "</td>";
			$output .= "<td class='from-date'>$result->from_date</td>";
			$output .= "<td class='thru-date'>$result->thru_date</td>";

			$output .= "<td class='status'>$result->status</td>";

			$output .= "<td class='edit'><a href='" . esc_url( add_query_arg( 'paged', $paged, $current_url ) ) . "&action=edit&affiliate_id=" . $result->affiliate_id . "' alt='" . __( 'Edit', 'affiliates') . "'><img src='". AFFILIATES_PLUGIN_URL ."images/edit.png'/></a></td>";
			$output .= "<td class='remove'>" .
				( !$is_deleted && ( !isset( $result->type ) || ( $result->type != AFFILIATES_DIRECT_TYPE )  ) ?
				"<a href='" . esc_url( $current_url ) . "&action=remove&affiliate_id=" . $result->affiliate_id . "' alt='" . __( 'Remove', 'affiliates') . "'><img src='". AFFILIATES_PLUGIN_URL ."images/remove.png'/></a>"
				: "" ) .
				"</td>";
			$output .= "<td class='links'>";
			$encoded_id = affiliates_encode_affiliate_id( $result->affiliate_id );
			$output .=
				__( 'Link', 'affiliates' ) .
				': ' .
				'<span class="affiliate-link">' . affiliates_get_affiliate_url( get_bloginfo('url'), $result->affiliate_id ) . '</span>' .
				'<br/>' .
				__( 'URL Parameter', 'affiliates' ) .
				': ' .
				 '<span class="affiliate-link-param">' . '?' . $pname . '=' . $encoded_id . '</span>';
				 // @deprecated
// 				'<br/>' .
// 				__( 'Pretty', 'affiliates' ) .
// 				': ' .
// 				'<span class="affiliate-permalink">' . get_bloginfo('url') . '/' . $pname . '/' . $encoded_id . '</span>' .
// 				( $wp_rewrite->using_permalinks() ? '' :
// 					' ' .
// 					sprintf( '<span class="warning" title="%s" style="cursor:help;padding:0 2px;">*</span>', __( 'Pretty URLs only work with appropriate permalink settings, this is not a requirement and most affiliate links will be using the URL parameter anyhow when linking to different pages on the site.', 'affiliates' ) ) .
// 					'</span>'
// 				)
			$output .= "</td>";
			$output .= '</tr>';

			if ( $show_totals ) {
				$output .= '<tr class="' . $class_status . $class_inoperative . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
				$totals = array();
				$totals[AFFILIATES_REFERRAL_STATUS_CLOSED]   = Affiliates_Shortcodes::get_total( $result->affiliate_id, null, null, AFFILIATES_REFERRAL_STATUS_CLOSED );
				$totals[AFFILIATES_REFERRAL_STATUS_ACCEPTED] = Affiliates_Shortcodes::get_total( $result->affiliate_id, null, null, AFFILIATES_REFERRAL_STATUS_ACCEPTED );
				$totals[AFFILIATES_REFERRAL_STATUS_PENDING]  = Affiliates_Shortcodes::get_total( $result->affiliate_id, null, null, AFFILIATES_REFERRAL_STATUS_PENDING );
				$totals[AFFILIATES_REFERRAL_STATUS_REJECTED] = Affiliates_Shortcodes::get_total( $result->affiliate_id, null, null, AFFILIATES_REFERRAL_STATUS_REJECTED );
				$output .= '<td colspan="' . $num_columns . '">';
				$output .= '<table class="affiliate-referral-totals">';
				$output .= '<thead>';
				$output .= '<tr>';
				foreach( $totals as $status => $total ) {
					if ( $total ) {
						$output .= '<th>';
						$output .= '<strong>';
						switch( $status ) {
							case AFFILIATES_REFERRAL_STATUS_CLOSED :
								$output .= sprintf( __( '<span style="cursor:help" title="%s">Closed</span>', 'affiliates' ), esc_attr( __( 'Accumulated total for closed referrals (commissions paid).', 'affiliates' ) ) );
								break;
							case AFFILIATES_REFERRAL_STATUS_ACCEPTED :
								$output .= sprintf( __( '<span style="cursor:help" title="%s">Accepted</span>', 'affiliates' ), esc_attr( __( 'Accumulated total for accepted referrals (commissions unpaid).', 'affiliates' ) ) );
								break;
							case AFFILIATES_REFERRAL_STATUS_PENDING :
								$output .= sprintf( __( '<span style="cursor:help" title="%s">Pending</span>', 'affiliates' ), esc_attr( __( 'Accumulated total for pending referrals.', 'affiliates' ) ) );
								break;
							case AFFILIATES_REFERRAL_STATUS_REJECTED :
								$output .= sprintf( __( '<span style="cursor:help" title="%s">Rejected</span>', 'affiliates' ), esc_attr( __( 'Accumulated total for rejected referrals.', 'affiliates' ) ) );
								break;
						}
						$output .= '</strong>';
						$output .= '</th>';
					}
				}
				$output .= '</thead>';
				$output .= '</tr>';
				$output .= '<tbody>';
				$output .= '<tr>';
				foreach( $totals as $status => $total ) {
					if ( $total ) {
						$output .= '<td>';
						$output .= '<ul>';
						foreach( $total as $currency => $amount ) {
							$output .= '<li>';
							$output .= sprintf( __( '%1$s %2$s', 'affiliates' ), $currency, $amount ); // translators: first is a three-letter currency code, second is a monetary amount
							$output .= '</li>';
						}
						$output .= '</ul>';
						$output .= '</td>';
					}
				}
				$output .= '</tr>';
				$output .= '<tbody>';
				$output .= '</table>';
				$output .= '</td>';
				$output .= '</tr>';
			}
		}
	} else {
		$output .= '<tr><td colspan="' . $num_columns . '">' . __('There are no results.', 'affiliates' ) . '</td></tr>';
	}

	$output .= '</tbody>';
	$output .= '</table>';
	$output .= '</form>'; // #affiliates-action

	if ( $paginate ) {
		require_once( AFFILIATES_CORE_LIB . '/class-affiliates-pagination.php' );
		$pagination = new Affiliates_Pagination($count, null, $row_count);
		$output .= '<div class="tablenav bottom">';
		$output .= $pagination->pagination( 'bottom' );
		$output .= '</div>';
	}

	$output .= '</div>'; // .affiliates-overview
	$output .= '</div>'; // .manage-affiliates
	echo $output;
	affiliates_footer();
} // function affiliates_admin_affiliates()
