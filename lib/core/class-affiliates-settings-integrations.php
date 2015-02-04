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
				'title'        => __( 'WooCommerce', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_title' => __( 'Affiliates WooCommerce Integration Light', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-woocommerce-light/',
				'description'  => __( 'This plugin integrates Affiliates with WooCommerce. With this integration plugin, referrals are created automatically for your affiliates when sales are made.', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_file'  => 'affiliates-woocommerce-light/affiliates-woocommerce-light.php',
				'notes'        => __( 'This light integration is suitable to be used with the <strong>Affiliates</strong> plugin.', AFFILIATES_PLUGIN_DOMAIN ),
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates' ),
				'platforms'    => array( 'woocommerce' )
			),
			'affiliates-contact-form-7' => array(
				'title'        => __( 'Contact Form 7', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_title' => __( 'Affiliates Contact Form 7 Integration', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-contact-form-7/',
				'description'  => __( 'This plugin integrates <strong>Affiliates</strong>, <strong>Affiliates Pro</strong> and <strong>Affiliates Enterprise</strong> with Contact Form 7. This integration stores data from submitted forms and tracks form submissions to the referring affiliate.', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_file'  => 'affiliates-contact-form-7/affiliates-contact-form-7.php',
				'notes'        => '',
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates', 'affiliates-pro', 'affiliates-enterprise' ),
				'platforms'    => array( 'contact-form-7' )
			),
			'affiliates-jigoshop-light' => array(
				'title'        => __( 'Jigoshop', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_title' => __( 'Affiliates Jigoshop Integration Light', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-jigoshop-light/',
				'description'  => __( 'This plugin integrates <strong>Affiliates</strong> with Jigoshop. With this integration plugin, referrals are created automatically for your affiliates when sales are made.', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_file'  => 'affiliates-jigoshop-light/affiliates-jigoshop-light.php',
				'notes'        => __( 'This light integration is suitable to be used with the <strong>Affiliates</strong> plugin.', AFFILIATES_PLUGIN_DOMAIN ),
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates' ),
				'platforms'    => array( 'jigoshop' )
			),
			'affiliates-wp-e-commerce' => array(
				'title'       => __( 'WP e-Commerce', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_title' => __( 'Affiliates WP e-Commerce Integration', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-wp-e-commerce/',
				'description' => __( 'This plugin integrates <strong>Affiliates</strong>, <strong>Affiliates Pro</strong> and <strong>Affiliates Enterprise</strong> with WP e-Commerce. With this integration plugin, referrals are created automatically for your affiliates when sales are made.', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_file' => 'affiliates-wp-e-commerce/affiliates-wp-e-commerce.php',
				'notes'        => '',
				'repository' => 'wordpress',
				'access'        => 'free',
				'targets'      => array( 'affiliates', 'affiliates-pro', 'affiliates-enterprise' ),
				'platforms'    => array( 'wp-e-commerce' )
			),
			'affiliates-eshop-light' => array(
				'title'        => __( 'eShop', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_title' => __( 'Affiliates eShop Integration Light', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_url'   => 'https://wordpress.org/plugins/affiliates-eshop-light/',
				'description'  => __( 'This plugin integrates Affiliates with eShop. With this integration plugin, referrals are created automatically for your affiliates when sales are made through eShop.', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_file'  => 'affiliates-eshop-light/affiliates-eshop-light.php',
				'notes'        => __( 'This light integration is suitable to be used with the <strong>Affiliates</strong> plugin.', AFFILIATES_PLUGIN_DOMAIN ),
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates' ),
				'platforms'    => array( 'eshop' )
			),
			'affiliates-ecwid-light' => array(
				'title'        => __( 'Ecwid', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_title' => __( 'Affiliates Ecwid Light', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-ecwid-light/',
				'description'  => __( 'This plugin integrates Affiliates with Ecwid. With this integration plugin, affiliates are credited with referrals automatically after a customer has made a purchase through the online store powered by Ecwid.', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_file'  => 'affiliates-ecwid-light/affiliates-ecwid-light.php',
				'notes'        => __( 'This light integration is suitable to be used with the <strong>Affiliates</strong> plugin.', AFFILIATES_PLUGIN_DOMAIN ),
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates' ),
				'platforms'    => array( 'ecwid' )
			),
			'affiliates-ready-light' => array(
				'title'        => __( 'Ready! Ecommerce', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_title' => __( 'Affiliates Ready! Ecommerce Integration Light', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_url'   => 'http://wordpress.org/plugins/affiliates-ready-light/',
				'description'  => __( 'This plugin integrates Affiliates with Ready! Ecommerce. With this integration plugin, referrals are created automatically for your affiliates when sales are made.', AFFILIATES_PLUGIN_DOMAIN ),
				'plugin_file'  => 'affiliates-ready-light/affiliates-ready-light.php',
				'notes'        => '',
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates' ),
				'platforms'    => array( 'ready-ecommerce' )
			)
		);
		self::$integrations = apply_filters( 'affiliates_settings_integrations', self::$integrations );
	}

	/**
	 * Renders the integrations section.
	 */
	public static function section() {

		$output = '';

		$output .= '<p class="description">';
		$output .= __( 'Integrations link the affiliate system to e-commerce plugins and other platforms.', AFFILIATES_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= __( 'The integrations are required to record referrals, as these award affiliates with commissions based on referred purchases or platform-specific actions.', AFFILIATES_PLUGIN_DOMAIN );
		$output .= '</p>';
		$output .= '<p class="description">';
		$output .= __( 'You can manage available integrations here, this includes the installation and activation of integrations with e-commerce and other systems.', AFFILIATES_PLUGIN_DOMAIN );
		$output .= '</p>';
		$output .= '<p class="description">';
		$output .= __( 'You only need to install integrations with plugins that are actually used on the site.', AFFILIATES_PLUGIN_DOMAIN );
		$output .= '</p>';
		$output .= '<p class="description">';
		$output .= __( 'User registrations do not require a specific integration to be installed.', AFFILIATES_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= sprintf(
			__( 'Enable the built-in integration if the options provided under <a href="%s">User Registration</a> are sufficient.', AFFILIATES_PLUGIN_DOMAIN ),
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
			$explanation = '';
			if ( !key_exists( $integration['plugin_file'], $all_plugins ) ) {
				$action = sprintf( '<a class="button" href="%s">Install</a>', esc_url( $install_url ) );
				$explanation = sprintf(
					__( 'The <a href="%s">%s</a> plugin is not installed.', AFFILIATES_PLUGIN_DOMAIN ),
					esc_attr( $integration['plugin_url'] ),
					esc_html( $integration['plugin_title'] )
				);
			} else {
				if ( is_plugin_inactive( $integration['plugin_file'] ) ) {
					$action = sprintf( '<a class="button" href="%s">Activate</a>', esc_url( $activate_url ) );
					$explanation = sprintf(
						__( 'The <a href="%s">%s</a> plugin is installed but not activated.', AFFILIATES_PLUGIN_DOMAIN ),
						esc_attr( $integration['plugin_url'] ),
						esc_html( $integration['plugin_title'] )
					);
					$integration_class .= ' inactive';
				} else {
					$action = sprintf( '<a class="button" href="%s">Deactivate</a>', esc_url( $deactivate_url ) );
					$explanation = sprintf(
						__( 'The <a href="%s">%s</a> plugin is installed and activated.', AFFILIATES_PLUGIN_DOMAIN ),
						esc_attr( $integration['plugin_url'] ),
						esc_html( $integration['plugin_title'] )
					);
					$integration_class .= ' active';
				}
			}
			$list .= '<li>';
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
			$list .= '<p>';
			$list .= $explanation;
			$list .= '</p>';
			$list .= '<p>';
			$list .= $action;
			$list .= '</p>';
			$list .= '</div>';
			$list .= '</li>';
		}
		$list .= '</ul>';
		$output .= $list;

		echo $output;
	}
}
Affiliates_Settings_Integrations::init();
