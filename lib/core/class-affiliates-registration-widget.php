<?php
/**
 * class-affiliates-registration-widget.php
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
 * @since affiliates 1.1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.WP.AlternativeFunctions.strip_tags_strip_tags

/**
 * Affiliate registration form as a widget.
 *
 * @link http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class Affiliates_Registration_Widget extends WP_Widget {

	/**
	 * Creates an affiliate registration widget.
	 */
	function __construct() {
		parent::__construct( false, $name = 'Affiliates Registration' );
	}

	/**
	 * Widget output
	 *
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ) {

		if ( affiliates_user_is_affiliate() ) {
			return;
		}

		extract( $args );
		$title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$widget_id = !empty( $args['widget_id'] ) ? $args['widget_id'] : '';
		echo $before_widget;
		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		$ext = '-' . $widget_id;

		$options = array(
			'is_widget' => true
		);
		if ( isset( $instance['terms_post_id'] ) ) {
			$options['terms_post_id'] = $instance['terms_post_id'];
		}
		echo Affiliates_Registration::render_form( $options );
		echo $after_widget;
	}

	/**
	 * Save widget options
	 *
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {
		$settings = $old_instance;

		if ( !empty( $new_instance['title'] ) ) {
			$settings['title'] = strip_tags( $new_instance['title'] );
		} else {
			unset( $settings['title'] );
		}
		if ( !empty( $new_instance['terms_post_id'] ) ) {
			$terms_post_id = $new_instance['terms_post_id'];
			if ( $post = get_post( $terms_post_id ) ) {
				$settings['terms_post_id'] = $post->ID;
			} else if ( $post = Affiliates_Utility::get_post_by_title( $terms_post_id ) ) {
				$settings['terms_post_id'] = $post->ID;
			} else {
				unset( $settings['terms_post_id'] );
			}
		}
		return $settings;
	}

	/**
	 * Output admin widget options form
	 *
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {

		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'affiliates' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php

		// terms_post_id
		// post_id
		$terms_post_id = isset( $instance['terms_post_id'] ) ? $instance['terms_post_id'] : '';
		echo '<p>';
		printf(
			'<label class="title" title="%1$s" for="%2$s">%3$s</label>',
			esc_attr__( 'Terms and conditions', 'affiliates' ),
			esc_attr( $this->get_field_id( 'terms_post_id' ) ),
			esc_html__( 'Terms Page or Post ID', 'affiliates' )
		);
		echo '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'terms_post_id' ) ) . '" name="' . esc_attr( $this->get_field_name( 'terms_post_id' ) ) . '" type="text" value="' . esc_attr( $terms_post_id ) . '" />';
		echo '<br/>';
		echo '<span class="description">' . esc_html__( "Write part of the title or the post ID. If left empty, no terms disclaimer will be shown.", 'affiliates' ) . '</span>';
		if ( !empty( $terms_post_id ) && ( $post_title = get_the_title( $terms_post_id ) ) ) {
			echo '<br/>';
			echo '<span class="description"> ';
			printf(
				/* translators: link */
				esc_html__( 'Terms page: %s', 'affiliates' ) ,
				'<a target="_blank" href="'. esc_url( get_permalink( $terms_post_id ) ) .'">' . esc_html( $post_title ) . '</a>'
			);
			echo '</span>';
		}
		echo '</p>';
	}
}// class Affiliates_Registration_Widget
