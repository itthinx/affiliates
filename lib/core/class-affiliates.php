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

	/**
	 * Default currency code.
	 *
	 * @var string
	 */
	const DEFAULT_CURRENCY = 'USD';

	/**
	 * Supported currencies.
	 *
	 * @var array
	 */
	public static $supported_currencies = null;

	/**
	 * Pairs of currency code (ISO 4217) and name.
	 *
	 * @var array
	 */
	public static $currencies = null;

	/**
	 * Initialize.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
	}

	/**
	 * Runs the affiliates_currencies and affiliates_supported_currencies filters.
	 * Hooked on init so that others can catch those filters.
	 */
	public static function wp_init() {
		self::set_currencies();
		self::$currencies = apply_filters( 'affiliates_currencies', self::$currencies );
		self::$supported_currencies = apply_filters(
			'affiliates_supported_currencies', array_keys( self::$currencies )
		);
	}

	/**
	 * Determine currencies.
	 *
	 * @since 5.1.0
	 */
	private static function set_currencies() {
		self::$currencies = array(
			'AED' => __( 'United Arab Emirates Dirham', 'affiliates' ),
			'AFN' => __( 'Afghanistan Afghani', 'affiliates' ),
			'ALL' => __( 'Albania Lek', 'affiliates' ),
			'AMD' => __( 'Armenia Dram', 'affiliates' ),
			'ANG' => __( 'Netherlands Antilles Guilder', 'affiliates' ),
			'AOA' => __( 'Angola Kwanza', 'affiliates' ),
			'ARS' => __( 'Argentina Peso', 'affiliates' ),
			'AUD' => __( 'Australia Dollar', 'affiliates' ),
			'AWG' => __( 'Aruba Guilder', 'affiliates' ),
			'AZN' => __( 'Azerbaijan Manat', 'affiliates' ),
			'BAM' => __( 'Bosnia and Herzegovina Convertible Marka', 'affiliates' ),
			'BBD' => __( 'Barbados Dollar', 'affiliates' ),
			'BDT' => __( 'Bangladesh Taka', 'affiliates' ),
			'BGN' => __( 'Bulgaria Lev', 'affiliates' ),
			'BHD' => __( 'Bahrain Dinar', 'affiliates' ),
			'BIF' => __( 'Burundi Franc', 'affiliates' ),
			'BMD' => __( 'Bermuda Dollar', 'affiliates' ),
			'BND' => __( 'Brunei Darussalam Dollar', 'affiliates' ),
			'BOB' => __( 'Bolivia Boliviano', 'affiliates' ),
			'BRL' => __( 'Brazil Real', 'affiliates' ),
			'BSD' => __( 'Bahamas Dollar', 'affiliates' ),
			'BTN' => __( 'Bhutan Ngultrum', 'affiliates' ),
			'BWP' => __( 'Botswana Pula', 'affiliates' ),
			'BYN' => __( 'Belarus Ruble', 'affiliates' ),
			'BZD' => __( 'Belize Dollar', 'affiliates' ),
			'CAD' => __( 'Canada Dollar', 'affiliates' ),
			'CDF' => __( 'Congo/Kinshasa Franc', 'affiliates' ),
			'CHF' => __( 'Switzerland Franc', 'affiliates' ),
			'CLP' => __( 'Chile Peso', 'affiliates' ),
			'CNY' => __( 'China Yuan Renminbi', 'affiliates' ),
			'COP' => __( 'Colombia Peso', 'affiliates' ),
			'CRC' => __( 'Costa Rica Colon', 'affiliates' ),
			'CUC' => __( 'Cuba Convertible Peso', 'affiliates' ),
			'CUP' => __( 'Cuba Peso', 'affiliates' ),
			'CVE' => __( 'Cape Verde Escudo', 'affiliates' ),
			'CZK' => __( 'Czech Republic Koruna', 'affiliates' ),
			'DJF' => __( 'Djibouti Franc', 'affiliates' ),
			'DKK' => __( 'Denmark Krone', 'affiliates' ),
			'DOP' => __( 'Dominican Republic Peso', 'affiliates' ),
			'DZD' => __( 'Algeria Dinar', 'affiliates' ),
			'EGP' => __( 'Egypt Pound', 'affiliates' ),
			'ERN' => __( 'Eritrea Nakfa', 'affiliates' ),
			'ETB' => __( 'Ethiopia Birr', 'affiliates' ),
			'EUR' => __( 'Euro Member Countries', 'affiliates' ),
			'FJD' => __( 'Fiji Dollar', 'affiliates' ),
			'FKP' => __( 'Falkland Islands (Malvinas) Pound', 'affiliates' ),
			'GBP' => __( 'United Kingdom Pound', 'affiliates' ),
			'GEL' => __( 'Georgia Lari', 'affiliates' ),
			'GGP' => __( 'Guernsey Pound', 'affiliates' ),
			'GHS' => __( 'Ghana Cedi', 'affiliates' ),
			'GIP' => __( 'Gibraltar Pound', 'affiliates' ),
			'GMD' => __( 'Gambia Dalasi', 'affiliates' ),
			'GNF' => __( 'Guinea Franc', 'affiliates' ),
			'GTQ' => __( 'Guatemala Quetzal', 'affiliates' ),
			'GYD' => __( 'Guyana Dollar', 'affiliates' ),
			'HKD' => __( 'Hong Kong Dollar', 'affiliates' ),
			'HNL' => __( 'Honduras Lempira', 'affiliates' ),
			'HRK' => __( 'Croatia Kuna', 'affiliates' ),
			'HTG' => __( 'Haiti Gourde', 'affiliates' ),
			'HUF' => __( 'Hungary Forint', 'affiliates' ),
			'IDR' => __( 'Indonesia Rupiah', 'affiliates' ),
			'ILS' => __( 'Israel Shekel', 'affiliates' ),
			'IMP' => __( 'Isle of Man Pound', 'affiliates' ),
			'INR' => __( 'India Rupee', 'affiliates' ),
			'IQD' => __( 'Iraq Dinar', 'affiliates' ),
			'IRR' => __( 'Iran Rial', 'affiliates' ),
			'ISK' => __( 'Iceland Krona', 'affiliates' ),
			'JEP' => __( 'Jersey Pound', 'affiliates' ),
			'JMD' => __( 'Jamaica Dollar', 'affiliates' ),
			'JOD' => __( 'Jordan Dinar', 'affiliates' ),
			'JPY' => __( 'Japan Yen', 'affiliates' ),
			'KES' => __( 'Kenya Shilling', 'affiliates' ),
			'KGS' => __( 'Kyrgyzstan Som', 'affiliates' ),
			'KHR' => __( 'Cambodia Riel', 'affiliates' ),
			'KMF' => __( 'Comorian Franc', 'affiliates' ),
			'KPW' => __( 'Korea (North) Won', 'affiliates' ),
			'KRW' => __( 'Korea (South) Won', 'affiliates' ),
			'KWD' => __( 'Kuwait Dinar', 'affiliates' ),
			'KYD' => __( 'Cayman Islands Dollar', 'affiliates' ),
			'KZT' => __( 'Kazakhstan Tenge', 'affiliates' ),
			'LAK' => __( 'Laos Kip', 'affiliates' ),
			'LBP' => __( 'Lebanon Pound', 'affiliates' ),
			'LKR' => __( 'Sri Lanka Rupee', 'affiliates' ),
			'LRD' => __( 'Liberia Dollar', 'affiliates' ),
			'LSL' => __( 'Lesotho Loti', 'affiliates' ),
			'LYD' => __( 'Libya Dinar', 'affiliates' ),
			'MAD' => __( 'Morocco Dirham', 'affiliates' ),
			'MDL' => __( 'Moldova Leu', 'affiliates' ),
			'MGA' => __( 'Madagascar Ariary', 'affiliates' ),
			'MKD' => __( 'Macedonia Denar', 'affiliates' ),
			'MMK' => __( 'Myanmar (Burma) Kyat', 'affiliates' ),
			'MNT' => __( 'Mongolia Tughrik', 'affiliates' ),
			'MOP' => __( 'Macau Pataca', 'affiliates' ),
			'MRU' => __( 'Mauritania Ouguiya', 'affiliates' ),
			'MUR' => __( 'Mauritius Rupee', 'affiliates' ),
			'MVR' => __( 'Maldives (Maldive Islands) Rufiyaa', 'affiliates' ),
			'MWK' => __( 'Malawi Kwacha', 'affiliates' ),
			'MXN' => __( 'Mexico Peso', 'affiliates' ),
			'MYR' => __( 'Malaysia Ringgit', 'affiliates' ),
			'MZN' => __( 'Mozambique Metical', 'affiliates' ),
			'NAD' => __( 'Namibia Dollar', 'affiliates' ),
			'NGN' => __( 'Nigeria Naira', 'affiliates' ),
			'NIO' => __( 'Nicaragua Cordoba', 'affiliates' ),
			'NOK' => __( 'Norway Krone', 'affiliates' ),
			'NPR' => __( 'Nepal Rupee', 'affiliates' ),
			'NZD' => __( 'New Zealand Dollar', 'affiliates' ),
			'OMR' => __( 'Oman Rial', 'affiliates' ),
			'PAB' => __( 'Panama Balboa', 'affiliates' ),
			'PEN' => __( 'Peru Sol', 'affiliates' ),
			'PGK' => __( 'Papua New Guinea Kina', 'affiliates' ),
			'PHP' => __( 'Philippines Peso', 'affiliates' ),
			'PKR' => __( 'Pakistan Rupee', 'affiliates' ),
			'PLN' => __( 'Poland Zloty', 'affiliates' ),
			'PYG' => __( 'Paraguay Guarani', 'affiliates' ),
			'QAR' => __( 'Qatar Riyal', 'affiliates' ),
			'RON' => __( 'Romania Leu', 'affiliates' ),
			'RSD' => __( 'Serbia Dinar', 'affiliates' ),
			'RUB' => __( 'Russia Ruble', 'affiliates' ),
			'RWF' => __( 'Rwanda Franc', 'affiliates' ),
			'SAR' => __( 'Saudi Arabia Riyal', 'affiliates' ),
			'SBD' => __( 'Solomon Islands Dollar', 'affiliates' ),
			'SCR' => __( 'Seychelles Rupee', 'affiliates' ),
			'SDG' => __( 'Sudan Pound', 'affiliates' ),
			'SEK' => __( 'Sweden Krona', 'affiliates' ),
			'SGD' => __( 'Singapore Dollar', 'affiliates' ),
			'SHP' => __( 'Saint Helena Pound', 'affiliates' ),
			'SLL' => __( 'Sierra Leone Leone', 'affiliates' ),
			'SOS' => __( 'Somalia Shilling', 'affiliates' ),
			'SPL' => __( 'Seborga Luigino', 'affiliates' ),
			'SRD' => __( 'Suriname Dollar', 'affiliates' ),
			'STN' => __( 'São Tomé and Príncipe Dobra', 'affiliates' ),
			'SVC' => __( 'El Salvador Colon', 'affiliates' ),
			'SYP' => __( 'Syria Pound', 'affiliates' ),
			'SZL' => __( 'eSwatini Lilangeni', 'affiliates' ),
			'THB' => __( 'Thailand Baht', 'affiliates' ),
			'TJS' => __( 'Tajikistan Somoni', 'affiliates' ),
			'TMT' => __( 'Turkmenistan Manat', 'affiliates' ),
			'TND' => __( 'Tunisia Dinar', 'affiliates' ),
			'TOP' => __( 'Tonga Pa\'anga', 'affiliates' ),
			'TRY' => __( 'Turkey Lira', 'affiliates' ),
			'TTD' => __( 'Trinidad and Tobago Dollar', 'affiliates' ),
			'TVD' => __( 'Tuvalu Dollar', 'affiliates' ),
			'TWD' => __( 'Taiwan New Dollar', 'affiliates' ),
			'TZS' => __( 'Tanzania Shilling', 'affiliates' ),
			'UAH' => __( 'Ukraine Hryvnia', 'affiliates' ),
			'UGX' => __( 'Uganda Shilling', 'affiliates' ),
			'USD' => __( 'United States Dollar', 'affiliates' ),
			'UYU' => __( 'Uruguay Peso', 'affiliates' ),
			'UZS' => __( 'Uzbekistan Som', 'affiliates' ),
			'VEF' => __( 'Venezuela Bolívar', 'affiliates' ),
			'VND' => __( 'Viet Nam Dong', 'affiliates' ),
			'VUV' => __( 'Vanuatu Vatu', 'affiliates' ),
			'WST' => __( 'Samoa Tala', 'affiliates' ),
			'XAF' => __( 'Communauté Financière Africaine (BEAC) CFA Franc BEAC', 'affiliates' ),
			'XCD' => __( 'East Caribbean Dollar', 'affiliates' ),
			'XDR' => __( 'International Monetary Fund (IMF) Special Drawing Rights', 'affiliates' ),
			'XOF' => __( 'Communauté Financière Africaine (BCEAO) Franc', 'affiliates' ),
			'XPF' => __( 'Comptoirs Français du Pacifique (CFP) Franc', 'affiliates' ),
			'YER' => __( 'Yemen Rial', 'affiliates' ),
			'ZAR' => __( 'South Africa Rand', 'affiliates' ),
			'ZMW' => __( 'Zambia Kwacha', 'affiliates' ),
			'ZWD' => __( 'Zimbabwe Dollar', 'affiliates' )
		);
		self::$supported_currencies = array_keys( self::$currencies );
	}
}
Affiliates::init();
