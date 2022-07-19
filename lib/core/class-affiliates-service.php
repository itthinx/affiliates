<?php
/**
 * class-affiliates-service.php
 *
 * Copyright (c) 2010, 2011, 2012 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 2.1.1
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides service-related methods.
 */
class Affiliates_Service {

	/**
	 * Obtain the referring affiliate's id.
	 * @param string $service by name
	 *
	 * @return int affiliate id or false if none applies
	 */
	public static function get_referrer_id( $service = null ) {
		global $affiliates_request_encoded_id;
		$affiliate_id = false;
		switch ( $service ) {
			default :
				$value = null;
				if ( !empty( $affiliates_request_encoded_id ) ) {
					$value = trim( $affiliates_request_encoded_id );
				} else if ( isset( $_COOKIE[AFFILIATES_COOKIE_NAME] ) ) {
					$value = trim( $_COOKIE[AFFILIATES_COOKIE_NAME] );
				}
				if ( !empty( $value ) ) {
					if ( ( $dot = strpos( $value, '.' ) ) === false ) {
						$affiliate_id = affiliates_check_affiliate_id_encoded( $value );
					} else {
						if ( ( $dot > 0 ) && ( $dot < strlen( $value ) - 1 ) ) {
							$affiliate_id = affiliates_check_affiliate_id_encoded( substr( $value, 0, $dot ) );
						}
					}
				}
		}
		if ( !$affiliate_id ) {
			if ( get_option( 'aff_use_direct', false ) ) {
				// Assume a direct referral
				$affiliate_id = affiliates_get_direct_id();
			}
		}
		return apply_filters( 'affiliates_service_affiliate_id', $affiliate_id, $service );
	}

	/**
	 * Obtain the referrer's campaign's id.
	 *
	 * @param string $service
	 *
	 * @return int campaign id or false if none applies
	 */
	public static function get_campaign_id( $service = null ) {
		global $affiliates_request_encoded_id;
		$campaign_id = false;
		switch ( $service ) {
			default :
				if ( isset( $_COOKIE[AFFILIATES_COOKIE_NAME] ) ) {
					$value = trim( $_COOKIE[AFFILIATES_COOKIE_NAME] );
					if ( ( $dot = strpos( $value, '.' ) ) !== false ) {
						if ( ( $dot > 0 ) && ( $dot < strlen( $value ) - 1 ) ) {
							if ( $affiliate_id = affiliates_check_affiliate_id_encoded( substr( $value, 0, $dot ) ) ) {
								$_campaign_id  = substr( $value, $dot + 1 );
								if ( class_exists( 'Affiliates_Campaign' ) && method_exists( 'Affiliates_Campaign', 'is_affiliate_campaign' ) ) {
									if ( Affiliates_Campaign::is_affiliate_campaign( $affiliate_id, $_campaign_id ) ) {
										$campaign_id = $_campaign_id;
									}
								}
							}
						}
					}
				}
		}
		return apply_filters( 'affiliates_service_campaign_id', $campaign_id, $service );
	}

	/**
	 * Returns the affiliate and campaign ids if present.
	 *
	 * @param string $service
	 *
	 * @return array or null
	 */
	public static function get_ids( $service = null ) {
		$affiliate_id = null;
		$campaign_id  = null;
		$result = null;
		switch ( $service ) {
			default :
				if ( isset( $_COOKIE[AFFILIATES_COOKIE_NAME] ) ) {
					$value = trim( $_COOKIE[AFFILIATES_COOKIE_NAME] );
					if ( ( $dot = strpos( $value, '.' ) ) === false ) {
						$affiliate_id = affiliates_check_affiliate_id_encoded( $value );
					} else {
						if ( ( $dot > 0 ) && ( $dot < strlen( $value ) - 1 ) ) {
							if ( $affiliate_id = affiliates_check_affiliate_id_encoded( substr( $value, 0, $dot ) ) ) {
								$_campaign_id  = substr( $value, $dot + 1 );
								if ( class_exists( 'Affiliates_Campaign' ) && method_exists( 'Affiliates_Campaign', 'is_affiliate_campaign' ) ) {
									if ( Affiliates_Campaign::is_affiliate_campaign( $affiliate_id, $_campaign_id ) ) {
										$campaign_id = $_campaign_id;
									}
								}
							}
						}
					}
				}
		}
		if ( !$affiliate_id ) {
			if ( get_option( 'aff_use_direct', false ) ) {
				// Assume a direct referral
				$affiliate_id = affiliates_get_direct_id();
			}
		}
		if ( $affiliate_id ) {
			$result = array(
				'affiliate_id' => $affiliate_id,
				'campaign_id'  => $campaign_id
			);
		}
		return apply_filters( 'affiliates_service_ids', $result, $service );
	}

	/**
	 * Returns the hit ID based on hash if present and valid, otherwise null.
	 *
	 * @param string $service
	 *
	 * @return int hit ID or null
	 */
	public static function get_hit_id( $service = null ) {

		global $wpdb;

		$result = null;
		switch ( $service ) {
			default :
				if ( isset( $_COOKIE[AFFILIATES_HASH_COOKIE_NAME] ) ) {
					$affiliate_id = self::get_referrer_id( $service );
					$hash = trim( $_COOKIE[AFFILIATES_HASH_COOKIE_NAME] );
					$hits_table = _affiliates_get_tablename( 'hits' );
					$row = $wpdb->get_row( $wpdb->prepare(
						"SELECT hit_id, hash, affiliate_id FROM $hits_table WHERE affiliate_id = %d AND hash = %s",
						intval( $affiliate_id ),
						$hash
					) );
					if ( $row && !empty( $row->hit_id ) ) {
						$result = $row->hit_id;
					}
				}
		}
		return $result;
	}
}
