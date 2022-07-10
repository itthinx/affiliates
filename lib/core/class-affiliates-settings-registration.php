<?php
/**
 * class-affiliates-settings-registration.php
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
 * Registration settings section.
 */
class Affiliates_Settings_Registration extends Affiliates_Settings {

	/**
	 * Default registration form fields.
	 * 
	 * @var array
	 */
	private static $default_fields = null;

	/**
	 * Registration fields.
	 * 
	 * @return array
	 */
	public static function get_fields() {
		return get_option( 'aff_registration_fields', self::$default_fields );
	}

	/**
	 * Settings initialization.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		self::$default_fields = array(
			'first_name' => array( 'obligatory' => false, 'enabled' => true, 'label' => __( 'First Name', 'affiliates' ), 'required' => true, 'is_default' => true, 'type' => 'text' ),
			'last_name'  => array( 'obligatory' => false, 'enabled' => true, 'label' => __( 'Last Name', 'affiliates' ), 'required' => true, 'is_default' => true, 'type' => 'text' ),
			'user_login' => array( 'obligatory' => false, 'enabled' => true, 'label' => __( 'Username', 'affiliates' ), 'required' => true, 'is_default' => true, 'type' => 'text' ),
			'user_email' => array( 'obligatory' => true, 'enabled' => true, 'label' => __( 'Email', 'affiliates' ), 'required' => true, 'is_default' => true, 'type' => 'text' ),
			'user_url'	 => array( 'obligatory' => false, 'enabled' => true, 'label' => __( 'Website', 'affiliates' ), 'required' => false, 'is_default' => true, 'type' => 'text' ),
			'password'	 => array( 'obligatory' => false, 'enabled' => false, 'label' => __( 'Password', 'affiliates' ), 'required' => false, 'is_default' => true, 'type' => 'password' )
		);
	}

	/**
	 * Registers an admin_notices action.
	 */
	public static function admin_init() {
		
	}

	/**
	 * Registration settings.
	 */
	public static function section() {

		if ( isset( $_POST['submit'] ) ) {
			if (
				isset( $_POST[AFFILIATES_ADMIN_SETTINGS_NONCE] ) &&
				wp_verify_nonce( $_POST[AFFILIATES_ADMIN_SETTINGS_NONCE], 'admin' )
			) {

				delete_option( 'aff_registration' );
				add_option( 'aff_registration', !empty( $_POST['registration'] ), '', 'no' );

				delete_option( 'aff_status' );
				$status = !empty( $_POST['affiliate_status'] ) ? $_POST['affiliate_status'] : 'active';
				switch( $status ) {
					case 'active' :
					case 'pending' :
						break;
					default :
						$status = 'active';
				}
				add_option( 'aff_status', $status, '', 'no' );

				delete_option( 'aff_registration_terms_post_id' );
				$terms_post_id = !empty( $_POST['terms_post_id'] ) ? intval( $_POST['terms_post_id'] ) : '';
				if ( $terms_post_id && ( get_post( $terms_post_id ) !== null ) ) {
					add_option( 'aff_registration_terms_post_id', $terms_post_id );
				}

				if ( !get_option( 'aff_registration_fields' ) ) {
					add_option( 'aff_registration_fields', self::$default_fields, '', 'no' );
				}
				$field_enabled  = isset( $_POST['field-enabled'] ) ? $_POST['field-enabled'] : array();
				$field_name     = isset( $_POST['field-name'] ) ? $_POST['field-name'] : array();
				$field_label    = isset( $_POST['field-label'] ) ? $_POST['field-label'] : array();
				$field_required = isset( $_POST['field-required'] ) ? $_POST['field-required'] : array();
				$field_type     = isset( $_POST['field-type'] ) ? $_POST['field-type'] : array();
				$max_index = max( array(
					max( array_keys( $field_enabled ) ),
					max( array_keys( $field_name ) ),
					max( array_keys( $field_label ) ),
					max( array_keys( $field_required ) ),
					max( array_keys( $field_type ) )
				) );
				$fields = array();
				for( $i = 0; $i <= $max_index; $i++ ) {
					if ( !empty( $field_name[$i] ) ) {
						$name = strip_tags( $field_name[$i] );
						$name = strtolower( $name );
						$name = preg_replace( '/[^a-z0-9_]/', '_', $name );
						$name = preg_replace( '/[_]+/', '_', $name );
						if ( !empty( $name ) && !isset( $fields[$name] ) ) {
							$fields[$name] = array(
								'obligatory' => false || isset( self::$default_fields[$name] ) && self::$default_fields[$name]['obligatory'],
								'enabled'    => !empty( $field_enabled[$i] ) || isset( self::$default_fields[$name] ) && self::$default_fields[$name]['obligatory'],
								'label'      => !empty( $field_label[$i] ) ? strip_tags( $field_label[$i] ) : '',
								'required'   => !empty( $field_required[$i]),
								'is_default' => key_exists( $field_name[$i], self::$default_fields ),
								'type'       => !empty( $field_type[$i] ) ? $field_type[$i] : 'text'
							);
						}
					}
				}

				update_option( 'aff_registration_fields', $fields );

				self::settings_saved_notice();

			}
		}

		$registration     = get_option( 'aff_registration', get_option( 'users_can_register', false ) );
		$affiliate_status = get_option( 'aff_status', 'active' );
		$terms_post_id    = get_option( 'aff_registration_terms_post_id', '' );

		echo
			'<form action="" name="options" method="post">' .
			'<div>' .
			'<h3>' . esc_html__( 'Affiliate Registration', 'affiliates' ) . '</h3>' .
			'<p>' .
			'<label>' .
			'<input name="registration" type="checkbox" ' . ( $registration ? 'checked="checked"' : '' ) . '/>' .
			' ' .
			esc_html__( 'Allow affiliate registration', 'affiliates' ) .
			'</label>' .
			'</p>';

		echo
			'<p>' .
			'<label>' .
			esc_html__( 'Status', 'affiliates' ) .
			' ' .
			'<select name="affiliate_status">' .
			sprintf( '<option value="active" %s>', $affiliate_status == 'active' ? ' selected="selected" ' : '' ) .
			esc_html__( 'Active', 'affiliates' ) .
			'</option>' .
			sprintf( '<option value="pending" %s>', $affiliate_status == 'pending' ? ' selected="selected" ' : '' ) .
			esc_html__( 'Pending', 'affiliates' ) .
			'</option>' .
			'</select>' .
			'</label>';
		echo '<br/>';
		echo '<span class="description">';
		esc_html_e( 'This determines if new affiliate applications require manual approval or whether they are accepted automatically.', 'affiliates' );
		echo ' ';
		echo wp_kses( __( '<em>Pending</em> will require manual approval of new affiliates. <em>Active</em> will accept new affiliates automatically.', 'affiliates' ), array( 'em' => array() ) );
		echo '</span>';
		echo '</p>';

		echo '<p>';
		echo '<label for="terms_post_id">';
		esc_html_e( 'Terms', 'affiliates' );
		echo ' ';
		wp_dropdown_pages(
			array(
				'name'              => 'terms_post_id',
				'echo'              => true,
				'show_option_none'  => __( '&mdash; Select &mdash;' ),
				'option_none_value' => '',
				'selected'          => $terms_post_id
			)
		);
		echo '</label>';
		echo '<br/>';
		echo '<span class="description">';
		esc_html_e( 'Terms and conditions', 'affiliates' );
		echo ' &mdash; ';
		esc_html_e( 'If chosen, a disclaimer and link to the page will be displayed with the registration form.', 'affiliates' );
		echo '</span>';
		echo '</p>';

		// registration fields
		echo '<h3>' . __( 'Affiliate Registration Form', 'affiliates' ) . '</h3>';
		echo '<p class="description">';
		esc_html_e( 'The following fields are provided on the affiliate registration form.', 'affiliates' );
		echo '</p>';
		$registration_fields = get_option( 'aff_registration_fields', self::$default_fields );
		echo '<div id="registration-fields">';
		echo '<table>';
		echo '<thead>';
		echo '</th>';
		echo '<th>';
		esc_html_e( 'Enabled', 'affiliates' );
		echo '</th>';
		echo '<th>';
		esc_html_e( 'Field Name', 'affiliates' );
		echo '</th>';
		echo '<th>';
		esc_html_e( 'Field Label', 'affiliates' );
		echo '</th>';
		echo '<th>';
		esc_html_e( 'Required', 'affiliates' );
		echo '</th>';
		echo '<tr>';
		echo '<th>';
		echo '</tr>';
		echo '</thead>';
		$i = 0;
		echo '<tbody>';
		foreach( $registration_fields as $name => $field ) {
			echo '<tr>';
			echo '<td>';
			echo sprintf( '<input type="checkbox" name="field-enabled[%d]" %s %s />', $i, $field['enabled'] ? ' checked="checked" ' : '', $field['obligatory'] ? ' readonly="readonly" disabled="disabled" ' : '' );
			echo '</td>';
			echo '<td>';
			echo sprintf( '<input type="text" name="field-name[%d]" value="%s" %s />', $i, esc_attr( $name ), $field['is_default'] ? ' readonly="readonly" ' : '' );
			echo '</td>';
			echo '<td>';
			echo sprintf( '<input type="text" name="field-label[%d]" value="%s" />', $i, esc_attr( stripslashes( $field['label'] ) ) );
			echo '</td>';
			echo '<td>';
			echo sprintf( '<input type="checkbox" name="field-required[%d]" %s />', $i, $field['required'] ? ' checked="checked" ' : '' );
			echo '</td>';
			echo '<td>';
			echo sprintf( '<input type="hidden" name="field-type[%d]" value="%s" />', $i, $field['type'] );
			echo sprintf( '<button class="field-up button" type="button" value="%d">%s</button>', $i, esc_html( __( 'Up', 'affiliates' ) ) );
			echo sprintf( '<button class="field-down button" type="button" value="%d">%s</button>', $i, esc_html( __( 'Down', 'affiliates' ) ) );
			if ( !$field['is_default'] ) {
				echo sprintf( '<button class="field-remove button" type="button" value="%d">%s</button>', $i, esc_html( __( 'Remove', 'affiliates' ) ) );
			}
			echo '</td>';
			echo '</tr>';
			$i++;
		}
		echo '</tbody>';
		echo '</table>';

		echo '<p>';
		echo sprintf( '<button class="field-add button" type="button" value="%d">%s</button>', $i, esc_html( __( 'Add a field', 'affiliates' ) ) );
		echo '</p>';

		echo '</div>'; // #registration-fields

		echo
			'<p>' .
			wp_nonce_field( 'admin', AFFILIATES_ADMIN_SETTINGS_NONCE, true, false ) .
			'<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', 'affiliates' ) . '"/>' .
			'</p>' .
			'</div>' .
			'</form>';

			affiliates_footer();
	}
}
Affiliates_Settings_Registration::init();
