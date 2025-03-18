<?php
/*
Template Name: Homepage (Widgetized)
*/
?>

<?php get_header(); ?>


<?php if ( option::is_on( 'featured_posts_show' ) && is_front_page() && $paged < 2) : ?>

    <?php get_template_part( 'wpzoom-slider' ); ?>

<?php endif; ?>

    <main id="main" class="site-main" role="main">

        <section class="home-widgetized-sections">


            <?php if ( is_active_sidebar('home-main') ) { ?>

                <div class="homepage_widgets homepage_full"><?php dynamic_sidebar('home-main'); ?></div>

            <?php } ?>


            <?php if ( is_active_sidebar('home-main') && ( is_active_sidebar('home-1') || is_active_sidebar('home-2') || is_active_sidebar('home-3') || is_active_sidebar('home-bottom') )  ) { ?>

                <div class="home_separator"></div>

            <?php } ?>


            <div class="homepage_widgets">

                <div class="home_column">

                    <?php dynamic_sidebar( 'home-1' ); ?>

                </div>

                <div class="home_column">

                    <?php dynamic_sidebar( 'home-2' ); ?>

                </div>

                <div class="home_column">

                    <?php dynamic_sidebar( 'home-3' ); ?>

                </div>


            </div>


            <?php if ( is_active_sidebar('home-bottom') && ( is_active_sidebar('home-1') || is_active_sidebar('home-2') || is_active_sidebar('home-3') )  ) { ?>

                <div class="home_separator"></div>

            <?php } ?>



            <?php if ( is_active_sidebar('home-bottom') ) { ?>

                <div class="homepage_widgets homepage_full"><?php dynamic_sidebar('home-bottom'); ?></div>

            <?php } ?>



            <?php if ( ( is_active_sidebar('home-bottom') || is_active_sidebar('home-1') || is_active_sidebar('home-2') || is_active_sidebar('home-3') || is_active_sidebar('home-bottom') )   && ( is_active_sidebar('home-4') || is_active_sidebar('home-5') || is_active_sidebar('home-6') )  ) { ?>

                <div class="clear"></div>
                <div class="home_separator"></div>

            <?php } ?>


            <?php if ( is_active_sidebar('home-4') || is_active_sidebar('home-5') || is_active_sidebar('home-5') ) { ?>

                <div class="homepage_widgets">

                    <div class="home_column">

                        <?php dynamic_sidebar( 'home-4' ); ?>

                    </div>

                    <div class="home_column">

                        <?php dynamic_sidebar( 'home-5' ); ?>

                    </div>

                    <div class="home_column">

                        <?php dynamic_sidebar( 'home-6' ); ?>

                    </div>

                </div>

            <?php } ?>



        </section><!-- .home-widgetized-sections -->


    </main><!-- .site-main -->


    <?php get_footer(); ?>