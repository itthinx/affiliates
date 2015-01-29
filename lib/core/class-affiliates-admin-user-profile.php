<?php
/**
 * class-affiliates-admin-user-profile.php
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
 * Shows affiliates user meta info on user profile pages.
 */
class Affiliates_Admin_User_Profile {

	/**
	 * Adds user profile actions.
	 */
	public static function init() {
		add_action( 'show_user_profile', array( __CLASS__, 'show_user_profile' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'edit_user_profile' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'personal_options_update' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'edit_user_profile_update' ) );
		//add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Use to enqueue styles and scripts if needed on user-edit and profile screens.
	 */
	public static function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( isset( $screen->id ) ) {
			switch( $screen->id ) {
				case 'user-edit' :
				case 'profile' :
					// ...
					break;
			}
		}
	}

	/**
	 * Own profile.
	 * 
	 * @param WP_User $user
	 */
	public static function show_user_profile( $user ) {
		self::edit_user_profile( $user );
	}

	/**
	 * Editing a user profile.
	 * 
	 * @param WP_User $user
	 */
	public static function edit_user_profile( $user ) {

		if ( !affiliates_user_is_affiliate( $user->ID ) ) {
			return;
		}

		$output = '';

		$output .= '<h3>';
		$output .= __( 'Affiliate Information', AFFILIATES_PLUGIN_DOMAIN );
		$output .= '</h3>';

		require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings.php';
		require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-registration.php';
		$registration_fields = Affiliates_Settings_Registration::get_fields();

		// remove fields not stored as user meta
		foreach( Affiliates_Registration::get_skip_meta_fields() as $key ) {
			unset( $registration_fields[$key] );
		}
		unset( $registration_fields['first_name'] );
		unset( $registration_fields['last_name'] );
		$n = 0;
		if ( !empty( $registration_fields ) ) {
			$output .= '<table class="form-table">';
			$output .= '</body>';
			foreach( $registration_fields as $name => $field ) {
				if ( $field['enabled'] ) {
					$n++;
					$output .= '<tr>';
					$output .= '<th>';
					$output .= sprintf( '<label for="%s">', esc_attr( $name ) );
					$output .= esc_html( $field['label'] ); // @todo i18n
					$output .= '</label>';
					$output .= '</th>';
					$output .= '<td>';
					$type = isset( $field['type'] ) ? $field['type'] : 'text';
					$value = get_user_meta( $user->ID, $name , true );
					$output .= sprintf(
							'<input type="%s" class="%s" name="%s" value="%s" %s />',
							esc_attr( $type ),
							'regular-text ' . esc_attr( $name ) . ( $field['required'] ? ' required ' : '' ),
							esc_attr( $name ),
							esc_attr( $value ),
							$field['required'] ? ' required="required" ' : ''
					);
					$output .= '</td>';
					$output .= '</tr>';
				}
			}
			$output .= '</tbody>';
			$output .= '</table>';
		}
		if ( $n == 0 ) {
			$output .= '<p>';
			$output .= __( 'No specific affiliate information is available.', AFFILIATES_PLUGIN_DOMAIN );
			$output .= '</p>';
		}
		echo $output;
	}

	/**
	 * Updates user meta when a user's own profile is saved.
	 * 
	 * @param int $user_id
	 */
	public static function personal_options_update( $user_id ) {
		self::edit_user_profile_update( $user_id );
	}

	/**
	 * Updates the user meta.
	 * 
	 * @param int $user_id
	 */
	public static function edit_user_profile_update( $user_id ) {

		if ( !affiliates_user_is_affiliate( $user_id ) ) {
			return;
		}

		require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings.php';
		require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-registration.php';
		$registration_fields = Affiliates_Settings_Registration::get_fields();

		// remove fields not stored as user meta
		foreach( Affiliates_Registration::get_skip_meta_fields() as $key ) {
			unset( $registration_fields[$key] );
		}
		unset( $registration_fields['first_name'] );
		unset( $registration_fields['last_name'] );

		// update user meta
		if ( !empty( $registration_fields ) ) {
			foreach( $registration_fields as $name => $field ) {
				$meta_value = isset( $_POST[$name] ) ? $_POST[$name] : '';
				$meta_value = Affiliates_Utility::filter( $meta_value );
				update_user_meta( $user_id, $name, maybe_unserialize( $meta_value ) );
			}
		}
	}

}
Affiliates_Admin_User_Profile::init();
