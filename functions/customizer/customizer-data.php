<?php


function presence_customizer_data()
{
    static $data = array();

    if(empty($data)){

        $media_viewport = 'screen and (min-width: 950px)';

        $data = array(
            'slider-container' => array(
                'title' => __('Slider Styles', 'wpzoom'),
                'priority' => 51,
                'options' => array(
                    'slider-styles' => array(
                        'setting' => array(
                            'default' => 'slide-style-1',
                            'sanitize_callback' => 'sanitize_text_field'
                        ),
                        'control' => array(
                            'type' => 'select',
                            'label' => __('Slider Styles', 'wpzoom'),
                            'choices' => array(
                                'slide-style-1' => __('Slider Style 1', 'wpzoom'),
                                'slide-style-2' => __('Slider Style 2', 'wpzoom'),
                                'slide-style-3' => __('Slider Style 3', 'wpzoom'),
                            ),
                        ),
                        'dom' => array(
                            'selector' => '#slider',
                            'rule' => 'toggle-class'
                        )
                    ),
                )
            ),
            'color-palettes-container' => array(
                'title' => __('Color Scheme', 'wpzoom'),
                'priority' => 40,
                'options' => array(
                    'color-palettes' => array(
                        'setting' => array(
                            'default' => 'default',
                            'sanitize_callback' => 'sanitize_text_field'
                        ),
                        'control' => array(
                            'control_type' => 'WPZOOM_Customizer_Control_Radio',
                            'label' => __('Color Scheme', 'wpzoom'),
                            'mode' => 'buttonset',
                            'choices' => array(
                                'default' => __('Default', 'wpzoom'),
                                'light' => __('Light', 'wpzoom'),
                                'dark' => __('Dark (new)', 'wpzoom'),
                                'blue' => __('Blue', 'wpzoom'),
                                'green' => __('Green', 'wpzoom'),
                                'red' => __('Red', 'wpzoom'),
                                'brown' => __('Brown', 'wpzoom')
                            )
                        ),
                        'dom' => array(
                            // * - mean that it is dynamic and would be from select choices.
                            'selector' => 'presence-style-color-*-css',
                            'rule' => 'change-stylesheet'
                        )
                    ),
                )
            ),
            'title_tagline' => array(
                'title' => __('Site Identity', 'wpzoom'),
                'priority' => 20,
                'options' => array(
                    'hide-tagline' => array(
                        'setting' => array(
                            'sanitize_callback' => 'absint',
                            'default' => true
                        ),
                        'control' => array(
                            'label' => __('Show Tagline', 'wpzoom'),
                            'type' => 'checkbox',
                            'priority' => 11
                        ),
                        'style' => array(
                            'selector' => '.brand-wrap .tagline',
                            'rule' => 'display'
                        )
                    ),
                    'custom_logo_retina_ready' => array(
                        'setting' => array(
                            'sanitize_callback' => 'absint',
                            'default' => false,
                        ),
                        'control' => array(
                            'label' => __('Is retina ready?', 'wpzoom'),
                            'type' => 'checkbox',
                            'priority' => 9
                        ),
                        'partial' => array(
                            'selector' => '.navbar-brand-wpz a',
                            'container_inclusive' => false,
                            'render_callback' => 'presence_custom_logo'
                        )
                    ),
                    'blogname' => array(
                        'setting' => array(
                            'sanitize_callback' => 'sanitize_text_field',
                            'default' => get_option('blogname'),
                            'transport' => 'postMessage',
                            'type' => 'option'
                        ),
                        'control' => array(
                            'label' => __('Site Title', 'wpzoom'),
                            'type' => 'text',
                            'priority' => 9
                        ),
                        'partial' => array(
                            'selector' => '.navbar-brand-wpz a',
                            'container_inclusive' => false,
                            'render_callback' => 'zoom_customizer_partial_blogname'
                        )
                    ),
                    'blogdescription' => array(
                        'setting' => array(
                            'sanitize_callback' => 'sanitize_text_field',
                            'default' => get_option('blogdescription'),
                            'transport' => 'postMessage',
                            'type' => 'option'
                        ),
                        'control' => array(
                            'label' => __('Tagline', 'wpzoom'),
                            'type' => 'text',
                            'priority' => 10
                        ),
                        'partial' => array(
                            'selector' => '.navbar-brand-wpz .tagline',
                            'container_inclusive' => false,
                            'render_callback' => 'zoom_customizer_partial_blogdescription'
                        )
                    ),
                    'custom_logo' => array(
                        'partial' => array(
                            'selector' => '.navbar-brand-wpz a',
                            'container_inclusive' => false,
                            'render_callback' => 'presence_custom_logo'
                        )
                    )
                )
            ),
            'theme-layout' => array(
                'title' => __('Theme Layout', 'wpzoom'),
                'priority' => 40,
                'options' => array(
                    'theme-layout-type' => array(
                        'setting' => array(
                            'sanitize_callback' => 'sanitize_text_field',
                            'default' => 'wpz_layout_boxed'
                        ),
                        'control' => array(
                            'label' => __('Select Theme Layout', 'wpzoom'),
                            'type' => 'radio',
                            'choices' => array(
                                'wpz_layout_boxed' => __('Boxed', 'wpzoom'),
                                'wpz_layout_full' => __('Full Width', 'wpzoom')
                            )
                        ),
                        'dom' => array(
                            'selector' => 'body',
                            'rule' => 'toggle-class'
                        )
                    )
                )
            ),
            'header' => array(
                'title' => __('Header Options', 'wpzoom'),
                'priority' => 50,
                'options' => array(
                    'top-navbar' => array(
                        'setting' => array(
                            'sanitize_callback' => 'sanitize_text_field',
                            'default' => 'block'
                        ),
                        'control' => array(
                            'label' => __('Show Top Navigation Menu', 'wpzoom'),
                            'type' => 'checkbox',
                        ),
                        'style' => array(
                            'selector' => '.top-navbar',
                            'rule' => 'display'
                        )
                    ),
                    'navbar-hide-search' => array(
                        'setting' => array(
                            'sanitize_callback' => 'sanitize_text_field',
                            'default' => 'block'
                        ),
                        'control' => array(
                            'label' => __('Show Search Form', 'wpzoom'),
                            'type' => 'checkbox',
                        ),
                        'style' => array(
                            'selector' => '.sb-search',
                            'rule' => 'display'
                        )
                    ),
                    'logo-align' => array(
                        'setting' => array(
                            'sanitize_callback' => 'sanitize_text_field',
                            'default' => 'left'
                        ),
                        'control' => array(
                            'label' => __('Logo Align', 'wpzoom'),
                            'type' => 'radio',
                            'choices' => array(
                                'left' => __('Left', 'wpzoom'),
                                'logo_center' => __('Center', 'wpzoom')
                            )
                        ),
                        'dom' => array(
                            'selector' => '.brand-wrap',
                            'rule' => 'toggle-class'
                        )
                    ),
                    'menu-align' => array(
                        'setting' => array(
                            'sanitize_callback' => 'sanitize_text_field',
                            'default' => 'left'
                        ),
                        'control' => array(
                            'label' => __('Main Menu Align', 'wpzoom'),
                            'type' => 'radio',
                            'choices' => array(
                                'left' => __('Left', 'wpzoom'),
                                'menu_center' => __('Center', 'wpzoom')
                            )
                        ),
                        'dom' => array(
                            'selector' => '#navbar-main',
                            'rule' => 'toggle-class'
                        )
                    )
                )
            ),
            'color' => array(
                'title' => __('General', 'wpzoom'),
                'panel' => 'color-scheme',
                'priority' => 110,
                'capability' => 'edit_theme_options',
                'options' => array(
                    'color-background' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#676c71'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Background Color', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => 'body',
                            'rule' => 'background'
                        )
                    ),
                    'color-body-text' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#444444'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Body Text', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => 'body',
                            'rule' => 'color'
                        )
                    ),
                    'color-logo' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Logo Color', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.navbar-brand-wpz a',
                            'rule' => 'color'
                        ),
                    ),
                    'color-logo-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Logo Color on Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.navbar-brand-wpz a:hover',
                            'rule' => 'color'
                        )
                    ),
                    'color-tagline' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#969696'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Site Description', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.navbar-brand-wpz .tagline',
                            'rule' => 'color'
                        )
                    ),
                    'color-link' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Link Color', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => 'a, .zoom-twitter-widget a',
                            'rule' => 'color'
                        )
                    ),
                    'color-link-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Link Color on Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => 'a:hover, .zoom-twitter-widget a:hover',
                            'rule' => 'color'
                        ),
                    ),
                    'color-button-background' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Buttons Background', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => 'button, input[type=button], input[type=reset], input[type=submit]',
                            'rule' => 'background'
                        )
                    ),
                    'color-button-color' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#ffffff'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Buttons Text Color', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => 'button, input[type=button], input[type=reset], input[type=submit]',
                            'rule' => 'color'
                        )
                    ),
                    'color-button-background-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Buttons Background on Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => 'button:hover, input[type=button]:hover, input[type=reset]:hover, input[type=submit]:hover',
                            'rule' => 'background'
                        )
                    ),
                    'color-button-color-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#ffffff'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Buttons Text Color on Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => 'button:hover, input[type=button]:hover, input[type=reset]:hover, input[type=submit]:hover',
                            'rule' => 'color'
                        )
                    )
                ),

            ),
            'color-top-menu' => array(
                'panel' => 'color-scheme',
                'title' => __('Top Menu', 'wpzoom'),
                'options' => array(
                    'color-top-menu-link' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Menu Item', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.top-navbar .navbar-wpz a',
                            'rule' => 'color'
                        )
                    ),
                    'color-top-menu-link-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Menu Item Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.top-navbar .navbar-wpz a:hover',
                            'rule' => 'color'
                        )
                    ),
                    'color-top-menu-link-current' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Menu Current Item', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.top-navbar .navbar-wpz > .current-menu-item > a, .top-navbar .navbar-wpz > .current_page_item > a',
                            'rule' => 'color'
                        )
                    )
                )
            ),
            'color-main-menu' => array(
                'panel' => 'color-scheme',
                'title' => __('Main Menu', 'wpzoom'),
                'options' => array(
                    'color-menu-background' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#fff'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Main Menu Background', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.main-navbar',
                            'rule' => 'background'
                        )
                    ),
                    'color-menu-link' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Menu Item', 'wpzoom'),
                        ),
                        'style' => array(
                            'id' => 'color-menu-link',
                            'selector' => '.main-navbar .sf-menu > li > a',
                            'rule' => 'color'
                        )
                    ),
                    'color-menu-link-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Menu Item Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.main-navbar .sf-menu > li a:hover',
                            'rule' => 'color'
                        )
                    ),
                    'color-menu-link-current' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Menu Current Item', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.main-navbar .navbar-wpz > .current-menu-item > a, .main-navbar .navbar-wpz > .current_page_item > a',
                            'rule' => 'color'
                        )
                    ),
                    'color-search-icon' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Search Icon Color', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.sb-search .sb-icon-search',
                            'rule' => 'color'
                        )
                    ),
                    'color-search-icon-background-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Search Icon Background on Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.sb-search .sb-icon-search:hover, .sb-search .sb-search-input',
                            'rule' => 'background'
                        )
                    ),
                    'color-search-icon-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#ffffff'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Search Icon Color on Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.sb-search .sb-icon-search:hover, .sb-search .sb-search-input, .sb-search.sb-search-open .sb-icon-search:before',
                            'rule' => 'color'
                        )
                    ),
                    'color-menu-hamburger' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Hamburger Icon Color', 'wpzoom'),
                        ),
                        'style' => array(
                            array(
                            'selector' => '.slicknav_menu .slicknav_menutxt',
                            'rule' => 'color'
                            ),
                            array(
                            'selector' => '.slicknav_menu .slicknav_icon-bar',
                            'rule' => 'background-color'
                            ),
                        )
                    )
                )
            ),
            'color-slider' => array(
                'panel' => 'color-scheme',
                'title' => __('Featured Slider', 'wpzoom'),
                'options' => array(
                    'color-slider-post-title' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => ''
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Title', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slide-style-1 .slides li h3, .slide-style-1 .slides li h3 a, .slide-style-2 .slides li h3, .slide-style-2 .slides li h3 a, .slide-style-3 .slides li h3, .slide-style-3 .slides li h3 a',
                            'rule' => 'color',
                            'media' => $media_viewport
                        )
                    ),
                    'color-slider-post-title-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => ''
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Title Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slide-style-1 .slides li h3 a:hover, .slide-style-2 .slides li h3 a:hover, .slide-style-3 .slides li h3 a:hover',
                            'rule' => 'color',
                            'media' => $media_viewport
                        )
                    ),
                    'color-slider-post-cat' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => ''
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Category Link', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slides li .cat-links a',
                            'rule' => 'color'
                        )
                    ),
                    'color-slider-post-cat-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => ''
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Category Link Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slides li .cat-links a:hover',
                            'rule' => 'color'
                        )
                    ),
                    'color-slider-post-meta' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => ''
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Meta', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slides li .entry-meta',
                            'rule' => 'color'
                        )
                    ),
                    'color-slider-post-meta-link' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => ''
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Meta Link', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slides li .entry-meta a',
                            'rule' => 'color'
                        )
                    ),
                    'color-slider-post-meta-link-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => ''
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Meta Link Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slides li .entry-meta a:hover',
                            'rule' => 'color'
                        )
                    ),
                    'color-slider-button-color' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Button Text', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slide-style-1 .slides .slide_button a, .slide-style-2 .slides .slide_button a, .slide-style-3 .slides .slide_button a',
                            'rule' => 'color'
                        )
                    ),
                    'color-slider-button-background' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#fff'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Button Background', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slide-style-1 .slides .slide_button a, .slide-style-2 .slides .slide_button a, .slide-style-3 .slides .slide_button a',
                            'rule' => 'background'
                        )
                    ),
                    'color-slider-button-color-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#fff'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Button Text Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slide-style-1 .slides .slide_button a:hover, .slide-style-2 .slides .slide_button a:hover, .slide-style-3 .slides .slide_button a:hover',
                            'rule' => 'color'
                        )
                    ),
                    'color-slider-button-background-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Button Background Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.slide-style-1 .slides .slide_button a:hover, .slide-style-2 .slides .slide_button a:hover, .slide-style-3 .slides .slide_button a:hover',
                            'rule' => 'background'
                        )
                    ),

                )
            ),
            'color-posts' => array(
                'panel' => 'color-scheme',
                'title' => __('Recent Posts', 'wpzoom'),
                'options' => array(
                    'color-post-title' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Title', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.entry-title a',
                            'rule' => 'color'
                        )
                    ),
                    'color-post-title-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Title Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.entry-title a:hover',
                            'rule' => 'color'
                        )
                    ),
                    'color-post-cat' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Category', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.cat-links a',
                            'rule' => 'color'
                        )
                    ),
                    'color-post-cat-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Category Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.cat-links a:hover',
                            'rule' => 'color'
                        )
                    ),
                    'color-post-meta' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#676c71'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Meta', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.entry-meta',
                            'rule' => 'color'
                        )
                    ),
                    'color-post-meta-link' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Meta Link', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.entry-meta a',
                            'rule' => 'color'
                        )
                    ),
                    'color-post-meta-link-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Meta Link Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.entry-meta a:hover',
                            'rule' => 'color'
                        )
                    ),
                    'color-post-button-color' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Read More Link', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.readmore_button a',
                            'rule' => 'color'
                        )
                    ),
                    'color-post-button-color-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Read More Link Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.readmore_button a:hover, .readmore_button a:active',
                            'rule' => 'color'
                        )
                    )
                )
            ),
            'color-navigation' => array(
                'panel' => 'color-scheme',
                'title' => __('Page Navigation', 'wpzoom'),
                'options' => array(
                    'color-infinite-button' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Jetpack Infinite Scroll Button', 'wpzoom'),
                            'description' => __('If you have the Infinite Scroll feature enabled, you can change here the color of the "Older Posts" button. You can find more instructions in <a href="https://www.wpzoom.com/documentation/tempo/#infinite" target="_blank">Documentation</a>', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.infinite-scroll .inner-wrap #infinite-handle span',
                            'rule' => 'background'
                        )
                    ),

                    'color-infinite-button-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Jetpack Infinite Scroll Button Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.infinite-scroll .inner-wrap #infinite-handle span:hover',
                            'rule' => 'background'
                        )
                    ),
                )
            ),
            'color-single' => array(
                'panel' => 'color-scheme',
                'title' => __('Individual Posts and Pages', 'wpzoom'),
                'options' => array(
                    'color-single-title' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post/Page Title', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.page h1.entry-title, .single h1.entry-title',
                            'rule' => 'color'
                        )
                    ),
                    'color-single-meta' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#676c71'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Meta', 'wpzoom'),
                        ),
                        'style' => array(
                            'id' => 'color-single-meta',
                            'selector' => '.single .entry-meta',
                            'rule' => 'color'
                        )
                    ),
                    'color-single-meta-link' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Meta Link', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.single .entry-meta a',
                            'rule' => 'color'
                        )
                    ),
                    'color-single-meta-link-hover' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post Meta Link Hover', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.single .entry-meta a:hover',
                            'rule' => 'color'
                        )
                    ),
                    'color-single-content' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#444444'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Post/Page Text Color', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.entry-content',
                            'rule' => 'color'
                        )
                    ),
                    'color-single-link' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#0700ce'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Links Color in Posts', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.entry-content a',
                            'rule' => 'color'
                        )
                    ),

                )
            ),
            'color-widgets' => array(
                'panel' => 'color-scheme',
                'title' => __('Widgets', 'wpzoom'),
                'options' => array(
                    'color-widget-title' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#000'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Widget Title', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.section-title, .widget h3.title',
                            'rule' => 'color'
                        )
                    ),
                )
            ),
            'color-footer' => array(
                'panel' => 'color-scheme',
                'title' => __('Footer', 'wpzoom'),
                'options' => array(
                    'footer-background-color' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#161719'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Footer Background Color', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.site-footer',
                            'rule' => 'background-color'
                        )
                    ),
                    'footer-widget-color' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#fff'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Footer Widget Title Color', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.site-footer .widget .title',
                            'rule' => 'color'
                        )
                    ),
                    'footer-text-color' => array(
                        'setting' => array(
                            'sanitize_callback' => 'maybe_hash_hex_color',
                            'transport' => 'postMessage',
                            'default' => '#87888a'
                        ),
                        'control' => array(
                            'control_type' => 'WP_Customize_Color_Control',
                            'label' => __('Footer Text Color', 'wpzoom'),
                        ),
                        'style' => array(
                            'selector' => '.site-footer',
                            'rule' => 'color'
                        )
                    ),

                )
            ),
            /**
             *  Typography
             */
            'body-typography' => array(
                'panel' => 'typography',
                'title' => __('Body', 'wpzoom'),
                'options' => array(
                    'body' => array(
                        'type' => 'typography',
                        'selector' => 'body',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-family-sync-all' => false,
                            'font-size' => 16,
                            'font-weight' => 'normal',
                            'letter-spacing' => 0,
                            'text-transform' => 'none',
                            'font-style' => 'normal',
                            'font-subset' => 'latin',
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 16,
                            'tablet' => 16,
                            'mobile' => 16
                        )
                    )
                )
            ),
            'title-typography' => array(
                'panel' => 'typography',
                'title' => __('Site Title', 'wpzoom'),
                'options' => array(
                    'title' => array(
                        'type' => 'typography',
                        'selector' => '.navbar-brand-wpz h1 a',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 55,
                            'font-weight' => 'bold',
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 55,
                            'tablet' => 34,
                            'mobile' => 26
                        )
                    )
                )
            ),
            'description-typography' => array(
                'panel' => 'typography',
                'title' => __('Site Description', 'wpzoom'),
                'options' => array(
                    'description' => array(
                        'type' => 'typography',
                        'selector' => '.navbar-brand-wpz .tagline',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 18,
                            'font-weight' => 'normal',
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 18,
                            'tablet' => 14,
                            'mobile' => 14
                        )
                    )
                )
            ),
            'topmenu-typography' => array(
                'panel' => 'typography',
                'title' => __('Top Menu Links', 'wpzoom'),
                'options' => array(
                    'topmenu' => array(
                        'type' => 'typography',
                        'selector' => '.top-navbar .navbar-wpz a',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 16,
                            'font-weight' => 'normal',
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        )
                    )
                )
            ),
            'mainmenu-typography' => array(
                'panel' => 'typography',
                'title' => __('Main Menu Links', 'wpzoom'),
                'options' => array(
                    'mainmenu' => array(
                        'type' => 'typography',
                        'selector' => '.main-navbar a',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 18,
                            'font-weight' => 'normal',
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        )
                    )
                )
            ),

            'font-nav-mobile' => array(
                'panel' => 'typography',
                'title' => __('Main Menu Links (Mobile)', 'wpzoom'),
                'options' => array(
                    'mainmenu-mobile' => array(
                        'type' => 'typography',
                        'selector' => '.slicknav_nav a',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 18,
                            'font-weight' => 'normal',
                            'letter-spacing' => 0,
                            'text-transform' => 'none',
                            'font-style' => 'normal'
                        )
                    )
                )
            ),

            'slider-title-typography' => array(
                'panel' => 'typography',
                'title' => __('Homepage Slider Title', 'wpzoom'),
                'options' => array(
                    'slider-title' => array(
                        'type' => 'typography',
                        'selector' => '.slides li h3, .slide-style-3 .slides li h3, .slide-style-3 .slides li h3 a',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 40,
                            'letter-spacing' => 0,
                            'font-weight' => 'bold',
                            'text-transform' => 'none',
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 40,
                            'tablet' => 36,
                            'mobile' => 22
                        )
                    )
                )
            ),
            'slider-text-typography' => array(
                'panel' => 'typography',
                'title' => __('Homepage Slider Text', 'wpzoom'),
                'options' => array(
                    'slider-text' => array(
                        'type' => 'typography',
                        'selector' => '.slides li .slide-header p',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 18,
                            'font-weight' => 'normal',
                            'text-transform' => 'none',
                            'font-style' => 'normal'
                        )
                    )
                )
            ),
            'slider-button-typography' => array(
                'panel' => 'typography',
                'title' => __('Homepage Slider Button Text', 'wpzoom'),
                'options' => array(
                    'slider-button' => array(
                        'type' => 'typography',
                        'selector' => '.slides .slide_button',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 16,
                            'font-weight' => 600,
                            'text-transform' => 'none',
                            'letter-spacing' => 1,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 16,
                            'tablet' => 14,
                            'mobile' => 14
                        )
                    )
                )
            ),
            'home-widget-full-typography' => array(
                'panel' => 'typography',
                'title' => __('Homepage Widget Title (Full-width)', 'wpzoom'),
                'options' => array(
                    'home-widget-full' => array(
                        'type' => 'typography',
                        'selector' => '.homepage_full .widget h3.title',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 26,
                            'font-weight' => 600,
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 26,
                            'tablet' => 26,
                            'mobile' => 20
                        )
                    )
                )
            ),
            'home-widget-typography' => array(
                'panel' => 'typography',
                'title' => __('Homepage Widget Title (1/3 Column)', 'wpzoom'),
                'options' => array(
                    'home-widget' => array(
                        'type' => 'typography',
                        'selector' => '.homepage_widgets .home_column h3.title',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 22,
                            'font-weight' => 600,
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 22,
                            'tablet' => 22,
                            'mobile' => 20
                        )
                    )
                )
            ),
            'page-title-typography' => array(
                'panel' => 'typography',
                'title' => __('Single Page Title', 'wpzoom'),
                'options' => array(
                    'page-title' => array(
                        'type' => 'typography',
                        'selector' => '.page h1.entry-title',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 40,
                            'font-weight' => 'bold',
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 40,
                            'tablet' => 30,
                            'mobile' => 28
                        )
                    )
                )
            ),
            'post-title-typography' => array(
                'panel' => 'typography',
                'title' => __('Single Post Title', 'wpzoom'),
                'options' => array(
                    'post-title' => array(
                        'type' => 'typography',
                        'selector' => '.single h1.entry-title',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 40,
                            'font-weight' => 'bold',
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 40,
                            'tablet' => 30,
                            'mobile' => 28
                        )
                    )
                )
            ),
            'archive-title-typography' => array(
                'panel' => 'typography',
                'title' => __('Archives Page Title', 'wpzoom'),
                'options' => array(
                    'archive-title' => array(
                        'type' => 'typography',
                        'selector' => '.section-title',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 28,
                            'font-weight' => 'bold',
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 28,
                            'tablet' => 24,
                            'mobile' => 22
                        )
                    )
                )
            ),
            'blog-title-typography' => array(
                'panel' => 'typography',
                'title' => __('Blog Posts Title', 'wpzoom'),
                'options' => array(
                    'blog-title' => array(
                        'type' => 'typography',
                        'selector' => '.entry-title, .recent-posts.blog-view .post .entry-title',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 26,
                            'font-weight' => 600,
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 26,
                            'tablet' => 24,
                            'mobile' => 22
                        )
                    )
                )
            ),
            'more-button-typography' => array(
                'panel' => 'typography',
                'title' => __('Continue Reading Link', 'wpzoom'),
                'options' => array(
                    'more-button' => array(
                        'type' => 'typography',
                        'selector' => '.readmore_button',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 16,
                            'font-weight' => 600,
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 16,
                            'tablet' => 16,
                            'mobile' => 16
                        )
                    )
                )
            ),
            'widget-title-typography' => array(
                'panel' => 'typography',
                'title' => __('Sidebar Widget Title', 'wpzoom'),
                'options' => array(
                    'widget-title' => array(
                        'type' => 'typography',
                        'selector' => '.widget .title',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 20,
                            'font-weight' => 600,
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 20,
                            'tablet' => 20,
                            'mobile' => 20
                        )
                    )
                )
            ),
            'footer-widget-title-typography' => array(
                'panel' => 'typography',
                'title' => __('Footer Widget Title', 'wpzoom'),
                'options' => array(
                    'footer-widget-title' => array(
                        'type' => 'typography',
                        'selector' => '.site-footer .widget .title',
                        'rules' => array(
                            'font-family' => 'Libre Franklin',
                            'font-size' => 20,
                            'font-weight' => 600,
                            'text-transform' => 'none',
                            'letter-spacing' => 0,
                            'font-style' => 'normal'
                        ),
                        'font-size-responsive' => array(
                            'desktop' => 20,
                            'tablet' => 20,
                            'mobile' => 20
                        )
                    )
                )
            ),
            'footer-area' => array(
                'title' => __('Footer', 'wpzoom'),
                'options' => array(
                    'blogcopyright' => array(
                        'setting' => array(
                            'sanitize_callback' => 'sanitize_text_field',
                            'default' => get_option('blogcopyright', sprintf( __( 'Copyright &copy; %1$s %2$s', 'wpzoom' ), date( 'Y' ), get_bloginfo( 'name' ) )),
                            'transport' => 'postMessage',
                            'type' => 'option'
                        ),
                        'control' => array(
                            'label' => __('Footer Text', 'wpzoom'),
                            'type' => 'text',
                            'priority' => 10
                        ),
                        'partial' => array(
                            'selector' => '.site-info .copyright',
                            'container_inclusive' => false,
                            'render_callback' => 'zoom_customizer_partial_blogcopyright'
                        )

                    )
                )
            )
        );

        zoom_customizer_normalize_options($data);
    }


    return $data;
}

add_filter('wpzoom_customizer_data', 'presence_customizer_data');