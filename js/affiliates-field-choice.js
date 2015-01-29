/**
 * affiliates-field-choice.js
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

jQuery(document).ready(function(){

	var aff_reg_field_count = jQuery('#registration-fields > table > tbody tr').length;

	jQuery("#registration-fields").on('click','button.field-add',function(event){
		event.stopPropagation();
		var i = aff_reg_field_count++,
		row =
		'<tr>' +
		'<td>' +
		'<input type="checkbox" name="field-enabled['+i+']" checked="checked" />' +
		'</td>' +
		'<td>' +
		'<input type="text" name="field-name['+i+']" value="" />' +
		'</td>' +
		'<td>' +
		'<input type="text" name="field-label['+i+']" value="" />' +
		'</td>' +
		'<td>' +
		'<input type="checkbox" name="field-required['+i+']" />' +
		'</td>' +
		'<td>' +
		'<input type="hidden" name="field-type['+i+']" value="text" />' +
		'<button class="field-remove" type="button" value="'+i+'">Remove</button>' + // @todo l8n
		'</td>' +
		'</tr>';
		jQuery('#registration-fields > table > tbody').append(row);
	});

	jQuery("#registration-fields").on('click','button.field-remove',function(event){
		event.stopPropagation();
		jQuery(event.target).parent().parent().remove();
	});
});
