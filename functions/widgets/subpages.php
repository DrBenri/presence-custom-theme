<?php

/*------------------------------------------*/
/* WPZOOM: Sub-Pages						*/
/*------------------------------------------*/

class WPZOOM_Widget_Subpages extends WP_Widget {

	/* Widget setup. */
	function __construct() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'wpzoom_subpages', 'description' => __('Displays a specific number of sub-pages from a parent page.', 'wpzoom') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'wpzoom-widget-subpages' );

		/* Create the widget. */
		parent::__construct( 'wpzoom-widget-subpages', __('WPZOOM: Sub-Pages', 'wpzoom'), $widget_ops, $control_ops );
	}

	/* How to display the widget on the screen. */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$page_id = $instance['page_id'];
		$page_order = $instance['page_order'];
        $page_number = $instance['page_number'];
 		$page_row = $instance['page_row'];
        $show_excerpt = $instance['page_excerpt'];
        $read_more = $instance['read_more'];
 		$thumb_background = $instance['thumb_background'];


		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( $title )

			echo $before_title . $title . $after_title;

		?>
		<ul class="wpzoom-subpages subpages-<?php echo $page_row; ?>">
		<?php
			$i = 0;

			if ($page_order == 'title' || $page_order == 'date_old') {
				$page_order_asc = 'ASC';
			} else {
				$page_order_asc = 'DESC';
			}

			if ($page_order == 'date_new' || $page_order == 'date_old') {
				$page_order = 'date';
			}

			if ($page_id > 0) {
				$loop = new WP_Query( array( 'posts_per_page' => $page_number, 'orderby' => $page_order, 'order' => $page_order_asc, 'post_parent' => $page_id, 'post_type' => 'page' ) );

				while ( $loop->have_posts() ) : $loop->the_post(); global $post;
				?>

				<li>

                    <?php if ( !empty($thumb_background) ) { ?>

                        <div class="post_thumb_withbg">

                            <?php
                            $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'loop-retina');
                            $style = ' style="background-image:url(\'' . wpzoom_get_value($large_image_url, '', 0) . '\')"';
                            ?>

                            <div class="page_background" <?php echo $style; ?>>


                                <div class="featured_page_content">

                                    <h3 class="title"><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h3>

                                    <?php if (!empty($show_excerpt)) { ?><p class="post-excerpt"><?php echo get_the_excerpt(); ?></p><?php } ?>

                                   <?php if (!empty($read_more)) { ?><a class="more_link" href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php _e('Read More', 'wpzoom'); ?></a><?php } ?>

                                </div><!-- .post-content -->

                            </div>

                        </div>


                    <?php } else { ?>

                        <div class="post-thumb">

                            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'loop-retina' ); ?></a>

                        </div>


                        <div class="post-content">

                            <h3 class="title"><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h3>

                            <?php if (!empty($show_excerpt)) { ?><p class="post-excerpt"><?php echo get_the_excerpt(); ?></p><?php } ?>

                           <?php if (!empty($read_more)) { ?><a class="more_link" href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php _e('Read More', 'wpzoom'); ?></a><?php } ?>

                        </div><!-- .post-content -->

                    <?php } ?>


				</li>


			<?php endwhile;

		} // while ?>



		</ul><!-- .wpzoom-subpages-->

        <div class="clear"></div>

		<?php
		// echo $after_widget;
		wp_reset_query();

		/* After widget (defined by themes). */
		echo $after_widget;

		}

		/* Update the widget settings.*/
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			/* Strip tags for title and name to remove HTML (important for text inputs). */
			$instance['title'] = $new_instance['title'];
			$instance['page_id'] = $new_instance['page_id'];
			$instance['page_order'] = $new_instance['page_order'];
            $instance['page_number'] = $new_instance['page_number'];
 			$instance['page_row'] = $new_instance['page_row'];
            $instance['page_excerpt'] = !empty($new_instance['page_excerpt']);
            $instance['read_more'] = !empty($new_instance['read_more']);
 			$instance['thumb_background'] = !empty($new_instance['thumb_background']);

			return $instance;
		}

		/** Displays the widget settings controls on the widget panel.
		 * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff. */
		function form( $instance ) {

			/* Set up some default widget settings. */
			$defaults = array('title' => '', 'page_id' => '0', 'page_order' => 'none', 'page_number' => 4, 'page_excerpt' => true, 'read_more' => true, 'thumb_background' => false, 'page_row' => '4');
			$instance = wp_parse_args( (array) $instance, $defaults );
	    ?>

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Widget title', 'wpzoom'); ?>:</label><br />
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
			</p>

 			<p>
				<label for="<?php echo $this->get_field_id('page_id'); ?>"><?php _e('Select parent page:', 'wpzoom'); ?></label>
				<?php wp_dropdown_pages( array(  'show_option_none' => __( '— Select —', 'wpzoom' ),  'option_none_value' => '0', 'name' => $this->get_field_name('page_id'), 'id' => $this->get_field_id('page_id'), 'selected' => (int) $instance['page_id'] ) ); ?>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'page_order' ); ?>"><?php _e('Order', 'wpzoom'); ?>: </label>
				<select id="<?php echo $this->get_field_id( 'page_order' ); ?>" name="<?php echo $this->get_field_name( 'page_order' ); ?>">
					<option value="none" <?php if ( $instance['page_order'] == 'none' ) echo 'selected="selected"'; ?>><?php _e('Default', 'wpzoom'); ?></option>
					<option value="title" <?php if ( $instance['page_order'] == 'title' ) echo 'selected="selected"'; ?>><?php _e('By Title Alphabetically', 'wpzoom'); ?></option>
					<option value="date_new" <?php if ( $instance['page_order'] == 'date_new' ) echo 'selected="selected"'; ?>><?php _e('By Creation Date (newest first)', 'wpzoom'); ?></option>
					<option value="date_old" <?php if ( $instance['page_order'] == 'date_old' ) echo 'selected="selected"'; ?>><?php _e('By Creation Date (oldest first)', 'wpzoom'); ?></option>
					<option value="menu_order" <?php if ( $instance['page_order'] == 'menu_order' ) echo 'selected="selected"'; ?>><?php _e('By Page Order', 'wpzoom'); ?></option>

				</select>
			</p>



			<p>
				<label for="<?php echo $this->get_field_id( 'page_number' ); ?>"><?php _e('Number of pages', 'wpzoom'); ?>: </label>
				<input id="<?php echo $this->get_field_id( 'page_number' ); ?>" name="<?php echo $this->get_field_name( 'page_number' ); ?>" value="<?php echo $instance['page_number']; ?>" type="text" size="2" />
			</p>



            <p>
                <label for="<?php echo $this->get_field_id( 'page_row' ); ?>"><?php _e('Pages per Row', 'wpzoom'); ?>: </label>
                <select id="<?php echo $this->get_field_id( 'page_row' ); ?>" name="<?php echo $this->get_field_name( 'page_row' ); ?>">
                    <option value="3" <?php if ( $instance['page_row'] == '3' ) echo 'selected="selected"'; ?>><?php _e('3', 'wpzoom'); ?></option>
                    <option value="4" <?php if ( $instance['page_row'] == '4' ) echo 'selected="selected"'; ?>><?php _e('4', 'wpzoom'); ?></option>
                    <option value="5" <?php if ( $instance['page_row'] == '5' ) echo 'selected="selected"'; ?>><?php _e('5', 'wpzoom'); ?></option>

                </select>
            </p>

			<p>
				<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('page_excerpt'); ?>" name="<?php echo $this->get_field_name('page_excerpt'); ?>"  <?php checked( $instance['page_excerpt'] ); ?> />
				<label for="<?php echo $this->get_field_id('page_excerpt'); ?>"><?php _e('Display Page Excerpt', 'wpzoom'); ?></label>
			</p>

            <p>
                <input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('read_more'); ?>" name="<?php echo $this->get_field_name('read_more'); ?>"  <?php checked( $instance['read_more'] ); ?> />
                <label for="<?php echo $this->get_field_id('read_more'); ?>"><?php _e('Display Read More button', 'wpzoom'); ?></label>
            </p>

            <p>
                <input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('thumb_background'); ?>" name="<?php echo $this->get_field_name('thumb_background'); ?>"  <?php checked( $instance['thumb_background'] ); ?> />
                <label for="<?php echo $this->get_field_id('thumb_background'); ?>"><?php _e('Display Featured Image as Background', 'wpzoom'); ?></label>
            </p>


		<?php
		}
}

function wpzoom_register_subpages_widget() {
	register_widget('WPZOOM_Widget_Subpages');
}
add_action('widgets_init', 'wpzoom_register_subpages_widget');
?>