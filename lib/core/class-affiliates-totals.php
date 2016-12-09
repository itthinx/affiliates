<?php
/**
 * class-affiliates-totals.php
 * 
 * Copyright (c) 2010 - 2014 "kento" Karim Rahimpur www.itthinx.com
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
 * @since 2.7.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'Affiliates_Totals' ) ) {

/**
 * Totals admin.
 */
class Affiliates_Totals {

	const NONCE       = 'totals-nonce';
	const NONCE_1     = 'totals-nonce-1';
	const NONCE_2     = 'totals-nonce-2';
	const SET_FILTERS = 'set-filters';
	const SET_RPP     = 'set-rpp';
	const SET_PAGE    = 'set-page';
	const TOTALS_PER_PAGE = 10;

	public static function view() {

		global $wpdb, $affiliates_options;

		$output = '';
		$today = date( 'Y-m-d', time() );

		if ( !current_user_can( AFFILIATES_ACCESS_AFFILIATES ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}

		if ( isset ( $_GET['action'] ) ) {
			switch( $_GET['action'] ) {
				case 'close_referrals' :
					$params = array(
						'tables' => array(
							'referrals' => _affiliates_get_tablename( 'referrals' ),
							'affiliates' => _affiliates_get_tablename( 'affiliates' ),
							'affiliates_users' => _affiliates_get_tablename( 'affiliates_users' ),
							'users' => $wpdb->users,
						)
					);
					$params = array_merge( $_GET, $params );
					echo self::update_status( AFFILIATES_REFERRAL_STATUS_CLOSED, $params );
					die();
					break;
			}
		}

		$from_date            = $affiliates_options->get_option( 'totals_from_date', null );
		$from_datetime        = null;
		$thru_date            = $affiliates_options->get_option( 'totals_thru_date', null );
		$thru_datetime        = null;
		$affiliate_status     = $affiliates_options->get_option( 'totals_affiliate_status', null );
		$referral_status      = $affiliates_options->get_option( 'totals_referral_status', null );
		$currency_id          = $affiliates_options->get_option( 'totals_currency_id', null );

		if ( isset( $_POST['clear_filters'] ) || isset( $_POST['submitted'] ) ) {
			if ( !wp_verify_nonce( $_POST[self::NONCE], self::SET_FILTERS ) ) {
				wp_die( __( 'Access denied.', 'affiliates' ) );
			}
		}

		if ( isset( $_POST['clear_filters'] ) ) {
			$affiliates_options->delete_option( 'totals_from_date' );
			$affiliates_options->delete_option( 'totals_thru_date' );
			$affiliates_options->delete_option( 'totals_affiliate_status' );
			$affiliates_options->delete_option( 'totals_referral_status' );
			$affiliates_options->delete_option( 'totals_currency_id' );

			$from_date       = null;
			$from_datetime   = null;
			$thru_date       = null;
			$thru_datetime   = null;
			$affiliate_status = null;
			$referral_status = null;
			$currency_id     = null;

		} else if ( isset( $_POST['submitted'] ) ) {

			if ( !empty( $_POST['from_date'] ) ) {
				$from_date = date( 'Y-m-d', strtotime( $_POST['from_date'] ) );
				$affiliates_options->update_option( 'totals_from_date', $from_date );
			} else {
				$from_date = null;
				$affiliates_options->delete_option( 'totals_from_date' );
			}
			if ( !empty( $_POST['thru_date'] ) ) {
				$thru_date = date( 'Y-m-d', strtotime( $_POST['thru_date'] ) );
				$affiliates_options->update_option( 'totals_thru_date', $thru_date );
			} else {
				$thru_date = null;
				$affiliates_options->delete_option( 'totals_thru_date' );
			}
			if ( $from_date && $thru_date ) {
				if ( strtotime( $from_date ) > strtotime( $thru_date ) ) {
					$thru_date = null;
					$affiliates_options->delete_option( 'totals_thru_date' );
				}
			}

			if ( !empty( $_POST['affiliate_status'] ) && ( $affiliate_status = Affiliates_Utility::verify_affiliate_status( $_POST['affiliate_status'] ) ) ) {
				$affiliates_options->update_option( 'totals_affiliate_status', $affiliate_status );
			} else {
				$affiliate_status = null;
				$affiliates_options->delete_option( 'totals_affiliate_status' );
			}

			if ( !empty( $_POST['referral_status'] ) && ( $referral_status = Affiliates_Utility::verify_referral_status_transition( $_POST['referral_status'], $_POST['referral_status'] ) ) ) {
				$affiliates_options->update_option( 'totals_referral_status', $referral_status );
			} else {
				$referral_status = null;
				$affiliates_options->delete_option( 'totals_referral_status' );
			}

			if ( !empty( $_POST['currency_id'] ) && ( $currency_id = Affiliates_Utility::verify_currency_id( $_POST['currency_id'] ) ) ) {
				$affiliates_options->update_option( 'totals_currency_id', $currency_id );
			} else {
				$currency_id = null;
				$affiliates_options->delete_option( 'totals_currency_id' );
			}
		}

		if ( isset( $_POST['row_count'] ) ) {
			if ( !wp_verify_nonce( $_POST[self::NONCE_1], self::SET_RPP ) ) {
				wp_die( __( 'Access denied.', 'affiliates' ) );
			}
		}

		if ( isset( $_POST['paged'] ) ) {
			if ( !wp_verify_nonce( $_POST[self::NONCE_2], self::SET_PAGE ) ) {
				wp_die( __( 'Access denied.', 'affiliates' ) );
			}
		}

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$current_url = remove_query_arg( 'paged', $current_url );
		$current_url = remove_query_arg( 'action', $current_url );
		$current_url = remove_query_arg( 'affiliate_id', $current_url );

		$referrals_table        = _affiliates_get_tablename( 'referrals' );
		$affiliates_table       = _affiliates_get_tablename( 'affiliates' );
		$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );

		$output .= '<div class="totals">';
		$output .= '<h1>';
		$output .= __( 'Totals', 'affiliates' );
		$output .= '</h1>';

		$row_count = isset( $_POST['row_count'] ) ? intval( $_POST['row_count'] ) : 0;

		if ($row_count <= 0) {
			$row_count = $affiliates_options->get_option( 'totals_per_page', self::TOTALS_PER_PAGE );
		} else {
			$affiliates_options->update_option('totals_per_page', $row_count );
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
			case 'affiliate_id' :
			case 'name' :
			case 'user_login' :
			case 'email' :
			case 'total' :
			case 'currency_id' :
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

		// We have the desired dates from the user's point of view, i.e. in her timezone.
		// If supported, adjust the dates for the site's timezone:
		if ( $from_date ) {
			$from_datetime = DateHelper::u2s( $from_date );
		}
		if ( $thru_date ) {
			$thru_datetime = DateHelper::u2s( $thru_date, 24*3600 );
		}
		if ( $from_datetime && $thru_datetime ) {
			$filters[] = " r.datetime >= %s AND r.datetime < %s ";
			$filter_params[] = $from_datetime;
			$filter_params[] = $thru_datetime;
		} else if ( $from_datetime ) {
			$filters[] = " r.datetime >= %s ";
			$filter_params[] = $from_datetime;
		} else if ( $thru_datetime ) {
			$filters[] = " r.datetime < %s ";
			$filter_params[] = $thru_datetime;
		}

		if ( $affiliate_status ) {
			$filters[] = " a.status = %s ";
			$filter_params[] = $affiliate_status;
		}

		if ( $referral_status ) {
			$filters[] = " r.status = %s ";
			$filter_params[] = $referral_status;
		}

		if ( $currency_id ) {
			$filters[] = " r.currency_id = %s ";
			$filter_params[] = $currency_id;
		}

		if ( !empty( $filters ) ) {
			$filters = " WHERE " . implode( " AND ", $filters );
		} else {
			$filters = '';
		}

		$having = '';

		// note double select to obtain number of rows (otherwise group counts are obtained)
		$count = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COUNT(*) FROM (
			SELECT r.affiliate_id
			FROM $referrals_table r
			LEFT JOIN $affiliates_table a ON r.affiliate_id = a.affiliate_id
			LEFT JOIN $affiliates_users_table au ON a.affiliate_id = au.affiliate_id
			LEFT JOIN $wpdb->users u on au.user_id = u.ID
			$filters 
			GROUP BY r.affiliate_id, r.currency_id
			$having
			) tmp
			",
			$filter_params
		) );

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

		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT a.*, u.user_login, SUM(r.amount) as total, r.currency_id
			FROM $referrals_table r
			LEFT JOIN $affiliates_table a ON r.affiliate_id = a.affiliate_id
			LEFT JOIN $affiliates_users_table au ON a.affiliate_id = au.affiliate_id
			LEFT JOIN $wpdb->users u on au.user_id = u.ID
			$filters
			GROUP BY r.affiliate_id, r.currency_id
			$having
			ORDER BY $orderby $order LIMIT $row_count OFFSET $offset
			",
			$filter_params
		) );

		$column_display_names = array(
			'affiliate_id' => __( 'Id', 'affiliates' ),
			'name'         => __( 'Affiliate', 'affiliates' ),
			'email'        => __( 'Email', 'affiliates' ),
			'user_login'   => __( 'Username', 'affiliates' ),
			'total'        => __( 'Total', 'affiliates' ),
			'currency_id'  => __( 'Currency', 'affiliates' )
		);

		$output .= '<div class="totals-overview">';

		$mp_params = "";
		if ( !empty( $from_date ) ) {
			$mp_params .= "&from_date=" . urlencode( $from_date );
		}
		if ( !empty( $thru_date ) ) {
			$mp_params .= "&thru_date=" . urlencode( $thru_date );
		}
		if ( !empty( $affiliate_status ) ) {
			$mp_params .= "&affiliate_status=" . urlencode( $affiliate_status );
		}
		if ( !empty( $referral_status ) ) {
			$mp_params .= "&referral_status=" . urlencode( $referral_status );
		}
		if ( !empty( $currency_id ) ) {
			$mp_params .= "&currency_id=" . urlencode( $currency_id );
		}
		if ( !empty( $orderby ) ) {
			$mp_params .= "&orderby=" . urlencode( $orderby );
		}
		if ( !empty( $order ) ) {
			$mp_params .= "&order=" . urlencode( $order );
		}

		$output .= '<style type="text/css">';
		$output .= '.close-referrals img, .close-referrals span.label { vertical-align: middle; }';
		$output .= '</style>';

		$output .= '<div class="manage">';
		$output .= '<p>';
		$output .=
			"<a title='" . __( 'Click to close these referrals', 'affiliates' ) . "' " .
			"class='button close-referrals' " .
			"href='" . esc_url( $current_url ) . "&action=close_referrals" . $mp_params . "'>" .
			"<img class='icon' alt='" . __( 'Close referrals', 'affiliates') . "' src='". AFFILIATES_PLUGIN_URL ."images/closed.png'/>" .
			"<span class='label'>" . __( 'Close Referrals', 'affiliates') . "</span>" .
			"</a>";
		$output .= "</p>";
		$output .= '</div>';

		$affiliate_status_descriptions = array(
				AFFILIATES_AFFILIATE_STATUS_ACTIVE => __( 'Active', 'affiliates' ),
				AFFILIATES_AFFILIATE_STATUS_PENDING   => __( 'Pending', 'affiliates' ),
				AFFILIATES_AFFILIATE_STATUS_DELETED => __( 'Deleted', 'affiliates' ),
		);
		
		$affiliate_status_select = '<label class="affiliate-status-filter" for="affiliate_status">' . __('Affiliate Status', 'affiliates' ) . '</label>';
		$affiliate_status_select .= ' ';
		$affiliate_status_select .= '<select class="affiliate-status-filter" name="affiliate_status">';
		$affiliate_status_select .= '<option value="" ' . ( empty( $affiliate_status ) ? ' selected="selected" ' : '' ) . '>--</option>';
		foreach ( $affiliate_status_descriptions as $key => $label ) {
			$selected = $key == $affiliate_status ? ' selected="selected" ' : '';
			$affiliate_status_select .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
		}
		$affiliate_status_select .= '</select>';

		$status_descriptions = array(
			AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', 'affiliates' ),
			AFFILIATES_REFERRAL_STATUS_CLOSED   => __( 'Closed', 'affiliates' ),
			AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', 'affiliates' ),
			AFFILIATES_REFERRAL_STATUS_REJECTED => __( 'Rejected', 'affiliates' ),
		);

		$status_select = '<label class="referral-status-filter" for="referral_status">' . __('Referral Status', 'affiliates' ) . '</label>';
		$status_select .= ' ';
		$status_select .= '<select class="referral-status-filter" name="referral_status">';
		$status_select .= '<option value="" ' . ( empty( $referral_status ) ? ' selected="selected" ' : '' ) . '>--</option>';
		foreach ( $status_descriptions as $key => $label ) {
			$selected = $key == $referral_status ? ' selected="selected" ' : '';
			$status_select .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
		}
		$status_select .= '</select>';

		$currencies = $wpdb->get_results( "SELECT DISTINCT(currency_id) FROM $referrals_table WHERE currency_id IS NOT NULL" );
		$currency_select = '<label class="currency-id-filter" for="currency_id">' . __( 'Currency', 'affiliates' ) . '</label>';
		$currency_select .= ' ';
		$currency_select .= '<select class="currency-id-filter" name="currency_id">';
		$currency_select .= '<option value="" ' . ( empty( $currency_id ) ? ' selected="selected" ' : '' ) . '>--</option>';
		foreach ( $currencies as $currency ) {
			$selected = $currency->currency_id == $currency_id ? ' selected="selected" ' : '';
			$currency_select .= '<option ' . $selected . ' value="' . esc_attr( $currency->currency_id ) . '">' . $currency->currency_id . '</option>';
		}
		$currency_select .= '</select>';

		$output .=
			'<div class="filters">' .
				'<label class="description" for="setfilters">' . __( 'Filters', 'affiliates' ) . '</label>' .
				'<form id="setfilters" action="" method="post">' .
					'<p>' .
					$affiliate_status_select .
					' ' .
					$status_select .
					' ' .
					$currency_select .
					'</p>' .
					'<p>' .
					'<label class="from-date-filter" for="from_date">' . __( 'From', 'affiliates' ) . '</label>' .
					'<input class="datefield from-date-filter" name="from_date" type="text" value="' . esc_attr( $from_date ) . '"/>'.
					'<label class="thru-date-filter" for="thru_date">' . __( 'Until', 'affiliates' ) . '</label>' .
					'<input class="datefield thru-date-filter" name="thru_date" type="text" class="datefield" value="' . esc_attr( $thru_date ) . '"/>'.
					'</p>
					<p>' .
					wp_nonce_field( self::SET_FILTERS, self::NONCE, true, false ) .
					'<input class="button" type="submit" value="' . __( 'Apply', 'affiliates' ) . '"/>' .
					'<input class="button" type="submit" name="clear_filters" value="' . __( 'Clear', 'affiliates' ) . '"/>' .
					'<input type="hidden" value="submitted" name="submitted"/>' .
					'</p>' .
				'</form>' .
			'</div>';

		$output .= '
			<div class="page-options">
				<form id="setrowcount" action="" method="post">
					<div>
						<label for="row_count">' . __('Results per page', 'affiliates' ) . '</label>' .
						'<input name="row_count" type="text" size="2" value="' . esc_attr( $row_count ) .'" />
						' . wp_nonce_field( self::SET_RPP, self::NONCE_1, true, false ) . '
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
			$output .= wp_nonce_field( self::SET_PAGE, self::NONCE_2, true, false );
			$output .= '</div>';
			$output .= '<div class="tablenav top">';
			$output .= $pagination->pagination( 'top' );
			$output .= '</div>';
			$output .= '</form>';
		}

		$output .= '
			<table class="wp-list-table widefat fixed" cellspacing="0">
			<thead>
				<tr>
				';

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
		}

		$output .= '</tr>
			</thead>
			<tbody>
			';

		if ( count( $results ) > 0 ) {
			for ( $i = 0; $i < count( $results ); $i++ ) {

				$result = $results[$i];

				$name_suffix = '';
				$class_deleted = '';
				if ( $is_deleted = ( strcmp( $result->status, 'deleted' ) == 0 ) ) {
					$class_deleted = ' deleted ';
					$name_suffix .= " " . __( '(removed)', 'affiliates' );
				}

				$class_inoperative = '';
				if ( $is_inoperative = ! ( ( $result->from_date <= $today ) && ( $result->thru_date == null || $result->thru_date >= $today ) ) ) {
					$class_inoperative = ' inoperative ';
					$name_suffix .= " " . __( '(inoperative)', 'affiliates' );
				}

				$output .= '<tr class="' . $class_deleted . $class_inoperative . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
				$output .= "<td class='affiliate-id'>";
				if ( affiliates_encode_affiliate_id( $result->affiliate_id ) != $result->affiliate_id ) {
					$output .= '<span class="encoded-hint" title="' . affiliates_encode_affiliate_id( $result->affiliate_id ) . '">' . $result->affiliate_id . '</span>';
				} else {
					$output .= $result->affiliate_id;
				}
				$output .= "</td>";
				$output .= "<td class='affiliate-name'>" . stripslashes( wp_filter_nohtml_kses( $result->name ) ) . $name_suffix . "</td>";
				$output .= "<td class='affiliate-email'>" . $result->email . "</td>";
				$output .= "<td class='affiliate-user-login'>" . $result->user_login . "</td>";

				$output .= "<td class='total'>$result->total</td>";
				$output .= "<td class='currency-id'>$result->currency_id</td>";

				$output .= '</tr>';
			}
		} else {
			$output .= '<tr><td colspan="' . count( $column_display_names ) . '">' . __( 'There are no results.', 'affiliates' ) . '</td></tr>';
		}

		$output .= '</tbody>';
		$output .= '</table>';

		if ( $paginate ) {
			$pagination = new Affiliates_Pagination($count, null, $row_count);
			$output .= '<div class="tablenav bottom">';
			$output .= $pagination->pagination( 'bottom' );
			$output .= '</div>';
		}

		$output .= '</div>'; // .totals-overview
		$output .= '</div>'; // .totals
		echo $output;
		affiliates_footer();
	}

	public static function update_status( $new_status, $params = null ) {

		global $wpdb;

		$output = "";

		$from_date            = isset( $params['from_date'] ) ? $params['from_date'] : null;
		$from_datetime        = $from_date ? DateHelper::u2s( $from_date ) : null;
		$thru_date            = isset( $params['thru_date'] ) ? $params['thru_date'] : null;
		$thru_datetime        = $thru_date ? DateHelper::u2s( $thru_date, 24*3600 ) : null;

		$affiliate_status     = isset( $params['affiliate_status'] ) ? Affiliates_Utility::verify_affiliate_status( $params['affiliate_status'] ) : null;
		$referral_status      = isset( $params['referral_status'] ) ? Affiliates_Utility::verify_referral_status_transition( $params['referral_status'], $params['referral_status'] ) : null;
		$currency_id          = isset( $params['currency_id'] ) ? Affiliates_Utility::verify_currency_id( $params['currency_id'] ) : null;

		$orderby              = isset( $params['orderby'] ) ? $params['orderby'] : null;
		$order                = isset( $params['order'] ) ? $params['order'] : null;

		switch ( $orderby ) {
			case 'affiliate_id' :
			case 'name' :
			case 'email' :
				$orderby = 'a.' . $orderby;
				break;
			case 'user_login' :
				$orderby = 'au.' . $orderby;
				break;
			case 'currency_id' :
				$orderby = 'r.' . $orderby;
				break;
			default:
				$orderby = 'a.name';
		}

		switch ( $order ) {
			case 'asc' :
			case 'ASC' :
			case 'desc' :
			case 'DESC' :
				break;
			default:
				$order = 'ASC';
		}

		if ( isset( $params['tables'] ) ) {

			$output .= "<h1>" . __( "Closing referrals", 'affiliates' ) . "</h1>";
			$output .= "<div class='closing-referrals-overview'>";

			$affiliates_table       = $params['tables']['affiliates'];
			$affiliates_users_table = $params['tables']['affiliates_users'];
			$referrals_table        = $params['tables']['referrals'];
			$users_table            = $params['tables']['users'];

			$filters = array( " 1=%d " );
			$filter_params = array( 1 );

			if ( $from_datetime && $thru_datetime ) {
				$filters[] = " r.datetime >= %s AND r.datetime < %s ";
				$filter_params[] = $from_datetime;
				$filter_params[] = $thru_datetime;
			} else if ( $from_datetime ) {
				$filters[] = " r.datetime >= %s ";
				$filter_params[] = $from_datetime;
			} else if ( $thru_datetime ) {
				$filters[] = " r.datetime < %s ";
				$filter_params[] = $thru_datetime;
			}

			if ( $affiliate_status ) {
				$filters[] = " a.status = %s ";
				$filter_params[] = $affiliate_status;
			}

			if ( $referral_status ) {
				$filters[] = " r.status = %s ";
				$filter_params[] = $referral_status;
			}

			if ( $currency_id ) {
				$filters[] = " r.currency_id = %s ";
				$filter_params[] = $currency_id;
			}

			if ( !empty( $filters ) ) {
				$filters = " WHERE " . implode( " AND ", $filters );
			} else {
				$filters = '';
			}

			$order_by = '';
			if ( $orderby && $order ) {
				$order_by .= " ORDER BY $orderby $order ";
			}

			$step = isset( $params['step'] ) ? intval( $params['step'] ) : 1;

			switch ( $step ) {
				case 1 :
					$results = $wpdb->get_results( $wpdb->prepare(
						"
						SELECT a.*, r.*, u.user_login
						FROM $referrals_table r
						LEFT JOIN $affiliates_table a ON r.affiliate_id = a.affiliate_id
						LEFT JOIN $affiliates_users_table au ON a.affiliate_id = au.affiliate_id
						LEFT JOIN $users_table u on au.user_id = u.ID
						$filters
						$order_by
						",
						$filter_params
					) );

					$output .= "<div class='manage'>";
					$output .= "<div class='warning'>";
					$output .= "<p>";
					$output .= "<strong>";
					$output .= __( "Please review the list of referrals that will be <em>closed</em>.", 'affiliates' );
					$output .= "</strong>";
					$output .= "</p>";
					$output .= "</div>"; // .warning

					$output .= "<p>";
					$output .= __( "Usually only referrals that are <em>accepted</em> and have been paid out should be <em>closed</em>. If there are unwanted or too many referrals shown, restrict your filter settings.", 'affiliates' );
					$output .= "</p>";

					$output .= "<p>";
					$output .= __( "If these referrals can be closed, click the confirmation button below.", 'affiliates' );
					$output .= "</p>";
					$output .= "</div>";

					$output .= '<div id="referrals-overview" class="referrals-overview">';
					$output .= self::render_results( $results );
					$output .= '</div>'; // .referrals-overview

					if ( count( $results > 0 ) ) {
						$mp_params = "";
						if ( !empty( $from_date ) ) {
							$mp_params .= "&from_date=" . urlencode( $from_date );
						}
						if ( !empty( $thru_date ) ) {
							$mp_params .= "&thru_date=" . urlencode( $thru_date );
						}
						if ( !empty( $affiliate_status ) ) {
							$mp_params .= "&affiliate_status=" . urlencode( $affiliate_status );
						}
						if ( !empty( $referral_status ) ) {
							$mp_params .= "&referral_status=" . urlencode( $referral_status );
						}
						if ( !empty( $currency_id ) ) {
							$mp_params .= "&currency_id=" . urlencode( $currency_id );
						}
						if ( !empty( $orderby ) ) {
							$mp_params .= "&orderby=" . urlencode( $orderby );
						}
						if ( !empty( $order ) ) {
							$mp_params .= "&order=" . urlencode( $order );
						}

						$output .= '<div class="manage confirm">';

						$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
						$current_url = remove_query_arg( 'paged', $current_url );
						$current_url = remove_query_arg( 'action', $current_url );
						$current_url = remove_query_arg( 'affiliate_id', $current_url );

						$output .= '<style type="text/css">';
						$output .= '.close-referrals img, .close-referrals .label { vertical-align: middle; }';
						$output .= '</style>';

						$output .= "<p>";
						$output .= __( "Close these referrals by clicking:", 'affiliates' );
						$output .= "</p>";
						$output .=
							"<a title='" . __( 'Click to close these referrals', 'affiliates' ) . "' " .
							"class='close-referrals button' " .
							"href='" . esc_url( $current_url ) . "&action=close_referrals&step=2" . $mp_params . "'>" .
							"<img class='icon' alt='" . __( 'Close referrals', 'affiliates') . "' src='". AFFILIATES_PLUGIN_URL ."images/closed.png'/>" .
							"<span class='label'>" . __( 'Close Referrals', 'affiliates') . "</span>" .
							"</a>";

						$output .= "<div class='warning'>";
						$output .= "<p>";
						$output .= "<strong>";
						$output .= __( "This action can not be undone*.", 'affiliates' );
						$output .= "</strong>";
						$output .= "</p>";
						$output .= "<p>";
						$output .= "<span style='font-size:0.8em;'>";
						$output .= __( "*To undo, each referral would have to be set to the desired status individually.", 'affiliates' );
						$output .= "</span>";
						$output .= "</p>";
						$output .= "</div>"; // .warning

						$output .= '</div>'; // .manage.confirm
					}
					break; // step 1 - ask for confirmation confirmation

				case 2 :
					// try to make the changes
					$results = $wpdb->get_results( $wpdb->prepare(
						"
						SELECT a.*, r.*, u.user_login
						FROM $referrals_table r
						LEFT JOIN $affiliates_table a ON r.affiliate_id = a.affiliate_id
						LEFT JOIN $affiliates_users_table au ON a.affiliate_id = au.affiliate_id
						LEFT JOIN $users_table u on au.user_id = u.ID
						$filters
						$order_by
						",
						$filter_params
					) );

					$updated = array();
					$omitted = array();
					$failed  = array();
					foreach ( $results as $result ) {
						if ( $s = Affiliates_Utility::verify_referral_status_transition( $result->status, $new_status ) ) {

							if ( $wpdb->query( $wpdb->prepare(
									"UPDATE $referrals_table SET status = %s WHERE affiliate_id = %d AND post_id = %d AND datetime = %s ",
									$s,
									$result->affiliate_id,
									$result->post_id,
									$result->datetime
							) ) ) {
								$result->status = $s;
								$updated[] = $result;
							} else {
								$failed[] = $result;
							}
						} else {
							$omitted[] = $result;
						}
					}
					// always show at least the updated table because this will
					// also give information if no results have been updated
					$status_descriptions = array(
						AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', 'affiliates' ),
						AFFILIATES_REFERRAL_STATUS_CLOSED   => __( 'Closed', 'affiliates' ),
						AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', 'affiliates' ),
						AFFILIATES_REFERRAL_STATUS_REJECTED => __( 'Rejected', 'affiliates' ),
					);
					$output .= "<h2>" . __( "Updated", 'affiliates' ) . "</h2>";
					$output .= "<p>";
					$output .= sprintf( __( "These referrals have been updated to <em>%s</em>.", 'affiliates' ), ( isset( $status_descriptions[$new_status] ) ? $status_descriptions[$new_status] : $new_status ) );
					$output .= "</p>";
					$output .= self::render_results( $updated );

					if ( count( $omitted ) > 0 ) {
						$output .= "<h2>" . __( "Omitted", 'affiliates' ) . "</h2>";
						$output .= "<p>";
						$output .= sprintf( __( "These referrals have been omitted because their status must not be changed to <em>%s</em>.", 'affiliates' ), ( isset( $status_descriptions[$new_status] ) ? $status_descriptions[$new_status] : $new_status ) );
						$output .= "</p>";
						$output .= self::render_results( $omitted );
					}

					if ( count( $failed ) > 0 ) {
						$output .= "<h2>" . __( "Failed", 'affiliates' ) . "</h2>";
						$output .= "<p>";
						$output .= sprintf( __( "These referrals could not be updated to <em>%s</em>.", 'affiliates' ), ( isset( $status_descriptions[$new_status] ) ? $status_descriptions[$new_status] : $new_status ) );
						$output .= "</p>";
						$output .= self::render_results( $failed );
					}
					break; // step 2 -commit changes
			}

			$output .= "</div>";// .closing-referrals-overview
		}

		return $output;
	}

	public static function render_results( $results ) {
		$output = "";
		$column_display_names = array(
			'datetime'    => __( 'Date', 'affiliates' ),
			'post_title'  => __( 'Post', 'affiliates' ),
			'name'        => __( 'Affiliate', 'affiliates' ),
			'amount'      => __( 'Amount', 'affiliates' ),
			'currency_id' => __( 'Currency', 'affiliates' ),
			'status'      => __( 'Status', 'affiliates' )
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

		$output .= '<table id="referrals" class="referrals wp-list-table widefat fixed" cellspacing="0">';
		$output .= "<thead>";
		$output .= "<tr>";
		foreach ( $column_display_names as $key => $column_display_name ) {
			$output .= "<th scope='col'>$column_display_name</th>";
		}
		$output .= "</tr>";
		$output .= "</thead>";
		$output .= "<tbody>";

		if ( count( $results ) > 0 ) {
			for ( $i = 0; $i < count( $results ); $i++ ) {
				$result = $results[$i];
				$output .= '<tr class="details-referrals ' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';
				$output .= '<td class="datetime">' . DateHelper::s2u( $result->datetime ) . '</td>';
				$title = get_the_title( $result->post_id );
				$output .= '<td class="post_title">' . wp_filter_nohtml_kses( $title ) . '</td>';
				$output .= "<td class='name'>" . stripslashes( wp_filter_nohtml_kses( $result->name ) ) . "</td>";
				$output .= "<td class='amount'>" . stripslashes( wp_filter_nohtml_kses( $result->amount ) ) . "</td>";
				$output .= "<td class='currency_id'>" . stripslashes( wp_filter_nohtml_kses( $result->currency_id ) ) . "</td>";
				$output .= "<td class='status'>";
				$output .= isset( $status_icons[$result->status] ) ? $status_icons[$result->status] : '';
				$output .= isset( $status_descriptions[$result->status] ) ? $status_descriptions[$result->status] : '';
				$output .= "</td>";
				$output .= '</tr>';
			}
		} else {
			$output .= '<tr><td colspan="' . count( $column_display_names ) . '">' . __('There are no results.', 'affiliates' ) . '</td></tr>';
		}
		$output .= '</tbody>';
		$output .= '</table>';
		return $output;
	}
}

} // if
