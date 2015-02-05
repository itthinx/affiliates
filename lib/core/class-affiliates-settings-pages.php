<?php
/**
 * class-affiliates-settings-pages.php
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
 * Integration section.
 */
class Affiliates_Settings_Pages extends Affiliates_Settings {

	/**
	 * Renders the generator form and handles page
	 * generation form submission.
	 */
	public static function section() {

		$pages_generated_info = '';

		//
		// handle page generation form submission
		//
		if ( isset( $_POST['generate'] ) ) {
			if ( wp_verify_nonce( $_POST[AFFILIATES_ADMIN_SETTINGS_GEN_NONCE], 'admin' ) ) {
				require_once( AFFILIATES_CORE_LIB . '/class-affiliates-generator.php' );
				$post_ids = Affiliates_Generator::setup_pages();
				foreach ( $post_ids as $post_id ) {
					$link = '<a href="' . get_permalink( $post_id ) . '" target="_blank">' . get_the_title( $post_id ) . '</a>';
					$pages_generated_info .= '<div class="info">' . __( sprintf( 'The %s page has been created.', $link ), AFFILIATES_PLUGIN_DOMAIN ) . '</div>';
				}
			}
		}

		echo '<h3>' . __( 'Generator', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>';

		//
		// Generator form
		//
		echo
			'<form action="" name="options" method="post">' .
			'<div>' .
			'<p>' .
			__( 'Press the button to generate a default affiliate area.', AFFILIATES_PLUGIN_DOMAIN ) .
			' ' .
			'<input class="generate button" name="generate" type="submit" value="' . __( 'Generate', AFFILIATES_PLUGIN_DOMAIN ) .'" />' .
			wp_nonce_field( 'admin', AFFILIATES_ADMIN_SETTINGS_GEN_NONCE, true, false ) .
			'</p>' .
			$pages_generated_info.
			'</div>' .
			'</form>';
		
		echo '<p>';
		echo __( 'The generated page contains Affiliates shortcodes and can be used as an out-of-the-box affiliate area or as a framework for customized affiliate areas and pages.', AFFILIATES_PLUGIN_DOMAIN );
		echo '</p>';

		//
		// Pages containing affiliates shortcodes
		//
		echo '<h3>' . __( 'Pages', AFFILIATES_PLUGIN_DOMAIN ) . '</h3>';

		global $wpdb;
		$post_options = '';
		$post_ids = array();
		// We also have [referrer_id] and [referrer_user] but these are not essential in
		// determining whether an affiliate page has been set up.
		$posts = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_content LIKE '%[affiliates_%' AND post_status = 'publish'" );
		foreach( $posts as $post ) {
			$post_ids[] = $post->ID;
		}

		if ( count( $posts ) == 0 ) {
			echo '<p>';
			echo __( 'It seems that you do not have any pages set up for your affiliates yet.', AFFILIATES_PLUGIN_DOMAIN );
			echo '</p>';
			echo '<p>';
			echo __( 'You can use the page generation option to create the default affiliate area for your affiliates.', AFFILIATES_PLUGIN_DOMAIN );
			echo '</p>';
		} else {
			echo '<p>';
			echo _n(
				'This page containing Affiliates shortcodes has been detected :',
				'These pages containing Affiliates shortcodes have been detected :',
				count( $posts ),
				AFFILIATES_PLUGIN_DOMAIN
			);
			echo '</p>';
			$post_list = '<ul>';
			foreach( $post_ids as $post_id ) {
				$post_title = get_the_title( $post_id );
				$post_list .= sprintf(
					'<li><a href="%s">%s</a></li>',
					get_permalink( $post_id ),
					esc_html( $post_title )
				);
			}
			$post_list .= '</ul>';
			echo $post_list;
		}

		echo '<p>';
		_e( 'You can modify the default affiliate area and also create customized pages for your affiliates using shortcodes.', AFFILIATES_PLUGIN_DOMAIN );
		echo '</p>';
		echo '<p>';
		// @todo update the documentation link :
		_e( 'Please refer to the <a href="http://www.itthinx.com/documentation/affiliates/">Documentation</a> for more details.', AFFILIATES_PLUGIN_DOMAIN );
		echo '</p>';

		affiliates_footer();
	}
}
