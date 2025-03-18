<?php
/**
 * Theme functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 */

if ( ! function_exists( 'presence_setup' ) ) :
/**
 * Theme setup.
 *
 * Set up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support post thumbnails.
 */
function presence_setup() {
    // This theme styles the visual editor to resemble the theme style.
    add_editor_style( array( 'css/editor-style.css' ) );

    /* Image Sizes */

    /* Blog Posts */
    add_image_size( 'loop', 360, 360, true );
    add_image_size( 'loop-retina', 600, 600, true );
    add_image_size( 'loop-large', 800 );

    /* Single Post/Page Header Image */
    add_image_size( 'entry-cover', 2000, 800, true );

    /* Recent Posts Widget */
    add_image_size( 'recent-thumbnail', 360, 240, true );

    /* Homepage Slider */
    add_image_size( 'featured', 1360, 550, true );
    add_image_size( 'featured-retina', 2720, 1100, true );

    /* Jetpack Portfolio */
    add_image_size( 'portfolio', 650, 450, true );
    add_image_size( 'portfolio-square', 650, 650, true );

    /* Single Page Widget */
    add_image_size( 'widget-featured', 600, 600, true );

    /* Testimonials Widget */
    add_image_size( 'testimonial-widget-author-photo', 100, 100, true );


    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support( 'html5', array(
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
    ) );

    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support( 'title-tag' );

    // Register nav menus
    register_nav_menus( array(
        'secondary' => __( 'Top Menu', 'wpzoom' ),
        'primary' => __( 'Main Menu', 'wpzoom' ),
        'tertiary' => __( 'Mobile Menu', 'wpzoom' )
    ) );


    /*
     * JetPack Infinite Scroll
     */
    add_theme_support( 'infinite-scroll', array(
        'container' => 'recent-posts',
        'wrapper' => false,
        'footer' => false
    ) );

    /**
     * Theme Logo
     */
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true
    ) );

    /**
    * Gutenberg Wide Images
    */
    add_theme_support( 'align-wide' );

    // Add support for default block styles.
    add_theme_support( 'wp-block-styles' );

    // Add support for editor styles.
    add_theme_support( 'editor-styles' );

    // Enqueue editor styles.
    add_editor_style( 'css/gutenberg-editor-style.css' );

    // Enqueue fonts in the editor.
    add_editor_style( presence_fonts_editor() );

    presence_load_bb_templates();

    /**
     *  Declare support for selective refreshing of widgets.
     */
    add_theme_support( 'customize-selective-refresh-widgets' );


}
endif;
add_action( 'after_setup_theme', 'presence_setup' );



/*  Add support for Featured Posts Module.
============================================ */
if ( ! option::is_on( 'disable_featured_posts_module' ) ) {
    add_theme_support( 'wpz-featured-posts-settings', array(
            array(
                'id'          => 'wpzoom_is_featured_id',
                'menu_title'  => __( 'Re-order', 'wpzoom' ),
                'post_type'   => 'slider',
                'posts_limit' => option::get( 'featured_posts_posts' ),
                'show'        => ( option::get( 'featured_type' ) == 'Slideshow Posts' )
            ),
            array(
                'id'          => 'wpzoom_is_featured_id',
                'menu_title'  => __( 'Featured Posts', '' ),
                'title'       => 'Featured',
                'value'       => false,
                'name'        => 'wpzoom_is_featured',
                'post_type'   => 'post',
                'posts_limit' => option::get( 'slideshow_posts' ),
                'show'        => ( option::get( 'featured_type' ) == 'Featured Posts' )
            ),
            array(
                'id'          => 'wpzoom_is_featured_id',
                'menu_title'  => __( 'Featured Pages', '' ),
                'title'       => 'Featured',
                'value'       => false,
                'name'        => 'wpzoom_is_featured',
                'post_type'   => 'page',
                'posts_limit' => option::get( 'slideshow_posts' ),
                'show'        => ( option::get( 'featured_type' ) == 'Featured Pages' )
            )
        )
    );
}

/*
 * Multiple demo importer support
 ==================================== */

add_theme_support('wpz-multiple-demo-importer',
    array(
        'demos' => array(
            array(
                'name'  => 'default',
                'id'    => 'presence-default',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/default.png',
            ),
            array(
                'name'  => 'business',
                'id'    => 'presence-business',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/business.png',
            ),
            array(
                'name'  => 'dark',
                'id'    => 'presence-dark',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/dark.png',
            ),
            array(
                'name'  => 'church',
                'id'    => 'presence-church',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/church.png',
            ),
            array(
                'name'  => 'education',
                'id'    => 'presence-education',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/education.png',
            ),
            array(
                'name'  => 'organic-shop',
                'id'    => 'presence-organic-shop',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/organic.png',
            ),
            array(
                'name'  => 'fitness',
                'id'    => 'presence-fitness',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/fitness.png',
            ),
            array(
                'name'  => 'hotel',
                'id'    => 'presence-hotel',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/hotel.png',
            ),
            array(
                'name'  => 'magazine',
                'id'    => 'presence-magazine',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/magazine.png',
            ),
            array(
                'name'  => 'music',
                'id'    => 'presence-music',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/music.png',
            ),
            array(
                'name'  => 'portfolio',
                'id'    => 'presence-portfolio',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/portfolio.png',
            ),
            array(
                'name'  => 'real-estate',
                'id'    => 'presence-real-estate',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/real-estate.png',
            ),
            array(
                'name'  => 'shop',
                'id'    => 'presence-shop',
                'thumbnail' => get_template_directory_uri() . '/functions/assets/images/shop.png',
            ),
        ),
        'default' => 'default'
    )
);



/**
 *
 * Register Beaver Builder Templates in our theme
 *
 */
function presence_load_bb_templates() {

    if ( ! class_exists( 'FLBuilder' ) || ! method_exists( 'FLBuilder', 'register_templates' ) ) {
        return;
    }
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/business.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/dark.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/about.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/music.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/organic.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/real-estate.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/hotel.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/hotel-rooms.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/hotel-2.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/education.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/portfolio.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/church.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/events.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/fitness.dat' );
    FLBuilder::register_templates( get_template_directory() . '/functions/bb-templates/pricing.dat' );
}



/* This theme uses a Static Page as front page */
add_theme_support('zoom-front-page-type', array(
   'type' => 'static_page'
));


/*  Add support for Custom Background
==================================== */

add_theme_support( 'custom-background' );



/*  Add Support for Shortcodes in Excerpt
========================================== */

add_filter( 'the_excerpt', 'shortcode_unautop' );
add_filter( 'the_excerpt', 'do_shortcode' );

add_filter( 'widget_text', 'shortcode_unautop' );
add_filter( 'widget_text', 'do_shortcode' );



/* Flush rewrite rules for custom post types
========================================== */
add_action( 'after_switch_theme', 'flush_rewrite_rules' );



/* Disable Jetpack Related Posts on Post Type
========================================== */

function presence_no_related_posts( $options ) {
    if ( !is_singular( 'post' ) ) {
        $options['enabled'] = false;
    }
    return $options;
}
add_filter( 'jetpack_relatedposts_filter_options', 'presence_no_related_posts' );



/*  Recommended Plugins
========================================== */

function zoom_register_theme_required_plugins_callback($plugins){

    if (option::is_on('component_music')) {

        $plugins =  array_merge(array(

            array(
                'name'         => 'GigPress',
                'slug'         => 'gigpress',
                'required'     => true,
            ),

            array(
                'name'         => 'Cue by AudioTheme.com',
                'slug'         => 'cue',
                'required'     => true,
            ),

        ), $plugins);

    }

    if (option::is_on('component_events')) {

        $plugins =  array_merge(array(

            array(
                'name'         => 'The Events Calendar',
                'slug'         => 'the-events-calendar',
                'required'     => false,
            ),

        ), $plugins);

    }

    if (option::is_on('component_portfolio')) {

        $plugins =  array_merge(array(

            array(
                'name'         => 'WPZOOM Portfolio',
                'slug'         => 'wpzoom-portfolio',
                'required'     => false,
            ),

        ), $plugins);

    }

    if (option::is_on('component_realestate')) {

        $plugins =  array_merge(array(

            array(
                'name'         => 'IMPress Listings',
                'slug'         => 'wp-listings',
                'required'     => true,
                'source'       => 'https://downloads.wordpress.org/plugin/wp-listings.zip',
            ),

            array(
                'name'         => 'IMPress Agents',
                'slug'         => 'impress-agents',
                'required'     => false,
                'source'       => 'https://downloads.wordpress.org/plugin/impress-agents.zip'
            ),


        ), $plugins);

    }


    $beaver_plugin = array(
        'name'     => 'Beaver Builder',
        'slug'     => 'beaver-builder-lite-version',
        'required' => true,
    );


    if ( defined('FL_BUILDER_LITE') && ! FL_BUILDER_LITE ) {

        $beaver_plugin = array(
            'name'         => 'Beaver Builder Plugin',
            'slug'         => 'bb-plugin',
            'required'     => true
        );

    }

    $plugins =  array_merge(array(

        $beaver_plugin,

        array(
            'name'     => 'WPZOOM Addons for Beaver Builder',
            'slug'     => 'wpzoom-addons-for-beaver-builder',
            'required' => true,
        ),

        array(
            'name'         => 'Instagram Widget by WPZOOM',
            'slug'         => 'instagram-widget-by-wpzoom',
            'required'     => false,
        ),

        array(
            'name'         => 'MailPoet',
            'slug'         => 'mailpoet',
            'required'     => false,
        ),

    ), $plugins);

    return $plugins;

}

add_filter('zoom_register_theme_required_plugins', 'zoom_register_theme_required_plugins_callback');




/*  Let users change "Older Posts" button text from Jetpack Infinite Scroll
=========================================================================== */

function presence_infinite_scroll_js_settings( $settings ) {
    $settings['text'] = esc_js( esc_html( option::get( 'infinite_scroll_handle_text' ) ) );

    return $settings;
}
add_filter( 'infinite_scroll_js_settings', 'presence_infinite_scroll_js_settings' );



/* Enable Excerpts for Pages
==================================== */

add_action( 'init', 'wpzoom_excerpts_to_pages' );
function wpzoom_excerpts_to_pages() {
    add_post_type_support( 'page', 'excerpt' );
}



/*  Custom Excerpt Length
==================================== */

function new_excerpt_length( $length ) {
    return (int) option::get( "excerpt_length" ) ? (int)option::get( "excerpt_length" ) : 50;
}

add_filter( 'excerpt_length', 'new_excerpt_length' );



/*  Maximum width for images in posts
=========================================== */

if ( ! isset( $content_width ) ) $content_width = 800;

function presence_content_width() {
   if ( is_page_template( 'page-templates/full-width.php' ) || is_page_template( 'page-templates/homepage-widgetized.php' ) ) {
            global $content_width;
            $content_width = 1215;
   }
}

add_action( 'template_redirect', 'presence_content_width' );




if ( ! function_exists( 'presence_get_the_archive_title' ) ) :

/* Custom Archives titles.
=================================== */
function presence_get_the_archive_title( $title ) {
    if ( is_category() ) {
        $title = single_cat_title( '', false );
    }

    return $title;
}
endif;
add_filter( 'get_the_archive_title', 'presence_get_the_archive_title' );



if ( ! function_exists( 'presence_alter_main_query' ) ) :
/**
 * Alter main WordPress Query to exclude specific categories
 * and posts from featured slider if this is configured via Theme Options.
 *
 * @param $query WP_Query
 */
function presence_alter_main_query( $query ) {
    // until this is fixed https://core.trac.wordpress.org/ticket/27015
    $is_front = false;

    if ( get_option( 'page_on_front' ) == 0 ) {
        $is_front = is_front_page();
    } else {
        $is_front = $query->get( 'page_id' ) == get_option( 'page_on_front' );
    }

    if ( $query->is_main_query() && $is_front ) {
        if ( option::is_on( 'hide_featured' ) ) {
            $featured_posts = new WP_Query( array(
                'post__not_in'   => get_option( 'sticky_posts' ),
                'posts_per_page' => option::get( 'slideshow_posts' ),
                'meta_key'       => 'wpzoom_is_featured',
                'meta_value'     => 1
            ) );

            $postIDs = array();
            while ( $featured_posts->have_posts() ) {
                $featured_posts->the_post();
                $postIDs[] = get_the_ID();
            }

            wp_reset_postdata();

            $query->set( 'post__not_in', $postIDs );
        }

        if (
            is_array( option::get( 'recent_part_exclude' ) ) &&
            count( option::get( 'recent_part_exclude' ) )
        ) {
            $query->set( 'cat', '-' . implode( ',-', (array) option::get('recent_part_exclude') ) );
        }
    }
}
endif;
add_action( 'pre_get_posts', 'presence_alter_main_query' );



/* Register Custom Fields in Profile: Facebook, Twitter
===================================================== */

add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) { ?>

    <h3><?php _e('Additional Profile Information', 'wpzoom'); ?></h3>

    <table class="form-table">


        <tr>
            <th><label for="twitter"><?php _e('Twitter Username', 'wpzoom'); ?></label></th>

            <td>
                <input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e('Please enter your Twitter username', 'wpzoom'); ?></span>
            </td>
        </tr>

        <tr>
            <th><label for="facebook_url"><?php _e('Facebook Profile URL', 'wpzoom'); ?></label></th>

            <td>
                <input type="text" name="facebook_url" id="facebook_url" value="<?php echo esc_attr( get_the_author_meta( 'facebook_url', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e('Please enter your Facebook profile URL', 'wpzoom'); ?></span>
            </td>
        </tr>

        <tr>
            <th><label for="facebook_url"><?php _e('Instagram Username', 'wpzoom'); ?></label></th>

            <td>
                <input type="text" name="instagram_url" id="instagram_url" value="<?php echo esc_attr( get_the_author_meta( 'instagram_url', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e('Please enter your Instagram username', 'wpzoom'); ?></span>
            </td>
        </tr>

    </table>
<?php }

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );

function my_save_extra_profile_fields( $user_id ) {

    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;

    update_user_meta( $user_id, 'instagram_url', $_POST['instagram_url'] );
    update_user_meta( $user_id, 'facebook_url', $_POST['facebook_url'] );
    update_user_meta( $user_id, 'twitter', $_POST['twitter'] );
}



/* Disable Unyson shortcodes with the same name as in ZOOM Framework
====================================================================== */

function _filter_theme_disable_default_shortcodes($to_disable) {
    $to_disable[] = 'tabs';
    $to_disable[] = 'button';

    return $to_disable;
}
add_filter('fw_ext_shortcodes_disable_shortcodes', '_filter_theme_disable_default_shortcodes');


if ( ! function_exists( 'fw_get_category_term_list' ) ) :
    /**
     * Function that return an array of categories by post_type.
     *
     * @return array - array of available categories
     */
    function fw_get_category_term_list( $post_type = 'post' ) {

        $args   = array(
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
            'taxonomy'   => 'category'
        );

        if ( $post_type === 'portfolio' ) {
            $args   = array(
                'hide_empty' => true,
                'taxonomy'   => 'portfolio'
            );
        } elseif ( $post_type === 'jetpack-portfolio' ) {
            $args   = array(
                'hide_empty' => true,
                'taxonomy'   => 'jetpack-portfolio-type'
            );
        } elseif ( $post_type === 'product' ) {
            $args   = array(
                'hide_empty' => true,
                'taxonomy'   => 'product_cat'
            );
        }

        $terms  = get_terms( $args );
        $result = wp_list_pluck( $terms, 'name', 'term_id' );

        return array( 0 => esc_html__( 'All Categories', 'fw' ) ) + $result;
    }
endif;



/**
 * Show custom logo or blog title and description.
 *
 */
function presence_custom_logo()
{
    has_custom_logo() ? the_zoom_custom_logo() : printf('<a href="%s" title="%s">%s</a>', home_url(), get_bloginfo('description'), get_bloginfo('name'));
}


/* Make the Gallery Widget (Jetpack) wider
============================================ */

add_filter('gallery_widget_content_width', 'gallery_widget_content_width_callback');

function gallery_widget_content_width_callback($width){
    return 1215;
}

/* Enqueue scripts and styles for the front end.
=========================================== */

function presence_scripts() {

    $data = presence_customizer_data();

    if ( '' !== $google_request = zoom_get_google_font_uri( $data ) ) {
        wp_enqueue_style( 'presence-google-fonts', $google_request, WPZOOM::$themeVersion );
    }

    // Load our main stylesheet.
    wp_enqueue_style( 'presence-style', get_stylesheet_uri(), array(), WPZOOM::$themeVersion );

    wp_enqueue_style( 'media-queries', get_template_directory_uri() . '/css/media-queries.css', array(), WPZOOM::$themeVersion );

    $color_scheme = get_theme_mod('color-palettes', zoom_customizer_get_default_option_value( 'color-palettes', $data ));
    wp_enqueue_style('presence-style-color-' . $color_scheme, get_template_directory_uri() . '/styles/' . $color_scheme . '.css', array(), WPZOOM::$themeVersion);

    wp_enqueue_style( 'dashicons' );

    wp_enqueue_script( 'slicknav', get_template_directory_uri() . '/js/jquery.slicknav.min.js', array( 'jquery' ), WPZOOM::$themeVersion, true );

    wp_enqueue_script( 'flickity', get_template_directory_uri() . '/js/flickity.pkgd.min.js', array(), WPZOOM::$themeVersion, true );

    wp_enqueue_script( 'fitvids', get_template_directory_uri() . '/js/jquery.fitvids.js', array( 'jquery' ), WPZOOM::$themeVersion, true );

    wp_enqueue_script( 'flexslider', get_template_directory_uri() . '/js/flexslider.js', array( 'jquery' ), WPZOOM::$themeVersion, true );

    wp_enqueue_script( 'retina', get_template_directory_uri() . '/js/retina.min.js', array('underscore'), WPZOOM::$themeVersion, true );

    wp_enqueue_script( 'superfish', get_template_directory_uri() . '/js/superfish.min.js', array( 'jquery' ), WPZOOM::$themeVersion, true );

    wp_enqueue_script( 'search_button', get_template_directory_uri() . '/js/search_button.js', array(), WPZOOM::$themeVersion, true );

    $themeJsOptions = option::getJsOptions();

    wp_enqueue_script( 'presence-script', get_template_directory_uri() . '/js/functions.js', array( 'jquery' ), WPZOOM::$themeVersion, true );
    wp_localize_script( 'presence-script', 'zoomOptions', $themeJsOptions );
}

add_action( 'wp_enqueue_scripts', 'presence_scripts' );


/**
 * Enqueue scripts and styles needed for Gutenberg and admin area.
 ================================================== */

function presence_block_scripts() {
    // Add custom fonts, used in the main stylesheet.
    wp_enqueue_style( 'presence-fonts', presence_fonts_editor(), false, '@@pkg.version', 'all' );

}
add_action( 'admin_enqueue_scripts', 'presence_block_scripts' );


/**
 * Register custom fonts in Gutenberg.
 ================================================== */

function presence_fonts_editor() {
    $fonts_url = '';

    $font_families = array();
        $font_families[] = 'Libre Franklin:100,100i,200,200i,300,300i,400,400i,600,600i,700,700i|Montserrat:500,700';
        $query_args = array(
            'family' => rawurlencode( implode( '|', $font_families ) ),
            'subset' => rawurlencode( 'latin,latin-ext' ),
        );
        $fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
    return esc_url_raw( $fonts_url );
}

if (!function_exists('wpzoom_get_value')):
    function wpzoom_get_value($val, $default = '', $key = null)
    {
        if (isset($val) && isset($key)) {
            return (isset($val[$key])) ? $val[$key] : $default;
        } else {
            return isset($val) ? $val : $default;
        }
    }
endif;