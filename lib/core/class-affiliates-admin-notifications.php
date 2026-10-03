<?php
/**
 * class-affiliates-admin-menu-wordpress.php
 *
 * Copyright (c) 2016 "kento" Karim Rahimpur www.itthinx.com
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
 * @package affiliates-pro
 * @since affiliates-pro 2.16.0
 */

/**
 * Admin view for notifications.
 */
class Affiliates_Admin_Notifications {

	const NONCE = 'aff-admin-menu';
	const NOTIFICATIONS = 'aff-notifications';

	/**
	 * Holds the administrative sections related to notifications.
	 * @var array
	 */
	protected static $sections = null;

	/**
	 * Administrative interface.
	 */
	public static function view() {

		if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			wp_die( esc_html__( 'Access denied.', 'affiliates' ) );
		}

		wp_enqueue_style( 'affiliates-admin-notifications' );

		self::init_sections();

		// Section
		$section = sanitize_text_field( wp_unslash( $_REQUEST['section'] ?? '' ) );

		if ( !array_key_exists( $section, self::$sections ) ) {
			$section = 'affiliates';
		}
		$section_title = self::$sections[$section];

		echo
			'<h1>' .
			esc_html__( 'Notifications', 'affiliates' ) .
			'</h1>';

		$section_links = '';
		foreach( self::$sections as $sec => $sec_data ) {
			$section_links .= sprintf(
				'<a class="section-link nav-tab %s" href="%s">%s</a>',
				$section == $sec ? 'active nav-tab-active' : '',
				esc_url( add_query_arg( 'section', $sec, admin_url( 'admin.php?page=affiliates-admin-notifications' ) ) ),
				$sec_data
			);
		}
		echo '<div class="section-links nav-tab-wrapper">';
		echo $section_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';

		echo '<h2>' . esc_html( $section_title ) . '</h2>';

		switch ( $section ) {
			case 'administrator' :
				self::administrator_registration_section();
				break;
			case 'affiliates' :
			default :
				self::affiliates_registration_section();
				break;
		}

		affiliates_footer();
	}

	/**
	 * Settings sections.
	 *
	 * @return array
	 */
	public static function init_sections() {
		self::$sections = apply_filters(
			'affiliates_notifications_sections',
			array(
				'affiliates'    => __( 'Affiliates', 'affiliates' ),
				'administrator' => __( 'Administrator', 'affiliates' )
			)
		);
	}

	/**
	 * Display the Affiliates - Registration notifications section.
	 */
	public static function affiliates_registration_section () {

		if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			wp_die( esc_html__( 'Access denied.', 'affiliates' ) );
		}

		$notifications = get_option( 'affiliates_notifications', null );
		if ( $notifications === null ) {
			add_option( 'affiliates_notifications', array(), '', 'no' );
		}

		if ( isset( $_POST['submit'] ) ) {
			if ( affiliates_verify_post_nonce( $_POST[self::NONCE], self::NOTIFICATIONS ) ) {
				$notifications[Affiliates_Notifications::REGISTRATION_ENABLED] = !empty( $_POST[Affiliates_Notifications::REGISTRATION_ENABLED] );
				update_option( 'affiliates_notifications', $notifications );
			}
		}

		$registration_enabled = isset( $notifications[Affiliates_Notifications::REGISTRATION_ENABLED] ) ? $notifications[Affiliates_Notifications::REGISTRATION_ENABLED] : Affiliates_Notifications::REGISTRATION_ENABLED_DEFAULT;

		echo '<div class="notifications">';
		echo '<div class="manage">';
		echo '<form action="" name="notifications" method="post">';
		echo '<div>';

		// Affiliate registration notifications

		echo '<h3>' . esc_html__( 'Registration notifications', 'affiliates' ) . '</h3>';

		echo '<p>';
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr( Affiliates_Notifications::REGISTRATION_ENABLED ) . '" id="' . esc_attr( Affiliates_Notifications::REGISTRATION_ENABLED ) . '" ' . ( $registration_enabled ? ' checked="checked" ' : '' ) . '/>';
		echo esc_html__( 'Enable registration emails', 'affiliates' );
		echo '</label>';
		echo '</p>';
		echo '<p class="description">';
		echo esc_html__( 'Send new affiliates an email when their user account is created.', 'affiliates' );
		echo ' ';
		echo esc_html__( 'This should normally be enabled, so that new affiliates receive their username and password to be able to log in and access their account.', 'affiliates' );
		echo '</p>';

		echo '<p>';
		echo wp_nonce_field( self::NOTIFICATIONS, self::NONCE, true, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<input class="button button-primary" type="submit" name="submit" value="' . esc_html__( 'Save', 'affiliates' ) . '"/>';
		echo '</p>';

		echo '</div>';
		echo '</form>';
		echo '</div>'; // .manage
		echo '</div>'; // .notifications

	}

	/**
	 * Display the Administrator - Registration notifications section.
	 */
	public static function administrator_registration_section () {

		if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			wp_die( esc_html__( 'Access denied.', 'affiliates' ) );
		}

		if ( isset( $_POST['submit'] ) ) {
			if ( affiliates_verify_post_nonce( self::NONCE, self::NOTIFICATIONS ) ) {
				// admin registration enabled
				delete_option( 'aff_notify_admin' );
				add_option( 'aff_notify_admin', !empty( $_POST[Affiliates_Notifications::REGISTRATION_NOTIFY_ADMIN] ), '', 'no' );
			}
		}

		$notify_admin = get_option( 'aff_notify_admin', true );

		echo '<div class="notifications">';
		echo '<div class="manage">';
		echo '<form action="" name="notifications" method="post">';
		echo '<div>';

		// Administrator registration notifications

		echo '<h3>' . esc_html__( 'Registration notifications', 'affiliates' ) . '</h3>';

		echo '<p>';
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr( Affiliates_Notifications::REGISTRATION_NOTIFY_ADMIN ) . '" id="' . esc_attr( Affiliates_Notifications::REGISTRATION_NOTIFY_ADMIN ) . '" ' . ( $notify_admin ? ' checked="checked" ' : '' ) . '/>';
		echo esc_html__( 'Enable registration emails', 'affiliates' );
		echo '</label>';
		echo '</p>';
		echo '<p class="description">';
		echo esc_html__( 'Send the administrator an email when a new affiliate user account is created.', 'affiliates' );
		echo ' ';
		echo esc_html__( 'This should normally be enabled, especially when the status for new affiliates is pending approval by the administrator.', 'affiliates' );
		echo '</p>';

		echo '<p>';
		echo wp_nonce_field( self::NOTIFICATIONS, self::NONCE, true, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<input class="button button-primary" type="submit" name="submit" value="' . esc_attr__( 'Save', 'affiliates' ) . '"/>';
		echo '</p>';

		echo '</div>';
		echo '</form>';
		echo '</div>'; // .manage

		echo '</div>'; // .notifications

	}

	/**
	 * Adds help tabs.
	 */
	public static function load_page() {
	}
}
