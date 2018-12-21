<?php
/**
 * overview.php
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
 *   mytheme/affiliates/dashboard/overview.php
 *
 * It is highly recommended to use a child theme for such customizations.
 * Child themes are suitable to keep things up-to-date when the parent
 * theme is updated, while any customizations in the child theme are kept.
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

global $affiliates_dashboard_section;

$totals = $affiliates_dashboard_section->get_totals();

$hits = isset( $totals['hits'] ) ? intval( $totals['hits'] ) : 0;
$visits = isset( $totals['visits'] ) ? intval( $totals['visits'] ) : 0;
$referrals = isset( $totals['referrals'] ) ? intval( $totals['referrals'] ) : 0;
$amounts = array();
if ( isset( $totals['amounts_by_currency'] ) ) {
	foreach ( $totals['amounts_by_currency'] as $currency_id => $amount ) {
		$amounts[$currency_id] = round( $amount, 2, PHP_ROUND_HALF_UP );
	}
}
?>

<div class="dashboard-section dashboard-section-overview" style="display:grid">

<div class="stats-container" style="display:flex">
	<div class="stats-item" style="flex-grow:1">
		<div class="stats-item-heading"><?php _e( 'Recent Visits', 'affiliates' ); ?></div>
		<div class="stats-item-value"><?php echo $hits; ?></div>
	</div>
	<div class="stats-item" style="flex-grow:1">
		<div class="stats-item-heading"><?php _e( 'Recent Referrals', 'affiliates' ); ?></div>
		<div class="stats-item-value"><?php echo $visits; ?></div>
	</div>
	<div class="stats-item" style="flex-grow:1">
		<div class="stats-item-heading"><?php _e( 'Recent Earnings', 'affiliates' )?></div>
		<?php foreach ( $amounts as $currency_id => $amount ) { ?>
			<div class="stats-item-value">
				<span class="stats-item-currency"><?php echo $currency_id; ?> <span class="stats-item-amount"><?php echo $amount; ?></span>
			</div>
		<?php } ?>
	</div>
</div>

<div id="affiliates-dashboard-overview-graph" class="graph" style="width:100%; height: 400px;"></div>
<div id="affiliates-dashboard-overview-legend" class="legend"></div>

<style type="text/css">
.dashboard-section .stats-container {
	margin: 0;
}
.dashboard-section .stats-item {
	background-color: #f2f2f2;
	border-radius: 4px;
	margin: 4px;
	padding: 4px;
	text-align: center;
	font-size: 16px;
}
.dashboard-section .stats-item .stats-item-heading {
	font-weight: bold;
}
.dashboard-section .stats-item .stats-item-value {
	font-size: 24px;
}
.dashboard-section .graph {
	background-color: #fafafa;
	border-radius: 4px;
	margin: 4px;
}
.dashboard-section .legend {
	display: flex;
	text-align: center;
	background-color: #f2f2f2;
	border-radius: 4px;
	margin: 4px;
}
.dashboard-section .legend-item {
flex-grow:1;
}
.dashboard-section .legend-item.active {
	background-color: #e0e0e0;
	border-radius: 2px;
}
.dashboard-section .legend-item-label {
	font-size: 14px;
	display:inline-block;
	vertical-align:middle;
	padding: 4px;
}
.dashboard-section .legend-item-color {
	width: 16px;
	height: 16px;
	display:inline-block;
	vertical-align:middle;
}
</style>
<div>
<p><?php _e( 'Your affiliate URL:', 'affiliates' ); ?></p>
<p>
<code>
<?php echo Affiliates_Shortcodes::affiliates_url( array() ); ?>
</code>
</p>
</div>

</div>
