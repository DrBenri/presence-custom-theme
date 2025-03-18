<?php

/*------------------------------------------*/
/* WPZOOM: WooCommerce Category             */
/*------------------------------------------*/

class Wpzoom_Woo_Category extends WP_Widget {

    /* Widget setup. */
    function __construct() {
        /* Widget settings. */
        $widget_ops = array( 'classname' => 'wpzoom-singlepage', 'description' => __('Custom WPZOOM widget that displays a single WooCommerce Category.', 'wpzoom') );

        /* Widget control settings. */
        $control_ops = array( 'id_base' => 'wpzoom-woo-category' );

        /* Create the widget. */
        parent::__construct( 'wpzoom-woo-category', __('WPZOOM: WooCommerce Category', 'wpzoom'), $widget_ops, $control_ops );
    }

    /* How to display the widget on the screen. */
    function widget( $args, $instance ) {
        extract( $args );

        /* Our variables from the widget settings. */
        $category = $instance['category'];
        $show_content = $instance['show_content'] == true;
        $show_count = $instance['show_count'] == true;
        $view_all_text = $instance['view_all_text'];
        $page_data = get_term_by('id', $category, 'product_cat' );

        if ( ! $page_data ) return false;

        $title = apply_filters( 'widget_title', trim($page_data->name), $instance, $this->id_base );
        $read_more = (bool) $instance['read_more'];

        echo $before_widget;

            echo '<div class="post_thumb_withbg">';

                $cat_thumb_id = get_woocommerce_term_meta( $page_data->term_id, 'thumbnail_id', true );
                $large_image_url = wp_get_attachment_image_src( $cat_thumb_id, 'loop-retina' );

                $style = ' style="background-image:url(\'' . $large_image_url[0]. '\')"';

                echo '<div class="page_background" '.$style.'">';


                    /* Title of widget (before and after defined by themes). */

                    echo '<div class="featured_page_content">';


                        if ( $title ) {
                            echo $before_title;

                            echo '<a href="' . esc_url( get_term_link($page_data->term_id) ) . '">';
                            echo $title;
                            echo '</a>';

                            if ( $show_count ) {

                                echo ' <span class="wc_cat_count">('.$page_data->count.')</span>';

                            }

                            echo $after_title;
                        }


                        if ( $show_content ) {

                            echo term_description( $page_data->term_id, 'product_cat' );

                        }


                        if ($read_more) {
                            echo '<a class="more_link" href="' . esc_url( get_term_link($page_data->term_id) ) . '">'. esc_html( $view_all_text ) .'</a>';
                        }



                echo '</div>';

            echo '</div></div>';


            echo $after_widget;

    }

        /* Update the widget settings.*/
        function update( $new_instance, $old_instance ) {
            $instance = $old_instance;

            /* Strip tags for title and name to remove HTML (important for text inputs). */
            $instance['page_id'] = (int) $new_instance['page_id'];
            $instance['category'] = $new_instance['category'];
            $instance['read_more'] = $new_instance['read_more'];
            $instance['view_all_text'] = $new_instance['view_all_text'];
            $instance['show_content'] = $new_instance['show_content'] == 'on';
            $instance['show_count'] = $new_instance['show_count'] == 'on';

            return $instance;
        }

        /** Displays the widget settings controls on the widget panel.
         * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff. */
        function form( $instance ) {
            /* Set up some default widget settings. */
            $defaults = array( 'category' => 0, 'read_more' => true, 'view_all_text' => 'View All Products',  'show_content' => true, 'show_count' => true );
            $instance = wp_parse_args( (array) $instance, $defaults );

            ?>

            <p>
                <label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e('Category to Display', 'wpzoom'); ?>:</label>
                <select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
                    <?php
                    $categories = get_categories( array( 'taxonomy' => 'product_cat' ) );

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
                <label>
                    <input class="checkbox" type="checkbox" <?php checked( $instance['show_content'] ); ?> id="<?php echo $this->get_field_id( 'show_content' ); ?>" name="<?php echo $this->get_field_name( 'show_content' ); ?>" />
                    <?php _e( 'Display Category Description', 'wpzoom' ); ?>
                </label>
            </p>

            <p>
                <label>
                    <input class="checkbox" type="checkbox" <?php checked( $instance['show_count'] ); ?> id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" />
                    <?php _e( 'Display Number of Products', 'wpzoom' ); ?>
                </label>
            </p>


            <p>
                <input class="checkbox" type="checkbox" <?php checked( $instance['read_more'], 'on' ); ?> id="<?php echo $this->get_field_id( 'read_more' ); ?>" name="<?php echo $this->get_field_name( 'read_more' ); ?>" />
                <label for="<?php echo $this->get_field_id( 'read_more' ); ?>"><?php _e('Display Button', 'wpzoom'); ?></label>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'view_all_text' ); ?>"><?php _e('Text for button:', 'wpzoom'); ?></label><br />
                <input id="<?php echo $this->get_field_id( 'view_all_text' ); ?>" name="<?php echo $this->get_field_name( 'view_all_text' ); ?>" value="<?php echo $instance['view_all_text']; ?>" type="text" class="widefat" />
            </p>


            <?php
        }
}

function wpzoom_register_wooc_widget() {
    register_widget('Wpzoom_Woo_Category');
}
add_action('widgets_init', 'wpzoom_register_wooc_widget');
?>