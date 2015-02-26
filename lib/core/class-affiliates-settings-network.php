<?php
/**
 * class-affiliates-settings-network.php
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

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFFILIATES_MS_ADMIN_SETTINGS_NONCE', 'aff_ms_settings_nonce' );

/**
 * Settings admin section.
 */
class Affiliates_Settings_Network {

	/**
	 * Settings initialization.
	 */
	public static function init() {
		
	}

	/**
	 * Network options.
	 */
	public static function network_admin_settings() {
			global $wp, $wpdb, $affiliates_options, $wp_roles;
			if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
				wp_die( __( 'Access denied.', AFFILIATES_PLUGIN_DOMAIN ) );
			}
			echo '<h1>' . __( 'Affiliates', AFFILIATES_PLUGIN_DOMAIN ) . '</h1>';
			if ( affiliates_is_sitewide_plugin() ) {
				if ( isset( $_POST['submit'] ) ) {
					if ( wp_verify_nonce( $_POST[AFFILIATES_MS_ADMIN_SETTINGS_NONCE], 'admin' ) ) {
						if ( !empty( $_POST['delete-network-data'] ) ) {
							update_option( 'aff_delete_network_data', true );
						} else {
							update_option( 'aff_delete_network_data', false );
						}
					}
				}
				$delete_network_data = get_option( 'aff_delete_network_data', false );
				echo
				'<form action="" name="options" method="post">' .
				'<div>' .
				'<h3>' . __( 'Affiliates network data', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
				'<p>' .
				'<input name="delete-network-data" type="checkbox" ' . ( $delete_network_data ? 'checked="checked"' : '' ) . '/>' .
				'<label for="delete-network-data">' . __( 'Delete all affiliate data on network deactivation', AFFILIATES_PLUGIN_DOMAIN ) . '</label>' .
				'</p>' .
				'<p class="description warning">' .
				__( 'READ AND UNDERSTAND the following before activating this option:', AFFILIATES_PLUGIN_DOMAIN ) .
				'</p>' .
				'<ol class="description warning">' .
				'<li>' . __( 'CAUTION: If this option is active while the plugin is network deactivated, <strong>ALL affiliate and referral data will be DELETED on all sites of the network</strong>.', AFFILIATES_PLUGIN_DOMAIN ) . '</li>' .
				'<li>' . __( 'This option should only be used to clean up after testing.', AFFILIATES_PLUGIN_DOMAIN ) . '</li>' .
				'<li>' . __( 'Make sure to back up your data or do not enable this option.', AFFILIATES_PLUGIN_DOMAIN ) . '</li>' .
				'<li>' . __( 'By enabling this option you agree to be solely responsible for any loss of data or any other consequences thereof.', AFFILIATES_PLUGIN_DOMAIN ) . '</li>' .
				'</ol>' .
				'<p>' .
				wp_nonce_field( 'admin', AFFILIATES_MS_ADMIN_SETTINGS_NONCE, true, false ) .
				'<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', AFFILIATES_PLUGIN_DOMAIN ) . '"/>' .
				'</p>' .
				'</div>' .
				'</form>';
			}
	}

}
Affiliates_Settings_Network::init();
