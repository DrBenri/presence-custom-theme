<?php
/**
 * The main template file.
 */

get_header(); ?>

<?php $template = option::get( 'layout_home' ); ?>

<?php if ( option::is_on( 'featured_posts_show' ) && is_front_page() && $paged < 2) : ?>

    <?php get_template_part( 'wpzoom-slider' ); ?>

<?php endif; ?>

        <main id="main" class="site-main" role="main">

            <?php if ( is_active_sidebar( 'homepage-top' ) && is_front_page() && $paged < 2  ) : ?>

                <section class="home-widgetized-sections">

            <?php endif; ?>


            <?php if ( is_active_sidebar( 'homepage-top' ) && is_front_page() && $paged < 2 ) : ?>

                <section class="site-widgetized-section section-top">
                    <div class="widgets clearfix">

                        <?php dynamic_sidebar( 'homepage-top' ); ?>

                    </div>
                </section><!-- .site-widgetized-section -->

            <?php endif; ?>


            <?php if ( is_active_sidebar( 'homepage-top' ) && is_front_page() && $paged < 2  ) : ?>

                </section><!-- .home-widgetized-sections -->

            <?php endif; ?>


            <h2 class="section-title">

                <?php if ( is_front_page() ) : ?><?php echo esc_html( option::get('recent_title') ); ?>

                <?php else: ?>

                    <?php echo get_the_title( get_option( 'page_for_posts' ) ); ?>

                <?php endif; ?>

             </h2>


            <section class="content-area<?php if ( 'full' == $template ) { echo ' full-layout'; } ?>">

                <?php if ( have_posts() ) : ?>

                    <section id="recent-posts" class="recent-posts<?php if (option::get('post_view') == 'Blog') { echo " blog-view"; } elseif (option::get('post_view') == 'List') { echo " list-view"; } ?>">

                        <?php while ( have_posts() ) : the_post(); ?>

                            <?php

                                get_template_part( 'content', get_post_format() );

                            ?>


                        <?php endwhile; ?>

                    </section>

                    <?php get_template_part( 'pagination' ); ?>

                <?php else: ?>

                    <?php get_template_part( 'content', 'none' ); ?>

                <?php endif; ?>

            </section><!-- .content-area -->

            <?php if ( 'full' != $template ) : ?>

                <?php get_sidebar(); ?>

            <?php else : ?>

                <div class="clear"></div>

            <?php endif; ?>

        </main><!-- .site-main -->


<?php
get_footer();
