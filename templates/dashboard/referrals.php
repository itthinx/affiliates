<?php
/**
 * referrals.php
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
 *   mytheme/affiliates/dashboard/referrals.php
 *
 * It is highly recommended to use a child theme for such customizations.
 * Child themes are suitable to keep things up-to-date when the parent
 * theme is updated, while any customizations in the child theme are kept.
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

global $affiliates_dashboard_section;

?>

<h3><?php _e( 'Total Earnings', 'affiliates' ); ?></h3>

<p><?php _e( 'Commissions pending payment', 'affiliates' ); ?></p>
<p><?php echo Affiliates_Shortcodes::affiliates_referrals( array( 'show' => 'total', 'status' => 'accepted' ) ); ?></p>

<p><?php _e( 'Commissions paid', 'affiliates' ); ?></p>
<p><?php echo Affiliates_Shortcodes::affiliates_referrals( array( 'show' => 'total', 'status' => 'closed' ) ); ?></p>

<h3><?php _e( 'Number of sales referred', 'affiliates' ); ?></h3>

<p>
<?php
	_e( 'Accepted referrals pending payment:', 'affiliates' );
	echo ' ';
	echo Affiliates_Shortcodes::affiliates_referrals( array( 'status' => 'accepted' ) );
?>
</p>
<p>
<?php
	_e( 'Referrals paid:', 'affiliates' );
	echo ' ';
	echo Affiliates_Shortcodes::affiliates_referrals( array( 'status' => 'closed' ) );
?>
</p>
