<?php
/**
 * earnings.php
 *
 * Copyright (c) 2010 - 2019 "kento" Karim Rahimpur www.itthinx.com
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
 *   mytheme/affiliates/dashboard/earnings.php
 *
 * It is highly recommended to use a child theme for such customizations.
 * Child themes are suitable to keep things up-to-date when the parent
 * theme is updated, while any customizations in the child theme are kept.
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var Affiliates_Dashboard_Earnings $section Section object available for use in the template.
 */
?>
<h2><?php esc_html_e( 'Earnings', 'affiliates' ); ?></h2>
<?php
	//
	// Render the earnings filter form
	//
?>
<div class="dashboard-section dashboard-section-earnings">
	<form id="setfilters" class="filters capsule-container" action="" method="post">
		<div class="capsule half left">
			<label for="from_date" class="from-date-filter"><?php _e( 'From', 'affiliates' ); ?></label>
			<input class="datefield from-date-filter" name="from_date" type="date" value="<?php echo esc_attr( $section->get_from_date() ); ?>"/>
		</div>
		<div class="capsule half right">
			<label for="thru_date" class="thru-date-filter"><?php esc_html_e( 'Until', 'affiliates' ); ?></label>
			<input class="datefield thru-date-filter" name="thru_date" type="date" class="datefield" value="<?php echo esc_attr( $section->get_thru_date() ); ?>"/>
		</div>
		<div class="filter-buttons">
			<input class="button apply-button" type="submit" name="apply_filters" value="<?php esc_html_e( 'Apply', 'affiliates' ); ?>"/>
			<input class="button clear-button" type="submit" name="clear_filters" value="<?php esc_html_e( 'Clear', 'affiliates' ); ?>"/>
		</div>
	</form>
	<?php
		//
		// Filter styles
		//
	?>
	<style type="text/css">
	.dashboard-section form.filters {
		background-color: #f2f2f2;
		border-radius: 4px;
		margin: 4px;
		padding: 4px;
	}
	.dashboard-section .capsule-container {
		width: 100%;
		display: grid;
		grid-template-columns: repeat(auto-fill, 25%);
	}
	.dashboard-section .capsule-container .capsule.half.left {
		grid-column: 1 / 3;
	}
	.dashboard-section .capsule-container .capsule.half.right {
		grid-column: 3 / 5;
	}
	.dashboard-section .capsule-container .capsule.full {
		grid-column: 1 / 5;
	}
	.dashboard-section .capsule-container .capsule {
		display: flex;
		padding: 4px;
		margin: 4px;
		align-items: center;
	}
	.dashboard-section .capsule-container .capsule label {
		padding: 0 4px;
	}
	.dashboard-section .capsule-container .capsule input {
		flex: 1;
		overflow: hidden;
	}
	.dashboard-section .filters .filter-buttons {
		display: flex;
		margin: 4px;
	}
	.dashboard-section .filters .filter-buttons input {
		flex: 1;
		margin: 4px;
	}
	</style>
<?php
	//
	// Render the earnings section
	//
?>
<div class="earnings-container">
	<?php $primary_columns = 0; ?>
	<?php foreach ( $section->get_columns() as $key => $column ) : ?>
		<?php
		$primary_columns++;
		$order_options = array(
			'orderby' => $key,
			'order' => $section->get_switch_sort_order()
		);
		$class = '';
		$arrow = '';
		if ( strcmp( $key, $section->get_orderby() ) == 0 ) {
			$lorder = strtolower( $section->get_sort_order() );
			$class = "$key manage-column sorted $lorder";
			switch( $lorder ) {
				case 'asc' :
					$arrow = ' &uarr;';
					break;
				case 'desc' :
					$arrow = ' &darr;';
					break;
			}
		} else {
			$class = "$key manage-column sortable";
		}
		$link = esc_url( add_query_arg( $order_options, $section->get_current_url() ) );
		?>
		<div class="cell heading <?php echo esc_attr( $class ); ?>">
			<?php if ( $key === 'period' ) :?>
			<a href="<?php echo $link; ?>" title="<?php echo esc_html( $column['description'] ); ?>">
				<span><?php echo esc_html( $column['title'] ); ?></span><span class="sorting-indicator"><?php echo $arrow; ?></span>
			</a>
			<?php else : ?>
				<span><?php echo esc_html( $column['title'] ); ?></span>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
	<?php // Render the entries ?>
	<?php if ( $section->get_count() > 0 ) :
		$i = 0;
		foreach ( $section->get_entries() as $entry ) :
			Affiliates_Templates::include_template(
				'dashboard/earnings-entry.php',
				array(
					'section' => $section,
					'entry'   => $entry,
					'index'   => $i
				)
			);
			$i++;
		endforeach;
	?>
	<?php else : ?>
		<div class="cell odd full"><?php esc_html_e( 'There are no results.', 'affiliates' ); ?><div>
	<?php endif; ?>

</div><?php // .earnings-container ?>
<?php
	//
	// Render the section navigation
	//
?>
<?php if ( $section->get_count() > 0 ) : ?>
	<div class="section-navigation">
		<?php if ( $section->get_current_page() > 0 ) : ?>
			<a style="margin: 4px;" class="button" href="<?php echo esc_url( $section->get_url( array( 'earnings-page' => $section->get_current_page() - 1 ) ) ); ?>"><?php echo esc_html_x( 'Previous', 'Label used to show previous page of affiliate earnings results', 'affiliates' ); ?></a>
		<?php endif; ?>
		<?php if ( $section->get_current_page() < $section->get_pages() - 1 ) : ?>
			<a style="margin: 4px;" class="button" href="<?php echo esc_url( $section->get_url( array( 'earnings-page' => $section->get_current_page() + 1 ) ) ); ?>"><?php echo esc_html_x( 'Next', 'Label used to show next page of affiliate earnings results', 'affiliates' ); ?></a>
		<?php endif; ?>
	</div>
	<div class="section-navigation-options">
		<form action="<?php echo esc_url( $section->get_url( array( 'per_page' => null ) ) ); ?>" method="post">
			<label class="row-count">
				<?php esc_html_e( 'Results per page', 'affiliates' ); ?>
				<input class="per-page" name="per_page" type="text" value="<?php echo esc_attr( $section->get_per_page() ); ?>" />
				<input class="button" type="submit" value="<?php esc_attr_e( 'Apply', 'affiliates' ); ?>"/>
			</label>
		</form>
	</div>
<?php endif; ?>
</div><?php // .affiliates-earnings ?>
<?php
	//
	// Section styles
	//
?>
<style type="text/css">
.dashboard-section .earnings-container {
	width: 100%;
	display: grid;
	grid-template-columns: 40% 30% 30%;
	margin: 4px;
}
.dashboard-section .earnings-container .cell {
	word-break: break-all;
	padding: 4px;
	background-color: #f0f0f0;
	padding: 4px;
}
.dashboard-section .earnings-container .cell.full {
	grid-column: 1 / -1;
}
.dashboard-section .earnings-container .period {
	grid-column: 1 / 2;
	word-break: break-word;
}
.dashboard-section .earnings-container .earnings {
	grid-column: 2 / 3;
}
.dashboard-section .earnings-container .earnings:not(.heading) {
	text-align: right;
}
.dashboard-section .earnings-container .paid {
	grid-column: 3 / 4;
}
.dashboard-section .earnings-container .paid:not(.heading) {
	text-align: right;
}

.dashboard-section .earnings-container .heading {
	background-color: #ffffff;
	color: 171717;
	font-weight: bold;
	word-break: break-word;
	border-bottom: 4px solid #9e9e9e;
}
.dashboard-section .earnings-container .odd {
	background-color: #ffffff;
	color: #252525;
}
.dashboard-section .earnings-container .even {
	background-color: #e0e0e0;
	color: #171717;
}
.dashboard-section .section-navigation-options {
	margin: 4px;
}
.dashboard-section .section-navigation-options input.per-page {
	width: 4em;
}
@media only screen and (max-width: 768px) {
	.dashboard-section .earnings-container .heading {
		border: none;
	}
	.dashboard-section .earnings-container div.cell:nth-child(4) {
		border-bottom: 4px solid #9e9e9e;
	}
	.dashboard-section .earnings-container {
		grid-template-columns: 100%;
	}
	.dashboard-section .earnings-container .period {
		grid-column: 1;
		word-break: break-word;
	}
	.dashboard-section .earnings-container .earnings {
		grid-column: 1;
	}
	.dashboard-section .earnings-container .earnings:not(.heading) {
		text-align: initial;
	}
	.dashboard-section .earnings-container .paid {
		grid-column: 1;
	}
	.dashboard-section .earnings-container .paid:not(.heading) {
		text-align: initial;
	}
	.dashboard-section .earnings-container .heading {
		font-size: small;
	}
	.dashboard-section .earnings-container .cell::before {
		display: block;
		font-size: smaller;
		font-weight: bolder;
		content: attr(data-heading);
	}
}
</style>
