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

// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

/**
 * General settings section.
 */
class Affiliates_Settings_General extends Affiliates_Settings {

	/**
	 * Renders the general settings section.
	 */
	public static function section() {

		global $wp, $wpdb, $affiliates_options, $wp_roles;

		if ( isset( $_REQUEST['subsection'] ) && $_REQUEST['subsection'] === 'robot-cleaner' ) {
			Affiliates_Robot_Cleaner::admin();
			echo '<p style="border-top: 1px solid #ccc; margin: 8px 0; padding: 8px 0;">';
			if ( !isset( $_REQUEST['action'] ) ) {
				$url = add_query_arg(
					array( 'section' => 'general' ),
					admin_url( 'admin.php?page=affiliates-admin-settings' )
				);
			} else {
				$url = add_query_arg(
					array( 'section' => 'general', 'subsection' => 'robot-cleaner' ),
					admin_url( 'admin.php?page=affiliates-admin-settings' )
				);
			}
			printf(
				'<a class="button" href="%s">%s</a>',
				esc_url( $url ),
				esc_html__( 'Back', 'affiliates' )
			);
			echo '</p>';
			return;
		}

		if ( isset( $_REQUEST['subsection'] ) && $_REQUEST['subsection'] === 'data-cleaner' ) {
			Affiliates_Data_Cleaner::admin();
			echo '<p style="border-top: 1px solid #ccc; margin: 8px 0; padding: 8px 0;">';
			if ( !isset( $_REQUEST['action'] ) ) {
				$url = add_query_arg(
					array( 'section' => 'general' ),
					admin_url( 'admin.php?page=affiliates-admin-settings' )
				);
			} else {
				$url = add_query_arg(
					array( 'section' => 'general', 'subsection' => 'data-cleaner' ),
					admin_url( 'admin.php?page=affiliates-admin-settings' )
				);
			}
			printf(
				'<a class="button" href="%s">%s</a>',
				esc_url( $url ),
				esc_html__( 'Back', 'affiliates' )
				);
			echo '</p>';
			return;
		}

		$robots_table = _affiliates_get_tablename( 'robots' );

		if ( isset( $_POST['submit'] ) ) {

			if ( affiliates_verify_post_nonce( AFFILIATES_ADMIN_SETTINGS_NONCE, 'admin' ) ) {

				// robots
				$robots = sanitize_textarea_field( wp_unslash( $_POST['robots'] ?? '' ) ); // preserve newlines
				$wpdb->query( "DELETE FROM $robots_table" );
				if ( !empty( $robots ) ) {
					$robots = str_replace( ",", "\n", $robots );
					$robots = str_replace( "\r", "", $robots );
					$robots = explode( "\n", $robots );
					$robots = array_unique( $robots );
					foreach ( $robots as $robot ) {
						$robot = trim( $robot );
						if ( !empty( $robot ) ) {
							$query = $wpdb->prepare( "INSERT INTO $robots_table (name) VALUES (%s);", $robot );
							$wpdb->query( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						}
					}
				}

				$pname = !empty( $_POST['pname'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['pname'] ) ) ) : get_option( 'aff_pname', AFFILIATES_PNAME );
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
					echo '<div class="error">' . esc_html__( 'The Affiliate URL parameter name has not been changed, the suggested name is not valid. Only lower case letters and the underscore _ are allowed.', 'affiliates' ) . '</div>';
				} else if ( in_array( $pname, $forbidden_names ) ) {
					$pname = get_option( 'aff_pname', AFFILIATES_PNAME );
					echo '<div class="error">' . esc_html__( 'The Affiliate URL parameter name has not been changed, the suggested name is forbidden.', 'affiliates' ) . '</div>';
				}
				$old_pname = get_option( 'aff_pname', AFFILIATES_PNAME );
				if ( $pname !== $old_pname ) {
					update_option( 'aff_pname', $pname );
					affiliates_update_rewrite_rules();
					echo '<div class="info">' .
						'<p>' .
						sprintf(
							esc_html__( 'The Affiliate URL parameter name has been changed from %1$s to %2$s.', 'affiliates' ),
							'<code>' . esc_html( $old_pname ) . '</code>',
							'<code>' . esc_html( $pname ) . '</code>'
						) .
						'</p>' .
						'<p class="warning">' .
						esc_html__( 'If your affiliates are using affiliate links based on the previous Affiliate URL parameter name, they NEED to update their affiliate links.', 'affiliates' ) .
						'</p>' .
						'<p class="warning">' .
						esc_html__( 'Unless the incoming affiliate links reflect the current Affiliate URL parameter name, no affiliate hits, visits or referrals will be recorded.', 'affiliates' ) .
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

				$encoding_id = sanitize_text_field( wp_unslash( $_POST['id_encoding'] ?? '' ) );
				if ( array_key_exists( $encoding_id, affiliates_get_id_encodings() ) ) {
					// important: must use normal update_option/get_option otherwise we'd have a per-user encoding
					update_option( 'aff_id_encoding', $encoding_id );
				}

				$rolenames = $wp_roles->get_names();
				$caps = array(
					AFFILIATES_ACCESS_AFFILIATES => __( 'Access affiliates', 'affiliates' ),
					AFFILIATES_ADMINISTER_AFFILIATES => __( 'Administer affiliates', 'affiliates' ),
					AFFILIATES_ADMINISTER_OPTIONS => __( 'Administer options', 'affiliates' ),
				);
				foreach ( $rolenames as $rolekey => $rolename ) {
					$role = $wp_roles->get_role( $rolekey );
					foreach ( $caps as $capkey => $capname ) {
						$role_cap_id = $rolekey.'-'.$capkey;
						if ( !empty( $_POST[$role_cap_id] ) ) {
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

				self::settings_saved_notice();
			}
		}

		$robots = '';
		$db_robots = $wpdb->get_results( "SELECT name FROM $robots_table", OBJECT );
		foreach ( $db_robots as $db_robot ) {
			$robots .= $db_robot->name . "\n";
		}

		$pname    = get_option( 'aff_pname', AFFILIATES_PNAME );
		$redirect = get_option( 'aff_redirect', false );

		$id_encoding = get_option( 'aff_id_encoding', AFFILIATES_NO_ID_ENCODING );
		$id_encoding_select = '';
		$encodings = affiliates_get_id_encodings();
		if ( !empty( $encodings ) ) {
			$id_encoding_select .= '<label class="id-encoding" for="id_encoding">' . esc_html__('Affiliate ID Encoding', 'affiliates' ) . '</label>';
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
			AFFILIATES_ACCESS_AFFILIATES => __( 'Access affiliates', 'affiliates' ),
			AFFILIATES_ADMINISTER_AFFILIATES => __( 'Administer affiliates', 'affiliates' ),
			AFFILIATES_ADMINISTER_OPTIONS => __( 'Administer options', 'affiliates' ),
		);
		$caps_table = '<table class="affiliates-permissions">';
		$caps_table .= '<thead>';
		$caps_table .= '<tr>';
		$caps_table .= '<td class="role">';
		$caps_table .= esc_html__( 'Role', 'affiliates' );
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
				$caps_table .= '<input type="checkbox" name="' . esc_attr( $role_cap_id ) . '" id="' . esc_attr( $role_cap_id ) . '" ' . $checked . '/>';
				$caps_table .= '</td>';
			}
			$caps_table .= '</tr>';
		}
		$caps_table .= '</tbody>';
		$caps_table .= '</table>';

		$delete_data = get_option( 'aff_delete_data', false );

		do_action( 'affiliates_settings_general_before_form' );

		echo
			'<form action="" name="options" method="post">' .
			'<div>';

		echo
			'<h3>' . esc_html__( 'Affiliate URL parameter name', 'affiliates' ) . '</h3>' .
			'<p>' .
			'<input class="pname" name="pname" type="text" value="' . esc_attr( $pname ) . '" />' .
			'</p>' .
			'<ul>' .
			'<li>' .
			sprintf( esc_html__( 'The current Affiliate URL parameter name is: %s', 'affiliates' ), '<code>' . esc_html( $pname ) . '</code>' ) .
			'</li>' .
			'<li>' .
			sprintf( esc_html__( 'The default Affiliate URL parameter name is: %s', 'affiliates' ), '<code>' . esc_html( AFFILIATES_PNAME ) . '</code>' ) .
			'</li>' .
			'</ul>' .
			'<p class="description warning">' .
			esc_html__( 'CAUTION: If you change this setting and have distributed affiliate links or permalinks, make sure that these are updated. Unless the incoming affiliate links reflect the current URL parameter name, no affiliate hits, visits or referrals will be recorded.', 'affiliates' ) .
			'</p>';

		echo
			'<h3>' . esc_html__( 'Redirection', 'affiliates' ) . '</h3>' .
			'<p>' .
			'<label>' .
			sprintf( '<input class="redirect" name="redirect" type="checkbox" %s/>', $redirect ? ' checked="checked" ' : '' ) .
			' ' .
			esc_html__( 'Redirect', 'affiliates' ) .
			'</label>' .
			'</p>' .
			'<p class="description">' .
			esc_html__( 'Redirect to destination without Affiliate URL parameter, after a hit on an affiliate link has been detected.', 'affiliates' ) .
			'</p>';

		echo
			'<h3>' . esc_html__( 'Affiliate ID encoding', 'affiliates' ) . '</h3>' .
			'<p>' .
			$id_encoding_select . // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'</p>' .
			'<p>' .
			sprintf( esc_html__( 'The current encoding in effect is: %s', 'affiliates' ), '<strong>' . esc_html( $encodings[$id_encoding] ) . '</strong>' ) .
			'</p>' .
			'<p class="description warning">' .
			esc_html__( 'CAUTION: If you change this setting and have distributed affiliate links or permalinks, make sure that these are updated. Unless the incoming affiliate links reflect the current encoding, no affiliate hits, visits or referrals will be recorded.', 'affiliates' ) .
			'</p>';

		echo
			'<h3>' . esc_html__( 'Permissions', 'affiliates' ) . '</h3>' .
			'<p>' .
			esc_html__( 'Do not assign permissions to open access for affiliates here.', 'affiliates' ) .
			' ' .
			esc_html__( 'This section is only intended to grant administrative access on affiliate management functions to privileged roles.', 'affiliates' ) .
			'</p>' .
			$caps_table . // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'<p class="description">' .
			esc_html__( 'A minimum set of permissions will be preserved.', 'affiliates' ) .
			'<br/>' .
			esc_html__( 'If you lock yourself out, please ask an administrator to help.', 'affiliates' ) .
			'</p>';

		echo
			'<h3>' . esc_html__( 'Robots', 'affiliates' ) . '</h3>' .
			'<p>' .
			'<textarea id="robots" name="robots" rows="10" cols="45">' . esc_html( $robots ) . '</textarea>' .
			'</p>' .
			'<p>' .
			esc_html__( 'Hits on affiliate links from these robots will be marked or not recorded. Put one entry on each line.', 'affiliates' ) .
			'</p>';
		echo '<p>' .
			sprintf(
				esc_html__( 'Use the robot cleaner to remove existing hits from robots: %s', 'affiliates' ),
				sprintf(
					'<a class="button" href="%s">%s</a>',
					esc_url( add_query_arg(
						array( 'section' => 'general', 'subsection' => 'robot-cleaner' ),
						admin_url( 'admin.php?page=affiliates-admin-settings' )
					) ),
					esc_html__( 'Robot Cleaner', 'affiliates' )
				)
			);
		echo '</p>';

		echo '<h3>' . esc_html__( 'Data', 'affiliates' ) . '</h3>';
		echo '<p>';
		printf(
			esc_html__( 'Use the data cleaner to remove data on unused hits, URIs and user agents: %s', 'affiliates' ),
			sprintf(
				'<a class="button" href="%s">%s</a>',
				esc_url( add_query_arg(
					array( 'section' => 'general', 'subsection' => 'data-cleaner' ),
					admin_url( 'admin.php?page=affiliates-admin-settings' )
				) ),
				esc_html__( 'Data Cleaner', 'affiliates' )
			)
		);
		echo '</p>';

		if ( !affiliates_is_sitewide_plugin() ) {
			echo
				'<h3>' . esc_html__( 'Deactivation and data persistence', 'affiliates' ) . '</h3>' .
				'<p>' .
				'<label>' .
				'<input name="delete-data" type="checkbox" ' . ( $delete_data ? 'checked="checked"' : '' ) . '/>' .
				' ' .
				esc_html__( 'Delete all plugin data on deactivation', 'affiliates' ) .
				'</label>' .
				'</p>' .
				'<p class="description warning">' .
				esc_html__( 'CAUTION: If this option is active while the plugin is deactivated, ALL affiliate and referral data will be DELETED. If you want to retrieve data about your affiliates and their referrals and are going to deactivate the plugin, make sure to back up your data or do not enable this option. By enabling this option you agree to be solely responsible for any loss of data or any other consequences thereof.', 'affiliates' ) .
				'</p>';
		}

		echo
			'<p>' .
			wp_nonce_field( 'admin', AFFILIATES_ADMIN_SETTINGS_NONCE, true, false ) . // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'<input class="button button-primary" type="submit" name="submit" value="' . esc_attr__( 'Save', 'affiliates' ) . '"/>' .
			'</p>' .
			'</div>' .
			'</form>';

		do_action( 'affiliates_settings_general_after_form' );

		affiliates_footer();
	}
}
