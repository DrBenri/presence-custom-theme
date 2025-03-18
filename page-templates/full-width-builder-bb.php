<?php
/*
Template Name: Full-width (Page Builder)
*/

get_header(); ?>


</div><!-- /.inner-wrap -->

    <?php while ( have_posts() ) : the_post(); ?>

        <main id="main" class="site-main full-width-page" role="main">

            <div class="builder-wrap">

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <?php the_content(); ?>

                </article><!-- #post-## -->

            </div>

        </main><!-- #main -->

    <?php endwhile; // end of the loop. ?>


<div class="inner-wrap">

<?php get_footer(); ?>