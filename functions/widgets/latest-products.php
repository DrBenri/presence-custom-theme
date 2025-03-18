<?php

/*------------------------------------------*/
/* WPZOOM: WooCommerce Latest Products      */
/*------------------------------------------*/

class WPZoom_Latest_Products extends WP_Widget {

    function __construct() {
        /* Widget settings. */
        $widget_ops = array( 'classname' => 'carousel-slider', 'description' => 'Custom WPZOOM widget that displays latest WooCommerce products' );

        /* Widget control settings. */
        $control_ops = array( 'id_base' => 'wpzoom-latest-products' );

        /* Create the widget. */
        parent::__construct( 'wpzoom-latest-products', 'Latest WooCommerce Products', $widget_ops, $control_ops );
    }

    function widget( $args, $instance ) {

        extract( $args );

        /* User-selected settings. */
        $title = apply_filters('widget_title', $instance['title'] );
        $category = $instance['category'];

        $show_count = $instance['show_count'];
        $auto_scroll = $instance['auto_scroll'] == true;
        $show_dots = $instance['show_dots'] == true;

        /* Before widget (defined by themes). */
        echo $before_widget;

        /* Title of widget (before and after defined by themes). */
        if ( $title )
            echo $before_title . $title . $after_title;

        ?>


        <div id="loading-<?php echo $this->get_field_id('id'); ?>">
            <div class="spinner">
                <div class="rect1"></div> <div class="rect2"></div> <div class="rect3"></div> <div class="rect4"></div> <div class="rect5"></div>
            </div>
        </div>

        <div class="carousel_widget_wrapper" id="carousel_widget_wrapper-<?php echo $this->get_field_id('id'); ?>">

            <div id="carousel-<?php echo $this->get_field_id('id'); ?>">

            <?php

            $args = array(
                'post_type' => 'product',
                'posts_per_page' => $show_count,
            );

            if ( $category ) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'terms' => $category,
                        'field' => 'term_id',
                    )
                );
            }


            $woo_args = new WP_Query( $args );


                $woo_loop = new WP_Query( $args );
                while ( $woo_loop->have_posts() ) : $woo_loop->the_post(); $_product;
                if ( function_exists( 'wc_get_product' ) ) {
                    $_product = wc_get_product( $woo_loop->post->ID );
                } else {
                    $_product = new WC_Product( $woo_loop->post->ID );
                }
            ?>


            <?php

                echo '<div class="item">';

                    if ( has_post_thumbnail() ) : ?>
                        <div class="post-thumb"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <?php the_post_thumbnail('loop'); ?>
                        </a></div>
                    <?php endif; ?>


                    <div class="shop_item_details">

                        <h3 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>

                        <span class="price"><?php echo $_product->get_price_html(); ?></span>

                    </div>

                <?php echo '</div>';
                endwhile;

                //Reset query_posts
                wp_reset_query();

            ?></div>
            <div class="clear"></div>

        </div>

        <script type="text/javascript">
            jQuery(function($) {

                var $c = $('#carousel-<?php echo $this->get_field_id('id'); ?>');

                $c.imagesLoaded( function(){

                    $('#carousel_widget_wrapper-<?php echo $this->get_field_id('id'); ?>').show();
                    $('#loading-<?php echo $this->get_field_id('id'); ?>').hide();

                    $c.flickity({
                        autoPlay: <?php echo $auto_scroll === true ? 'true' : 'false'; ?>,
                        cellAlign: 'left',
                        contain: true,
                        percentPosition: true,
                        pageDots: <?php echo $show_dots === true ? 'true' : 'false'; ?>,
                        wrapAround: true,
                        imagesLoaded: true,
                        arrowShape: {
                          x0: 10,
                          x1: 60, y1: 50,
                          x2: 65, y2: 45,
                          x3: 20
                        },
                        accessibility: false
                    });

                });

            });
        </script><?php

        /* After widget (defined by themes). */
        echo $after_widget;
    }


    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        /* Strip tags (if needed) and update the widget settings. */
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['category'] = $new_instance['category'];

        $instance['show_count'] = $new_instance['show_count'];
        $instance['auto_scroll'] = $new_instance['auto_scroll'] == 'on';
        $instance['show_dots'] = $new_instance['show_dots'] == 'on';
        $instance['posts'] = $new_instance['posts'];

        return $instance;
    }

    function form( $instance ) {

        /* Set up some default widget settings. */
        $defaults = array( 'title' => '', 'category' => 0, 'show_count' => 10,  'auto_scroll' => true, 'show_dots' => true);
        $instance = wp_parse_args( (array) $instance, $defaults ); ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'wpzoom'); ?>:</label><br />
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'category' ); ?>">Category:</label>
            <select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
                <option value="0" <?php if ( !$instance['category'] ) echo 'selected="selected"'; ?>>All</option>
                <?php
                $categories = get_categories(array('taxonomy' => 'product_cat'));

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
            <label for="<?php echo $this->get_field_id( 'show_count' ); ?>"><?php _e('Show', 'wpzoom'); ?>:</label>
            <input id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="<?php echo $instance['show_count']; ?>" type="text" size="2" /> <?php _e('posts', 'wpzoom'); ?>
        </p>

        <p>
            <label>
                <input class="checkbox" type="checkbox" <?php checked( $instance['auto_scroll'] ); ?> id="<?php echo $this->get_field_id( 'auto_scroll' ); ?>" name="<?php echo $this->get_field_name( 'auto_scroll' ); ?>" />
                <?php _e( 'Auto-Scroll', 'wpzoom' ); ?>
            </label>
            <span class="howto"><?php _e( 'Automatically scroll through the posts', 'wpzoom' ); ?></span>
        </p>

        <p>
            <label>
                <input class="checkbox" type="checkbox" <?php checked( $instance['show_dots'] ); ?> id="<?php echo $this->get_field_id( 'show_dots' ); ?>" name="<?php echo $this->get_field_name( 'show_dots' ); ?>" />
                <?php _e( 'Show Navigation Dots', 'wpzoom' ); ?>
            </label>
        </p>


        <?php
    }
}

function wpzoom_register_lpw_widget() {
    register_widget('WPZoom_Latest_Products');
}
add_action('widgets_init', 'wpzoom_register_lpw_widget');