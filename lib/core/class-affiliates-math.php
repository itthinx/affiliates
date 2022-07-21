<?php
/**
 * class-affiliates-math.php
 *
 * Copyright (c) www.itthinx.com
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
 * @author itthinx
 * @package affiliates
 * @since 4.15.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Math class.
 *
 * @since 4.15.0
 */
class Affiliates_Math {

	/**
	 * @var int
	 */
	private static $scale = null;

	/**
	 * @var boolean
	 */
	private static $bcmath = true;

	/**
	 * Null parameters are swapped to 0.
	 *
	 * @param mixed $left_operand
	 * @param mixed $right_operand
	 */
	private static function process_parameters( &$left_operand = null, &$right_operand = null ) {
		if ( $left_operand === null ) {
			$left_operand = 0;
		}
		if ( $right_operand === null ) {
			$right_operand = 0;
		}
	}

	/**
	 * Apply scale to value.
	 *
	 * @param number $value
	 * @param int $scale
	 *
	 * @return number|string
	 */
	private static function apply_scale( $value, $scale = null ) {
		$result = $value;
		if ( $scale !== null ) {
			$result = sprintf( '%.' . $scale . 'F', $value );
		} else if ( self::$scale !== null  ) {
			$result = sprintf( '%.' . self::$scale . 'F', $value );
		}
		return $result;
	}

	/**
	 * BCMath enable/disable (use for tests).
	 *
	 * @param boolean $enable
	 */
	public static function bcmath( $enable = true ) {
		self::$bcmath = boolval( $enable );
	}

	/**
	 * Add.
	 *
	 * @param mixed $left_operand
	 * @param mixed $right_operand
	 * @param int $scale
	 *
	 * @return number|string
	 */
	public static function add( $left_operand, $right_operand, $scale = null ) {
		self::process_parameters( $left_operand, $right_operand );
		if ( self::$bcmath && function_exists( 'bcadd' ) ) {
			$result = bcadd( $left_operand, $right_operand, $scale );
		} else {
			$result = floatval( $left_operand ) + floatval( $right_operand );
			$result = self::apply_scale( $result, $scale );
		}
		return $result;
	}

	/**
	 * Compare.
	 *
	 * @param mixed $left_operand
	 * @param mixed $right_operand
	 * @param int $scale
	 *
	 * @return number
	 */
	public static function comp( $left_operand, $right_operand, $scale = null ) {
		self::process_parameters( $left_operand, $right_operand );
		if ( self::$bcmath && function_exists( 'bccomp' ) ) {
			$result = bccomp( $left_operand, $right_operand, $scale );
		} else {
			$result = floatval( $left_operand ) - floatval( $right_operand );
			$result = self::apply_scale( $result, $scale );
		}
		return $result;
	}

	/**
	 * Divide.
	 *
	 * @param mixed $left_operand
	 * @param mixed $right_operand
	 * @param int $scale
	 *
	 * @return number|string
	 */
	public static function div( $left_operand, $right_operand, $scale = null ) {
		self::process_parameters( $left_operand, $right_operand );
		if ( self::$bcmath && function_exists( 'bcdiv' ) ) {
			$result = bcdiv( $left_operand, $right_operand, $scale );
		} else {
			$result = floatval( $left_operand ) / floatval( $right_operand );
			$result = self::apply_scale( $result, $scale );
		}
		return $result;
	}

	/**
	 * Modulus.
	 *
	 * @param mixed $left_operand
	 * @param mixed $modulus
	 * @param int $scale
	 *
	 * @return number|string
	 */
	public static function mod( $left_operand, $modulus, $scale = null ) {
		self::process_parameters( $left_operand, $modulus );
		if ( self::$bcmath && function_exists( 'bcmod' ) ) {
			$result = bcmod( $left_operand, $modulus );
		} else {
			$result = intval( $left_operand ) % intval( $modulus );
			$result = self::apply_scale( $result, $scale );
		}
		return $result;
	}

	/**
	 * Multiply.
	 *
	 * @param mixed $left_operand
	 * @param mixed $right_operand
	 * @param int $scale
	 *
	 * @return number|string
	 */
	public static function mul( $left_operand, $right_operand, $scale = null ) {
		self::process_parameters( $left_operand, $right_operand );
		if ( self::$bcmath && function_exists( 'bcmul' ) ) {
			$result = bcmul( $left_operand, $right_operand, $scale );
		} else {
			$result = floatval( $left_operand ) * floatval( $right_operand );
			$result = self::apply_scale( $result, $scale );
		}
		return $result;
	}

	/**
	 * Power.
	 *
	 * @param mixed $left_operand
	 * @param mixed $right_operand
	 * @param int $scale
	 *
	 * @return number|string
	 */
	public static function pow( $left_operand, $right_operand, $scale = null ) {
		self::process_parameters( $left_operand, $right_operand );
		if ( self::$bcmath && function_exists( 'bcpow' ) ) {
			$result = bcpow( $left_operand, $right_operand, $scale );
		} else {
			$result = pow( floatval( $left_operand ), floatval( $right_operand ) );
			$result = self::apply_scale( $result, $scale );
		}
		return $result;
	}

	/**
	 * Power => modulus.
	 *
	 * @param mixed $left_operand
	 * @param mixed $right_operand
	 * @param mixed $modulus
	 * @param int $scale
	 *
	 * @return number|string
	 */
	public static function powmod( $left_operand , $right_operand , $modulus, $scale = null ) {
		self::process_parameters( $left_operand, $right_operand );
		if ( self::$bcmath && function_exists( 'bcpowmod' ) ) {
			$result = bcpowmod( $left_operand, $right_operand, $modulus, $scale );
		} else {
			$result = pow( floatval( $left_operand ), floatval( $right_operand ) ) % intval( $modulus );
			$result = self::apply_scale( $result, $scale );
		}
		return $result;
	}

	/**
	 * Set scale.
	 *
	 * @param int $scale
	 */
	public static function scale( $scale ) {
		if ( self::$bcmath && function_exists( 'bcscale' ) ) {
			bcscale( $scale );
		} else {
			self::$scale = $scale;
		}
	}

	/**
	 * Square root.
	 *
	 * @param mixed $operand
	 * @param mixed $scale
	 *
	 * @return number|string
	 */
	public static function sqrt( $operand, $scale = null ) {
		if ( $operand === null ) {
			$operand = 0;
		}
		if ( self::$bcmath && function_exists( 'bcsqrt' ) ) {
			$result = bcsqrt( $operand, $scale );
		} else {
			$result = sqrt( floatval( $operand ) );
			$result = self::apply_scale( $result, $scale );
		}
		return $result;
	}

	/**
	 * Subtract.
	 *
	 * @param mixed $left_operand
	 * @param mixed $right_operand
	 * @param int $scale
	 *
	 * @return number|string
	 */
	public static function sub( $left_operand, $right_operand, $scale = null ) {
		self::process_parameters( $left_operand, $right_operand );
		if ( self::$bcmath && function_exists( 'bcsub' ) ) {
			$result = bcsub( $left_operand, $right_operand, $scale );
		} else {
			$result = floatval( $left_operand ) - floatval( $right_operand );
			$result = self::apply_scale( $result, $scale );
		}
		return $result;
	}
}
