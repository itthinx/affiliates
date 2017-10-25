<?php
/**
 * affiliates-admin-add-ons.php
 * 
 * Copyright (c) 2010 - 2014 "kento" Karim Rahimpur www.itthinx.com
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
 * @since 2.7.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

function affiliates_admin_add_ons() {

	echo '<div class="affiliates-admin-add-ons add-ons">';

	echo '<h1>';
	echo __( 'Affiliates Extensions and Add-Ons', 'affiliates' );
	echo '</h1>';

	echo '<p>';
	echo __( 'Get additional features and access to premium support!', 'affiliates' );
	echo '</p>';

	echo '<h2>';
	echo __( 'Recommended plugins and extensions', 'affiliates' );
	echo '</h2>';

	$entries = array(
		'affiliates-pro' => array(
			'title'   => 'Affiliates Pro',
			'content' => 'Affiliates Pro is a powerful affiliate marketing and management system for sellers, shops and developers, who want to increase sales and foster growth with their own affiliate program.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-pro.png',
			'url'     => 'http://www.itthinx.com/shop/affiliates-pro/',
			'index'   => 10
		),
		'affiliates-enterprise' => array(
			'title'   => 'Affiliates Enterprise',
			'content' => 'Affiliates Enterprise is a powerful affiliate marketing and management system for active marketers, sellers, shops and developers. This growth-oriented business solution features affiliate campaigns, mulitple tiers and pixel tracking, in addition to all the powerful features included in Affiliates Pro.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-enterprise.png',
			'url'     => 'http://www.itthinx.com/shop/affiliates-enterprise/',
			'index'   => 10
		),
		'affiliates-by-username' => array(
			'title'   => 'Affiliates by Username',
			'content' => 'This extension allows affiliate links to indicate usernames in addition to the affiliate IDs used normally.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-by-username.png',
			'url'     => 'http://www.itthinx.com/shop/affiliates-by-username/',
			'index'   => 20
		),
		'affiliates-coupons' => array(
			'title'   => 'Affiliates Coupons',
			'content' => 'This extension requires Affiliates Pro or Affiliates Enterprise and WooCommerce. It allows to create coupons for affiliates automatically and in bulk.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-coupons.png',
			'url'     => 'http://www.itthinx.com/shop/affiliates-coupons/',
			'index'   => 100
		),
		'affiliates-ms' => array(
			'title'   => 'Affiliates MS',
			'content' => 'Affiliates MS is a solution to maintain a centralized affiliate program for a WordPress Network of sites.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-ms.png',
			'url'     => 'http://www.itthinx.com/shop/affiliates-ms/',
			'index'   => 100
		),
		'affiliates-permanent' => array(
			'title'   => 'Affiliates Permanent',
			'content' => 'New customers (or new users) are assigned to the referring affiliate. The affiliate will be credited with a referral on every purchase made by the customer from thereon. Assignments can be changed manually in user profiles.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-permanent.png',
			'url'     => 'http://www.itthinx.com/shop/affiliates-permanent/',
			'index'   => 100
		),
		'affiliates-products' => array(
			'title'   => 'Affiliates Products',
			'content' => 'This extension requires WooCommerce and provides product commissions for distribution and vendors. It automatically grants commissions on product sales to assigned partners or affiliates. It is suitable to share revenue on every sale of one or more products.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-products.png',
			'url'     => 'http://www.itthinx.com/shop/affiliates-products/',
			'index'   => 100
		),
		'affiliates-users' => array(
			'title'   => 'Affiliates Users',
			'content' => 'This extension automatically creates affiliate accounts for new users. It also allows to create affiliate accounts for all existing users.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-users.png',
			'url'     => 'http://www.itthinx.com/shop/affiliates-users/',
			'index'   => 20
		),
	);
	usort( $entries, 'affiliates_admin_add_ons_sort' );

	echo '<ul class="add-ons">';
	foreach( $entries as $key => $entry ) {
		echo '<li class="add-on">';
		echo sprintf( '<a href="%s">', $entry['url'] );
		echo '<h3>';
		echo sprintf( '<img src="%s"/>', $entry['image'] );
		echo $entry['title'];
		echo '</h3>';
		echo '<p>';
		echo $entry['content'];
		echo '</p>';
		echo '</a>';
		echo '</li>'; // .add-on
	}
	echo '</ul>'; // .add-ons

	if ( AFFILIATES_PLUGIN_NAME == 'affiliates' ) {

	echo '<h2>';
	echo __( 'Affiliates Pro', 'affiliates' );
	echo '</h2>';

	echo sprintf( '<img class="screenshot" alt="Affiliates Pro Menu" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Affiliates Pro Menu-small.png' );
	echo sprintf( '<img class="screenshot" alt="Banners" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Banners-small.png' );
	echo sprintf( '<img class="screenshot" alt="Notifications" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Notifications - Affiliate Registration-small.png' );
	echo sprintf( '<img class="screenshot" alt="Totals" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Totals-small.png' );

	echo '<ul>';

	echo '<li>';
	echo __( 'Additional and advanced integrations accessible with Affiliates Pro and Affiliates Enterprise include social sharing integrations with AddToAny and AddThis, support for affiliate commissions based on Pay Per Click (PPC), Events Manager, Formidable Forms, Formidable Pro and Gravity Forms integrations.', 'affiliates' );
	echo ' ';
	echo __( 'Please consult the Shop pages for an updated list of included integrations.', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Affiliate attributes for individual commission rates, coupons, ...', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Fixed, percentage or formula based commissions', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Extended totals report with additional filters', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Export Totals and Mass Payment File generation', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Advanced shortcodes including banners and graphs', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Banner management', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Customizable affiliate registration email', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Notifications including customizable messages', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Affiliate link generator form', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'The <a href="http://docs.itthinx.com/">Documentation</a> site also provides up-to-date information on the Affiliates, Affiliates Pro and Affiliates Enterprise plugin features.', 'affiliates' );
	echo '</li>';

	echo '</ul>';

	}

	if ( ( AFFILIATES_PLUGIN_NAME == 'affiliates' ) || ( AFFILIATES_PLUGIN_NAME == 'affiliates-pro' ) ) {

	echo '<h2>';
	echo __( 'Affiliates Enterprise', 'affiliates' );
	echo '</h2>';

	echo sprintf( '<img class="screenshot enterprise" alt="Multiple Tiers" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Multi-tiered Referrals-small.png' );
	echo sprintf( '<img class="screenshot enterprise" alt="Banners" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Specific Level Rates-small.png' );
	echo sprintf( '<img class="screenshot enterprise" alt="Notifications" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Tiers-small.png' );
	echo sprintf( '<img class="screenshot enterprise" alt="Campaigns" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/campaigns.png' );
	echo sprintf( '<img class="screenshot enterprise" alt="Campaigns Administration" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/campaigns-admin.png' );

	echo '<ul>';

	echo '<li>';
	echo __( 'Includes all additional features available in Affiliates Pro.', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Affiliate campaign management and tracking.', 'affiliates' );
	echo ' ';
	echo __( 'This allows affiliates to distinguish between income they generate by placing affiliate links on Facebook, from that generated through Twitter and other sources.', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Multi-tier capability with unlimited levels and rates.', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'Pixel Tracking makes it even easier for Affiliates to refer customers, as they do not even need to click an affiliate link. Supported methods are image and iframe tracking pixels.', 'affiliates' );
	echo '</li>';

	echo '</ul>';

	}

	echo '<h2>';
	echo __( 'Add-Ons', 'affiliates' );
	echo '</h2>';

	echo '<p>';
	echo __( 'Free and premium extensions are listed on the <a href="http://www.itthinx.com/plugins-overview/">Overview</a> page and in the <a href="http://www.itthinx.com/shop/">Shop</a>.', 'affiliates' );
	echo '</p>';

	echo '</div>';

	affiliates_footer();
}

function affiliates_admin_add_ons_sort( $e1, $e2 ) {
	$i1 = isset( $e1['index'] ) ? $e1['index'] : 0;
	$i2 = isset( $e2['index'] ) ? $e2['index'] : 0;
	$t1 = isset( $e1['title'] ) ? $e1['title'] : '';
	$t2 = isset( $e2['title'] ) ? $e2['title'] : '';

	return $i1 - $i2 + strnatcmp( $t1, $t2 );
}
