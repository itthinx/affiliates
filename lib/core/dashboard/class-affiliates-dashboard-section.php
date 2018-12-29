<?php
/**
 * class-affiliates-dashboard-section.php
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

/**
 * Implements common methods to all dashboard sections.
 */
abstract class Affiliates_Dashboard_Section implements I_Affiliates_Dashboard_Section {

	/**
	 * @var int
	 */
	protected $user_id = null;

	/**
	 * @var string the template to be used for the section, e.g. 'dashboard/login.php'
	 */
	protected $template = null;

	/**
	 * @var int the section's order
	 */
	protected $order = self::DEFAULT_ORDER;

	/**
	 * @var string Whether the template is only included for connected users.
	 */
	protected $require_user_id = false;

	/**
	 * Create a new dashboard section instance.
	 *
	 * Parameters :
	 * - user_id : (int) if not provided, will obtain it from the current user
	 * - order : (int) if provided, establishes the value for the section's order
	 *
	 * @param array $params
	 */
	public function __construct( $params = array() ) {
		// user_id
		if ( isset( $params['user_id'] ) ) {
			$this->user_id = intval( $params['user_id'] );
		} else {
			$this->user_id = get_current_user_id();
		}
		if ( $this->user_id === 0 ) {
			$this->user_id = null;
		} else {
			$user = get_user_by( 'id', $this->user_id );
			if ( $user === false || $user->ID === 0 ) {
				$this->user_id = null;
			}
		}
		// order
		if ( isset( $params['order'] ) ) {
			$this->order = intval( $params['order'] );
		}
	}

	/**
	 * Returns the user ID related to this instance.
	 *
	 * @return int or null
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * {@inheritDoc}
	 * @see I_Affiliates_Dashboard_Section::get_order()
	 */
	public function get_order() {
		return $this->order;
	}

	public static function get_default_order() {
		return self::DEFAULT_ORDER;
	}

	/**
	 * Outputs the dashboard section's template.
	 */
	public function render() {
		if ( !$this->require_user_id || $this->user_id !== null ) {
			Affiliates_Templates::include_template( $this->template, array( 'section' => $this ) );
		}
	}

}
