<?php
/**
 * class-affiliates-utility.php
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
 * @since affiliates 1.1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides utility methods.
 */
class Affiliates_Utility {
		
	/**
	 * @var string captcha field id
	 */
	private static $captcha_field_id = 'lmfao';
	
	static function get_captcha_field_id() {
		return self::$captcha_field_id;
	}
		
	/**
	 * Filters mail header injection, html, ... 
	 * @param string $unfiltered_value
	 */
	static function filter( $unfiltered_value ) {
		$mail_filtered_value = preg_replace('/(%0A|%0D|content-type:|to:|cc:|bcc:)/i', '', $unfiltered_value );
		return stripslashes( wp_filter_nohtml_kses( Affiliates_Utility::filter_xss( trim( strip_tags( $mail_filtered_value ) ) ) ) );
	}
	
	/**
	 * Filter xss
	 * 
	 * @param string $string input
	 * @return filtered string
	 */
	static function filter_xss( $string ) {
		// Remove NUL characters (ignored by some browsers)
		$string = str_replace(chr(0), '', $string);
		// Remove Netscape 4 JS entities
		$string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);
		
		// Defuse all HTML entities
		$string = str_replace('&', '&amp;', $string);
		// Change back only well-formed entities in our whitelist
		// Decimal numeric entities
		$string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
		// Hexadecimal numeric entities
		$string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
		// Named entities
		$string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);
		return preg_replace('%
		(
		<(?=[^a-zA-Z!/])  # a lone <
		|                 # or
		<[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
		|                 # or
		>                 # just a >
		)%x', '', $string);
	}
		
	/**
	 * Returns captcha field markup.
	 * 
	 * @return captcha field markup
	 */
	static function captcha_get( $value ) {
		$style = 'display:none;';
		$field = '<input name="' . Affiliates_Utility::$captcha_field_id . '" id="' . Affiliates_Utility::$captcha_field_id . '" class="' . Affiliates_Utility::$captcha_field_id . ' field" style="' . $style . '" value="' . esc_attr( $value ) . '" type="text"/>';
		$field = apply_filters( 'affiliates_captcha_get', $field, $value );
		return $field;
	}

	/**
	 * Validates a captcha field.
	 * 
	 * @param string $field_value field content
	 * @return true if the field validates
	 */
	static function captcha_validates( $field_value = null ) {
		$result = false;
		if ( empty( $field_value ) ) {
			$result = true;
		}
		$result = apply_filters( 'affiliates_captcha_validate', $result, $field_value );
		return $result;
	}
	
	/**
	 * Retrieves the first post that contains $title.
	 * @param string $title what to search in titles for
	 * @param string $output Optional, default is Object. Either OBJECT, ARRAY_A, or ARRAY_N.
	 * @param string $post_type Optional, default is null meaning any post type.
	 */
	static function get_post_by_title( $title, $output = OBJECT, $post_type = null ) {
		global $wpdb;
		$post = null;
		if ( $post_type == null ) {
			$query = $wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_title LIKE '%%%s%%'",
				$title
			);
		} else {
			$query = $wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_title LIKE '%%%s%%' AND post_type= %s",
				$title,
				$post_type
			);
		}
		$result = $wpdb->get_row( $query );
		if ( !empty( $result ) ) {
			$post_id = $result->ID;
			$post = get_post( $post_id, $output );
		}
		return $post;
	}
	
	/**
	 * Verifies and returns formatted amount.
	 * @param string $amount
	 * @return string amount, false upon error or wrong format
	 */
	static function verify_referral_amount( $amount ) {
		$result = false;
		if ( preg_match( "/([0-9,]+)?(\.[0-9]+)?/", $amount, $matches ) ) {
			if ( isset( $matches[1] ) ) {
				$n = str_replace(",", "", $matches[1] );
			} else {
				$n = "0";
			}
			if ( isset( $matches[2] ) ) {
				// exceeding decimals are TRUNCATED
				$d = substr( $matches[2], 1, AFFILIATES_REFERRAL_AMOUNT_DECIMALS );
			} else {
				$d = "0";
			}
			if ( isset( $matches[1] ) || isset( $matches[2] ) ) {
				$result = $n . "." . $d;
			}
		}
		return $result;
	}
	
	/**
	 * Verify and return currency id.
	 * @param string $currency_id
	 * @return string currency id or false on error
	 */
	static function verify_currency_id( $currency_id ) {
		if ( !empty( $currency_id ) ) {
			return substr( trim( strtoupper( $currency_id ) ), 0, AFFILIATES_REFERRAL_CURRENCY_ID_LENGTH );
		} else {
			return false;
		}
	}
	
	/**
	 * Verifies states and transition.
	 * 
	 * @param string $old_status
	 * @param string $new_status
	 * @return new status or false on failure to verify
	 */
	static function verify_referral_status_transition( $old_status, $new_status ) {
		$result = false;
		switch ( $old_status ) {
			case AFFILIATES_REFERRAL_STATUS_ACCEPTED :
			case AFFILIATES_REFERRAL_STATUS_CLOSED :
			case AFFILIATES_REFERRAL_STATUS_PENDING :
			case AFFILIATES_REFERRAL_STATUS_REJECTED :
				switch ( $new_status ) {
					case AFFILIATES_REFERRAL_STATUS_ACCEPTED :
					case AFFILIATES_REFERRAL_STATUS_CLOSED :
					case AFFILIATES_REFERRAL_STATUS_PENDING :
					case AFFILIATES_REFERRAL_STATUS_REJECTED :
						$result = $new_status;
						break; 
				}
				break;
		}
		return $result;
	}

	/**
	 * Verifies affiliate states.
	 *
	 * @param string $status
	 * @return status or false on failure to verify
	 */
	static function verify_affiliate_status( $status ) {
		$result = false;
		switch ( $status ) {
			case AFFILIATES_AFFILIATE_STATUS_ACTIVE :
			case AFFILIATES_AFFILIATE_STATUS_PENDING :
			case AFFILIATES_AFFILIATE_STATUS_DELETED :
				$result = $status;
				break;
		}
		return $result;
	}

}// class Affiliates_Utility
