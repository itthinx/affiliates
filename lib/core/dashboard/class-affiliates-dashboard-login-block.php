<?php
/**
 * class-affiliates-dashboard-login-block.php
 *
 * Copyright (c) 2010 - 2018 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 4.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard section: Login
 */
class Affiliates_Dashboard_Login_Block extends Affiliates_Dashboard_Login {

	/**
	 * Adds our init action.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
	}

	/**
	 * Initialization - adds the shortcode.
	 */
	public static function wp_init() {
		if ( function_exists( 'register_block_type' ) ) {

			// Our script used to edit and render the blocks.
			wp_register_script(
				'affiliates-dashboard-login-block',
				plugins_url( 'js/dashboard-login-block.js', AFFILIATES_FILE ),
				array( 'wp-blocks', 'wp-element' )
			);

			wp_localize_script(
				'affiliates-dashboard-login-block',
				'affiliates_dashboard_login_block',
				array(
					'keyword_affiliates'     => __( 'Affiliates', 'affiliates' ),
					'keyword_dashboard'      => __( 'Dashboard', 'affiliates' ),
					'keyword_login'          => __( 'Login', 'affiliates' ),
					'dashboard_login_notice' => _x( 'Affiliates Dashboard Login', 'Notice shown when editing the Affiliates Dashboard Login block.', 'affiliates' )
				)
			);

			// Our editor stylesheet - not required yet.
			// wp_register_style(
			//	'affiliates-dashboard-login-block-editor',
			//	plugins_url( 'css/dashboard-blocks-editor.css', AFFILIATES_FILE ),
			//	array( 'wp-edit-blocks' ),
			//	AFFILIATES_CORE_VERSION
			// );

			// Our front end stylesheet - not required yet.
			// wp_register_style(
			//	'affiliates-dashboard-login-block',
			//	plugins_url( 'css/dashboard-blocks.css', AFFILIATES_FILE ),
			//	array(),
			//	AFFILIATES_CORE_VERSION
			// );

			register_block_type(
				'affiliates/dashboard-login',
				array(
					'editor_script' => 'affiliates-dashboard-login-block',
					'render_callback' => array( __CLASS__, 'block' )
				)
			);
		}
	}

	/**
	 * Shortcode handler for the section shortcode.
	 *
	 * @param array $atts shortcode attributes
	 * @param string $content not used
	 *
	 * @return string
	 */
	public static function block( $atts, $content = '' ) {
		// Render the login form:
		$section = new Affiliates_Dashboard_Login();
		ob_start();
		$section->render();
		$output = ob_get_clean();
		// The following fixes a Gutenberg UX/UI bug : if the callback returns an empty string, you would see a spinner that never goes away.
		// So we render something other than the empty string, to avoid the spinner being shown eternally.
		// The form obviously won't be rendered when previewing in the editor because you're logged in.
		// The REST_REQUEST ... part is trying to recognize it's a request to render the block on the back end.
		if (
			strlen( $output ) === 0 &&
			is_user_logged_in() &&
			defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_REQUEST['context'] ) && $_REQUEST['context'] === 'edit'
		) {
			$output .= '<div style="display:none"></div>';
		}
		return $output;
	}

}
Affiliates_Dashboard_Login_Block::init();
