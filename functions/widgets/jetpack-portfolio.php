<?php

/*------------------------------------------*/
/* WPZOOM: Jetpack Portoflio                 */
/*------------------------------------------*/

class WPZOOM_Portfolio_Showcase extends WP_Widget {

    function __construct() {
        /* Widget settings. */
        $widget_ops = array( 'classname' => 'portfolio-showcase', 'description' => 'Displays latest Jetpack Portfolio posts.' );

        /* Widget control settings. */
        $control_ops = array( 'id_base' => 'wpzoom-portfolio-showcase' );

        /* Create the widget. */
        parent::__construct( 'wpzoom-portfolio-showcase', 'WPZOOM: Jetpack Portfolio', $widget_ops, $control_ops );
    }

    function widget( $args, $instance ) {
        extract( $args );

        /* User-selected settings. */
        $title = apply_filters( 'widget_title', $instance['title'] );
        $category = $instance['category'];
        $show_count = $instance['show_count'];
        $show_excerpt = !empty($instance['show_excerpt']);
        $show_category = !empty($instance['show_category']);
        $view_all_enabled = !empty($instance['view_all_enabled']);
        $view_all_text = $instance['view_all_text'];
        $view_all_link = $instance['view_all_link'];

        /* Before widget (defined by themes). */
        echo $before_widget;

        /* Title of widget (before and after defined by themes). */
        if ( $title )
            echo $before_title . $title . $after_title;

        $args = array(
            'post_type' => 'jetpack-portfolio',
            'posts_per_page' => $show_count,
        );

        if ( $category ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'jetpack-portfolio-type',
                    'terms' => $category,
                    'field' => 'term_id',
                )
            );
        }

        $wp_query = new WP_Query( $args );
        ?>

        <?php if ( $wp_query->have_posts() ) : ?>


                <section class="recent-projects">

                    <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="post-thumb"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <?php the_post_thumbnail('portfolio'); ?>
                                </a></div>
                            <?php endif; ?>

                            <section class="entry-body">

                                <?php the_title( sprintf( '<h3 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' ); ?>

                                <div class="entry-meta">
                                    <?php if ( $show_category == true) { ?><span class="entry-category"><?php the_terms($wp_query->post->ID, 'jetpack-portfolio-type'); ?></span><?php } ?>
                                </div>

                                <?php if ( $show_excerpt == true) { ?>

                                    <div class="entry-content">
                                        <?php the_excerpt(); ?>
                                    </div>

                               <?php } ?>

                            </section>

                            <div class="clearfix"></div>
                        </article><!-- #post-<?php the_ID(); ?> -->

                    <?php endwhile; ?>

                </section>


        <?php endif; ?>

        <?php wp_reset_query(); ?>

        <?php if ( $view_all_enabled ) : ?>

            <div class="wpz-btn-center">
                <a class="wpz-btn btn" href="<?php echo esc_url( $view_all_link ); ?>" title="<?php echo esc_attr( $view_all_text ); ?>">
                    <?php echo esc_html( $view_all_text ); ?>
                </a>
            </div><!-- .portfolio-view_all-link -->

        <?php endif; ?>


        <?php
        /* After widget (defined by themes). */
        echo $after_widget;
    }


    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        /* Strip tags (if needed) and update the widget settings. */
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['category'] = $new_instance['category'];
        $instance['show_count'] = $new_instance['show_count'];
        $instance['show_excerpt'] = !empty($new_instance['show_excerpt']);
        $instance['show_category'] = !empty($new_instance['show_category']);
        $instance['view_all_enabled'] = !empty($new_instance['view_all_enabled']);
        $instance['view_all_text'] = $new_instance['view_all_text'];
        $instance['view_all_link'] = $new_instance['view_all_link'];

        return $instance;
    }

    function form( $instance ) {

        /* Set up some default widget settings. */
        $defaults = array( 'title' => 'Our Work', 'category' => 0,  'show_count' => 6, 'show_excerpt' => false, 'show_category' => true, 'view_all_enabled' => true, 'view_all_text' => 'View All', 'view_all_link' => '#' );
        $instance = wp_parse_args( (array) $instance, $defaults ); ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label><br />
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'category' ); ?>">Category:</label>
            <select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
                <option value="0" <?php if ( ! $instance['category'] ) echo 'selected="selected"'; ?>>All</option>
                <?php
                $categories = get_categories( array( 'taxonomy' => 'jetpack-portfolio-type' ) );

                foreach( $categories as $cat ) {
                    echo '<option value="' . $cat->cat_ID . '"';

                    if ( $cat->cat_ID == $instance['category'] ) echo  ' selected="selected"';

                    echo '>' . $cat->cat_name . ' (' . $cat->category_count . ')';

                    echo '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'show_count' ); ?>">Show:</label>
            <input id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="<?php echo $instance['show_count']; ?>" type="text" size="2" /> portfolio posts
        </p>


        <hr />


        <p>
            <label>
                <input class="checkbox" type="checkbox" <?php checked( $instance['show_excerpt'] ); ?> id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" />
                <?php _e( 'Display Excerpts', 'wpzoom' ); ?>
            </label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_category'] ); ?> id="<?php echo $this->get_field_id( 'show_category' ); ?>" name="<?php echo $this->get_field_name( 'show_category' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'show_category' ); ?>"><?php _e( 'Display Category', 'wpzoom' ); ?></label>
        </p>

        <hr />

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['view_all_enabled'] ); ?> id="<?php echo $this->get_field_id( 'view_all_enabled' ); ?>" name="<?php echo $this->get_field_name( 'view_all_enabled' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'view_all_enabled' ); ?>"><?php _e( 'Display View All button', 'wpzoom' ); ?></label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'view_all_text' ); ?>"><?php _e( 'Text for View All button', 'wpzoom' ); ?>:</label><br />
            <input id="<?php echo $this->get_field_id( 'view_all_text' ); ?>" name="<?php echo $this->get_field_name( 'view_all_text' ); ?>" value="<?php echo $instance['view_all_text']; ?>" type="text" class="widefat" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'view_all_link' ); ?>"><?php _e( 'Link for View All button', 'wpzoom' ); ?>:</label><br />
            <input id="<?php echo $this->get_field_id( 'view_all_link' ); ?>" name="<?php echo $this->get_field_name( 'view_all_link' ); ?>" value="<?php echo $instance['view_all_link']; ?>" type="text" class="widefat" />
        </p>

        <?php
    }
}

function wpzoom_register_psc_widget() {
    register_widget('WPZOOM_Portfolio_Showcase');
}
add_action('widgets_init', 'wpzoom_register_psc_widget');