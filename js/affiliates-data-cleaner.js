/**
 * affiliates-data-cleaner.js
 *
 * Copyright (c) 2010 - 2026 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 6.0.0
 */

( function( $ ) {
	$( document ).ready( function() {
		/* launch cleaning */
		$( document ).on( 'click touchstart', '#affiliates-data-cleaner-clean', function( event ) {

			if ( typeof event.preventDefault === 'function' ) {
				event.preventDefault();
			}
			if ( typeof event.stopImmediatePropagation === 'function' ) {
				event.stopImmediatePropagation();
			}
			if ( typeof event.stopPropagation === 'function' ) {
				event.stopPropagation();
			}

			var ajaxing = $( '#affiliates-data-cleaner-clean' ).data( 'ajaxing' );
			if ( typeof ajaxing !== 'undefined' && ajaxing ) {
				return;
			}

			if (
				( typeof ajaxurl !== 'undefined' ) &&
				( typeof affiliates_data_cleaner_ajax_nonce !== 'undefined' )
			) {
				$( '#affiliates-data-cleaner-clean' ).prop( 'disabled', true );
				$( '#affiliates-data-cleaner-clean' ).data( 'ajaxing', true );
				$( '#affiliates-data-cleaner-throbber' ).show();
				var data = {
					action : 'affiliates_data_cleaner_clean',
					affiliates_data_cleaner_ajax_nonce : affiliates_data_cleaner_ajax_nonce
				};
				$.ajax( {
					type   : 'POST',
					async  : false,
					url    : ajaxurl,
					data   : data,
					dataType : 'json',
					success : function( data, textStatus, jqXHR ) {
						$( '#affiliates-data-cleaner-result' ).html(
							'<ul>' +
							'<li>' + affiliates_data_cleaner.hits_deleted + ' ' + data.hits + ' / ' + data.count_hits + ' / ' + data.total_hits + '</li>' +
							'<li>' + affiliates_data_cleaner.uris_deleted + ' ' + data.uris + ' / ' + data.count_uris + ' / ' + data.total_uris + '</li>' +
							'<li>' + affiliates_data_cleaner.user_agents_deleted + ' ' + data.user_agents + ' / ' + data.count_user_agents + ' / ' + data.total_user_agents + '</li>' +
							'</ul>'
						);
					},
					error : function( data, textStatus, jqXHR ) {
						$( '#affiliates-data-cleaner-result' ).html( affiliates_data_cleaner.failed );
					},
					complete : function( data, textStatus, jqXHR ) {
						$( '#affiliates-data-cleaner-clean' ).prop( 'disabled', false );
						$( '#affiliates-data-cleaner-clean' ).data( 'ajaxing', false );
						$( '#affiliates-data-cleaner-throbber' ).fadeOut( 1000 );
					}
				} );
			}
		});
	} );
} )( jQuery );
