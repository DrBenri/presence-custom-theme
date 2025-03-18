<?php

/*------------------------------------------*/
/* WPZOOM: Testimonials           			*/
/*------------------------------------------*/

class WPZOOM_Testimonials extends WP_Widget {

	function __construct() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'wpzoom-testimonial', 'description' => 'Displays latest testimonials in a slider' );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'wpzoom-testimonial' );

		/* Create the widget. */
		parent::__construct( 'wpzoom-testimonial', 'WPZOOM: Testimonial Slider', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {

		extract( $args );

		/* User-selected settings. */
		$title 	= apply_filters('widget_title', $instance['title'] );
		$show_photo = $instance['show_photo'];
		$show_author = $instance['show_author'];
		$show_author_position = $instance['show_author_position'];
		$show_author_company = $instance['show_author_company'];
		$show_author_company_link = $instance['show_author_company_link'];
		$random_post = $instance['random_post'];
        $show_count     = $instance['show_count'];

		/* Before widget (defined by themes). */
		echo $before_widget;


		/* Title of widget (before and after defined by themes). */

		if ( $title )
			echo $before_title . $title . $after_title;

        ?>

        <script type="text/javascript">
            jQuery(function($){
                $('#testimonial-slider-<?php echo $widget_id; ?>').flexslider({
                    selector: 'ul > li',
                    controlNav: false,
                    directionNav: true,
                    animationLoop: true,
                    animation: 'slide',
                    slideshow: true,
                    useCSS: true,
                    smoothHeight: false,
                    animationSpeed: 300,
                    minItems: 1,
                    maxItems: 1
                });
            });
            </script>

        <?php
        $orderby = !empty($random_post)? 'rand' : 'date';

		$loop = new WP_Query( array( 'post_type' => 'testimonial', 'posts_per_page' => $show_count, 'orderby' => $orderby) );


        ?>

        <div id="testimonial-slider-<?php echo $widget_id; ?>" class="testomonial_wrapper">

            <ul class="slides">

                <?php


        		while ( $loop->have_posts() ) : $loop->the_post();

        		$customFields = get_post_custom();

        		if (!empty($show_author)) {
        			$testimonial_author = $customFields['wpzoom_testimonial_author'][0];
        		}
        		if (!empty($show_author_position)) {
        			$testimonial_position = $customFields['wpzoom_testimonial_author_position'][0];
        		}
        		if (!empty($show_author_company)) {
        			$testimonial_company = $customFields['wpzoom_testimonial_author_company'][0];
        		}
        		if (!empty($show_author_company_link)) {
        			$testimonial_company_url = $customFields['wpzoom_testimonial_author_company_url'][0];
        		}

        		?>


                <li>

    			    <blockquote><?php the_content(); ?></blockquote>

                    <div class="testimonial_footer">
                        <?php
                            if (!empty($show_photo)) {

                                if ( has_post_thumbnail() ) : ?>
                                    <div class="testimonial-thumb">
                                        <?php the_post_thumbnail('testimonial-widget-author-photo'); ?>
                                    </div>
                                <?php endif;

                            }

                        ?>

                        <div class="testimonial_details">

                            <?php

                            if (!empty($testimonial_author)) echo "<h4>$testimonial_author</h4>";


                            if (!empty($testimonial_position)) echo "<span class=\"position\">$testimonial_position</span>";

                            if (!empty($testimonial_company) & !empty($testimonial_position)) { echo ", "; }


                            if (!empty($testimonial_company)) {
                                echo '<span class="company">';
                                if (!empty($testimonial_company_url)) echo "<a href=\"$testimonial_company_url\">";
                                echo $testimonial_company;
                                if (!empty($testimonial_company_url)) echo '</a>';
                                echo '</span>';
                            }

                            ?>

                        </div>

                    </div>

                </li>

    			<?php
    			endwhile;

    			//Reset query_posts
    			wp_reset_query();

                ?>

            </ul><!-- /.slides -->

        </div><!-- /.testimonial -->

        <?php

		/* After widget (defined by themes). */
		echo $after_widget;
	}


	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['show_photo'] = !empty($new_instance['show_photo']);
		$instance['show_author'] = !empty($new_instance['show_author']);
		$instance['show_author_position'] = !empty($new_instance['show_author_position']);
		$instance['show_author_company'] = !empty($new_instance['show_author_company']);
		$instance['show_author_company_link'] = !empty($new_instance['show_author_company_link']);
		$instance['random_post'] = !empty($new_instance['random_post']);
        $instance['show_count'] = $new_instance['show_count'];
		return $instance;
	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => '', 'show_title' => true, 'show_count' => 3, 'show_photo' => true, 'show_author' => true, 'show_author_position' => true, 'show_author_company' => true, 'show_author_company_link' => true, 'random_post' => true);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Widget Title:', 'wpzoom'); ?></label><br />
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" size="35" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_photo'); ?>" name="<?php echo $this->get_field_name('show_photo'); ?>" <?php checked( $instance['show_photo'] ); ?> />
			<label for="<?php echo $this->get_field_id('show_photo'); ?>"><?php _e('Display author photo', 'wpzoom'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>" <?php checked( $instance['show_author'] ); ?> />
			<label for="<?php echo $this->get_field_id('show_author'); ?>"><?php _e('Display author name', 'wpzoom'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_author_position'); ?>" name="<?php echo $this->get_field_name('show_author_position'); ?>" <?php checked( $instance['show_author_position'] ); ?> />
			<label for="<?php echo $this->get_field_id('show_author_position'); ?>"><?php _e('Display author position', 'wpzoom'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_author_company'); ?>" name="<?php echo $this->get_field_name('show_author_company'); ?>" <?php checked( $instance['show_author_company'] ); ?> />
			<label for="<?php echo $this->get_field_id('show_author_company'); ?>"><?php _e('Display author company', 'wpzoom'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_author_company_link'); ?>" name="<?php echo $this->get_field_name('show_author_company_link'); ?>" <?php checked( $instance['show_author_company_link'] ); ?> />
			<label for="<?php echo $this->get_field_id('show_author_company_link'); ?>"><?php _e('Link author company', 'wpzoom'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('random_post'); ?>" name="<?php echo $this->get_field_name('random_post'); ?>" <?php checked( $instance['random_post'] ); ?> />
			<label for="<?php echo $this->get_field_id('random_post'); ?>"><?php _e('Order Randomly', 'wpzoom'); ?></label>
		</p>

        <p>
            <label for="<?php echo $this->get_field_id( 'show_count' ); ?>"><?php _e('Show:', 'wpzoom'); ?></label>
            <input id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="<?php echo $instance['show_count']; ?>" type="text" size="2" /> <?php _e('testimonials', 'wpzoom'); ?>
        </p>

		<?php
	}
}

function wpzoom_register_testimonials_widget() {
	register_widget('WPZOOM_Testimonials');
}
add_action('widgets_init', 'wpzoom_register_testimonials_widget');