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
			'first_name' => array( 'obligatory' => false, 'enabled' => true, 'label' => __( 'First Name', AFFILIATES_PLUGIN_DOMAIN ), 'required' => true, 'is_default' => true, 'type' => 'text' ),
			'last_name'  => array( 'obligatory' => false, 'enabled' => true, 'label' => __( 'Last Name', AFFILIATES_PLUGIN_DOMAIN ), 'required' => true, 'is_default' => true, 'type' => 'text' ),
			'user_login' => array( 'obligatory' => false, 'enabled' => true, 'label' => __( 'Username', AFFILIATES_PLUGIN_DOMAIN ), 'required' => true, 'is_default' => true, 'type' => 'text' ),
			'user_email' => array( 'obligatory' => true, 'enabled' => true, 'label' => __( 'Email', AFFILIATES_PLUGIN_DOMAIN ), 'required' => true, 'is_default' => true, 'type' => 'text' ),
			'user_url'	 => array( 'obligatory' => false, 'enabled' => true, 'label' => __( 'Website', AFFILIATES_PLUGIN_DOMAIN ), 'required' => false, 'is_default' => true, 'type' => 'text' ),
			'password'	 => array( 'obligatory' => false, 'enabled' => false, 'label' => __( 'Password', AFFILIATES_PLUGIN_DOMAIN ), 'required' => false, 'is_default' => true, 'type' => 'password' )
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
			if ( wp_verify_nonce( $_POST[AFFILIATES_ADMIN_SETTINGS_NONCE], 'admin' ) ) {

				delete_option( 'aff_registration' );
				add_option( 'aff_registration', !empty( $_POST['registration'] ), '', 'no' );

				delete_option( 'aff_notify_admin' );
				add_option( 'aff_notify_admin', !empty( $_POST['notify_admin'] ), '', 'no' );

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

		$registration = get_option( 'aff_registration', get_option( 'users_can_register', false ) );
		$notify_admin = get_option( 'aff_notify_admin', get_option( 'aff_notify_admin', true ) );

		echo
			'<form action="" name="options" method="post">' .
			'<div>' .
			'<h3>' . __( 'Affiliate Registration', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			'<label>' .
			'<input name="registration" type="checkbox" ' . ( $registration ? 'checked="checked"' : '' ) . '/>' .
			' ' .
			__( 'Allow affiliate registration', AFFILIATES_PLUGIN_DOMAIN ) .
			'</label>' .
			'</p>';

		echo
			'<p>' .
			'<label>' .
			'<input name="notify_admin" type="checkbox" ' . ( $notify_admin ? 'checked="checked"' : '' ) . '/>' .
			' ' .
			__( 'Notify the site admin when a new affiliate is registered', AFFILIATES_PLUGIN_DOMAIN ) .
			'</label>' .
			'</p>';

		// registration fields
		echo '<h3>' . __( 'Affiliate Registration Form', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>';
		echo '<p class="description">';
		echo __( 'The following fields are provided on the affiliate registration form rendered by the <code>[affiliates_registration]</code> shortcode.', AFFILIATES_PLUGIN_DOMAIN );
		echo '</p>';
		$registration_fields = get_option( 'aff_registration_fields', self::$default_fields );
		echo '<div id="registration-fields">';
		echo '<table>';
		echo '<thead>';
		echo '</th>';
		echo '<th>';
		echo __( 'Enabled', AFFILIATES_PLUGIN_DOMAIN );
		echo '</th>';
		echo '<th>';
		echo __( 'Field Name', AFFILIATES_PLUGIN_DOMAIN );
		echo '</th>';
		echo '<th>';
		echo __( 'Field Label', AFFILIATES_PLUGIN_DOMAIN );
		echo '</th>';
		echo '<th>';
		echo __( 'Required', AFFILIATES_PLUGIN_DOMAIN );
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
			echo sprintf( '<input type="text" name="field-label[%d]" value="%s" />', $i, esc_attr( $field['label'] ) );
			echo '</td>';
			echo '<td>';
			echo sprintf( '<input type="checkbox" name="field-required[%d]" %s />', $i, $field['required'] ? ' checked="checked" ' : '' );
			echo '</td>';
			echo '<td>';
			echo sprintf( '<input type="hidden" name="field-type[%d]" value="%s" />', $i, $field['type'] );
			echo sprintf( '<button class="field-up" type="button" value="%d">%s</button>', $i, esc_html( __( 'Up', AFFILIATES_PLUGIN_DOMAIN ) ) );
			echo sprintf( '<button class="field-down" type="button" value="%d">%s</button>', $i, esc_html( __( 'Down', AFFILIATES_PLUGIN_DOMAIN ) ) );
			if ( !$field['is_default'] ) {
				echo sprintf( '<button class="field-remove" type="button" value="%d">%s</button>', $i, esc_html( __( 'Remove', AFFILIATES_PLUGIN_DOMAIN ) ) );
			}
			echo '</td>';
			echo '</tr>';
			$i++;
		}
		echo '</tbody>';
		echo '</table>';

		echo '<p>';
		echo sprintf( '<button class="field-add" type="button" value="%d">%s</button>', $i, esc_html( __( 'Add a field', AFFILIATES_PLUGIN_DOMAIN ) ) );
		echo '</p>';

		echo '</div>'; // #registration-fields

		echo
			'<p>' .
			wp_nonce_field( 'admin', AFFILIATES_ADMIN_SETTINGS_NONCE, true, false ) .
			'<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', AFFILIATES_PLUGIN_DOMAIN ) . '"/>' .
			'</p>' .
			'</div>' .
			'</form>';

			affiliates_footer();
	}
}
Affiliates_Settings_Registration::init();
