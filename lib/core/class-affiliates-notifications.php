<?php
/**
 * class-affiliates-notifications.php
 * 
 * Copyright (c) 2010 - 2016 "kento" Karim Rahimpur www.itthinx.com
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
 * @since 2.16.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'Affiliates_Notifications' ) ) {

/**
 * Notifications admin.
 */
class Affiliates_Notifications {

	const NONCE = 'aff-admin-menu';
	const NOTIFICATIONS = 'aff-notifications';

	const AFFILIATES_NOTIFICATIONS = 'affiliates_notifications';

	const REGISTRATION_ENABLED               = 'registration_enabled';
	const REGISTRATION_ENABLED_DEFAULT       = true;

	const REGISTRATION_NOTIFY_ADMIN          = 'aff_notify_admin';
	const REGISTRATION_NOTIFY_ADMIN_DEFAULT  = true;

	public static $default_registration_pending_subject;
	public static $default_registration_pending_message;
	public static $default_registration_active_subject;
	public static $default_registration_active_message;
	public static $default_status_active_subject;
	public static $default_status_active_message;

	public static $default_admin_registration_pending_subject;
	public static $default_admin_registration_pending_message;
	public static $default_admin_registration_active_subject;
	public static $default_admin_registration_active_message;

	static $sections = null;

	/**
	 * Adds hooks and actions for notifications.
	 */
	public static function init() {

		// The emails subject and message
		self::$default_registration_pending_subject  = __( '[[site_title]] Your username and password', 'affiliates' );
		self::$default_registration_pending_message  = __(
				'Username: [username]<br/>
				Password: [password]<br/>
				[site_login_url]<br/>
				Your request to join the Affiliate Program is pending approval.',
				'affiliates' );
		self::$default_registration_active_subject  = __( '[[site_title]] Your username and password', 'affiliates' );
		self::$default_registration_active_message  = __(
				'Username: [username]<br/>
				Password: [password]<br/>
				[site_login_url]<br/>
				Thanks for joining the Affiliate Program.',
				'affiliates' );
		self::$default_status_active_subject  = __( '[[site_title]] Welcome to the Affiliate Program.', 'affiliates' );
		self::$default_status_active_message  = __(
				'Congratulations [user_login],<br />
				Your request to join the Affiliate Program has been approved.<br />',
				'affiliates' );
		self::$default_admin_registration_pending_subject = __( '[[site_title]] New Affiliate Registration', 'affiliates' );
		self::$default_admin_registration_pending_message = __(
				'New affiliate registration on your site [site_title]:<br/>
				<br/>
				Username: [user_login]<br/>
				E-mail: [user_email]<br/>
				This affiliate is pending approval.<br />',
				'affiliates' );
		self::$default_admin_registration_active_subject = __( '[[site_title]] New Affiliate Registration', 'affiliates' );
		self::$default_admin_registration_active_message = __(
				'New affiliate registration on your site [site_title]:<br/>
				<br/>
				Username: [user_login]<br/>
				E-mail: [user_email]<br/>',
				'affiliates' );

		// registration notifications
		add_filter( 'pre_option_aff_notify_affiliate_user', array( __CLASS__, 'pre_option_aff_notify_affiliate_user' ) );
		add_filter( 'affiliates_new_affiliate_user_registration_subject', array( __CLASS__, 'affiliates_new_affiliate_user_registration_subject' ), 10, 2 );
		add_filter( 'affiliates_new_affiliate_user_registration_message', array( __CLASS__, 'affiliates_new_affiliate_user_registration_message' ), 10, 2 );
		add_filter( 'affiliates_new_affiliate_user_registration_headers', array( __CLASS__, 'affiliates_new_affiliate_user_registration_headers' ), 10, 2 );

		add_filter( 'affiliates_new_affiliate_registration_subject', array( __CLASS__, 'affiliates_new_affiliate_registration_subject' ), 10, 2 );
		add_filter( 'affiliates_new_affiliate_registration_message', array( __CLASS__, 'affiliates_new_affiliate_registration_message' ), 10, 2 );
		add_filter( 'affiliates_new_affiliate_registration_headers', array( __CLASS__, 'affiliates_new_affiliate_registration_headers' ), 10, 2 );

		add_filter( 'affiliates_updated_affiliate_status_subject', array( __CLASS__, 'affiliates_updated_affiliate_status_subject' ), 10, 2 );
		add_filter( 'affiliates_updated_affiliate_status_message', array( __CLASS__, 'affiliates_updated_affiliate_status_message' ), 10, 2 );
		add_filter( 'affiliates_updated_affiliate_status_headers', array( __CLASS__, 'affiliates_updated_affiliate_status_headers' ), 10, 2 );

		add_filter( 'affiliates_updated_affiliate_status', array( __CLASS__, 'affiliates_updated_affiliate_status' ), 10, 3 );

		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );

	}

	/**
	 * Registers the affiliates-admin-notifications css style.
	 */
	public static function admin_init() {
		wp_register_style( 'affiliates-admin-notifications', AFFILIATES_PLUGIN_URL . 'css/affiliates_admin_notifications.css' );
	}

	/**
	 * Filter the registration email option, return true if the registration
	 * email should be sent (which is the default), otherwise false.
	 *
	 * @param mixed $value this is always false
	 * @return boolean
	 */
	public static function pre_option_aff_notify_affiliate_user( $value ) {
		$notifications = get_option( 'affiliates_notifications', array() );
		$registration_enabled = isset( $notifications[Affiliates_Notifications::REGISTRATION_ENABLED] ) ? $notifications[Affiliates_Notifications::REGISTRATION_ENABLED] : Affiliates_Notifications::REGISTRATION_ENABLED_DEFAULT;
		if ( $registration_enabled ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;
	}

	public static function view() {

		global $wp, $wpdb, $affiliates_options, $wp_roles;

		if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}

		wp_enqueue_style( 'affiliates-admin-notifications' );

		self::init_sections();

		// Section
		$section = isset( $_REQUEST['section'] ) ? $_REQUEST['section'] : null;

		if ( !key_exists( $section, self::$sections ) ) {
			$section = 'affiliates';
		}
		$section_title = self::$sections[$section];

		echo
		'<h1>' .
		__( 'Notifications', 'affiliates' ) .
		'</h1>';

		$section_links = array();
		foreach( self::$sections as $sec => $sec_data ) {
			$section_links[$sec] = sprintf(
					'<a class="section-link %s" href="%s">%s</a>',
					$section == $sec ? 'active' : '',
					esc_url( add_query_arg( 'section', $sec, admin_url( 'admin.php?page=affiliates-admin-notifications' ) ) ),
					$sec_data
					);
		}
		echo '<div class="section-links">';
		echo implode( ' | ', $section_links );
		echo '</div>';

		echo
		'<h2>' .
		$section_title .
		'</h2>';

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
				'affiliates'      => __( 'Affiliates', 'affiliates' ),
				'administrator'   => __( 'Administrator', 'affiliates' )
			)
		);
	}

	/**
	 * Display the Affiliates - Registration notifications section.
	 */
	public static function affiliates_registration_section () {

			if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
				wp_die( __( 'Access denied.', 'affiliates' ) );
			}

			$notifications = get_option( 'affiliates_notifications', null );
			if ( $notifications === null ) {
				add_option( 'affiliates_notifications', array(), null, 'no' );
			}

			if ( isset( $_POST['submit'] ) ) {
				if ( wp_verify_nonce( $_POST[self::NONCE], self::NOTIFICATIONS ) ) {

					$notifications[Affiliates_Notifications::REGISTRATION_ENABLED] = !empty( $_POST[Affiliates_Notifications::REGISTRATION_ENABLED] );

					update_option( 'affiliates_notifications', $notifications );
				}
			}

			$registration_enabled = isset( $notifications[Affiliates_Notifications::REGISTRATION_ENABLED] ) ? $notifications[Affiliates_Notifications::REGISTRATION_ENABLED] : Affiliates_Notifications::REGISTRATION_ENABLED_DEFAULT;

			echo '<div class="notifications">';

			echo '<div class="manage">';

			echo
			'<form action="" name="notifications" method="post">' .
			'<div>' .

			// Affiliate registration notifications

			'<h3>' . __( 'Registration notifications', 'affiliates' ) . '</h3>' .

			'<p>' .
			'<label>' .
			'<input type="checkbox" name="' . Affiliates_Notifications::REGISTRATION_ENABLED . '" id="' . Affiliates_Notifications::REGISTRATION_ENABLED . '" ' . ( $registration_enabled ? ' checked="checked" ' : '' ) . '/>' .
			__( 'Enable registration emails', 'affiliates' ) .
			'</label>' .
			'</p>' .
			'<p class="description">' .
			__( 'Send new affiliates an email when their user account is created.', 'affiliates' ) .
			' ' .
			__( 'This should normally be enabled, so that new affiliates receives their username and password to be able to log in and access their account.', 'affiliates' ) .
			'</p>' .

			'<p>' .
			wp_nonce_field( self::NOTIFICATIONS, self::NONCE, true, false ) .
			'<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', 'affiliates' ) . '"/>' .
			'</p>' .

			'</div>' .
			'</form>' .
			'</div>'; // .manage

			echo '</div>'; // .notifications

	}

	/**
	 * Display the Administrator - Registration notifications section.
	 */
	public static function administrator_registration_section () {

		if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}

		if ( isset( $_POST['submit'] ) ) {
			if ( wp_verify_nonce( $_POST[self::NONCE], self::NOTIFICATIONS ) ) {

				// admin registration enabled
				delete_option( 'aff_notify_admin' );
				add_option( 'aff_notify_admin', !empty( $_POST[Affiliates_Notifications::REGISTRATION_NOTIFY_ADMIN] ), '', 'no' );

			}
		}

		$notify_admin = get_option( 'aff_notify_admin', true );

		echo '<div class="notifications">';

		echo '<div class="manage">';

		echo
		'<form action="" name="notifications" method="post">' .
		'<div>' .

		// Administrator registration notifications

		'<h3>' . __( 'Registration notifications', 'affiliates' ) . '</h3>' .

		'<p>' .
		'<label>' .
		'<input type="checkbox" name="' . Affiliates_Notifications::REGISTRATION_NOTIFY_ADMIN . '" id="' . Affiliates_Notifications::REGISTRATION_NOTIFY_ADMIN . '" ' . ( $notify_admin ? ' checked="checked" ' : '' ) . '/>' .
		__( 'Enable registration emails', 'affiliates' ) .
		'</label>' .
		'</p>' .
		'<p class="description">' .
		__( 'Send the administrator an email when a new affiliate user account is created.', 'affiliates' ) .
		' ' .
		__( 'This should normally be enabled, especially when the status for new affiliates is pending approval by the administrator.', 'affiliates' ) .
		'</p>' .

		'<p>' .
		wp_nonce_field( self::NOTIFICATIONS, self::NONCE, true, false ) .
		'<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', 'affiliates' ) . '"/>' .
		'</p>' .

		'</div>' .
		'</form>' .
		'</div>'; // .manage

		echo '</div>'; // .notifications

	}

	/**
	 * Changes the admin registration email subject.
	 *
	 * @param string $subject
	 * @param array $params
	 * @return string
	 */
	public static function affiliates_new_affiliate_registration_subject( $subject, $params ) {

		$notifications        = get_option( 'affiliates_notifications', array() );
		$status = get_option( 'aff_status', null );
		switch ( $status ) {
			case 'pending' :
				$registration_subject = Affiliates_Notifications::$default_admin_registration_pending_subject;
				break;
			case 'active':
			default:
				$registration_subject = Affiliates_Notifications::$default_admin_registration_active_subject;
				break;
		}
		$tokens               = self::get_registration_tokens( $params );
		$subject              = self::substitute_tokens( stripslashes( $registration_subject ), $tokens );
		return $subject;
	}

	/**
	 * Changes the admin registration email message.
	 *
	 * @param string $message
	 * @param array $params
	 * @return string
	 */
	public static function affiliates_new_affiliate_registration_message( $message, $params ) {
		$notifications = get_option( 'affiliates_notifications', array() );

		$status = get_option( 'aff_status', null );

		switch ( $status ) {
			case 'pending' :
				$registration_message = Affiliates_Notifications::$default_admin_registration_pending_message;
				break;
			case 'active':
			default:
				$registration_message = Affiliates_Notifications::$default_admin_registration_active_message;
				break;
		}
		$tokens               = self::get_registration_tokens( $params );
		$message              = self::substitute_tokens( stripslashes( $registration_message ), $tokens );
		return $message;
	}

	/**
	 * Additional mail headers for wp_mail() - used to set the type to HTML.
	 *
	 * @param string $headers
	 * @param array $params
	 * @return string
	 */
	public static function affiliates_new_affiliate_registration_headers( $headers = '', $params = array() ) {
		$headers .= 'Content-type: text/html; charset="' . get_option( 'blog_charset' ) . '"' . "\r\n";
		return $headers;
	}

	/**
	 * Changes the affiliate registration email subject.
	 *
	 * @param string $subject
	 * @param array $params
	 * @return string
	 */
	public static function affiliates_new_affiliate_user_registration_subject( $subject, $params ) {

		$notifications        = get_option( 'affiliates_notifications', array() );

		$status = get_option( 'aff_status', null );
		switch ( $status ) {
			case 'pending' :
				$registration_subject = Affiliates_Notifications::$default_registration_pending_subject;
				break;
			case 'active':
			default:
				$registration_subject = Affiliates_Notifications::$default_registration_active_subject;
				break;
		}
		$tokens = self::get_registration_tokens( $params );
		$subject = self::substitute_tokens( stripslashes( $registration_subject ), $tokens );
		return $subject;
	}

	/**
	 * Changes the affiliate registration email message.
	 *
	 * @param string $message
	 * @param array $params
	 * @return string
	 */
	public static function  affiliates_new_affiliate_user_registration_message( $message, $params ) {
		$notifications = get_option( 'affiliates_notifications', array() );

		$status = get_option( 'aff_status', null );

		switch ( $status ) {
			case 'pending' :
				$registration_message = Affiliates_Notifications::$default_registration_pending_message;
				break;
			case 'active':
			default:
				$registration_message = Affiliates_Notifications::$default_registration_active_message;
				break;
		}
		$tokens = self::get_registration_tokens( $params );
		$message = self::substitute_tokens( stripslashes( $registration_message ), $tokens );
		return $message;
	}

	/**
	 * Additional mail headers for wp_mail() - used to set the type to HTML.
	 *
	 * @param string $headers
	 * @param array $params
	 * @return string
	 */
	public static function affiliates_new_affiliate_user_registration_headers( $headers = '', $params = array() ) {
		$headers .= 'Content-type: text/html; charset="' . get_option( 'blog_charset' ) . '"' . "\r\n";
		return $headers;
	}

	/**
	 * Changes the affiliate status changed email subject.
	 *
	 * @param string $subject
	 * @param array $params
	 * @return string
	 */
	public static function affiliates_updated_affiliate_status_subject( $subject, $params ) {

		$notifications = get_option( 'affiliates_notifications', array() );
		$status_subject = Affiliates_Notifications::$default_status_active_subject;

		$tokens = self::get_registration_tokens( $params );
		$subject = self::substitute_tokens( stripslashes( $status_subject ), $tokens );
		return $subject;
	}

	/**
	 * Changes the affiliate status changed email message.
	 *
	 * @param string $message
	 * @param array $params
	 * @return string
	 */
	public static function  affiliates_updated_affiliate_status_message( $message, $params ) {
		$notifications = get_option( 'affiliates_notifications', array() );
		$status_message = Affiliates_Notifications::$default_status_active_message;

		$tokens = self::get_registration_tokens( $params );
		$message = self::substitute_tokens( stripslashes( $status_message ), $tokens );
		return $message;
	}

	/**
	 * Additional mail headers for wp_mail() - used to set the type to HTML.
	 *
	 * @param string $headers
	 * @param array $params
	 * @return string
	 */
	public static function affiliates_updated_affiliate_status_headers( $headers = '', $params = array() ) {
		$headers .= 'Content-type: text/html; charset="' . get_option( 'blog_charset' ) . '"' . "\r\n";
		return $headers;
	}

	/**
	 * Builds an array of tokens adn values based on the parameters provided.
	 *
	 * These tokens are added automatically:
	 * - site_title
	 * - site_url
	 *
	 * token-string tuples are extracted from $params and included automatically.
	 *
	 * Note that at this stage the affiliate entry has not yet been created
	 * and we can not use affiliates_get_user_affiliate() to obtain the
	 * affiliate details like ID or status.
	 *
	 * This method is used internally to obtain the tokens for substitution
	 * in the affiliate user registration email subject and message.
	 *
	 * @param array $params
	 * @return array
	 */
	private static function get_registration_tokens( $params ) {
		$tokens = array();
		foreach( $params as $key => $value ) {
			if ( is_string( $value ) ) {
				$tokens[$key] = $value;
			}
		}
		$tokens['site_title'] = wp_specialchars_decode( get_bloginfo( 'blogname' ), ENT_QUOTES );
		$tokens['site_url']   = get_bloginfo( 'url' );
		if ( isset( $params['user_id'] ) ) {
			$user_id = intval( $params['user_id'] );
			if ( ( $user = get_user_by( 'id', $user_id ) ) ) {
				require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings.php';
				require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-registration.php';
				$registration_fields = Affiliates_Settings_Registration::get_fields();
				// unset( $registration_fields['password'] );
				if ( !empty( $registration_fields ) ) {
					foreach( $registration_fields as $name => $field ) {
						if ( $field['enabled'] ) {
							$type = isset( $field['type'] ) ? $field['type'] : 'text';
							switch( $name ) {
								case 'user_login' :
									$value = $user->user_login;
									break;
								case 'user_email' :
									$value = $user->user_email;
									break;
								case 'user_url' :
									$value = $user->user_url;
									break;
								case 'password' :
									$value = '';
									break;
								default :
									$value = get_user_meta( $user_id, $name , true );
							}
							if ( !isset( $tokens[$name] ) ) {
								$tokens[$name]  = esc_attr( stripslashes( $value ) );
							}
						}
					}
				}
			}
		}
		$tokens = apply_filters(
				'affiliates_registration_tokens',
				$tokens
				);
		return $tokens;
	}

	/**
	 * Substitutes tokens found in subject $s.
	 *
	 * @param string $s
	 * @param array $tokens
	 * @return string
	 */
	private static function substitute_tokens( $s, $tokens ) {
		foreach ( $tokens as $key => $value ) {
			if ( key_exists( $key, $tokens ) ) {
				$substitute = $tokens[$key];
				$s = str_replace( "[" . $key . "]", $substitute, $s );
			}
		}
		return $s;
	}

	/**
	 * Notify the affiliate of his status changed.
	 * Notification is sent when the status change:
	 * - From pending to active
	 *
	 * @param int $user_id User ID
	 */
	public static function affiliates_updated_affiliate_status( $affiliate_id, $old_status, $new_status ) {

		if ( ( $old_status == 'pending' ) && ( $new_status == 'active' ) ) {
			$user_id = affiliates_get_affiliate_user ( $affiliate_id );
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			if ( $user = get_userdata( $user_id ) ) {
				if ( get_option( 'aff_notify_affiliate_user', 'yes' ) != 'no' ) {
					$message  = Affiliates_Notifications::$default_status_active_message;
					$params = array(
						'user_id'  => $user_id,
						'user'     => $user,
						'username' => $user->user_login,
						'site_login_url' => wp_login_url(),
						'blogname'       => $blogname
					);
					@wp_mail(
						$user->user_email,
						apply_filters( 'affiliates_updated_affiliate_status_subject', sprintf( __( '[%s] Affiliate program', 'affiliates' ), $blogname ), $params ),
						apply_filters( 'affiliates_updated_affiliate_status_message', $message, $params ),
						apply_filters( 'affiliates_updated_affiliate_status_headers', '', $params )
					);
				}
			}
		}

	}

}

add_action( 'init', array( 'Affiliates_Notifications', 'init' ) );

} // if
