<?php
/**
 * Disable & Remove Google Fonts
 * Plugin URI: https://wordpress.org/plugins/disable-remove-google-fonts/
 * Author: Fonts Plugin
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * @package disable-remove-google-fonts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dequeue Google Fonts based on URL.
 */
function wpz_dequeueu_google_fonts() {

    // Dequeue Google Fonts loaded by Revolution Slider.
    remove_action( 'wp_footer', array( 'RevSliderFront', 'load_google_fonts' ) );


    global $wp_styles;

    if ( ! ( $wp_styles instanceof WP_Styles ) ) {
        return;
    }

    $allowed = apply_filters(
        'drgf_exceptions',
        [ 'olympus-google-fonts' ]
    );

    foreach ( $wp_styles->registered as $style ) {
        $handle = $style->handle;
        $src    = $style->src;

        if ( strpos( $src, 'fonts.googleapis' ) !== false ) {
            if ( ! array_key_exists( $handle, array_flip( $allowed ) ) ) {
                wp_dequeue_style( $handle );
            }
        }
    }

    foreach ( $wp_styles->registered as $style ) {
        foreach( $style->deps as $dep ) {
            if ( ( strpos( $dep, 'google-fonts' ) !== false ) || ( strpos( $dep, 'google_fonts' ) !== false ) || ( strpos( $dep, 'googlefonts' ) !== false ) ) {
                $wp_styles->remove( $dep );
                $wp_styles->add( $dep, '' );
            }
        }
    }
    remove_action( 'wp_head', 'hu_print_gfont_head_link', 2 );

    remove_action('wp_head', 'appointment_load_google_font');

}
add_action( 'wp_enqueue_scripts', 'wpz_dequeueu_google_fonts', PHP_INT_MAX );
add_action( 'wp_print_styles', 'wpz_dequeueu_google_fonts', PHP_INT_MAX );

/**
 * Dequeue Google Fonts loaded by Elementor.
 */
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

/**
 * Dequeue Google Fonts loaded by Beaver Builder.
 */
add_filter(
    'fl_builder_google_fonts_pre_enqueue',
    function( $fonts ) {
        return array();
    }
);


/**
 * Dequeue Google Fonts loaded by the Hustle plugin.
 */
add_filter( 'hustle_load_google_fonts', '__return_false' );
