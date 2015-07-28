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

	echo '<style type="text/css">';
	echo 'div.add-ons { background-color: #fff; padding: 1em; }';
	echo 'img.screenshot { margin: 0 4px; padding: 4px; background-color: #fff; border: 1px solid #ccc; border-radius: 4px; height: 258px; }';
	echo 'img.screenshot.enterprise { height: 190px; }';
	echo '</style>';

	echo '<div class="add-ons">';

	echo '<h1>';
	echo __( 'Affiliates Extensions and Add-Ons', AFFILIATES_PLUGIN_DOMAIN );
	echo '</h1>';

	echo '<p>';
	echo __( 'Get additional features and access to premium support!', AFFILIATES_PLUGIN_DOMAIN );
	echo '</p>';

	if ( AFFILIATES_PLUGIN_NAME == 'affiliates' ) {

	echo '<h2>';
	echo __( 'Affiliates Pro', AFFILIATES_PLUGIN_DOMAIN );
	echo '</h2>';

	echo sprintf( '<img class="screenshot" alt="Affiliates Pro Menu" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Affiliates Pro Menu-small.png' );
	echo sprintf( '<img class="screenshot" alt="Banners" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Banners-small.png' );
	echo sprintf( '<img class="screenshot" alt="Notifications" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Notifications - Affiliate Registration-small.png' );
	echo sprintf( '<img class="screenshot" alt="Totals" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Totals-small.png' );

	echo '<ul>';

	echo '<li>';
	echo __( 'Additional and advanced integrations accessible with Affiliates Pro and Affiliates Enterprise include social sharing integrations with AddToAny and AddThis, support for affiliate commissions based on Pay Per Click (PPC), Events Manager, Formidable Forms, Formidable Pro and Gravity Forms integrations.', AFFILIATES_PLUGIN_DOMAIN );
	echo ' ';
	echo __( 'Please consult the Shop pages for an updated list of included integrations.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Affiliate attributes for individual commission rates, coupons, ...', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Fixed, percentage or formula based commissions', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Extended totals report with additional filters', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Export Totals and Mass Payment File generation', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Advanced shortcodes including banners and graphs', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Banner management', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Customizable affiliate registration email', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Notifications including customizable messages', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Affiliate link generator form', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'The <a href="http://docs.itthinx.com/">Documentation</a> site also provides up-to-date information on the Affiliates, Affiliates Pro and Affiliates Enterprise plugin features.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '</ul>';

	}

	if ( ( AFFILIATES_PLUGIN_NAME == 'affiliates' ) || ( AFFILIATES_PLUGIN_NAME == 'affiliates-pro' ) ) {

	echo '<h2>';
	echo __( 'Affiliates Enterprise', AFFILIATES_PLUGIN_DOMAIN );
	echo '</h2>';

	echo sprintf( '<img class="screenshot enterprise" alt="Multiple Tiers" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Multi-tiered Referrals-small.png' );
	echo sprintf( '<img class="screenshot enterprise" alt="Banners" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Specific Level Rates-small.png' );
	echo sprintf( '<img class="screenshot enterprise" alt="Notifications" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/Tiers-small.png' );
	echo sprintf( '<img class="screenshot enterprise" alt="Campaigns" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/campaigns.png' );
	echo sprintf( '<img class="screenshot enterprise" alt="Campaigns Administration" src="%s"/>', AFFILIATES_PLUGIN_URL . 'images/add-ons/campaigns-admin.png' );

	echo '<ul>';

	echo '<li>';
	echo __( 'Includes all additional features available in Affiliates Pro.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Affiliate campaign management and tracking.', AFFILIATES_PLUGIN_DOMAIN );
	echo ' ';
	echo __( 'This allows affiliates to distinguish between income they generate by placing affiliate links on Facebook, from that generated through Twitter and other sources.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Multi-tier capability with unlimited levels and rates.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Pixel Tracking makes it even easier for Affiliates to refer customers, as they do not even need to click an affiliate link. Supported methods are image and iframe tracking pixels.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '</ul>';

	}

	echo '<h2>';
	echo __( 'Add-Ons', AFFILIATES_PLUGIN_DOMAIN );
	echo '</h2>';

	echo '<p>';
	echo __( 'Free and premium extensions are listed on the <a href="http://www.itthinx.com/plugins-overview/">Overview</a> page and in the <a href="http://www.itthinx.com/shop/">Shop</a>.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</p>';

	echo '</div>';

	affiliates_footer();
}
