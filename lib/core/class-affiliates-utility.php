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

// phpcs:disable WordPress.WP.AlternativeFunctions.strip_tags_strip_tags

/**
 * Provides utility methods.
 */
class Affiliates_Utility {

	/**
	 * @var string captcha field id
	 */
	private static $captcha_field_id = 'lmfao';

	public static function get_captcha_field_id() {
		return self::$captcha_field_id;
	}

	/**
	 * Filters mail header injection, html, ...
	 * @param string $unfiltered_value
	 */
	public static function filter( $unfiltered_value ) {
		$mail_filtered_value = preg_replace('/(%0A|%0D|content-type:|to:|cc:|bcc:)/i', '', $unfiltered_value );
		return stripslashes( wp_filter_nohtml_kses( Affiliates_Utility::filter_xss( trim( strip_tags( $mail_filtered_value ) ) ) ) );
	}

	/**
	 * Filter xss
	 *
	 * @param string $string input
	 *
	 * @return string filtered string
	 */
	public static function filter_xss( $string ) {
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
	 * @return string captcha field markup
	 */
	public static function captcha_get( $value ) {
		$style = 'display:none;';
		$field = '<input name="' . Affiliates_Utility::$captcha_field_id . '" id="' . Affiliates_Utility::$captcha_field_id . '" class="' . Affiliates_Utility::$captcha_field_id . ' field" style="' . $style . '" value="' . esc_attr( $value ) . '" type="text"/>';
		$field = apply_filters( 'affiliates_captcha_get', $field, $value );
		return $field;
	}

	/**
	 * Validates a captcha field.
	 *
	 * @param string $field_value field content
	 *
	 * @return true if the field validates
	 */
	public static function captcha_validates( $field_value = null ) {
		$result = false;
		if ( empty( $field_value ) ) {
			$result = true;
		}
		$result = apply_filters( 'affiliates_captcha_validate', $result, $field_value );
		return $result;
	}

	/**
	 * Retrieves the first post that contains $title.
	 *
	 * @param string $title what to search in titles for
	 * @param string $output Optional, default is Object. Either OBJECT, ARRAY_A, or ARRAY_N.
	 * @param string $post_type Optional, default is null meaning any post type.
	 */
	public static function get_post_by_title( $title, $output = OBJECT, $post_type = null ) {
		global $wpdb;
		$post = null;
		if ( $post_type == null ) {
			$query = $wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_title LIKE %s",
				'%' . $wpdb->esc_like( $title ) . '%'
			);
		} else {
			$query = $wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_title LIKE %s AND post_type= %s",
				'%' . $wpdb->esc_like( $title ) . '%',
				$post_type
			);
		}
		$result = $wpdb->get_row( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( !empty( $result ) ) {
			$post_id = $result->ID;
			$post = get_post( $post_id, $output );
		}
		return $post;
	}

	/**
	 * Verifies and returns formatted amount.
	 *
	 * @param string $amount
	 *
	 * @return string amount, false upon error or wrong format
	 */
	public static function verify_referral_amount( $amount ) {
		$result = false;
		if ( is_numeric( $amount ) ) {
			$amount = sprintf( '%.' . ( affiliates_get_referral_amount_decimals() + 1 ) . 'F', $amount );
			if ( preg_match( "/([0-9,]+)?(\.[0-9]+)?/", $amount, $matches ) ) {
				if ( isset( $matches[1] ) ) {
					$n = str_replace(",", "", $matches[1] );
				} else {
					$n = "0";
				}
				if ( isset( $matches[2] ) ) {
					// exceeding decimals are TRUNCATED
					$d = substr( $matches[2], 1, affiliates_get_referral_amount_decimals() );
				} else {
					$d = "0";
				}
				if ( isset( $matches[1] ) || isset( $matches[2] ) ) {
					$result = $n . "." . $d;
				}
			}
		}
		return $result;
	}

	/**
	 * Verify and return currency id.
	 *
	 * @param string $currency_id
	 *
	 * @return string currency id or false on error
	 */
	public static function verify_currency_id( $currency_id ) {
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
	 *
	 * @return string|boolean new status or false on failure to verify
	 */
	public static function verify_referral_status_transition( $old_status, $new_status ) {
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
	 *
	 * @return string|boolean status or false on failure to verify
	 */
	public static function verify_affiliate_status( $status ) {
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

	/**
	 * Unslash, sanitize and verify nonce.
	 *
	 * @since 7.0.0
	 *
	 * @see wp_unslash()
	 * @see sanitize_text_field()
	 * @see wp_verify_nonce()
	 *
	 * @param string $nonce nonce value
	 * @param string|number $action
	 *
	 * @return int|boolean
	 */
	public static function verify_nonce( $nonce, $action = -1 ) {
		return wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), $action );
	}
	
	/**
	 * Unslash, sanitize and verify named nonce provided via $_POST.
	 *
	 * @since 7.0.0
	 *
	 * @param string $name nonce name
	 * @param string|number $action
	 *
	 * @return int|boolean
	 */
	public static function verify_post_nonce( $name, $action = -1 ) {
		$result = false;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST[$name] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$result = self::verify_nonce( $_POST[$name], $action );
		}
		return $result;
	}
	
	/**
	 * Unslash, sanitize and verify named nonce provided via $_GET.
	 *
	 * @since 7.0.0
	 *
	 * @param string $name nonce name
	 * @param string|number $action
	 *
	 * @return int|boolean
	 */
	public static function verify_get_nonce( $name, $action = -1 ) {
		$result = false;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[$name] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$result = self::verify_nonce( $_GET[$name], $action );
		}
		return $result;
	}
	
	/**
	 * Unslash, sanitize and verify named nonce provided via $_REQUEST.
	 *
	 * @since 7.0.0
	 *
	 * @param string $name nonce name
	 * @param string|number $action
	 *
	 * @return int|boolean
	 */
	public static function verify_request_nonce( $name, $action = -1 ) {
		$result = false;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST[$name] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$result = self::verify_nonce( $_REQUEST[$name], $action );
		}
		return $result;
	}

	/**
	 * Provide the current URL, sanitized.
	 *
	 * @since 7.0.0
	 *
	 * @return string
	 */
	public static function get_current_url() {
		$host = wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$uri  = wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return sanitize_url( ( is_ssl() ? 'https://' : 'http://' ) . $host . $uri );
	}

	/**
	 * Sanitize the given input value.
	 *
	 * Applies wp_unslash() and then sanitize_text_field().
	 *
	 * Preserves the original type of the value.
	 *
	 * @since 7.0.0
	 *
	 * @param string|number|boolean|array $value
	 *
	 * @return null|string|boolean|array
	 */
	public static function sanitize_input( $value ) {
		$result = null;
		if ( is_numeric( $value ) || is_string( $value ) ) {
			$original_value = $value;
			$result = sanitize_text_field( wp_unslash( $value ) );
			if ( is_int( $original_value ) ) {
				$result = intval( $result );
			} else if ( is_float( $original_value ) ) {
				$result = floatval( $result );
			} else if ( is_bool( $original_value ) ) {
				$result = boolval( $result );
			}
		} else if ( is_array( $value ) ) {
			$result = array_map( array( __CLASS__, 'sanitize_input' ), $value );
		}
		return $result;
	}

	/**
	 * Sanitize form data from $_POST.
	 *
	 * @since 7.0.0
	 *
	 * @param string $name
	 *
	 * @return null|string
	 */
	public static function sanitize_post( $name ) {
		$result = null;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,  WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST[$name] ) && ( is_numeric( $_POST[$name] ) || is_string( $_POST[$name] ) || is_array( $_POST[$name] ) ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
			$result = self::sanitize_input( $_POST[$name] );
		}
		return $result;
	}

	/**
	 * Sanitize form data from $_GET.
	 *
	 * @since 7.0.0
	 *
	 * @param string $name
	 *
	 * @return null|string
	 */
	public static function sanitize_get( $name ) {
		$result = null;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,  WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[$name] ) && ( is_numeric( $_GET[$name] ) || is_string( $_GET[$name] ) || is_array( $_GET[$name] ) ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
			$result = self::sanitize_input( $_GET[$name] );
		}
		return $result;
	}

	/**
	 * Sanitize form data from $_REQUEST.
	 *
	 * @since 7.0.0
	 *
	 * @param string $name
	 *
	 * @return null|string
	 */
	public static function sanitize_request( $name ) {
		$result = null;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,  WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST[$name] ) && ( is_numeric( $_REQUEST[$name] ) || is_string( $_REQUEST[$name] ) || is_array( $_REQUEST[$name] ) ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
			$result = self::sanitize_input( $_REQUEST[$name] );
		}
		return $result;
	}

}// class Affiliates_Utility

/**
 * Unslash, sanitize and verify nonce.
 *
 * @since 7.0.0
 *
 * @param string $nonce
 * @param string|number $action
 *
 * @return int|boolean
 */
function affiliates_verify_nonce( $nonce, $action = -1 ) {
	return Affiliates_Utility::verify_nonce( $nonce, $action );
}

/**
 * Unslash, sanitize and verify named nonce provided via $_POST.
 *
 * @since 7.0.0
 *
 * @param string $name nonce name
 * @param string|number $action
 *
 * @return int|boolean
 */
function affiliates_verify_post_nonce( $name, $action = -1 ) {
	return Affiliates_Utility::verify_post_nonce( $name, $action );
}

/**
 * Unslash, sanitize and verify named nonce provided via $_GET.
 *
 * @since 7.0.0
 *
 * @param string $name nonce name
 * @param string|number $action
 *
 * @return int|boolean
 */
function affiliates_verify_get_nonce( $name, $action = -1 ) {
	return Affiliates_Utility::verify_get_nonce( $name, $action );
}

/**
 * Unslash, sanitize and verify named nonce provided via $_GET.
 *
 * @since 7.0.0
 *
 * @param string $name nonce name
 * @param string|number $action
 *
 * @return int|boolean
 */
function affiliates_verify_request_nonce( $name, $action = -1 ) {
	return Affiliates_Utility::verify_request_nonce( $name, $action );
}

/**
 * Provide the current URL, sanitized.
 *
 * @since 7.0.0
 *
 * @return string
 */
function affiliates_get_current_url() {
	return Affiliates_Utility::get_current_url();
}

/**
 * @since 7.0.0
 *
 * @see Affiliates_Utility::sanitize_input()
 *
 * @param string|number|boolean|array $value
 *
 * @return null|string|boolean|array
 */
function affiliates_sanitize_input( $value ) {
	return Affiliates_Utility::sanitize_input( $value );
}

/**
 * @since 7.0.0
 *
 * @see Affiliates_Utility::sanitize_post()
 *
 * @param string $name
 *
 * @return null|string
 */
function affiliates_sanitize_post( $name ) {
	return Affiliates_Utility::sanitize_post( $name );
}

/**
 * @since 7.0.0
 *
 * @see Affiliates_Utility::sanitize_get()
 *
 * @param string $name
 *
 * @return null|string
 */
function affiliates_sanitize_get( $name ) {
	return Affiliates_Utility::sanitize_get( $name );
}

/**
 * @since 7.0.0
 *
 * @see Affiliates_Utility::sanitize_request()
 *
 * @param string $name
 *
 * @return null|string
 */
function affiliates_sanitize_request( $name ) {
	return Affiliates_Utility::sanitize_request( $name );
}
