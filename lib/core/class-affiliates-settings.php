<?php
/**
 * class-affiliates-settings.php
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

/**
 * @var string options form nonce name
 */
define( 'AFFILIATES_ADMIN_SETTINGS_NONCE', 'affiliates-admin-nonce' );

/**
 * @var string generator nonce
*/
define( 'AFFILIATES_ADMIN_SETTINGS_GEN_NONCE', 'affiliates-admin-gen-nonce' );

/**
 * Settings admin section.
 */
class Affiliates_Settings {

	static $sections = null; 

	/**
	 * Settings initialization.
	 */
	public static function init() {
		self::$sections = apply_filters(
			'affiliates_settings_sections',
			array(
				'integration'  => __( 'Integration', AFFILIATES_PLUGIN_DOMAIN ),
				'pages'        => __( 'Pages', AFFILIATES_PLUGIN_DOMAIN ),
				'referrals'    => __( 'Referrals', AFFILIATES_PLUGIN_DOMAIN ),
				'registration' => __( 'Registration', AFFILIATES_PLUGIN_DOMAIN ),
				'general'      => __( 'General', AFFILIATES_PLUGIN_DOMAIN )
			)
		);
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}

	/**
	 * Registers an admin_notices action.
	 */
	public static function admin_init() {
		wp_register_style( 'affiliates-admin-settings', AFFILIATES_PLUGIN_URL . 'css/affiliates_admin_settings.css' );
		if ( get_option( 'aff_generate_page', 1 ) == 1 ) {
			add_action( 'admin_notices', array( __CLASS__, 'setup_notice' ) );
		}
	}

	/**
	 * Prints setup notices.
	 */
	public static function setup_notice() {
		echo '<div id="message" class="updated affiliates-settings">';

		echo '<p>';
		_e( '<strong>Welcome to Affiliates</strong>', AFFILIATES_PLUGIN_DOMAIN );
		echo '</p>';

		echo '<p class="submit">';

		printf (
			'<a href="%s" class="button-primary">%s</a>',
			add_query_arg( 'section', 'pages', admin_url( 'admin.php?page=affiliates-admin-settings' ) ),
			__( 'Create an Affiliate Area', AFFILIATES_PLUGIN_DOMAIN )
		);

		echo ' ';

		printf (
			'<a href="%s" class="button-primary">%s</a>',
			add_query_arg( 'section', 'integration', admin_url( 'admin.php?page=affiliates-admin-settings' ) ),
			__( 'Install an Integration', AFFILIATES_PLUGIN_DOMAIN )
		);
		echo ' ';

		printf (
		'<a href="%s" class="button-primary">%s</a>',
		add_query_arg( 'section', 'registration', admin_url( 'admin.php?page=affiliates-admin-settings' ) ),
		__( 'Enable Affiliate Registration', AFFILIATES_PLUGIN_DOMAIN )
		);
		echo ' ';

		// @todo link to review general settings? maybe not

		printf( '<a class="hide button-primary" href="%s">%s</a>',
			add_query_arg( 'affiliates_setup_hide', 'true', admin_url( 'admin.php?page=affiliates-admin-settings' ) ),
			__( 'Hide this', AFFILIATES_PLUGIN_DOMAIN )
		);
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Settings admin section.
	 */
	public static function admin_settings() {
		global $wp, $wpdb, $affiliates_options, $wp_roles;

		if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			wp_die( __( 'Access denied.', AFFILIATES_PLUGIN_DOMAIN ) );
		}

		wp_enqueue_style( 'affiliates-admin-settings' );

		$section = isset( $_REQUEST['section'] ) ? $_REQUEST['section'] : null;

		if ( !key_exists( $section, self::$sections ) ) {
			$section = 'integration';
		}
		$section_title = self::$sections[$section];

		echo
			'<h1>' .
			__( 'Settings', AFFILIATES_PLUGIN_DOMAIN ) .
			'</h1>';

		$section_links = array();
		foreach( self::$sections as $sec => $title ) {
			$section_links[] = sprintf(
				'<a class="section-link %s" href="%s">%s</a>',
				$section == $sec ? 'active' : '',
				esc_url( add_query_arg( 'section', $sec, admin_url( 'admin.php?page=affiliates-admin-settings' ) ) ),
				$title
			);
		}
		echo '<div class="section-links">';
		echo implode( ' | ', $section_links );
		echo '</div>';

		echo
			'<h2>' .
			$section_title .
			'</h2>';
		
		switch( $section ) {
			case 'integration' :
				require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-integration.php';
				Affiliates_Settings_Integration::section();
				break;
			case 'pages' :
				require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-pages.php';
				Affiliates_Settings_Pages::section();
				break;
			case 'referrals' :
				require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-referrals.php';
				Affiliates_Settings_Referrals::section();
				break;
			case 'registration' :
				require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-registration.php';
				Affiliates_Settings_Registration::section();
				break;
			case 'general' :
				require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-general.php';
				Affiliates_Settings_General::section();
				break;
			default :
				do_action( 'affiliates_settings_section', $section );
		}

	}

	public static function network_admin_settings() {
		// @todo network admin settings
	}

}
Affiliates_Settings::init();
