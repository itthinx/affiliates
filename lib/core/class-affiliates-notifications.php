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

/**
 * Notifications admin.
 */
class Affiliates_Notifications {

	const AFFILIATES_NOTIFICATIONS = 'affiliates_notifications';

	const REGISTRATION_ENABLED               = 'registration_enabled';
	const REGISTRATION_ENABLED_DEFAULT       = true;

	const REGISTRATION_NOTIFY_ADMIN          = 'aff_notify_admin';
	const REGISTRATION_NOTIFY_ADMIN_DEFAULT  = true;

	/**
	 * Message subject when new affiliate is pending.
	 * @var string
	 */
	const DEFAULT_REGISTRATION_PENDING_SUBJECT = 'default_registration_pending_subject';

	/**
	 * Message body when new affiliate is pending.
	 * @var string
	 */
	const DEFAULT_REGISTRATION_PENDING_MESSAGE = 'default_registration_pending_message';

	/**
	 * Message subject when new affiliate is registered and automatically active.
	 * @var string
	 */
	const DEFAULT_REGISTRATION_ACTIVE_SUBJECT  = 'default_registration_active_subject';

	/**
	 * Message body when new affiliate is registered and automatically active.
	 * @var string
	 */
	const DEFAULT_REGISTRATION_ACTIVE_MESSAGE  = 'default_registration_active_message';

	/**
	 * Default message subject when an affiliate has been accepted, passing from pending to active status.
	 * @var string
	 */
	const DEFAULT_AFFILIATE_PENDING_TO_ACTIVE_SUBJECT = 'default_affiliate_pending_to_active_subject';

	/**
	 * Default message body when an affiliate has been accepted, passing from pending to active status.
	 * @var string
	 */
	const DEFAULT_AFFILIATE_PENDING_TO_ACTIVE_MESSAGE = 'default_affiliate_pending_to_active_message';

	/**
	 * Admin message subject for new pending affiliate.
	 * @var string
	 */
	const DEFAULT_ADMIN_REGISTRATION_PENDING_SUBJECT = 'default_admin_registration_pending_subject';

	/**
	 * Admin message body for new pending affiliate.
	 * @var string
	 */
	const DEFAULT_ADMIN_REGISTRATION_PENDING_MESSAGE = 'default_admin_registration_pending_message';

	/**
	 * Admin message subject for new activated affiliate.
	 * @var string
	 */
	const DEFAULT_ADMIN_REGISTRATION_ACTIVE_SUBJECT = 'default_admin_registration_active_subject';

	/**
	 * Admin message body for new activated affiliate.
	 * @var string
	 */
	const DEFAULT_ADMIN_REGISTRATION_ACTIVE_MESSAGE = 'default_admin_registration_active_message';

	/**
	 * Singleton instance.
	 * @var Affiliates_Notifications
	 */
	private static $instance = null;

	/**
	 * Singleton constructor.
	 */
	protected function __construct() {
		self::$instance = $this;
		self::init();
	}

	/**
	 * Returns the appropriate instance for notifications.
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			if ( class_exists( 'Affiliates_Notifications_Extended' ) ) {
				new Affiliates_Notifications_Extended();
			} else {
				new Affiliates_Notifications();
			}
		}
		return self::$instance;
	}

	/**
	 * Returns the name of the related admin class.
	 * @return string
	 */
	public function get_admin_class() {
		return 'Affiliates_Admin_Notifications';
	}

	/**
	 * Returns the default message subject or message body template identified by $which.
	 * These are the defaults used to construct notifications.
	 * 
	 * @param string $which self::DEFAULT_REGISTRATION_PENDING_SUBJECT, self::DEFAULT_REGISTRATION_PENDING_MESSAGE, ...
	 * @return string message template (may contain tokens)
	 */
	public static function get_default( $which ) {
		$text = '';
		switch ( $which ) {
			case self::DEFAULT_REGISTRATION_PENDING_SUBJECT :
				$text = __( '[[site_title]] Your username and password', 'affiliates' );
				break;
			case self::DEFAULT_REGISTRATION_PENDING_MESSAGE :
				$text = __(
'Username: [username]<br/>
Password: [password]<br/>
[site_login_url]<br/>
<br/>
Your request to join the Affiliate Program is pending approval.<br/>',
					'affiliates'
				);
				break;
			case self::DEFAULT_REGISTRATION_ACTIVE_SUBJECT :
				$text = __( '[[site_title]] Your username and password', 'affiliates' );
				break;
			case self::DEFAULT_REGISTRATION_ACTIVE_MESSAGE :
				$text = __(
'Username: [username]<br/>
Password: [password]<br/>
[site_login_url]<br/>
<br/>
Thanks for joining the Affiliate Program.<br/>',
					'affiliates'
				);
				break;
			case self::DEFAULT_AFFILIATE_PENDING_TO_ACTIVE_SUBJECT :
				$text = __( '[[site_title]] Welcome to the Affiliate Program', 'affiliates' );
				break;
			case self::DEFAULT_AFFILIATE_PENDING_TO_ACTIVE_MESSAGE :
				$text = __(
'Congratulations [user_login],<br/>
<br/>
Your request to join the Affiliate Program has been approved.<br/>
[site_url]<br/>',
					'affiliates'
				);
				break;
			case self::DEFAULT_ADMIN_REGISTRATION_PENDING_SUBJECT :
				$text = __( '[[site_title]] New Affiliate Registration', 'affiliates' );
				break;
			case self::DEFAULT_ADMIN_REGISTRATION_PENDING_MESSAGE :
				$text = __(
'New affiliate registration on your site [site_title]:<br/>
<br/>
Username: [user_login]<br/>
E-mail: [user_email]<br/>
This affiliate is pending approval.<br/>',
					'affiliates'
				);
				break;
			case self::DEFAULT_ADMIN_REGISTRATION_ACTIVE_SUBJECT :
				$text = __( '[[site_title]] New Affiliate Registration', 'affiliates' );
				break;
			case self::DEFAULT_ADMIN_REGISTRATION_ACTIVE_MESSAGE :
				$text = __(
'New affiliate registration on your site [site_title]:<br/>
<br/>
Username: [user_login]<br/>
E-mail: [user_email]<br/>',
					'affiliates'
				);
				break;
		}
		return $text;
	}

	/**
	 * Adds hooks and actions for notifications.
	 */
	public static function init() {

		// registration notifications
		add_filter( 'pre_option_aff_notify_affiliate_user', array( self::$instance, 'pre_option_aff_notify_affiliate_user' ) );
		add_filter( 'affiliates_new_affiliate_user_registration_subject', array( self::$instance, 'affiliates_new_affiliate_user_registration_subject' ), 10, 2 );
		add_filter( 'affiliates_new_affiliate_user_registration_message', array( self::$instance, 'affiliates_new_affiliate_user_registration_message' ), 10, 2 );
		add_filter( 'affiliates_new_affiliate_user_registration_headers', array( self::$instance, 'affiliates_new_affiliate_user_registration_headers' ), 10, 2 );

		add_filter( 'affiliates_new_affiliate_registration_subject', array( self::$instance, 'affiliates_new_affiliate_registration_subject' ), 10, 2 );
		add_filter( 'affiliates_new_affiliate_registration_message', array( self::$instance, 'affiliates_new_affiliate_registration_message' ), 10, 2 );
		add_filter( 'affiliates_new_affiliate_registration_headers', array( self::$instance, 'affiliates_new_affiliate_registration_headers' ), 10, 2 );

		add_filter( 'affiliates_updated_affiliate_status_subject', array( self::$instance, 'affiliates_updated_affiliate_status_subject' ), 10, 4 );
		add_filter( 'affiliates_updated_affiliate_status_message', array( self::$instance, 'affiliates_updated_affiliate_status_message' ), 10, 4 );
		add_filter( 'affiliates_updated_affiliate_status_headers', array( self::$instance, 'affiliates_updated_affiliate_status_headers' ), 10, 4 );

		add_action( 'affiliates_updated_affiliate_status', array( self::$instance, 'affiliates_updated_affiliate_status' ), 10, 3 );

		add_action( 'admin_init', array( self::$instance, 'admin_init' ) );

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
		$registration_enabled = isset( $notifications[self::REGISTRATION_ENABLED] ) ? $notifications[self::REGISTRATION_ENABLED] : self::REGISTRATION_ENABLED_DEFAULT;
		if ( $registration_enabled ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;
	}

	/**
	 * Changes the admin registration email subject.
	 *
	 * @param string $subject
	 * @param array $params
	 * @return string
	 */
	public static function affiliates_new_affiliate_registration_subject( $subject, $params ) {

		$status = get_option( 'aff_status', null );
		switch ( $status ) {
			case 'pending' :
				$registration_subject = self::get_default( self::DEFAULT_ADMIN_REGISTRATION_PENDING_SUBJECT );
				break;
			case 'active':
			default:
				$registration_subject = self::get_default( self::DEFAULT_ADMIN_REGISTRATION_ACTIVE_SUBJECT );
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

		$status = get_option( 'aff_status', null );
		switch ( $status ) {
			case 'pending' :
				$registration_message = self::get_default( self::DEFAULT_ADMIN_REGISTRATION_PENDING_MESSAGE );
				break;
			case 'active':
			default:
				$registration_message = self::get_default( self::DEFAULT_ADMIN_REGISTRATION_ACTIVE_MESSAGE );
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

		$status = get_option( 'aff_status', null );
		switch ( $status ) {
			case 'pending' :
				$registration_subject = self::get_default( self::DEFAULT_REGISTRATION_PENDING_SUBJECT );
				break;
			case 'active':
			default:
				$registration_subject = self::get_default( self::DEFAULT_REGISTRATION_ACTIVE_SUBJECT );
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

		$status = get_option( 'aff_status', null );
		switch ( $status ) {
			case 'pending' :
				$registration_message = self::get_default( self::DEFAULT_REGISTRATION_PENDING_MESSAGE );
				break;
			case 'active':
			default:
				$registration_message = self::get_default( self::DEFAULT_REGISTRATION_ACTIVE_MESSAGE );
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
	public static function affiliates_updated_affiliate_status_subject( $subject, $params, $old_status, $new_status ) {

		$notifications = get_option( 'affiliates_notifications', array() );
		$status_subject = self::get_default( self::DEFAULT_AFFILIATE_PENDING_TO_ACTIVE_SUBJECT );

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
	public static function  affiliates_updated_affiliate_status_message( $message, $params, $old_status, $new_status ) {
		$notifications = get_option( 'affiliates_notifications', array() );
		$status_message = self::get_default( self::DEFAULT_AFFILIATE_PENDING_TO_ACTIVE_MESSAGE );

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
	public static function affiliates_updated_affiliate_status_headers( $headers = '', $params = array(), $old_status, $new_status ) {
		$headers .= 'Content-type: text/html; charset="' . get_option( 'blog_charset' ) . '"' . "\r\n";
		return $headers;
	}

	/**
	 * Builds an array of tokens and values based on the parameters provided.
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
					$message  = self::get_default( self::DEFAULT_AFFILIATE_PENDING_TO_ACTIVE_MESSAGE );
					$params = array(
						'user_id'  => $user_id,
						'user'     => $user,
						'username' => $user->user_login,
						'site_login_url' => wp_login_url(),
						'blogname'       => $blogname
					);
					@wp_mail(
						$user->user_email,
						apply_filters( 'affiliates_updated_affiliate_status_subject', sprintf( __( '[%s] Affiliate program', 'affiliates' ), $blogname ), $params, $old_status, $new_status ),
						apply_filters( 'affiliates_updated_affiliate_status_message', $message, $params, $old_status, $new_status ),
						apply_filters( 'affiliates_updated_affiliate_status_headers', '', $params, $old_status, $new_status )
					);
				}
			}
		}

	}

}
// trigger initialization for action and filter hooks
add_action( 'init', array( 'Affiliates_Notifications', 'get_instance' ) );
