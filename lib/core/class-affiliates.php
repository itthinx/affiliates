<?php
/**
 * class-affiliates.php
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
 * Base class.
 */
class Affiliates {

	const DEFAULT_CURRENCY = 'USD';

	public static $supported_currencies = array(
		// Australian Dollar
		'AUD',
		// Brazilian Real
		'BRL',
		// Canadian Dollar
		'CAD',
		// Czech Koruna
		'CZK',
		// Danish Krone
		'DKK',
		// Euro
		'EUR',
		// Hong Kong Dollar
		'HKD',
		// Hungarian Forint
		'HUF',
		// Israeli New Sheqel
		'ILS',
		// Japanese Yen
		'JPY',
		// Malaysian Ringgit
		'MYR',
		// Mexican Peso
		'MXN',
		// Norwegian Krone
		'NOK',
		// New Zealand Dollar
		'NZD',
		// Philippine Peso
		'PHP',
		// Polish Zloty
		'PLN',
		// Pound Sterling
		'GBP',
		// Singapore Dollar
		'SGD',
		// Swedish Krona
		'SEK',
		// Swiss Franc
		'CHF',
		// Taiwan New Dollar
		'TWD',
		// Thai Baht
		'THB',
		// Turkish Lira
		'TRY',
		// U.S. Dollar
		'USD'
	);
}