<?php
/**
 * class-affiliates-ui-elements.php
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
 * @since 2.8.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders common User Interface elements.
 */
class Affiliates_UI_Elements {

	/**
	 * Renders a selection input element for affiliates.
	 * The rendered input element's name is affiliate_id.
	 * 
	 * @param array $args indexes : affiliate_id for the selected affiliate, show_inoperative to include inoperative affiliates
	 * @return string HTML
	 */
	public static function affiliates_select( $args = array() ) {

		$affiliate_id = null;
		if ( isset( $args['affiliate_id'] ) ) {
			$affiliate_id = intval( $args['affiliate_id'] );
		}
		$show_inoperative = false;
		if ( isset( $args['show_inoperative'] ) ) {
			$show_inoperative = boolval( $args['show_inoperative'] );
		}

		$affiliates = affiliates_get_affiliates( true, !$show_inoperative );
		$affiliates_select = '';
		if ( !empty( $affiliates ) ) {
			$affiliates_select .= '<label class="affiliate-id-filter">';
			$affiliates_select .= __( 'Affiliate', AFFILIATES_PLUGIN_DOMAIN );
			$affiliates_select .= ' ';
			$affiliates_select .= '<select class="affiliate-id-filter" name="affiliate_id">';
			$affiliates_select .= '<option value="">--</option>';
			foreach ( $affiliates as $affiliate ) {
				if ( $affiliate_id == $affiliate['affiliate_id']) {
					$selected = ' selected="selected" ';
				} else {
					$selected = '';
				}
				$affiliates_select .= '<option ' . $selected . ' value="' . esc_attr( $affiliate['affiliate_id'] ) . '">' . esc_attr( stripslashes( $affiliate['name'] ) ) . '</option>';
			}
			$affiliates_select .= '</select>';
			$affiliates_select .= '</label>';
		}
		return $affiliates_select;
	}
}
