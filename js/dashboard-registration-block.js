/*!
 * dashboard-registration-block.js
 *
 * Copyright (c) 2010 - 2018 "kento" Karim Rahimpur www.itthinx.com
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

if ( typeof wp !== 'undefined' ) {

	//
	// The Affiliates Dashboard Registration block.
	// This also uses the ServerSideRender to "preview" the block in the editor. If the viewer is not an affiliate,
	// we render a notice. This avoids the spinner bug (never goes away when content is empty in blocks preview).
	//
	wp.blocks.registerBlockType(
		'affiliates/dashboard-registration',
		{
			title       : affiliates_dashboard_registration_block.title,
			description : affiliates_dashboard_registration_block.description,
			icon        : 'id',
			category    : 'affiliates',
			keywords    : [ affiliates_dashboard_registration_block.keyword_affiliates, affiliates_dashboard_registration_block.keyword_dashboard, affiliates_dashboard_registration_block.keyword_login ],
			supports    : { html : false },
			attributes  : {
				header_tag : {
					type : 'string'
				},
				content_tag : {
					type : 'string'
				},
				content : {
					type : 'string'
				}
			},
			// Add some inspector controls, use ServerSideRender to preview the block.
			edit : function( props ) {
				// let header_tag = props.attributes.header_tag || 'h2';
				// let content_tag = props.attributes.content_tag || 'p';
				let info = wp.element.createElement(
					'div',
					{
						style : {
							color: '#666',
							padding: '4px',
							backgroundColor : '#eee',
							fontSize: '14px'
						},
						key : 'info'
					},
					affiliates_dashboard_registration_block.dashboard_registration_notice
				);
				let fields = [ info ];
				let ssr_type = null;
				if ( typeof wp.serverSideRender !== 'undefined' ) {
					ssr_type = wp.serverSideRender;
				} else if ( typeof wp.components.ServerSideRender !== 'undefined' ) {
					ssr_type = wp.components.ServerSideRender;
				}
				if ( ssr_type !== null ) {
					// render the content preview via our PHP callback
					let ssr = wp.element.createElement(
						ssr_type,
						{
							block : 'affiliates/dashboard-registration',
							attributes : props.attributes,
							key : 'block'
						}
					);
					fields.unshift( ssr );
				}
				return wp.element.createElement(
					wp.components.Disabled,
					{},
					fields
				);
			},
			// It's rendered via our PHP callback so this returns simply null.
			save : function( props ) {
				return null;
			}
		}
	);

}
