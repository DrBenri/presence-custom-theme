<?php

/*------------------------------------------*/
/* WPZOOM: Single Page                      */
/*------------------------------------------*/

class WPZOOM_Single_Page extends WP_Widget {

    /* Widget setup. */
    function __construct() {
        /* Widget settings. */
        $widget_ops = array( 'classname' => 'wpzoom-singlepage', 'description' => __('Custom WPZOOM widget that displays a single specified static page.', 'wpzoom') );

        /* Widget control settings. */
        $control_ops = array( 'id_base' => 'wpzoom-single-page' );

        /* Create the widget. */
        parent::__construct( 'wpzoom-single-page', __('WPZOOM: Single Page', 'wpzoom'), $widget_ops, $control_ops );
    }

    /* How to display the widget on the screen. */
    function widget( $args, $instance ) {
        extract( $args );

        /* Our variables from the widget settings. */
        $page_id = (int) $instance['page_id'];
        $featured_image = $instance['featured_image'];
        $show_content = $instance['show_content'] == true;
        $remove_formatting = $instance['remove_formatting'];
        $aspect_ratio = isset($instance['aspect_ratio']) ? $instance['aspect_ratio'] : 'landscape';

        if ( empty( $page_id ) || $page_id < 1 ) return false;
        $current_language = apply_filters( 'wpml_current_language', NULL );
        $page_data = get_page( apply_filters( 'wpml_object_id', $page_id, 'page', true, $current_language ) );

        if ( ! $page_data ) return false;

        $title = apply_filters( 'widget_title', trim($page_data->post_title), $instance, $this->id_base );
        $link_title = (bool) $instance['link_title'];
        $read_more = (bool) $instance['read_more'];

        if ( !empty( $page_data->post_content ) ) {
            echo $before_widget;

            $page_excerpt = trim( $page_data->post_excerpt );


            if ( $featured_image == 'background' ) {

                echo '<div class="post_thumb_withbg">';

                $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($page_data->ID), 'loop-retina');

                $style = ' style="background-image:url(\'' . $large_image_url[0] . '\')"';

                echo '<div class="page_background" '.$style.'">';

            }


            if ( $featured_image != 'none' && $featured_image != 'background' ) {

                if ($page_excerpt) {

                    echo '<div class="post-video_'.$featured_image.'"><div class="video_cover">';

                        echo apply_filters( 'the_content', trim($page_data->post_excerpt) );

                    echo '</div></div>';

                } else {

                    if ($aspect_ratio == 'landscape') {
                        $image_size = 'portfolio';
                    } else {
                        $image_size = 'loop-retina';
                    }


                    echo '<div class="post-thumb_'.$featured_image.'">';

                    echo get_the_post_thumbnail( $page_data->ID, $image_size );

                    echo '</div>';

                }

            }

            /* Title of widget (before and after defined by themes). */

            echo '<div class="featured_page_content page_align_'.$featured_image.'">';

                if ( $title ) {
                    echo $before_title;

                    if ( $link_title ) echo '<a href="' . esc_url( get_permalink($page_data->ID) ) . '">';
                    echo $title;
                    if ( $link_title ) echo '</a>';

                    echo $after_title;
                }

                if ( $show_content ) {

                    $empty_p_patterns = "/<p[^>]*><\\/p[^>]*>/";

                    if ( false !== ( $more_tag_pos = strpos( $page_data->post_content, '<!--more-->' ) ) ) {
                        $content = substr( $page_data->post_content, 0, $more_tag_pos );

                        if ( $remove_formatting ) {
                            $content = force_balance_tags( wp_kses( $content, array( 'p' => array() ) ) );
                            $content = preg_replace( $empty_p_patterns, '', $content );
                        }

                        echo apply_filters( 'the_content', $content);
                    } else {
                        $content = $page_data->post_content;

                        if ( $remove_formatting ) {
                            $content = force_balance_tags( wp_kses( $content, array( 'p' => array() ) ) );
                            $content = preg_replace( $empty_p_patterns, '', $content );
                        }

                        echo apply_filters( 'the_content', $content);
                    }

                    if ($read_more) {
                        echo '<a class="more_link" href="' . esc_url( get_permalink($page_data->ID) ) . '">'.__('View More', 'wpzoom').'</a>';
                    }

                }


            echo '</div>';


            if ( $featured_image == 'background' ) {

                echo '</div></div>';

            }


            echo $after_widget;
        }
    }

        /* Update the widget settings.*/
        function update( $new_instance, $old_instance ) {
            $instance = $old_instance;

            /* Strip tags for title and name to remove HTML (important for text inputs). */
            $instance['page_id'] = (int) $new_instance['page_id'];
            $instance['link_title'] = !empty($new_instance['link_title']);
            $instance['read_more'] = !empty($new_instance['read_more']);
            $instance['featured_image'] = $new_instance['featured_image'];
            $instance['show_content'] = !empty($new_instance['show_content']);
            $instance['remove_formatting'] = !empty($new_instance['remove_formatting']);
            $instance['aspect_ratio'] = $new_instance['aspect_ratio'];

            return $instance;
        }

        /** Displays the widget settings controls on the widget panel.
         * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff. */
        function form( $instance ) {
            /* Set up some default widget settings. */
            $defaults = array( 'page_id' => 0, 'link_title' => true, 'read_more' => true, 'remove_formatting' => '', 'featured_image' => 'none', 'show_content' => true, 'aspect_ratio' => 'landscape', 'remove_formating' => false );
            $instance = wp_parse_args( (array) $instance, $defaults );

            ?><p>
                <label for="<?php echo $this->get_field_id('page_id'); ?>"><?php _e('Page to Display:', 'wpzoom'); ?></label>
                <?php wp_dropdown_pages( array( 'name' => $this->get_field_name('page_id'), 'id' => $this->get_field_id('page_id'), 'selected' => (int) $instance['page_id'] ) ); ?>
            </p>

            <p>
                <label>
                    <input class="checkbox" type="checkbox" <?php checked( $instance['show_content'] ); ?> id="<?php echo $this->get_field_id( 'show_content' ); ?>" name="<?php echo $this->get_field_name( 'show_content' ); ?>" />
                    <?php _e( 'Display Page Content', 'wpzoom' ); ?>
                </label>
            </p>

            <p class="description">
                <?php _e('You can easily split the content you want to show in the widget by adding the <code>&lt;!--more--&gt;</code> tag.', 'wpzoom'); ?>
            </p>

            <p>
                <label>
                    <input class="checkbox" type="checkbox" <?php checked( $instance['remove_formatting'] ); ?> id="<?php echo $this->get_field_id( 'remove_formatting' ); ?>" name="<?php echo $this->get_field_name( 'remove_formatting' ); ?>" />
                    <?php _e( 'Remove Formatting', 'wpzoom' ); ?>
                </label>
            </p>


            <p>
                <label for="<?php echo $this->get_field_id( 'featured_image' ); ?>"><?php _e('Featured Image/Video Position', 'wpzoom'); ?>:</label>
                <select id="<?php echo $this->get_field_id( 'featured_image' ); ?>" name="<?php echo $this->get_field_name( 'featured_image' ); ?>">
                    <option value="none" <?php if ( $instance['featured_image'] == 'none' ) echo 'selected="selected"'; ?>><?php _e('Don\'t display', 'wpzoom'); ?></option>
                    <option value="background" <?php if ( $instance['featured_image'] == 'background' ) echo 'selected="selected"'; ?>><?php _e('As Background', 'wpzoom'); ?></option>
                    <option value="top" <?php if ( $instance['featured_image'] == 'top' ) echo 'selected="selected"'; ?>><?php _e('At the Top', 'wpzoom'); ?></option>
                    <option value="left" <?php if ( $instance['featured_image'] == 'left' ) echo 'selected="selected"'; ?>><?php _e('Left', 'wpzoom'); ?></option>
                    <option value="right" <?php if ( $instance['featured_image'] == 'right' ) echo 'selected="selected"'; ?>><?php _e('Right', 'wpzoom'); ?></option>

                </select>
            </p>


            <p>
                <label for="<?php echo $this->get_field_id('aspect_ratio'); ?>"><?php _e('Thumbnail Aspect Ratio:', 'wpzoom'); ?></label>
                <select id="<?php echo $this->get_field_id('aspect_ratio'); ?>" name="<?php echo $this->get_field_name('aspect_ratio'); ?>" style="width:90%;">
                <option value="landscape"<?php if ($instance['aspect_ratio'] == 'landscape') { echo ' selected="selected"';} ?>><?php _e('Landscape (default)', 'wpzoom'); ?></option>
                <option value="portrait"<?php if ($instance['aspect_ratio'] == 'portrait') { echo ' selected="selected"';} ?>><?php _e('Portrait', 'wpzoom'); ?></option>
                </select>
            </p>



            <p class="description">
                <?php _e('To display a video in the widget, make sure to insert the <strong>embed code</strong> in the <strong>Excerpt</strong> field of the selected page.', 'wpzoom'); ?>
            </p>

            <p>
                <input class="checkbox" type="checkbox" <?php checked( $instance['link_title'] ); ?> id="<?php echo $this->get_field_id( 'link_title' ); ?>" name="<?php echo $this->get_field_name( 'link_title' ); ?>" />
                <label for="<?php echo $this->get_field_id( 'link_title' ); ?>"><?php _e('Link Page Title to Page', 'wpzoom'); ?></label>
            </p>

            <p>
                <input class="checkbox" type="checkbox" <?php checked( $instance['read_more']); ?> id="<?php echo $this->get_field_id( 'read_more' ); ?>" name="<?php echo $this->get_field_name( 'read_more' ); ?>" />
                <label for="<?php echo $this->get_field_id( 'read_more' ); ?>"><?php _e('Display Read More button', 'wpzoom'); ?></label>
            </p>

            <?php
        }
}

function wpzoom_register_sp_widget() {
    register_widget('WPZOOM_Single_Page');
}
add_action('widgets_init', 'wpzoom_register_sp_widget');
?>