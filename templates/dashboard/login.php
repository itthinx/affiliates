<?php
/**
 * login.php
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
 *
 * This is a template file. You can customize it by copying it
 * into the appropriate subfolder of your theme:
 *
 *   mytheme/affiliates/dashboard/login.php
 *
 * It is highly recommended to use a child theme for such customizations.
 * Child themes are suitable to keep things up-to-date when the parent
 * theme is updated, while any customizations in the child theme are kept.
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var Affiliates_Dashboard_Login $section Section object available for use in the template.
 */

if ( !is_user_logged_in() ) {
	?>
	<h2><?php esc_html_e( 'Login', 'affiliates' ); ?></h2>
	<p><?php esc_html_e( 'Please log in to access the affiliate area.', 'affiliates' ); ?></p>
	<?php
	echo Affiliates_Shortcodes::affiliates_login_redirect( array() );
}
?>
<style type="text/css">
	.affiliates-dashboard .affiliates-dashboard-section .login-username label,
	.affiliates-dashboard .affiliates-dashboard-section .login-password label {
		display: block;
	}
	.affiliates-dashboard .affiliates-dashboard-section .login-username input,
	.affiliates-dashboard .affiliates-dashboard-section .login-password input {
		max-width: 100%;
		width: 320px;
	}
</style>
