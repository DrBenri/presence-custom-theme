<?php return apply_filters(
	'zoom_options',
	array(


		/* Framework Admin Menu */
		'menu'          => array(

			array(
				'id'   => '4',
				'name' => __( 'Miscellaneous', 'wpzoom' ),
			),

			// 5 is reserved for styling

			'framework'     => array(
				'name' => __( 'Framework', 'wpzoom' ),
			),

			// 7 is reserved for banners

			'import-export' => array(
				'name' => __( 'Import/Export', 'wpzoom' ),
			),
		),

		/* Framework Admin Options */

		'id4'           => array(
			array(
				'type' => 'preheader',
				'name' => __( 'Custom Code', 'wpzoom' ),
			),

			array(
				'name' => __( 'Header Code', 'wpzoom' ),
				'desc' => __( 'Here you can add scripts that will be added before the end of <code>&lt;head&gt;</code> tag.', 'wpzoom' ),
				'id'   => 'header_code',
				'std'  => '',
				'type' => 'textarea',
			),

			array(
				'name' => __( 'Footer Code & Analytics', 'wpzoom' ),
				'desc' => __( 'If you want to add some tracking script to the footer, like Google Analytics, insert the complete tracking code here. The following code will be added to the footer before the closing <code>&lt;/body&gt;</code> tag.', 'wpzoom' ),
				'id'   => 'footer_code',
				'std'  => '',
				'type' => 'textarea',
			),

            array(
                'type' => 'preheader',
                'name' => __( 'Google Fonts', 'wpzoom' ),
            ),
            array(
                'name' => __( 'Disable and Remove Google Fonts Globally', 'wpzoom' ),
                'desc' => __( 'Enable this option to disable Google Fonts entirely. It will also remove Google Fonts registered by popular plugins such as Elementor, Beaver Builder. <br/> Removing Google Fonts will make your website GDPR-compliant and the Page Speed may increase.', 'wpzoom' ),

                'id'   => 'disable_google_fonts_wpz',
                'std'  => 'off',
                'type' => 'checkbox',
            ),
            array(
                'name' => esc_html__( 'Download & Host Google Fonts Locally', 'wpzoom' ),
                'desc' => __( 'Enable this option to download Google Fonts and host them locally. This will make your website GDPR-compliant, and your theme will continue using custom fonts without sending any visitor data to Google.<br/><br/><u><em>This feature is not available in all themes</em></u>', 'wpzoom' ),

                'id'   => 'enable_google_fonts_downloader',
                'std'  => 'off',
                'type' => 'checkbox',
            ),
            array(
                'name' => esc_html__( 'Google Fonts API Key', 'wpzoom' ),
                'desc' => __( 'Add your own API key for Google Fonts to override the key bundled with your theme. You can follow the steps from <a target="_blank" href="https://developers.google.com/fonts/docs/developer_api#APIKey">Google docs</a> to get an API key.', 'wpzoom' ),

                'id'   => 'google_fonts_api_key',
                'std'  => '',
                'type' => 'text',
            ),

			array(
				'type' => 'preheader',
				'name' => __( 'WordPress Features', 'wpzoom' ),
			),
			array(
				'name' => __( 'Disable Block-based Widgets Screen', 'wpzoom' ),
				'id'   => 'disable_widgets_block_editor',
				'std'  => 'on',
				'type' => 'checkbox',
			),

		),


		'framework'     => array(
			array(
				'type' => 'preheader',
				'name' => __( 'Framework Options', 'wpzoom' ),
			),

			array(
				'name' => __( 'Framework Generator Meta Tags', 'wpzoom' ),
				'desc' => __( "Includes information about theme and framework you use in meta tags along to WordPress ones, they are used just for information and doesn't impact your SEO.", 'wpzoom' ),
				'id'   => 'meta_generator',
				'std'  => 'on',
				'type' => 'checkbox',
			),

			array(
				'name' => __( 'Typography Preview', 'wpzoom' ),
				'desc' => __( 'Preview fonts in typography option dropdown. Disable this if this page takes long to load or you never need that.', 'wpzoom' ),
				'id'   => 'framework_fonts_preview',
				'std'  => 'on',
				'type' => 'checkbox',
			),

			array(
				'name' => __( 'Framework Updater', 'wpzoom' ),
				'desc' => __( 'This enables update features for WPZOOM framework such as menu in wp-admin and also global notifications about new updates.', 'wpzoom' ),
				'id'   => 'framework_update_enable',
				'std'  => 'on',
				'type' => 'checkbox',
			),

			array(
				'name' => __( 'Framework Updater Notification', 'wpzoom' ),
				'desc' => __( 'Enables or disables global wp-admin notification about new versions of framework. If previous option is disabled this one is irrelevant.', 'wpzoom' ),
				'id'   => 'framework_update_notification_enable',
				'std'  => 'on',
				'type' => 'checkbox',
			),

			array(
				'name' => __( 'Themes Updater Notification', 'wpzoom' ),
				'desc' => __( 'Enables or disables global wp-admin notification about new versions of theme.', 'wpzoom' ),
				'id'   => 'framework_theme_update_notification_enable',
				'std'  => 'on',
				'type' => 'checkbox',
			),

			array(
				'type' => 'preheader',
				'name' => __( 'Debug', 'wpzoom' ),
			),

			array(
				'name' => __( 'Debug info', 'wpzoom' ),
				'desc' => __( 'You can include this information in your support tickets on WPZOOM Support Desk.', 'wpzoom' ),
				'id'   => 'misc_debug',
				'std'  => '',
				'type' => 'textarea',
			),
			
		),

		'import-export' => array(
			array(
				'type' => 'preheader',
				'name' => __( 'Theme Options', 'wpzoom' ),
			),

			array(
				'name' => __( 'Import Options', 'wpzoom' ),
				'desc' => __( 'To import the options from another installation of this theme paste your code here.', 'wpzoom' ),
				'id'   => 'misc_import',
				'std'  => '',
				'type' => 'textarea',
			),

			array(
				'name' => __( 'Export Options', 'wpzoom' ),
				'desc' => __( 'Export the options to another installation of this theme, or to keep a backup of your options. You can can also save your options in a new text document.', 'wpzoom' ),
				'id'   => 'misc_export',
				'std'  => '',
				'type' => 'textarea',
			),

			array(
				'type' => 'preheader',
				'name' => __( 'Widgets', 'wpzoom' ),
			),

			array(
				'name'  => __( 'Load default widget settings', 'wpzoom' ),
				'desc'  => __( 'Click on this button to load the default widget settings (as in theme demo).</br><em><strong>NOTE:</strong> Click on <strong>Save all changes</strong> button to save other modifications before loading default widgets.</em>', 'wpzoom' ),
				'id'    => 'misc_load_default_widgets',
				'class' => 'button-primary',
				'type'  => 'button',
			),

			array(
				'name' => __( 'Import Widgets', 'wpzoom' ),
				'desc' => __( 'To import widgets from another installation of this theme insert your exported code here.', 'wpzoom' ),
				'id'   => 'misc_import_widgets',
				'std'  => '',
				'type' => 'textarea',
			),

			array(
				'name' => __( 'Export Widgets', 'wpzoom' ),
				'desc' => __( 'Export widgets to another installation of this theme.', 'wpzoom' ),
				'id'   => 'misc_export_widgets',
				'std'  => '',
				'type' => 'textarea',
			),
		),


	/* end return */
	)
);
