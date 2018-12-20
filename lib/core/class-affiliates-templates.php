<?php
/**
 * class-affiliates-templates.php
 *
 * Copyright (c) 2010 - 2018 "kento" Karim Rahimpur www.itthinx.com
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
 * Template loader.
 */
class Affiliates_Templates {

	/**
	 * @var string location (subfolder) of the templates within themes
	 */
	const TEMPLATE_ROOT = 'affiliates';

	/**
	 * Get the template and include it.
	 *
	 * @param string $template
	 * @param array $args
	 */
	public static function include_template( $template, $args = array() ) {

		// Do we have the template in the theme?
		$template_filename = locate_template( array(
			trailingslashit( apply_filters( 'affiliates_template_root', self::TEMPLATE_ROOT ) ) . $template,
			$template
		) );

		// If we don't have a particular template we use our OOTB one:
		if ( $template_filename === '' ) {
			$template_filename = AFFILIATES_CORE_DIR . '/templates/' . $template;
		}

		if ( file_exists( $template_filename ) ) {
			include $template_filename;
		}

	}
}
