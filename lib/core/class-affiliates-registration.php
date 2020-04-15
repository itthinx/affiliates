<?php
/**
 * class-affiliates-registration.php
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
 * @since affiliates 1.1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Affiliate registration form.
 * 
 * Deleting users vs. removing affiliates :
 * 
 * - If a user is deleted, the affiliate is marked as deleted and the
 *   association is released.
 * - Marking an affiliate as deleted (pressing Remove) marks the affiliate
 *   as deleted but does not delete the user, the association is maintained.
 * 
 * @todo WPML field translations, also on form output
 * 
 * @link http://wpml.org/documentation/support/translation-for-texts-by-other-plugins-and-themes/
 * 
 * icl_register_string('Contact Form 7', 'Input field label', 'Profession');
 * icl_unregister_string ( string $context, string $name );
 * icl_translate ( string $context, string $name, string $value );
 */
class Affiliates_Registration {

	/**
	 * Accepted form parameters.
	 * 
	 * @var array
	 */
	private static $defaults = array(
		'is_widget'                    => false,
		'registered_profile_link_text' => null,
		'registered_profile_link_url'  => null,
		'redirect'                     => false,
		'redirect_to'                  => null,
		'submit_button_label'          => null,
		'terms_post_id'                => null
	);

	/**
	 * Not stored as user meta.
	 * 
	 * @var array
	 */
	private static $skip_meta_fields = array(
		'user_login',
		'user_email',
		'user_url',
		'user_pass',
		'password'
	);

	/**
	 * Returns the keys not stored as user meta.
	 * 
	 * @return array
	 */
	public static function get_skip_meta_fields() {
		return self::$skip_meta_fields;
	}

	private static $submit_button_label = null;

	/**
	 * Class initialization.
	 */
	public static function init() {

		// registration form shortcode
		add_shortcode( 'affiliates_registration', array( __CLASS__, 'affiliates_registration_shortcode' ) );

		// delete affiliate when user is deleted
		add_action( 'deleted_user', array( __CLASS__, 'deleted_user' ) );

		add_action( 'init', array( __CLASS__, 'wp_init' ) );
	}

	/**
	 * Registers registration field labels with WPML.
	 *
	 * @link https://wpml.org/documentation/support/translation-for-texts-by-other-plugins-and-themes/
	 */
	public static function wp_init() {
		if ( defined( 'AFFILIATES_WPML' ) && AFFILIATES_WPML ) {
			require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings.php';
			require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-registration.php';
			$registration_fields = Affiliates_Settings_Registration::get_fields();
			if ( is_array( $registration_fields ) ) {
				foreach( $registration_fields as $name => $field ) {
					// context, name, value
					do_action( 'wpml_register_single_string', 'affiliates', self::get_wpml_string_name( $name ), $field['label'] );
				}
			}
		}
	}

	/**
	 * Returns the string's registered name for use with WPML string translation.
	 *
	 * @param string $name
	 *
	 * @return string registered name
	 */
	private static function get_wpml_string_name( $name ) {
		return sprintf( 'Field Label: %s', esc_attr( $name ) );
	}

	/**
	 * Registration form shortcode handler.
	 *
	 * @param array $atts attributes
	 * @param string $content not used
	 */
	public static function affiliates_registration_shortcode( $atts, $content = null ) {
		$options = shortcode_atts( self::$defaults, $atts );
		return self::render_form( $options );
	}

	/**
	 * Registration form.
	 * 
	 * @see Affiliates_Registration::$defaults for accepted parameters
	 * 
	 * @param array $options form options
	 * @return string rendered registration form
	 */
	public static function render_form( $options = array() ) {

		wp_enqueue_style( 'affiliates' );

		self::$submit_button_label = __( 'Sign Up', 'affiliates' );

		$output = '';

		//
		// Existing affiliate pending approval
		//
		if ( affiliates_user_is_affiliate_status( null, 'pending' ) ) {
			$output .= '<div class="affiliates-registration registered pending">';
			$output .= '<p>';
			$output .= __( 'Your affiliate application is pending approval.', 'affiliates' );
			$output .= '</p>';
			$output .= '</div>';
			return $output;
		}

		//
		// Existing affiliate
		//
		if ( $is_affiliate = affiliates_user_is_affiliate() ) {
			$output .= '<div class="affiliates-registration registered">';
			$output .= '<p>';
			$output .= __( 'You are already registered as an affiliate.', 'affiliates' );
			$output .= '</p>';
			if ( isset( $options['registered_profile_link_url'] ) ) {
				$output .= '<p>';
				$output .= '<a href="' . esc_url( $options['registered_profile_link_url'] ) . '">';
				if ( isset( $options['registered_profile_link_text'] ) ) {
					$output .= wp_filter_kses( $options['registered_profile_link_text'] );
				} else {
					$output .= __( 'Access your profile', 'affiliates' );
				}
				$output .= '</a>';
				$output .= '</p>';
			}
			$output .= '</div>';
			return $output;
		}

		//
		// Registration closed
		//
		if ( !get_option( 'aff_registration', get_option( 'users_can_register', false ) ) ) {
			$output .= '<p>' . __( 'Registration is currently closed.', 'affiliates' ) . '</p>';
			return $output;
		}

		require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings.php';
		require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-registration.php';
		$registration_fields = Affiliates_Settings_Registration::get_fields();

		//
		// Gather user info
		//
		$user = null;
		if ( $is_logged_in = is_user_logged_in() ) {
			$user = wp_get_current_user();

			if ( isset( $registration_fields['first_name'] ) && $registration_fields['first_name']['enabled'] ) {
				$first_name = $user->first_name;
				$first_name = sanitize_user_field( 'first_name', $first_name, $user->ID, 'display' );
				$registration_fields['first_name']['value'] = $first_name;
			}

			if ( isset( $registration_fields['last_name'] ) && $registration_fields['last_name']['enabled'] ) {
				$last_name  = $user->last_name;
				$last_name  = sanitize_user_field( 'last_name', $last_name, $user->ID, 'display' );
				$registration_fields['last_name']['value'] = $last_name;
			}

			if ( isset( $registration_fields['user_login'] ) && $registration_fields['user_login']['enabled'] ) {
				$user_login = $user->user_login;
				$user_login = sanitize_user_field( 'user_login', $user_login, $user->ID, 'display' );
				$registration_fields['user_login']['value'] = $user_login;
			}

			if ( isset( $registration_fields['user_email'] ) && $registration_fields['user_email']['enabled'] ) {
				$user_email      = $user->user_email;
				$user_email      = sanitize_user_field( 'email', $user_email, $user->ID, 'display' );
				$registration_fields['user_email']['value'] = $user_email;
			}

			if ( isset( $registration_fields['user_url'] ) && $registration_fields['user_url']['enabled'] ) {
				$url        = $user->user_url;
				$url        = sanitize_user_field( 'user_url', $url, $user->ID, 'display' );
				$registration_fields['user_url']['value'] = $url;
			}
		}

		$submit_name        = 'affiliates-registration-submit';
		$nonce              = 'affiliates-registration-nonce';
		$nonce_action       = 'affiliates-registration';
		$send               = false;
		$captcha            = '';
		$error              = false;

		if ( !empty( $_POST[$submit_name] ) ) {

			if ( !wp_verify_nonce( $_POST[$nonce], $nonce_action ) ) {
				$error = true; // fail but don't give clues
			}

			$captcha = !empty( $_POST[Affiliates_Utility::get_captcha_field_id()] ) ? $_POST[Affiliates_Utility::get_captcha_field_id()] : null;
			if ( !Affiliates_Utility::captcha_validates( $captcha ) ) {
				$error = true; // dumbot
			}

			// gather field values
			foreach( $registration_fields as $name => $field ) {
				if ( $field['enabled'] ) {
					$value = isset( $_POST[$name] ) ? $_POST[$name] : '';
					$value = Affiliates_Utility::filter( $value );
					if ( $field['required'] && empty( $value ) ) {
						$error = true;
						$output .= '<div class="error">';
						$output .= __( '<strong>ERROR</strong>', 'affiliates' );
						$output .= ' : ';
						$output .= sprintf( __( 'Please fill out the field <em>%s</em>.', 'affiliates' ), $field['label'] );
						$output .= '</div>';
					}
					$registration_fields[$name]['value'] = $value;
				}
			}

			$error = apply_filters( 'affiliates_registration_error_validate', $error );

			if ( !$error ) {

				$userdata = array();
				foreach( $registration_fields as $name => $field ) {
					if ( $registration_fields[$name]['enabled'] ) {
						$userdata[$name] = $registration_fields[$name]['value'];
					}
				}

				// don't try to create a new user on multiple renderings
				global $affiliate_user_id, $new_affiliate_registered, $stored_affiliate;
				if ( !isset( $affiliate_user_id ) ) {
					if ( !$is_logged_in ) {
						// allow plugins to be aware of new user account being created
						do_action( 'affiliates_before_register_affiliate', $userdata );
						// create the affiliate user account
						$affiliate_user_id = self::register_affiliate( $userdata );
						$new_affiliate_registered = true;
						do_action( 'affiliates_after_register_affiliate', $userdata );
					} else {
						$affiliate_user_id = $user->ID;
						$new_affiliate_registered = true;
					}
				}

				// register as affiliate
				if ( !is_wp_error( $affiliate_user_id ) ) {

					// add affiliate entry
					$send = true;

					if ( !isset( $stored_affiliate ) ) {
						if ( $new_affiliate_registered ) {
							$affiliate_id = self::store_affiliate( $affiliate_user_id, $userdata );
							// update user including meta
							self::update_affiliate_user( $affiliate_user_id, $userdata );
							do_action( 'affiliates_stored_affiliate', $affiliate_id, $affiliate_user_id );
						}
						$stored_affiliate = true;
					}

					$is_widget    = isset( $options['is_widget'] ) && ( $options['is_widget'] === true || $options['is_widget'] == 'true' );
					$redirect     = isset( $options['redirect'] ) && ( $options['redirect'] === true || $options['redirect'] == 'true' );
					$redirect_url =
						empty( $_REQUEST['redirect_to'] ) ?
						apply_filters( 'affiliates_registration_login_redirect_url', get_home_url( get_current_blog_id(), 'wp-login.php?checkemail=confirm' ) ) :
						$_REQUEST['redirect_to'];

					if ( $redirect && !$is_widget && !headers_sent() ) {
						wp_safe_redirect( $redirect_url );
						exit();
					} else {
						$output .= apply_filters( 'affiliates_thanks_sign_up_text', '<p>' . esc_html__( 'Thanks for signing up!', 'affiliates' ) . '</p>' );
						if ( !$is_logged_in ) {
							$output .= apply_filters( 'affiliates_check_confirmation_text', '<p>' . esc_html__( 'Please check your email for the confirmation link.', 'affiliates' ) . '</p>' );
							if ( $redirect && !$is_widget ) {
								$output .= '<script type="text/javascript">window.location="' . esc_url( $redirect_url ) . '";</script>';
							} else {
								$show_login = isset( $options['show_login'] ) ? $options['show_login'] : false;
								switch ( $show_login ) {
									case true :
									case 'true' :
									case 'yes' :
										$show_login = true;
										break;
									default :
										$show_login = false;
								}
								if ( $show_login ) {
									$login_url = !empty( $options['login_url'] ) ? $options['login_url'] : get_home_url( get_current_blog_id(), 'wp-login.php?checkemail=confirm' );
									$login_url = apply_filters( 'affiliates_registration_login_redirect_url', $login_url );
									$output .= '<p>';
									$output .= wp_kses(
										sprintf(
											__( 'Log in <a href="%s">here</a>.', 'affiliates' ),
											esc_url( $login_url )
										),
										array( 'a' => array( 'href' => array() ) )
									);
									$output .= '</p>';
								}
							}
						} else {
							if ( isset( $options['registered_profile_link_url'] ) ) {
								$output .= '<p>';
								$output .= '<a href="' . esc_url( $options['registered_profile_link_url'] ) . '">';
								if ( isset( $options['registered_profile_link_text'] ) ) {
									$output .= wp_filter_kses( $options['registered_profile_link_text'] );
								} else {
									$output .= esc_html__( 'Access your profile', 'affiliates' );
								}
								$output .= '</a>';
								$output .= '</p>';
							}
						}
					}

				} else { // is_wp_error( $affiliate_user_id ), user registration failed

					$error    = true;
					$wp_error = $affiliate_user_id;
					if ( $wp_error->get_error_code() ) {
						$errors   = array();
						$messages = array();
						foreach ( $wp_error->get_error_codes() as $code ) {
							$severity = $wp_error->get_error_data( $code );
							foreach ( $wp_error->get_error_messages( $code ) as $error ) {
								if ( 'message' == $severity ) {
									$messages[] = $error;
								} else {
									$errors[] = $error;
								}
							}
						}
						if ( !empty( $errors ) ) {
							$output .= '<div class="error">';
							$output .= apply_filters( 'login_errors', implode( '<br />', $errors ) );
							$output .= '</div>';
						}
						if ( !empty( $messages ) ) {
							$output .= '<div class="message">';
							$output .= apply_filters( 'login_messages', implode( '<br />', $messages ) );
							$output .= '</div>';
						}
					}
				}
			}

		}

		// Registration form
		if ( !$send ) {

			$terms_post_id = get_option( 'aff_registration_terms_post_id', '' );
			if ( !empty( $terms_post_id ) || isset( $options['terms_post_id'] ) ) {
				if ( empty( $terms_post_id ) ) {
					$terms_post_id = $options['terms_post_id'];
				}
				$terms_post = get_post( $terms_post_id );
				if ( $terms_post ) {
					$terms_post_link = '<a target="_blank" href="' . esc_url( get_permalink( $terms_post->ID ) ) . '">' . get_the_title( $terms_post->ID ) . '</a>';
					$terms = sprintf(
						apply_filters( 'affiliates_terms_post_link_text', __( 'By signing up, you indicate that you have read and agree to the %s.', 'affiliates' ) ),
						$terms_post_link
					);
				}
			}

			$output .= '<div class="affiliates-registration" id="affiliates-registration">';
			$output .= '<img id="affiliates-registration-throbber" src="' . AFFILIATES_PLUGIN_URL . 'images/affiliates-throbber.gif" style="display:none" />';
			$output .= '<form id="affiliates-registration-form" method="post">';
			$output .= '<div>';

			$output .= apply_filters( 'affiliates_registration_before_fields', '' );
			$output .= self::render_fields( $registration_fields );
			$output .= apply_filters( 'affiliates_registration_after_fields', '' );

			if ( isset( $terms ) ) {
				$output .= '<div class="terms">' . $terms . '</div>';
			}
			$output .= Affiliates_Utility::captcha_get( $captcha );

			$output .= wp_nonce_field( $nonce_action, $nonce, true, false );

			if ( isset( $options['redirect_to'] ) ) {
				$output .= '<input type="hidden" name="redirect_to" value="'. esc_url( $options['redirect_to'] ) . '" />';
			}

			$output .= '<div class="sign-up">';
			$output .= '<input type="submit" name="' . $submit_name . '" value="'. self::$submit_button_label . '" />';
			$output .= '</div>';

			$output .= '</div>';
			$output .= '</form>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Renders the registration form fields.
	 * 
	 * @return string
	 */
	public static function render_fields( $registration_fields = null ) {
		$output = '';
		require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings.php';
		require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-registration.php';
		if ( $registration_fields === null ) {
			$registration_fields = Affiliates_Settings_Registration::get_fields();
		}
		foreach( $registration_fields as $name => $field ) {
			if ( $field['enabled'] ) {
				$output .= '<div class="field">';
				$output .= '<label>';
				$label = $field['label'];
				// Translate registration field labels with WPML String Translation?
				// https://wpml.org/documentation/support/translation-for-texts-by-other-plugins-and-themes/
				// https://wpml.org/wpml-hook/wpml_translate_single_string/
				if ( defined( 'AFFILIATES_WPML' ) && AFFILIATES_WPML ) {
					// original value, domain, name, language code (optional and not used here)
					$label = apply_filters( 'wpml_translate_single_string', $field['label'], 'affiliates', self::get_wpml_string_name( $name ) );
				}
				$output .= stripslashes( $label );
				$output .= ' ';
				$type = isset( $field['type'] ) ? $field['type'] : 'text';
				$readonly = is_user_logged_in() && ( ( $name == 'user_login' ) || ( $name == 'user_email' ) ) ? ' readonly="readonly" ' : '';
				$output .= sprintf(
					'<input type="%s" class="%s" name="%s" value="%s" %s %s />',
					esc_attr( $type ),
					esc_attr( $name ) . ( $field['required'] ? ' required ' : '' ),
					esc_attr( $name ),
					esc_attr( stripslashes( isset( $field['value'] ) ? $field['value'] : '' ) ),
					$field['required'] ? ' required="required" ' : '',
					$readonly
				);
				$output .= '</label>';
				$output .= '</div>';
			}
		}
		return $output;
	}

	/**
	 * Register a new affiliate user.
	 *
	 * @param array $userdata
	 * @return int|WP_Error Either user's ID or error on failure.
	 */
	public static function register_affiliate( $userdata ) {
		$errors = new WP_Error();

		$user_email = apply_filters( 'user_registration_email', $userdata['user_email'] );
		if ( isset( $userdata['user_login'] ) ) {
			$sanitized_user_login = sanitize_user( $userdata['user_login'] );
		} else {
			$sanitized_user_login = sanitize_user( $user_email );
		}

		// Check the username
		if ( $sanitized_user_login == '' ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Please enter a username.', 'affiliates' ) );
		} elseif ( ! validate_username( $sanitized_user_login ) ) {
			$errors->add( 'invalid_username', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'affiliates' ) );
			$sanitized_user_login = '';
		} elseif ( username_exists( $sanitized_user_login ) ) {
			$errors->add( 'username_exists', __( '<strong>ERROR</strong>: This username is already registered, please choose another one.', 'affiliates' ) );
		}

		// Check the e-mail address
		if ( $user_email == '' ) {
			$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please type your e-mail address.', 'affiliates' ) );
		} elseif ( ! is_email( $user_email ) ) {
			$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.', 'affiliates' ) );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.', 'affiliates' ) );
		}

		do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		// use user-provided password if present
		if ( !empty( $userdata['password'] ) ) {
			$user_pass = $userdata['password'];
		} else {
			$user_pass = wp_generate_password( AFFILIATES_REGISTRATION_PASSWORD_LENGTH, false );
		}

		$userdata['first_name'] = sanitize_text_field( $userdata['first_name'] );
		$userdata['last_name']  = sanitize_text_field( $userdata['last_name'] );
		$userdata['user_login'] = $sanitized_user_login;
		$userdata['user_email'] = $user_email;
		$userdata['password']   = $user_pass;
		if ( !empty( $userdata['user_url'] ) ) {
			$userdata['user_url'] = esc_url_raw( $userdata['user_url'] );
			$userdata['user_url'] = preg_match( '/^(https?|ftps?|mailto|news|irc|gopher|nntp|feed|telnet):/is', $userdata['user_url'] ) ? $userdata['user_url'] : 'http://' . $userdata['user_url'];
		}
		// create affiliate entry
		$user_id = self::create_affiliate( $userdata );

		if ( ! $user_id ) {
			$errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !' ), get_option( 'admin_email' ) ) );
			return $errors;
		}

		update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

		// notify new user
		self::new_user_notification( $user_id, $user_pass );

		return $user_id;
	}

	/**
	 * Updates an affiliate user.
	 *
	 * @access private
	 * @param array $userdata
	 * @return int|WP_Error Either user's ID or error on failure.
	 */
	public static function update_affiliate_user( $user_id, $userdata ) {

		global $wpdb, $affiliates_update_affiliate_user;

		if ( !isset( $affiliates_update_affiliate_user ) ) {
			$affiliates_update_affiliate_user = true;
		} else {
			return;
		}

		$errors = new WP_Error();

		if ( ( $user = get_user_by( 'id', $user_id ) ) && affiliates_user_is_affiliate( $user_id ) ) {

			$new_password = false;

			$user_email = apply_filters( 'user_registration_email', $userdata['user_email'] );

			// Check the e-mail address
			if ( $user_email == '' ) {
				$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please type your e-mail address.', 'affiliates' ) );
			} elseif ( ! is_email( $user_email ) ) {
				$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.', 'affiliates' ) );
				$user_email = '';
			} elseif ( $other_user_id = email_exists( $user_email ) ) {
				if ( $other_user_id != $user_id ) {
					$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.', 'affiliates' ) );
				}
			}

			if ( $errors->get_error_code() ) {
				return $errors;
			}

			$userdata['first_name'] = sanitize_text_field( $userdata['first_name'] );
			$userdata['last_name']  = sanitize_text_field( $userdata['last_name'] );
			$userdata['user_email'] = $user_email;
			if ( !empty( $userdata['user_url'] ) ) {
				$userdata['user_url'] = esc_url_raw( $userdata['user_url'] );
				$userdata['user_url'] = preg_match( '/^(https?|ftps?|mailto|news|irc|gopher|nntp|feed|telnet):/is', $userdata['user_url'] ) ? $userdata['user_url'] : 'http://' . $userdata['user_url'];
			}

			// update affiliate user and affiliate entry
			$_userdata = array(
				'ID'         => $user_id,
				'first_name' => esc_sql( $userdata['first_name'] ),
				'last_name'  => esc_sql( $userdata['last_name'] ),
				'user_email' => esc_sql( $userdata['user_email'] ),
			);
			if ( !empty( $userdata['password'] ) ) {
				// Don't pass the new password through wp_update_user as we risk
				// sending headers again when wp_update_user() clears the cookies while
				// the new password is set. Instead, we set the new password here and
				// only update the other user data below.
				$new_password = true;
				wp_set_password( $userdata['password'], $user_id );
			}
			if ( isset( $userdata['user_url'] ) ) {
				$_userdata['user_url'] = esc_sql( $userdata['user_url'] );
			}

			// update user and affiliate entry
			$user_id = wp_update_user( $_userdata ); // if WP_Error it's returned below

			if ( !is_wp_error( $user_id ) ) {

				// add user meta from remaining fields
				foreach( $userdata as $meta_key => $meta_value ) {
					if ( !key_exists( $meta_key, $_userdata ) && ( !in_array( $meta_key, self::$skip_meta_fields) ) ) {
						update_user_meta( $user_id, $meta_key, maybe_unserialize( $meta_value ) );
					}
				}

				// update affiliate entry
				$affiliate_ids = affiliates_get_user_affiliate( $user_id );
				if ( $affiliate_id = array_shift( $affiliate_ids ) ) {

					$affiliates_table = _affiliates_get_tablename( 'affiliates' );
					$query = $wpdb->prepare(
						"UPDATE $affiliates_table SET name = %s, email = %s WHERE affiliate_id = %d",
						$_userdata['first_name'] . ' ' . $_userdata['last_name'],
						$_userdata['user_email'],
						intval( $affiliate_id )
					);
					if ( $wpdb->query( $query ) ) {
						do_action( 'affiliates_updated_affiliate', $affiliate_id );
					}
				}
				// @todo headers already sent notices with WC when password is changed ... for example
				// wp_woocommerce_session_... cookie cannot be set - headers already sent by .../wp-content/themes/twentytwelve/header.php on line 13 in .../wp-content/plugins/woocommerce/includes/wc-core-functions.php on line 469
				// below doesn't solve this and we don't want to force logout/login on password change anyway
// 				if ( $new_password ) {
// 					$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
// 					wp_redirect( wp_login_url( $current_url ) );
// 					exit;
// 				}
			}
		}
		return $user_id;
	}

	/**
	 * Create an affiliate user.
	 * 
	 * @param array $userdata
	 * @return int|WP_Error user ID or error
	 */
	public static function create_affiliate( $userdata ) {
		$_userdata = array(
			'first_name' => esc_sql( $userdata['first_name'] ),
			'last_name'  => esc_sql( $userdata['last_name'] ),
			'user_login' => esc_sql( $userdata['user_login'] ),
			'user_email' => esc_sql( $userdata['user_email'] ),
			'user_pass'  => esc_sql( $userdata['password'] )
		);
		if ( isset( $userdata['user_url'] ) ) {
			$_userdata['user_url'] = esc_sql( $userdata['user_url'] );
		}

		global $create_affiliate_userdata;
		$create_affiliate_userdata = $userdata;

		$user_id = wp_insert_user( $_userdata );
		if ( !is_wp_error( $user_id ) ) {
			// add user meta from remaining fields
			foreach( $userdata as $meta_key => $meta_value ) {
				if ( !key_exists( $meta_key, $_userdata ) && ( !in_array( $meta_key, self::$skip_meta_fields) ) ) {
					add_user_meta( $user_id, $meta_key, maybe_unserialize( $meta_value ) );
				}
			}
		}
		return $user_id;
	}

	/**
	 * Creates an affiliate entry and relates it to a user.
	 * Notifies site admin of affiliate registration.
	 * 
	 * @param int $user_id user id
	 * @param array $userdata affiliate data
	 * @param string $status affiliate status
	 * @return int|boolean if successful new affiliate's id, otherwise false
	 */
	public static function store_affiliate( $user_id, $userdata, $status = null ) {
		global $wpdb;

		$result = false;

		$affiliates_table = _affiliates_get_tablename( 'affiliates' );
		$today = date( 'Y-m-d', time() );
		$name = $userdata['first_name'] . " " . $userdata['last_name'];
		$email = $userdata['user_email'];
		$data = array(
			'name' => esc_sql( $name ),
			'email' => esc_sql( $email ),
			'from_date' => esc_sql( $today ),
		);
		$formats = array( '%s', '%s', '%s' );
		// pending affiliate status?
		switch( $status ) {
			case 'active' :
			case 'pending' :
				$affiliate_status = $status;
				break;
			default :
				$affiliate_status = get_option( 'aff_status', 'active' );
		}
		if ( $affiliate_status == 'pending' ) {
			$data['status'] = 'pending';
			$formats[] = '%s';
		}
		if ( $wpdb->insert( $affiliates_table, $data, $formats ) ) {
			$affiliate_id = $wpdb->get_var( "SELECT LAST_INSERT_ID()" );
			// create association
			if ( $wpdb->insert(
				_affiliates_get_tablename( 'affiliates_users' ),
				array(
					'affiliate_id' => $affiliate_id,
					'user_id' => $user_id
				),
				array( '%d', '%d' )
			) ) {
				$result = $affiliate_id;
				self::new_affiliate_notification( $user_id );
			}

			// hook
			if ( !empty( $affiliate_id ) ) {
				do_action( 'affiliates_added_affiliate', intval( $affiliate_id ) );
			}
		}
		return $result;
	}

	/**
	 * Hooked on delete_user to mark affiliate as deleted.
	 * Note that the affiliate-user association is maintained.
	 * @param int $user_id
	 */
	public static function deleted_user( $user_id ) {

		global $wpdb;

		$affiliates_table = _affiliates_get_tablename( 'affiliates' );
		$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );

		if ( $affiliate_user = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $affiliates_users_table WHERE user_id = %d",
				intval( $user_id ) ) ) ) {
			$affiliate_id = $affiliate_user->affiliate_id;

			$valid_affiliate = false;
			// do not mark the pseudo-affiliate as deleted: type != ...
			$check = $wpdb->prepare(
				"SELECT affiliate_id FROM $affiliates_table WHERE affiliate_id = %d AND (type IS NULL OR type != '" . AFFILIATES_DIRECT_TYPE . "')",
				intval( $affiliate_id ) );
			if ( $wpdb->query( $check ) ) {
				$valid_affiliate = true;
			}

			if ( $valid_affiliate ) {
				// mark the affiliate as deleted - will go through and also
				// clean up the association even if the affiliate was already
				// marked as deleted
				$wpdb->query(
					$query = $wpdb->prepare(
						"UPDATE $affiliates_table SET status = 'deleted' WHERE affiliate_id = %d",
						intval( $affiliate_id )
					)
				);
				do_action( 'affiliates_deleted_affiliate', intval( $affiliate_id ) );
				// the user is removed from the users table, it wouldn't make sense to maintain
				// a dangling reference to a non-existent user so release the association as well 
				$wpdb->query(
					$query = $wpdb->prepare(
						"DELETE FROM $affiliates_users_table WHERE affiliate_id = %d AND user_id = %d",
						intval( $affiliate_id ), intval( $user_id )
					)
				);
			}
		}

	}

	/**
	 * Notify the blog admin of a new affiliate.
	 *
	 * @param int $user_id User ID
	 */
	public static function new_affiliate_notification( $user_id ) {
		$user = new WP_User( $user_id );

		$user_login = stripslashes( $user->user_login );
		$user_email = stripslashes( $user->user_email );

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$message  = sprintf( __( 'New affiliate registration on your site %s:', 'affiliates' ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'affiliates' ), $user_login ) . "\r\n\r\n";
		$message .= sprintf( __( 'E-mail: %s', 'affiliates' ), $user_email ) . "\r\n";

		if ( get_option( 'aff_notify_admin', true ) ) {
			$params = array(
				'user_id'        => $user_id,
				'user'           => $user,
				'username'       => $user->user_login,
				'site_login_url' => wp_login_url(),
				'blogname'       => $blogname
			);
			@wp_mail(
				apply_filters( 'affiliates_admin_email', get_option( 'admin_email' ) ),
				apply_filters( 'affiliates_new_affiliate_registration_subject', sprintf( __( '[%s] New Affiliate Registration', 'affiliates' ), $blogname ), $params ),
				apply_filters( 'affiliates_new_affiliate_registration_message', $message, $params ),
				apply_filters( 'affiliates_new_affiliate_registration_headers', '', $params )
			);

		}

	}

	/**
	 * Notify of new user creation for an affiliate.
	 * 
	 * @param int $user_id User ID.
	 * @param string $plaintext_pass Optional. The user's plaintext password. Default empty.
	 */
	public static function new_user_notification( $user_id, $plaintext_pass = '' ) {
		$user = get_userdata( $user_id );
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		if ( !empty( $plaintext_pass ) ) {
			if ( get_option( 'aff_notify_affiliate_user', 'yes' ) != 'no' ) {
				$message  = sprintf( __( 'Username: %s', 'affiliates' ), $user->user_login) . "\r\n";
				$message .= sprintf( __( 'Password: %s', 'affiliates' ), $plaintext_pass ) . "\r\n";
				$message .= wp_login_url() . "\r\n";
				$params = array(
					'user_id'  => $user_id,
					'user'     => $user,
					'username' => $user->user_login,
					'password' => $plaintext_pass,
					'site_login_url' => wp_login_url(),
					'blogname'       => $blogname
				);
				@wp_mail(
					$user->user_email,
					apply_filters( 'affiliates_new_affiliate_user_registration_subject', sprintf( __( '[%s] Your username and password', 'affiliates' ), $blogname ), $params ),
					apply_filters( 'affiliates_new_affiliate_user_registration_message', $message, $params ),
					apply_filters( 'affiliates_new_affiliate_user_registration_headers', '', $params )
				);
			}
		}
	}

}
Affiliates_Registration::init();
