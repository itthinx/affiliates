<?php
/**
* class-affiliates-generator.php
*
* Copyright (c) 2010-2012 "kento" Karim Rahimpur www.itthinx.com
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
* @since affiliates 1.3.1
*/

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page generator
 */
class Affiliates_Generator {

	/**
	 * Creates the default affiliate area page (or pages).
	 *
	 * @return int post ID(s)
	 */
	public static function setup_pages() {

		do_action( 'affiliates_before_setup_pages' );

		$post_ids = array();

		// create a page with the dashboard shortcode
		$affiliate_area_page_content = '[affiliates_dashboard]';
		$affiliate_area_page_content = apply_filters( 'affiliates_affiliate_area_page_content', $affiliate_area_page_content );

		$postarr = array(
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_content'   => $affiliate_area_page_content,
			'post_status'    => 'publish',
			'post_title'     => __( 'Affiliate Area', 'affiliates' ),
			'post_type'      => 'page'
		);
		$post_id = wp_insert_post( $postarr, true ); // @since 5.4.1 cause error to be returned instead of 0
		if ( $post_id instanceof WP_Error ) {
			wp_admin_notice( // wp_kses_post's the message output so we do not escape here
				sprintf(
					__( 'The affiliate area page could not be created. Error: %s', 'affiliates' ),
					$post_id->get_error_message()
				),
				array( 'type' => 'error' )
			);
		} else {
			$post_ids[] = $post_id;
		}

		$post_ids = apply_filters( 'affiliates_setup_pages', $post_ids );

		do_action( 'affiliates_after_setup_pages', $post_ids );

		return $post_ids;
	}
}
