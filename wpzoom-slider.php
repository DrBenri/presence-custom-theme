<?php

    if ( option::get( 'featured_type' ) == 'Featured Posts' ) {
        $FeaturedSource = 'post';
    } elseif ( option::get( 'featured_type' ) == 'Featured Pages' ) {
        $FeaturedSource = 'page';
    } elseif ( option::get( 'featured_type' ) == 'Featured Listings' ) {
        $FeaturedSource = 'listing';
    } else {
        $FeaturedSource = 'slider';
    }


    if ( option::get( 'featured_type' ) == 'Slideshow Posts' ) {

        $featured = new WP_Query( array(
            'posts_per_page'    => option::get( 'slideshow_posts' ),
            'post_type' => 'slider',
            'orderby'     => 'menu_order date',
            'post_status' => array( 'publish' )
        ) );

    } elseif ( option::get( 'featured_type' ) == 'Featured Listings' ) {

        $featured = new WP_Query( array(
            'posts_per_page'    => option::get( 'slideshow_posts' ),
            'post_type' => 'listing',
            'meta_key'     => 'wpzoom_is_featured',
            'meta_value'   => 1,
        ) );

    } else {

        $featured = new WP_Query( array(
            'showposts'    => option::get( 'slideshow_posts' ),
            'post__not_in' => get_option( 'sticky_posts' ),
            'meta_key'     => 'wpzoom_is_featured',
            'meta_value'   => 1,
            'orderby'     => 'menu_order date',
            'post_type' => $FeaturedSource
        ) );

    }

?></div><!-- /.inner-wrap -->

<div class="slider-wrap">

    <div id="slider" class="<?php echo get_theme_mod('slider-styles', zoom_customizer_get_default_option_value('slider-styles', presence_customizer_data()))?>">

    	<?php if ( $featured->have_posts() ) : ?>

    		<ul class="slides clearfix">

    			<?php while ( $featured->have_posts() ) : $featured->the_post(); ?>
                    <?php $slide_url = trim( get_post_meta( get_the_ID(), 'wpzoom_slide_url', true ) );
                    $btn_title = trim( get_post_meta( get_the_ID(), 'wpzoom_slide_button_title', true ) );
                    $btn_url = trim( get_post_meta( get_the_ID(), 'wpzoom_slide_button_url', true ) );
                    $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'featured-retina');
                    $image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'featured');
                    $style = ' style="background-image:url(\'' . $image_url[0] . '\')" data-rjs="' . $large_image_url[0] . '"'; ?>

                    <li class="slide">
                        <div class="slide-overlay">
                            <div class="slide-header">
                               <?php if ( option::is_on( 'slider_category' ) && $FeaturedSource == 'post' ) printf( '<span class="cat-links">%s</span>', get_the_category_list( ', ' ) ); ?>

                                <?php if ($FeaturedSource == 'slider') { ?>
                                    <?php if (empty($slide_url)) : ?>
                                        <?php the_title('<h3>', '</h3>'); ?>
                                    <?php else: ?>
                                        <?php the_title(sprintf('<h3><a href="%s">', esc_url($slide_url)), '</a></h3>'); ?>
                                    <?php endif; ?>
                                <?php } else { ?>
                                    <h3><?php if ($FeaturedSource == 'post') { ?><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'wpzoom' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php }?><?php the_title(); ?><?php if ($FeaturedSource == 'post') { ?></a><?php } ?></h3>
                                <?php } ?>

                                <?php if ($FeaturedSource == 'post') { ?>
                                    <div class="entry-meta">
                                        <?php if ( option::is_on( 'slider_date' ) )     printf( '<span class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></span>', esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ) ); ?>
                                        <?php if ( option::is_on( 'slider_comments' ) ) { ?><span class="comments-link"><?php comments_popup_link( __('0 comments', 'wpzoom'), __('1 comment', 'wpzoom'), __('% comments', 'wpzoom'), '', __('Comments are Disabled', 'wpzoom')); ?></span><?php } ?>
                                    </div>
                                <?php } ?>

                                <?php if ( option::is_on( 'slider_excerpt' ) ) { ?>

                                    <?php if ($FeaturedSource == 'page' || $FeaturedSource == 'listing') { ?>
                                        <?php the_excerpt(); ?>
                                    <?php } ?>

                                <?php } ?>

                                <?php if ($FeaturedSource == 'slider') { ?>
                                    <?php the_content(); ?>
                                <?php } ?>

                                <?php if ( option::is_on( 'slider_button' ) ) { ?>
                                    <?php if ($FeaturedSource == 'slider') { ?>
                                        <?php if (!empty($btn_title) && !empty($btn_url)) {
                                            ?>
                                            <div class="slide_button">
                                                <a href="<?php echo esc_url($btn_url); ?>"><?php echo esc_html($btn_title); ?></a>
                                            </div><?php
                                        } ?>
                                    <?php } else { ?>
                                        <div class="slide_button">
                                            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'wpzoom' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php _e('View More', 'wpzoom'); ?></a>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div><!--/.slide-header-->
                        </div><!--/.slide-overlay-->
                        <div class="slide-background" <?php echo $style; ?>></div>
                    </li>
                <?php endwhile; ?>
    		</ul>

    	<?php else: ?>

    		<div class="empty-slider">
    			<p><strong><?php _e( 'You are now ready to set-up your Slideshow content.', 'wpzoom' ); ?></strong></p>

    			<p>
    				<?php
    				printf(
    					__( 'For more information about adding posts to the slider, please <a href="%1$s">read the documentation</a>', 'wpzoom' ),
    					'https://www.wpzoom.com/documentation/presence/'
    				);
    				?>
    			</p>
    		</div>

    	<?php endif; ?>

    </div><!--/#slider -->
</div><!--/.slider-wrap -->

<div class="inner-wrap">