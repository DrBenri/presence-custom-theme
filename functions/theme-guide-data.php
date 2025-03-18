<?php
return array(
    'documentation' => array(
        'header' => __('Read Theme Documentation', 'wpzoom'),
        'content' => __('<strong>Theme Documentation</strong> is the place where you\'ll find the information needed to setup the theme quickly, and other details about theme-specific features.', 'wpzoom'),
        'actions' => array(
            '<a class="button button-primary" href="https://www.wpzoom.com/documentation/'.str_replace('_', '-', WPZOOM::$theme_raw_name).'/" target="_blank">'.WPZOOM::$themeName.' Documentation &raquo;</a>
'
        )
    ),
    'demo-content' => array(
        'header' => __('Import the Demo Content', 'wpzoom'),
        'content' => __('If you’re installing the theme on a new site, installing the demo content is the best way to get familiarized. This feature can be found on the <a href="admin.php?page=wpzoom_options" target="_blank">Theme Options</a> page, in the <strong>Import/Export</strong> section.', 'wpzoom'),
        'actions' => array(
            '<a class="button button-primary" href="https://www.wpzoom.com/docs/demo-content-importer/" target="_blank">View Instructions</a> &nbsp;&nbsp;',
            '<a class="button button-secondary" href="admin.php?page=wpzoom_options" target="_blank">Open Theme Options</a>'
        )
    ),
    'customizer' => array(
        'header' => __('Add your Logo & Customize the Theme', 'wpzoom'),
        'content' => __('Using the <strong>Live Customizer</strong> you can easily upload your <strong>logo image</strong>, change <strong>fonts, colors, widgets, menus</strong> and much more!', 'wpzoom'),
        'actions' => array(
            '<a class="button button-primary" href="customize.php" target="_blank">Open Theme Customizer »</a>',
        )
    ),
    'plugins' => array(
        'header' => __('Install Required Plugins', 'wpzoom'),
        'content' => __('In order to enable all the features from your theme, you’ll need to install and activate the required plugins such as <strong>Jetpack</strong> or <strong>WooCommerce</strong>, which are available for <strong>free</strong>.<br/>
            <h4>General Plugins</h4><ul><li><a href="https://wordpress.org/plugins/beaver-builder-lite-version/" target="_blank">Beaver Builder</a> <em>(Free)</em> - a simple and easy to use <strong>Page Builder</strong></li> <li><a href="https://wordpress.org/plugins/wpzoom-addons-for-beaver-builder/" target="_blank">Beaver Builder Addons by WPZOOM</a> <em>(Free)</em> - A suite of useful addons for Beaver Builder</li> <li><a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> <em>(Free)</em> - popular <strong>eCommerce</strong> plugin</li><li><a href="https://wordpress.org/plugins/social-icons-widget-by-wpzoom/" target="_blank">Social Icons Widget</a> <em>(Free)</em> - simple plugin to add social icons</li><li><a href="https://wordpress.org/plugins/mailpoet/" target="_blank">MailPoet</a> <em>(Free)</em> - create and send newsletters</li></ul>
           <br /><hr /> <h4>Hotel Plugins</h4><ul><li><a href="https://1.envato.market/ePB0D" target="_blank">HBook</a> <em>($39)</em> - powerful <strong>hotel booking system</strong></li> </ul>
            <br /><hr /><h4>Music Plugins</h4><ul><li><a href="https://wordpress.org/plugins/cue/" target="_blank">Cue Playlists</a> <em>(Free)</em> - create and manage audio playlists</li> <li><a href="https://audiotheme.com/view/cuebar/" target="_blank">CueBar by AudioTheme</a> <em>($39 -  Get it FREE on this <a href="https://audiotheme.com/checkout/?add-to-cart=3610&coupon_code=wpzoom3610" target="_blank">link</a>)</em> - music bar on top or bottom of the site</li><li><a href="https://wordpress.org/plugins/gigpress/" target="_blank">GigPress</a> - display and manage upcoming shows and tours</li></ul>
            <br /><hr /><h4>Real Estate Plugins</h4><ul><li><a href="https://wordpress.org/plugins/wp-listings/" target="_blank">IMPress Listings</a> <em>(Free)</em> - real estate listing management</li><li><a href="https://wordpress.org/plugins/impress-agents/" target="_blank">IMPress Agents</a> <em>(Free)</em> - add agents profiles to real estate listings</li><li><a href="https://wordpress.org/plugins/posts-to-posts/" target="_blank">Posts 2 Posts</a> <em>(Free)</em> - connect listings with agents</li></ul>
            <br /><hr /><h4>Events Plugins</h4><ul><li><a href="https://wordpress.org/plugins/the-events-calendar/" target="_blank">The Events Calendar</a> <em>(Free)</em> - one of the most popular events plugin</li></ul>
            ', 'wpzoom'),
        'actions' => array(
            '<a class="button button-primary" href="admin.php?page=tgmpa-install-plugins" target="_blank">Install Required Plugins</a>&nbsp;&nbsp;',
            '<a class="button button-secondary" href="https://www.wpzoom.com/recommended-plugins/" target="_blank">Recommended Plugins by WPZOOM</a>'
        )
    ),
    'front-page' => array(
        'header' => __('Setup Homepage Template', 'wpzoom'),
        'content' => __('Don\'t want to display your latest posts on homepage? <br/><br/>Create a <a href="post-new.php?post_type=page">new page</a> and assign a special <strong>Page Template</strong> to it, depending on your needs (<a href="http://www.wpzoom.com/docs/page-templates/" target="_blank">view instructions</a>):<br/><ul><li><strong>Homepage (Widgetized)</strong> - <em>page template that includes multiple widget areas. Manage widgets located on this page from Widgets page or Customizer. </em></li><li><strong>Homepage (Page Builder)</strong> - <em>special page template for front page that integrates great with <strong>Unyson Page Builder</strong> and that displays a slideshow at the top.</em></li></ul>', 'wpzoom'),
        'actions' => array(
            '<a class="button button-primary" href="post-new.php?post_type=page" target="_blank">Create a New Page »</a>&nbsp;&nbsp;',
            '<a class="button button-secondary" href="options-reading.php" target="_blank">Change what Front Page displays</a>'
        ),
    ),
    'support' => array(
        'header' => __('Need one-to-one Assistance?', 'wpzoom'),
        'content' => __('Need help setting up your theme or have a question? Get in touch with our Support Team. We\'d love the opportunity to help you.', 'wpzoom'),
        'actions' => array(
            '<a class="button button-primary" href="https://www.wpzoom.com/support/tickets/" target="_blank">Open Support Desk »</a>'
        ),
    )
);