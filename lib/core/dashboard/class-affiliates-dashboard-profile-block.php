<?php
/**
 * class-affiliates-dashboard-profile-block.php
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
 * Dashboard section block: Profile
 */
class Affiliates_Dashboard_Profile_Block extends Affiliates_Dashboard_Profile {

	/**
	 * Adds our init action.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueue_block_assets' ) );
	}

	public static function enqueue_block_editor_assets() {
		// Our script used to edit and render the blocks.
		wp_register_script(
			'affiliates-dashboard-profile-block',
			plugins_url( 'js/dashboard-profile-block.js', AFFILIATES_FILE ),
			array( 'wp-blocks', 'wp-element' )
		);

		wp_localize_script(
			'affiliates-dashboard-profile-block',
			'affiliates_dashboard_profile_block',
			array(
				'title'                    => _x( 'Affiliates Dashboard Profile', 'block title', 'affiliates' ),
				'description'              => _x( 'Displays the Profile section from the Affiliates Dashboard', 'block description', 'affiliates' ),
				'keyword_affiliates'       => __( 'Affiliates', 'affiliates' ),
				'keyword_dashboard'        => __( 'Dashboard', 'affiliates' ),
				'keyword_profile'          => __( 'Profile', 'affiliates' ),
				'dashboard_profile_notice' => _x( 'Affiliates Dashboard Profile', 'Notice shown when editing the Affiliates Dashboard Profile block as a non-affiliate.', 'affiliates' )
			)
		);

		// Our editor stylesheet - not required yet.
		// wp_register_style(
		//	'affiliates-dashboard-profile-block-editor',
		//	plugins_url( 'css/dashboard-blocks-editor.css', AFFILIATES_FILE ),
		//	array( 'wp-edit-blocks' ),
		//	AFFILIATES_CORE_VERSION
		// );
	}

	public static function enqueue_block_assets() {
		// Our front end stylesheet - not required yet.
		// wp_register_style(
		//	'affiliates-dashboard-profile-block',
		//	plugins_url( 'css/dashboard-blocks.css', AFFILIATES_FILE ),
		//	array(),
		//	AFFILIATES_CORE_VERSION
		// );

		if ( !wp_script_is( 'affiliates-fields' ) ) {
			wp_register_style( 'affiliates-fields', AFFILIATES_PLUGIN_URL . 'css/affiliates-fields.css', array(), AFFILIATES_CORE_VERSION, 'all' );
		}
		wp_enqueue_style( 'affiliates-fields' );
	}

	/**
	 * Initialization - registers the block.
	 */
	public static function wp_init() {
		if ( function_exists( 'register_block_type' ) ) {
			register_block_type(
				'affiliates/dashboard-profile',
				array(
					'editor_script' => 'affiliates-dashboard-profile-block',
					'render_callback' => array( __CLASS__, 'block' )
				)
			);
		}
	}

	/**
	 * Callback that renders the section block.
	 *
	 * @param array $atts attributes
	 * @param string $content not used
	 *
	 * @return string
	 */
	public static function block( $atts, $content = '' ) {
		$output = '';
		if ( affiliates_user_is_affiliate( get_current_user_id() ) ) {
			// Render the profile:
			/**
			 * @var Affiliates_Dashboard_Profile $section
			 */
			$section = Affiliates_Dashboard_Section_Factory::get_section_instance( Affiliates_Dashboard_Profile::get_key() );
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
Affiliates_Dashboard_Profile_Block::init();
