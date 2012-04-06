<?php
	//adapted from http://abhirama.wordpress.com/2010/06/07/wordpress-plugin-and-widget-tutorial - thanks!
	//TODO: Comment / refactor this code
	class GooproWidget extends WP_Widget {
		function GooproWidget() {
				parent::WP_Widget( false, $name = 'Google Products Widget' );
		}

		function widget( $args, $instance ) {
			
			global $wpdb;
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			//sets the table name with the appropriate prefix
			$table_name = $wpdb->prefix . "goopro";

			extract( $args );

			//sets the title and applies a filter to it
			$title = apply_filters( 'widget_title', $instance['title'] );

			//sets the number of products
			//if an invalid number is given, default to 5
			$num = $instance['num'];
			if ($num <= 0) {
				$num = 5;
			}

			echo $before_widget;
			if ($title) {
				echo $before_title . $title . $after_title;
			}
			?>

			<div class="goopro_widget">
				<?php
						goopro_getproducts($num);
				?>
			</div>

			<?php
			echo $after_widget;
		}

		function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['num'] = (int) $new_instance['num'];
			return $instance;
		}

		function form( $instance ) {
			$title = esc_attr( $instance['title'] );
			$num = esc_attr( $instance['num'] );
			?>

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'num' ); ?>"><?php _e( 'Number of products to show:' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'num' ); ?>" name="<?php echo $this->get_field_name( 'num' ); ?>" type="text" value="<?php echo $num; ?>" />
				</label>
			</p>
			<?php
		}
	}

	add_action( 'widgets_init', 'goopro_widget_init' );

	function goopro_widget_init() {
		register_widget( 'GooproWidget' );
	}
?>
