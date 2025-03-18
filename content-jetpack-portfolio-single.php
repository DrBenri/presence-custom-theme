<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <header class="entry-header">

        <div class="entry-meta">

            <?php if ( option::is_on( 'single_portfolio_category' ) ) : ?><span class="entry-category"><?php the_terms($post->ID, 'jetpack-portfolio-type'); ?></span><?php endif; ?>

            <?php edit_post_link( __( 'Edit', 'wpzoom' ), '<span class="edit-link">', '</span>' ); ?>
        </div>

        <?php the_title( '<h1 class="entry-title fn">', '</h1>' ); ?>

    </header><!-- .entry-header -->

    <div class="entry-content">
        <?php the_content(); ?>

        <div class="clear"></div>

    </div><!-- .entry-content -->

    <div class="clear"></div>

</article><!-- #post-## -->