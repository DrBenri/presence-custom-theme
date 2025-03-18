<?php
/**
 * The Template for displaying all single posts.
 */

get_header(); ?>

<?php
    $template = get_post_meta($post->ID, 'wpzoom_post_template', true);
?>

</div><!-- /.inner-wrap -->


<?php while ( have_posts() ) : the_post(); ?>

<?php $entryCoverBackground = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'entry-cover' ); ?>

    <?php if ( option::is_on( 'post_thumb' ) && has_post_thumbnail() ) { ?>

        <div class="entry-cover" style="background-image:url('<?php echo $entryCoverBackground[0]; ?>')"></div>

    <?php } ?>


    <div class="inner-wrap">

        <main id="main" class="site-main<?php if ($template == 'full') { echo ' full-width'; } ?>" role="main">


                <div class="content-area">

                    <?php get_template_part( 'content', 'single' ); ?>

                    <?php if (option::is_on('post_comments') ) : ?>

                        <?php comments_template(); ?>

                    <?php endif; ?>

                </div>

            <?php endwhile; // end of the loop. ?>

            <?php if ($template != 'full') {
                get_sidebar();
            } else { echo "<div class=\"clear\"></div>"; } ?>

        </main><!-- #main -->

<?php get_footer(); ?>