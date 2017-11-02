<?php
/**
 * class-affiliates-settings-integrations.php
 * 
 * Copyright (c) 2010 - 2015 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 2.8.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integration section.
 */
class Affiliates_Settings_Integrations extends Affiliates_Settings {

	private static $integrations = null;

	public static function init() {
		self::$integrations =  array(
			'affiliates-woocommerce-light' => array(
				'title'        => __( 'WooCommerce (light)', 'affiliates' ),
				'plugin_title' => __( 'Affiliates WooCommerce Integration Light', 'affiliates' ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-woocommerce-light/',
				'description'  => __( 'This plugin integrates <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a> with WooCommerce. With this integration plugin, referrals are created automatically for your affiliates when sales are made.', 'affiliates' ),
				'plugin_file'  => 'affiliates-woocommerce-light/affiliates-woocommerce-light.php',
				'notes'        => __( 'This light integration is suitable to be used with the <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a> plugin.', 'affiliates' ),
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates' ),
				'platforms'    => array( 'woocommerce' )
			),
			'affiliates-contact-form-7' => array(
				'title'        => __( 'Contact Form 7', 'affiliates' ),
				'plugin_title' => __( 'Affiliates Contact Form 7 Integration', 'affiliates' ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-contact-form-7/',
				'description'  => __( 'This plugin integrates <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a>, <a href="http://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a> and <a href="http://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a> with Contact Form 7. This integration stores data from submitted forms and tracks form submissions to the referring affiliate.', 'affiliates' ),
				'plugin_file'  => 'affiliates-contact-form-7/affiliates-contact-form-7.php',
				'notes'        => '',
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates', 'affiliates-pro', 'affiliates-enterprise' ),
				'platforms'    => array( 'contact-form-7' )
			),
			'affiliates-ninja-forms' => array(
				'title'        => __( 'Ninja Forms', 'affiliates' ),
				'plugin_title' => __( 'Affiliates Ninja Forms Integration', 'affiliates' ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-ninja-forms/',
				'description'  => __( 'This plugin integrates <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a>, <a href="http://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a> and <a href="http://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a> with Ninja Forms. Affiliates can sign up through forms handled with Ninja Forms. Form submissions that are referred through affiliates, can grant commissions to affiliates and record referral details.', 'affiliates' ),
				'plugin_file'  => 'affiliates-ninja-forms/affiliates-ninja-forms.php',
				'notes'        => '',
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates', 'affiliates-pro', 'affiliates-enterprise' ),
				'platforms'    => array( 'ninja-forms' )
			),
			'affiliates-jigoshop-light' => array(
				'title'        => __( 'Jigoshop (light)', 'affiliates' ),
				'plugin_title' => __( 'Affiliates Jigoshop Integration Light', 'affiliates' ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-jigoshop-light/',
				'description'  => __( 'This plugin integrates <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a> with Jigoshop. With this integration plugin, referrals are created automatically for your affiliates when sales are made.', 'affiliates' ),
				'plugin_file'  => 'affiliates-jigoshop-light/affiliates-jigoshop-light.php',
				'notes'        => __( 'This light integration is suitable to be used with the <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a> plugin.', 'affiliates' ),
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates' ),
				'platforms'    => array( 'jigoshop' )
			),
			'affiliates-wp-e-commerce' => array(
				'title'       => __( 'WP e-Commerce', 'affiliates' ),
				'plugin_title' => __( 'Affiliates WP e-Commerce Integration', 'affiliates' ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-wp-e-commerce/',
				'description' => __( 'This plugin integrates <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a>, <a href="http://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a> and <a href="http://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a> with WP e-Commerce. With this integration plugin, referrals are created automatically for your affiliates when sales are made.', 'affiliates' ),
				'plugin_file' => 'affiliates-wp-e-commerce/affiliates-wp-e-commerce.php',
				'notes'        => '',
				'repository' => 'wordpress',
				'access'        => 'free',
				'targets'      => array( 'affiliates', 'affiliates-pro', 'affiliates-enterprise' ),
				'platforms'    => array( 'wp-e-commerce' )
			),
			'affiliates-eshop-light' => array(
				'title'        => __( 'eShop (light)', 'affiliates' ),
				'plugin_title' => __( 'Affiliates eShop Integration Light', 'affiliates' ),
				'plugin_url'   => 'https://wordpress.org/plugins/affiliates-eshop-light/',
				'description'  => __( 'This plugin integrates <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a> with eShop. With this integration plugin, referrals are created automatically for your affiliates when sales are made through eShop.', 'affiliates' ),
				'plugin_file'  => 'affiliates-eshop-light/affiliates-eshop-light.php',
				'notes'        => __( 'This light integration is suitable to be used with the <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a> plugin.', 'affiliates' ),
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates' ),
				'platforms'    => array( 'eshop' )
			),
			'affiliates-ecwid-light' => array(
				'title'        => __( 'Ecwid (light)', 'affiliates' ),
				'plugin_title' => __( 'Affiliates Ecwid Light', 'affiliates' ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-ecwid-light/',
				'description'  => __( 'This plugin integrates <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a> with Ecwid. With this integration plugin, affiliates are credited with referrals automatically after a customer has made a purchase through the online store powered by Ecwid.', 'affiliates' ),
				'plugin_file'  => 'affiliates-ecwid-light/affiliates-ecwid-light.php',
				'notes'        => __( 'This light integration is suitable to be used with the <a href="http://wordpress.org/plugins/affiliates/">Affiliates</a> plugin.', 'affiliates' ),
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates' ),
				'platforms'    => array( 'ecwid' )
			)
		);
		self::$integrations = apply_filters( 'affiliates_settings_integrations', self::$integrations );
	}

	/**
	 * Returns the registered integrations.
	 * 
	 * @return array
	 */
	public static function get_integrations() {
		return self::$integrations;
	}

	/**
	 * Renders the integrations section.
	 */
	public static function section() {

		$output = '';

		$output .= '<p class="description">';
		$output .= __( 'Integrations link the affiliate system to e-commerce plugins and other platforms.', 'affiliates' );
		$output .= ' ';
		$output .= __( 'The integrations are required to record referrals, as these award affiliates with commissions based on referred purchases or platform-specific actions.', 'affiliates' );
		$output .= '</p>';
		if ( AFFILIATES_PLUGIN_NAME != 'affiliates' ) {
			$output .= '<p class="description">';
			$output .= __( 'You can manage available integrations here, this includes the installation and activation of integrations with e-commerce and other systems.', 'affiliates' );
			$output .= '</p>';
		} else {
			$output .= '<p class="description">';
			$output .= sprintf( __( 'You can install available integrations in the <a href="%s">Plugins</a> section.', 'affiliates' ), esc_attr( admin_url( 'plugin-install.php?tab=search&type=author&s=itthinx' ) ) );
			$output .= '</p>';
		}
		$output .= '<p class="description">';
		$output .= __( 'You only need to install integrations with plugins that are actually used on the site.', 'affiliates' );
		$output .= '</p>';
		$output .= '<p class="description">';
		$output .= __( 'User registrations do not require a specific integration to be installed.', 'affiliates' );
		$output .= ' ';
		$output .= sprintf(
			__( 'Enable the built-in integration if the options provided under <a href="%s">User Registration</a> are sufficient.', 'affiliates' ),
			esc_attr( admin_url( 'admin.php?page=affiliates-admin-user-registration' ) )
		);
		$output .= '</p>';

		$active_plugins = apply_filters( 'active_plugins', get_option('active_plugins' ) );
		$all_plugins    = get_plugins();

		$list = '<ul class="integrations">';
		foreach( self::$integrations as $key => $integration ) {
			$install_url = wp_nonce_url(
				self_admin_url(
					'update.php?action=install-plugin&plugin=' . $key ),
					'install-plugin_' . $key
			);
			$activate_url   = 'plugins.php?action=activate&plugin=' . urlencode( "$key/$key.php" ) . '&plugin_status=all&paged=1&s&_wpnonce=' . urlencode( wp_create_nonce( "activate-plugin_$key/$key.php" ) );
			$deactivate_url = 'plugins.php?action=deactivate&plugin=' . urlencode( "$key/$key.php" ) . '&plugin_status=all&paged=1&s&_wpnonce=' . urlencode( wp_create_nonce( "deactivate-plugin_$key/$key.php" ) );
			$integration_class = isset( $integration['class'] ) ? $integration['class'] : '';
			$action      = '';
			$button      = '';
			$explanation = '';
			if ( !key_exists( $integration['plugin_file'], $all_plugins ) ) {
				$action = 'install';
				$button = sprintf( '<a class="button" href="%s">Install</a>', esc_url( $install_url ) );
				$explanation = sprintf(
					__( 'The <a href="%s">%s</a> plugin is not installed.', 'affiliates' ),
					esc_attr( $integration['plugin_url'] ),
					esc_html( $integration['plugin_title'] )
				);
			} else {
				if ( is_plugin_inactive( $integration['plugin_file'] ) ) {
					$action = 'activate';
					$button = sprintf( '<a class="button" href="%s">Activate</a>', esc_url( $activate_url ) );
					$explanation = sprintf(
						__( 'The <a href="%s">%s</a> plugin is installed but not activated.', 'affiliates' ),
						esc_attr( $integration['plugin_url'] ),
						esc_html( $integration['plugin_title'] )
					);
					$integration_class .= ' inactive';
				} else {
					$action = 'deactivate';
					$button = sprintf( '<a class="button" href="%s">Deactivate</a>', esc_url( $deactivate_url ) );
					$explanation = sprintf(
						__( 'The <a href="%s">%s</a> plugin is installed and activated.', 'affiliates' ),
						esc_attr( $integration['plugin_url'] ),
						esc_html( $integration['plugin_title'] )
					);
					$integration_class .= ' active';
				}
			}
			if ( AFFILIATES_PLUGIN_NAME == 'affiliates' ) {
				$button = '';
			}
			$button = apply_filters( 'affiliates_settings_integration_button', $button, $action, $key, $integration );
			$explanation = apply_filters( 'affiliates_settings_integration_explanation', $explanation, $action, $key, $integration );
			$list .= sprintf( '<li id="integration-%s">', $key );
			$list .= sprintf( '<div class="integration %s">', $integration_class );
			$list .= '<h3>' . $integration['title'] . '</h3>';
			$list .= '<p class="description">';
			$list .= $integration['description'];
			$list .= '</p>';
			if ( !empty( $integration['notes'] ) ) {
				$list .= '<p class="notes">';
				$list .= $integration['notes'];
				$list .= '</p>';
			}
			if ( !empty( $explanation ) ) {
				$list .= '<p>';
				$list .= $explanation;
				$list .= '</p>';
			}
			if ( !empty( $button ) ) {
				$list .= '<p>';
				$list .= $button;
				$list .= '</p>';
			}
			$list .= '</div>';
			$list .= '</li>';
		}
		$list .= '</ul>';
		$output .= $list;

		echo $output;

		affiliates_footer();
	}
}
Affiliates_Settings_Integrations::init();
