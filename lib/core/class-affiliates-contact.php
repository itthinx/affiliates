<?php
/**
 * class-affiliates-contact.php
 *
 * Copyright (c) 2010, 2011 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This contact form is an example of how referrals are stored.
 *
 * @link http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class Affiliates_Contact extends WP_Widget {

	/**
	 * This class, as it currently is, will only work correctly as a singleton.
	 * Note though that it is not implemented as such (you may freely create so many ...)
	 * but widget() will only work with one instance of the widget.
	 * @var boolean always true
	 */
	private $is_singleton = true;

	/**
	 * @var string captcha field id
	 */
	private static $captcha_field_id = 'lmfao';

	/**
	 * Creates a contact widget.
	 */
	function __construct() {
		parent::__construct( false, $name = 'Affiliates Contact' );
	}

	/**
	 * Widget output
	 *
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

		$widget_id = $args['widget_id'];

		// output

		echo $before_widget;
		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		if ( $this->is_singleton ) {
			$ext = '';
		} else {
			$ext = '-' . $widget_id;
		}

		if ( $this->is_singleton ) {
			Affiliates_Contact::render_form( '', isset( $instance['amount'] ) ? $instance['amount'] : null, isset( $instance['currency_id'] ) ? $instance['currency_id'] : null );
		} else {
			Affiliates_Contact::render_form( $widget_id, isset( $instance['amount'] ) ? $instance['amount'] : null, isset( $instance['currency_id'] ) ? $instance['currency_id'] : null );
		}
		echo $after_widget;
	}

	/**
	 * Renders the contact form.
	 * Remember NOT to use any form input elements named 'name', 'year', ...
	 * @static
	 */
	public static function render_form( $widget_id = '', $amount = null, $currency_id = null ) {

		wp_enqueue_style( 'affiliates' );

		$method = 'post';
		$action = "";

		if ( !empty( $widget_id ) ) {
			$ext = '-' . $widget_id;
		} else {
			$ext = '';
		}

		$submit_name   = 'affiliates-contact-submit';
		$nonce         = 'affiliates-contact-nonce';
		$nonce_action  = 'affiliates-contact';
		$send          = false;

		$sender_class  = '';
		$email_class   = '';
		$message_class = '';
		$captcha       = '';

		$error         = false;

		if ( !empty( $_POST[$submit_name] ) ) {

			if (
				!isset( $_POST[$nonce] ) ||
				!wp_verify_nonce( $_POST[$nonce], $nonce_action )
			) {
				$error = true; // fail but don't give clues
			}

			$captcha = $_POST[Affiliates_Contact::$captcha_field_id];
			if ( !Affiliates_Contact::captcha_validates( $captcha ) ) {
				$error = true; // dumbot
			}

			$sender  = Affiliates_Contact::filter( $_POST['sender'] );
			$email   = Affiliates_Contact::filter( $_POST['email'] );
			$message = Affiliates_Contact::filter( $_POST['message'] );


			if ( empty( $sender ) ) {
				$sender_class .= ' class="missing" ';
				$error = true;
			}
			if ( empty( $email )  || !is_email( $email ) ) {
				$email_class .= ' class="missing" ';
				$error = true;
			}
			if ( empty( $message ) ) {
				$message_class .= ' class="missing" ';
				$error = true;
			}

			if ( !$error ) {
				$send = true;
				$description = __( 'Affiliates contact form submission', 'affiliates' );
				$data = array(
					'name'    => array( 'title' => 'Name', 'domain' => 'affiliates', 'value' => $sender ),
					'email'   => array( 'title' => 'Email', 'domain' => 'affiliates', 'value' => $email ),
					'message' => array( 'title' => 'Message', 'domain' => 'affiliates', 'value' => $message )
				);
				// request a referral
				$affiliate = null;
				if ( function_exists('affiliates_suggest_referral') ) {
					$post_id = get_the_ID();
					$affiliate_id = affiliates_suggest_referral( $post_id, $description, $data, $amount, $currency_id, null, null, 'ACF' . md5( time() ) );
					if ( $affiliate_id ) {
						$affiliate = affiliates_get_affiliate( $affiliate_id );
						// Now you could send an email to the affiliate ...
					}
				}
			}

		} else {
			$sender  = '';
			$email   = '';
			$message = '';
		}

		if ( !$send ) {
			echo '<div class="affiliates-contact" id="affiliates-contact' . $ext . '">';
			echo '<img id="affiliates-contact-throbber' . $ext . '" src="' . AFFILIATES_PLUGIN_URL . 'images/affiliates-throbber.gif" style="display:none" />';
			echo '<form id="affiliates-contact-form' . $ext . '" action="' . $action . '" method="' . $method . '">';
			echo '<div>';
			echo '<label ' . $sender_class . ' id="affiliates-contact-form' . $ext . '-sender-label" for="sender">' . __( 'Name', 'affiliates' ) . '</label>';
			echo '<input id="affiliates-contact-form' . $ext . '-sender" name="sender" type="text" value="' . esc_attr( $sender ) . '"/>';
			echo '<label ' . $email_class . ' id="affiliates-contact-form' . $ext . '-email-label" for="email">' . __( 'Email', 'affiliates' ) . '</label>';
			echo '<input id="affiliates-contact-form' . $ext . '-email" name="email" type="text" value="' . esc_attr( $email ) . '"/>';
			echo '<label ' . $message_class . 'id="affiliates-contact-form' . $ext . '-message-label" for="message">' . __( 'Message', 'affiliates' ) . '</label>';
			echo '<textarea id="affiliates-contact-form' . $ext . '-message" name="message">' . $message . '</textarea>';
			echo Affiliates_Contact::captcha_get( $captcha );
			echo wp_nonce_field( $nonce_action, $nonce, true, false );
			echo '<input type="submit" name="' . $submit_name . '" value="'. __( 'Send', 'affiliates' ) . '" />';
			echo '</div>';
			echo '</form>';
			echo '</div>';
		} else {
			echo '<p>' . __( 'Thanks!', 'affiliates' ) . '</p>';
		}
	}

	/**
	 * Filters mail header injection, html, ...
	 * @param string $unfiltered_value
	 */
	public static function filter( $unfiltered_value ) {
		$mail_filtered_value = preg_replace('/(%0A|%0D|content-type:|to:|cc:|bcc:)/i', '', $unfiltered_value );
		return stripslashes( wp_filter_nohtml_kses( Affiliates_Contact::filter_xss( trim( strip_tags( $mail_filtered_value ) ) ) ) );
	}

	/**
	 * Filter xss
	 *
	 * @param string $string input
	 *
	 * @return string filtered string
	 */
	public static function filter_xss( $string ) {
		// Remove NUL characters (ignored by some browsers)
		$string = str_replace(chr(0), '', $string);
		// Remove Netscape 4 JS entities
		$string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);

		// Defuse all HTML entities
		$string = str_replace('&', '&amp;', $string);
		// Change back only well-formed entities in our whitelist
		// Decimal numeric entities
		$string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
		// Hexadecimal numeric entities
		$string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
		// Named entities
		$string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);
		return preg_replace('%
		(
		<(?=[^a-zA-Z!/])  # a lone <
		|                 # or
		<[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
		|                 # or
		>                 # just a >
		)%x', '', $string);
	}

	/**
	 * Returns captcha field markup.
	 *
	 * @return string captcha field markup
	 */
	public static function captcha_get( $value ) {
		$style = 'display:none;';
		$field = '<input name="' . Affiliates_Contact::$captcha_field_id . '" id="' . Affiliates_Contact::$captcha_field_id . '" class="' . Affiliates_Contact::$captcha_field_id . ' field" style="' . $style . '" value="' . esc_attr( $value ) . '" type="text"/>';
		return $field;
	}

	/**
	 * Validates a captcha field.
	 *
	 * @param string $field_value field content
	 *
	 * @return true if the field validates
	 */
	public static function captcha_validates( $field_value = null ) {
		$result = false;
		if ( empty( $field_value ) ) {
			$result = true;
		}
		return $result;
	}

	/**
	 * Save widget options
	 *
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {
		$settings = $old_instance;
		$settings['title'] = strip_tags( $new_instance['title'] );
		if ( !empty( $new_instance['amount'] ) ) {
			$settings['amount'] =  Affiliates_Utility::verify_referral_amount( $new_instance['amount'] );
		} else {
			unset( $settings['amount'] );
		}
		if ( !empty( $new_instance['currency_id'] ) ) {
			$settings['currency_id'] = Affiliates_Utility::verify_currency_id( $new_instance['currency_id'] );
		} else {
			unset( $settings['currency_id'] );
		}
		return $settings;
	}

	/**
	 * Output admin widget options form
	 *
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$amount = isset( $instance['amount'] ) ? esc_attr( $instance['amount'] ) : '';
		$currency_id = isset( $instance['currency_id'] ) ? esc_attr( $instance['currency_id'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'affiliates' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'amount' ); ?>"><?php _e( 'Amount (use . for decimals):', 'affiliates' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'amount' ); ?>" name="<?php echo $this->get_field_name( 'amount' ); ?>" type="text" value="<?php echo $amount; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'currency_id' ); ?>"><?php _e( 'Currency - 3 letter code, e.g. USD, EUR:', 'affiliates' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'currency_id' ); ?>" name="<?php echo $this->get_field_name( 'currency_id' ); ?>" type="text" value="<?php echo $currency_id; ?>" />
		</p>
		<p>
			<?php _e( 'This contact form will request a referral and store the data that has been submitted.', 'affiliates' ); ?>
		</p>
		<p>
			<?php _e( 'It has two purposes:', 'affiliates' ); ?>
		</p>
		<ul>
			<li><?php _e( 'To be used as an entry-level referral tool (e.g. in lead generation), if you want to track who has contacted you and has visited your site through an affiliate.', 'affiliates' ); ?></li>
			<li><?php _e( 'To serve as an example on how to use the API provided by the Affiliates plugin', 'affiliates' ); ?></li>
		</ul>
		<?php
	}
}// class Affiliates_Contact
