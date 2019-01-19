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

/**
 * @var Affiliates_Dashboard $dashboard Dashboard object available for use in the template.
 */

do_action( 'affiliates_dashboard_before' );
?>
<div class="affiliates-dashboard">
	<?php do_action( 'affiliates_dashboard_before_sections' ); ?>
	<div class="affiliates-dashboard-sections">
	<?php
	$sections = $dashboard->get_sections();

	if ( $sections !== null && count( $sections ) > 0 ) {
		$current = $dashboard->get_current_section();

		if ( $current !== null ) {
			$current_section_key = $current->get_key();
		}

		// section links
		do_action( 'affiliates_dashboard_before_section_links', $sections );
		?>
		<div class="affiliates-dashboard-section-links">
			<?php
			foreach ( $sections as $section_key => $section ) {
				?>
				<div class='section-link-item <?php echo esc_attr( $section_key . ' ' . ( $section_key === $current_section_key ? 'active' : '' ) ); ?>'>
					<a href="<?php echo esc_url( $dashboard->get_url( array( Affiliates_Dashboard::SECTION_URL_PARAMETER => $section_key ) ) ); ?>"><?php echo esc_html( $section['class']::get_name() ); ?></a>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		do_action( 'affiliates_dashboard_after_section_links', $sections );

		// current section
		do_action( 'affiliates_dashboard_before_section', $current_section_key );
		?>
		<div class='affiliates-dashboard-section <?php echo esc_attr( $current_section_key ); ?>'>
			<?php
			if ( $current !== null ) {
				$current->render();
			}
			?>
		</div>
		<?php
		do_action( 'affiliates_dashboard_after_section', $current_section_key );
	}
	?>
	</div>
	<?php do_action( 'affiliates_dashboard_after_sections' ); ?>
</div>
<?php
do_action( 'affiliates_dashboard_after' );
?>
<style type="text/css">
.affiliates-dashboard-section-links {
	display: flex;
	flex-wrap: wrap;
	text-align: center;
	background-color: #f2f2f2;
	border-radius: 4px;
	margin: 4px;
}
.affiliates-dashboard-section-links .section-link-item {
	flex-grow: 1;
	cursor: pointer;
	border-bottom: 4px solid #9e9e9e;
}
.affiliates-dashboard-section-links .section-link-item a {
	text-decoration: none;
	padding: 0 0.32em;
}
.affiliates-dashboard-section-links .section-link-item.active {
	font-weight: bold;
	background-color: #e0e0e0;
	border-radius: 2px;
	border-bottom: 4px solid #616161;
}
</style>
