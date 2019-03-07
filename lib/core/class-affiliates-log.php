<?php
/**
 * class-affiliates-log.php
 *
 * Copyright (c) 2010-2019 "kento" Karim Rahimpur www.itthinx.com
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
 * @since 4.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFFILIATES_LOG_INFO', Affiliates_Log::INFO );
define( 'AFFILIATES_LOG_WARNING', Affiliates_Log::WARNING );
define( 'AFFILIATES_LOG_ERROR', Affiliates_Log::ERROR );

/**
 * Log the message with given level.
 *
 * @param string $message message to log
 * @param int $level log level
 *
 * @see Affiliates_Log::log()
 */
function affiliates_log( $message, $level = AFFILIATES_LOG_INFO ) {
	Affiliates_Log::log( $message, $level );
}

/**
 * Log an informational message.
 *
 * @param string $message message to log
 * @param boolean $always whether to always log
 */
function affiliates_log_info( $message, $always = false ) {
	Affiliates_Log::log( $message, Affiliates_Log::INFO, $always );
}


/**
 * Log a warning.
 *
 * @param string $message message to log
 * @param boolean $always whether to always log
 */
function affiliates_log_warning( $message, $always = false ) {
	Affiliates_Log::log( $message, Affiliates_Log::WARNING, $always );
}


/**
 * Log an error.
 *
 * @param string $message message to log
 */
function affiliates_log_error( $message ) {
	Affiliates_Log::log( $message, Affiliates_Log::ERROR, true );
}

/**
 * Logging class
 */
class Affiliates_Log {

	const INFO = 0;
	const WARNING = 1;
	const ERROR = 2;

	/**
	 * Log an informational, warning or error message.
	 *
	 * The log $level and the value of AFFILIATES_DEBUG determines whether the message is actually logged or not, unless $always is set to true.
	 *
	 * A message with log level AFFILIATES_LOG_INFO or AFFILIATES_LOG_WARNING is logged only when AFFILIATES_DEBUG is true.
	 * A message with log level AFFILIATES_LOG_ERROR is always logged.
	 *
	 * @param string $message the log message to record
	 * @param int $level the applicable log level
	 * @param bool $always whether to log always independent of level
	 *
	 * @return bool true if the message has been logged
	 */
	public static function log( $message, $level = self::INFO, $always = false ) {
		$result = false;
		$log = $always;
		if ( strlen( $message ) > 0 ) {
			switch( $level ) {
				case self::ERROR :
					$log = true;
					break;
				default :
					if ( AFFILIATES_DEBUG ) {
						$log = true;
					}
					break;
			}
		}
		if ( $log ) {
			$pid = @getmypid();
			if ( $pid === false ) {
				$pid = 'ERROR';
			}
			switch( $level ) {
				case self::ERROR :
					$level_str = 'ERROR';
					break;
				case self::WARNING :
					$level_str = 'WARNING';
					break;
				default :
					$level_str = 'INFO';
			}
			$result = error_log( sprintf(
				'[' . AFFILIATES_PLUGIN_NAME . '][%s][%s] %s',
				$level_str,
				$pid,
				$message
			) );
		}
		return $result;
	}
}
