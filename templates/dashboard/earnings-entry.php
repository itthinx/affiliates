<?php
/**
 * earnings-entry.php
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
 *   mytheme/affiliates/dashboard/earnings-entry.php
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
 * @var array $entry Current earnings entry data available for use in the template.
 * @var int $index Index number of the current earnings entry: 0, 1, 2, ... < $section->get_per_page()
 */

$columns = $section->get_columns();
?>
<?php foreach ( $columns as $key => $column ) : ?>
	<div class="cell <?php echo ( $index === 0 ? 'first' : '' ) . ' ' . ( ( $index + 1 ) % 2 === 0 ? 'even' : 'odd' ); ?> <?php echo esc_attr( $key ); ?>" data-heading="<?php echo esc_attr( $column['title'] ); ?>">
		<?php
		switch ( $key ) {
			case 'period' :
				echo esc_html( date_i18n( _x( 'F Y', 'earnings period year and month', 'affiliates' ), strtotime( $entry->year . '-' . $entry->month . '-01' ) ) ); // translators: date format; month and year for earnings display
				break;
			case 'earnings' :
				$display_amount = sprintf( '%.' . affiliates_get_referral_amount_decimals( 'display' ) . 'f', $entry->total );
				echo esc_html( $entry->currency_id ) . ' ' . esc_html( $display_amount );
				break;
			case 'paid' :
				$display_amount = sprintf( '%.' . affiliates_get_referral_amount_decimals( 'display' ) . 'f', $entry->total_closed );
				echo esc_html( $entry->currency_id ) . ' ' . esc_html( $display_amount );
				break;
			default :
				echo '';
		}
		?>
	</div>
<?php endforeach;
