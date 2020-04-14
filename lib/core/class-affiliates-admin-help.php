<?php
/**
 * class-affiliates-admin-help.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package affiliates
 * @since 4.6.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns or renders the common footer for help tabs.
 *
 * @param boolean $render
 *
 * @return string
 */
function affiliates_help_tab_footer( $render = true ) {
	return Affiliates_Admin_Help::affiliates_help_tab_footer( $render );
}

/**
 * Help sections of the Affiliates system.
 */
class Affiliates_Admin_Help {

	/**
	 * Adds the filter for contextual help.
	 */
	public static function init() {
		add_action( 'current_screen', array( __CLASS__, 'current_screen' ) );
	}

	/**
	 * Adds contextual help on our screens.
	 *
	 * @param WP_Screen $screen
	 */
	public static function current_screen( $screen ) {
		if ( $screen instanceof WP_Screen ) {
 			$screen_id = $screen->id;

			$pname = get_option( 'aff_pname', AFFILIATES_PNAME );

			$show_affiliates_help = false;

			$title = '<h3>';
			$title .= esc_html__( 'Affiliates', 'affiliates' );
			$title .= '</h3>';

			$help = apply_filters( 'affiliates_help_tab_title', $title );

			switch ( $screen_id ) {
				case 'toplevel_page_affiliates-admin' :
				case 'affiliates_page_affiliates-admin-affiliates':
				case 'affiliates_page_affiliates-admin-hits' :
				case 'affiliates_page_affiliates-admin-hits-affiliate' :
				case 'affiliates_page_affiliates-admin-totals' :
				case 'affiliates_page_affiliates-admin-referrals' :
				case 'affiliates_page_affiliates-admin-options' :
					$show_affiliates_help = true;
					break;
				default:
					$show_affiliates_help = strpos( $screen_id, 'affiliates_page_affiliates' ) !== false;
			}

			$help .= self::affiliates_help_tab_footer( false );

			if ( !defined( 'AFFILIATES_PRO_PLUGIN_DOMAIN' ) && !defined( 'AFFILIATES_ENTERPRISE_PLUGIN_DOMAIN' ) ) {
				$help .= '<p>';
				$help .= esc_html__( 'We highly appreciate it if you support our work by using our commercial software.', 'affiliates' );
				$help .= '</p>';
				$help .= '<p>';
				$help .= affiliates_donate( false, true );
				$help .= '</p>';
			}

			if ( $show_affiliates_help ) {
				$screen->add_help_tab(
					array(
						'id' => 'affiliates',
						'title' => esc_html__( 'Affiliates', 'affiliates' ),
						'content' => $help
					)
				);
			}
		}
	}

	/**
	 * Returns or renders the common footer for help tabs.
	 *
	 * @param boolean $render
	 *
	 * @return string or nothing
	 */
	public static function affiliates_help_tab_footer( $render = true ) {

		$prefix = '<p>';
		$prefix .= __( 'The complete documentation is available on the Documentation pages &hellip;', 'affiliates' );
		$prefix .= '</p>';

		$footer =
			'<div class="affiliates-documentation">' .
			sprintf(
				'<a href="%s">%s</a>',
				esc_attr( 'http://docs.itthinx.com/document/affiliates/' ),
				esc_html( __( 'Online documentation', 'affiliates' ) )
			) .
			'</div>';
		$footer = apply_filters( 'affiliates_help_tab_footer', $footer );

		$footer = $prefix . $footer;

		if ( $render ) {
			echo $footer;
		} else {
			return $footer;
		}
	}
}
Affiliates_Admin_Help::init();
