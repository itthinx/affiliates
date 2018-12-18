<?php
/**
 * class-affiliates-dashboard-referrals-block.php
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
 * Dashboard section: Referrals
 */
class Affiliates_Dashboard_Referrals_Block extends Affiliates_Dashboard_Referrals {

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
				'affiliates-dashboard-referrals-block',
				plugins_url( 'js/dashboard-referrals-block.js', AFFILIATES_FILE ),
				array( 'wp-blocks', 'wp-element' )
			);

			wp_localize_script(
				'affiliates-dashboard-referrals-block',
				'affiliates_dashboard_referrals_block',
				array(
					'keyword_affiliates'        => __( 'Affiliates', 'affiliates' ),
					'keyword_dashboard'         => __( 'Dashboard', 'affiliates' ),
					'keyword_referrals'          => __( 'Referrals', 'affiliates' ),
					'dashboard_referrals_notice' => _x( 'Affiliates Dashboard Referrals', 'Notice shown when editing the Affiliates Dashboard Referrals block as a non-affiliate.', 'affiliates' )
				)
			);

			// Our editor stylesheet - not required yet.
			// wp_register_style(
			//	'affiliates-dashboard-referrals-block-editor',
			//	plugins_url( 'css/dashboard-blocks-editor.css', AFFILIATES_FILE ),
			//	array( 'wp-edit-blocks' ),
			//	AFFILIATES_CORE_VERSION
			// );

			// Our front end stylesheet - not required yet.
			// wp_register_style(
			//	'affiliates-dashboard-referrals-block',
			//	plugins_url( 'css/dashboard-blocks.css', AFFILIATES_FILE ),
			//	array(),
			//	AFFILIATES_CORE_VERSION
			// );

			register_block_type(
				'affiliates/dashboard-referrals',
				array(
					'editor_script' => 'affiliates-dashboard-referrals-block',
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
		$output = '';
		if ( affiliates_user_is_affiliate( get_current_user_id() ) ) {
			// Render the referrals:
			$section = new Affiliates_Dashboard_Referrals();
			ob_start();
			$section->render();
			$output = ob_get_clean();
		}
		// The following fixes a Gutenberg UX/UI bug : if the callback returns an empty string, you would see a spinner that never goes away.
		// So we render something other than the empty string, to avoid the spinner being shown eternally.
		// The form obviously won't be rendered when previewing in the editor because you're logged in.
		// The REST_REQUEST ... part is trying to recognize it's a request to render the block on the back end.
		if (
			( strlen( $output ) === 0 ) &&
			defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_REQUEST['context'] ) && $_REQUEST['context'] === 'edit'
		) {
			$output .= '<div style="display:none"></div>';
		}
		return $output;
	}

}
Affiliates_Dashboard_Referrals_Block::init();
