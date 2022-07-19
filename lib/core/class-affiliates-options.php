<?php
/**
 * class-affiliates-options.php
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
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Affiliates options
 */
class Affiliates_Options {

	/**
	 * Returns the value of a setting.
	 *
	 * @param string $option the option id
	 * @param mixed $default default value to retrieve if option is not set
	 *
	 * @return mixed option value, $default if set or null
	 */
	function get_option( $option, $default = null ) {

		$current_user = wp_get_current_user();
		$value = null;

		if ( !empty( $current_user ) ) {
			$options = get_option( 'affiliates_plugin' );
			if ( is_array( $options ) && isset( $options[$current_user->ID] ) && is_array( $options[$current_user->ID] ) )	{
				$value = isset( $options[$current_user->ID][$option] ) ? $options[$current_user->ID][$option] : null;
			} else {
				$value = null;
			}
		}
		if ( $value === null ) {
			$value = $default;
		}
		return $value;
	}

	/**
	 * Updates a setting.
	 *
	 * @param string $option the option's id
	 * @param mixed $new_value the new value
	 */
	function update_option( $option, $new_value ) {
		$current_user = wp_get_current_user();
		if ( !empty( $current_user ) ) {
			$options = get_option( 'affiliates_plugin' );
			if ( !is_array( $options ) ) {
				$options = array( $current_user->ID => array() );
			}
			$options[$current_user->ID][$option] = $new_value;
			update_option( 'affiliates_plugin', $options );
		}
	}

	/**
	 * Deletes a setting.
	 *
	 * @param string $option the option's id
	 */
	function delete_option( $option ) {
		$current_user = wp_get_current_user();
		if ( !empty( $current_user ) ) {
			$options = get_option( 'affiliates_plugin' );
			if ( isset( $options[$current_user->ID][$option] ) ) {
				unset( $options[$current_user->ID][$option] );
				update_option( 'affiliates_plugin', $options );
			}
		}
	}

	/**
	 * Deletes all settings.
	 */
	function flush_options() {
		delete_option( 'affiliates_plugin' );
	}
}
