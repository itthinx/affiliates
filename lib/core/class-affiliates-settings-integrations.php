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

// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

/**
 * Integration section.
 */
class Affiliates_Settings_Integrations extends Affiliates_Settings {

	private static $integrations = null;
	private static $premium_integrations = null;

	public static function init() {
		self::$integrations =  array(
			'affiliates-woocommerce-light' => array(
				'title'        => __( 'WooCommerce (light)', 'affiliates' ),
				'plugin_title' => __( 'Affiliates WooCommerce Integration Light', 'affiliates' ),
				'plugin_url'   => 'https://wordpress.org/plugins/affiliates-woocommerce-light/',
				'description'  => __( 'This plugin integrates Affiliates with WooCommerce. With this integration plugin, referrals are created automatically for your affiliates when sales are made.', 'affiliates' ),
				'plugin_file'  => 'affiliates-woocommerce-light/affiliates-woocommerce-light.php',
				'notes'        => __( 'This light integration is suitable to be used with the Affiliates plugin.', 'affiliates' ),
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates' ),
				'platforms'    => array( 'woocommerce' )
			),
			'affiliates-contact-form-7' => array(
				'title'        => __( 'Contact Form 7', 'affiliates' ),
				'plugin_title' => __( 'Affiliates Contact Form 7 Integration', 'affiliates' ),
				'plugin_url'   => 'https://wordpress.org/plugins/affiliates-contact-form-7/',
				'description'  => sprintf(
					__( 'This plugin integrates Affiliates, %1$s and %2$s with Contact Form 7. This integration stores data from submitted forms and tracks form submissions to the referring affiliate.', 'affiliates' ),
					'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
					'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>'
				),
				'plugin_file'  => 'affiliates-contact-form-7/affiliates-contact-form-7.php',
				'notes'        => '',
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates', 'affiliates-pro', 'affiliates-enterprise' ),
				'platforms'    => array( 'contact-form-7' )
			),
			'affiliates-events-manager' => array(
				'title'        => __( 'Events Manager', 'affiliates' ),
				'plugin_title' => __( 'Affiliates Events Manager Integration', 'affiliates' ),
				'plugin_url'   => 'https://wordpress.org/plugins/affiliates-events-manager/',
				'description'  => sprintf(
					__( 'This plugin integrates Affiliates, %1$s and %2$s with Events Manager. This integration allows to record referrals to grant affiliates commissions on referred bookings.', 'affiliates' ),
					'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
					'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>'
				),
				'plugin_file'  => 'affiliates-events-manager/affiliates-events-manager.php',
				'notes'        => '',
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates', 'affiliates-pro', 'affiliates-enterprise' ),
				'platforms'    => array( 'events-manager' )
			),
			'affiliates-formidable' => array(
				'title'        => __( 'Formidable Forms', 'affiliates' ),
				'plugin_title' => __( 'Affiliates Formidable Forms Integration', 'affiliates' ),
				'plugin_url'   => 'https://wordpress.org/plugins/affiliates-formidable/',
				'description'  => sprintf(
					__( 'This plugin integrates Affiliates, %1$s and %2$s with Formidable Forms. Affiliates can sign up through forms handled with Formidable Forms. Form submissions that are referred through affiliates, can grant commissions to affiliates and record referral details.', 'affiliates' ),
					'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
					'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>'
				),
				'plugin_file'  => 'affiliates-formidable/affiliates-formidable.php',
				'notes'        => '',
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates', 'affiliates-pro', 'affiliates-enterprise' ),
				'platforms'    => array( 'formidable' )
			),
			'affiliates-ninja-forms' => array(
				'title'        => __( 'Ninja Forms', 'affiliates' ),
				'plugin_title' => __( 'Affiliates Ninja Forms Integration', 'affiliates' ),
				'plugin_url'   => 'https://wordpress.org/plugins/affiliates-ninja-forms/',
				'description'  => sprintf(
					__( 'This plugin integrates Affiliates, %1$s and %2$s with Ninja Forms. Affiliates can sign up through forms handled with Ninja Forms. Form submissions that are referred through affiliates, can grant commissions to affiliates and record referral details.', 'affiliates' ),
					'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
					'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>'
				),
				'plugin_file'  => 'affiliates-ninja-forms/affiliates-ninja-forms.php',
				'notes'        => '',
				'repository'   => 'wordpress',
				'access'       => 'free',
				'targets'      => array( 'affiliates', 'affiliates-pro', 'affiliates-enterprise' ),
				'platforms'    => array( 'ninja-forms' )
			),
		);
		self::$integrations = apply_filters( 'affiliates_settings_integrations', self::$integrations );
		self::$premium_integrations = array(
			'affiliates-woocommerce' => array(
				'title'        => __( 'WooCommerce', 'affiliates' ),
				'description'  =>
					sprintf(
						__( 'This plugin integrates %1$s and %2$s with WooCommerce. With this advanced integration plugin, referrals are created and synchronized automatically for your affiliates when sales are made. This integration also supports referrals on recurring payments related to subscriptions and coupons related to affiliates to grant referrals when customers use them to credit the corresponding affiliate.', 'affiliates' ),
						'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
						'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>'
					),
				'notes'        => sprintf(
					__( 'This integration is suitable to be used with %1$s or %2$s.', 'affiliates' ),
					'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
					'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>'
				),
				'class'        => 'ext',
			),
			'affiliates-addtoany' => array(
				'title'        => __( 'AddToAny', 'affiliates' ),
				'description'  => sprintf(
					__( 'This plugin integrates %1$s and %2$s with %3$s. The %4$s are required.', 'affiliates' ),
					'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
					'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>',
					'<a href="https://www.addtoany.com/">AddToAny</a>',
					'<a href="https://wordpress.org/plugins/add-to-any/">Share Buttons by AddToAny</a>'
					
				),
				'notes'        =>
					__( 'Makes it even easier to share using affiliate links automatically.', 'affiliates' ) .
					' ' .
					sprintf(
						__( 'This integration is suitable to be used with %1$s or %2$s.', 'affiliates' ),
						'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
						'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>'
					),
				'class'        => 'ext'
			),
			'affiliates-ppc' => array(
				'title'        => __( 'Pay per Click', 'affiliates' ),
				'description'  => sprintf(
					__( 'Pay affiliate commissions based on clicks or visits to affiliate links. This plugin adds the possibility to grant commissions based on Pay per Click, Pay per Visit and Pay per Daily Visit with %1$s and %2$s.', 'affiliates' ),
					'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
					'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>',
				),
				'notes'        => sprintf(
					__( 'This integration is suitable to be used with %1$s or %2$s.', 'affiliates' ),
					'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
					'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>',
				),
				'class'        => 'ext'
			),
			'affiliates-gravityforms' => array(
				'title'        => __( 'Gravity Forms', 'affiliates' ),
				'description'  => sprintf(
					__( 'This plugin integrates %1$s and %2$s with %3$s.', 'affiliates' ),
					'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
					'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>',
					'<a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=290919">Gravity Forms</a>'
				),
				'notes'        =>
					__( 'This extension allows to record referrals for form submissions and to create affiliate accounts (requires the Gravity Forms User Registation Add-On) for new users based on Gravity Forms.', 'affiliates' ) .
					' ' .
					sprintf(
						__( 'This integration is suitable to be used with %1$s or %2$s.', 'affiliates' ),
						'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
						'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>',
					),
				'class'        => 'ext'
			)
		);
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
		$output .= sprintf(
			esc_html__( 'Please also refer to the %s for additional extensions.', 'affiliates' ),
			sprintf( '<a href="%s">Add-Ons</a>', esc_url( admin_url( 'admin.php?page=affiliates-admin-add-ons' ) ) )
		);
		$output .= '</p>';

		$output .= '<p class="description">';
		$output .= esc_html__( 'Integrations link the affiliate system to e-commerce plugins and other platforms.', 'affiliates' );
		$output .= ' ';
		$output .= esc_html__( 'The integrations are required to record referrals, as these award affiliates with commissions based on referred purchases or platform-specific actions.', 'affiliates' );
		$output .= '</p>';
		if ( AFFILIATES_PLUGIN_NAME != 'affiliates' ) {
			$output .= '<p class="description">';
			$output .= esc_html__( 'You can manage available integrations here, this includes the installation and activation of integrations with e-commerce and other systems.', 'affiliates' );
			$output .= '</p>';
		} else {
			$output .= '<p class="description">';
			$output .= sprintf(
				esc_html__( 'You can install available integrations in the %s section.', 'affiliates' ),
				sprintf( '<a href="%s">Plugins</a>', esc_url( admin_url( 'plugin-install.php?tab=search&type=author&s=itthinx' ) ) )
			);
			$output .= '</p>';
		}
		$output .= '<p class="description">';
		$output .= __( 'You only need to install integrations with plugins that are actually used on the site.', 'affiliates' );
		$output .= '</p>';
		$output .= '<p class="description">';
		$output .= esc_html__( 'User registrations do not require a specific integration to be installed.', 'affiliates' );
		$output .= ' ';
		$output .= sprintf(
			esc_html__( 'Enable the built-in integration if the options provided under %s are sufficient.', 'affiliates' ),
			sprintf( '<a href="%s">User Registration</a>', esc_url( admin_url( 'admin.php?page=affiliates-admin-user-registration' ) ) )
		);
		$output .= '</p>';

		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
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
			if ( !array_key_exists( $integration['plugin_file'], $all_plugins ) ) {
				$action = 'install';
				$button = sprintf( '<a class="button" href="%s">Install</a>', esc_url( $install_url ) );
				$explanation = sprintf(
					esc_html__( 'The %s plugin is not installed.', 'affiliates' ),
					sprintf(
						'<a href="%s">%s</a>',
						esc_attr( $integration['plugin_url'] ),
						esc_html( $integration['plugin_title'] )
					)
				);
			} else {
				if ( is_plugin_inactive( $integration['plugin_file'] ) ) {
					$action = 'activate';
					$button = sprintf( '<a class="button" href="%1$s">%2$s</a>', esc_url( $activate_url ), esc_html__( 'Activate', 'affiliates' ) );
					$explanation = sprintf(
						esc_html__( 'The %s plugin is installed but not activated.', 'affiliates' ),
						sprintf(
							'<a href="%s">%s</a>',
							esc_attr( $integration['plugin_url'] ),
							esc_html( $integration['plugin_title'] )
						)
					);
					$integration_class .= ' inactive';
				} else {
					$action = 'deactivate';
					$button = sprintf( '<a class="button" href="%1$s">%2$s</a>', esc_url( $deactivate_url ), esc_html__( 'Deactivate', 'affiliates' ) );
					$explanation = sprintf(
						esc_html__( 'The %s plugin is installed and activated.', 'affiliates' ),
						sprintf(
							'<a href="%s">%s</a>',
							esc_attr( $integration['plugin_url'] ),
							esc_html( $integration['plugin_title'] )
						)
					);
					$integration_class .= ' active';
				}
			}
			if ( AFFILIATES_PLUGIN_NAME === 'affiliates' ) {
				$button = '';
			}
			$button = apply_filters( 'affiliates_settings_integration_button', $button, $action, $key, $integration );
			$explanation = apply_filters( 'affiliates_settings_integration_explanation', $explanation, $action, $key, $integration );
			$list .= sprintf( '<li id="integration-%s">', esc_attr( $key ) );
			$list .= sprintf( '<div class="integration %s">', esc_attr( $integration_class ) );
			$list .= '<h3>' . esc_html( $integration['title'] ) . '</h3>';
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

		if ( AFFILIATES_PLUGIN_NAME === 'affiliates' ) {
			$output .= '<h2>';
			$output .= esc_html__( 'Premium Integrations', 'affiliates' );
			$output .= '</h2>';
			$output .= '<p>';
			$output .= sprintf(
				esc_html__( 'These integrations are available with %1$s and %2$s.', 'affiliates' ),
				'<a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a>',
				'<a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a>',
			);
			$output .= '</p>';
			$list = '<ul class="integrations">';
			foreach( self::$premium_integrations as $key => $integration ) {
				$integration_class = isset( $integration['class'] ) ? $integration['class'] : '';
				$list .= sprintf( '<li id="integration-%s">', esc_attr( $key ) );
				$list .= sprintf( '<div class="integration %s">', esc_attr( $integration_class ) );
				$list .= '<h3>' . esc_html( $integration['title'] ) . '</h3>';
				$list .= '<p class="description">';
				$list .= $integration['description'];
				$list .= '</p>';
				if ( !empty( $integration['notes'] ) ) {
					$list .= '<p class="notes">';
					$list .= $integration['notes'];
					$list .= '</p>';
				}
				$list .= '</div>';
				$list .= '</li>';
			}
			$list .= '</ul>';
			$output .= $list;
		}

		echo $output;

		affiliates_footer();
	}
}
Affiliates_Settings_Integrations::init();
