<?php get_header(); ?>

<?php $template = option::get( 'layout_home' ); ?>


    <main id="main" class="site-main" role="main">

       <h2 class="section-title"><?php _e('Search Results for','wpzoom');?> <strong>"<?php the_search_query(); ?>"</strong></h2>

        <section class="content-area<?php if ( 'full' == $template ) { echo ' full-layout'; } ?>">

            <?php if ( have_posts() ) : ?>

                <section id="recent-posts" class="recent-posts<?php if (option::get('post_view') == 'Blog') { echo " blog-view"; } elseif (option::get('post_view') == 'List') { echo " list-view"; } ?>">

                    <?php while ( have_posts() ) : the_post(); ?>

                        <?php get_template_part( 'content', get_post_format() ); ?>

                    <?php endwhile; ?>

                </section><!-- .recent-posts -->


                <?php get_template_part( 'pagination' ); ?>

            <?php else: ?>

                <?php get_template_part( 'content', 'none' ); ?>

            <?php endif; ?>

        </section><!-- .content-area -->

        <?php if ( 'full' != $template ) : ?>

            <?php get_sidebar(); ?>

        <?php else : ?>

            <div class="clear"></div>

        <?php endif; ?>

    </main><!-- .site-main -->


<?php
get_footer();
