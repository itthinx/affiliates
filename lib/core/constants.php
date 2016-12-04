<?php
/**
 * class-affiliates-options.php
 * 
 * Copyright (c) 2010, 2011 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFFILIATES_DEFAULT_VERSION', '1.0.0' );

/**
 * @var string plugin domain
 */
define( 'AFFILIATES_PLUGIN_DOMAIN', 'affiliates' );

/**
 * @var string plugin directory on the server
 */
define( 'AFFILIATES_PLUGIN_DIR', AFFILIATES_CORE_DIR );

/**
 * @var string plugin url
 */
define( 'AFFILIATES_PLUGIN_URL', plugin_dir_url( AFFILIATES_FILE ) );

/**
 * @var int cookie expiration multiplier, 1 day
 */
define( 'AFFILIATES_COOKIE_TIMEOUT_BASE', 86400 );

/**
 * @var int default timeout in days
 */
define( 'AFFILIATES_COOKIE_TIMEOUT_DAYS', 30 );

/**
 * @var string this cookie stores the affiliate id
 */
define( 'AFFILIATES_COOKIE_NAME', 'wp_affiliates' );

/**
 * @var string (default) affiliate URL parameter name
 */
define( 'AFFILIATES_PNAME', 'affiliates' );

/**
 * @var string affiliates form nonce name
 */
define( 'AFFILIATES_ADMIN_AFFILIATES_NONCE', 'affiliates-nonce' );

/**
 * @var string affiliates table prefix
 */
define( 'AFFILIATES_TP', 'aff_' );

/**
 * @var boolean Store robot hits
 */
define( 'AFFILIATES_RECORD_ROBOT_HITS', false );

/**
 * @var string expander showing expandable state
 */
define( 'AFFILIATES_EXPANDER_EXPAND', '[+] ' );

/**
 * @var string expander showing retractable state
 */
define( 'AFFILIATES_EXPANDER_RETRACT', '[-] ' );

/**
 * @var string determines the affiliates part in a URL
 */
define( 'AFFILIATES_REGEX_PATTERN', 'affiliates/([^/]+)/?$' );

/**
 * @var string the direct type is used to determine a default pseudo-affiliate, i.e. the site owner 
 */
define( 'AFFILIATES_DIRECT_TYPE', 'direct' );

/**
 * @var string the name of the pseudo-affiliate
 */
define( 'AFFILIATES_DIRECT_NAME', 'Direct' );

// translators: the name of the pseudo-affiliate
_x( 'Direct', 'pseudo-affiliate name', 'affiliates' );

/**
 * @var int ids are not encoded
 */
define( 'AFFILIATES_NO_ID_ENCODING', 1 );

/**
 * @var string ids are MD5-encoded
 */
define( 'AFFILIATES_MD5_ID_ENCODING', 2 );

// affiliates administrative capabilities
/**
 * @var string allows access to the affiliates section
 */
define( 'AFFILIATES_ACCESS_AFFILIATES', 'aff_access' );

/**
 * @var string allows to administer affiliates (create, delete, view)
 */
define( 'AFFILIATES_ADMINISTER_AFFILIATES', 'aff_admin_affiliates');

/**
 * @var string allows to administer plugin options
 */
define( 'AFFILIATES_ADMINISTER_OPTIONS', 'aff_admin_options');

/**
 * @var int generated password length
 */
define( 'AFFILIATES_REGISTRATION_PASSWORD_LENGTH', 12 );

/** 
 * @var int decimal places for referral amount
 */
define( 'AFFILIATES_REFERRAL_AMOUNT_DECIMALS', apply_filters( 'affiliates_referral_amount_decimals', 2 ) );

/**
 * @var int number of characters in currency id
 */
define( 'AFFILIATES_REFERRAL_CURRENCY_ID_LENGTH', 3 );

/**
 * @var string referral status pending
 */
define( 'AFFILIATES_REFERRAL_STATUS_PENDING', 'pending' );

/**
 * @var string referral status accepted
 */
define( 'AFFILIATES_REFERRAL_STATUS_ACCEPTED', 'accepted' );

/**
 * @var string referral status rejected
 */
define( 'AFFILIATES_REFERRAL_STATUS_REJECTED', 'rejected' );

/**
 * @var string referral status closed
 */
define( 'AFFILIATES_REFERRAL_STATUS_CLOSED', 'closed' );

/**
 * @var string affiliate status active
 */
define( 'AFFILIATES_AFFILIATE_STATUS_ACTIVE', 'active' );

/**
 * @var string affiliate status pending
 */
define( 'AFFILIATES_AFFILIATE_STATUS_PENDING', 'pending' );

/**
 * @var string affiliate status deleted
 */
define( 'AFFILIATES_AFFILIATE_STATUS_DELETED', 'deleted' );

/**
 * @var string qualifies as affiliate
 */
//define( 'AFFILIATES_IS_AFFILIATE', 'aff_is_affiliate' );

// constants used in affiliates-admin-hits.php & affiliaets-admin-referrals.php
define( 'AFFILIATES_HITS_PER_PAGE', 10 );
define( 'AFFILIATES_ADMIN_OVERVIEW_NONCE',    'affiliates-admin-overview-nonce' );
define( 'AFFILIATES_ADMIN_HITS_NONCE_1',      'affiliates-admin-hits-nonce-1' );
define( 'AFFILIATES_ADMIN_HITS_NONCE_2',      'affiliates-admin-hits-nonce-2' );
define( 'AFFILIATES_ADMIN_HITS_FILTER_NONCE', 'affiliates-admin-hits-filter-nonce' );
define( 'AFFILIATES_ADMIN_REFERRALS_NONCE',   'affiliates-admin-referrals-nonce' );
