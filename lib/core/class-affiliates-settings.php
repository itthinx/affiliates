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
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}

	/**
	 * Settings sections.
	 * 
	 * @return array
	 */
	public static function init_sections() {
		self::$sections = apply_filters(
			'affiliates_settings_sections',
			array(
				'integrations' => __( 'Integrations', AFFILIATES_PLUGIN_DOMAIN ),
				'pages'        => __( 'Pages', AFFILIATES_PLUGIN_DOMAIN ),
				'referrals'    => __( 'Referrals', AFFILIATES_PLUGIN_DOMAIN ),
				'registration' => __( 'Registration', AFFILIATES_PLUGIN_DOMAIN ),
				'general'      => __( 'General', AFFILIATES_PLUGIN_DOMAIN )
			)
		);
	}

	/**
	 * Registers an admin_notices action.
	 */
	public static function admin_init() {
		wp_register_style( 'affiliates-admin-settings', AFFILIATES_PLUGIN_URL . 'css/affiliates_admin_settings.css' );
		if ( isset( $_REQUEST['aff_setup_hide'] ) ) {
			if ( wp_verify_nonce( $_REQUEST['aff_setup_nonce'], 'aff_setup_hide' ) ) {
				add_option( 'aff_setup_hide', 'yes', '', 'no' );
			}
		}
		if ( !get_option( 'aff_setup_hide' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'setup_notice' ) );
		}
	}

	/**
	 * Prints setup notices.
	 */
	public static function setup_notice() {
		echo '<div id="message" class="updated affiliates-settings">';

		// render the welcome header and a brief explanation
		echo '<p>';
		echo
			sprintf(
				__( '<strong>Welcome to %s</strong>', AFFILIATES_PLUGIN_DOMAIN ),
				ucwords( str_replace('-', ' ', AFFILIATES_PLUGIN_NAME ) )
			);
		echo '</p>';

		echo '<p>';
		echo __( 'Please review the following suggested steps to set up the affiliate system.', AFFILIATES_PLUGIN_DOMAIN );
		echo ' ';
		echo __( 'This is intended as a guidance and you can safely hide this message when finished.', AFFILIATES_PLUGIN_DOMAIN );
		echo ' ';
		echo sprintf(
			__( 'Use the <a href="%s">Settings</a> section to review or adjust the system anytime.', AFFILIATES_PLUGIN_DOMAIN ),
			admin_url( 'admin.php?page=affiliates-admin-settings' )
		);
		echo '</p>';

		$buttons = apply_filters(
			'affiliates_setup_buttons',
			array(
				'pages' => sprintf (
					'<a href="%s" class="button-primary">%s</a>',
					add_query_arg( 'section', 'pages', admin_url( 'admin.php?page=affiliates-admin-settings' ) ),
					__( 'Create an Affiliate Area', AFFILIATES_PLUGIN_DOMAIN )
				),
				'integrations' => sprintf (
					'<a href="%s" class="button-primary">%s</a>',
					add_query_arg( 'section', 'integrations', admin_url( 'admin.php?page=affiliates-admin-settings' ) ),
					__( 'Install an Integration', AFFILIATES_PLUGIN_DOMAIN )
				),
				'registration' => sprintf (
					'<a href="%s" class="button-primary">%s</a>',
					add_query_arg( 'section', 'registration', admin_url( 'admin.php?page=affiliates-admin-settings' ) ),
					__( 'Enable Affiliate Registration', AFFILIATES_PLUGIN_DOMAIN )
				),
				'general' => sprintf(
					'<a href="%s" class="button-primary">%s</a>',
					add_query_arg( 'section', 'general', admin_url( 'admin.php?page=affiliates-admin-settings' ) ),
					__( 'Review General Settings', AFFILIATES_PLUGIN_DOMAIN )
				)
			)
		);

		// render the buttons
		echo '<p class="submit">';
		echo implode( ' ', $buttons );
		echo ' ';
		printf( '<a class="hide button" href="%s">%s</a>',
			wp_nonce_url(
				add_query_arg( 'aff_setup_hide', 'true', admin_url( 'admin.php?page=affiliates-admin-settings' ) ),
				'aff_setup_hide',
				'aff_setup_nonce'
			),
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

		self::init_sections();

		$section = isset( $_REQUEST['section'] ) ? $_REQUEST['section'] : null;

		if ( !key_exists( $section, self::$sections ) ) {
			$section = 'integrations';
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
			case 'integrations' :
				require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-integrations.php';
				Affiliates_Settings_Integrations::section();
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
