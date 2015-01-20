/**
 * affiliates.js
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
jQuery(document).ready(function(){
	
	/* design */
	jQuery(".affiliate").corner("5px");
	jQuery(".filters").corner("5px");
	jQuery(".manage").corner("5px");
	
	/* effects & handling */
	var clickToggler = function() {
		var description = jQuery(this).parent().children(".view");
		var expander = jQuery(this).parent().children(".expander");
		if ( description.is(":hidden") ) {
			description.slideDown("fast");
			expander.contents().remove();
			expander.append("[-] ");
		} else {
			description.slideUp("fast");
			expander.contents().remove();
			expander.append("[+] ");
		}
	};
	
	jQuery('.view-toggle .expander').each( function() {
		jQuery(this).click(clickToggler);
	});
	jQuery('.view-toggle .view-toggle-label').each( function() {
		jQuery(this).click(clickToggler);
	});

	/* filter highlighting */
	jQuery('.filters input[type="text"], .filters input[type="checkbox"], .filters input[type="radio"], .filters textarea, .filters select').each( function() {
		if ( jQuery(this).val() !== '' ) {
			this.className += ' active-filter';
		}
	});
});
