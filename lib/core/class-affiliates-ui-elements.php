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
	 * Extension used for select
	 * @var string
	 */
	private static $select = 'selectize';

	/**
	 * Extension chooser - determines what UI extension is used for an element.
	 *
	 * @param string $element choices: select
	 * @param string $extension choices: selectize
	 */
	public static function set_extension( $element, $extension ) {
		switch( $element ) {
			case 'select' :
				self::$select = $extension;
				break;
		}
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public static function enqueue( $element = null ) {
		global $affiliates_version;
		switch( $element ) {
			case 'select' :
				switch ( self::$select ) {
					case 'selectize' :
						if ( !wp_script_is( 'selectize' ) ) {
							wp_enqueue_script( 'selectize', AFFILIATES_PLUGIN_URL . 'js/selectize/selectize.min.js', array( 'jquery' ), $affiliates_version, false );
						}
						if ( !wp_style_is( 'selectize' ) ) {
							wp_enqueue_style( 'selectize', AFFILIATES_PLUGIN_URL . 'css/selectize/selectize.bootstrap2.css', array(), $affiliates_version );
						}
						break;
				}
				break;
		}
	}

	/**
	 * Renders a selection input element for affiliates.
	 * The rendered input element's name is affiliate_id.
	 *
	 * @param array $args indexes : affiliate_id for the selected affiliate, show_inoperative to include inoperative affiliates
	 *
	 * @return string HTML
	 */
	public static function affiliates_select( $args = array() ) {

		$defaults = array(
			'name'        => 'affiliate_id',
			'class'       => 'affiliates-uie',
			'label-class' => '',
			'label-prefix' => '',
			'label-suffix' => ''
		);
		$args = array_merge( $defaults, $args );
		$affiliate_id = null;
		if ( isset( $args['affiliate_id'] ) ) {
			$affiliate_id = intval( $args['affiliate_id'] );
		}
		$show_inoperative = false;
		if ( isset( $args['show_inoperative'] ) ) {
			$show_inoperative = (bool) $args['show_inoperative'];
		}
		$show_deleted = false;
		if ( isset( $args['show_deleted'] ) ) {
			$show_deleted = (bool) $args['show_deleted'];
		}

		$affiliates = affiliates_get_affiliates( !$show_deleted, !$show_inoperative );
		$affiliates_select = '';
		if ( !empty( $affiliates ) ) {
			$affiliates_select .= sprintf( '<label class="%s">', esc_attr( $args['label-class'] ) );
			$affiliates_select .= $args['label-prefix'];
			$affiliates_select .= __( 'Affiliate', 'affiliates' );
			$affiliates_select .= $args['label-suffix'];
			$affiliates_select .= ' ';
			$affiliates_select .= sprintf( '<select class="%s" name="%s">', esc_attr( $args['class'] ), esc_attr( $args['name'] ) );
			$affiliates_select .= sprintf( '<option value="" %s>&mdash;</option>', empty( $affiliate_id ) ? ' selected="selected" ' : '' );
			foreach ( $affiliates as $affiliate ) {
				if ( $affiliate_id == $affiliate['affiliate_id']) {
					$selected = ' selected="selected" ';
				} else {
					$selected = '';
				}
				$affiliates_select .= sprintf(
					'<option value="%s" %s>%s [%d]</option>',
					esc_attr( $affiliate['affiliate_id'] ),
					$selected,
					esc_html( stripslashes( $affiliate['name'] ) ),
					esc_html( $affiliate['affiliate_id'] )
				);
			}
			$affiliates_select .= '</select>';
			$affiliates_select .= '</label>';
		}
		return $affiliates_select;
	}

	/**
	 * Render select script and style.
	 * @param string $selector identifying the select, default: select.affiliates-uie
	 * @param boolean $script render the script, default: true
	 * @param boolean $on_document_ready whether to trigger on document ready, default: true
	 * @param boolean $create allow to create items, default: false (only with selectize)
	 *
	 * @return string HTML
	 */
	public static function render_select( $selector = 'select.affiliates-uie', $script = true, $on_document_ready = true, $create = false ) {
		$output = '';
		if ( $script ) {
			$output .= '<script type="text/javascript">';
			if ( $on_document_ready ) {
				$output .= 'document.addEventListener( "DOMContentLoaded", function() {';
			}
			$output .= 'if (typeof jQuery !== "undefined"){';
			switch( self::$select ) {
				case 'selectize' :
					$output .= sprintf(
					'jQuery("%s").selectize({%splugins: ["remove_button"]});',
					$selector,
					$create ? 'create:true,' : ''
							);
					break;
			}
			$output .= '}'; // typeof jQuery
			if ( $on_document_ready ) {
				$output .= '});';
			}
			$output .= '</script>';
		}
		return $output;
	}

	public static function render_add_titles( $selector ) {
		$output = '<script type="text/javascript">';
		$output .= 'if ( typeof jQuery !== "undefined" ) {';
		$output .= sprintf( 'jQuery("%s").each(', $selector );
		$output .= 'function(){';
		$output .= 'var title = jQuery(this).html().replace( /(<\/[^>]+>)/igm , "$1 ");';
		$output .= 'jQuery(this).attr("title", this.innerText || jQuery(jQuery.parseHTML(title)).text().replace(/\s+/igm, " ") );';
		$output .= '}';
		$output .= ');';
		$output .= '}';
		$output .= '</script>';
		return $output;
	}

}
