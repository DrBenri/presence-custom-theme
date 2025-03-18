<?php
/**
 * The Template for displaying all Jetpack Portfolio posts.
 */

get_header(); ?>

</div><!-- /.inner-wrap -->


<?php while ( have_posts() ) : the_post(); ?>

    <?php $entryCoverBackground = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'entry-cover' ); ?>

    <?php if ( option::is_on( 'single_portfolio_thumb' ) && has_post_thumbnail() ) { ?>

        <div class="entry-cover" style="background-image:url('<?php echo $entryCoverBackground[0]; ?>')"></div>

    <?php } ?>

    <div class="inner-wrap">

        <main id="main" class="site-main narrow-column" role="main">

             <?php get_template_part( 'content', 'jetpack-portfolio-single' ); ?>

            <?php endwhile; // end of the loop. ?>

        </main><!-- #main -->

<?php get_footer(); ?>