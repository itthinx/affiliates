/**
 * affiliates.js
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
 * @since affiliates 1.0.0
 */

jQuery( document ).ready( function() {

	/* effects & handling */
	var clickToggler = function() {
		var description = jQuery( this ).parent().children( ".view" );
		var expander = jQuery( this ).parent().children( ".expander" );
		if ( description.is( ":hidden" ) ) {
			description.slideDown( "fast" );
			expander.contents().remove();
			expander.append( "[-] " );
		} else {
			description.slideUp( "fast" );
			expander.contents().remove();
			expander.append( "[+] " );
		}
	};

	jQuery( '.view-toggle .expander' ).each( function() {
		jQuery( this ).click( clickToggler );
	} );
	jQuery( '.view-toggle .view-toggle-label' ).each( function() {
		jQuery( this ).click( clickToggler );
	} );

	/* filter highlighting */
	jQuery( '.filters input[type="text"], .filters input[type="checkbox"], .filters input[type="radio"], .filters textarea, .filters select' ).each( function() {
		if ( jQuery( this ).val() !== '' ) {
			this.className += ' active-filter';
		}
	} );

	/* filters toggle */
	jQuery( '#filters-toggle' ).click( function() {
		var ajaxing = jQuery( '#filters-toggle' ).data( 'ajaxing' );
		if ( !( typeof ajaxing === 'undefined' || !ajaxing ) ) {
			return;
		}
		jQuery( '#filters-toggle' ).data( 'ajaxing', true );
		jQuery( '#filters-container' ).toggle();
		var visible = jQuery( '#filters-container' ).is( ':visible' );
		if ( visible ) {
			jQuery( this ).addClass( 'on' );
			jQuery( this ).removeClass( 'off' );
		} else {
			jQuery( this ).addClass( 'off' );
			jQuery( this ).removeClass( 'on' );
		}
		if (
			( typeof ajaxurl !== 'undefined' ) &&
			( typeof affiliates_ajax_nonce !== 'undefined' )
		) {
			var data = {
				action : 'affiliates_set_option',
				affiliates_ajax_nonce : affiliates_ajax_nonce,
				key : 'show_filters',
				value : JSON.stringify( visible )
			};
			jQuery.ajax( {
				type   : 'POST',
				async  : false,
				url    : ajaxurl,
				data   : data
			} );
		}
		jQuery( '#filters-toggle' ).data( 'ajaxing', false );
	} );
} );
