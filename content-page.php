<?php
/**
 * The template used for displaying page content in page.php
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <header class="entry-header">

        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

        <?php edit_post_link( __( 'Edit', 'wpzoom' ), '<span class="edit-link">', '</span>' ); ?>

    </header><!-- .entry-header -->


    <div class="entry-content">
        <?php the_content(); ?>
        <?php
            wp_link_pages( array(
                'before' => '<div class="page-links">' . __( 'Pages:', 'wpzoom' ),
                'after'  => '</div>',
            ) );
        ?>
    </div><!-- .entry-content -->

</article><!-- #post-## -->