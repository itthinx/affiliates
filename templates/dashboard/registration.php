<?php
/**
 * registration.php
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
 *   mytheme/affiliates/dashboard/registration.php
 *
 * It is highly recommended to use a child theme for such customizations.
 * Child themes are suitable to keep things up-to-date when the parent
 * theme is updated, while any customizations in the child theme are kept.
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var Affiliates_Dashboard_Registration $section Section object available for use in the template.
 */
?>
<?php if ( !affiliates_user_is_affiliate() ) : ?>
	<h2><?php esc_html_e( 'Registration', 'affiliates' ); ?></h2>
	<div class="dashboard-section dashboard-section-registration">
	<?php if ( empty( $_POST['affiliates-registration-submit'] ) && !affiliates_user_is_affiliate_status( null, 'pending' ) ) : ?>
		<p>
			<?php esc_html_e( 'If you are not an affiliate, you can join the affiliate program here:', 'affiliates' ); ?>
		</p>
	<?php endif; ?>
		<?php echo Affiliates_Registration::render_form( array( 'show_login' => $section->get_show_login(), 'login_url' => $section->get_login_url() ) ); ?>
	</div><?php // .dashboard-section-registration ?>
<?php endif; ?>
<style type="text/css">
.dashboard-section-registration {
	margin: 4px;
}
</style>
