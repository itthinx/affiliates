<?php
/**
 * class-affiliates-translations.php
 *
 * Copyright (c) 2010 - 2026 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 6.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date

/**
 * Translations.
 */
class Affiliates_Translations {

	private static $mofiles = array();

	public static function boot() {
		add_action( 'init', array( __CLASS__, 'init' ) );
	}

	public static function init() {
		add_filter( 'load_textdomain_mofile', array( __CLASS__, 'load_textdomain_mofile' ), 10, 2 );
		add_filter( 'load_script_translation_file', array( __CLASS__, 'load_script_translation_file' ), 10, 3 );
	}

	/**
	 * Determine the mofile.
	 *
	 * @return string mofile
	 */
	public static function get_mofile( $location = null ) {

		if ( $location === null ) {
			$location = AFFILIATES_CORE_LIB . '/languages/';
		}

		if ( isset( self::$mofiles[$location] ) ) {
			return self::$mofiles[$location];
		}

		$locale = get_locale();
		if ( function_exists( 'get_user_locale' ) ) {
			$locale = get_user_locale();
		}
		$locale = apply_filters( 'plugin_locale', $locale, 'affiliates' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$mofile = $location . 'affiliates-' . $locale . '.mo';

		if ( !file_exists( $mofile ) ) {
			$parts = explode( '_', $locale );
			$language = $parts[0];
			$country  = isset( $parts[1] ) ? $parts[1] : '';
			$form     = isset( $parts[2] ) ? $parts[2] : '';
			switch ( $country ) {
				case 'CH':
					switch ( $form ) {
						case 'informal':
							$form = '';
							break;
						case '':
							$form = 'formal';
							break;
					}
					break;
			}
			$the_mofile = null;
			if ( $form !== '' ) {
				$the_mofile = $location . 'affiliates' . '-' . $language . '_' . $form . '.mo';
				if ( !file_exists( $the_mofile ) ) {
					$the_mofile = null;
				}
			}
			if ( $the_mofile === null ) {
				$the_mofile = $location . 'affiliates' . '-' . $language . '.mo';
				if ( !file_exists( $the_mofile ) ) {
					$the_mofile = null;
				}
			}
			if ( $the_mofile !== null ) {
				$mofile = $the_mofile;
			}
		}

		self::$mofiles[$location] = $mofile;

		return $mofile;
	}

	/**
	 * Load determined translation file.
	 *
	 * @param string $mofile
	 * @param string $domain
	 *
	 * @return string mofile
	 */
	public static function load_textdomain_mofile( $mofile, $domain ) {
		if ( $domain === 'affiliates' ) {
			remove_filter( 'load_textdomain_mofile', array( __CLASS__, 'load_textdomain_mofile' ), 10 );
			$own_mofile = self::get_mofile();
			if ( $own_mofile != $mofile ) {
				if ( !is_textdomain_loaded( $domain ) ) {
					if ( is_readable( $own_mofile ) ) {
						$mofile = $own_mofile;
					}
				}
			}
			add_filter( 'load_textdomain_mofile', array( __CLASS__, 'load_textdomain_mofile' ), 10, 2 );
		}
		return $mofile;
	}

	/**
	 * Load script translations.
	 *
	 * @param string $file
	 * @param string $handle
	 * @param string $domain
	 *
	 * @return string
	 */
	public static function load_script_translation_file( $file, $handle, $domain ) {
		if ( $domain === 'affiliates' ) {
			$mofile = self::get_mofile();
			if ( $mofile !== null ) {
				$base = basename( $mofile, '.mo' );
				$path = AFFILIATES_CORE_LIB . '/languages/js/' . $base . '.json';
				if ( file_exists( $path ) ) {
					$file = $path;
				}
			}
		}
		return $file;
	}

}

Affiliates_Translations::boot();
