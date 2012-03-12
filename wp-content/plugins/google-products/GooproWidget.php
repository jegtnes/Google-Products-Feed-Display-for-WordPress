<?php
    //adapted from http://abhirama.wordpress.com/2010/06/07/wordpress-plugin-and-widget-tutorial - thanks!
    class GooproWidget extends WP_Widget {
        function GooproWidget() {
            parent::WP_Widget( false, $name = 'Google Products Widget' );
        }

        function widget( $args, $instance ) {
            extract( $args );
            $title = apply_filters( 'widget_title', $instance['title'] );
            echo $before_widget;
            if ($title) {
                echo $before_title . $title . $after_title;
            }
            ?>

            <div class="goopro_widget">
                <p>This is where products are meant to appear.</p>
            </div>

            <?php
            echo $after_widget;
        }

        function update( $new_instance, $old_instance ) {
            return $new_instance;
        }

        function form( $instance ) {
            $title = esc_attr( $instance['title'] );
            ?>

            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
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
