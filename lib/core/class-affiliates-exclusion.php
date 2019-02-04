<?php
/**
 * class-affiliates-exclusion.php
 *
 * Copyright (c) 2010 - 2015 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 2.8.0
 */

class Affiliates_Exclusion {

	private static $ap_priority = false;

	public static function init() {
		if ( get_option( 'aff_allow_auto', 'no' ) == 'no' ) {
			add_filter( 'affiliates_service_affiliate_id', array( __CLASS__, 'service' ), 999, 2 );
// 			add_filter( 'affiliates_coupon_affiliate_id', array( __CLASS__, 'coupon' ), 999, 2 );
		}
		if ( get_option( 'aff_allow_auto_coupons', 'no' ) == 'no' ) {
			add_filter( 'woocommerce_coupon_is_valid', array( __CLASS__, 'woocommerce_coupon_is_valid' ), 999, 2 );
			add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'woocommerce_after_checkout_validation' ), 999 );
		}
	}

	/**
	 * Service hook.
	 * @param int $affiliate_id
	 * @param string $service
	 * @return int affiliate id or null
	 */
	public static function service( $affiliate_id, $service = null ) {
		return self::validate( $affiliate_id );
	}

	/**
	 * Coupon hook.
	 * @param int $affiliate_id
	 * @param string $coupon
	 * @return int affiliate id or null
	 */
	public static function coupon( $affiliate_id, $coupon ) {
		return self::validate( $affiliate_id );
	}

	/**
	 * Returns null if the user of the affiliate is the same as the current user.
	 * @param int $affiliate_id
	 * @return int affiliate id or null
	 */
	public static function validate( $affiliate_id ) {
		$result = $affiliate_id;
		if ( $affiliate_id !== null ) {
			if ( $affiliate = affiliates_get_affiliate( $affiliate_id ) ) {
				if ( $user_id = affiliates_get_affiliate_user( $affiliate_id ) ) {
					if ( (int) $user_id === (int) get_current_user_id() ) {
						$result = null;
					} else {
						if ( !empty( $affiliate['email'] ) ) {
							if ( $user = get_user_by( 'email', $affiliate['email'] ) ) {
								if ( (int) $user->ID === (int) get_current_user_id() ) {
									$result = null;
								}
							}
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Invalidate affiliate's own coupons.
	 * 
	 * @param boolean $valid
	 * @param WC_Coupons $coupon
	 * @return boolean
	 */
	public static function woocommerce_coupon_is_valid( $valid, $coupon ) {

		global $woocommerce;

		$code = method_exists( $coupon, 'get_code' ) ? $coupon->get_code() : $coupon->code;
		$id   = method_exists( $coupon, 'get_id' ) ? $coupon->get_id() : $coupon->id;

		// Only perform checks if the coupon is valid at this stage.
		if ( $valid && !empty( $coupon ) && !empty( $id ) && !empty( $code ) ) {
			if ( method_exists( 'Affiliates_Attributes_WordPress', 'get_affiliate_for_coupon' ) ) {
				self::remove_filters();
				if ( $affiliate_id = Affiliates_Attributes_WordPress::get_affiliate_for_coupon( $code ) ) {
					if ( $user_id = get_current_user_id() ) {
						if ( $affiliate_ids = affiliates_get_user_affiliate( $user_id ) ) {
							if ( in_array( $affiliate_id, $affiliate_ids ) ) {
								$valid = false;
							}
						}
					}
				}
				self::add_filters();
			}
		}
		return $valid;
	}

	/**
	 * Performs coupon checks after checkout validation.
	 *
	 * @param array $posted posted form data
	 */
	public static function woocommerce_after_checkout_validation( $posted ) {

		global $woocommerce;

		if ( isset( $woocommerce->cart ) ) {
			$cart = $woocommerce->cart;
			if ( ! empty( $cart->applied_coupons ) ) {
				if ( method_exists( 'Affiliates_Attributes_WordPress', 'get_affiliate_for_coupon' ) ) {

					$valid = true;

					$emails = array( $posted['billing_email'] );
					if ( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$emails[] = $current_user->user_email;
					}
					$emails = array_map( 'sanitize_email', array_map( 'strtolower', $emails ) );

					self::remove_filters();
					foreach ( $cart->applied_coupons as $key => $code ) {
						$coupon = new WC_Coupon( $code );
						if ( ! is_wp_error( $coupon->is_valid() ) ) {

							$coupon_code = method_exists( $coupon, 'get_code' ) ? $coupon->get_code() : $coupon->code;

							if ( $affiliate_id = Affiliates_Attributes_WordPress::get_affiliate_for_coupon( $coupon_code ) ) {
								if ( $user_id = get_current_user_id() ) {
									if ( $affiliate_ids = affiliates_get_user_affiliate( $user_id ) ) {
										if ( in_array( $affiliate_id, $affiliate_ids ) ) {
											$valid = false;
											break;
										}
									}
								}
								if ( $affiliate = affiliates_get_affiliate( $affiliate_id ) ) {
									if ( isset( $affiliate['email'] ) && in_array( strtolower( $affiliate['email'] ), $emails ) ) {
										$valid = false;
										break;
									}
								}
							}
						}
					}
					self::add_filters();

					if ( !$valid ) {
						$coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_INVALID_REMOVED );
						unset( $cart->applied_coupons[ $key ] );
						$woocommerce->session->coupon_codes   = $cart->applied_coupons;
						$woocommerce->session->refresh_totals = true;
					}
				}
			}
		}

	}

	/**
	 * These filters must be removed so we can get the affiliate id without
	 * their methods interfering when using get_affiliate_for_coupon().
	 */
	private static function remove_filters() {
		self::$ap_priority = has_filter( 'affiliates_coupon_affiliate_id', array( 'Affiliates_Permanent', 'affiliates_coupon_affiliate_id' ) );
		if ( self::$ap_priority !== false ) {
			remove_filter( 'affiliates_coupon_affiliate_id', array( 'Affiliates_Permanent', 'affiliates_coupon_affiliate_id' ), self::$ap_priority, 2 );
		}
		remove_filter( 'affiliates_coupon_affiliate_id', array( __CLASS__, 'coupon' ), 999 );
	}

	/**
	 * Add the filters back.
	 */
	private static function add_filters() {
		if ( self::$ap_priority !== false ) {
			add_filter( 'affiliates_coupon_affiliate_id', array( 'Affiliates_Permanent', 'affiliates_coupon_affiliate_id' ), self::$ap_priority, 2 );
		}
		self::$ap_priority = false;
		add_filter( 'affiliates_coupon_affiliate_id', array( __CLASS__, 'coupon' ), 999, 2 );
	}

}
Affiliates_Exclusion::init();
