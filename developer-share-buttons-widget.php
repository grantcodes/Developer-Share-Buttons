<?php
/**
 * The developer share buttons widgets
 *
 * @package  dev-share-buttons
 */

if ( ! class_exists( 'Dev_Share_Buttons_Widget' ) ) {

	add_action( 'widgets_init', array( 'DeveloperShareButtons', 'widget' ) );

	/**
	 * Adds Dev_Share_Buttons_Widget widget.
	 */
	class Dev_Share_Buttons_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			parent::__construct(
				DeveloperShareButtons::$slug_ . '_widget',
				__( 'Developer Share Buttons', 'dev-share-buttons' ),
				array(
					'description' => __( 'Developer Share Buttons Widget', 'dev-share-buttons' ),
				)
			);
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {
			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $args['before_widget'];
			if ( ! empty( $title ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			if ( function_exists( 'the_dev_share_buttons' ) ) {
				the_dev_share_buttons( $instance['services'], $instance['url'] );
			}
			echo $args['after_widget'];
		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			if ( isset( $instance['title'] ) ) {
				$title = $instance['title'];
			}
			if ( isset( $instance['url'] ) ) {
				$url = $instance['url'];
			}
			if ( isset( $instance['services'] ) ) {
				$services = $instance['services'];
			}
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php if ( ! empty( $title ) ) { echo esc_attr( $title ); } ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e( 'Url:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="url" placeholder="Defaults to current url" value="<?php if ( ! empty( $url ) ) { echo esc_attr( $url ); } ?>" />
			</p>
			<p>
				<label><?php _e( 'Services:' ); ?></label>
			</p>
			<p>
			<?php foreach ( DeveloperShareButtons::get_services() as $service_id => $service ) : ?>
				<?php if ( $service['url_structure'] ) : ?>
					<label><input type="checkbox" class="checkbox" <?php checked( isset( $services ) && in_array( $service_id, $services ) ); ?> name="<?php echo $this->get_field_name( 'services' ); ?>[]" value="<?php echo esc_attr( $service_id ); ?>"> <?php _e( $service['title'] ); ?></label><br>
				<?php endif; ?>
			<?php endforeach; ?>
				</p>
			<?php
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['url'] = ( ! empty( $new_instance['url'] ) ) ? strip_tags( $new_instance['url'] ) : '';
			if ( is_array( $new_instance['services'] ) ) {
				$instance['services'] = $new_instance['services'];
			} else {
				$instance['services'] = array();
			}

			return $instance;
		}
	}
}

if ( ! class_exists( 'Dev_Share_Buttons_Profiles_Widget' ) ) {

	add_action( 'widgets_init', array( 'DeveloperShareButtons', 'widget' ) );

	/**
	 * Adds Dev_Share_Buttons_Profiles_Widget widget.
	 */
	class Dev_Share_Buttons_Profiles_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			parent::__construct(
				DeveloperShareButtons::$slug_ . '_profiles_widget',
				__( 'Developer Share Buttons Profiles', 'dev-share-buttons-profiles' ),
				array(
					'description' => __( 'Developer Share Buttons Social Profiles Widget', 'dev-share-buttons-profiles' ),
				)
			);
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {
			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $args['before_widget'];
			if ( ! empty( $title ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			if ( function_exists( 'the_dev_profile_links' ) ) {
				the_dev_profile_links();
			}
			echo $args['after_widget'];
		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			if ( isset( $instance['title'] ) ) {
				$title = $instance['title'];
			}
			?>
				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php if ( ! empty( $title ) ) { echo esc_attr( $title ); } ?>" />
				</p>
			<?php
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

			return $instance;
		}
	}
}
