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

/**
 * Renders the section.
 */
function affiliates_admin_add_ons() {

	echo '<div class="affiliates-admin-add-ons add-ons">';

	echo '<h1>';
	echo esc_html__( 'Affiliates Extensions and Add-Ons', 'affiliates' );
	echo '</h1>';

	echo '<p>';
	echo esc_html__( 'Get additional features and access to premium support!', 'affiliates' );
	echo '</p>';

	affiliates_donate();

	echo '<p>';
	printf(
		__( 'Please also refer to the available <a href="%s">Integrations</a>.', 'affiliates' ),
		esc_url( add_query_arg( 'section', 'integrations', admin_url( 'admin.php?page=affiliates-admin-settings' ) ) )
	);
	echo '</p>';

	echo '<h2 class="add-ons-sub-title">';
	echo __( 'Recommended Tools', 'affiliates' );
	echo '</h2>';

	$entries = array(
		'woocommerce-product-search' => array(
			'title'    => 'WooCommerce Product Search',
			'content'  => esc_html__( 'The essential extension for every WooCommerce store! The perfect Search Engine for your store helps your customers to find and buy the right products quickly.', 'affiliates' ),
			'image'    => AFFILIATES_PLUGIN_URL . 'images/add-ons/woocommerce-product-search.png',
			'url'      => 'https://woocommerce.com/products/woocommerce-product-search/',
			'featured' => true,
			'index'    => 10
		),
		'groups-woocommerce' => array(
			'title'    => 'Group Memberships for WooCommerce',
			'content'  => esc_html__( 'Sell Memberships with Groups and WooCommerce! Groups WooCommerce grants memberships based on products. It automatically assigns a customer to one or more groups based on the products ordered.', 'affiliates' ),
			'image'    => AFFILIATES_PLUGIN_URL . 'images/add-ons/groups-woocommerce.png',
			'url'      => 'https://woocommerce.com/products/groups-woocommerce/',
			'featured' => true,
			'index'    => 20
		),
		'restrict-payment-methods' => array(
			'title'    => 'Restrict Payment Methods for WooCommerce',
			'content'  => esc_html__( 'Limit the use of Payment Methods by Group Memberships, Roles, Countries, and Order Amounts. ', 'affiliates' ),
			'image'    => AFFILIATES_PLUGIN_URL . 'images/add-ons/restrict-payment-methods.png',
			'url'      => 'https://woocommerce.com/products/restrict-payment-methods/',
			'featured' => true,
			'index'    => 30
		),
		'woocommerce-group-coupons' => array(
			'title'    => 'Group Coupons for WooCommerce',
			'content'  => esc_html__( 'Offer exclusive, automatic and targeted coupon discounts for your customers! Use group memberships and roles to control the validity of coupons.', 'affiliates' ),
			'image'    => AFFILIATES_PLUGIN_URL . 'images/add-ons/woocommerce-group-coupons.png',
			'url'      => 'https://woocommerce.com/products/group-coupons/',
			'featured' => false,
			'index'    => 40
		),
		'woocommerce-sales-analysis' => array(
			'title'    => 'Sales Analysis for WooCommerce',
			'content'  => esc_html__( 'Sales Analysis oriented at Marketing & Management. Get in-depth views on fundamental Business Intelligence, focused on Sales and net Revenue Trends, International Sales Reports, Product Market and Customer Trends.', 'affiliates' ),
			'url'      => 'https://woocommerce.com/products/sales-analysis-for-woocommerce/',
			'image'    => AFFILIATES_PLUGIN_URL . 'images/add-ons/woocommerce-sales-analysis.png',
			'featured' => false,
			'index'    => 50
		),
		'volume-discount-coupons' => array(
			'title'    => 'Volume Discount Coupons for WooCommerce',
			'content'  => esc_html__( 'Provides automatic discounts and coupons based on the quantities of products in the cart.', 'affiliates' ),
			'image'    => AFFILIATES_PLUGIN_URL . 'images/add-ons/volume-discount-coupons.png',
			'url'      => 'https://woocommerce.com/products/volume-discount-coupons/',
			'featured' => false,
			'index'    => 60
		),
		'groups' => array(
			'title'   => 'Groups',
			'content' => esc_html__( 'Groups is designed as an efficient, powerful and flexible solution for group-oriented memberships and content access control. Use it to control who can view documents and more.', 'affiliates' ),
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/groups.png',
			'url'     => 'https://wordpress.org/plugins/groups/',
			'index'   => 100
		),
		'groups-drip-content' => array(
			'title'   => 'Groups Drip Content',
			'content' => esc_html__( 'This extension for WordPress is used to release content on a schedule. It can be used with the popular Groups membership solution or without it. Content dripping can be based on user account creation, group memberships and specific dates and times.', 'affiliates' ),
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/groups-drip-content.png',
			'url'     => 'https://www.itthinx.com/shop/groups-drip-content/',
			'index'   => 100
		),
		'groups-file-access' => array(
			'title'   => 'Groups File Access',
			'content' => esc_html__( 'Provide exclusive access to files for members. The ideal companion to provide exclusive access to resources for group members.', 'affiliates' ),
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/groups-file-access.png',
			'url'     => 'https://www.itthinx.com/shop/groups-file-access/',
			'index'   => 100
		),
		'groups-newsletters' => array(
			'title'   => 'Groups Newsletters',
			'content' => esc_html__( 'Newsletter Campaigns for Subscribers and Groups. Groups Newsletters helps you to communicate efficiently, providing targeted information to groups of recipients through automated campaigns.', 'affiliates' ),
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/groups-newsletters.png',
			'url'     => 'https://www.itthinx.com/shop/groups-newsletters/',
			'index'   => 100
		),
		'groups-restrict-categories' => array(
			'title'   => 'Groups Restrict Categories',
			'content' => esc_html__( 'An extension based on Groups, provides access restrictions for categories and tags, custom post types and taxonomies. Very useful to restrict whole sets of documents based on their document categories or tags.', 'affiliates' ),
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/groups-restrict-categories.png',
			'url'     => 'https://www.itthinx.com/shop/groups-restrict-categories/',
			'index'   => 100
		)
	);
	usort( $entries, 'affiliates_admin_add_ons_sort' );

	echo '<ul class="add-ons">';
	foreach( $entries as $key => $entry ) {
		echo '<li class="add-on">';
		echo sprintf( '<a href="%s" target="_blank">', $entry['url'] );
		echo '<h3 class="add-ons-sub-sub-title">';
		echo sprintf( '<img src="%s"/>', $entry['image'] );
		echo '<span class="title">';
		echo $entry['title'];
		echo '</span>';
		echo '</h3>';
		echo '<p>';
		echo $entry['content'];
		echo '</p>';
		echo '</a>';
		echo '</li>'; // .add-on
	}
	echo '</ul>'; // .add-ons

	echo '<h2>';
	echo esc_html__( 'Recommended Extensions', 'affiliates' );
	echo '</h2>';

	$entries = array(
		'affiliates-pro' => array(
			'title'   => 'Affiliates Pro',
			'content' => 'Affiliates Pro is a powerful affiliate marketing and management system for sellers, shops and developers, who want to increase sales and foster growth with their own affiliate program.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-pro.png',
			'url'     => 'https://www.itthinx.com/shop/affiliates-pro/',
			'index'   => 10
		),
		'affiliates-enterprise' => array(
			'title'   => 'Affiliates Enterprise',
			'content' => 'Affiliates Enterprise is a powerful affiliate marketing and management system for active marketers, sellers, shops and developers. This growth-oriented business solution features affiliate campaigns, mulitple tiers and pixel tracking, in addition to all the powerful features included in Affiliates Pro.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-enterprise.png',
			'url'     => 'https://www.itthinx.com/shop/affiliates-enterprise/',
			'index'   => 10
		),
		'affiliates-import' => array(
			'title'   => 'Affiliates Import <span style="color:#f00">FREE</span>',
			'content' => 'This extension allows to import affiliate accounts from a text file into the affiliate system.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-import.png',
			'url'     => 'https://wordpress.org/plugins/affiliates-import/',
			'index'   => 20
		),
		'affiliates-buddypress' => array(
			'title'   => 'Affiliates BuddyPress <span style="color:#f00">FREE</span>',
			'content' => 'This integration with BuddyPress helps to display affiliate content in the BuddyPress user profile.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-buddypress.png',
			'url'     => 'https://wordpress.org/plugins/affiliates-buddypress/',
			'index'   => 20
		),
		'affiliates-recaptcha' => array(
			'title'   => 'Affiliates reCAPTCHA <span style="color:#f00">FREE</span>',
			'content' => 'This extension integrates with Google\'s reCAPTCHA service for the affiliate registration form.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-recaptcha.png',
			'url'     => 'https://wordpress.org/plugins/affiliates-recaptcha/',
			'index'   => 20
		),
		'affiliates-by-username' => array(
			'title'   => 'Affiliates by Username',
			'content' => 'This extension allows affiliate links to indicate usernames in addition to the affiliate IDs used normally.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-by-username.png',
			'url'     => 'https://www.itthinx.com/shop/affiliates-by-username/',
			'index'   => 20
		),
		'affiliates-coupons' => array(
			'title'   => 'Affiliates Coupons',
			'content' => 'This extension requires Affiliates Pro or Affiliates Enterprise and WooCommerce. It allows to create coupons for affiliates automatically and in bulk.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-coupons.png',
			'url'     => 'https://www.itthinx.com/shop/affiliates-coupons/',
			'index'   => 100
		),
		'affiliates-ms' => array(
			'title'   => 'Affiliates MS',
			'content' => 'Affiliates MS is a solution to maintain a centralized affiliate program for a WordPress Network of sites.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-ms.png',
			'url'     => 'https://www.itthinx.com/shop/affiliates-ms/',
			'index'   => 100
		),
		'affiliates-permanent' => array(
			'title'   => 'Affiliates Permanent',
			'content' => 'New customers (or new users) are assigned to the referring affiliate. The affiliate will be credited with a referral on every purchase made by the customer from thereon. Assignments can be changed manually in user profiles.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-permanent.png',
			'url'     => 'https://www.itthinx.com/shop/affiliates-permanent/',
			'index'   => 100
		),
		'affiliates-products' => array(
			'title'   => 'Affiliates Products',
			'content' => 'This extension requires WooCommerce and provides product commissions for distribution and vendors. It automatically grants commissions on product sales to assigned partners or affiliates. It is suitable to share revenue on every sale of one or more products.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-products.png',
			'url'     => 'https://www.itthinx.com/shop/affiliates-products/',
			'index'   => 100
		),
		'affiliates-users' => array(
			'title'   => 'Affiliates Users',
			'content' => 'This extension automatically creates affiliate accounts for new users. It also allows to create affiliate accounts for all existing users.',
			'image'   => AFFILIATES_PLUGIN_URL . 'images/add-ons/affiliates-users.png',
			'url'     => 'https://www.itthinx.com/shop/affiliates-users/',
			'index'   => 20
		),
	);
	uasort( $entries, 'affiliates_admin_add_ons_sort' );

	echo '<ul class="add-ons">';
	foreach( $entries as $key => $entry ) {
		echo sprintf( '<li class="add-on %s">', esc_attr( $key ) );
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
	echo esc_html__( 'Affiliates Pro', 'affiliates' );
	echo '</h2>';

	echo '<ul class="feature-listing">';

	echo '<li>';
	echo esc_html__( 'Advanced and additional integrations are provided with Affiliates Pro and Affiliates Enterprise.', 'affiliates' );
	echo ' ';
	echo esc_html__( 'Please refer to the Shop pages for included integrations.', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Affiliate attributes for individual commission rates, coupons, ...', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Fixed, percentage or formula based commissions', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Extended totals report with additional filters', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Export Totals and Mass Payment File generation', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Advanced shortcodes including banners and graphs', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Banner management', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Customizable affiliate registration email', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Notifications including customizable messages', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Affiliate link generator form', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo __( 'The <a href="https://docs.itthinx.com/">Documentation</a> site also provides up-to-date information on the Affiliates, Affiliates Pro and Affiliates Enterprise plugin features.', 'affiliates' );
	echo '</li>';

	echo '</ul>';

	}

	if ( ( AFFILIATES_PLUGIN_NAME == 'affiliates' ) || ( AFFILIATES_PLUGIN_NAME == 'affiliates-pro' ) ) {

	echo '<h2>';
	echo esc_html__( 'Affiliates Enterprise', 'affiliates' );
	echo '</h2>';

	echo '<ul class="feature-listing">';

	echo '<li>';
	echo esc_html__( 'Includes all additional features available in Affiliates Pro.', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Affiliate campaign management and tracking.', 'affiliates' );
	echo ' ';
	echo esc_html__( 'This allows affiliates to distinguish between income they generate by placing affiliate links on Facebook, from that generated through Twitter and other sources.', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Multi-tier capability with unlimited levels and rates.', 'affiliates' );
	echo '</li>';

	echo '<li>';
	echo esc_html__( 'Pixel Tracking makes it even easier for Affiliates to refer customers, as they do not even need to click an affiliate link. Supported methods are image and iframe tracking pixels.', 'affiliates' );
	echo '</li>';

	echo '</ul>';

	}

	echo '<h2>';
	echo esc_html__( 'Add-Ons', 'affiliates' );
	echo '</h2>';

	echo '<p>';
	echo __( 'Free and premium extensions are listed on the <a href="https://www.itthinx.com/plugins-overview/">Overview</a> page and in the <a href="https://www.itthinx.com/shop/">Shop</a>.', 'affiliates' );
	echo '</p>';

	echo '</div>';

	affiliates_footer();
}

/**
 * Custom sorting function.
 *
 * @param array $e1 first element
 * @param array $e2 second element
 *
 * @return int
 */
function affiliates_admin_add_ons_sort( $e1, $e2 ) {
	$i1 = isset( $e1['index'] ) ? $e1['index'] : 0;
	$i2 = isset( $e2['index'] ) ? $e2['index'] : 0;
	$t1 = isset( $e1['title'] ) ? $e1['title'] : '';
	$t2 = isset( $e2['title'] ) ? $e2['title'] : '';

	return $i1 - $i2 + strnatcmp( $t1, $t2 );
}
