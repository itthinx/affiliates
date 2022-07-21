<?php
/**
 * class-affiliates-user-registration.php
 *
 * Copyright (c) 2010 - 2014 "kento" Karim Rahimpur www.itthinx.com
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
 * @since 2.7.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User registration integration.
 */
class Affiliates_User_Registration {

	/**
	 * Referral type used for user registration referrals.
	 * @var string
	 */
	const REFERRAL_TYPE = 'user';

	/**
	 * Hooks on user_register to record a referral when new users register.
	 * (only when the built-in User Registration integration is enabled).
	 */
	public static function init() {
		if ( get_option( 'aff_user_registration_enabled', 'no' ) == 'yes' ) {
			add_action( 'user_register', array( __CLASS__, 'user_register' ) );
		}
		if ( get_option( 'aff_customer_registration_enabled', 'no' ) == 'yes' ) {
			add_action( 'woocommerce_created_customer', array( __CLASS__, 'woocommerce_created_customer' ), 10, 3 );
		}
	}

	/**
	 * Hooks on customer creation to record a referral when a new customer is created.
	 *
	 * @param int $customer_id
	 * @param array $new_customer_data
	 * @param boolean $password_generated
	 */
	public static function woocommerce_created_customer( $customer_id, $new_customer_data, $password_generated ) {
		self::user_register( $customer_id, array( 'force' => true, 'type' => 'customer' ) );
	}

	/**
	 * Record a referral when a new user has been referred by an affiliate.
	 *
	 * @param int $user_id
	 * @param array $params registration parameters
	 */
	public static function user_register( $user_id, $params = array() ) {

		extract( $params );

		if ( !isset( $force ) ) {
			$force = false;
		}
		if ( !isset( $type ) ) {
			$type = null;
		}

		if ( !$force && is_admin() ) {
			if ( !apply_filters( 'affiliates_user_registration_on_admin', false ) ) {
				return;
			}
		}

		if ( $user = get_user_by( 'id', $user_id ) ) {

			$post_id = null;
			// Notes on registrations made on WooCommerce checkout:
			// Using global $post; and $post->ID just provides the ID of the first product in the shop.
			// The same applies for $post = get_post(); and $post->ID.
			// And also for $permalink = get_permalink(); and $post_id = url_to_postid( $permalink ) );
			// The folllowing obtains the shop's ID on checkout and corresponding page IDs for normal other cases.
			$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$post_id = url_to_postid( $current_url );
			if ( $post_id === 0 ) {
				$post_id = null;
			}

			switch ( $type ) {
				case 'customer' :
					$description = sprintf( 'Customer Registration %s', esc_html( $user->user_login ) );
					break;
				default :
					$description = sprintf( 'User Registration %s', esc_html( $user->user_login ) );
			}
			$base_amount = null;
			if ( AFFILIATES_PLUGIN_NAME != 'affiliates' ) {
				$base_amount = get_option( 'aff_user_registration_base_amount', null );
			}
			$amount = null;
			if ( empty( $base_amount ) ) {
				$amount = get_option( 'aff_user_registration_amount', '0' );
			}
			$currency = get_option( 'aff_user_registration_currency', Affiliates::DEFAULT_CURRENCY );
			$user_registration_referral_status = get_option(
				'aff_user_registration_referral_status',
				get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED )
			);

			$data = array(
				'user_login' => array(
					'title'  => 'Username',
					'domain' => 'affiliates',
					'value'  => $user->user_login,
				),
				'user_email' => array(
					'title'  => 'Email',
					'domain' => 'affiliates',
					'value'  => $user->user_email,
				),
				'first_name' => array(
					'title'  => 'First Name',
					'domain' => 'affiliates',
					'value'  => $user->first_name,
				),
				'last_name' => array(
					'title'  => 'Last Name',
					'domain' => 'affiliates',
					'value'  => $user->last_name,
				),
				'base_amount' => array(
					'title'  => 'Base Amount',
					'domain' => 'affiliates',
					'value'  => $base_amount
				)
			);

			if ( class_exists( 'Affiliates_Referral_Controller' ) ) {
				$rc = new Affiliates_Referral_Controller();
				$params = $rc->evaluate_referrer();
				if ( $params !== null && is_array( $params ) && isset( $params['affiliate_id'] ) ) {
					$affiliate_id = $params['affiliate_id'];

					$group_ids = null;
					if ( class_exists( 'Groups_User' ) ) {
						if ( $affiliate_user_id = affiliates_get_affiliate_user( $affiliate_id ) ) {
							$groups_user = new Groups_User( $affiliate_user_id );
							$group_ids = $groups_user->group_ids_deep;
							if ( !is_array( $group_ids ) || ( count( $group_ids ) === 0 ) ) {
								$group_ids = null;
							}
						}
					}

					$referral_items = array();
					if ( $rate = $rc->seek_rate(
						array(
							'affiliate_id' => $affiliate_id,
							'group_ids'    => $group_ids,
							'integration'  => 'user-registration'
					) ) ) {
						$rate_id = $rate->rate_id;
						switch ( $rate->type ) {
							case AFFILIATES_PRO_RATES_TYPE_AMOUNT :
								$amount = Affiliates_Math::add( '0', $rate->value, affiliates_get_referral_amount_decimals() );
								break;
							case AFFILIATES_PRO_RATES_TYPE_RATE :
								if ( $base_amount !== null ) {
									$amount = Affiliates_Math::mul( $base_amount, $rate->value, affiliates_get_referral_amount_decimals() );
								}
								break;
							case AFFILIATES_PRO_RATES_TYPE_FORMULA :
								if ( $base_amount !== null ) {
									$tokenizer = new Affiliates_Formula_Tokenizer( $rate->get_meta( 'formula' ) );
									$quantity = 1;
									$variables = apply_filters(
										'affiliates_formula_computer_variables',
										array(
											's' => $base_amount,
											't' => $base_amount,
											'p' => $base_amount / $quantity,
											'q' => $quantity
										),
										$rate,
										array(
											'affiliate_id' => $affiliate_id,
											'integration'  => 'user-registration',
											'post_id'      => $post_id,
											'user_id'      => $user_id
										)
									);
									$computer = new Affiliates_Formula_Computer( $tokenizer, $variables );
									$amount = $computer->compute();
									if ( $computer->has_errors() ) {
										affiliates_log_error( $computer->get_errors_pretty( 'text' ) );
									}
									if ( $amount === null || $amount < 0 ) {
										$amount = 0.0;
									}
									$amount = Affiliates_Math::add( '0', $amount, affiliates_get_referral_amount_decimals() );
								}
								break;
						}
						$referral_item = new Affiliates_Referral_Item( array(
							'rate_id'     => $rate_id,
							'amount'      => $amount,
							'currency_id' => $currency,
							'type'        => 'user',
							'reference'   => $user_id,
							'line_amount' => $amount,
							'object_id'   => $user_id
						) );
						$referral_items[] = $referral_item;
					}
					$params['post_id']          = $post_id;
					$params['description']      = $description;
					$params['data']             = $data;
					$params['currency_id']      = $currency;
					$params['status']           = $user_registration_referral_status;
					$params['type']             = self::REFERRAL_TYPE;
					$params['referral_items']   = $referral_items;
					$params['reference']        = $user_id;
					$params['reference_amount'] = $base_amount;
					$rc->add_referral( $params );
				}
			} else {
				$affiliate_id = affiliates_suggest_referral( $post_id, $description, $data, $amount, $currency, $user_registration_referral_status, self::REFERRAL_TYPE );
			}

		}
	}
}
Affiliates_User_Registration::init();
