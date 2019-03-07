<?php
/**
 * class-affiliates-robot-cleaner.php
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
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cleans up hits from robots.
 */
class Affiliates_Robot_Cleaner {

	public static function init() {
		add_action(
			'wp_ajax_affiliates_robot_cleaner_clean',
			array( __CLASS__, 'clean' )
		);
	}

	/**
	 * Initializes the ajax nonce in the footer.
	 */
	public static function admin_footer() {
		echo
			'<script type="text/javascript">' .
			'affiliates_robot_cleaner_ajax_nonce = \'' . wp_create_nonce( 'affiliates-robot-cleaner-ajax-nonce' ) . '\';' .
			'</script>';
	}

	/**
	 * Ajax wp_ajax_affiliates_robot_cleaner_clean cleaner handler.
	 */
	public static function clean() {
		global $wpdb;

		$result = false;

		if ( !current_user_can( AFFILIATES_ADMINISTER_AFFILIATES ) ) {
			exit;
		}

		if ( check_ajax_referer( 'affiliates-robot-cleaner-ajax-nonce', 'affiliates_robot_cleaner_ajax_nonce' ) ) {
			set_time_limit( 0 );
			// remove all hits that are related to robots and don't have a referral related
			$hits_table = _affiliates_get_tablename( 'hits' );
			$referrals_table = _affiliates_get_tablename( 'referrals' );
			$robots_table = _affiliates_get_tablename( 'robots' );
			$user_agents_table = _affiliates_get_tablename( 'user_agents' );
			$query =
				"DELETE FROM $hits_table WHERE hit_id IN " .
				"( SELECT DISTINCT hits.hit_id FROM " .
				"( " .
				"SELECT h.hit_id " .
				"FROM $hits_table h " .
				"LEFT JOIN ( " .
				"SELECT a.user_agent_id FROM $user_agents_table a " .
				"LEFT JOIN $robots_table r ON a.user_agent LIKE CONCAT( '%%', r.name, '%%' ) " .
				"WHERE r.robot_id IS NOT NULL " .
				") AS rua ON h.user_agent_id = rua.user_agent_id " .
				"LEFT JOIN ( " .
				"SELECT COUNT(*) count, hit_id FROM $referrals_table WHERE hit_id IS NOT NULL GROUP BY hit_id " .
				") AS r ON r.hit_id = h.hit_id " .
				"WHERE " .
				"rua.user_agent_id IS NOT NULL " .
				"AND r.count IS NULL OR r.count = 0 " .
				") AS hits " .
				")";
			ob_start();
			$rows = 0;
			$result = $wpdb->query( $query );
			if ( $result ) {
				$rows = $wpdb->get_var( "SELECT ROW_COUNT()" );
			}
			$ob = ob_get_clean();
			affiliates_log_warning( $ob );
			echo json_encode( $rows );
		}
		exit;
	}

	public static function admin() {

		global $wpdb, $affiliates_version;

		if ( !current_user_can( AFFILIATES_ADMINISTER_AFFILIATES ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}

		add_action( 'admin_footer', array( __CLASS__, 'admin_footer' ) );
		wp_enqueue_script( 'affiliates-robot-cleaner', AFFILIATES_PLUGIN_URL . 'js/affiliates-robot-cleaner.js', array( 'jquery' ), $affiliates_version, true );

		wp_localize_script(
			'affiliates-robot-cleaner',
			'affiliates_robot_cleaner',
			array(
				'rows_deleted' => _x( 'Hits deleted:', 'robot cleaner', 'affiliates' ),
				'failed' => _x( 'Could not delete any hits', 'robot cleaner', 'affiliates' )
			)
		);

		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : null;

		if (
			$action !== null &&
			(
				!isset( $_REQUEST['robot-cleaner-nonce'] ) ||
				!wp_verify_nonce( $_REQUEST['robot-cleaner-nonce'], 'robot-cleaner-action' )
			)
		) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}

		echo '<h3>' . esc_html__( 'Robot Cleaner', 'affiliates' ) . '</h3>';

		switch ( $action ) {
			case null :
				// ask if you want to check for robot hits and clean them up
				printf(
					esc_html__( 'Current list of robots defined under %s:', 'affiliates' ),
					sprintf(
						'<a href="%s">%s</a>',
						add_query_arg( 'section', 'general', admin_url( 'admin.php?page=affiliates-admin-settings' ) ),
						esc_html__( 'General', 'affiliates' )
					)
				);
				echo '</p>';
				$robots_table = _affiliates_get_tablename( 'robots' );
				if ( $robots = $wpdb->get_results( "SELECT name FROM $robots_table", OBJECT ) ) {
					echo '<ol>';
					foreach ( $robots as $robot ) {
						printf( '<li><code>%s</code></li>',  esc_html( $robot->name ) );
					}
					echo '</ol>';
					echo '<p>';
					esc_html_e( 'Check for hits from robots that can be removed &hellip;', 'affiliates' );
					echo '</p>';
					echo
						'<form action="" name="robot-cleaner" method="post">' .
							'<input class="button button-primary" name="robot-cleaner" type="submit" value="' . __( 'Check', 'affiliates' ) .'" />' .
							'<input type="hidden" name="action" value="confirm"/>' .
							wp_nonce_field( 'robot-cleaner-action', 'robot-cleaner-nonce', true, false ) .
						'</form>';
					echo '<p>';
				} else {
					echo '<p>' .
						esc_html__( 'There are no robots defined.', 'affiliates' ) .
						' ' .
						esc_html__( 'There must be at least one entry to proceed to clean up existing hits from robots.', 'affiliates' ) .
						'</p>';
				}
				break;
			case 'confirm' :
				// show the current eligible count of hits per user agent identified as robots
				$hits_table = _affiliates_get_tablename( 'hits' );
				$referrals_table = _affiliates_get_tablename( 'referrals' );
				$robots_table = _affiliates_get_tablename( 'robots' );
				$user_agents_table = _affiliates_get_tablename( 'user_agents' );
				$query =
					"SELECT COUNT(*) AS count, rua.user_agent AS user_agent " .
					"FROM $hits_table h " .
					"LEFT JOIN ( " .
					"SELECT a.user_agent_id, a.user_agent FROM $user_agents_table a " .
					"LEFT JOIN $robots_table r ON a.user_agent LIKE CONCAT( '%%', r.name, '%%' ) " .
					"WHERE r.robot_id IS NOT NULL " .
					") AS rua ON h.user_agent_id = rua.user_agent_id " .
					"LEFT JOIN ( " .
					"SELECT COUNT(*) count, hit_id FROM $referrals_table WHERE hit_id IS NOT NULL GROUP BY hit_id " .
					") AS r ON r.hit_id = h.hit_id " .
					"WHERE " .
					"rua.user_agent_id IS NOT NULL " .
					"AND r.count IS NULL OR r.count = 0 " .
					"GROUP BY h.user_agent_id";
				$robot_hits = $wpdb->get_results( $query );
				if ( is_array( $robot_hits ) && count( $robot_hits ) > 0 ) {
					echo '<p>' . esc_html__( 'The following matching hits have been found and can be cleaned up.', 'affiliates' ) . '</p>';
					echo '<table style="margin: 4px; border: 1px solid #333; background-color: #fff; color: #333;">';
					echo '<thead>';
					echo '<tr>';
					echo '<th style="padding: 4px; border-bottom: 1px solid #999;">' . esc_html__( 'Hits', 'affiliates' ) . '</th>';
					echo '<th style="padding: 4px; border-bottom: 1px solid #999;">' . esc_html__( 'User Agent', 'affiliates' ) . '</th>';
					echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					$count = 0;
					foreach ( $robot_hits as $robot_hit ) {
						$count += $robot_hit->count;
						echo '<tr>';
						echo '<td style="text-align:right; width:10%; padding: 4px;">';
						echo $robot_hit->count;
						echo '</td>';
						echo '<td style="padding: 4px;">';
						echo esc_html( $robot_hit->user_agent );
						echo '</td>';
						echo '</tr>';
					}
					echo '<tr>';
					echo '<td style="text-align:right; width:10%; padding: 4px; border-top: 1px solid #999;">';
					printf( '<strong>%d</strong>', $count );
					echo '</td>';
					echo '<td style="padding: 4px; border-top: 1px solid #999;">';
					echo esc_html__( 'Total', 'affiliates' );
					echo '</td>';
					echo '</tr>';
					echo '</tbody>';
					echo '</table>';
					echo '<p>';
					esc_html_e( 'If you want to delete these matching hits, click the button to proceed.', 'affiliates' );
					echo ' ';
					esc_html_e( 'Once you click the button, the hits will be deleted immediately.', 'affiliates' );
					echo ' ';
					esc_html_e( 'It can take a while to clean up a large number of hits.', 'affiliates' );
					echo ' ';
					esc_html_e( 'This action cannot be undone.', 'affiliates' );
					echo '</p>';
					echo '<p>';
					echo
						'<form action="" name="robot-cleaner" method="post">' .
							'<input id="affiliates-robot-cleaner-clean" class="button button-primary" name="robot-cleaner" type="submit" value="' . __( 'Delete', 'affiliates' ) .'" />' .
							'<input type="hidden" name="action" value="clean"/>' .
							wp_nonce_field( 'robot-cleaner-action', 'robot-cleaner-nonce', true, false ) .
							'<span style="padding: 4px">' .
							sprintf(
								'<img id="affiliates-robot-cleaner-throbber" src="%s" style="display:none" />',
								esc_url( AFFILIATES_PLUGIN_URL . 'images/affiliates-throbber.gif' )
							) .
							'</span>' .
						'</form>';
					echo '<div id="affiliates-robot-cleaner-result" style="margin: 8px 0;"></div>';
					echo '</p>';
				} else {
					echo '<p>' . esc_html__( 'No matching hits have been found.', 'affiliates' ) . '</p>';
				}
				break;
		}
	}
}
Affiliates_Robot_Cleaner::init();
