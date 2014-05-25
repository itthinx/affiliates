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

	echo '<h2>';
	echo __( 'Affiliates Enterprise', AFFILIATES_PLUGIN_DOMAIN );
	echo '</h2>';
}

