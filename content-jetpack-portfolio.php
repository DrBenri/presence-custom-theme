<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <?php if ( has_post_thumbnail() ) : ?>

        <?php if (option::get('portfolio_ratio') == 'Landscape (4:3)') {
            $size = "portfolio";
        }
        else {
            $size = "portfolio-square";
        } ?>

        <div class="post-thumb"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
            <?php the_post_thumbnail($size); ?>
        </a></div>
    <?php endif; ?>

    <section class="entry-body">

        <?php the_title( sprintf( '<h3 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' ); ?>

        <div class="entry-meta">
            <?php if ( option::is_on( 'portfolio_category' ) ) : ?><span class="entry-category"><?php the_terms($post->ID, 'jetpack-portfolio-type'); ?></span><?php endif; ?>
        </div>

        <?php if ( option::is_on( 'portfolio_excerpt' ) ) : ?>

            <div class="entry-content">
                <?php the_excerpt(); ?>
            </div>

        <?php endif; ?>

    </section>

    <div class="clearfix"></div>
</article><!-- #post-<?php the_ID(); ?> -->