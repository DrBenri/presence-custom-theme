<?php

/*------------------------------------------*/
/* WPZOOM: Call to Action                   */
/*------------------------------------------*/

class Wpzoom_Call_To_Action extends WP_Widget {

	/* Widget setup. */
	function __construct() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'wpzoom-calltoaction', 'description' => __('Custom WPZOOM widget that displays a call-to-action message and button.', 'wpzoom') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'wpzoom-call-to-action-venture' );

		/* Create the widget. */
		parent::__construct( 'wpzoom-call-to-action-venture', __('WPZOOM: Call to Action', 'wpzoom'), $widget_ops, $control_ops );
	}

	/* How to display the widget on the screen. */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
        $title          = apply_filters('widget_title', $instance['title'] );
		$msg = trim($instance['msg']);
		$btntxt = strip_tags( trim($instance['btn_text']) );
        $btnhref = esc_url( trim($instance['btn_href']) );
		$cta_align = $instance['cta_align'];

		if ( !empty( $msg ) ) {

			echo $before_widget;


            echo '<div class="cta_align_'.$cta_align.'">';


                if ( $cta_align == 'center' ) {

                    /* Title of widget (before and after defined by themes). */
                    if ( $title )
                        echo $before_title . $title . $after_title;


    			    echo '<span class="cta-msg">' . apply_filters( 'the_content', trim($msg) ) . '</span>';

                    if ( !empty( $btntxt ) && !empty( $btnhref ) ) echo '<a href="' . $btnhref . '" class="cta-btn">' . $btntxt . '</a>';


                } else {

                    echo '<div class="cta_wrapper">';

                        if ( !empty( $btntxt ) && !empty( $btnhref ) ) echo '<a href="' . $btnhref . '" class="cta-btn">' . $btntxt . '</a>';

                        echo '<div class="cta_content">';

                            /* Title of widget (before and after defined by themes). */
                            if ( $title )
                                echo $before_title . $title . $after_title;


                            echo '<span class="cta-msg">' . apply_filters( 'the_content', trim($msg) ) . '</span>';

                        echo '</div>';
                    echo '</div>';
                }

            echo '</div>';

            echo $after_widget;
		}
	}

		/* Update the widget settings.*/
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

            $instance['title'] = strip_tags( $new_instance['title'] );

			/* Strip tags for title and name to remove HTML (important for text inputs). */
			if ( current_user_can('unfiltered_html') )
				$instance['msg'] = $new_instance['msg'];
			else
				$instance['msg'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['msg']) ) ); // wp_filter_post_kses() expects slashed
			$instance['btn_text'] = strip_tags( $new_instance['btn_text'] );
            $instance['btn_href'] = esc_url( $new_instance['btn_href'] );
			$instance['cta_align'] = $new_instance['cta_align'];

			return $instance;
		}

		/** Displays the widget settings controls on the widget panel.
		 * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff. */
		function form( $instance ) {
			/* Set up some default widget settings. */
			$defaults = array( 'title' => '', 'msg' => '', 'btn_text' => '', 'btn_href' => '', 'cta_align' => 'right' );
			$instance = wp_parse_args( (array) $instance, $defaults );
			$msg = esc_textarea($instance['msg']);
			$btntxt = strip_tags($instance['btn_text']);
			$btnhref = strip_tags($instance['btn_href']);

			?>

            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label><br />
                <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
            </p>

            <p>
				<label for="<?php echo $this->get_field_id('msg'); ?>"><?php _e('Message:', 'wpzoom'); ?></label>
				<textarea class="widefat" rows="3" cols="20" id="<?php echo $this->get_field_id('msg'); ?>" name="<?php echo $this->get_field_name('msg'); ?>"><?php echo $msg; ?></textarea>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('btn_text'); ?>"><?php _e('Button Label:', 'wpzoom'); ?></label>
				<input type="text" value="<?php echo $btntxt; ?>" name="<?php echo $this->get_field_name('btn_text'); ?>" id="<?php echo $this->get_field_id('btn_text'); ?>" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('btn_href'); ?>"><?php _e('Button URL:', 'wpzoom'); ?></label>
				<input type="text" value="<?php echo $btnhref; ?>" name="<?php echo $this->get_field_name('btn_href'); ?>" id="<?php echo $this->get_field_id('btn_href'); ?>" class="widefat" />
			</p>

            <p>
                <label for="<?php echo $this->get_field_id( 'cta_align' ); ?>"><?php _e('Text Alignment', 'wpzoom'); ?>:</label>
                <select id="<?php echo $this->get_field_id( 'cta_align' ); ?>" name="<?php echo $this->get_field_name( 'cta_align' ); ?>">
                <option value="right" <?php if ( $instance['cta_align'] == 'right' ) echo 'selected="selected"'; ?>><?php _e('Right', 'wpzoom'); ?></option>

                    <option value="center" <?php if ( $instance['cta_align'] == 'center' ) echo 'selected="selected"'; ?>><?php _e('Center', 'wpzoom'); ?></option>
                    <option value="left" <?php if ( $instance['cta_align'] == 'left' ) echo 'selected="selected"'; ?>><?php _e('Left', 'wpzoom'); ?></option>

                </select>
            </p>

            <?php
		}
}

function wpzoom_register_cta_widget() {
	register_widget('Wpzoom_Call_To_Action');
}
add_action('widgets_init', 'wpzoom_register_cta_widget');
?>