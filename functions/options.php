<?php return array(


/* Theme Admin Menu */
"menu" => array(
    array("id"    => "1",
          "name"  => "General"),

    array("id"    => "2",
          "name"  => "Homepage"),

    array("id"    => "3",
          "name"  => "Portfolio"),


    array("id"    => "7",
          "name"  => "Banners"),
),

/* Theme Admin Options */
"id1" => array(
    array("type"  => "preheader",
          "name"  => "Theme Settings"),


    array(
        "type" => "startsub",
        "name" => "Enabled Components",
    ),

        array(
            "desc" => sprintf('Here you can enable or disable features needed for different uses of the theme. '),
            "type" => "paragraph",
            "id" => "uses_desc",
        ),

        array("name"  => "Music",
              "id"    => "component_music",
              "desc" => 'This will require the following plugins: <a href="https://wordpress.org/plugins/cue/" target="_blank">Cue Playlists</a>, <a href="https://audiotheme.com/view/cuebar/" target="_blank">CueBar by AudioTheme</a>.',
              "std"   => "off",
              "type"  => "checkbox"),

        array("name"  => "Real Estate",
              "id"    => "component_realestate",
              "desc" => 'This will require the following plugin: <a href="https://wordpress.org/plugins/idx-broker-platinum/" target="_blank">IMPress for IDX Broker</a>',
              "std"   => "off",
              "type"  => "checkbox"),

        array("name"  => "Portfolio",
              "id"    => "component_portfolio",
              "desc" => 'This will require to activate the <a href="https://wordpress.org/plugins/wpzoom-portfolio/" target="_blank">WPZOOM Portfolio</a> plugin.',
              "std"   => "on",
              "type"  => "checkbox"),

        array("name"  => "Events",
              "id"    => "component_events",
              "desc" => 'This will require the following plugin: <a href="https://wordpress.org/plugins/the-events-calendar/" target="_blank">The Events Calendar</a>.',
              "std"   => "off",
              "type"  => "checkbox"),


    array(
        "type" => "endsub"
    ),


    array(
        "type" => "startsub",
        "name" => "Miscellaneous",
    ),

    array("name"  => "Custom Feed URL",
          "desc"  => "Example: <strong>http://feeds.feedburner.com/wpzoom</strong>",
          "id"    => "misc_feedburner",
          "std"   => "",
          "type"  => "text"),


    array(
      "name" => "Display WooCommerce Cart Button in the Header?",
      "id" => "cart_icon",
      "std" => "on",
      "type" => "checkbox"
    ),

    array(
        "name" => __( "Disable Featured Posts Re-Order Module", 'wpzoom' ),
        "desc"  => "This feature is used for Featured Posts and Page showing in the Slideshow. If it creates problems or conflicts with other plugins, you can disable it here.",
        "id"   => "disable_featured_posts_module",
        "std"  => "off",
        "type" => "checkbox"
    ),

    array(
        "type" => "endsub"
    ),

    array(
        "type" => "startsub",
        "name" => "Infinite Scroll",
    ),

        array(
            "desc" => sprintf('This feature depends on <a href="http://jetpack.me" target="_blank">Jetpack</a>, please install it first and then <a href="http://jetpack.me/support/activate-and-deactivate-modules/" target="_blank">activate Infinite Scroll module</a>. <br>Then navigate to %1$s to select a trigger for infinite scroll.', sprintf('<a href="%1$s" target="_blank">Reading Settings</a>', esc_url(admin_url('options-reading.php#infinite-scroll-options')))),
            "type" => "paragraph",
            "id" => "infinite_desc",
        ),

        array(
            "name" => "Load More Button Text",
            "desc" => "Used only when Scroll Infinitely is disabled in Reading Settings.",
            "id"   => "infinite_scroll_handle_text",
            "type" => "text",
            "std"  => "Older Posts"
        ),

    array(
        "type" => "endsub"
    ),


    array(
            "type" => "preheader",
            "name" => "Blog Posts"
        ),


          array("type" => "startsub",
               "name" => "Blog Layout"),

              array(
                  "name" => "Page Layout",
                  "desc" => "Select if you want to show or not the Sidebar on pages with posts",
                  "id" => "layout_home",
                  "options" => array(
                    'side-right' => 'Sidebar on the right',
                    'full' => 'Full Width'
                  ),
                  "std" => "side-right",
                  "type" => "select-layout"
              ),


              array("name"  => "Posts Style",
                  "id"    => "post_view",
                  "options" => array('List', 'Grid', 'Blog'),
                  "std"   => "List",
                  "type"  => "select"),

          array("type"  => "endsub"),



        array(
            "name" => "Content",
            "desc" => "Number of posts displayed on homepage can be changed <a href=\"options-reading.php\" target=\"_blank\">here</a>.",
            "id" => "display_content",
            "options" => array(
                'Excerpt',
                'Full Content',
                'None'
            ),
            "std" => "Excerpt",
            "type" => "select"
        ),

        array(
            "name" => "Excerpt length",
            "desc" => "Default: <strong>50</strong> (words)",
            "id" => "excerpt_length",
            "std" => "50",
            "type" => "text"
        ),


        array(
            "name" => "Display Featured Image",
            "id" => "display_thumb",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Category",
            "id" => "display_category",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Author",
            "id" => "display_author",
            "std" => "on",
            "type" => "checkbox"
        ),


        array(
            "name" => "Display Date/Time",
            "desc" => "<strong>Date/Time format</strong> can be changed <a href='options-general.php' target='_blank'>here</a>.",
            "id" => "display_date",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Comments Count",
            "id" => "display_comments",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Read More Button",
            "id" => "display_more",
            "std" => "on",
            "type" => "checkbox"
        ),



        array(
            "type" => "preheader",
            "name" => "Single Posts Options"
        ),


        array("type" => "startsub",
               "name" => "Header Image"),

            array(
                "name" => "Display Featured Image at the Top",
                "id" => "post_thumb",
                "std" => "on",
                "type" => "checkbox"
            ),


        array("type"  => "endsub"),


        array(
            "name" => "Display Author",
            "desc" => "You can edit your profile on this <a href='profile.php' target='_blank'>page</a>.",
            "id" => "post_author",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Date/Time",
            "desc" => "<strong>Date/Time format</strong> can be changed <a href='options-general.php' target='_blank'>here</a>.",
            "id" => "post_date",
            "std" => "on",
            "type" => "checkbox"
        ),


        array(
            "name" => "Display Category",
            "id" => "post_category",
            "std" => "on",
            "type" => "checkbox"
        ),



        array(
            "name" => "Display Tags",
            "id" => "post_tags",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Author Profile",
            "desc" => "You can edit your profile on this <a href='profile.php' target='_blank'>page</a>.",
            "id" => "post_author_box",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "name" => "Display Comments",
            "id" => "post_comments",
            "std" => "on",
            "type" => "checkbox"
        ),

        array(
            "type" => "preheader",
            "name" => "Single Page Options"
        ),


        array("type" => "startsub",
               "name" => "Header Image"),

            array(
                "name" => "Display Featured Image at the Top",
                "id" => "page_thumb",
                "std" => "on",
                "type" => "checkbox"
            ),

        array("type"  => "endsub"),

        array("name"  => "Display Comments",
              "id"    => "comments_page",
              "std"   => "off",
              "type"  => "checkbox"),


    ),


"id2" => array(

    array("type"  => "preheader",
          "name"  => "Homepage Slideshow"),

    array("name"  => "Display Slideshow on Homepage?",
          "desc"  => "To feature a post or page in the slider just check the option <strong>Featured in Homepage Slider</strong> when you edit a specific post or page.",
          "id"    => "featured_posts_show",
          "std"   => "on",
          "type"  => "checkbox"),

    array("name"  => "What Slideshow Displays",
          "desc"  => "Select the type of content that should be displayed in the slider. <strong>Slides are ordered by date</strong>.<br /><br /><strong>Slideshow posts</strong> can be added on this <a href=\"edit.php?post_type=slider\">page</a>.<br /><br /><strong>Featured Listings</strong> can be displayed if the <a target=\"_blank\" href=\"https://wordpress.org/plugins/wp-listings/\">IMPress Listings</a> plugin is installed.",
          "options" => array( 'Slideshow Posts', 'Featured Posts', 'Featured Pages', 'Featured Listings'),
          "id"   => "featured_type",
          "std"   => "Featured Posts",
          "type"  => "select"),


    array("name"  => "Autoplay Slideshow?",
          "desc"  => "Do you want to auto-scroll the slides?",
          "id"    => "slideshow_auto",
          "std"   => "off",
          "type"  => "checkbox",
          "js"    => true),

    array("name"  => "Slider Autoplay Interval",
          "desc"  => "Select the interval (in miliseconds) at which the Slider should change slides (<strong>if autoplay is enabled</strong>). Default: 3000 (3 seconds).",
          "id"    => "slideshow_speed",
          "std"   => "3000",
          "type"  => "text",
          "js"    => true),

    array("name"  => "Number of Posts/Pages in Slider",
          "desc"  => "How many posts or pages should appear in the Slider on the homepage? Default: 5.",
          "id"    => "slideshow_posts",
          "std"   => "5",
          "type"  => "text"),


    array(
        "type" => "startsub",
        "name" => "Featured Posts Options",
    ),

    array(
        "name" => "Display Category",
         "id" => "slider_category",
        "std" => "on",
        "type" => "checkbox"
    ),

    array(
        "name" => "Display Date/Time",
        "desc" => "<strong>Date/Time format</strong> can be changed <a href='options-general.php' target='_blank'>here</a>.",
        "id" => "slider_date",
        "std" => "on",
        "type" => "checkbox"
    ),

    array(
        "name" => "Display Comments Count",
        "id" => "slider_comments",
        "std" => "on",
        "type" => "checkbox"
    ),

    array(
        "type" => "endsub"
    ),


    array(
        "name" => "Display Excerpt",
        "desc" => "Excerpt appears only on Featured Pages and Featured Listings",
        "id" => "slider_excerpt",
        "std" => "on",
        "type" => "checkbox"
    ),

    array(
        "name" => "Display View More Button",
        "id" => "slider_button",
        "std" => "on",
        "type" => "checkbox"
    ),



    array("type"  => "preheader",
        "name"  => "Recent Posts"),

    array("name"  => "Title for Recent Posts",
        "desc"  => "Default: <em>Recent Posts</em>",
        "id"    => "recent_title",
        "std"   => "Recent Posts",
        "type"  => "text"),

    array("name"  => "Exclude categories",
        "desc"  => "Choose the categories which should be excluded from the main Loop on the homepage.<br/><em>Press CTRL or CMD key to select/deselect multiple categories </em>",
        "id"    => "recent_part_exclude",
        "std"   => "",
        "type"  => "select-category-multi"),

    array("name"  => "Hide Featured Posts in Recent Posts?",
        "desc"  => "You can use this option if you want to hide posts which are featured in the slider on front page.",
        "id"    => "hide_featured",
        "std"   => "off",
        "type"  => "checkbox"),

),



'id3' => array(
   array(
         "type" => "preheader",
         "name" => "Jetpack Portfolio Options",
         "desc" => '</p><div class="clear"></div><p style="width:100%; margin-top:20px; font-style:normal;">In order to enable the <strong>Portfolio</strong> section, please install <strong>Jetpack</strong> plugin and activate the feature <strong>Custom Content Types</strong> from <strong>Jetpack Settings > Writing</strong>.<br/><br/>To display all your Portfolio posts on one page, create a new page, and assign the <strong>Portfolio (Jetpack)</strong> page template.<br/><br/>Make sure to use a different slug than <strong>"portfolio"</strong> for your Portfolio page, because this one is reserved by Jetpack.</p><div class="clear"></div>'
    ),


    array(
        "name" => "Portfolio Page",
        "desc" => "Choose the page to which should link the <strong>All</strong> button from Portfolio Categories navigation.",
        "id" => "portfolio_url",
        "std" => "",
        "type" => "select-page"
    ),

    array(
         "name" => "Number of posts per page in Portfolio Page (Jetpack)",
         "desc" => "Default: <strong>9</strong>",
         "id" => "portfolio_posts",
         "std" => "9",
         "type" => "text"
    ),

     array(
         "name" => "Display Category Navigation (Project Types)",
         "id" => "portfolio_category_nav",
         "std" => "on",
         "type" => "checkbox"
     ),


     array(
         "type" => "startsub",
         "name" => "Portfolio Posts Options on Portfolio Page",
     ),

         array("name"  => "Featured Image Aspect Ratio",
               "options" => array( 'Landscape (4:3)', 'Square (1:1)'),
               "id"   => "portfolio_ratio",
               "std"   => "Landscape (4:3)",
               "type"  => "select"),

         array(
             "name" => "Display Category",
             "id" => "portfolio_category",
             "std" => "on",
             "type" => "checkbox"
         ),

         array(
             "name" => "Display Excerpt",
             "id" => "portfolio_excerpt",
             "std" => "off",
             "type" => "checkbox"
        ),

     array(
         "type" => "endsub"
     ),



    array("type"  => "preheader",
          "name"  => "Portfolio Posts Options"),

    array(
        "name" => "Display Porfolio Category",
        "id" => "single_portfolio_category",
        "std" => "on",
        "type" => "checkbox"
    ),

    array(
        "name" => "Display Featured Image at the Top",
        "id" => "single_portfolio_thumb",
        "std" => "on",
        "type" => "checkbox"
    ),

),


"id7" => array(
    array("type"  => "preheader",
          "name"  => "Header Ad"),

    array("name"  => "Enable ad space in the header?",
          "id"    => "ad_head_select",
          "std"   => "off",
          "type"  => "checkbox"),

    array("name"  => "HTML Code (Adsense)",
          "desc"  => "Enter complete HTML code for your banner (or Adsense code) or upload an image below.",
          "id"    => "ad_head_code",
          "std"   => "",
          "type"  => "textarea"),

    array("name"  => "Upload your image",
          "desc"  => "Upload a banner image or enter the URL of an existing image.<br/>Recommended size: <strong>728 × 90px</strong>",
          "id"    => "banner_top",
          "std"   => "",
          "type"  => "upload"),

    array("name"  => "Destination URL",
          "desc"  => "Enter the URL where this banner ad points to.",
          "id"    => "banner_top_url",
          "type"  => "text"),

    array("name"  => "Banner Title",
          "desc"  => "Enter the title for this banner which will be used for ALT tag.",
          "id"    => "banner_top_alt",
          "type"  => "text"),


    array("type"  => "preheader",
          "name"  => "Sidebar Ad"),

    array("name"  => "Enable ad space in sidebar?",
          "id"    => "ad_side",
          "std"   => "off",
          "type"  => "checkbox"),

    array("name"  => "Ad Position",
          "desc"  => "Do you want to place the banner before the widgets or after the widgets?",
          "id"    => "ad_side_pos",
          "options" => array('Before widgets', 'After widgets'),
          "std"   => "Before widgets",
          "type"  => "select"),

    array("name"  => "HTML Code (Adsense)",
          "desc"  => "Enter complete HTML code for your banner (or Adsense code) or upload an image below.",
          "id"    => "ad_side_imgpath",
          "std"   => "",
          "type"  => "textarea"),

    array("name"  => "Upload your image",
          "desc"  => "Upload a banner image or enter the URL of an existing image.<br/>Recommended size: <strong>300 × 300px</strong>",
          "id"    => "banner_sidebar",
          "std"   => "",
          "type"  => "upload"),

    array("name"  => "Destination URL",
          "desc"  => "Enter the URL where this banner ad points to.",
          "id"    => "banner_sidebar_url",
          "type"  => "text"),

    array("name"  => "Banner Title",
          "desc"  => "Enter the title for this banner which will be used for ALT tag.",
          "id"    => "banner_sidebar_alt",
          "type"  => "text"),


    array("type"  => "preheader",
          "name"  => "Post Ad"),

  array("name"  => "Enable ad space after the content in posts?",
          "id"    => "banner_post_enable",
          "std"   => "off",
          "type"  => "checkbox"),

    array("name"  => "HTML Code (Adsense)",
          "desc"  => "Enter complete HTML code for your banner (or Adsense code) or upload an image below.",
          "id"    => "banner_post_html",
          "std"   => "",
          "type"  => "textarea"),

  array("name"  => "Upload your image",
          "desc"  => "Upload a banner image or enter the URL of an existing image.<br/>Recommended size: <strong>728 × 90px</strong>",
          "id"    => "banner_post",
          "std"   => "",
          "type"  => "upload"),

  array("name"  => "Destination URL",
          "desc"  => "Enter the URL where this banner ad points to.",
          "id"    => "banner_post_url",
          "type"  => "text"),

  array("name"  => "Banner Title",
          "desc"  => "Enter the title for this banner which will be used for ALT tag.",
          "id"    => "banner_post_alt",
          "type"  => "text"),

)

/* end return */);