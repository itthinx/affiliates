/**
 * datepickers.js
 *
 * Copyright (c) 2010, 2011 "kento" Karim Rahimpur www.itthinx.com
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

( function( $ ) {
	$( document ).ready( function() {
		// Add a datepicker on .datefield text input fields.
		$( '.datefield[type="text"]' ).not( '.hasDatePicker' ).datepicker(
			{
				dateFormat : 'yy-mm-dd',
				firstDay   : 1
			}
		);
		// Add a datepicker on date fields where the browser does not support it.
		if ( $( '[type="date"]' ).prop( 'type' ) != 'date' ) {
			$( '.datefield[type="date"]' ).not( '.hasDatePicker' ).datepicker(
				{
					dateFormat : 'yy-mm-dd',
					firstDay   : 1
				}
			);
		}
	} );
} )( jQuery );
