<?php
/**
* class-affiliates-generator.php
*
* Copyright (c) 2010-2012 "kento" Karim Rahimpur www.itthinx.com
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
* @since affiliates 1.3.1
*/
class Affiliates_Generator {
	
	public static function setup_pages() {
		
		global $affiliates_admin_messages;
		
		do_action( 'affiliates_before_setup_pages' );
		
		$post_ids = array();
		
		// create a page with 
		$affiliate_area_page_content =
'[affiliates_is_not_affiliate]
<p>Please log in to access the affiliate area.</p>
[affiliates_login_redirect]
<p>If you are not an affiliate, you can join the affiliate program here:</p>
[affiliates_registration]
[/affiliates_is_not_affiliate]

[affiliates_is_affiliate]
<p>Welcome to your affiliate area. Here you can find information about your affiliate link and earnings.</p>
<h3>Affiliate link</h3>
<p>Your affiliate URL:<br/>
<code>[affiliates_url]</code></p>
<p>Use this code to embed your affiliate link:<br/>
<code>&lt;a href="[affiliates_url]"&gt;Affiliate link&lt;/a&gt;</code></p>
<p>Tip: You should change the text <em>Affiliate link</em> to something more attractive.</p>
<h3>Performance</h3>
<h4>Total Earnings</h4>
<h5>Commissions pending payment</h5>
[affiliates_referrals show="total" status="accepted"]
<h5>Commissions paid</h5>
[affiliates_referrals show="total" status="closed"]
<h4>Number of sales referred</h4>
<ul>
<li>Accepted referrals pending payment: [affiliates_referrals status="accepted"]</li>
<li>Referrals paid: [affiliates_referrals status="closed"]</li>
</ul>
<h4>Monthly Earnings</h4>
[affiliates_earnings]
[affiliates_logout]
[/affiliates_is_affiliate]
';
		$affiliate_area_page_content = apply_filters( 'affiliates_affiliate_area_page_content', $affiliate_area_page_content );
		
		$postarr = array(
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_content'   => $affiliate_area_page_content,
			'post_status'    => 'publish',
			'post_title'     => __( 'Affiliate Area', AFFILIATES_PLUGIN_DOMAIN ),
			'post_type'      => 'page'
		);
		$post_id = wp_insert_post( $postarr );
		if ( $post_id instanceof WP_Error ) {
			$affiliates_admin_messages[] = '<div class="error">' . __( sprintf( 'The affiliate area page could not be created. Error: %s', $post_id->get_error_message() ), AFFILIATES_PLUGIN_DOMAIN ) . '</div>';
		} else {
			$post_ids[] = $post_id;
		}
		
		$post_ids = apply_filters( 'affiliates_setup_pages', $post_ids );
		
		do_action( 'affiliates_after_setup_pages', $post_ids );
		
		return $post_ids;
	}
}

