<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>


    <header class="entry-header">

        <?php the_title( '<h1 class="entry-title fn">', '</h1>' ); ?>

        <div class="entry-meta">
            <?php if ( option::is_on( 'post_author' ) )   { printf( '<span class="entry-author">%s ', __( 'Written by', 'wpzoom' ) ); the_author_posts_link(); print('</span>'); } ?>

            <?php if ( option::is_on( 'post_date' ) )     : ?><span class="entry-date"><?php _e( 'on', 'wpzoom' ); ?> <?php printf( '<time class="entry-date" datetime="%1$s">%2$s</time> ', esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ) ); ?></span> <?php endif; ?>

            <?php if ( option::is_on( 'post_category' ) ) : ?><span class="entry-category"><?php _e( 'in', 'wpzoom' ); ?> <?php the_category( ', ' ); ?></span><?php endif; ?>

            <?php edit_post_link( __( 'Edit', 'wpzoom' ), '<span class="edit-link">', '</span>' ); ?>
        </div>

    </header><!-- .entry-header -->


    <div class="entry-content">
        <?php the_content(); ?>

        <div class="clear"></div>

        <?php if ( option::is_on('banner_post_enable')  ) { // Banner after first post ?>

            <div class="adv_content">
            <?php
                if ( option::get('banner_post_html') <> "" ) {
                    echo stripslashes(option::get('banner_post_html'));
                } else {
                    ?><a href="<?php echo option::get('banner_post_url'); ?>"><img src="<?php echo option::get('banner_post'); ?>" alt="<?php echo option::get('banner_post_alt'); ?>" /></a><?php
                }

            ?></div><?php
        } ?>


    </div><!-- .entry-content -->



        <?php
            wp_link_pages( array(
                'before' => '<div class="page-links">' . __( 'Pages:', 'wpzoom' ),
                'after'  => '</div>',
            ) );
        ?>


        <?php if ( option::is_on( 'post_tags' ) ) : ?>

            <?php the_tags( '<div class="tag_list">', '', '</div>' ); ?>

        <?php endif; ?>

        <div class="clear"></div>

        <?php if ( option::is_on( 'post_author_box' ) ) : ?>

            <footer class="entry-footer">

                <div class="post_author">

                    <?php echo get_avatar( get_the_author_meta( 'ID' ) , 100 ); ?>

                    <div class="author-description">

                        <h3 class="author-title author"><?php the_author_posts_link(); ?></h3>

                        <p class="author-bio">
                            <?php the_author_meta( 'description' ); ?>
                        </p>

                        <div class="author_links">

                            <?php if ( get_the_author_meta( 'facebook_url' ) ) { ?><a class="author_facebook" href="<?php the_author_meta( 'facebook_url' ); ?>" title="Facebook Profile" target="_blank">Facebook</a><?php } ?>


                            <?php if ( get_the_author_meta( 'twitter' ) ) { ?><a class="author_twitter" href="https://twitter.com/<?php the_author_meta( 'twitter' ); ?>" title="Follow <?php the_author_meta( 'display_name' ); ?> on Twitter" target="_blank">Twitter</a><?php } ?>


                            <?php if ( get_the_author_meta( 'instagram_url' ) ) { ?><a class="author_instagram" href="https://instagram.com/<?php the_author_meta( 'instagram_url' ); ?>" title="Instagram" target="_blank">Instagram</a><?php } ?>

                        </div>

                    </div>

                    <div class="clear"></div>

                </div>

            </footer><!-- .entry-footer -->

        <?php endif; ?>


        <?php if ( is_active_sidebar( 'sidebar-post' ) ) : ?>

            <section class="site-widgetized-section section-single">

                <?php dynamic_sidebar( 'sidebar-post' ); ?>

            </section><!-- .site-widgetized-section -->

        <?php endif; ?>



</article><!-- #post-## -->