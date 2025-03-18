<?php
/*-----------------------------------------------------------------------------------*/
/* Initializing Widgetized Areas (Sidebars)				 							 */
/*-----------------------------------------------------------------------------------*/


register_sidebar(array('name'=>'Sidebar',
   'id' => 'Sidebar',
   'before_widget' => '<div class="widget %2$s" id="%1$s">',
   'after_widget' => '<div class="clear"></div></div>',
   'before_title' => '<h3 class="title">',
   'after_title' => '</h3>',
));



/*----------------------------------*/
/* Homepage widgetized areas        */
/*----------------------------------*/

register_sidebar(array(
    'name'=>'Homepage: Top (full-width)',
    'id' => 'home-main',
    'description' => 'Widget area below the slider on page template "Homepage (Widgetized)"',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="title">',
    'after_title' => '</h3>',
));


register_sidebar( array(
    'name'          => 'Homepage: Column 1/3',
    'id'            => 'home-1',
    'description'   => 'Widget area for page template "Homepage (Widgetized)".',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="clear"></div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );

register_sidebar( array(
    'name'          => 'Homepage: Column 2/3',
    'id'            => 'home-2',
    'description'   => 'Widget area for page template "Homepage (Widgetized)"',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="clear"></div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );

register_sidebar( array(
    'name'          => 'Homepage: Column 3/3',
    'id'            => 'home-3',
    'description'   => 'Widget area for page template "Homepage (Widgetized)".',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="clear"></div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );



register_sidebar(array(
    'name'=>'Homepage: Bottom (full-width)',
    'id' => 'home-bottom',
    'description' => 'Widget area below the 3 columns on template "Homepage (Widgetized)". &#13; &#10; &#09; Recommended widgets: Call to Action, Jetpack Portfolio, Testimonials, Sub-pages, Recent Posts.',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="title">',
    'after_title' => '</h3>',
));



register_sidebar( array(
    'name'          => 'Homepage Bottom: Column 1/3',
    'id'            => 'home-4',
    'description'   => 'Widget area for page template "Homepage (Widgetized)".',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="clear"></div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );

register_sidebar( array(
    'name'          => 'Homepage Bottom: Column 2/3',
    'id'            => 'home-5',
    'description'   => 'Widget area for page template "Homepage (Widgetized)"',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="clear"></div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );

register_sidebar( array(
    'name'          => 'Homepage Bottom: Column 3/3',
    'id'            => 'home-6',
    'description'   => 'Widget area for page template "Homepage (Widgetized)".',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="clear"></div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );


/*----------------------------------*/
/* Footer widgetized areas		    */
/*----------------------------------*/

register_sidebar( array(
    'name'          => 'Footer: Column 1',
    'id'            => 'footer_1',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="clear"></div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );

register_sidebar( array(
    'name'          => 'Footer: Column 2',
    'id'            => 'footer_2',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="clear"></div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );

register_sidebar( array(
    'name'          => 'Footer: Column 3',
    'id'            => 'footer_3',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="clear"></div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );

register_sidebar( array(
    'name'          => 'Footer: Full-width Column',
    'id'            => 'footer_4',
    'description'   => 'Recommended widget: Instagram Widget by WPZOOM.',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="clear"></div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );



/* WooCommerce Sidebar
===============================*/

register_sidebar( array(
    'name'          => 'WooCommerce Sidebar',
    'id'            => 'sidebar-shop',
    'description'   => 'Right sidebar for WooCommerce pages. Leave empty for a full-width shop page.',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="cleaner">&nbsp;</div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );


/* Impress Listings
===============================*/

register_sidebar( array(
    'name'          => 'Listings Sidebar',
    'id'            => 'sidebar-listings',
    'description'   => 'Right sidebar for Listing pages (Requires IMPress Listings plugin). Recommended widgets: WP Listings - Search, WP Listings - Featured Listings',
    'before_widget' => '<div class="widget %2$s" id="%1$s">',
    'after_widget'  => '<div class="cleaner">&nbsp;</div></div>',
    'before_title'  => '<h3 class="title">',
    'after_title'   => '</h3>',
) );


/* Header - for social icons
===============================*/

register_sidebar(array(
    'name'=>'Header Social Icons',
    'id' => 'header_social',
    'description' => 'Widget area in the header. Install the "Social Icons Widget by WPZOOM" plugin and add the widget here.',
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="title"><span>',
    'after_title' => '</span></h3>',
));
