<?php
/**
 * class-affiliates-data-cleaner.php
 *
 * Copyright (c) 2010 - 2026 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 6.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clean-up.
 */
class Affiliates_Data_Cleaner {

	const HOUR = 3600;

	public static function init() {
		add_action(
			'wp_ajax_affiliates_data_cleaner_clean',
			array( __CLASS__, 'clean' )
		);
	}

	/**
	 * Initializes the ajax nonce in the footer.
	 */
	public static function admin_footer() {
		echo
			'<script type="text/javascript">' .
			'affiliates_data_cleaner_ajax_nonce = \'' . wp_create_nonce( 'affiliates-data-cleaner-ajax-nonce' ) . '\';' .
			'</script>';
	}

	/**
	 * Ajax wp_ajax_affiliates_data_cleaner_clean cleaner handler.
	 */
	public static function clean() {
		global $wpdb;

		if ( !current_user_can( AFFILIATES_ADMINISTER_AFFILIATES ) ) {
			exit;
		}

		if ( check_ajax_referer( 'affiliates-data-cleaner-ajax-nonce', 'affiliates_data_cleaner_ajax_nonce' ) ) {
			affiliates_request_execution_unlimited();
			// delete unrelated hits beyond validity timeframe
			$hits_table = _affiliates_get_tablename( 'hits' );
			$uris_table = _affiliates_get_tablename( 'uris' );
			$referrals_table = _affiliates_get_tablename( 'referrals' );
			$user_agents_table = _affiliates_get_tablename( 'user_agents' );

			$hits_rows = 0;
			$uris_rows = 0;
			$user_agents_rows = 0;

			ob_start();

			// hits
			$count_hits = $wpdb->get_var( "SELECT COUNT(*) FROM $hits_table WHERE hit_id NOT IN (SELECT hit_id FROM $referrals_table WHERE hit_id IS NOT NULL)" );
			if ( is_numeric( $count_hits ) ) {
				$count_hits = intval( $count_hits );
				if ( $count_hits > 0 ) {
					// until as many days or up to an hour before
					$days = get_option( 'aff_cookie_timeout_days', AFFILIATES_COOKIE_TIMEOUT_DAYS );
					$days = max( 0, is_numeric( $days ) ? intval( $days ) : 0 );
					$seconds = $days * AFFILIATES_COOKIE_TIMEOUT_BASE;
					$seconds = max( $seconds, self::HOUR );
					$timestamp = time() - $seconds;
					$until = date( 'Y-m-d H:i:s', $timestamp );
					$query = $wpdb->prepare(
						"DELETE FROM $hits_table WHERE datetime < %s AND hit_id NOT IN (SELECT hit_id FROM $referrals_table WHERE hit_id IS NOT NULL)",
						$until
					);
					$result = $wpdb->query( $query );
					if ( $result ) {
						$hits_rows = $wpdb->get_var( "SELECT ROW_COUNT()" );
					}
				}
			}

			// URIs
			$count_uris = $wpdb->get_var( "SELECT COUNT(*) FROM $uris_table WHERE uri_id NOT IN (SELECT src_uri_id FROM $hits_table WHERE src_uri_id IS NOT NULL) AND uri_id NOT IN (SELECT dest_uri_id FROM $hits_table WHERE dest_uri_id IS NOT NULL)" );
			if ( is_numeric( $count_uris ) ) {
				$count_uris = intval( $count_uris );
				if ( $count_uris > 0 ) {
					$query = $wpdb->prepare(
						"DELETE FROM $uris_table WHERE uri_id NOT IN (SELECT src_uri_id FROM $hits_table WHERE src_uri_id IS NOT NULL) AND uri_id NOT IN (SELECT dest_uri_id FROM $hits_table WHERE dest_uri_id IS NOT NULL)"
					);
					$result = $wpdb->query( $query );
					if ( $result ) {
						$uris_rows = $wpdb->get_var( "SELECT ROW_COUNT()" );
					}
				}
			}

			// user agents
			$count_user_agents = $wpdb->get_var( "SELECT COUNT(*) FROM $user_agents_table WHERE user_agent_id NOT IN (SELECT user_agent_id FROM $hits_table WHERE user_agent_id IS NOT NULL)" );
			if ( is_numeric( $count_user_agents ) ) {
				$count_user_agents = intval( $count_user_agents );
				if ( $count_user_agents > 0 ) {
					$query = $wpdb->prepare(
						"DELETE FROM $user_agents_table WHERE user_agent_id NOT IN (SELECT user_agent_id FROM $hits_table WHERE user_agent_id IS NOT NULL)"
					);
					$result = $wpdb->query( $query );
					if ( $result ) {
						$user_agents_rows = $wpdb->get_var( "SELECT ROW_COUNT()" );
					}
				}
			}

			$total_hits = $wpdb->get_var( "SELECT COUNT(*) FROM $hits_table" );
			$total_uris = $wpdb->get_var( "SELECT COUNT(*) FROM $uris_table" );
			$total_user_agents = $wpdb->get_var( "SELECT COUNT(*) FROM $user_agents_table" );

			$ob = ob_get_clean();

			affiliates_log_warning( $ob );

			echo json_encode( array(
				'count_hits' => $count_hits,
				'count_uris' => $count_uris,
				'count_user_agents' => $count_user_agents,
				'hits' => $hits_rows,
				'uris' => $uris_rows,
				'user_agents' => $user_agents_rows,
				'total_hits' => $total_hits,
				'total_uris' => $total_uris,
				'total_user_agents' => $total_user_agents
			) );
		}
		exit;
	}

	public static function admin() {

		global $wpdb, $affiliates_version;

		if ( !current_user_can( AFFILIATES_ADMINISTER_AFFILIATES ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}

		add_action( 'admin_footer', array( __CLASS__, 'admin_footer' ) );
		wp_enqueue_script( 'affiliates-data-cleaner', AFFILIATES_PLUGIN_URL . 'js/affiliates-data-cleaner.js', array( 'jquery' ), $affiliates_version, true );

		wp_localize_script(
			'affiliates-data-cleaner',
			'affiliates_data_cleaner',
			array(
				'hits_deleted' => _x( 'Hits deleted:', 'data cleaner', 'affiliates' ),
				'uris_deleted' => _x( 'URIs deleted:', 'data cleaner', 'affiliates' ),
				'user_agents_deleted' => _x( 'User Agents deleted:', 'data cleaner', 'affiliates' ),
				'failed' => _x( 'Failed', 'data cleaner', 'affiliates' )
			)
		);

		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : null;

		if (
			$action !== null &&
			(
				!isset( $_REQUEST['data-cleaner-nonce'] ) ||
				!wp_verify_nonce( $_REQUEST['data-cleaner-nonce'], 'data-cleaner-action' )
			)
		) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}

		echo '<h3>' . esc_html__( 'Data Cleaner', 'affiliates' ) . '</h3>';

		switch ( $action ) {

			case null :
				$hits_table = _affiliates_get_tablename( 'hits' );
				$uris_table = _affiliates_get_tablename( 'uris' );
				$referrals_table = _affiliates_get_tablename( 'referrals' );
				$user_agents_table = _affiliates_get_tablename( 'user_agents' );
				$total_hits = $wpdb->get_var( "SELECT COUNT(*) FROM $hits_table" );
				$total_uris = $wpdb->get_var( "SELECT COUNT(*) FROM $uris_table" );
				$total_user_agents = $wpdb->get_var( "SELECT COUNT(*) FROM $user_agents_table" );
				echo '<ol>';
				printf( '<li>%1$s &mdash; <code>%2$d</code></li>', esc_html__( 'Hits', 'affiliates' ), intval( $total_hits ) );
				printf( '<li>%1$s &mdash; <code>%2$d</code></li>', esc_html__( 'URIs', 'affiliates' ), intval( $total_uris ) );
				printf( '<li>%1$s &mdash; <code>%2$d</code></li>', esc_html__( 'User Agents', 'affiliates' ), intval( $total_user_agents ) );
				echo '</ol>';
				echo '<p>';
				esc_html_e( 'Check for data that can be removed &hellip;', 'affiliates' );
				echo '</p>';
				echo '<form action="" name="data-cleaner" method="post">';
				printf( '<button class="button button-primary" name="data-cleaner" type="submit" >%s</button>', esc_html__( 'Check', 'affiliates' ) );
				echo '<input type="hidden" name="action" value="confirm"/>';
				echo wp_nonce_field( 'data-cleaner-action', 'data-cleaner-nonce', true, false );
				echo '</form>';
				echo '<p>';
				break;

			case 'confirm' :

				$hits_table = _affiliates_get_tablename( 'hits' );
				$uris_table = _affiliates_get_tablename( 'uris' );
				$referrals_table = _affiliates_get_tablename( 'referrals' );
				$user_agents_table = _affiliates_get_tablename( 'user_agents' );
				$total_hits = $wpdb->get_var( "SELECT COUNT(*) FROM $hits_table" );
				$total_uris = $wpdb->get_var( "SELECT COUNT(*) FROM $uris_table" );
				$total_user_agents = $wpdb->get_var( "SELECT COUNT(*) FROM $user_agents_table" );
				$count_hits = $wpdb->get_var( "SELECT COUNT(*) FROM $hits_table WHERE hit_id NOT IN (SELECT hit_id FROM $referrals_table WHERE hit_id IS NOT NULL)" );
				$count_uris = $wpdb->get_var( "SELECT COUNT(*) FROM $uris_table WHERE uri_id NOT IN (SELECT src_uri_id FROM $hits_table WHERE src_uri_id IS NOT NULL) AND uri_id NOT IN (SELECT dest_uri_id FROM $hits_table WHERE dest_uri_id IS NOT NULL)" );
				$count_user_agents = $wpdb->get_var( "SELECT COUNT(*) FROM $user_agents_table WHERE user_agent_id NOT IN (SELECT user_agent_id FROM $hits_table WHERE user_agent_id IS NOT NULL)" );
				echo '<ol>';
				printf( '<li>%1$s &mdash; <code>%2$d / %3$d</code></li>', esc_html__( 'Hits', 'affiliates' ), intval( $count_hits ), intval( $total_hits ) );
				printf( '<li>%1$s &mdash; <code>%2$d / %3$d</code></li>', esc_html__( 'URIs', 'affiliates' ), intval( $count_uris ), intval( $total_uris ) );
				printf( '<li>%1$s &mdash; <code>%2$d / %3$d</code></li>', esc_html__( 'User Agents', 'affiliates' ), intval( $count_user_agents ), intval( $total_user_agents ) );
				echo '</ol>';

				esc_html_e( 'If you want to delete the data, click the button to proceed.', 'affiliates' );
				echo ' ';
				esc_html_e( 'Once you click the button, the data will be deleted immediately.', 'affiliates' );
				echo ' ';
				esc_html_e( 'It can take a while to clean up a large number of data.', 'affiliates' );
				echo ' ';
				esc_html_e( 'This action cannot be undone.', 'affiliates' );
				echo '</p>';

				echo '<p>';
				echo '<form action="" name="data-cleaner" method="post">';
				printf( '<button id="affiliates-data-cleaner-clean" class="button button-primary" name="data-cleaner" type="submit">%s</button>', esc_html__( 'Delete', 'affiliates' ) );
				echo '<input type="hidden" name="action" value="clean"/>';
				echo wp_nonce_field( 'data-cleaner-action', 'data-cleaner-nonce', true, false );
				echo '<span style="padding: 4px">';
				printf(
					'<img id="affiliates-data-cleaner-throbber" src="%s" style="display:none" />',
					esc_url( AFFILIATES_PLUGIN_URL . 'images/affiliates-throbber.gif' )
				);
				echo '</span>';
				echo '</form>';
				echo '<div id="affiliates-data-cleaner-result" style="margin: 8px 0;"></div>';
				echo '</p>';

				break;
		}
	}
}
Affiliates_Data_Cleaner::init();
