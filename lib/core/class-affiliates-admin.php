<?php
/**
 * class-affiliates-admin.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package affiliates
 * @since 4.18.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin basics.
 *
 * @since 4.18.0
 */
class Affiliates_Admin {

	/**
	 * Adds actions and filters.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_filter( 'plugin_action_links_'. plugin_basename( AFFILIATES_FILE ), array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 4 );
	}

	/**
	 * Admin init hook.
	 */
	public static function admin_init() {
		if (
			isset( $_REQUEST['aff_setup_show'] ) &&
			isset( $_REQUEST['aff_setup_nonce'] ) &&
			wp_verify_nonce( $_REQUEST['aff_setup_nonce'], 'aff_setup_show' )
		) {
			delete_option( 'aff_setup_hide' );
		}
	}

	/**
	 * Adds particular plugin links.
	 *
	 * @param string[] $links
	 *
	 * @return string[] links
	 */
	public static function plugin_action_links( $links ) {

		$deactivate = null;
		if ( isset( $links['deactivate'] ) ) {
			$deactivate = $links['deactivate'];
			unset( $links['deactivate'] );
		}

		if ( current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			$links['settings'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_admin_url( null, 'admin.php?page=affiliates-admin-settings' ) ),
				esc_html__( 'Settings', 'affiliates' )
			);
		}

		switch (  AFFILIATES_PLUGIN_NAME ) {
			case 'affiliates-pro':
				$links['documentation'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( 'https://docs.itthinx.com/document/affiliates-pro/' ),
					esc_html__( 'Documentation', 'affiliates' )
				);
				break;
			case 'affiliates-enterprise':
				$links['documentation'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( 'https://docs.itthinx.com/document/affiliates-enterprise/' ),
					esc_html__( 'Documentation', 'affiliates' )
				);
				break;
			default:
				$links['documentation'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( 'https://docs.itthinx.com/document/affiliates/' ),
					esc_html__( 'Documentation', 'affiliates' )
				);
		}

		if ( get_option( 'aff_setup_hide', false ) ) {
			$links['welcome'] = sprintf(
				'<a href="%s" title="%s">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg( 'aff_setup_show', 'true', get_admin_url( null, 'admin.php?page=affiliates-admin-settings' ) ),
						'aff_setup_show',
						'aff_setup_nonce'
					)
				),
				esc_html__( 'Show the welcome note', 'affiliates' ),
				esc_html__( 'Welcome', 'affiliates' )
			);
		}

		$links['shop'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( 'https://www.itthinx.com/shop/' ),
			esc_html__( 'Shop', 'affiliates' )
		);

		if ( $deactivate !== null ) {
			$links['deactivate'] = $deactivate;
		}

		return $links;
	}

	/**
	 * Adds plugin metas.
	 *
	 * @param string[] $plugin_meta
	 * @param string $plugin_file
	 * @param array $plugin_data
	 * @param string $status
	 *
	 * @return string[]
	 */
	public static function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( $plugin_file === plugin_basename( AFFILIATES_FILE ) ) {
			switch (  AFFILIATES_PLUGIN_NAME ) {
				case 'affiliates-pro':
					$plugin_meta[] = '<a style="color: #5da64f; font-weight: bold; padding: 1px;" href="http://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>';
					break;
				case 'affiliates-enterprise':
					break;
				default:
					$plugin_meta[] = '<a style="color: #5da64f; font-weight: bold; padding: 1px;" href="http://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>';
					$plugin_meta[] = '<a style="color: #5da64f; font-weight: bold; padding: 1px;" href="http://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>';
			}
			$plugin_meta[] = '<a style="color: #d65d4f; font-weight: bold; padding: 1px;" href="http://www.itthinx.com/shop/">Shop</a>';
		}
		return $plugin_meta;
	}
}
Affiliates_Admin::init();
