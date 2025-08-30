/**
 * affiliates-robot-cleaner.js
 *
 * Copyright (c) 2010 - 2019 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 4.0.0
 */

( function( $ ) {
	$( document ).ready( function() {
		/* launch cleaning */
		$( document ).on( 'click touchstart', '#affiliates-robot-cleaner-clean', function( event ) {

			if ( typeof event.preventDefault === 'function' ) {
				event.preventDefault();
			}
			if ( typeof event.stopImmediatePropagation === 'function' ) {
				event.stopImmediatePropagation();
			}
			if ( typeof event.stopPropagation === 'function' ) {
				event.stopPropagation();
			}

			var ajaxing = $( '#affiliates-robot-cleaner-clean' ).data( 'ajaxing' );
			if ( typeof ajaxing !== 'undefined' && ajaxing ) {
				return;
			}

			if (
				( typeof ajaxurl !== 'undefined' ) &&
				( typeof affiliates_robot_cleaner_ajax_nonce !== 'undefined' )
			) {
				$( '#affiliates-robot-cleaner-clean' ).prop( 'disabled', true );
				$( '#affiliates-robot-cleaner-clean' ).data( 'ajaxing', true );
				$( '#affiliates-robot-cleaner-throbber' ).show();
				var data = {
					action : 'affiliates_robot_cleaner_clean',
					affiliates_robot_cleaner_ajax_nonce : affiliates_robot_cleaner_ajax_nonce
				};
				$.ajax( {
					type   : 'POST',
					async  : false,
					url    : ajaxurl,
					data   : data,
					dataType : 'json',
					success : function( data, textStatus, jqXHR ) {
						$( '#affiliates-robot-cleaner-result' ).html( affiliates_robot_cleaner.rows_deleted + ' ' + data );
					},
					error : function( data, textStatus, jqXHR ) {
						$( '#affiliates-robot-cleaner-result' ).html( affiliates_robot_cleaner.failed );
					},
					complete : function( data, textStatus, jqXHR ) {
						$( '#affiliates-robot-cleaner-clean' ).prop( 'disabled', false );
						$( '#affiliates-robot-cleaner-clean' ).data( 'ajaxing', false );
						$( '#affiliates-robot-cleaner-throbber' ).fadeOut( 1000 );
					}
				} );
			}
		});
	} );
} )( jQuery );
