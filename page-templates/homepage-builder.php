<?php
/*
Template Name: Homepage (Unyson Builder)
*/
?>

<?php get_header(); ?>

    <?php if ( option::is_on( 'featured_posts_show' ) ) : ?>

        <?php get_template_part( 'wpzoom-slider' ); ?>

    <?php endif; ?>

    </div><!--/.inner-wrap -->


    <main id="main" class="site-main" role="main">

        <div class="builder-wrap">

            <?php while ( have_posts() ) : the_post(); ?>

                <?php the_content(); ?>

            <?php endwhile; // end of the loop. ?>

        </div>

    </main><!-- .site-main -->

<div class="inner-wrap">

<?php get_footer(); ?>