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

/**
 * Provides service-related methods.
 */
class Affiliates_Service {

	/**
	 * Obtain the referring affiliate's id.
	 * @param string $service by name
	 * @return int affiliate id or false if none applies
	 */
	public static function get_referrer_id( $service = null ) {
		$affiliate_id = false;
		switch ( $service ) {
			default :
				if ( isset( $_COOKIE[AFFILIATES_COOKIE_NAME] ) ) {
					$affiliate_id = affiliates_check_affiliate_id_encoded( trim( $_COOKIE[AFFILIATES_COOKIE_NAME] ) );
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

}
