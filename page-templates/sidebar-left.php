<?php
/*
Template Name: Sidebar on the left
*/

get_header(); ?>

</div><!-- /.inner-wrap -->

    <?php while ( have_posts() ) : the_post(); ?>

    <?php $entryCoverBackground = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'entry-cover' ); ?>

    <?php if ( option::is_on( 'page_thumb' ) && has_post_thumbnail() ) { ?>

        <div class="entry-cover" style="background-image:url('<?php echo $entryCoverBackground[0]; ?>')"></div>

    <?php } ?>


    <div class="inner-wrap">

        <main id="main" class="site-main sidebar-left" role="main">

            <div class="content-area">

                <?php get_template_part( 'content', 'page' ); ?>

                <?php if (option::get('comments_page') == 'on') { ?>
                    <?php comments_template(); ?>
                <?php } ?>

            </div>

            <?php get_sidebar(); ?>

        </main><!-- #main -->

        <?php endwhile; // end of the loop. ?>


<?php get_footer(); ?>