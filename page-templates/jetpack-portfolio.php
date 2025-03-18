<?php
/*
Template Name: Portfolio (Jetpack)
*/
?>
<?php get_header(); ?>


    <main id="main" class="site-main" role="main">

        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

        <?php the_content(); ?>

        <?php if (option::is_on('portfolio_category_nav') ) { ?>

            <nav class="portfolio-archive-taxonomies">
                <ul class="portfolio-taxonomies">
                    <li class="cat-item cat-item-all current-cat"><a href="<?php echo get_page_link( option::get( 'portfolio_url' ) ); ?>"><?php _e( 'All', 'wpzoom' ); ?></a></li>

                    <?php wp_list_categories( array( 'title_li' => '', 'hierarchical' => true,  'taxonomy' => 'jetpack-portfolio-type', 'depth' => 1 ) ); ?>
                </ul>
            </nav>

        <?php } ?>

        <?php
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

        $args = array(
            'post_type'      => 'jetpack-portfolio',
            'paged'          => $paged,
            'posts_per_page' => option::get( 'portfolio_posts' ),
        );

        $wp_query = new WP_Query( $args );
        ?>

        <?php if ( $wp_query->have_posts() ) : ?>

            <section class="recent-projects">

                <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

                    <?php get_template_part( 'content', 'jetpack-portfolio'); ?>

                <?php endwhile; ?>

            </section><!-- .recent-projects -->


            <?php get_template_part( 'pagination' ); ?>

        <?php else: ?>

            <?php get_template_part( 'content', 'none' ); ?>

        <?php endif; ?>

    </main><!-- .site-main -->

<?php
get_footer();
