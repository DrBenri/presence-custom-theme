<?php get_header(); ?>

    <main id="main" class="site-main" role="main">

        <?php the_archive_title( '<h2 class="section-title">', '</h2>' ); ?>

        <nav class="portfolio-archive-taxonomies">
            <ul class="portfolio-taxonomies">
                <li class="cat-item cat-item-all current-cat"><a href="<?php echo get_page_link( option::get( 'portfolio_url' ) ); ?>"><?php _e( 'All', 'wpzoom' ); ?></a></li>

                <?php wp_list_categories( array( 'title_li' => '', 'hierarchical' => true,  'taxonomy' => 'jetpack-portfolio-type', 'depth' => 1 ) ); ?>
            </ul>
        </nav>


        <?php if ( have_posts() ) : ?>

            <section id="recent-posts" class="recent-projects">

                <?php while ( have_posts() ) : the_post(); ?>

                    <?php get_template_part( 'content', 'jetpack-portfolio'); ?>

                <?php endwhile; ?>

            </section><!-- .recent-posts -->


            <?php get_template_part( 'pagination' ); ?>

        <?php else: ?>

            <?php get_template_part( 'content', 'none' ); ?>

        <?php endif; ?>

    </main><!-- .site-main -->

<?php
get_footer();
