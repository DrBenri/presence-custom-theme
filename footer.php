<?php
/**
 * The template for displaying the footer
 *
 */

$widgets_areas = 3;

$has_active_sidebar = false;
if ( $widgets_areas > 0 ) {
    $i = 1;

    while ( $i <= $widgets_areas ) {
        if ( is_active_sidebar( 'footer_' . $i ) ) {
            $has_active_sidebar = true;
            break;
        }

        $i++;
    }
}

?>

    <div class="clear"></div>

    </div><!-- .inner-wrap -->

    <footer id="colophon" class="site-footer" role="contentinfo">

        <div class="inner-wrap">

            <?php if ( $has_active_sidebar ) : ?>

                <div class="footer-widgets widgets widget-columns-3">
                    <?php for ( $i = 1; $i <= $widgets_areas; $i ++ ) : ?>

                        <div class="column">
                            <?php dynamic_sidebar( 'footer_' . $i ); ?>
                        </div><!-- .column -->

                    <?php endfor; ?>

                    <div class="clear"></div>
                </div><!-- .footer-widgets -->

            <?php endif; ?>


            <?php if ( is_active_sidebar( 'footer_4' ) ) : ?>

                <section class="site-widgetized-section section-footer">
                    <div class="widgets clearfix">

                        <?php dynamic_sidebar( 'footer_4' ); ?>

                    </div>
                </section><!-- .site-widgetized-section -->

            <?php endif; ?>


            <div class="site-info">

                <p class="copyright"><?php zoom_customizer_partial_blogcopyright(); ?></p>

                <p class="designed-by"><?php printf( __( 'Designed by %s', 'wpzoom' ), '<a href="https://www.drbenri.com/" target="_blank" rel="designer">DrBenri</a>' ); ?></p>

            </div><!-- .site-info -->

        </div>

    </footer><!-- #colophon -->

</div><!-- .page-wrap -->

<?php wp_footer(); ?>

</body>
</html>