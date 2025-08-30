<?php
/**
 * wp-init.php
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
 * @since affiliates 1.1.2
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

global $affiliates_options, $affiliates_version, $affiliates_admin_messages;

if ( !isset( $affiliates_admin_messages ) ) {
	$affiliates_admin_messages = array();
}

if ( !isset( $affiliates_version ) ) {
	$affiliates_version = AFFILIATES_CORE_VERSION;
}

// base class
require_once AFFILIATES_CORE_LIB . '/class-affiliates.php';

// math class
require_once AFFILIATES_CORE_LIB . '/class-affiliates-math.php';

// options
require_once AFFILIATES_CORE_LIB . '/class-affiliates-options.php';
if ( $affiliates_options == null ) {
	$affiliates_options = new Affiliates_Options();
}

// utilities
require_once AFFILIATES_CORE_LIB . '/class-affiliates-utility.php';
require_once AFFILIATES_CORE_LIB . '/class-affiliates-ui-elements.php';
require_once AFFILIATES_CORE_LIB . '/class-affiliates-log.php';

// ajax
require_once AFFILIATES_CORE_LIB . '/class-affiliates-ajax.php';

// robot cleaner
require_once AFFILIATES_CORE_LIB . '/class-affiliates-robot-cleaner.php';

// forms, shortcodes, widgets
require_once AFFILIATES_CORE_LIB . '/class-affiliates-contact.php';
require_once AFFILIATES_CORE_LIB . '/class-affiliates-registration.php';
require_once AFFILIATES_CORE_LIB . '/class-affiliates-registration-widget.php';
require_once AFFILIATES_CORE_LIB . '/class-affiliates-shortcodes.php'; // don't make it conditional on is_admin(), get_total() is used in Manage Affiliates

// templates and dashboard
require_once AFFILIATES_CORE_LIB . '/class-affiliates-templates.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/interface-affiliates-dashboard.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-factory.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-section-factory.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/interface-affiliates-dashboard-section.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/interface-affiliates-dashboard-section-table.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-section.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-section-table.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-login.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-login-block.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-login-shortcode.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-registration.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-registration-block.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-registration-shortcode.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-overview.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-overview-block.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-overview-shortcode.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-earnings.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-earnings-block.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-earnings-shortcode.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-profile.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-profile-block.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-profile-shortcode.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-block.php';
require_once AFFILIATES_CORE_LIB . '/dashboard/class-affiliates-dashboard-shortcode.php';

// built-in user registration integration
if (
	( get_option( 'aff_user_registration_enabled', 'no' ) == 'yes' ) ||
	( get_option( 'aff_customer_registration_enabled', 'no' ) == 'yes' )
) {
	require_once AFFILIATES_CORE_LIB . '/class-affiliates-user-registration.php';
}

// affiliates excluded
require_once AFFILIATES_CORE_LIB . '/class-affiliates-exclusion.php';

// affiliates notifications
require_once AFFILIATES_CORE_LIB . '/class-affiliates-notifications.php';
if ( is_admin() ) {
	if ( AFFILIATES_PLUGIN_NAME == 'affiliates' ) {
		require_once AFFILIATES_CORE_LIB . '/class-affiliates-admin-notifications.php';
	}
}

// affiliates notice
require_once AFFILIATES_CORE_LIB . '/class-affiliates-notice.php';

add_action( 'widgets_init', 'affiliates_widgets_init' );

/**
 * Register widgets
 */
function affiliates_widgets_init() {
	register_widget( 'Affiliates_Contact' );
	register_widget( 'Affiliates_Registration_Widget' );
}

add_action( 'admin_init', 'affiliates_admin_init' );

/**
 * Hook into admin_init. Used to get our styles at the right place.
 * @see affiliates_admin_menu()
 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_script#Load_scripts_only_on_plugin_pages
 */
function affiliates_admin_init() {
	global $affiliates_version;
	wp_register_style( 'smoothness', AFFILIATES_PLUGIN_URL . 'css/smoothness/jquery-ui.min.css', array(), $affiliates_version );
	wp_register_style( 'affiliates_admin', AFFILIATES_PLUGIN_URL . 'css/affiliates_admin.css', array(), $affiliates_version );
}

/**
 * Load styles.
 * @see affiliates_admin_menu()
 */
function affiliates_admin_print_styles() {
	wp_enqueue_style( 'smoothness' );
	wp_enqueue_style( 'affiliates_admin' );
}

/**
 * Load scripts.
 */
function affiliates_admin_print_scripts() {
	global $post_type, $affiliates_version;

	// load datepicker scripts for all
	wp_enqueue_script( 'datepicker', AFFILIATES_PLUGIN_URL . 'js/jquery-ui.min.js', array( 'jquery', 'jquery-ui-core' ), $affiliates_version );
	wp_enqueue_script( 'datepickers', AFFILIATES_PLUGIN_URL . 'js/datepickers.js', array( 'jquery', 'jquery-ui-core', 'datepicker' ), $affiliates_version );
	// add more dates used for trips and events
	wp_enqueue_script( 'affiliates', AFFILIATES_PLUGIN_URL . 'js/affiliates.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-button' ), $affiliates_version );
	// and thus are the translations of the buttons used

	wp_enqueue_script( 'excanvas', AFFILIATES_PLUGIN_URL . 'js/graph/flot/excanvas.min.js', array( 'jquery' ), $affiliates_version );
	wp_enqueue_script( 'flot', AFFILIATES_PLUGIN_URL . 'js/graph/flot/jquery.flot.min.js', array( 'jquery' ), $affiliates_version );
	wp_enqueue_script( 'flot-resize', AFFILIATES_PLUGIN_URL . 'js/graph/flot/jquery.flot.resize.min.js', array( 'jquery', 'flot' ), $affiliates_version );

	// Selectize
	$screen = get_current_screen();
	if ( isset( $screen->id ) ) {
		switch( $screen->id ) {
			case 'affiliates_page_affiliates-admin-referrals' :
			case 'affiliates_page_affiliates-admin-hits-uri' :
			case 'affiliates_page_affiliates-admin-hits-affiliate' :
			case 'affiliates_page_affiliates-admin-hits' :
				Affiliates_UI_Elements::enqueue( 'select' );
				break;
		}
	}

//	echo '
//		<script type="text/javascript">
//			var fooText = "' . __( 'Foo', 'affiliates' ) . '";
//		</script>
//		';
}

add_action( 'wp_enqueue_scripts', 'affiliates_wp_enqueue_scripts' );
function affiliates_wp_enqueue_scripts() {
	global $affiliates_version;
	wp_register_style( 'affiliates', AFFILIATES_PLUGIN_URL . 'css/affiliates.css', array(), $affiliates_version );
}
// ---

register_activation_hook( AFFILIATES_FILE, 'affiliates_activate' );
add_action( 'wpmu_new_blog', 'affiliates_wpmu_new_blog', 10, 2 );
add_action( 'delete_blog', 'affiliates_delete_blog', 10, 2 );

add_action( 'init', 'affiliates_version_check' );
function affiliates_version_check() {
	global $affiliates_version, $affiliates_admin_messages;
	$previous_version = get_option( 'affiliates_plugin_version', '' );
	$affiliates_version = AFFILIATES_CORE_VERSION;
	if ( version_compare( $previous_version, $affiliates_version ) < 0 ) {
		$update_result = affiliates_update( $previous_version );
		if ( $update_result === true ) {
			update_option( 'affiliates_plugin_version', $affiliates_version );
		} else {
			if ( $update_result === false ) {
				affiliates_log_error( 'There were errors during update (core) – this might only be a temporary issue, unless this message comes up permanently.' );
			}
		}
	}
}

function affiliates_admin_notices() {
	global $affiliates_admin_messages;
	if ( !empty( $affiliates_admin_messages ) ) {
		foreach ( $affiliates_admin_messages as $msg ) {
			echo $msg;
		}
	}
}

add_action( 'admin_notices', 'affiliates_admin_notices' );

/**
 * Tasks performed upon plugin activation.
 */
function affiliates_activate( $network_wide = false ) {
	if ( is_multisite() && $network_wide ) {
		$blog_ids = affiliates_get_blogs();
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			affiliates_setup();
			restore_current_blog();
		}
	} else {
		affiliates_setup();
	}
}

/**
 * Setup for a new creat blog.
 * @param int $blog_id
 */
function affiliates_wpmu_new_blog( $blog_id, $user_id ) {
	if ( is_multisite() ) {
		if ( affiliates_is_sitewide_plugin() ) {
			switch_to_blog( $blog_id );
			affiliates_setup();
			restore_current_blog();
		}
	}
}

/**
 * Clean up for a given blog.
 *
 * @param int $blog_id
 * @param boolean $drop
 */
function affiliates_delete_blog( $blog_id, $drop = false ) {
	if ( is_multisite() ) {
		if ( affiliates_is_sitewide_plugin() ) {
			switch_to_blog( $blog_id );
			affiliates_cleanup( $drop );
			restore_current_blog();
		}
	}
}

/**
 * Returns true if the plugin is site-wide.
 * @return boolean true if site-wide plugin
 */
function affiliates_is_sitewide_plugin() {
	$result = false;
	if ( is_multisite() ) {
		$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$active_sitewide_plugins = array_keys( $active_sitewide_plugins );
		$components = explode( DIRECTORY_SEPARATOR, AFFILIATES_FILE );
		$plugin = '';
		$n = count( $components );
		if ( isset( $components[$n - 2] ) ) {
			$plugin .= $components[$n - 2] . DIRECTORY_SEPARATOR;
		}
		$plugin .= $components[$n - 1];
		$result = in_array( $plugin, $active_sitewide_plugins );
	}
	return $result;
}

/**
 * Retrieve current blogs' ids.
 * @return array blog ids
 */
function affiliates_get_blogs() {
	global $wpdb;
	$result = array();
	if ( is_multisite() ) {
		$blogs = $wpdb->get_results( $wpdb->prepare(
			"SELECT blog_id FROM $wpdb->blogs WHERE site_id = %d AND archived = '0' AND spam = '0' AND deleted = '0'",
			$wpdb->siteid
		) );
		if ( is_array( $blogs ) ) {
			foreach( $blogs as $blog ) {
				$result[] = $blog->blog_id;
			}
		}
	} else {
		$result[] = get_current_blog_id();
	}
	return $result;
}

/**
 * Create tables and prepare data.
 */
function affiliates_setup() {
	global $wpdb, $wp_roles;

	_affiliates_set_default_capabilities();

	$charset_collate = '';
	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	}
	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE $wpdb->collate";
	}

	$affiliates_table = _affiliates_get_tablename('affiliates');
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $affiliates_table . "'" ) != $affiliates_table ) {
		$queries[] = "CREATE TABLE " . $affiliates_table . "(
				affiliate_id bigint(20) unsigned NOT NULL auto_increment,
				name         varchar(100) NOT NULL,
				email        varchar(512) default NULL,
				from_date    date NOT NULL,
				thru_date    date default NULL,
				status       varchar(10) NOT NULL DEFAULT 'active',
				type         varchar(10) NULL,
				PRIMARY KEY  (affiliate_id),
				INDEX        affiliates_afts (affiliate_id, from_date, thru_date, status),
				INDEX        affiliates_sft (status, from_date, thru_date)
			) $charset_collate;";
	}

	// email @see http://tools.ietf.org/html/rfc5321
	// 2.3.11. Mailbox and Address
	// ... The standard mailbox naming convention is defined to
	// be "local-part@domain"; ...
	// 4.1.2. Command Argument Syntax
	// ...
	// Mailbox        = Local-part "@" ( Domain / address-literal )
	// ...
	// 4.5.3. Sizes and Timeouts
	// 4.5.3.1.1. Local-part
	// The maximum total length of a user name or other local-part is 64 octets.
	// 4.5.3.1.2. Domain
	// The maximum total length of a domain name or number is 255 octets.
	// 4.5.3.1.3. Path
	// The maximum total length of a reverse-path or forward-path is 256
	// octets (including the punctuation and element separators).
	// So the maximum size of an email address is ... ?
	// 64 + 1 + 255 = 320 octets
	// Then again, people change their minds ... we'll assume 512 as sufficient.
	// Note: WP's user.user_email is varchar(100) @see wp-admin/includes/schema.php

	// IPv6 addr
	// FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF = 340282366920938463463374607431768211455
	// Note that ipv6 is not part of the PK but can be handled using
	// the lower bits of the ipv6 address to fill in ip and using the
	// complete IPv6 address on ipv6.
	// Note also, that currently Affiliates does NOT use ipv6.

	$referrals_table = _affiliates_get_tablename( 'referrals' );
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $referrals_table . "'" ) != $referrals_table ) {
		$queries[] = "CREATE TABLE " . $referrals_table . "(
				referral_id  BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				affiliate_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
				campaign_id  BIGINT(20) UNSIGNED DEFAULT NULL,
				post_id      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
				datetime     DATETIME NOT NULL,
				description  VARCHAR(5000),
				ip           INT(10) UNSIGNED DEFAULT NULL,
				ipv6         DECIMAL(39,0) UNSIGNED DEFAULT NULL,
				user_id      BIGINT(20) UNSIGNED DEFAULT NULL,
				amount       DECIMAL(24,6) DEFAULT NULL,
				reference_amount DECIMAL(24,6) DEFAULT NULL,
				currency_id  CHAR(3) DEFAULT NULL,
				data         LONGTEXT DEFAULT NULL,
				status       VARCHAR(10) NOT NULL DEFAULT '" . AFFILIATES_REFERRAL_STATUS_ACCEPTED . "',
				type         VARCHAR(10) NULL,
				reference    VARCHAR(100) DEFAULT NULL,
				hit_id       BIGINT(20) UNSIGNED DEFAULT NULL,
				integration  VARCHAR(255) DEFAULT NULL,
				PRIMARY KEY  (referral_id),
				INDEX        aff_referrals_apd (affiliate_id, post_id, datetime),
				INDEX        aff_referrals_da  (datetime, affiliate_id),
				INDEX        aff_referrals_sda (status, datetime, affiliate_id),
				INDEX        aff_referrals_tda (type, datetime, affiliate_id),
				INDEX        aff_referrals_ref (reference(20)),
				INDEX        aff_referrals_ac  (affiliate_id, campaign_id),
				INDEX        aff_referrals_c   (campaign_id),
				INDEX        aff_referrals_h   (hit_id),
				INDEX        integration (integration(20))
			) $charset_collate;";
		// @see http://bugs.mysql.com/bug.php?id=27645 as of now (2011-03-19) NOW() can not be specified as the default value for a datetime column
	}

	$referral_items_table = _affiliates_get_tablename( 'referral_items' );
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $referral_items_table . "'" ) != $referral_items_table ) {
		$queries[] = "CREATE TABLE " . $referral_items_table . "(
			referral_item_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			referral_id      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			amount           DECIMAL(24,6) DEFAULT NULL,
			line_amount      DECIMAL(24,6) DEFAULT NULL,
			currency_id      CHAR(3) DEFAULT NULL,
			rate_id          BIGINT(20) UNSIGNED DEFAULT NULL,
			type             VARCHAR(20) NULL,
			reference        VARCHAR(100) DEFAULT NULL,
			object_id        BIGINT(20) UNSIGNED DEFAULT NULL,
			PRIMARY KEY      (referral_item_id),
			INDEX            referral_id (referral_id),
			INDEX            reference (reference(20)),
			INDEX            object_id (object_id)
		) $charset_collate;";
	}

	// IMPORTANT:
	// datetime -- records the datetime with respect to the server's timezone
	// date and datetime are are also with respect to the server's timezone
	// date is used for better performance in queries, datetime to provide detail when viewed
	// We DO use the datetime (adjusted to the user's timezone using
	// DateHelper's s2u() function to display the date and time
	// in accordance to the user's date and time.
	// @todo ip/ipv6 (also for referrals table) : create new table and map hits.ip_id to ip.ip_id instead of storing those
	$hits_table = _affiliates_get_tablename( 'hits' );
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $hits_table . "'" ) != $hits_table ) {
		$queries[] = "CREATE TABLE " . $hits_table . "(
				hit_id          BIGINT(20) UNSIGNED NOT NULL auto_increment,
				hash            CHAR(64) DEFAULT NULL,
				affiliate_id    BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
				campaign_id     BIGINT(20) UNSIGNED DEFAULT NULL,
				date            DATE NOT NULL,
				datetime        DATETIME NOT NULL,
				ip              INT(10) UNSIGNED NOT NULL DEFAULT 0,
				src_uri_id      BIGINT(20) UNSIGNED DEFAULT NULL,
				dest_uri_id     BIGINT(20) UNSIGNED DEFAULT NULL,
				user_agent_id   BIGINT(20) UNSIGNED DEFAULT NULL,
				is_robot        TINYINT DEFAULT 0,
				user_id         BIGINT(20) UNSIGNED DEFAULT NULL,
				type            VARCHAR(10) DEFAULT NULL,
				PRIMARY KEY     (hit_id),
				INDEX           hash (hash),
				INDEX           idx_date (date),
				INDEX           aff_hits_acm (affiliate_id, campaign_id),
				INDEX           aff_hits_src_uri (src_uri_id),
				INDEX           aff_hits_dest_uri (dest_uri_id),
				INDEX           aff_hits_ua (user_agent_id)
			) $charset_collate;";
	}

	$uris_table = _affiliates_get_tablename( 'uris' );
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $uris_table . "'" ) != $uris_table ) {
		$queries[] = "CREATE TABLE " . $uris_table . "(
				uri_id      BIGINT(20) UNSIGNED NOT NULL auto_increment,
				uri         VARCHAR(2048) NOT NULL,
				type        VARCHAR(10) DEFAULT NULL,
				PRIMARY KEY (uri_id),
				INDEX       uri (uri(100)),
				INDEX       type (type)
			) $charset_collate;";
	}
	// add the user_agents table
	$user_agents_table = _affiliates_get_tablename( 'user_agents' );
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $user_agents_table . "'" ) != $user_agents_table ) {
		$queries[] = "CREATE TABLE " . $user_agents_table . "(
				user_agent_id BIGINT(20) UNSIGNED NOT NULL auto_increment,
				user_agent    VARCHAR(255) NOT NULL,
				PRIMARY KEY   (user_agent_id),
				INDEX         user_agent (user_agent(32))
				) $charset_collate;";
	}
	$robots_table = _affiliates_get_tablename( 'robots' );
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $robots_table . "'" ) != $robots_table ) {
		$queries[] = "CREATE TABLE " . $robots_table . "(
				robot_id    BIGINT(20) UNSIGNED NOT NULL auto_increment,
				name        VARCHAR(100) NOT NULL,
				PRIMARY KEY (robot_id),
				INDEX       aff_robots_n (name)
			) $charset_collate;";
	}
	$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $affiliates_users_table . "'" ) != $affiliates_users_table ) {
		$queries[] = "CREATE TABLE " . $affiliates_users_table . "(
				affiliate_id BIGINT(20) UNSIGNED NOT NULL,
				user_id      BIGINT(20) UNSIGNED NOT NULL,
				PRIMARY KEY (affiliate_id, user_id)
			) $charset_collate;";
	}
	if ( !empty( $queries ) ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $queries );
	}
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $affiliates_table . "'" ) == $affiliates_table ) {
		$today = date( 'Y-m-d', time() );
		$direct = intval( $wpdb->get_var( "SELECT COUNT(affiliate_id) FROM $affiliates_table WHERE type = '" . AFFILIATES_DIRECT_TYPE . "';" ) );
		if ( $direct <= 0 ) {
			$wpdb->query( "INSERT INTO $affiliates_table (name, from_date, type) VALUES ('" . AFFILIATES_DIRECT_NAME . "','$today','" . AFFILIATES_DIRECT_TYPE . "');" );
		}
	}

	affiliates_update();
	affiliates_update_rewrite_rules();
}

/**
 * Determines the default capabilities for the administrator role.
 * In lack of an administrator role, these capabilities are assigned
 * to any role that can manage_options.
 * This is also used to assure a minimum set of capabilities is
 * assigned to an appropriate role, so that it's not possible
 * to lock yourself out (although deactivating and then activating
 * the plugin would have the same effect but with the danger of
 * deleting all plugin data).
 * @param boolean $activate defaults to true, when this function is called upon plugin activation
 * @access private
 */
function _affiliates_set_default_capabilities() {
	global $wp_roles;
	// The administrator role should be there, if it's not, assign privileges to
	// any role that can manage_options:
	if ( $administrator_role = $wp_roles->get_role( 'administrator' ) ) {
		$administrator_role->add_cap( AFFILIATES_ACCESS_AFFILIATES );
		$administrator_role->add_cap( AFFILIATES_ADMINISTER_AFFILIATES );
		$administrator_role->add_cap( AFFILIATES_ADMINISTER_OPTIONS );
	} else {
		foreach ( $wp_roles->role_objects as $role ) {
			if ($role->has_cap( 'manage_options' ) ) {
				$role->add_cap( AFFILIATES_ACCESS_AFFILIATES );
				$role->add_cap( AFFILIATES_ADMINISTER_AFFILIATES );
				$role->add_cap( AFFILIATES_ADMINISTER_OPTIONS );
			}
		}
	}
}

/**
 * There must be at least one role with the minimum set of capabilities
 * to access and manage the Affiliates plugin's options.
 * If this condition is not met, the minimum set of capabilities is
 * reestablished.
 */
function _affiliates_assure_capabilities() {
	global $wp_roles;
	$complies = false;
	$roles = $wp_roles->role_objects;
	foreach( $roles as $role ) {
		if ( $role->has_cap( AFFILIATES_ACCESS_AFFILIATES ) && ( $role->has_cap( AFFILIATES_ADMINISTER_OPTIONS ) ) ) {
			$complies = true;
			break;
		}
	}
	if ( !$complies ) {
		_affiliates_set_default_capabilities();
	}
}

/**
 * Update from a previous version or repair on activation (within 2.x).
 *
 * This is called from affiliates_setup() on plugin activation and affiliates_version_check()
 * when a previous version is detected.
 *
 * @param string $previous_version
 *
 * @return boolean or null if update procedure was skipped (DOING_AJAX or DOING_CRON)
 */
function affiliates_update( $previous_version = null ) {

	global $wpdb;

	$result  = true;
	$queries = array();

	if (
		defined( 'DOING_AJAX' ) && DOING_AJAX ||
		defined( 'DOING_CRON' ) && DOING_CRON
	) {
		return null;
	}

	$charset_collate = '';
	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	}
	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE $wpdb->collate";
	}

	$hits_table = _affiliates_get_tablename( 'hits' );
	$column     = $wpdb->get_row( "SHOW COLUMNS FROM $hits_table LIKE 'campaign_id'" );
	if ( empty( $column ) ) {
		$queries[] = "ALTER TABLE " . $hits_table . "
		ADD COLUMN campaign_id BIGINT(20) UNSIGNED DEFAULT NULL,
		ADD INDEX aff_hits_acm (affiliate_id, campaign_id);";
	}
	$column = $wpdb->get_row( "SHOW COLUMNS FROM $hits_table LIKE 'hit_id'" );
	if ( empty( $column ) ) {
		$queries[] = "ALTER TABLE " . $hits_table . "
		ADD COLUMN hit_id BIGINT(20) UNSIGNED NOT NULL auto_increment,
		ADD COLUMN hash CHAR(64) DEFAULT NULL,
		DROP PRIMARY KEY,
		ADD PRIMARY KEY (hit_id),
		ADD INDEX hash (hash);";
	}

	// from 4.0.0 drop indexes : aff_hits_dtd, aff_hits_ddt, aid_d_t_ip
	$index = $wpdb->get_results( "SHOW INDEX FROM $hits_table WHERE Key_name = 'aff_hits_dtd'" );
	if ( is_array( $index ) && count( $index ) > 0 ) {
		$queries[] = "ALTER TABLE $hits_table DROP INDEX aff_hits_dtd;";
	}
	$index = $wpdb->get_results( "SHOW INDEX FROM $hits_table WHERE Key_name = 'aff_hits_ddt'" );
	if ( is_array( $index ) && count( $index ) > 0 ) {
		$queries[] = "ALTER TABLE $hits_table DROP INDEX aff_hits_ddt;";
	}
	$index = $wpdb->get_results( "SHOW INDEX FROM $hits_table WHERE Key_name = 'aid_d_t_ip'" );
	if ( is_array( $index ) && count( $index ) > 0 ) {
		$queries[] = "ALTER TABLE $hits_table DROP INDEX aid_d_t_ip;";
	}
	// from 4.0.0 add index : idx_date
	$index = $wpdb->get_results( "SHOW INDEX FROM $hits_table WHERE Key_name = 'idx_date'" );
	if ( $index === null || is_array( $index ) && count( $index ) === 0 ) {
		$queries[] = "ALTER TABLE $hits_table ADD INDEX idx_date (date);";
	}
	// from 4.0.0 drop the time column
	$column = $wpdb->get_row( "SHOW COLUMNS FROM $hits_table LIKE 'time'" );
	if ( !empty( $column ) ) {
		$queries[] = "ALTER TABLE $hits_table DROP COLUMN time;";
	}
	// from 4.0.0 drop the ipv6 column
	$column = $wpdb->get_row( "SHOW COLUMNS FROM $hits_table LIKE 'ipv6'" );
	if ( !empty( $column ) ) {
		$queries[] = "ALTER TABLE $hits_table DROP COLUMN ipv6;";
	}
	// from 4.0.0 drop the count column
	$column = $wpdb->get_row( "SHOW COLUMNS FROM $hits_table LIKE 'count'" );
	if ( !empty( $column ) ) {
		$queries[] = "ALTER TABLE $hits_table DROP COLUMN count;";
	}

	// URIs ... from 2.17.0
	// add the uris table
	$uris_table = _affiliates_get_tablename( 'uris' );
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $uris_table . "'" ) != $uris_table ) {
		$queries[] = "CREATE TABLE " . $uris_table . "(
		uri_id      BIGINT(20) UNSIGNED NOT NULL auto_increment,
		uri         VARCHAR(2048) NOT NULL,
		type        VARCHAR(10) DEFAULT NULL,
		PRIMARY KEY (uri_id),
		INDEX       uri (uri(100)),
		INDEX       type (type)
		) $charset_collate;";
	}

	// add uri columns and indexes to the hits table
	$column = $wpdb->get_row( "SHOW COLUMNS FROM $hits_table LIKE 'src_uri_id'" );
	if ( empty( $column ) ) {
		$queries[] = "ALTER TABLE " . $hits_table . "
		ADD COLUMN src_uri_id BIGINT(20) UNSIGNED DEFAULT NULL,
		ADD COLUMN dest_uri_id BIGINT(20) UNSIGNED DEFAULT NULL,
		ADD INDEX aff_hits_src_uri (src_uri_id),
		ADD INDEX aff_hits_dest_uri (dest_uri_id);";
	}
	// add the user_agents table
	$user_agents_table = _affiliates_get_tablename( 'user_agents' );
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $user_agents_table . "'" ) != $user_agents_table ) {
		$queries[] = "CREATE TABLE " . $user_agents_table . "(
		user_agent_id BIGINT(20) UNSIGNED NOT NULL auto_increment,
		user_agent    VARCHAR(255) NOT NULL,
		PRIMARY KEY   (user_agent_id),
		INDEX         user_agent (user_agent(32))
		) $charset_collate;";
	}
	// add the user_agent_id column to the hits table
	$column = $wpdb->get_row( "SHOW COLUMNS FROM $hits_table LIKE 'user_agent_id'" );
	if ( empty( $column ) ) {
		$queries[] = "ALTER TABLE " . $hits_table . "
		ADD COLUMN user_agent_id BIGINT(20) UNSIGNED DEFAULT NULL,
		ADD INDEX aff_hits_ua (user_agent_id);";
	}

	$referrals_table = _affiliates_get_tablename( 'referrals' );
	$column          = $wpdb->get_row( "SHOW COLUMNS FROM $referrals_table LIKE 'campaign_id'" );
	if ( empty( $column ) ) {
		$queries[] = "ALTER TABLE " . $referrals_table . "
		ADD COLUMN campaign_id BIGINT(20) UNSIGNED DEFAULT NULL,
		ADD INDEX aff_referrals_ac (affiliate_id, campaign_id),
		ADD INDEX aff_referrals_c (campaign_id);";
	}
	$column = $wpdb->get_row( "SHOW COLUMNS FROM $referrals_table LIKE 'hit_id'" );
	if ( empty( $column ) ) {
		$queries[] = "ALTER TABLE " . $referrals_table . "
		ADD COLUMN hit_id BIGINT(20) UNSIGNED DEFAULT NULL,
		ADD INDEX aff_referrals_h (hit_id);";
	}

	// Referrals amount precision to DECIMAL(24,6) ... from 2.18.0
	if ( !empty( $previous_version ) && version_compare( $previous_version, '2.18.0' ) < 0 ) {
		$queries[] = "ALTER TABLE " . $referrals_table . "
		MODIFY amount DECIMAL(24,6) DEFAULT NULL;";
	}

	// add the reference_amount and integration columns to the referrals table ... from 3.0.0
	// if ( !empty( $previous_version ) && version_compare( $previous_version, '3.0.0' ) < 0 ) {
	$column = $wpdb->get_row( "SHOW COLUMNS FROM $referrals_table LIKE 'reference_amount'" );
	if ( empty( $column ) ) {
		$queries[] = "ALTER TABLE " . $referrals_table . "
		ADD COLUMN reference_amount DECIMAL(24,6) DEFAULT NULL,
		ADD COLUMN integration VARCHAR(255) DEFAULT NULL,
		ADD INDEX integration (integration(20));";
	}
	// }

	// add the referral_items table ... from 3.0.0
	$referral_items_table = _affiliates_get_tablename( 'referral_items' );
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $referral_items_table . "'" ) != $referral_items_table ) {
		$queries[] = "CREATE TABLE " . $referral_items_table . "(
			referral_item_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			referral_id      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			amount           DECIMAL(24,6) DEFAULT NULL,
			line_amount      DECIMAL(24,6) DEFAULT NULL,
			currency_id      CHAR(3) DEFAULT NULL,
			rate_id          BIGINT(20) UNSIGNED DEFAULT NULL,
			type             VARCHAR(20) NULL,
			reference        VARCHAR(100) DEFAULT NULL,
			object_id        BIGINT(20) UNSIGNED DEFAULT NULL,
			PRIMARY KEY      (referral_item_id),
			INDEX            referral_id (referral_id),
			INDEX            reference (reference(20)),
			INDEX            object_id (object_id)
		) $charset_collate;";
	}

	// MySQL 5.7.3 PK requirements
	if ( !empty( $previous_version ) && version_compare( $previous_version, '2.15.10' ) < 0 ) {
		$queries[] = "ALTER TABLE " . $hits_table . "
		MODIFY ip INT(10) UNSIGNED NOT NULL DEFAULT 0;";
	}

	foreach ( $queries as $query ) {
		// don't use dbDelta, it doesn't handle ALTER
		if ( $wpdb->query( $query ) === false ) {
			$result = false;
		}
	}

	if ( !empty( $previous_version ) && version_compare( $previous_version, '2.1.5' ) < 0 ) {
		affiliates_update_rewrite_rules();
	}
	return $result;
}

register_deactivation_hook( AFFILIATES_FILE, 'affiliates_deactivate' );

/**
 * Drop tables and clear data if the plugin is deactivated.
 * This will happen only if the user chooses to delete data upon deactivation.
 */
function affiliates_deactivate( $network_wide = false ) {
	if ( is_multisite() && $network_wide ) {
		if ( get_option( 'aff_delete_network_data', false ) ) {
			$blog_ids = affiliates_get_blogs();
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				affiliates_cleanup( true );
				restore_current_blog();
			}
		}
	} else {
		affiliates_cleanup();
	}
}

/**
 * Cleans up tables, data, capabilities if the option is set.
 *
 * @param boolean $delete force deletion
 */
function affiliates_cleanup( $delete = false ) {
	global $wpdb, $affiliates_options, $wp_roles;

	$delete_data = get_option( 'aff_delete_data', false ) || $delete;
	if ( $delete_data ) {
		foreach ( $wp_roles->role_objects as $role ) {
			$role->remove_cap( AFFILIATES_ACCESS_AFFILIATES );
			$role->remove_cap( AFFILIATES_ADMINISTER_AFFILIATES );
			$role->remove_cap( AFFILIATES_ADMINISTER_OPTIONS );
		}
		$wpdb->query('DROP TABLE IF EXISTS ' . _affiliates_get_tablename( 'referral_items' ) );
		$wpdb->query('DROP TABLE IF EXISTS ' . _affiliates_get_tablename( 'referrals' ) );
		$wpdb->query('DROP TABLE IF EXISTS ' . _affiliates_get_tablename( 'hits' ) );
		$wpdb->query('DROP TABLE IF EXISTS ' . _affiliates_get_tablename( 'uris' ) );
		$wpdb->query('DROP TABLE IF EXISTS ' . _affiliates_get_tablename( 'user_agents' ) );
		$wpdb->query('DROP TABLE IF EXISTS ' . _affiliates_get_tablename( 'affiliates' ) );
		$wpdb->query('DROP TABLE IF EXISTS ' . _affiliates_get_tablename( 'robots' ) );
		$wpdb->query('DROP TABLE IF EXISTS ' . _affiliates_get_tablename( 'affiliates_users' ) );
		flush_rewrite_rules();
		$affiliates_options->flush_options();
		delete_option( 'affiliates_plugin_version' );
		delete_option( 'aff_allow_auto' );
		delete_option( 'aff_allow_auto_coupons' );
		delete_option( 'aff_cookie_timeout_days' );
		delete_option( 'aff_customer_registration_enabled' );
		delete_option( 'aff_default_referral_status' );
		delete_option( 'aff_delete_data' );
		delete_option( 'aff_delete_network_data' );
		delete_option( 'aff_duplicates' );
		delete_option( 'aff_id_encoding' );
		delete_option( 'aff_notify_admin' );
		delete_option( 'aff_pname' );
		delete_option( 'aff_redirect' );
		delete_option( 'aff_registration' );
		delete_option( 'aff_registration_terms_post_id' );
		delete_option( 'aff_registration_fields' );
		delete_option( 'aff_setup_hide' );
		delete_option( 'aff_status' );
		delete_option( 'aff_use_direct' );
		delete_option( 'aff_user_registration_amount' );
		delete_option( 'aff_user_registration_base_amount' );
		delete_option( 'aff_user_registration_currency' );
		delete_option( 'aff_user_registration_enabled' );
		delete_option( 'aff_user_registration_referral_status' );
		delete_site_option( 'affiliates-init-time' );
		delete_metadata( 'user', null, 'affiliates-hide-review-notice', null, true );
	}
}

add_action( 'init', 'affiliates_init' );

/**
 * Initialize.
 * Loads the plugin's translations.
 */
function affiliates_init() {
	load_plugin_textdomain( 'affiliates', null, AFFILIATES_PLUGIN_NAME . '/lib/core/languages' );
	if ( class_exists( 'Affiliates_Affiliate' ) && method_exists( 'Affiliates_Affiliate', 'register_attribute_filter' ) ) {
		Affiliates_Affiliate::register_attribute_filter( 'affiliates_attribute_filter' );
	}
	add_action( 'after_plugin_row_' . plugin_basename( AFFILIATES_FILE ), 'affiliates_after_plugin_row', 10, 3 );

	// translators: the name of the pseudo-affiliate
	_x( 'Direct', 'pseudo-affiliate name', 'affiliates' );
}

/**
 * Prints a warning when data is deleted on deactivation.
 *
 * @param string $plugin_file
 * @param array $plugin_data
 * @param string $status
 */
function affiliates_after_plugin_row( $plugin_file, $plugin_data, $status ) {
	if ( $plugin_file == plugin_basename( AFFILIATES_FILE ) ) {
		$delete_data         = get_option( 'aff_delete_data', false );
		$delete_network_data = get_option( 'aff_delete_network_data', false );
		if (
			( is_plugin_active( $plugin_file ) && $delete_data && current_user_can( 'install_plugins' ) ) ||
			( is_plugin_active_for_network( $plugin_file ) && $delete_network_data  && current_user_can( 'manage_network_plugins' ) )
		) {
			echo '<tr class="active">';
			echo '<td>&nbsp;</td>';
			echo '<td colspan="2">';
			echo '<div style="border: 2px solid #dc3232; padding: 1em">';
			echo '<p>';
			echo '<strong>';
			echo esc_html( __( 'Warning!', 'affiliates' ) );
			echo '</strong>';
			echo '</p>';
			echo '<p>';
			echo esc_html( __( 'The plugin is configured to delete its data on deactivation.', 'affiliates' ) );
			echo '</p>';
			echo '</div>';
			echo '</td>';
			echo '</tr>';
		}
	}
}

add_filter( 'query_vars', 'affiliates_query_vars', 999 ); // filter acts late to avoid being messed with by others

/**
 * Register the affiliate query variable.
 * In addition to existing query variables, we'll use affiliates in the URL to track referrals.
 * @see affiliates_update_rewrite_rules() for the rewrite rule that matches the affiliates id
 */
function affiliates_query_vars( $query_vars ) {
	$pname = get_option( 'aff_pname', AFFILIATES_PNAME );
	$query_vars[] = $pname;
	return $query_vars;
}

/**
 * Add a rewrite rule for pretty links.
 * @deprecated
 */
function affiliates_update_rewrite_rules() {
	$pname = get_option( 'aff_pname', AFFILIATES_PNAME );
	add_rewrite_rule(
		str_replace( AFFILIATES_PNAME, $pname, AFFILIATES_REGEX_PATTERN ),
		'index.php?' . $pname . '=$matches[1]',
		'top'
	);
	flush_rewrite_rules();
}

add_action( 'parse_request', 'affiliates_parse_request' );

/**
 * Looks in the query variables and sets a cookie with the affiliate id.
 * Hook into parse_request.
 * @param WP $wp the WordPress environment
 * @link http://php.net/manual/en/function.setcookie.php
 */
function affiliates_parse_request( &$wp ) {

	global $wpdb, $affiliates_options, $affiliates_request_encoded_id;

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	$pname = get_option( 'aff_pname', AFFILIATES_PNAME );
	$affiliate_id = isset( $wp->query_vars[$pname] ) ? affiliates_check_affiliate_id_encoded( trim( $wp->query_vars[$pname] ) ) : null;
	if ( isset( $wp->query_vars[$pname] ) ) {

		$maybe_affiliate_id = $affiliate_id;
		if ( has_filter( 'affiliates_parse_request_affiliate_id' ) ) {
			/**
			 * @deprecated The affiliates_parse_request_affiliate_id filter is deprecated since 4.2.0, use affiliates_parse_request_assess_affiliate_id instead.
			 */
			$maybe_affiliate_id = intval( apply_filters( 'affiliates_parse_request_affiliate_id', $wp->query_vars[$pname], $affiliate_id ) );
		}

		// @since 4.2.0
		$maybe_affiliate_id = apply_filters(
			'affiliates_parse_request_assess_affiliate_id',
			$maybe_affiliate_id,
			$wp->query_vars[$pname],
			$pname
		);

		if ( intval( $maybe_affiliate_id ) > 0 && $maybe_affiliate_id !== $affiliate_id ) {
			$maybe_affiliate_id = affiliates_check_affiliate_id( $maybe_affiliate_id );
			if ( $maybe_affiliate_id !== false ) {
				$affiliate_id = $maybe_affiliate_id;
			}
		}
	}

	if ( $affiliate_id ) {
		$encoded_id = affiliates_encode_affiliate_id( $affiliate_id );
		$days = apply_filters( 'affiliates_cookie_timeout_days', get_option( 'aff_cookie_timeout_days', AFFILIATES_COOKIE_TIMEOUT_DAYS ), $affiliate_id );
		if ( $days > 0 ) {
			$expire = time() + AFFILIATES_COOKIE_TIMEOUT_BASE * $days;
		} else {
			$expire = 0;
		}
		if ( class_exists( 'Affiliates_Campaign' ) && method_exists( 'Affiliates_Campaign', 'evaluate' ) ) {
			if ( !empty( $_REQUEST['cmid'] ) ) {
				if ( $cmid = Affiliates_Campaign::evaluate( $_REQUEST['cmid'], $affiliate_id ) ) {
					$encoded_id .= '.' . $cmid;
				}
			}
		}
		$affiliates_request_encoded_id = $encoded_id;
		$hit = affiliates_record_hit( $affiliate_id );
		$cookiepaths = array( COOKIEPATH );
		if ( SITECOOKIEPATH != COOKIEPATH ) {
			$cookiepaths[] = SITECOOKIEPATH;
		}
		foreach ( $cookiepaths as $cookiepath ) {
			setcookie(
				AFFILIATES_COOKIE_NAME,
				$encoded_id,
				$expire,
				$cookiepath,
				COOKIE_DOMAIN
			);
			if ( !empty( $hit['hash'] ) ) {
				setcookie(
					AFFILIATES_HASH_COOKIE_NAME,
					$hit['hash'],
					$expire,
					$cookiepath,
					COOKIE_DOMAIN
				);
			}
		}
		affiliates_pixel_request();
		unset( $wp->query_vars[$pname] ); // we use this to avoid ending up on the blog listing page
		if ( get_option( 'aff_redirect', false ) !== false ) {
			// use a redirect so that we end up on the desired url without the affiliate id dangling on the url
			$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$current_url = remove_query_arg( $pname, $current_url );
			$current_url = preg_replace( '#' . str_replace( AFFILIATES_PNAME, $pname, AFFILIATES_REGEX_PATTERN ) . '#', '', $current_url);
			// note that we must use delimiters other than / as these are used in AFFILIATES_REGEX_PATTERN
			$status = apply_filters( 'affiliates_redirect_status_code', 302 );
			$status = intval( $status );
			switch( $status ) {
				case 300 :
				case 301 :
				case 302 :
				case 303 :
				case 304 :
				case 305 :
				case 306 :
				case 307 :
					break;
				default :
					$status = 302;
			}
			wp_redirect( $current_url, $status );
			exit; // "wp_redirect() does not exit automatically and should almost always be followed by exit." @see http://codex.wordpress.org/Function_Reference/wp_redirect
		}
	} else {
		affiliates_pixel_request();
	}
}

/**
 * Requests pixel handling.
 */
function affiliates_pixel_request() {
	if (
		class_exists( 'Affiliates_Pixel' ) &&
		method_exists( 'Affiliates_Pixel', 'pixel' ) &&
		method_exists( 'Affiliates_Pixel', 'is_pixel_request' )
	) {
		$pname = get_option( 'aff_pname', AFFILIATES_PNAME );
		$p = new Affiliates_Pixel( trailingslashit( home_url() ), $pname );
		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if ( $p->is_pixel_request( $current_url ) ) {
			$p->pixel();
		}
	}
}

/**
 * Record an uris entry if this doesn't exist.
 *
 * @param string $type AFFILIATES_SRC_URI | AFFILIATES_DEST_URI
 * @param string $uri url string
 *
 * @return int|null uri_id added or existed. Null if there is a problem
 */
function affiliates_maybe_record_uri( $type = null, $uri = null ) {
	global $wpdb, $wp;

	$uri_id = null;

	$table = _affiliates_get_tablename( 'uris' );

	$uri = null;
	switch ( $type ) {
		case AFFILIATES_DEST_URI :
			$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$uri = esc_url_raw( $current_url );
			break;
		case AFFILIATES_SRC_URI :
		default :
			if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
				$uri = esc_url_raw( $_SERVER['HTTP_REFERER'] );
			}
			$type = AFFILIATES_SRC_URI;
			break;
	}

	if ( $uri !== null ) {
		$got_uri = $wpdb->get_var( $wpdb->prepare( "SELECT uri_id FROM $table WHERE uri = %s", $uri ) );

		if ( !$got_uri ) {

			$columns    = '(';
			$formats    = '(';
			$values     = array();

			$columns .= 'uri';
			$formats .= '%s';
			$values[] = $uri;

			if ( $type ) {
				$columns .= ',type';
				$formats .= ',%s';
				$values[] = $type;
			}

			$columns .= ')';
			$formats .= ')';
			$query = $wpdb->prepare( "INSERT INTO $table $columns VALUES $formats", $values );
			if ( $wpdb->query( $query ) ) {
				if ( $uri_id = $wpdb->get_var( "SELECT LAST_INSERT_ID()" ) ) {
					do_action(
						'affiliates_uri_added',
						array(
							'uri_id'       => $uri_id,
							'uri'          => $uri,
							'type'          => $type
						)
					);
				}
			}
		} else {  // already exists
			$uri_id = intval( $got_uri );
		}
	}
	return $uri_id;
}

/**
 * Records a new user agent or retrieves the existing entry's id and returns it.
 *
 * @param string $user_agent
 */
function affiliates_maybe_record_user_agent_id( $user_agent ) {

	global $wpdb;

	$user_agent = substr( $user_agent, 0, AFFILIATES_USER_AGENT_MAX_LENGTH );

	$user_agents_table = _affiliates_get_tablename( 'user_agents' );
	$q = $wpdb->prepare( "SELECT user_agent_id FROM $user_agents_table WHERE user_agent = %s", $user_agent );
	$user_agent_id = $wpdb->get_var( $q );
	if ( !$user_agent_id ) {
		$q = $wpdb->prepare( "INSERT INTO $user_agents_table (user_agent) VALUES (%s)", $user_agent );
		$wpdb->query( $q );
		if ( $user_agent_id = $wpdb->get_var( "SELECT LAST_INSERT_ID()" ) ) {
			do_action(
				'affiliates_user_agent_added',
				array(
					'user_agent_id' => $user_agent_id,
					'user_agent'    => $user_agent
				)
			);
		}
	}
	return $user_agent_id;
}

/**
 * Record a hit on an affiliate link.
 *
 * @param int $affiliate_id the affiliate's id
 * @param int $now UNIX timestamp to use, if null the current time is used
 * @param string $type the type of hit to record
 *
 * @return array ID of the inserted hit
 */
function affiliates_record_hit( $affiliate_id, $now = null, $type = null ) {

	global $wpdb;

	$result = null;

	// add a hit
	$hits_table = _affiliates_get_tablename( 'hits' );
	if ( $now === null ) {
		$now = time();
	}
	$date     = date( 'Y-m-d' , $now );
	$datetime = date( 'Y-m-d H:i:s' , $now );
	// @since 5.0.0 instead of using "SELECT COUNT(*) FROM $hits_table" which is slow with InnoDB and large tables
	$n        = $wpdb->get_var( "SELECT MAX(hit_id) FROM $hits_table" );
	$hash     = hash( 'sha256', '' . $n . $affiliate_id . $now );

	$columns  = '(hash, affiliate_id, date, datetime, type';
	$formats  = '(%s,%d,%s,%s,%s';
	$values   = array( $hash, $affiliate_id, $date, $datetime, $type );

	$ip_address = $_SERVER['REMOTE_ADDR'];
	if ( PHP_INT_SIZE >= 8 ) {
		if ( $ip_int = ip2long( $ip_address ) ) {
			$columns .= ',ip';
			$formats .= ',%d';
			$values[] = $ip_int;
		}
	} else {
		if ( $ip_int = ip2long( $ip_address ) ) {
			$ip_int = sprintf( '%u', $ip_int );
			$columns .= ',ip';
			$formats .= ',%s';
			$values[] = $ip_int;
		}
	}
	if ( $user_id = get_current_user_id() ) {
		$columns .= ',user_id';
		$formats .= ',%d';
		$values[] = $user_id;
	}

	$campaign_id = null;
	if ( class_exists( 'Affiliates_Campaign' ) && method_exists(  'Affiliates_Campaign', 'evaluate' ) ) {
		if ( isset( $_REQUEST['cmid'] ) ) {
			$campaign_id = Affiliates_Campaign::evaluate( $_REQUEST['cmid'], $affiliate_id, $_REQUEST );
			if ( $campaign_id ) {
				$columns .= ',campaign_id';
				$formats .= ',%d';
				$values[] = intval( $campaign_id );
			}
		}
	}
	// uris
	$src_uri_id = affiliates_maybe_record_uri( AFFILIATES_SRC_URI );
	if ( $src_uri_id !== null ) {
		$columns .= ',src_uri_id';
		$formats .= ',%d';
		$values[] = intval( $src_uri_id );
	}
	$dest_uri_id = affiliates_maybe_record_uri( AFFILIATES_DEST_URI );
	if ( $dest_uri_id !== null ) {
		$columns .= ',dest_uri_id';
		$formats .= ',%d';
		$values[] = intval( $dest_uri_id );
	}

	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$user_agent_id = affiliates_maybe_record_user_agent_id( $user_agent );

	if ( $user_agent_id ) {
		$columns .= ',user_agent_id';
		$formats .= ',%d';
		$values[] = $user_agent_id;
	}

	$robot  = 0;
	$robots = wp_cache_get( 'robots', 'affiliates' );
	if ( $robots === false ) {
		$robots = array();
		$robots_table = _affiliates_get_tablename( 'robots' );
		$names = $wpdb->get_results( "SELECT DISTINCT(name) FROM $robots_table" );
		if ( $names !== null && is_array( $names ) ) {
			foreach ( $names as $name ) {
				$robots[] = $name->name;
			}
		}
		wp_cache_set( 'robots', $robots, 'affiliates' );
	};
	if ( $robots !== null && is_array( $robots ) && count( $robots ) > 0 ) {
		foreach ( $robots as $name ) {
			if ( strpos( strtolower( $user_agent ), strtolower( $name ) ) !== false ) {
				$robot = 1;
				if ( AFFILIATES_DEBUG_ROBOTS ) {
					affiliates_log_info( sprintf( 'Skipping robot hit from [%s] %s', esc_html( $ip_address ), esc_html( $user_agent ) ) );
				}
				break;
			}
		}
	}
	if ( $robot > 0 ) {
		$columns .= ',is_robot';
		$formats .= ',%d';
		$values[] = '1';
	}

	$columns .= ')';
	$formats .= ')';

	if ( $robot === 0 || apply_filters( 'affiliates_record_robot_hits', AFFILIATES_RECORD_ROBOT_HITS ) ) {
		$query = $wpdb->prepare( "INSERT INTO $hits_table $columns VALUES $formats", $values );
		if ( $wpdb->query( $query ) ) {
			$hit_id = $wpdb->get_var( "SELECT LAST_INSERT_ID()" );
			$result = array(
				'hit_id'        => $hit_id,
				'hash'          => $hash,
				'affiliate_id'  => $affiliate_id,
				'campaign_id'   => $campaign_id,
				'date'          => $date,
				'datetime'      => $datetime,
				'ip'            => $ip_address,
				'is_robot'      => $robot,
				'user_id'       => $user_id,
				'type'          => $type,
				'src_uri_id'    => $src_uri_id,
				'dest_uri_id'   => $dest_uri_id,
				'user_agent_id' => $user_agent_id
			);
			do_action(
				'affiliates_hit',
				$result
			);
		}
	}
	return $result;
}

/**
 * Suggest to record a referral. This function is used to actually store referrals and associated information.
 * If a valid affiliate is found, the referral is stored and attributed to the affiliate and the affiliate's id is returned.
 * Use the $description to describe the nature of the referral and the associated transaction.
 * Use $data to store transaction data or additional information as needed.
 *
 * $data must either be a string or an array. If given as an array, it is assumed that one provides
 * field data (for example: customer id, transaction id, customer name, ...) and for each field
 * one entry is added to the $data array obeying this format:
 *
 * $data['example'] = array(
 *   'title' => 'Field title',
 *   'domain' => 'Domain'
 *   'value' => 'Field value'
 * );
 *
 * This information is then used to display field data for each referral in the Referrals section.
 *
 * 'title' is the field's title, the title is translated using the 'domain'.
 * 'value' is displayed as the field's value.
 *
 * Example:
 *
 * A customer has submitted an order and a possible referral shall be recorded, including the customer's id
 * and the order id:
 *
 * $data = array(
 *   'customer-id' => array( 'title' => 'Customer Id', 'domain' => 'my_order_plugin', 'value' => $customer_id ),
 *   'order-id'    => array( 'title' => 'Order Id', 'domain' => 'my_order_plugin', 'value' => $order_id )
 * );
 * if ( $affiliate_id = affiliates_suggest_referral( $post_id, "Referral for order number $order_id", $data ) ) {
 *   $affiliate = affiliates_get_affiliate( $affiliate_id );
 *   $affiliate_email = $affiliate['email'];
 *   if ( !empty( $affiliate_email ) ) {
 *   	$message = "Dear Affiliate, an order has been made. You will be credited as soon as payment is received.";
 *   	my_order_plugin_send_an_email( $affiliate_email, $message );
 *   }
 * }
 *
 * @param int $post_id the referral post id; where the transaction or referral originates
 * @param string $description the referral description
 * @param string|array $data additional information that should be stored along with the referral
 * @param string $amount referral amount - if used, a $currency_id must be given
 * @param string $currency_id three letter currency code - if used, an $amount must be given
 *
 * @return int affiliate id if a valid referral is recorded, otherwise false
 */
function affiliates_suggest_referral( $post_id, $description = '', $data = null, $amount = null, $currency_id = null, $status = null, $type = null, $reference = null ) {
	global $wpdb, $affiliates_options;
	require_once 'class-affiliates-service.php';
	$affiliate_id = Affiliates_Service::get_referrer_id();
	if ( $affiliate_id ) {
		$hit_id = Affiliates_Service::get_hit_id();
		$affiliate_id = affiliates_add_referral( $affiliate_id, $post_id, $description, $data, $amount, $currency_id, $status, $type, $reference, $hit_id );
	}
	return $affiliate_id;
}

/**
 * Store a referral.
 *
 * @param int $affiliate_id
 * @param int  $post_id
 * @param string $description
 * @param array $data
 * @param string $amount
 * @param string $currency_id
 * @param string $status
 * @param string $type
 * @param string $reference
 * @param int $hit_id
 * @param string $reference_amount
 * @param int $time
 *
 * @return int
 */
function affiliates_add_referral( $affiliate_id, $post_id, $description = '', $data = null, $amount = null, $currency_id = null, $status = null, $type = null, $reference = null, $hit_id = null, $reference_amount = null, $time = null ) {
	global $wpdb;

	if ( $affiliate_id ) {

		$current_user = wp_get_current_user();
		$when = time();
		if ( $time !== null ) {
			$when = intval( $time );
		}
		$datetime = date( 'Y-m-d H:i:s', $when );
		$table = _affiliates_get_tablename( 'referrals' );

		$columns = "(affiliate_id, post_id, datetime, description";
		$formats = "(%d, %d, %s, %s";
		$values = array( $affiliate_id, $post_id, $datetime, $description );

		if ( !empty( $current_user ) ) {
			$columns .= ",user_id ";
			$formats .= ",%d ";
			$values[] = $current_user->ID;
		}

		// add ip
		$ip_address = $_SERVER['REMOTE_ADDR'];
		if ( PHP_INT_SIZE >= 8 ) {
			if ( $ip_int = ip2long( $ip_address ) ) {
				$columns .= ',ip ';
				$formats .= ',%d ';
				$values[] = $ip_int;
			}
		} else {
			if ( $ip_int = ip2long( $ip_address ) ) {
				$ip_int = sprintf( '%u', $ip_int );
				$columns .= ',ip';
				$formats .= ',%s';
				$values[] = $ip_int;
			}
		}

		if ( is_array( $data ) && !empty( $data ) ) {
			$columns .= ",data ";
			$formats .= ",%s ";
			$values[] = serialize( $data );
		}

		if ( !empty( $amount ) && !empty( $currency_id ) ) {
			if ( $amount = Affiliates_Utility::verify_referral_amount( $amount ) ) {
				if ( $currency_id =  Affiliates_Utility::verify_currency_id( $currency_id ) ) {
					$columns .= ",amount ";
					$formats .= ",%s ";
					$values[] = $amount;

					$columns .= ",currency_id ";
					$formats .= ",%s ";
					$values[] = $currency_id;
				}
			}
		}
		if ( !empty( $status ) && Affiliates_Utility::verify_referral_status_transition( $status, $status ) ) {
			$columns .= ',status ';
			$formats .= ',%s ';
			$values[] = $status;
		} else {
			$columns .= ',status ';
			$formats .= ',%s ';
			$values[] = get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );
		}

		if ( !empty( $type ) ) {
			$columns  .= ',type ';
			$formats  .= ',%s';
			$values[] = $type;
		}

		if ( !empty( $reference ) ) {
			$columns  .= ',reference ';
			$formats  .= ',%s';
			$values[] = $reference;
		}

		if ( !empty( $hit_id ) ) {
			$columns  .= ',hit_id ';
			$formats  .= ',%d';
			$values[] = intval( $hit_id );
		}

		if ( !empty( $reference_amount ) ) {
			if ( $reference_amount = Affiliates_Utility::verify_referral_amount( $reference_amount ) ) {
				$columns .= ",reference_amount ";
				$formats .= ",%s ";
				$values[] = $reference_amount;
			}
		}

		$columns .= ")";
		$formats .= ")";

		// add the referral
		$keys = explode( ',', str_replace( ' ', '', substr( $columns, 1, strlen( $columns ) - 2 ) ) );
		$referral_data = array_combine( $keys, $values );
		$record_referral = apply_filters( 'affiliates_record_referral', true, $referral_data );
		if ( $record_referral ) {
			if ( !affiliates_is_duplicate_referral( compact( 'affiliate_id', 'amount', 'currency_id', 'type', 'reference', 'data' ) ) ) {
				$query = $wpdb->prepare( "INSERT INTO $table $columns VALUES $formats", $values );
				if ( $wpdb->query( $query ) !== false ) {
					if ( $referral_id = $wpdb->get_var( "SELECT LAST_INSERT_ID()" ) ) {
						do_action(
							'affiliates_referral',
							$referral_id,
							array(
								'affiliate_id' => $affiliate_id,
								'post_id' => $post_id,
								'description' => $description,
								'data' => $data,
								'amount' => $amount,
								'currency_id' => $currency_id,
								'status' => $status,
								'type' => $type,
								'reference' => $reference,
								'hit_id' => $hit_id,
								'reference_amount' => $reference_amount
							)
						);
					}
				}
			}
		}
	}
	return $affiliate_id;
}

/**
 * Determines whether a referral is considered as a duplicate of an existing
 * one. Semantics: Based on the option aff_duplicates, if the option allows
 * duplicates, no referral will be considered as a duplicate. If the option
 * does not allow duplicates, a duplicate is identified as such based on the
 * amount of attribute available: affiliate_id, amount, currency_id, type,
 * reference and data.
 *
 * @param array $atts referral attributes: affiliate_id (required) and others
 *
 * @return boolean
 */
function affiliates_is_duplicate_referral( $atts ) {

	global $wpdb;

	extract( $atts );

	$is_duplicate = false;
	if ( !get_option( 'aff_duplicates', false ) ) {
		if ( isset( $affiliate_id ) ) {
			$table = _affiliates_get_tablename( 'referrals' );
			$query = "SELECT * FROM $table WHERE affiliate_id = %d";
			$args = array( $affiliate_id );
			if ( !empty( $amount ) ) {
				$query .= " AND amount = %s";
				$args[] = $amount;
			} else {
				$query .= " AND ( amount IS NULL OR amount = '' ) ";
			}
			if ( !empty( $currency_id ) ) {
				$query .= " AND currency_id = %s";
				$args[] = $currency_id;
			} else {
				$query .= " AND ( currency_id IS NULL OR currency_id = '' ) ";
			}
			if ( !empty( $type ) ) {
				$query .= " AND type = %s";
				$args[] = $type;
			} else {
				$query .= " AND ( type IS NULL OR type = '' ) ";
			}
			if ( !empty( $reference ) ) {
				$query .= " AND reference = %s";
				$args[] = $reference;
			} else {
				$query .= " AND ( reference IS NULL OR reference = '' ) ";
			}
			if ( !empty( $data ) && is_array( $data ) ) {
				$query .= " AND data = %s";
				$args[] = serialize( $data );
			} else {
				$query .= " AND ( data IS NULL OR data = '' ) ";
			}
			if ( $wpdb->get_results( $wpdb->prepare( $query, $args ) ) ) {
				$is_duplicate = true;
			}
		}
	}
	return apply_filters( 'affiliates_is_duplicate_referral', $is_duplicate, $atts );
}

/**
 * Update the referral.
 *
 * @param array $attributes to update, supports: affiliate_id, post_id, datetime, description, amount, currency_id, status, reference
 *
 * @return array with keys, values and old_values or null if nothing was updated
 */
function affiliates_update_referral( $referral_id, $attributes ) {
	global $wpdb;

	$result = null;

	$referral = null;
	$referrals_table = _affiliates_get_tablename( 'referrals' );
	if ( $referrals = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $referrals_table WHERE referral_id = %d", intval( $referral_id) ) ) ) {
		if ( count( $referrals ) > 0 ) {
			$referral = $referrals[0];
		}
	}

	if ( $referral !== null ) {
		$set        = array();
		$keys       = array();
		$values     = array();
		$old_values = array();
		foreach( $attributes as $key => $value ) {
			$current_value = isset( $referral->$key ) ? $referral->$key : null;
			if ( $current_value !== $value ) {
				switch( $key ) {
					case 'affiliate_id' :
					case 'post_id' :
						$set[]        = " $key = %d ";
						$keys[]       = $key;
						$values[]     = intval( $value );
						$old_values[] = $current_value;
						break;
					case 'datetime' :
					case 'description' :
					case 'reference' :
						$set[]        = " $key = %s ";
						$keys[]       = $key;
						$values[]     = $value;
						$old_values[] = $current_value;
						break;
					case 'status' :
						// Just check that this is a valid status:
						if ( !empty( $value ) && Affiliates_Utility::verify_referral_status_transition( $value, $value ) ) {
							$set[]        = " $key = %s ";
							$keys[]       = $key;
							$values[]     = $value;
							$old_values[] = $current_value;
						}
						break;
					case 'reference_amount' :
					case 'amount' :
						if ( $value = Affiliates_Utility::verify_referral_amount( $value ) ) {
							$set[]        = " $key = %s ";
							$keys[]       = $key;
							$values[]     = $value;
							$old_values[] = $current_value;
						}
						break;
					case 'currency_id' :
						if ( $value =  Affiliates_Utility::verify_currency_id( $value ) ) {
							$set[]        = " $key = %s ";
							$keys[]       = $key;
							$values[]     = $value;
							$old_values[] = $current_value;
						}
						break;
				}
			}
		}
		if ( count( $set ) > 0 ) {
			$set = implode( ' , ', $set );
			if ( $wpdb->query( $wpdb->prepare( "UPDATE $referrals_table SET $set WHERE referral_id = %d", array_merge( $values, array( intval( $referral_id ) ) ) ) ) ) {
				$result = array(
					'keys'       => $keys,
					'values'     => $values,
					'old_values' => $old_values
				);
				do_action( 'affiliates_updated_referral', intval( $referral_id ), $keys, $values, $old_values );
			}
		}
	}
	return $result;
}



/**
 * Returns an array of possible id encodings.
 *
 * @return array of possible id encodings, keys: encoding identifier, values: encoding name
 */
function affiliates_get_id_encodings() {
	return array(
		AFFILIATES_NO_ID_ENCODING => __( 'No encoding', 'affiliates' ),
		AFFILIATES_MD5_ID_ENCODING => __( 'MD5', 'affiliates' )
	);
}

/**
 * Returns an encoded affiliate id.
 * If AFFILIATES_NO_ID_ENCODING is in effect, the $affiliate_id is returned as-is.
 *
 * @param string|int $affiliate_id the affiliate id to encode
 *
 * @return string|int encoded affiliate id
 */
function affiliates_encode_affiliate_id( $affiliate_id ) {
	global $affiliates_options;
	$encoded_id = null;

	$id_encoding = get_option( 'aff_id_encoding', AFFILIATES_NO_ID_ENCODING );
	switch ( $id_encoding ) {
		case AFFILIATES_MD5_ID_ENCODING :
			$encoded_id = md5( $affiliate_id );
			break;
		default:
			$encoded_id = $affiliate_id;
	}
	return $encoded_id;
}

/**
 * Checks if an affiliate id is from a currently valid affiliate.
 *
 * @param string $affiliate_id the affiliate id
 *
 * @return int|boolean returns the affiliate id if valid, otherwise FALSE
 */
function affiliates_check_affiliate_id_encoded( $affiliate_id ) {

	global $affiliates_options;

	$id_encoding = get_option( 'aff_id_encoding', AFFILIATES_NO_ID_ENCODING );
	switch( $id_encoding ) {
		case AFFILIATES_MD5_ID_ENCODING :
			$result = affiliates_check_affiliate_id_md5( $affiliate_id );
			break;
		default :
			$result = affiliates_check_affiliate_id( $affiliate_id );
	}
	return $result;
}

/**
 * Checks if an affiliate id is from a currently valid affiliate.
 *
 * @param string $affiliate_id the affiliate id
 *
 * @return int|boolean returns the affiliate id if valid, otherwise FALSE
 */
function affiliates_check_affiliate_id( $affiliate_id ) {
	global $wpdb;
	$result = FALSE;
	$today = date( 'Y-m-d', time() );
	$table = _affiliates_get_tablename( 'affiliates' );
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE affiliate_id = %d AND from_date <= %s AND ( thru_date IS NULL OR thru_date >= %s ) AND status = 'active'", intval( $affiliate_id ), $today, $today );
	$affiliate = $wpdb->get_row( $query, OBJECT );
	if ( !empty( $affiliate ) ) {
		$result = $affiliate->affiliate_id;
	}
	return $result;
}

/**
 * Checks if an md5-encoded affiliate id is from a currently valid affiliate.
 *
 * @param string $affiliate_id_md5 the md5-encoded affiliate id
 *
 * @return int|boolean returns the (unencoded) affiliate id if valid, otherwise FALSE
 */
function affiliates_check_affiliate_id_md5( $affiliate_id_md5 ) {
	global $wpdb;
	$result = FALSE;
	$today = date( 'Y-m-d', time() );
	$table = _affiliates_get_tablename( 'affiliates' );
	$query = $wpdb->prepare( "SELECT * FROM (SELECT *, md5(affiliate_id) as affiliate_id_md5 FROM $table) md5d WHERE affiliate_id_md5 = %s AND from_date <= %s AND ( thru_date IS NULL OR thru_date >= %s ) AND status = 'active'", $affiliate_id_md5, $today, $today );
	$affiliate = $wpdb->get_row( $query, OBJECT );
	if ( !empty( $affiliate ) ) {
		$result = $affiliate->affiliate_id;
	}
	return $result;
}

/**
 * Returns the first id of an affiliate of type AFFILIATES_DIRECT_TYPE.
 *
 * @return int|boolean returns the affiliate id (if there is at least one of type AFFILIATES_DIRECT_TYPE), otherwise FALSE
 */
function affiliates_get_direct_id() {
	global $wpdb;
	$result = FALSE;
	$today = date( 'Y-m-d', time() );
	$table = _affiliates_get_tablename( 'affiliates' );
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE type = %s AND from_date <= %s AND ( thru_date IS NULL OR thru_date >= %s ) AND status = 'active'", AFFILIATES_DIRECT_TYPE, $today, $today );
	$affiliate = $wpdb->get_row( $query, OBJECT );
	if ( !empty( $affiliate ) ) {
		$result = $affiliate->affiliate_id;
	}
	return $result;
}

// only needed when in admin
if ( is_admin() ) {
	require_once AFFILIATES_CORE_LIB . '/class-affiliates-admin.php';
	require_once AFFILIATES_CORE_LIB . '/affiliates-admin.php';
	require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings.php';
	require_once AFFILIATES_CORE_LIB . '/affiliates-admin-user-registration.php';
	if ( AFFILIATES_PLUGIN_NAME == 'affiliates' ) {
		require_once AFFILIATES_CORE_LIB . '/class-affiliates-totals.php';
	}
	require_once AFFILIATES_CORE_LIB . '/affiliates-admin-add-ons.php';
	require_once AFFILIATES_CORE_LIB . '/affiliates-admin-affiliates.php';
	require_once AFFILIATES_CORE_LIB . '/affiliates-admin-hits.php';
	require_once AFFILIATES_CORE_LIB . '/affiliates-admin-hits-affiliate.php';
	require_once AFFILIATES_CORE_LIB . '/affiliates-admin-hits-uri.php';
	require_once AFFILIATES_CORE_LIB . '/affiliates-admin-referrals.php';

	require_once AFFILIATES_CORE_LIB . '/class-affiliates-dashboard-widget.php';
	require_once AFFILIATES_CORE_LIB . '/class-affiliates-admin-user-profile.php';
	add_action( 'admin_menu', 'affiliates_admin_menu' );
	add_action( 'network_admin_menu', 'affiliates_network_admin_menu' );
}

/**
 * Register our admin section.
 * Arrange to load styles and scripts required.
 */
function affiliates_admin_menu() {

	$pages = array();

	// main
	$page = add_menu_page(
		__( 'Affiliates Overview', 'affiliates' ),
		'Affiliates', // do not translate (this was marked as todo after core bug 18857 had been fixed http://core.trac.wordpress.org/ticket/18857 translation affects $screen->id)
		AFFILIATES_ACCESS_AFFILIATES,
		'affiliates-admin',
		'affiliates_admin',
		AFFILIATES_PLUGIN_URL . '/images/affiliates.png',
		58
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );

	// overview on affiliates-admin
	$page = add_submenu_page(
		'affiliates-admin',
		__( 'Manage Affiliates', 'affiliates' ),
		__( 'Manage Affiliates', 'affiliates' ),
		AFFILIATES_ADMINISTER_AFFILIATES,
		'affiliates-admin-affiliates',
		apply_filters( 'affiliates_add_submenu_page_function', 'affiliates_admin_affiliates' )
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );

	// hits by date
	$page = add_submenu_page(
		'affiliates-admin',
		__( 'Visits & Referrals', 'affiliates' ),
		__( 'Visits & Referrals', 'affiliates' ),
		AFFILIATES_ACCESS_AFFILIATES,
		'affiliates-admin-hits',
		apply_filters( 'affiliates_add_submenu_page_function', 'affiliates_admin_hits' )
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );

	// hits by affiliate
	$page = add_submenu_page(
		'affiliates-admin',
		__( 'Affiliates & Referrals', 'affiliates' ),
		__( 'Affiliates & Referrals', 'affiliates' ),
		AFFILIATES_ACCESS_AFFILIATES,
		'affiliates-admin-hits-affiliate',
		apply_filters( 'affiliates_add_submenu_page_function', 'affiliates_admin_hits_affiliate' )
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );

	// hits by URIs
	$page = add_submenu_page(
		'affiliates-admin',
		__( 'Traffic', 'affiliates' ),
		__( 'Traffic', 'affiliates' ),
		AFFILIATES_ACCESS_AFFILIATES,
		'affiliates-admin-hits-uri',
		apply_filters( 'affiliates_add_submenu_page_function', 'affiliates_admin_hits_uri' )
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );

	// referrals
	$page = add_submenu_page(
		'affiliates-admin',
		__( 'Referrals', 'affiliates' ),
		__( 'Referrals', 'affiliates' ),
		AFFILIATES_ACCESS_AFFILIATES,
		'affiliates-admin-referrals',
		apply_filters( 'affiliates_add_submenu_page_function', 'affiliates_admin_referrals' )
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );

	// totals
	if ( AFFILIATES_PLUGIN_NAME == 'affiliates' ) {
		$page = add_submenu_page(
			'affiliates-admin',
			__( 'Totals', 'affiliates' ),
			__( 'Totals', 'affiliates' ),
			AFFILIATES_ACCESS_AFFILIATES,
			'affiliates-admin-totals',
			apply_filters( 'affiliates_add_submenu_page_function', array( 'Affiliates_Totals', 'view' ) )
		);
		$pages[] = $page;
		add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
		add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );
	}

	// settings
	$page = add_submenu_page(
		'affiliates-admin',
		__( 'Affiliates Settings', 'affiliates' ),
		__( 'Settings', 'affiliates' ),
		AFFILIATES_ADMINISTER_OPTIONS,
		'affiliates-admin-settings',
		apply_filters( 'affiliates_add_submenu_page_function', array( 'Affiliates_Settings', 'admin_settings' ) )
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );

	// user registration
	$page = add_submenu_page(
		'affiliates-admin',
		__( 'User Registration', 'affiliates' ),
		__( 'User Registration', 'affiliates' ),
		AFFILIATES_ADMINISTER_OPTIONS,
		'affiliates-admin-user-registration',
		apply_filters( 'affiliates_add_submenu_page_function', 'affiliates_admin_user_registration' )
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );

	// notifications
	$page = add_submenu_page(
		'affiliates-admin',
		__( 'Notifications', 'affiliates' ),
		__( 'Notifications', 'affiliates' ),
		AFFILIATES_ACCESS_AFFILIATES,
		'affiliates-admin-notifications',
		apply_filters( 'affiliates_add_submenu_page_function', array( Affiliates_Notifications::get_instance()->get_admin_class(), 'view' ) )
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );

	add_action( 'load-' . $page, array( Affiliates_Notifications::get_instance()->get_admin_class(), 'load_page' ) );

	// add-ons
	$page = add_submenu_page(
		'affiliates-admin',
		__( 'Add-Ons', 'affiliates' ),
		__( 'Add-Ons', 'affiliates' ),
		AFFILIATES_ADMINISTER_OPTIONS,
		'affiliates-admin-add-ons',
		apply_filters( 'affiliates_add_submenu_page_function', 'affiliates_admin_add_ons' )
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );

	do_action( 'affiliates_admin_menu', $pages );
}

/**
 * Adds network admin menu.
 */
function affiliates_network_admin_menu() {
	require_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-network.php';
	$pages = array();
	$page = add_menu_page(
		__( 'Affiliates', 'affiliates' ),
		__( 'Affiliates', 'affiliates' ),
		AFFILIATES_ACCESS_AFFILIATES,
		'affiliates-network-admin',
		array( 'Affiliates_Settings_Network', 'network_admin_settings' ),
		AFFILIATES_PLUGIN_URL . '/images/affiliates.png'
	);
	$pages[] = $page;
	add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
	add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );
	do_action( 'affiliates_network_admin_menu', $pages );
}

require_once AFFILIATES_CORE_LIB . '/class-affiliates-admin-help.php';

/**
 * Returns or renders the footer.
 *
 * @param boolean $render
 *
 * @return string or nothing
 */
function affiliates_footer( $render = true ) {
	$footer = '<div class="affiliates-footer">' .
		'<p>' .
		sprintf(
			/* translators: 1: link 2: link */
			__( 'Thank you for using the %1$s plugin by %2$s.', 'affiliates' ),
			'<a style="text-decoration:none;" href="https://www.itthinx.com/plugins/affiliates" target="_blank">Affiliates</a>',
			'<a style="text-decoration:none;" href="https://www.itthinx.com" target="_blank">itthinx</a>'
		) .
		' ' .
		sprintf(
			/* translators: link */
			__( 'Please give it a %s rating!', 'affiliates' ),
			sprintf( '<a style="text-decoration:none;" href="%s">&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
				'https://wordpress.org/support/view/plugin-reviews/affiliates?filter=5#postform'
			)
		) .
		'</p>' .
		'<p>' .
		affiliates_donate( false ) .
		'</p>' .
		'</div>';
	$footer = apply_filters( 'affiliates_footer', $footer );
	if ( $render ) {
		echo $footer;
	} else {
		return $footer;
	}
}

/**
 * Render or return a donation button.
 * Thanks for supporting me!
 *
 * @param boolean $render
 *
 * @return string|null
 */
function affiliates_donate( $render = true, $small = false ) {
	$output = '<style type="text/css">';
	$output .= '.button.affiliates-premium-button { background-color: #5da64f; color: #ffffff; font-weight: bold; border-top-color: #8dd67f; border-bottom-color: #2d761f; border-left-color: #7dc66f; border-right-color: #3d862f; }';
	$output .= '.button.affiliates-shop-button { background-color: #d65d4f; color: #ffffff; font-weight: bold; border-top-color: #f67d6f; border-bottom-color: #a62d1f; border-left-color: #e66d5f; border-right-color: #b63d2f; }';
	$output .= '.button.affiliates-premium-button:hover, .button.affiliates-shop-button:hover { background-color: #004fa6; color: #ffffff; font-weight: bold; }';
	$output .= '</style>';
	$output .= sprintf(
		'<a class="button affiliates-premium-button" href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a> <a class="button affiliates-premium-button" href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a> <a class="button affiliates-shop-button" href="https://www.itthinx.com/shop/">Shop</a>'
	);
	if ( $render ) {
		echo $output;
	} else {
		return $output;
	}
}

/**
 * Retrieves an affiliate by id.
 * The array returned provides the following attributes:
 * <ul>
 * <li>affiliate_id - the integer id</li>
 * <li>name - the affiliate's name</li>
 * <li>email - email address</li>
 * <li>from_date - from when the affiliate is valid (*)</li>
 * <li>thru_date - [optional] until when the affiliate is valid (*)</li>
 * <li>status - either 'active' or 'deleted'</li>
 * </ul>
 * (*) Suggested referrals will succeed if the current date is within those dates. Both are inclusive.
 *
 * Usage example:
 * $affiliate = affiliates_get_affiliate( $affiliate_id );
 * $email = $affiliate['email'];
 *
 * @param int|string $affiliate_id the affiliate's id
 *
 * @return array affiliate details or null
 */
function affiliates_get_affiliate( $affiliate_id ) {
	global $wpdb;
	$table = _affiliates_get_tablename( 'affiliates' );
	$affiliate = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM $table WHERE affiliate_id = %d", intval( $affiliate_id ) ),
		ARRAY_A
	);
	return $affiliate;
}

/**
 * Return the user id related to an affiliate.
 *
 * @param int $affiliate_id
 *
 * @return int user_id
 */
function affiliates_get_affiliate_user( $affiliate_id ) {
	global $wpdb;
	$table = _affiliates_get_tablename( 'affiliates_users' );
	$user_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT user_id FROM $table WHERE affiliate_id = %d", intval( $affiliate_id )
	) );
	return $user_id;
}

/**
 * Return the affiliate ids related to a user.
 *
 * @param int $user_id
 * @param string $status the affiliate's status, default is 'active'
 *
 * @return array of int affiliate ids or null on failure
 */
function affiliates_get_user_affiliate( $user_id, $status = 'active' ) {
	global $wpdb;
	switch( $status ) {
		case 'active' :
		case 'pending' :
		case 'deleted' :
			break;
		default :
			$status = 'active';
	}
	$result = null;
	$affiliates_table = _affiliates_get_tablename( 'affiliates' );
	$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
	if ( $affiliates = $wpdb->get_results( $wpdb->prepare(
		"SELECT $affiliates_table.affiliate_id FROM $affiliates_users_table LEFT JOIN $affiliates_table ON $affiliates_users_table.affiliate_id = $affiliates_table.affiliate_id WHERE $affiliates_users_table.user_id = %d AND $affiliates_table.status = %s",
		intval( $user_id ),
		$status
	) ) ) {
		$result = array();
		foreach( $affiliates as $affiliate ) {
			$result[] = $affiliate->affiliate_id;
		}
	}
	return $result;
}

/**
 * Returns true if the user is an affiliate.
 *
 * @param int|object $user (optional) specify a user or use current if none given
 *
 * @return boolean
 */
function affiliates_user_is_affiliate( $user_id = null ) {
	global $wpdb;
	$result = false;
	if ( is_user_logged_in() ) {
		if ( $user_id == null ) {
			$user = wp_get_current_user();
		} else {
			$user = get_user_by( 'id', $user_id );
		}
		if ( $user ) {
			$user_id = $user->ID;
			$affiliates_table = _affiliates_get_tablename( 'affiliates' );
			$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
			$affiliates = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM $affiliates_users_table LEFT JOIN $affiliates_table ON $affiliates_users_table.affiliate_id = $affiliates_table.affiliate_id WHERE $affiliates_users_table.user_id = %d AND $affiliates_table.status ='active'", intval( $user_id ) )
			);
			$result = !empty( $affiliates );
		}
	}
	return $result;
}

/**
 * Returns true if the user is an affiliate with the given status.
 *
 * @param int|object $user (optional) specify a user or use current if none given
 * @param string $status 'active' (default), 'pending', 'deleted'
 *
 * @return boolean
 */
function affiliates_user_is_affiliate_status( $user_id = null, $status = 'active' ) {
	global $wpdb;
	switch( $status ) {
		case 'active' :
		case 'pending' :
		case 'deleted' :
			break;
		default :
			$status = 'active';
	}
	$result = false;
	if ( is_user_logged_in() ) {
		if ( $user_id == null ) {
			$user = wp_get_current_user();
		} else {
			$user = get_user_by( 'id', $user_id );
		}
		if ( $user ) {
			$user_id = $user->ID;
			$affiliates_table = _affiliates_get_tablename( 'affiliates' );
			$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
			$affiliates = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $affiliates_users_table LEFT JOIN $affiliates_table ON $affiliates_users_table.affiliate_id = $affiliates_table.affiliate_id WHERE $affiliates_users_table.user_id = %d AND $affiliates_table.status = %s",
					intval( $user_id ),
					$status
				)
			);
			$result = !empty( $affiliates );
		}
	}
	return $result;
}

/**
 * Returns the current status of the affiliate.
 *
 * @param int $affiliate_id
 *
 * @return string affiliate status or null
 */
function affiliates_get_affiliate_status( $affiliate_id ) {
	global $wpdb;
	$affiliates_table = _affiliates_get_tablename( 'affiliates' );
	return $wpdb->get_var(
		$wpdb->prepare(
			"SELECT status FROM $affiliates_table WHERE affiliate_id = %d",
			intval( $affiliate_id )
		)
	);
}

/**
 * Returns an array of affiliates.
 *
 * @param boolean $active returns only active affiliates (not deleted via the admin UI)
 *
 * @return array of affiliates
 */
function affiliates_get_affiliates( $active = true, $valid = true ) {

	global $wpdb;
	$results = array();
	$today = date( 'Y-m-d', time() );
	$table = _affiliates_get_tablename( 'affiliates' );
	if ( $active ) {
		if ( $valid ) {
			$query = $wpdb->prepare( "SELECT * FROM $table WHERE from_date <= %s AND ( thru_date IS NULL OR thru_date >= %s ) AND status = 'active' ORDER BY NAME", $today, $today );
	 } else {
	 	$query = "SELECT * FROM $table WHERE status = 'active' ORDER BY NAME";
	 }
	} else {
		if ( $valid ) {
			$query = $wpdb->prepare( "SELECT * FROM $table WHERE from_date <= %s AND ( thru_date IS NULL OR thru_date >= %s ) ORDER BY NAME", $today, $today );
		} else {
			$query = "SELECT * FROM $table ORDER BY NAME";
		}
	}
	if ( $affiliates = $wpdb->get_results( $query, ARRAY_A ) ) {
		$results = $affiliates;
	}
	return $results;
}

/**
 * Returns the number of hits for a given affiliate.
 *
 * @param int $affiliate_id the affiliate's id
 * @param string $from_date optional from date
 * @param string $thru_date optional thru date
 * @param boolean $precise take time into account ($from_date and $thru_date include time)
 *
 * @return int number of hits
 */
function affiliates_get_affiliate_hits( $affiliate_id, $from_date = null , $thru_date = null, $precise = false ) {
	global $wpdb;
	$hits_table = _affiliates_get_tablename('hits');
	$result = 0;
	$where = " WHERE affiliate_id = %d";
	$values = array( $affiliate_id );
	if ( $from_date ) {
		if ( $precise ) {
			$from_datetime = date( 'Y-m-d H:i:s', strtotime( $from_date ) );
		} else {
			$from_date = date( 'Y-m-d', strtotime( $from_date ) );
		}
	}
	if ( $thru_date ) {
		if ( $precise ) {
			$thru_datetime = date( 'Y-m-d H:i:s', strtotime( $thru_date ) );
		} else {
			$thru_date = date( 'Y-m-d', strtotime( $thru_date ) + 24*3600 );
		}
	}
	if ( $from_date && $thru_date ) {
		if ( $precise ) {
			$where .= " AND datetime >= %s AND datetime < %s ";
			$values[] = $from_datetime;
			$values[] = $thru_datetime;
		} else {
			$where .= " AND date >= %s AND date < %s ";
			$values[] = $from_date;
			$values[] = $thru_date;
		}
	} else if ( $from_date ) {
		if ( $precise ) {
			$where .= " AND datetime >= %s ";
			$values[] = $from_datetime;
		} else {
			$where .= " AND date >= %s ";
			$values[] = $from_date;
		}
	} else if ( $thru_date ) {
		if ( $precise ) {
			$where .= " AND datetime < %s ";
			$values[] = $thru_datetime;
		} else {
			$where .= " AND date < %s ";
			$values[] = $thru_date;
		}
	}
	$query = $wpdb->prepare( "SELECT COUNT(*) FROM $hits_table $where",
		$values
	);
	$result = intval( $wpdb->get_var( $query) );
	return $result;
}

/**
 * Returns the number of visits for a given affiliate.
 * One or more hits from the same IP on the same day count as one visit.
 *
 * @param int $affiliate_id the affiliate's id
 * @param string $from_date optional from date
 * @param string $thru_date optional thru date
 * @param boolean $precise take time into account ($from_date and $thru_date include time)
 *
 * @return int number of visits
 */
function affiliates_get_affiliate_visits( $affiliate_id, $from_date = null , $thru_date = null, $precise = false ) {
	global $wpdb;
	$hits_table = _affiliates_get_tablename('hits');
	$result = 0;
	$where = " WHERE affiliate_id = %d";
	$values = array( $affiliate_id );
	// if ( $from_date ) {
	// 	$from_date = date( 'Y-m-d', strtotime( $from_date ) );
	// }
	// if ( $thru_date ) {
	// 	$thru_date = date( 'Y-m-d', strtotime( $thru_date ) + 24*3600 );
	// }
	// if ( $from_date && $thru_date ) {
	// 	$where .= " AND date >= %s AND date < %s ";
	// 	$values[] = $from_date;
	// 	$values[] = $thru_date;
	// } else if ( $from_date ) {
	// 	$where .= " AND date >= %s ";
	// 	$values[] = $from_date;
	// } else if ( $thru_date ) {
	// 	$where .= " AND date < %s ";
	// 	$values[] = $thru_date;
	// }
	if ( $from_date ) {
		if ( $precise ) {
			$from_datetime = date( 'Y-m-d H:i:s', strtotime( $from_date ) );
		} else {
			$from_date = date( 'Y-m-d', strtotime( $from_date ) );
		}
	}
	if ( $thru_date ) {
		if ( $precise ) {
			$thru_datetime = date( 'Y-m-d H:i:s', strtotime( $thru_date ) );
		} else {
			$thru_date = date( 'Y-m-d', strtotime( $thru_date ) + 24*3600 );
		}
	}
	if ( $from_date && $thru_date ) {
		if ( $precise ) {
			$where .= " AND datetime >= %s AND datetime < %s ";
			$values[] = $from_datetime;
			$values[] = $thru_datetime;
		} else {
			$where .= " AND date >= %s AND date < %s ";
			$values[] = $from_date;
			$values[] = $thru_date;
		}
	} else if ( $from_date ) {
		if ( $precise ) {
			$where .= " AND datetime >= %s ";
			$values[] = $from_datetime;
		} else {
			$where .= " AND date >= %s ";
			$values[] = $from_date;
		}
	} else if ( $thru_date ) {
		if ( $precise ) {
			$where .= " AND datetime < %s ";
			$values[] = $thru_datetime;
		} else {
			$where .= " AND date < %s ";
			$values[] = $thru_date;
		}
	}
	$query = $wpdb->prepare(
		"SELECT SUM(visits) FROM " .
		"(SELECT COUNT(DISTINCT IP) visits FROM $hits_table $where GROUP BY DATE) tmp",
		$values
	);
	$result = intval( $wpdb->get_var( $query) );
	return $result;
}

/**
 * Returns the number of referrals for a given affiliate.
 *
 * @param int $affiliate_id the affiliate's id
 * @param string $from_date optional from date
 * @param string $thru_date optional thru date
 *
 * @return int number of hits
 */
function affiliates_get_affiliate_referrals( $affiliate_id, $from_date = null , $thru_date = null, $status = null, $precise = false ) {
	global $wpdb;
	$referrals_table = _affiliates_get_tablename( 'referrals' );
	$result = 0;
	$where = " WHERE affiliate_id = %d";
	$values = array( $affiliate_id );
	if ( $from_date ) {
		if ( $precise ) {
			$from_date = date( 'Y-m-d H:i:s', strtotime( $from_date ) );
		} else {
			$from_date = date( 'Y-m-d', strtotime( $from_date ) );
		}
	}
	if ( $thru_date ) {
		if ( $precise ) {
			$thru_date = date( 'Y-m-d H:i:s', strtotime( $thru_date ) );
		} else {
			$thru_date = date( 'Y-m-d', strtotime( $thru_date ) + 24*3600 );
		}
	}
	if ( $from_date && $thru_date ) {
		$where .= " AND datetime >= %s AND datetime < %s ";
		$values[] = $from_date;
		$values[] = $thru_date;
	} else if ( $from_date ) {
		$where .= " AND datetime >= %s ";
		$values[] = $from_date;
	} else if ( $thru_date ) {
		$where .= " AND datetime < %s ";
		$values[] = $thru_date;
	}
	if ( !empty( $status ) ) {
		$where .= " AND status = %s ";
		$values[] = $status;
	} else {
		$where .= " AND status IN ( %s, %s ) ";
		$values[] = AFFILIATES_REFERRAL_STATUS_ACCEPTED;
		$values[] = AFFILIATES_REFERRAL_STATUS_CLOSED;
	}
	$query = $wpdb->prepare( "SELECT COUNT(*) FROM $referrals_table $where",
		$values
	);
	$result = intval( $wpdb->get_var( $query) );
	return $result;
}

/**
 * Returns the prefixed DB table name.
 *
 * @param string $name the name of the DB table
 *
 * @return string prefixed DB table name
 */
function _affiliates_get_tablename( $name ) {
	global $wpdb;
	return $wpdb->prefix . AFFILIATES_TP . $name;
}

/**
 * Attribute filter for overrides.
 *
 * @param mixed $value
 * @param int $affiliate_id
 * @param string $key
 *
 * @return mixed
 */
function affiliates_attribute_filter( $value, $affiliate_id, $key ) {
	if ( $user_id = affiliates_get_affiliate_user( $affiliate_id ) ) {
		$maybe_value = get_user_meta( $user_id, $key , true );
		if ( !empty( $maybe_value ) ) {
			$value = $maybe_value;
		}
	}
	return $value;
}

/**
 * Compose a URL based on its components.
 *
 * $components can provide values indexed by the keys scheme, host, port,
 * user, pass, path, query and fragment as produced by parse_url().
 *
 * @param array $components
 *
 * @return string URL
 */
function affiliates_compose_url( $components ) {
	$scheme   = isset( $components['scheme'] ) ? $components['scheme'] . '://' : '';
	$host     = isset( $components['host'] ) ? $components['host'] : '';
	$port     = isset( $components['port'] ) ? ':' . $components['port'] : '';
	$user     = isset( $components['user'] ) ? $components['user'] : '';
	$pass     = isset( $components['pass'] ) ? ':' . $components['pass']  : '';
	$pass     = ( !empty( $user ) || !empty( $pass ) ) ? "$pass@" : '';
	$path     = isset( $components['path'] ) ? $components['path'] : '';
	$query    = isset( $components['query'] ) ? '?' . $components['query'] : '';
	$fragment = isset( $components['fragment'] ) ? '#' . $components['fragment'] : '';
	return "$scheme$user$pass$host$port$path$query$fragment";
}

/**
 * Returns the URL converted to an affiliate URL for the given affiliate.
 *
 * @param string $url
 * @param int $affiliate_id
 *
 * @return string
 */
function affiliates_get_affiliate_url( $url, $affiliate_id ) {
	$pname = get_option( 'aff_pname', AFFILIATES_PNAME );
	$scheme = parse_url( $url, PHP_URL_SCHEME );
	if ( empty( $scheme ) ) {
		$prefix = '';
		// Although scheme is empty we could have a malformed scheme and we don't
		// want to make it worse so also check for http:// and https:// prefixes.
		if ( strpos( $url, 'http://' ) !== 0 && strpos( $url, 'https://' ) !== 0 ) {
			$prefix = !empty( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) != 'off' ? 'https:' : 'http:';
			if ( strpos( $url, '//' ) !== 0 ) {
				$prefix .= '//';
			}
		}
		$url = $prefix . $url;
	}
	$components = parse_url( $url );
	// If pname is already in query, don't modify.
	if ( strpos( isset( $components['query'] ) ? $components['query'] : '', "$pname=" ) === false ) {
		$query = '';
		if ( !empty( $components['query'] ) ) {
			$query = $components['query'] . '&';
		}
		$encoded_id = affiliates_encode_affiliate_id( $affiliate_id );
		$query .= sprintf( '%s=%s', $pname, $encoded_id );
		if ( empty( $components['path'] ) ) {
			$components['path'] = '/';
		}
		$components['query'] = $query;
	}
	return affiliates_compose_url( $components );
}

/**
 * Returns the precision for referral amount decimals.
 *
 * Uses the constants :
 * - AFFILIATES_REFERRAL_AMOUNT_DECIMALS for empty or default context ''
 * - AFFILIATES_REFERRAL_AMOUNT_DECIMALS_DISPLAY for context 'display'
 *
 * @param string $context provided and passed in the filter, default '', allows also 'display'
 *
 * @return int decimals for referral amounts
 */
function affiliates_get_referral_amount_decimals( $context = null ) {
	switch( $context ) {
		case 'display' :
			$result = apply_filters( 'affiliates_referral_amount_decimals', AFFILIATES_REFERRAL_AMOUNT_DECIMALS_DISPLAY, $context );
			break;
		default :
			$result = apply_filters( 'affiliates_referral_amount_decimals', AFFILIATES_REFERRAL_AMOUNT_DECIMALS, $context );
	}
	return $result;
}

/**
 * Provide the related post URL for the referral.
 *
 * @since 4.19.0
 *
 * @param array|object $referral
 *
 * @return string
 */
function affiliates_get_referral_post_permalink( $referral ) {
	$url = '';
	$post_id = null;
	if ( is_array( $referral ) && array_key_exists( 'post_id', $referral ) ) {
		$post_id = $referral['post_id'];
	} else if ( is_object( $referral ) && property_exists( $referral, 'post_id' ) ) {
		$post_id = $referral->post_id;
	}
	if ( $post_id !== null ) {
		$url = get_permalink( $post_id );
	}
	$url = apply_filters( 'affiliates_referral_post_permalink', $url, $referral );
	return $url;
}

/**
 * Provide the related post title for the referral.
 *
 * @since 4.19.0
 *
 * @param array|object $referral
 *
 * @return string
 */
function affiliates_get_referral_post_title( $referral ) {
	$title = '';
	$post_id = null;
	if ( is_array( $referral ) && array_key_exists( 'post_id', $referral ) ) {
		$post_id = $referral['post_id'];
	} else if ( is_object( $referral ) && property_exists( $referral, 'post_id' ) ) {
		$post_id = $referral->post_id;
	}
	if ( $post_id !== null ) {
		$title = get_the_title( $post_id );
	}
	$title = apply_filters( 'affiliates_referral_post_title', $title, $referral );
	return $title;
}

/**
 * Returns the referral amount formatted.
 *
 * @param number $amount
 * @param string $context see affiliates_get_referral_amount_decimals()
 *
 * @return string
 */
function affiliates_format_referral_amount( $amount, $context = '' ) {
	if ( $amount === null ) {
		$amount = 0;
	}
	return Affiliates_Math::add( '0', $amount, affiliates_get_referral_amount_decimals( $context ) );
}

/**
 * Attempt lifting the execution time limit.
 *
 * @since 5.2.0
 *
 * @return boolean
 */
function affiliates_request_execution_unlimited() {
	$set = false;
	if ( function_exists( 'set_time_limit' ) ) {
		$set = @set_time_limit( 0 );
	}
	if ( !$set ) {
		if ( function_exists( 'ini_set' ) ) {
			$set = @ini_set( 'max_execution_time', 0 ) !== false;
		}
	}
	return $set;
}
