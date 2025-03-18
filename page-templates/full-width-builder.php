<?php
/*
Template Name: Full-width (Unyson Builder)
*/

get_header(); ?>


</div><!-- /.inner-wrap -->

    <?php while ( have_posts() ) : the_post(); ?>

        <?php $entryCoverBackground = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'entry-cover' ); ?>

        <?php if ( option::is_on( 'page_thumb' ) && has_post_thumbnail() ) { ?>

            <div class="entry-cover" style="background-image:url('<?php echo $entryCoverBackground[0]; ?>')"></div>

        <?php } ?>


        <main id="main" class="site-main full-width-page" role="main">

            <div class="builder-wrap">

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <header class="entry-header">

                        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

                    </header><!-- .entry-header -->

                    <?php the_content(); ?>

                </article><!-- #post-## -->

            </div>

        </main><!-- #main -->

    <?php endwhile; // end of the loop. ?>


<div class="inner-wrap">

<?php get_footer(); ?>