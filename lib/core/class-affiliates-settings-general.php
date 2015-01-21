<?php
/**
 * class-affiliates-settings-general.php
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
 * General settings section.
 */
class Affiliates_Settings_General extends Affiliates_Settings {

	/**
	 * Renders the general settings section.
	 */
	public static function section() {

		global $wp, $wpdb, $affiliates_options, $wp_roles;

		$robots_table = _affiliates_get_tablename( 'robots' );

		if ( isset( $_POST['submit'] ) ) {

			if ( wp_verify_nonce( $_POST[AFFILIATES_ADMIN_SETTINGS_NONCE], 'admin' ) ) {

				// robots
				$robots = wp_filter_nohtml_kses( trim ( $_POST['robots'] ) );
				$wpdb->query("DELETE FROM $robots_table;");
				if ( !empty( $robots ) ) {
					$robots = str_replace( ",", "\n", $robots );
					$robots = str_replace( "\r", "", $robots );
					$robots = explode( "\n", $robots );
					foreach ( $robots as $robot ) {
						$robot = trim( $robot );
						if (!empty($robot)) {
							$query = $wpdb->prepare( "INSERT INTO $robots_table (name) VALUES (%s);", $robot );
							$wpdb->query( $query );
						}
					}
				}

				$pname = !empty( $_POST['pname'] ) ? trim( $_POST['pname'] ) : get_option( 'aff_pname', AFFILIATES_PNAME );
				$forbidden_names = array();
				if ( !empty( $wp->public_query_vars ) ) {
					$forbidden_names += $wp->public_query_vars;
				}
				if ( !empty( $wp->private_query_vars ) ) {
					$forbidden_names += $wp->private_query_vars;
				}
				if ( !empty( $wp->extra_query_vars ) ) {
					$forbidden_names += $wp->extra_query_vars;
				}
				if ( !preg_match( '/[a-z_]+/', $pname, $matches ) || !isset( $matches[0] ) || $pname !== $matches[0] ) {
					$pname = get_option( 'aff_pname', AFFILIATES_PNAME );
					echo '<div class="error">' . __( 'The Affiliate URL parameter name <strong>has not been changed</strong>, the suggested name <em>is not valid</em>. Only lower case letters and the underscore _ are allowed.', AFFILIATES_PLUGIN_DOMAIN ) . '</div>';
				} else if ( in_array( $pname, $forbidden_names ) ) {
					$pname = get_option( 'aff_pname', AFFILIATES_PNAME );
					echo '<div class="error">' . __( 'The Affiliate URL parameter name <strong>has not been changed</strong>, the suggested name <em>is forbidden</em>.', AFFILIATES_PLUGIN_DOMAIN ) . '</div>';
				}
				$old_pname = get_option( 'aff_pname', AFFILIATES_PNAME );
				if ( $pname !== $old_pname ) {
					update_option( 'aff_pname', $pname );
					affiliates_update_rewrite_rules();
					echo '<div class="info">' .
						'<p>' .
						sprintf(
							__( 'The Affiliate URL parameter name <strong>has been changed</strong> from <em><strong>%s</strong></em> to <em><strong>%s</strong></em>.', AFFILIATES_PLUGIN_DOMAIN ),
							$old_pname,
							$pname
						) .
						'</p>' .
						'<p class="warning">' .
						__( 'If your affiliates are using affiliate links based on the previous Affiliate URL parameter name, they <strong>NEED</strong> to update their affiliate links.', AFFILIATES_PLUGIN_DOMAIN ) .
						'</p>' .
						'<p class="warning">' .
						__( 'Unless the incoming affiliate links reflect the current Affiliate URL parameter name, no affiliate hits, visits or referrals will be recorded.', AFFILIATES_PLUGIN_DOMAIN ) .
						'</p>' .
						'</div>';
				}

				$redirect = !empty( $_POST['redirect'] );
				if ( $redirect ) {
					if ( get_option( 'aff_redirect', null ) === null ) {
						add_option( 'aff_redirect', 'yes', '', 'no' );
					} else {
						update_option( 'aff_redirect', 'yes' );
					}
				} else {
					delete_option( 'aff_redirect' );
				}

				$encoding_id = $_POST['id_encoding'];
				if ( key_exists( $encoding_id, affiliates_get_id_encodings() ) ) {
					// important: must use normal update_option/get_option otherwise we'd have a per-user encoding
					update_option( 'aff_id_encoding', $encoding_id );
				}

				$rolenames = $wp_roles->get_names();
				$caps = array(
					AFFILIATES_ACCESS_AFFILIATES => __( 'Access affiliates', AFFILIATES_PLUGIN_DOMAIN ),
					AFFILIATES_ADMINISTER_AFFILIATES => __( 'Administer affiliates', AFFILIATES_PLUGIN_DOMAIN ),
					AFFILIATES_ADMINISTER_OPTIONS => __( 'Administer options', AFFILIATES_PLUGIN_DOMAIN ),
				);
				foreach ( $rolenames as $rolekey => $rolename ) {
					$role = $wp_roles->get_role( $rolekey );
					foreach ( $caps as $capkey => $capname ) {
						$role_cap_id = $rolekey.'-'.$capkey;
						if ( !empty($_POST[$role_cap_id] ) ) {
							$role->add_cap( $capkey );
						} else {
							$role->remove_cap( $capkey );
						}
					}
				}
				// prevent locking out
				_affiliates_assure_capabilities();

				if ( !affiliates_is_sitewide_plugin() ) {
					delete_option( 'aff_delete_data' );
					add_option( 'aff_delete_data', !empty( $_POST['delete-data'] ), '', 'no' );
				}

			}
		}

		$robots = '';
		$db_robots = $wpdb->get_results( "SELECT name FROM $robots_table", OBJECT );
		foreach ($db_robots as $db_robot ) {
				$robots .= $db_robot->name . "\n";
		}

		$pname    = get_option( 'aff_pname', AFFILIATES_PNAME );
		$redirect = get_option( 'aff_redirect', false );

		$id_encoding = get_option( 'aff_id_encoding', AFFILIATES_NO_ID_ENCODING );
		$id_encoding_select = '';
		$encodings = affiliates_get_id_encodings();
		if ( !empty( $encodings ) ) {
			$id_encoding_select .= '<label class="id-encoding" for="id_encoding">' . __('Affiliate ID Encoding', AFFILIATES_PLUGIN_DOMAIN ) . '</label>';
			$id_encoding_select .= '<select class="id-encoding" name="id_encoding">';
			foreach ( $encodings as $key => $value ) {
				if ( $id_encoding == $key ) {
					$selected = ' selected="selected" ';
				} else {
					$selected = '';
				}
				$id_encoding_select .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . esc_attr( $value ) . '</option>';
			}
			$id_encoding_select .= '</select>';
		}

		$rolenames = $wp_roles->get_names();
		$caps = array(
			AFFILIATES_ACCESS_AFFILIATES => __( 'Access affiliates', AFFILIATES_PLUGIN_DOMAIN ),
			AFFILIATES_ADMINISTER_AFFILIATES => __( 'Administer affiliates', AFFILIATES_PLUGIN_DOMAIN ),
			AFFILIATES_ADMINISTER_OPTIONS => __( 'Administer options', AFFILIATES_PLUGIN_DOMAIN ),
		);
		$caps_table = '<table class="affiliates-permissions">';
		$caps_table .= '<thead>';
		$caps_table .= '<tr>';
		$caps_table .= '<td class="role">';
		$caps_table .= __( 'Role', AFFILIATES_PLUGIN_DOMAIN );
		$caps_table .= '</td>';
		foreach ( $caps as $cap ) {
			$caps_table .= '<td class="cap">';
			$caps_table .= $cap;
			$caps_table .= '</td>';
		}
		$caps_table .= '</tr>';
		$caps_table .= '</thead>';
		$caps_table .= '<tbody>';
		foreach ( $rolenames as $rolekey => $rolename ) {
			$role = $wp_roles->get_role( $rolekey );
			$caps_table .= '<tr>';
			$caps_table .= '<td>';
			$caps_table .= translate_user_role( $rolename );
			$caps_table .= '</td>';
			foreach ( $caps as $capkey => $capname ) {
				if ( $role->has_cap( $capkey ) ) {
					$checked = ' checked="checked" ';
				} else {
					$checked = '';
				}
				$caps_table .= '<td class="checkbox">';
				$role_cap_id = $rolekey.'-'.$capkey;
				$caps_table .= '<input type="checkbox" name="' . $role_cap_id . '" id="' . $role_cap_id . '" ' . $checked . '/>';
				$caps_table .= '</td>';
			}
			$caps_table .= '</tr>';
		}
		$caps_table .= '</tbody>';
		$caps_table .= '</table>';

		$delete_data = get_option( 'aff_delete_data', false );

		echo
			'<form action="" name="options" method="post">' .
			'<div>';

		echo
			'<h3>' . __( 'Affiliate URL parameter name', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			'<input class="pname" name="pname" type="text" value="' . esc_attr( $pname ) . '" />' .
			'</p>' .
			'<p>' .
			sprintf( __( 'The current Affiliate URL parameter name is: <b>%s</b>', AFFILIATES_PLUGIN_DOMAIN ), $pname ) .
			'</p>' .
			'<p>' .
			sprintf( __( 'The default Affiliate URL parameter name is <em>%s</em>.', AFFILIATES_PLUGIN_DOMAIN ), AFFILIATES_PNAME ) .
			'</p>' .
			'<p class="description warning">' .
			__( 'CAUTION: If you change this setting and have distributed affiliate links or permalinks, make sure that these are updated. Unless the incoming affiliate links reflect the current URL parameter name, no affiliate hits, visits or referrals will be recorded.', AFFILIATES_PLUGIN_DOMAIN ) .
			'</p>';

		echo
			'<h3>' . __( 'Redirection', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			'<label>' .
			sprintf( '<input class="redirect" name="redirect" type="checkbox" %s/>', $redirect ? ' checked="checked" ' : '' ) .
			' ' .
			__( 'Redirect', AFFILIATES_PLUGIN_DOMAIN ) .
			'</label>' .
			'</p>' .
			'<p class="description">' .
			 __( 'Redirect to destination without Affiliate URL parameter, after a hit on an affiliate link has been detected.', AFFILIATES_PLUGIN_DOMAIN ) .
			'</p>';

		echo
			'<h3>' . __( 'Affiliate ID encoding', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			$id_encoding_select .
			'</p>' .
			'<p>' .
			sprintf( __( 'The current encoding in effect is: <b>%s</b>', AFFILIATES_PLUGIN_DOMAIN ), $encodings[$id_encoding] ) .
			'</p>' .
			'<p class="description warning">' .
			__( 'CAUTION: If you change this setting and have distributed affiliate links or permalinks, make sure that these are updated. Unless the incoming affiliate links reflect the current encoding, no affiliate hits, visits or referrals will be recorded.', AFFILIATES_PLUGIN_DOMAIN ) .
			'</p>';

		echo
			'<h3>' . __( 'Permissions', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			__( 'Do not assign permissions to open access for affiliates here.', AFFILIATES_PLUGIN_DOMAIN ) .
			' ' .
			__( 'This section is only intended to grant administrative access on affiliate management functions to privileged roles.', AFFILIATES_PLUGIN_DOMAIN ) .
			'</p>' .
			$caps_table .
			'<p class="description">' .
			__( 'A minimum set of permissions will be preserved.', AFFILIATES_PLUGIN_DOMAIN ) .
			'<br/>' .
			__( 'If you lock yourself out, please ask an administrator to help.', AFFILIATES_PLUGIN_DOMAIN ) .
			'</p>';

		echo
			'<h3>' . __( 'Robots', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
			'<p>' .
			//'<label for="robots">' . __( 'Robots', AFFILIATES_PLUGIN_DOMAIN ) . '</label>' .
			'<textarea id="robots" name="robots" rows="10" cols="45">' . wp_filter_nohtml_kses( $robots ) . '</textarea>' .
			'</p>' .
			'<p>' .
			__( 'Hits on affiliate links from these robots will be marked or not recorded. Put one entry on each line.', AFFILIATES_PLUGIN_DOMAIN ) .
			'</p>';

		if ( !affiliates_is_sitewide_plugin() ) {
			echo
				'<h3>' . __( 'Deactivation and data persistence', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>' .
				'<p>' .
				'<label>' .
				'<input name="delete-data" type="checkbox" ' . ( $delete_data ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				__( 'Delete all plugin data on deactivation', AFFILIATES_PLUGIN_DOMAIN ) .
				'</label>' .
				'</p>' .
				'<p class="description warning">' .
				__( 'CAUTION: If this option is active while the plugin is deactivated, ALL affiliate and referral data will be DELETED. If you want to retrieve data about your affiliates and their referrals and are going to deactivate the plugin, make sure to back up your data or do not enable this option. By enabling this option you agree to be solely responsible for any loss of data or any other consequences thereof.', AFFILIATES_PLUGIN_DOMAIN ) .
				'</p>';
		}

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

