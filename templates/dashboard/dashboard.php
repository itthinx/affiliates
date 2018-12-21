<?php
/**
 * dashboard.php
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
 *   mytheme/affiliates/dashboard/dashboard.php
 *
 * It is highly recommended to use a child theme for such customizations.
 * Child themes are suitable to keep things up-to-date when the parent
 * theme is updated, while any customizations in the child theme are kept.
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

global $affiliates_dashboard;

do_action( 'affiliates_dashboard_before' );
?>
<div class="affiliates-dashboard">
	<div class="affiliates-dashboard-sections">
	<?php
	$sections = $affiliates_dashboard->get_sections();
	if ( $sections !== null && count( $sections ) > 0 ) {
		do_action( 'affiliates_dashboard_before_sections' );
		foreach ( $sections as $section_key => $section ) {
			do_action( 'affiliates_dashboard_before_section', $section_key );
			?>
			<div class='affiliates-dashboard-section <?php esc_attr( $section_key ); ?>'>
				<?php $section->render(); ?>
			</div>
			<?php
			do_action( 'affiliates_dashboard_after_section', $section_key );
		}
		do_action( 'affiliates_dashboard_after_sections' );
	}
	?>
	</div>
</div>
<?php
do_action( 'affiliates_dashboard_after' );
