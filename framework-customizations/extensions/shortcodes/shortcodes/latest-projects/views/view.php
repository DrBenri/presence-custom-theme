<?php if (!defined('FW')) die( 'Forbidden' ); ?>

    <?php

    $term_id   = (int) $atts['category'];

    $posts_per_page = (int) $atts['posts_number'];
    if ( $posts_per_page == 0 ) {
        $posts_per_page = - 1;
    }


    if ( $term_id == 0 ) {
        $args = array(
            'posts_per_page' => $posts_per_page,
            'post_type'      => 'jetpack-portfolio',
            'orderby'        => 'date'
        );
    } else {
        $args = array(
            'posts_per_page' => $posts_per_page,
            'post_type'      => 'jetpack-portfolio',
            'orderby'        => 'date',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'jetpack-portfolio-type',
                    'field'    => 'id',
                    'terms'    => $term_id
                )
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
                                <?php if ( $atts['postcategory'] == 'category_show') { ?><span class="entry-category"><?php the_terms($wp_query->post->ID, 'jetpack-portfolio-type'); ?></span><?php } ?>
                            </div>

                            <?php if ( $atts['excerpt'] == 'excerpt_show') { ?>

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


    <?php if ( $atts['button_all'] == 'button_all_show') { ?>

        <div class="wpz-btn-center">
            <a class="wpz-btn btn" href="<?php echo esc_attr($atts['link']) ?>" target="<?php echo esc_attr($atts['target']) ?>">
            <?php echo $atts['label']; ?>
            </a>
        </div>

    <?php } ?>
