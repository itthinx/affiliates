<?php
/**
* class-affiliates-shortcodes.php
*
* Copyright (c) 2010-2012 "kento" Karim Rahimpur www.itthinx.com
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
* @since affiliates 1.3.0
*/
class Affiliates_Shortcodes {

	// var $url_options = array();

	/**
	 * Add shortcodes.
	 */
	public static function init() {
		add_shortcode( 'affiliates_id', array( __CLASS__, 'affiliates_id' ) );
		add_shortcode( 'referrer_id', array( __CLASS__, 'referrer_id' ) );
		add_shortcode( 'referrer_user', array( __CLASS__, 'referrer_user' ) );
		add_shortcode( 'affiliates_is_affiliate', array( __CLASS__, 'affiliates_is_affiliate' ) );
		add_shortcode( 'affiliates_is_not_affiliate', array( __CLASS__, 'affiliates_is_not_affiliate' ) );
		add_shortcode( 'affiliates_hits', array( __CLASS__, 'affiliates_hits' ) );
		add_shortcode( 'affiliates_visits', array( __CLASS__, 'affiliates_visits' ) );
		add_shortcode( 'affiliates_referrals', array( __CLASS__, 'affiliates_referrals' ) );
		add_shortcode( 'affiliates_earnings', array( __CLASS__, 'affiliates_earnings' ) );
		add_shortcode( 'affiliates_url', array( __CLASS__, 'affiliates_url' ) );
		add_shortcode( 'affiliates_login_redirect', array( __CLASS__, 'affiliates_login_redirect' ) );
		add_shortcode( 'affiliates_logout', array( __CLASS__, 'affiliates_logout' ) );
	}

	/**
	 * Affiliate ID shortcode.
	 * Renders the affiliate's id.
	 *
	 * @param array $atts attributes
	 * @param string $content not used
	 */
	public static function affiliates_id( $atts, $content = null ) {
		global $wpdb;
		$output = "";
		$user_id = get_current_user_id();
		if ( $user_id && affiliates_user_is_affiliate( $user_id ) ) {
			$affiliates_table = _affiliates_get_tablename( 'affiliates' );
			$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
			if ( $affiliate_id = $wpdb->get_var( $wpdb->prepare(
				"SELECT $affiliates_users_table.affiliate_id FROM $affiliates_users_table LEFT JOIN $affiliates_table ON $affiliates_users_table.affiliate_id = $affiliates_table.affiliate_id WHERE $affiliates_users_table.user_id = %d AND $affiliates_table.status = 'active'",
				intval( $user_id )
			))) {
				$output .= affiliates_encode_affiliate_id( $affiliate_id );
			}
		}
		return $output;
	}

	/**
	 * Referrer ID shortcode.
	 * Renders the referring affiliate's id.
	 *
	 * @param array $atts attributes
	 * @param string $content not used
	 */
	public static function referrer_id( $atts, $content = null ) {
		$options = shortcode_atts(
			array(
				'direct'  => false
			),
			$atts
		);
		extract( $options );
		$output = "";
		require_once( 'class-affiliates-service.php' );
		$affiliate_id = Affiliates_Service::get_referrer_id();
		if ( $affiliate_id ) {
			if ( $direct || $affiliate_id !== affiliates_get_direct_id() ) {
				$output .= affiliates_encode_affiliate_id( $affiliate_id );
			}
		}
		return $output;
	}

	/**
	 * Renders the referrer's username.
	 * @param array $atts
	 * @param string $content not used
	 * @return string
	 */
	public static function referrer_user( $atts, $content = null ) {
		$options = shortcode_atts(
			array(
				'direct'  => false,
				'display' => 'user_login'
			),
			$atts
		);
		extract( $options );
		$output = '';
		require_once( 'class-affiliates-service.php' );
		$affiliate_id = Affiliates_Service::get_referrer_id();
		if ( $affiliate_id ) {
			if ( $direct || $affiliate_id !== affiliates_get_direct_id() ) {
				if ( $user_id = affiliates_get_affiliate_user( $affiliate_id ) ) {
					if ( $user = get_user_by( 'id', $user_id ) ) {
						switch( $display ) {
							case 'user_login' :
								$output .= $user->user_login;
								break;
							case 'user_nicename' :
								$output .= $user->user_nicename;
								break;
							case 'user_email' :
								$output .= $user->user_email;
								break;
							case 'user_url' :
								$output .= $user->user_url;
								break;
							case 'display_name' :
								$output .= $user->display_name;
								break;
							default :
								$output .= $user->user_login;
						}
						$output = wp_strip_all_tags( $output );
					}
				}
			}
		}
		return $output;
	}

	/**
	 * Affiliate content shortcode.
	 * Renders the content if the current user is an affiliate.
	 *
	 * @param array $atts attributes (none used)
	 * @param string $content this is rendered for affiliates
	 */
	public static function affiliates_is_affiliate( $atts, $content = null ) {

		remove_shortcode( 'affiliates_is_affiliate' );
		$content = do_shortcode( $content );
		add_shortcode( 'affiliates_is_affiliate', array( __CLASS__, 'affiliates_is_affiliate' ) );

		$output = "";
		if ( affiliates_user_is_affiliate( get_current_user_id() ) ) {
			$output .= $content;
		}
		return $output;
	}

	/**
	 * Non-Affiliate content shortcode.
	 *
	 * @param array $atts attributes
	 * @param string $content this is rendered for non-affiliates
	 */
	public static function affiliates_is_not_affiliate( $atts, $content = null ) {

		remove_shortcode( 'affiliates_is_not_affiliate' );
		$content = do_shortcode( $content );
		add_shortcode( 'affiliates_is_not_affiliate', array( __CLASS__, 'affiliates_is_not_affiliate' ) );

		$output = "";
		if ( !affiliates_user_is_affiliate( get_current_user_id() ) ) {
			$output .= $content;
		}
		return $output;
	}

	/**
	 * Adjust from und until dates from UTZ to STZ and take into account the
	 * for option which will adjust the from date to that of the current
	 * day, the start of the week or the month, leaving the until date
	 * set to null.
	 * 
	 * @param string $for "day", "week" or "month"
	 * @param string $from date/datetime
	 * @param string $until date/datetime
	 */
	private static function for_from_until( $for, &$from, &$until ) {
		include_once( AFFILIATES_CORE_LIB . '/class-affiliates-date-helper.php');
		if ( $for === null ) {
			if ( $from !== null ) {
				$from = date( 'Y-m-d H:i:s', strtotime( DateHelper::u2s( $from ) ) );
			}
			if ( $until !== null ) {
				$until = date( 'Y-m-d H:i:s', strtotime( DateHelper::u2s( $until ) ) );
			}
		} else {
			$user_now                      = strtotime( DateHelper::s2u( date( 'Y-m-d H:i:s', time() ) ) );
			$user_now_datetime             = date( 'Y-m-d H:i:s', $user_now );
			$user_daystart_datetime        = date( 'Y-m-d', $user_now ) . ' 00:00:00';
			$server_now_datetime           = DateHelper::u2s( $user_now_datetime );
			$server_user_daystart_datetime = DateHelper::u2s( $user_daystart_datetime );
			$until = null;
			switch ( strtolower( $for ) ) {
				case 'day' :
					$from = date( 'Y-m-d H:i:s', strtotime( $server_user_daystart_datetime ) );
					break;
				case 'week' :
					$fdow = intval( get_option( 'start_of_week' ) );
					$dow  = intval( date( 'w', strtotime( $server_user_daystart_datetime ) ) );
					$d    = $dow - $fdow;
					$from = date( 'Y-m-d H:i:s', mktime( 0, 0, 0, date( 'm', strtotime( $server_user_daystart_datetime ) )  , date( 'd', strtotime( $server_user_daystart_datetime ) )- $d, date( 'Y', strtotime( $server_user_daystart_datetime ) ) ) );
					break;
				case 'month' :
					$from = date( 'Y-m', strtotime( $server_user_daystart_datetime ) ) . '-01 00:00:00';
					break;
				default :
					$from = null;
			}
		}
	}

	/**
	 * Hits shortcode - renders the number of hits.
	 * 
	 * @param array $atts attributes
	 * @param string $content not used
	 */
	public static function affiliates_hits( $atts, $content = null ) {
		global $wpdb;

		include_once( AFFILIATES_CORE_LIB . '/class-affiliates-date-helper.php');

		remove_shortcode( 'affiliates_hits' );
		$content = do_shortcode( $content );
		add_shortcode( 'affiliates_hits', array( __CLASS__, 'affiliates_hits' ) );

		$output = "";

		$options = shortcode_atts(
			array(
				'from'  => null,
				'until' => null,
				'for'   => null
			),
			$atts
		);
		extract( $options );
		self::for_from_until( $for, $from, $until );
		$user_id = get_current_user_id();
		if ( $user_id && affiliates_user_is_affiliate( $user_id ) ) {
			$affiliates_table = _affiliates_get_tablename( 'affiliates' );
			$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
			if ( $affiliate_id = $wpdb->get_var( $wpdb->prepare(
				"SELECT $affiliates_users_table.affiliate_id FROM $affiliates_users_table LEFT JOIN $affiliates_table ON $affiliates_users_table.affiliate_id = $affiliates_table.affiliate_id WHERE $affiliates_users_table.user_id = %d AND $affiliates_table.status = 'active'",
				intval( $user_id )
			))) {
				$output .= affiliates_get_affiliate_hits( $affiliate_id, $from, $until, true );
			}
		}
		return $output;
	}

	/**
	 * Visits shortcode - renders the number of visits.
	 *
	 * @param array $atts attributes
	 * @param string $content not used
	 */
	public static function affiliates_visits( $atts, $content = null ) {

		global $wpdb;

		remove_shortcode( 'affiliates_visits' );
		$content = do_shortcode( $content );
		add_shortcode( 'affiliates_visits', array( __CLASS__, 'affiliates_visits' ) );

		$output = "";
		$options = shortcode_atts(
			array(
				'from'  => null,
				'until' => null,
				'for'   => null
			),
			$atts
		);
		extract( $options );
		self::for_from_until( $for, $from, $until );
		$user_id = get_current_user_id();
		if ( $user_id && affiliates_user_is_affiliate( $user_id ) ) {
			$affiliates_table = _affiliates_get_tablename( 'affiliates' );
			$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
			if ( $affiliate_id = $wpdb->get_var( $wpdb->prepare(
				"SELECT $affiliates_users_table.affiliate_id FROM $affiliates_users_table LEFT JOIN $affiliates_table ON $affiliates_users_table.affiliate_id = $affiliates_table.affiliate_id WHERE $affiliates_users_table.user_id = %d AND $affiliates_table.status = 'active'",
				intval( $user_id )
			) ) ) {
				$output .= affiliates_get_affiliate_visits( $affiliate_id, $from, $until, true );
			}
		}
		return $output;
	}

	/**
	 * Referrals shortcode - renders referral information.
	 *
	 * @param array $atts attributes
	 * @param string $content not used
	 */
	public static function affiliates_referrals( $atts, $content = null ) {
		global $wpdb;

		remove_shortcode( 'affiliates_referrals' );
		$content = do_shortcode( $content );
		add_shortcode( 'affiliates_referrals', array( __CLASS__, 'affiliates_referrals' ) );

		$output = "";
		$options = shortcode_atts(
			array(
				'status'   => null,
				'from'     => null,
				'until'    => null,
				'show'     => 'count',
				'currency' => null,
				'for'      => null,
				'if_empty' => null
			),
			$atts
		);
		extract( $options );
		self::for_from_until( $for, $from, $until );
		$user_id = get_current_user_id();
		if ( $user_id && affiliates_user_is_affiliate( $user_id ) ) {
			$affiliates_table = _affiliates_get_tablename( 'affiliates' );
			$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
			if ( $affiliate_id = $wpdb->get_var( $wpdb->prepare(
				"SELECT $affiliates_users_table.affiliate_id FROM $affiliates_users_table LEFT JOIN $affiliates_table ON $affiliates_users_table.affiliate_id = $affiliates_table.affiliate_id WHERE $affiliates_users_table.user_id = %d AND $affiliates_table.status = 'active'",
				intval( $user_id )
			))) {
				switch ( $show ) {
					case 'count' :
						switch ( $status ) {
							case null :
							case AFFILIATES_REFERRAL_STATUS_ACCEPTED :
							case AFFILIATES_REFERRAL_STATUS_CLOSED :
							case AFFILIATES_REFERRAL_STATUS_PENDING :
							case AFFILIATES_REFERRAL_STATUS_REJECTED :
								$referrals = affiliates_get_affiliate_referrals( $affiliate_id, $from, $until, $status );
								break;
							default :
								$referrals = "";
						}
						$output .= $referrals;
						break;
					case 'total' :
						if ( $totals = self::get_total( $affiliate_id, $from, $until, $status ) ) {
							if ( count( $totals ) > 0 ) {
								$output .= '<ul>';
								foreach ( $totals as $currency_id => $total ) {
									$output .= '<li>';
									$output .= $currency_id;
									$output .= '&nbsp;';
									$output .= $total;
									$output .= '</li>';
								}
								$output .= '</ul>';
							}
						}
						if ( !$totals || count( $totals ) === 0 ) {
							if ( $if_empty !== null ) {
								$output .= '<ul>';
								$output .= '<li>';
								$output .= wp_filter_nohtml_kses( $if_empty );
								$output .= '</li>';
								$output .= '</ul>';
							}
						}
						break;
				}
			}
		}
		return $output;
	}

	/**
	 * Shows monthly earnings.
	 * 
	 * Note that we don't do any s2u or u2s date adjustments here.
	 * 
	 * @param array $atts not used; this shortcode does not accept any arguments
	 * @param string $content not used
	 */
	public static function affiliates_earnings( $atts, $content = null ) {
		global $wpdb;
		$output = '';
		$user_id = get_current_user_id();
		if ( $user_id && affiliates_user_is_affiliate( $user_id ) ) {
			if ( $affiliate_ids = affiliates_get_user_affiliate( $user_id ) ) {

				$output .= '<table class="affiliates-earnings">';
				$output .= '<thead>';
				$output .= '<tr>';
				$output .= '<th>';
				$output .= __( 'Month', AFFILIATES_PLUGIN_DOMAIN );
				$output .= '</th>';
				$output .= '<th>';
				$output .= __( 'Earnings', AFFILIATES_PLUGIN_DOMAIN );
				$output .= '</th>';
				$output .= '</tr>';
				$output .= '</thead>';
				$output .= '<tbody>';

				$referrals_table = _affiliates_get_tablename( 'referrals' );
				if ( $range = $wpdb->get_row( "SELECT MIN(datetime) from_datetime, MAX(datetime) thru_datetime FROM $referrals_table WHERE affiliate_id IN (" . implode( ',', $affiliate_ids ) . ")") ) {
					if ( !empty( $range->from_datetime ) ) { // Covers for NULL when no referrals recorded yet, too.
						$t = strtotime( $range->from_datetime );
						$eom = strtotime( date( 'Y-m-t 23:59:59', time() ) );
						while ( $t < $eom ) {
							$from = date( 'Y-m', $t ) . '-01 00:00:00';
							$thru = date( 'Y-m-t', strtotime( $from ) );
							$sums = array();
							foreach( $affiliate_ids as $affiliate_id ) {
								if ( $totals = self::get_total( $affiliate_id, $from, $thru ) ) {
									if ( count( $totals ) > 0 ) {
										foreach ( $totals as $currency_id => $total ) {
											$sums[$currency_id] = isset( $sums[$currency_id] ) ? bcadd( $sums[$currency_id], $total, AFFILIATES_REFERRAL_AMOUNT_DECIMALS ) : $total;
										}
									}
								}
							}
	
							$output .= '<tr>';
	
							// month & year
							$output .= '<td>';
							$output .= date( __( 'F Y', AFFILIATES_PLUGIN_DOMAIN ), strtotime( $from ) ); // translators: date format; month and year for earnings display
							$output .= '</td>';
	
							// earnings
							$output .= '<td>';
							if ( count( $sums ) > 1 ) {
								$output .= '<ul>';
								foreach ( $sums as $currency_id => $total ) {
									$output .= '<li>';
									$output .= apply_filters( 'affiliates_earnings_display_currency', $currency_id );
									$output .= '&nbsp;';
									$output .= apply_filters( 'affiliates_earnings_display_total', number_format_i18n( $total, apply_filters( 'affiliates_earnings_decimals', 2 ) ), $total, $currency_id );
									$output .= '</li>';
								}
								$output .= '</ul>';
							} else if ( count( $sums ) > 0 ) {
								$output .= apply_filters( 'affiliates_earnings_display_currency', $currency_id );
								$output .= '&nbsp;';
								$output .= apply_filters( 'affiliates_earnings_display_total', number_format_i18n( $total, apply_filters( 'affiliates_earnings_decimals', 2 ) ), $total, $currency_id );
							} else {
								$output .= apply_filters( 'affiliates_earnings_display_total_none', __( 'None', AFFILIATES_PLUGIN_DOMAIN ) );
							}
							$output .= '</td>';
	
							$output .= '</tr>';
	
							$t = strtotime( '+1 month', $t );
						}
					} else {
						$output .= '<td colspan="2">';
						$output .= apply_filters( 'affiliates_earnings_display_total_no_earnings', __( 'There are no earnings yet.', AFFILIATES_PLUGIN_DOMAIN ) );
						$output .= '</td>';
					}
				}

				$output .= '</tbody>';
				$output .= '</table>';

			}
		}
		return $output;

	}

	/**
	 * Retrieve totals for an affiliate.
	 * 
	 * @param int $affiliate_id
	 * @param string $from_date
	 * @param string $thru_date
	 * @param string $status
	 * @return array of totals indexed by currency_id or false on error
	 */
	public static function get_total( $affiliate_id, $from_date = null , $thru_date = null, $status = null ) {
		global $wpdb;
		$referrals_table = _affiliates_get_tablename( 'referrals' );
		$where = " WHERE affiliate_id = %d";
		$values = array( $affiliate_id );
		if ( $from_date ) {
			$from_date = date( 'Y-m-d', strtotime( $from_date ) );
		}
		if ( $thru_date ) {
			$thru_date = date( 'Y-m-d', strtotime( $thru_date ) + 24*3600 );
		}
		if ( $from_date && $thru_date ) {
			$where .= " AND datetime >= %s AND datetime < %s ";
			$values[] = $from_date;
			$values[] = $thru_date;
		} else if ( $from_date ) {
			$where .= " AND datetime >= %s ";
			$values[] = $from_date;
		} else if ( $thru_date ) {
			$where .= " AND datetime < %s ";
			$values[] = $thru_date;
		}
		if ( !empty( $status ) ) {
			$where .= " AND status = %s ";
			$values[] = $status;
		} else {
			$where .= " AND status IN ( %s, %s ) ";
			$values[] = AFFILIATES_REFERRAL_STATUS_ACCEPTED;
			$values[] = AFFILIATES_REFERRAL_STATUS_CLOSED;
		}
		$totals = $wpdb->get_results( $wpdb->prepare(
			"SELECT SUM(amount) total, currency_id FROM $referrals_table
			$where
			GROUP BY currency_id
			",
			$values
		) );
		if ( $totals ) {
			$result = array();
			foreach( $totals as $total ) {
				if ( ( $total->currency_id !== null ) && ( $total->total !== null ) ) {
					$result[$total->currency_id] = $total->total;
				}
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * URL shortcode - renders the affiliate url.
	 *
	 * @param array $atts attributes
	 * @param string $content (is not used)
	 */
	public static function affiliates_url( $atts, $content = null ) {
		global $wpdb;

		$pname = get_option( 'aff_pname', AFFILIATES_PNAME );

		remove_shortcode( 'affiliates_url' );
		$content = do_shortcode( $content );
		add_shortcode( 'affiliates_url', array( __CLASS__, 'affiliates_url' ) );

		$output = "";
		$user_id = get_current_user_id();
		if ( $user_id && affiliates_user_is_affiliate( $user_id ) ) {
			$affiliates_table = _affiliates_get_tablename( 'affiliates' );
			$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
			if ( $affiliate_id = $wpdb->get_var( $wpdb->prepare(
				"SELECT $affiliates_users_table.affiliate_id FROM $affiliates_users_table LEFT JOIN $affiliates_table ON $affiliates_users_table.affiliate_id = $affiliates_table.affiliate_id WHERE $affiliates_users_table.user_id = %d AND $affiliates_table.status = 'active'",
				intval( $user_id )
			))) {
				$encoded_affiliate_id = affiliates_encode_affiliate_id( $affiliate_id );
				if ( strlen( $content ) == 0 ) {
					$base_url = get_bloginfo( 'url' );
				} else {
					$base_url = $content;
				}
				$separator = '?';
				$url_query = parse_url( $base_url, PHP_URL_QUERY );
				if ( !empty( $url_query ) ) {
					$separator = '&';
				}
				$output .= $base_url . $separator . $pname . '=' . $encoded_affiliate_id;
			}
		}
		return $output;
	}

	/**
	 * Renders a login form that can redirect to a url or the current page.
	 * 
	 * @param array $atts
	 * @param string $content
	 * @return string rendered form
	 */
	function affiliates_login_redirect( $atts, $content = null ) {
		extract( shortcode_atts( array( 'redirect_url' => '' ), $atts ) );
		$form = '';
		if ( !is_user_logged_in() ) {
			if ( empty( $redirect_url ) ) {
				$redirect_url = get_permalink();
			}
			$form = wp_login_form( array( 'echo' => false, 'redirect' => $redirect_url ) );
		}
		return $form;
	}

	/**
	 * Renders a link to log out.
	 * 
	 * @param array $atts
	 * @param string $content not used
	 * @return string rendered logout link or empty if not logged in
	 */
	function affiliates_logout( $atts, $content = null ) {
		if ( is_user_logged_in() ) {
			return '<a href="' . esc_url( wp_logout_url() ) .'">' . __( 'Log out', AFFILIATES_PLUGIN_DOMAIN ) . '</a>';
		} else {
			return '';
		}
	}
}
Affiliates_Shortcodes::init();
