<?php
/**
 * class-affiliates-date-helper.php
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
 *
 * Affiliates date helper.
 *
 * 1. Assumptions and preconditions.
 *
 * 1.1. Timezones and frames of reference.
 *
 * There are two frames of reference:
 * UTZ ~ Time from a user's point of view, determined by the user's timezone.
 * STZ ~ Time with respect to the server's timezone.
 *
 * 1.2. UTZ
 *
 * We assume that a time within UTZ is the time with respect to the timezone set
 * with WordPress's timezone_string, i.e. the timezone obtained querying
 * get_option('timezone_string').
 *
 * Note that we will not try to automagically find out the user's timezone
 * on the fly. First of all, it will fail with a reasonable probability, and
 * second we assume that the user will be perfectly aware of what her or his
 * real timezone is.
 *
 * 1.3. STZ
 *
 * We assume that a time within STZ is the time with respect to the timezone
 * that the server uses to record time. This timezone is the one obtained
 * through date_default_timezone_get().
 *
 * 2. Services
 *
 * 2.1. Conversion
 *
 * We will provide functions that simplify conversion between times expressed
 * with respect to UTZ and STZ.
 *
 * The functions for that are:
 *
 * u2s() which will convert a time from the user's timezone to a time in the
 * server's timezone.
 *
 * s2u() which does the opposite.
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date translation and format helper.
 */
class DateHelper {

	/**
	 * @var string $datetimeFormat The format to express a datetime.
	 */
	private static $datetimeFormat = 'Y-m-d H:i:s';

	/**
	 * @var string $dateFormat The format to express a date.
	 */
	private static $dateFormat = 'Y-m-d';

	/**
	 * @var string $timeFormat The format to express a time.
	 */
	private static $timeFormat = 'H:i:s';

	/**
	 * Convert a datetime within the STZ to a datetime within the UTZ frame of reference.
	 *
	 * @param string $datetime datetime with respect to STZ, i.e. stored at the server
	 * @param int $offset the offset in seconds is added to the datetime
	 *
	 * @return datetime with respect to UTZ, i.e. seen by the user
	 */
	static function s2u( $sdatetime, $offset = 0 ) {
		return self::_t2t( $sdatetime, 's2u', $offset );
	}

	/**
	 * Convert a datetime within the UTZ to a datetime within the STZ frame of reference.
	 *
	 * @param string $datetime datetime with respect to UTZ, i.e. seen by the user
	 * @param int $offset the offset in seconds is added to the datetime
	 *
	 * @return string datetime with respect to STZ, i.e. stored at the server
	 */
	static function u2s( $udatetime, $offset = 0 ) {
		return self::_t2t( $udatetime, 'u2s', $offset );
	}

	/**
	 * Does the actual conversion either way.
	 *
	 * @param string $datetime datetime to convert
	 * @param string $f function to use, either u2s or s2u
	 * @param int $offset the offset in seconds is added to the datetime
	 *
	 * @return string converted datetime
	 */
	private static function _t2t( $datetime, $f, $offset = 0 ) {

		$datetime_ = null;

		switch ( $f ) {
			case 'u2s' :
				$delta = 1;
				break;
			case 's2u' :
				$delta = -1;
				break;
		}

		// If supported, adjust the dates for the site's/server's timezone:
		if ( self::timezone_supported() ) {
			$time = time();
			$default_tz = date_default_timezone_get();
			$default_dtz = new DateTimeZone( $default_tz );
			$tzstring = get_option('timezone_string');
			if ( !empty( $tzstring ) ) {
				$site_dtz = new DateTimeZone( $tzstring );
			} else {
				$site_dtz = new DateTimeZone( $default_tz );
			}

			// Server dates and times are with respect to the default timezone.
			// The $offset is the difference between the default timezone's offset
			// and the user's timezone offset, or the negative value if we convert
			// a time with respect to the server's timezone to a time with respect
			// to the site's i.e. the user's timezone.
			$tz_offset = $default_dtz->getOffset( new DateTime( "@$time" ) ) - $site_dtz->getOffset( new DateTime( "@$time" ) );
			//echo "<div>tz_offset=$tz_offset</div>";
			$datetime_ = date( 'Y-m-d H:i:s', strtotime( $datetime ) + $tz_offset * $delta + $offset );

		} else {
			// If there is no support for timezones, there's nothing we can do.
			// Just check the date and return it if it makes sense.
			$datetime_ = date( 'Y-m-d H:i:s', strtotime( $datetime ) );
		}
		return $datetime_;
	}

	/**
	 * Returns a datetime formatted as a date without time component.
	 *
	 * @param string $datetime the datetime to format as a date
	 *
	 * @return string formatted date
	 */
	static function formatDate( $datetime ) {
		return date( self::$dateFormat, strtotime( $datetime ) );
	}

	/**
	 * Returns a datetime formatted as a time without date component.
	 *
	 * @param string $datetime the datetime to format as a time
	 *
	 * @return string formatted time
	 */
	static function formatTime( $datetime ) {
		return date( self::$timeFormat, strtotime( $datetime ) );
	}

	/**
	 * Returns a formatted datetime with a date and a time component.
	 *
	 * @param string $datetime the datetime to format
	 *
	 * @return string formatted datetime
	 */
	static function formatDatetime( $datetime ) {
		return date( self::$datetimeFormat, strtotime( $datetime ) );
	}

	static function getServerDateTimeZone() {
		$default_tz = date_default_timezone_get();
		$default_dtz = new DateTimeZone( $default_tz );
		return $default_dtz;
	}

	static function getUserDateTimeZone() {
		$tzstring = get_option('timezone_string');
		$site_dtz = new DateTimeZone( $tzstring );
		return $site_dtz;
	}

	/**
	 * Substitutes deprecated wp_timezone_supported().
	 * The current (WP 5.2.1) just returns true. Unfortunately we can
	 * not assume as of yet that everybody will run their WP on
	 * PHP 5 >= 5.2.0 so we maintain this for a while.
	 * @see wp_timezone_supported()
	 * @link http://core.trac.wordpress.org/ticket/16970
	 */
	static function timezone_supported() {
		$support = false;
		if (
			function_exists( 'date_create' ) && // PHP 5 >= 5.2.0
			function_exists( 'date_default_timezone_set' ) && // PHP 5 >= 5.1.0
			function_exists( 'timezone_identifiers_list' ) && // PHP 5 >= 5.2.0
			function_exists( 'timezone_open' ) && // PHP 5 >= 5.2.0
			function_exists( 'timezone_offset_get' ) // PHP 5 >= 5.2.0
		) {
			$support = true;
		}
		return apply_filters( 'timezone_support', $support );
	}
}
