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

	echo '<h1>';
	echo __( 'Affiliates Extensions and Add-Ons', AFFILIATES_PLUGIN_DOMAIN );
	echo '</h1>';

	echo '<h2>';
	echo __( 'Affiliates Pro', AFFILIATES_PLUGIN_DOMAIN );
	echo '</h2>';

	echo '<ul>';

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
	echo __( 'Mass payment file generation', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Advanced shortcodes including graphs', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Banner management', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Notifications including customizable messages', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Access to integrations with popular e-commerce systems', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '</ul>';

	echo '<h2>';
	echo __( 'Affiliates Enterprise', AFFILIATES_PLUGIN_DOMAIN );
	echo '</h2>';

	echo '<ul>';

	echo '<li>';
	echo __( 'Includes all additional features available in Affiliates Pro', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '<li>';
	echo __( 'Multi-tier capability with unlimited levels and rates', AFFILIATES_PLUGIN_DOMAIN );
	echo '</li>';

	echo '</ul>';

	echo '<h2>';
	echo __( 'Add-Ons', AFFILIATES_PLUGIN_DOMAIN );
	echo '</h2>';

	echo '<p>';
	echo __( 'Free and premium extensions are listed on the <a href="http://www.itthinx.com/plugins/overview/">Overview</a> page and in the <a href="http://www.itthinx.com/shop/">Shop</a>.', AFFILIATES_PLUGIN_DOMAIN );
	echo '</p>';
}

