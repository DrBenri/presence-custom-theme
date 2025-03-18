<?php
/**
 * Onboarding flow for themes.
 *
 * @package WPZOOM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * WPZOOM_Onboarding Class
 *
 * @package WPZOOM
 * @subpackage Onboarding
 */
class WPZOOM_Onboarding {
    /**
     * WPZOOM TGMPA Assistance instance.
     *
     * @var object
     */
    protected $tgmpa_assistance;

    /**
     * WPZOOM_Onboarding class instance.
     *
     * @access private
     * @var object
     */
    private static $instance;

    /**
     * Holds configurable array of strings.
     *
     * Default values are added in the constructor.
     *
     * @since 2.0.0
     *
     * @var array
     */
    public $strings = array();

    /**
     * WP_Theme object.
     *
     * @var object
     */
    public $theme;

    /**
     * Slug name of the current theme.
     *
     * @var string
     */
    public $theme_slug;

    /**
     * HTML classname prefix.
     *
     * @var string
     */
    protected $classname_prefix = 'wpz-onboard_';

    /**
     * Initiator.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        // Include class utils.
        require_once WPZOOM_INC . '/components/onboarding/class-wpzoom-onboarding-utils.php';

        //Clean importer cache
        add_action( 'switch_theme', array( $this, 'clear_template_data_cache' ) );
        add_action( 'after_switch_theme', array( $this, 'clear_template_data_cache' ) );

        // Onboarding action init.
        add_action( 'wpzoom_onboarding_init', array( $this, 'set_selected_design' ) );

        $this->init();
        $this->setup_demo_import();

        // References TGMPA Assistance constructor.
        $this->init_tgmpa_instance();

        // Add extra submenu options to the admin panel's.
        add_action( 'admin_menu', array( $this, 'admin_page' ) );

        // Include onboarding scripts/styles.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Sort plugins filter.
        add_filter( 'wpz_onboarding_sort_required_plugins', array( $this, 'sort_plugins' ) );

        // AJAX actions.
        add_action( 'wp_ajax_wpzoom_get_all_plugins', array( $this, 'get_installed_plugins_ajax' ) );
        add_action( 'wp_ajax_wpzoom_required_plugins', array( $this, 'plugin_install_ajax' ) );
        add_action( 'wp_ajax_wpzoom_get_theme_design', array( $this, 'get_theme_design_ajax' ) );

        // Create option with some value, disable the wpform redirect activation.
        update_option( 'wpforms_activation_redirect', true );

        // Onboarding Tour.
        if ( zoom_current_theme_supports( 'wpz-onboarding', 'onboarding-tour' ) ) {
            require_once WPZOOM_INC . '/components/onboarding/class-wpzoom-onboarding-tour.php';
        }

        add_filter( 'wpzoom_demo_import_advanced_settings', array( $this, 'demo_support_elementor' ) );
    }

    /**
     * Adds a menu item for the theme license under the appearance menu.
     *
     * @since 2.0.0
     */
    public function admin_page() {
        add_submenu_page(
            'wpzoom_options',
            __( 'Dashboard', 'wpzoom' ),
            __( 'Dashboard', 'wpzoom' ),
            'manage_options',
            'wpzoom_license',
            array( $this, 'display_admin_page' ),
            0
        );

        // Add Demo Importer submenu
        add_submenu_page(
            'wpzoom_options',
            __( 'Demo Importer', 'wpzoom' ),
            __( 'Demo Importer', 'wpzoom' ),
            'manage_options',
            'wpzoom_demo_importer',
            array( $this, 'redirect_to_demo_importer' ),
            1
        );

        // Add License Key submenu
        add_submenu_page(
            'wpzoom_options',
            __( 'License Key', 'wpzoom' ),
            __( 'License Key', 'wpzoom' ),
            'manage_options',
            'wpzoom_license_key',
            array( $this, 'redirect_to_license_key' ),
            2
        );
    }

    /**
     * Initialise the class configurations.
     *
     * @return void
     */
    protected function init() {
        // Template name.
        $this->theme_slug = get_template();

        // Theme object.
        $this->theme = wp_get_theme( $this->theme_slug );

        // Load class strings.
        $this->strings = array(
            'label_dashboard'                 => __( 'Home', 'wpzoom' ),
            'label_demo_importer'             => __( 'Demo Importer', 'wpzoom' ),
            'label_license'                   => __( 'License Key', 'wpzoom' ),
            'label_wpzoom'                    => __( 'WPZOOM', 'wpzoom' ),
            'label_themes'                    => __( 'Themes', 'wpzoom' ),
            'label_plugins'                   => __( 'Plugins', 'wpzoom' ),
            'label_blog'                      => __( 'Blog', 'wpzoom' ),
            'label_support'                   => __( 'Support', 'wpzoom' ),
            /* translators: %s theme name. */
            'label_content_title'             => __( 'Demo Importer', 'wpzoom'),
            'label_content_intro'             => __( 'Importing demo data (posts, pages, images, settings, etc.) is the fastest way to set up your theme, letting you edit instead of starting from scratch!', 'wpzoom' ),
            'label_license_title'             => __( 'License Key', 'wpzoom' ),
            /* translators: %s license account url. */
            'label_license_intro'             => sprintf( __( 'Enter your license key to receive theme updates and unlock all features. You can find your license in <a href="%s" target="_blank">WPZOOM Members Area → Licenses</a>.', 'wpzoom' ), esc_url( 'https://www.wpzoom.com/account/licenses/' ) ),
            'label_license_field_name'        => __( 'License Key', 'wpzoom' ),
            'label_license_note'              => __( 'Enter your license key for this theme to enable theme updates and unlock all features.', 'wpzoom' ),
            'label_license_submit'            => __( 'Save &amp; Activate', 'wpzoom' ),
            'label_steps_title1'              => __( 'Install plugins', 'wpzoom' ),
            'label_steps_intro1'              => __( 'To enable this demo\'s features, you must install and activate the plugins listed below. The ones labeled as Recommended can be skipped.', 'wpzoom' ),
            'label_steps_title2'              => __( 'Choose a demo', 'wpzoom' ),
            'label_steps_intro2'              => __( 'Click on the demo you want to import.', 'wpzoom' ),
            'label_steps_title3'              => __( 'Import demo', 'wpzoom' ),
            /* translators: %s admin import page. */
            'label_steps_intro3'              => __( 'Click on the button below to load the demo content. This is useful for seeing how the theme will look like when filled with content.', 'wpzoom' ),
            'label_docs_title'                => __( 'Documentation', 'wpzoom' ),
            'label_docs_content'              => __( 'Documentation is the place where you&rsquo;ll find the information needed to setup the theme quickly, and other details about theme-specific features.', 'wpzoom' ),
            'label_docs_button'               => __( 'View documentation &rarr;', 'wpzoom' ),
            'label_assist_title'              => __( 'Need assistance?', 'wpzoom' ),
            'label_assist_content'            => __( 'Need help setting up your theme or have a question? Get in touch with our Support Team. We&rsquo;d love the opportunity to help you.', 'wpzoom' ),
            'label_assist_button'             => __( 'Open Support Desk', 'wpzoom' ),
            'label_newwp_title'               => __( 'New to WordPress?', 'wpzoom' ),
            'label_newwp_content'             => __( '20+ video tutorials that cover everything you need to know to get started using WordPress.', 'wpzoom' ),
            'label_newwp_button'              => __( 'Learn WordPress today', 'wpzoom' ),
            'label_follow_title'              => __( 'Follow WPZOOM', 'wpzoom' ),
            'label_follow_content'            => __( 'Follow us on social media for news and updates on all your theme needs.', 'wpzoom' ),
            'label_follow_button1'            => __( 'Twitter', 'wpzoom' ),
            'label_follow_button2'            => __( 'Facebook', 'wpzoom' ),
            'label_follow_button3'            => __( 'Instagram', 'wpzoom' ),
            'label_go_back'                   => __( 'Go back', 'wpzoom' ),
            'label_page_preview'              => __( 'Page preview', 'wpzoom' ),
            'label_live_preview'              => __( 'Live preview', 'wpzoom' ),
            'label_template'                  => __( 'Selected template:', 'wpzoom' ),
            'label_import_button'             => __( 'Import demo content', 'wpzoom' ),
            'label_view_pages'                => __( 'View pages', 'wpzoom' ),
            'label_selected'                  => __( 'Selected', 'wpzoom' ),
            /* translators: 1: plugin name(s). */
            'notice_can_install_required'     => _n_noop(
                'In order to import the demo content correctly, please install the following required plugin: %1$s',
                'In order to import the demo content correctly, please install the following required plugins: %1$s',
                'wpzoom'
            ),
            /* translators: 1: plugin name(s). */
            'notice_can_install_recommended'  => _n_noop(
                'This theme recommends the following plugin: %1$s',
                'This theme recommends the following plugins: %1$s',
                'wpzoom'
            ),
            /* translators: 1: plugin name(s). */
            'notice_ask_to_update'            => _n_noop(
                'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s',
                'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s',
                'wpzoom'
            ),
            /* translators: 1: plugin name(s). */
            'notice_ask_to_update_maybe'      => _n_noop(
                'There is an update available for: %1$s',
                'There are updates available for the following plugins: %1$s',
                'wpzoom'
            ),
            /* translators: 1: plugin name(s). */
            'notice_can_activate_required'    => _n_noop(
                'The following required plugin is currently inactive: %1$s',
                'The following required plugins are currently inactive: %1$s',
                'wpzoom'
            ),
            /* translators: 1: plugin name(s). */
            'notice_can_activate_recommended' => _n_noop(
                'The following recommended plugin is currently inactive: %1$s',
                'The following recommended plugins are currently inactive: %1$s',
                'wpzoom'
            ),
            'notice_skip_recommended_link'    => _n_noop(
                'Skip recommended notice',
                'Skip recommended notices',
                'wpzoom'
            ),
            'title_install_required'          => _n_noop(
                'Missing required plugin',
                'Missing required plugins',
                'wpzoom'
            ),
            'title_activate_required'         => _n_noop(
                'Inactive required plugin',
                'Inactive required plugins',
                'wpzoom'
            ),
            'title_install_recommended'       => _n_noop(
                'Install recommended plugin',
                'Install recommended plugins',
                'wpzoom'
            ),
            'title_activate_recommended'      => _n_noop(
                'Activate recommended plugin',
                'Activate recommended plugins',
                'wpzoom'
            ),
            'title_update_maybe'              => _n_noop(
                'Update plugin',
                'Update plugins',
                'wpzoom'
            ),
            'title_update'                    => _n_noop(
                'Update required for plugin',
                'Update required for plugins',
                'wpzoom'
            ),
            'localize_strings'                => array(
                'submit_label_normal'      => __( 'Install & activate', 'wpzoom' ),
                'submit_label_all'         => __( 'Install & activate all', 'wpzoom' ),
                'submit_label_selected'    => __( 'Install & activate selected', 'wpzoom' ),
                'submit_label_loading'     => __( 'Installing...', 'wpzoom' ),
                'preparing_preview'        => __( 'Preparing preview', 'wpzoom' ),
                'preparing_demo_content'   => __( 'Preparing demo content...', 'wpzoom' ),
                'load_demo_content'        => __( 'Load demo content', 'wpzoom' ),
                'import_demo_content'      => __( 'Import demo content', 'wpzoom' ),
                'processing_template_data' => __( 'Processing demo content', 'wpzoom' ),
                'selected'                 => __( 'Selected', 'wpzoom' ),
                'imported'                 => __( 'Imported', 'wpzoom' ),
                'select_template'          => __( 'Select', 'wpzoom' ),
                'check_label_all'          => __( 'Select all', 'wpzoom' ),
                'check_label_none'         => __( 'Unselect all', 'wpzoom' ),
                'already_installed'        => __( 'Installed', 'wpzoom' ),
                'install_failed'           => __( 'Plugin installation failed! The server returned: ', 'wpzoom' ),
                'active'                   => __( 'Active', 'wpzoom' ),
                'something_wrong'          => __( 'Something went wrong when trying to install & activate plugins! Please, try again later.', 'wpzoom' ),
                'saving_license'           => __( 'Saving & activating license key...', 'wpzoom' ),
                'deactivating_license'     => __( 'Deactivating license key...', 'wpzoom' ),
                'deactivate_license'       => __( 'Deactivate', 'wpzoom' ),
                'save_activate_license'    => __( 'Save & Activate', 'wpzoom' ),
                'save_activate_license'    => __( 'Save & Activate', 'wpzoom' ),
                'something_wrong_license'  => __( 'Something went wrong when trying to save & activate license key! Please, try again later.', 'wpzoom' ),
            ),
        );

        // Maybe we don't need the activation redirect for Recipe Card Blocks plugin.
        $plugins = WPZOOM_TGMPA_Assistance::get_instance()->get_plugins();
        if ( ! empty( $plugins ) ) {
            foreach ( $plugins as $plugin ) {
                if ( 'recipe-card-blocks-by-wpzoom' === $plugin['slug'] || 'recipe-card-blocks-by-wpzoom-pro' === $plugin['slug'] ) {
                    delete_option( 'wpzoom_rcb_do_activation_redirect' );
                    break;
                }
            }
        }

        /**
         * Register configuration strings, current theme data, class name prefix ...
         *
         * @since 2.0.0
         */
        do_action( 'wpzoom_onboarding_init' );
    }

    /**
     * Initialise the class WPZOOM_TGMPA_Assistance.
     *
     * @return void
     */
    public function init_tgmpa_instance() {
        $this->tgmpa_assistance = WPZOOM_TGMPA_Assistance::get_instance()->tgmpa_instance();
    }

    /**
     * Enqueue the admin styles/scripts
     *
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_scripts( $hook_suffix ) {
        if ( 'wpzoom_page_wpzoom_license' !== $hook_suffix ) {
            return;
        }

        // phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
        wp_enqueue_style( 'wpzoom-options', WPZOOM::$assetsPath . '/options.css', array( 'thickbox' ), WPZOOM::$wpzoomVersion );

        wp_enqueue_style(
            'wpzoom-theme-admin-style',
            WPZOOM::get_root_uri() . '/components/onboarding/assets/css/admin.css',
            array( 'wpzoom-options' ),
            WPZOOM::$wpzoomVersion
        );

        wp_enqueue_script(
            'wpzoom-onboarding-admin-script',
            WPZOOM::get_root_uri() . '/components/onboarding/assets/js/admin.js',
            array( 'jquery' ),
            WPZOOM::$wpzoomVersion,
            true
        );
        // phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

        wp_localize_script(
            'wpzoom-onboarding-admin-script',
            'wpzoomOnboarding',
            array(
                'compatibilities' => $this->get_compatibilities(),
                'labels'          => $this->strings['localize_strings'],
            )
        );

        add_action(
            'admin_print_scripts',
            function() {
                echo '<script type="text/javascript">var wpzoom_ajax_url = \'' . esc_url( admin_url( 'admin-ajax.php' ) ) . '\';</script>';
            }
        );
    }

    /**
     * Load WPZOOM Demo Importer class.
     */
    public function setup_demo_import() {
        if ( zoom_current_theme_supports( 'wpz-onboarding', 'demo-importer' ) ) {
            require_once WPZOOM_INC . '/components/demo-importer/class-wpzoom-elementor-pages.php';
            require_once WPZOOM_INC . '/components/demo-importer/class-wpzoom-demo-import.php';
        }
    }

    /**
     * Outputs the markup for the admin screen.
     *
     * @return void
     */
    public function display_admin_page() {
        $license        = WPZOOM_Onboarding_License::get_instance();
        $class_prefix   = $this->classname_prefix;
        $theme_name     = $this->theme->get( 'Name' );
        $theme_icon     = '';
        $theme_slug = get_template();
        if ( $theme_slug === 'inspiro' ) {
            $theme_icon     = '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32ZM13.4982 7.17576C13.4982 5.78333 14.627 4.65455 16.0194 4.65455H18.2885C18.4277 4.65455 18.5406 4.76742 18.5406 4.90667V9.39303C18.5406 9.47618 18.4962 9.55301 18.4242 9.59458L13.8473 12.2371C13.6921 12.3267 13.4982 12.2147 13.4982 12.0355V7.17576ZM13.6145 16.7383L18.1915 14.0958C18.3467 14.0062 18.5406 14.1182 18.5406 14.2974V24.8242C18.5406 26.2167 17.4118 27.3455 16.0194 27.3455H13.7503C13.6111 27.3455 13.4982 27.2326 13.4982 27.0933V16.9399C13.4982 16.8567 13.5425 16.7799 13.6145 16.7383Z" fill="#242628"/></svg>';
            };
        $theme_version  = $this->theme->get( 'Version' );
        $step1_content  = $this->step_1_content();
        $step_builders  = $this->step_builders();
        $step2_content  = $this->step_2_content();
        $step3_content  = $this->step_3_content();
        $post_url       = esc_url( admin_url( 'admin-post.php' ) );
        $license_key    = $license->get_license_key();
        $license_status = $license->get_license_status();
        $license_nonce  = wp_create_nonce( 'wpzoom_set_license' );
        /* translators: %s WPZOOM framework version */
        $framework_version = sprintf( __( 'WPZOOM Framework %s', 'wpzoom' ), WPZOOM::$wpzoomVersion ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

        extract( $this->strings ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

        $license_status_badge = '';
        // Checks license status to display under license key.
        if ( ! $license_key ) {
            $label_license_note = $license->strings['enter-key'];
        } else {
            if ( ! get_transient( $this->theme_slug . '_license_message' ) ) {
                $license->check_license( $license_key );
            }
            $label_license_note = get_transient( $this->theme_slug . '_license_message' );
        }

        $label_badge               = '';
        $license_deactivate_button = '';
        $license_activate_button   = sprintf(
            '<input type="submit" value="%s" class="%s"/>',
            $label_license_submit,
            "{$class_prefix}content-main-license-submit button button-primary"
        );

        if ( 'valid' === $license_status ) {
            // Display "Deactivate" button.
            $license_deactivate_button = sprintf(
                '<input type="submit" value="%s" class="%s" data-deactivate-action="wpzoom_deactivate_license"/>',
                $this->strings['localize_strings']['deactivate_license'],
                "{$class_prefix}content-main-license-submit button button-secondary"
            );

            // Disable "Save & Activate" button.
            $license_activate_button = sprintf(
                '<input type="submit" value="%s" class="%s" disabled="disabled"/>',
                $label_license_submit,
                "{$class_prefix}content-main-license-submit button button-primary"
            );

            // Add label badge Active.
            $label_badge = '<span class="label-badge" data-license-status="' . esc_attr( $license_status ) . '">' . esc_html_x( 'Active', 'License status', 'wpzoom' ) . '</span>';
        } elseif ( ! empty( $license_key ) ) {
            $license_status_badge = '<span class="license-status-' . esc_attr( $license_status ) . '-badge"></span>';
            // Add label badge Inactive.
            $label_badge = '<span class="label-badge" data-license-status="' . esc_attr( $license_status ) . '">' . esc_html_x( 'Inactive', 'License status', 'wpzoom' ) . '</span>';
        }

        // Check if a specific step's cookie exists
        $state = '';
        $step_index = 0;
        if ( isset( $_COOKIE['wpzoom_step_' . $step_index] ) ) {
            $state = $_COOKIE['wpzoom_step_' . $step_index];
        }

          $toggle_state = $button_active = '';
          $panel_active = ' active';
          if( 'closed' == $state ) {
              $toggle_state  =  ' style="display:none;"';
              $panel_active  = '';
              $button_active = ' active';
          }

        $filter_designs = '';
        $is_block_design = self::is_block_design();
        if( $is_block_design ) {
            $filter_designs = <<<FILTER_DESIGNS
            <div class="{$class_prefix}filter-designs"{$toggle_state}>
                <a class="show-all active" data-filter="all" href="#">All Demos</a>
                <a class="show-elementor" data-filter="elementor" href="#"><svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0 8.99998C0 13.9703 4.0297 18 8.99997 18C13.9703 18 18 13.9703 18 8.99998C18 4.0297 13.9703 0 8.99997 0C4.0297 0 0 4.0297 0 8.99998ZM6.74964 5.2499H5.24987V12.7501H6.74964V5.2499ZM8.24943 5.2499H12.7488V6.74967H8.24943V5.2499ZM12.7488 8.24944H8.24943V9.74922H12.7488V8.24944ZM8.24943 11.2503H12.7488V12.7501H8.24943V11.2503Z" /></svg> Elementor</a>
                <a class="show-blocks" data-filter="block" href="#"><svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 0C4.041 0 0 4.041 0 9C0 13.959 4.041 18 9 18C13.959 18 18 13.959 18 9C18 4.041 13.959 0 9 0ZM0.909 9C0.909 7.83 1.161 6.714 1.611 5.706L5.472 16.281C2.772 14.967 0.909 12.204 0.909 9ZM9 17.091C8.208 17.091 7.443 16.974 6.714 16.758L9.144 9.702L11.628 16.515C11.646 16.551 11.664 16.596 11.682 16.623C10.845 16.929 9.945 17.091 9 17.091ZM10.116 5.202C10.602 5.175 11.043 5.121 11.043 5.121C11.475 5.067 11.43 4.428 10.989 4.455C10.989 4.455 9.675 4.554 8.829 4.554C8.037 4.554 6.696 4.455 6.696 4.455C6.264 4.437 6.21 5.103 6.651 5.13C6.651 5.13 7.065 5.184 7.497 5.211L8.757 8.667L6.984 13.977L4.041 5.202C4.527 5.184 4.968 5.13 4.968 5.13C5.4 5.076 5.355 4.437 4.914 4.464C4.914 4.464 3.6 4.563 2.754 4.563C2.601 4.563 2.421 4.563 2.232 4.554C3.69 2.358 6.174 0.909 9 0.909C11.106 0.909 13.023 1.71 14.463 3.033C14.427 3.033 14.391 3.024 14.355 3.024C13.563 3.024 12.996 3.717 12.996 4.464C12.996 5.13 13.383 5.697 13.788 6.363C14.094 6.903 14.454 7.596 14.454 8.595C14.454 9.288 14.184 10.089 13.842 11.214L13.032 13.914L10.116 5.202ZM16.101 5.121C17.1119 6.97107 17.3606 9.14271 16.794 11.1734C16.2275 13.2041 14.8906 14.9334 13.068 15.993L15.543 8.847C16.002 7.695 16.155 6.768 16.155 5.949C16.155 5.652 16.137 5.373 16.101 5.121Z"/></svg> Block Editor (Gutenberg)</a>
            </div>
            FILTER_DESIGNS;
        }

        $select_builder = '';
        $show_builder_select = empty( $step_builders ) ? 'style="display:none;"' : '';

        $select_builder = <<<SELECT_BUILDER
            <li id="step-choose-builder" class="{$class_prefix}content-main-step step-1a step-choose-builder" {$show_builder_select}>
                <h4 class="{$class_prefix}content-main-step-title">Choose a Builder</h4>
                <p class="{$class_prefix}content-main-step-intro">Select the builder you want to use to create your website.</p>
                <div class="{$class_prefix}content-main-step-content">
                    {$step_builders}
                </div>
            </li>
        SELECT_BUILDER;


        // phpcs:ignore WordPress.Security.EscapeOutput
        echo <<<HERE
            <div class="{$class_prefix}wrapper">
            <div class="{$class_prefix}header">
                <div class="{$class_prefix}title-wrapper">
                    <h1 class="{$class_prefix}title">$theme_icon $theme_name <span>$theme_version</span></h1>
                </div>

                <ul class="{$class_prefix}tabs" id="wpz-onboard_tabs">
                    <div class="wpz-onboard_tab-dashboard"> <p>DASHBOARD</p> <hr class="wpz-onboard_hr"> </div>

                    <li class="{$class_prefix}tab {$class_prefix}tab-dashboard active"><a href="#dashboard" title="$label_dashboard"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.5 18.5002V14.0002C6.5 12.6192 7.619 11.5002 9 11.5002V11.5002C10.381 11.5002 11.5 12.6192 11.5 14.0002V18.5002H17V9.91425C17 9.38425 16.789 8.87525 16.414 8.50025L9.707 1.79325C9.316 1.40225 8.683 1.40225 8.293 1.79325L1.586 8.50025C1.211 8.87525 1 9.38425 1 9.91425V18.5002H6.5Z" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg> $label_dashboard</a></li>
                    <li class="{$class_prefix}tab {$class_prefix}tab-demo-importer"><a href="#demo-importer" title="$label_demo_importer"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9H21" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M11 5.995L10.995 6L11 6.005L11.005 6L11 5.995" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M8.5 5.995L8.495 6L8.5 6.005L8.505 6L8.5 5.995" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M6 5.995L5.995 6L6 6.005L6.005 6L6 5.995" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M11 5.995L10.995 6L11 6.005L11.005 6L11 5.995" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M8.5 5.995L8.495 6L8.5 6.005L8.505 6L8.5 5.995" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M6 5.995L5.995 6L6 6.005L6.005 6L6 5.995" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M17.5 21H18C19.6569 21 21 19.6569 21 18V6C21 4.34315 19.6569 3 18 3H6C4.34315 3 3 4.34315 3 6V18C3 19.6569 4.34315 21 6 21H6.5" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M12 18L14 16" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10 16L12 18" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M12 13.5V18" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M14 21H10" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg> $label_demo_importer</a></li>
                    <li class="{$class_prefix}tab {$class_prefix}tab-license"><a href="#license" title="$label_license"><svg width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.6739 3.2469C10.5319 2.55135 10.1911 1.88827 9.65135 1.34856C8.18725 -0.116317 5.81231 -0.116058 4.34769 1.34856C2.8828 2.81346 2.8828 5.18733 4.34769 6.65222C5.81259 8.11712 8.18646 8.11712 9.65135 6.65222C10.1929 6.11072 10.5342 5.445 10.6754 4.74691L13.25 4.73629V6.60389C13.25 7.01811 13.5858 7.35389 14 7.35389C14.4142 7.35389 14.75 7.01811 14.75 6.60389V4.7301L17.25 4.71979V6.57069C17.25 6.9849 17.5858 7.32069 18 7.32069C18.4142 7.32069 18.75 6.9849 18.75 6.57069V3.96669C18.75 3.96565 18.75 3.96462 18.75 3.96358C18.7483 3.5508 18.4132 3.21669 18 3.21669M8.59047 2.409C7.71254 1.53032 6.28759 1.52999 5.40835 2.40922C4.52925 3.28833 4.52925 4.71246 5.40835 5.59156C6.28746 6.47067 7.71159 6.47067 8.59069 5.59156C9.02906 5.15319 9.24884 4.5793 9.25002 4.00504C9.25001 4.00429 9.25001 4.00354 9.25001 4.00278C9.25 4.00005 9.25 3.99732 9.25002 3.9946C9.24855 3.42072 9.02855 2.84708 8.59047 2.409Z" fill="#242628"/><path d="M17.9985 3.21669C17.9979 3.21669 17.9974 3.21669 17.9969 3.2167L10.6739 3.2469" fill="#242628"/><path fill-rule="evenodd" clip-rule="evenodd" d="M16.997 14.4433C17.5852 14.4405 18.0565 14.9172 18.0565 15.4988C18.0565 16.0813 17.5859 16.5563 17 16.5563C16.4163 16.5563 15.9435 16.0835 15.9435 15.4998C15.9435 14.917 16.4146 14.4449 16.997 14.4433ZM16.997 14.4433L16.994 14.4433L16.996 14.7248L17 14.7235V14.4433L16.997 14.4433Z" fill="#242628"/><path d="M14.0565 15.4988C14.0565 14.9172 13.5852 14.4405 12.997 14.4433L13 14.4433V14.7235L12.996 14.7248L12.994 14.4433L12.997 14.4433C12.4146 14.4449 11.9435 14.917 11.9435 15.4998C11.9435 16.0835 12.4163 16.5563 13 16.5563C13.5827 16.5563 14.0565 16.0835 14.0565 15.4988Z" fill="#242628"/><path fill-rule="evenodd" clip-rule="evenodd" d="M8.99697 14.4433C9.58515 14.4405 10.0565 14.9172 10.0565 15.4988C10.0565 16.0835 9.58271 16.5563 8.99998 16.5563C8.41625 16.5563 7.94348 16.0835 7.94348 15.4998C7.94348 14.917 8.41463 14.4449 8.99697 14.4433ZM8.99697 14.4433L8.99396 14.4433L8.99605 14.7248L8.99998 14.7235V14.4433L8.99697 14.4433Z" fill="#242628"/><path d="M6.05648 15.4988C6.05648 14.9172 5.58515 14.4405 4.99697 14.4433L4.99998 14.4433V14.7235L4.99605 14.7248L4.99396 14.4433L4.99697 14.4433C4.41463 14.4449 3.94348 14.917 3.94348 15.4998C3.94348 16.0835 4.41625 16.5563 4.99998 16.5563C5.58271 16.5563 6.05648 16.0835 6.05648 15.4988Z" fill="#242628"/><path fill-rule="evenodd" clip-rule="evenodd" d="M0.25 15.4999C0.25 13.1527 2.15279 11.2499 4.5 11.2499H17.5C19.8472 11.2499 21.75 13.1527 21.75 15.4999C21.75 17.8471 19.8472 19.7499 17.5 19.7499H4.5C2.15279 19.7499 0.25 17.8471 0.25 15.4999ZM4.5 12.7499C2.98121 12.7499 1.75 13.9811 1.75 15.4999C1.75 17.0187 2.98121 18.2499 4.5 18.2499H17.5C19.0188 18.2499 20.25 17.0187 20.25 15.4999C20.25 13.9811 19.0188 12.7499 17.5 12.7499H4.5Z" fill="#242628"/></svg> $label_license$license_status_badge</a></li>
                </ul>
            HERE; ?>

            <hr class="wpz-onboard_hr">
            <ul class="wpz-onboard_tabs">
                <li class="wpz-onboard_tab wpz-onboard_tab-dashboard"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom_options' ) ); ?>" title="Theme Options"><svg width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M3.77905 2.74023C3.85633 2.74023 3.93088 2.75192 4.00103 2.77363C4.10964 2.80686 4.21846 2.86673 4.313 2.96128C4.47346 3.12174 4.53405 3.3233 4.53405 3.49523C4.53405 3.66717 4.47346 3.86873 4.313 4.02919C4.15254 4.18965 3.95098 4.25023 3.77905 4.25023C3.60711 4.25023 3.40555 4.18965 3.24509 4.02919C3.08463 3.86873 3.02405 3.66717 3.02405 3.49523C3.02405 3.3233 3.08463 3.12174 3.24509 2.96128C3.33964 2.86673 3.44845 2.80686 3.55707 2.77363C3.62721 2.75192 3.70177 2.74023 3.77905 2.74023Z" fill="#242628"/>
<path d="M6.544 2.77363C6.47385 2.75192 6.3993 2.74023 6.32202 2.74023C6.24474 2.74023 6.17018 2.75192 6.10003 2.77363C5.99142 2.80686 5.88261 2.86673 5.78806 2.96128C5.6276 3.12174 5.56702 3.3233 5.56702 3.49523C5.56702 3.76278 5.70276 3.95643 5.82489 4.06329C5.93283 4.15774 6.10335 4.25023 6.32202 4.25023C6.49395 4.25023 6.69551 4.18965 6.85597 4.02919C7.01643 3.86873 7.07702 3.66717 7.07702 3.49523C7.07702 3.3233 7.01643 3.12174 6.85597 2.96128C6.76143 2.86673 6.65261 2.80686 6.544 2.77363Z" fill="#242628"/>
<path d="M8.86096 2.74023C8.93824 2.74023 9.01279 2.75192 9.08294 2.77363C9.19155 2.80686 9.30037 2.86673 9.39491 2.96128C9.55537 3.12174 9.61596 3.3233 9.61596 3.49523C9.61596 3.66717 9.55537 3.86873 9.39491 4.02919C9.23445 4.18965 9.03289 4.25023 8.86096 4.25023C8.68902 4.25023 8.48746 4.18965 8.327 4.02919C8.16654 3.86873 8.10596 3.66717 8.10596 3.49523C8.10596 3.3233 8.16654 3.12174 8.327 2.96128C8.42155 2.86673 8.53036 2.80686 8.63897 2.77363C8.70912 2.75192 8.78368 2.74023 8.86096 2.74023Z" fill="#242628"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M21.75 3C21.75 1.48079 20.5192 0.25 19 0.25H3C1.48079 0.25 0.25 1.48079 0.25 3V17C0.25 18.5192 1.48079 19.75 3 19.75H19C20.5192 19.75 21.75 18.5192 21.75 17V3ZM3 1.75C2.30921 1.75 1.75 2.30921 1.75 3V5.25H20.25V3C20.25 2.30921 19.6908 1.75 19 1.75H3ZM1.75 6.75H7.25V18.25H3C2.30921 18.25 1.75 17.6908 1.75 17V6.75ZM8.75 12.75V18.25H19C19.6908 18.25 20.25 17.6908 20.25 17V12.75H8.75ZM20.25 11.25H8.75V6.75H20.25V11.25Z" fill="#242628"/>
</svg> <?php esc_html_e( 'Theme Options', 'wpzoom' ); ?></a></li>
                <li class="wpz-onboard_tab wpz-onboard_tab-dashboard"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom_update' ) ); ?>" title="Framework"><svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M6.67013 5.67025C8.50898 3.8314 11.4903 3.83127 13.3293 5.66986C15.1683 7.50884 15.1686 10.4908 13.3297 12.3298C11.4907 14.1688 8.5091 14.1688 6.67013 12.3298C4.83115 10.4908 4.83115 7.50923 6.67013 5.67025ZM7.73079 6.73092C8.98399 5.47772 11.0158 5.47772 12.269 6.73091C13.5222 7.98411 13.5222 10.0159 12.269 11.2691C11.0158 12.5223 8.98398 12.5223 7.73079 11.2691C6.4776 10.0159 6.4776 7.9841 7.73079 6.73092Z" fill="#242628"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M6.53593 0.25C5.55445 0.25 4.64562 0.773543 4.15441 1.62501L0.69024 7.6253C0.199496 8.4762 0.199663 9.52409 0.690408 10.375L4.15441 16.375C4.64551 17.2263 5.55325 17.75 6.53593 17.75H13.4639C14.4454 17.75 15.3544 17.2262 15.8456 16.3747L19.3096 10.3747C19.8004 9.5238 19.8002 8.47591 19.3095 7.62501L15.8455 1.62501C15.3545 0.773905 14.4468 0.25 13.4649 0.25H6.53593ZM5.45362 2.3747C5.67645 1.98834 6.08952 1.75 6.53593 1.75H13.4649C13.9109 1.75 14.3234 1.98827 14.5464 2.37499L18.0104 8.37499C18.2334 8.76194 18.2333 9.23835 18.0102 9.6253L14.5462 15.6253C14.3234 16.0117 13.9103 16.25 13.4639 16.25H6.53593C6.08873 16.25 5.67639 16.0116 5.45346 15.625L1.98946 9.62501C1.76642 9.23806 1.76659 8.76165 1.98962 8.3747L5.45362 2.3747Z" fill="#242628"/>
</svg> <?php esc_html_e( 'Framework Updates', 'wpzoom' ); ?></a></li>
               <?php if ( 'Inspiro Premium' == $theme_name ) { ?> <li class="wpz-onboard_tab wpz-onboard_tab-dashboard"><a href="https://www.wpzoom.com/themes/inspiro/changelog/" title="Changelog" target="_blank"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M8.53033 0.46967C8.23744 0.176777 7.76256 0.176777 7.46967 0.46967C7.17678 0.762563 7.17678 1.23744 7.46967 1.53033L8.18934 2.25H4C1.92893 2.25 0.25 3.92893 0.25 6V14C0.25 16.0711 1.92893 17.75 4 17.75H7C7.41421 17.75 7.75 17.4142 7.75 17C7.75 16.5858 7.41421 16.25 7 16.25H4C2.75736 16.25 1.75 15.2426 1.75 14V6C1.75 4.75736 2.75736 3.75 4 3.75H8.18934L7.46967 4.46967C7.17678 4.76256 7.17678 5.23744 7.46967 5.53033C7.76256 5.82322 8.23744 5.82322 8.53033 5.53033L10.5303 3.53033C10.6022 3.45842 10.6565 3.37555 10.6931 3.28709C10.7298 3.19866 10.75 3.10169 10.75 3C10.75 2.89831 10.7298 2.80134 10.6931 2.71291C10.6565 2.62445 10.6022 2.54158 10.5303 2.46967L8.53033 0.46967Z" fill="#242628"/>
<path d="M16 3.75H13C12.5858 3.75 12.25 3.41421 12.25 3C12.25 2.58579 12.5858 2.25 13 2.25H16C18.0711 2.25 19.75 3.92893 19.75 6V14C19.75 16.0711 18.0711 17.75 16 17.75H11.8107L12.5303 18.4697C12.8232 18.7626 12.8232 19.2374 12.5303 19.5303C12.2374 19.8232 11.7626 19.8232 11.4697 19.5303L9.46967 17.5303C9.39776 17.4584 9.34351 17.3755 9.30691 17.2871C9.27024 17.1987 9.25 17.1017 9.25 17C9.25 16.9871 9.25033 16.9742 9.25098 16.9614C9.25542 16.8738 9.27491 16.7901 9.30691 16.7129C9.34351 16.6245 9.39776 16.5416 9.46967 16.4697L11.4697 14.4697C11.7626 14.1768 12.2374 14.1768 12.5303 14.4697C12.8232 14.7626 12.8232 15.2374 12.5303 15.5303L11.8107 16.25H16C17.2426 16.25 18.25 15.2426 18.25 14V6C18.25 4.75736 17.2426 3.75 16 3.75Z" fill="#242628"/>
<path d="M12.5303 9.03033C12.8232 8.73744 12.8232 8.26256 12.5303 7.96967C12.2374 7.67678 11.7626 7.67678 11.4697 7.96967L9.5 9.93934L8.53033 8.96967C8.23744 8.67678 7.76256 8.67678 7.46967 8.96967C7.17678 9.26256 7.17678 9.73744 7.46967 10.0303L8.96967 11.5303C9.26256 11.8232 9.73744 11.8232 10.0303 11.5303L12.5303 9.03033Z" fill="#242628"/>
</svg> <?php esc_html_e( 'Changelog', 'wpzoom' ); ?> &#8599;</a></li><?php } ?>
               <?php if ( 'Inspiro PRO' == $theme_name ) { ?> <li class="wpz-onboard_tab wpz-onboard_tab-dashboard"><a href="https://www.wpzoom.com/themes/inspiro-pro/changelog/" title="Changelog" target="_blank"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M8.53033 0.46967C8.23744 0.176777 7.76256 0.176777 7.46967 0.46967C7.17678 0.762563 7.17678 1.23744 7.46967 1.53033L8.18934 2.25H4C1.92893 2.25 0.25 3.92893 0.25 6V14C0.25 16.0711 1.92893 17.75 4 17.75H7C7.41421 17.75 7.75 17.4142 7.75 17C7.75 16.5858 7.41421 16.25 7 16.25H4C2.75736 16.25 1.75 15.2426 1.75 14V6C1.75 4.75736 2.75736 3.75 4 3.75H8.18934L7.46967 4.46967C7.17678 4.76256 7.17678 5.23744 7.46967 5.53033C7.76256 5.82322 8.23744 5.82322 8.53033 5.53033L10.5303 3.53033C10.6022 3.45842 10.6565 3.37555 10.6931 3.28709C10.7298 3.19866 10.75 3.10169 10.75 3C10.75 2.89831 10.7298 2.80134 10.6931 2.71291C10.6565 2.62445 10.6022 2.54158 10.5303 2.46967L8.53033 0.46967Z" fill="#242628"/>
<path d="M16 3.75H13C12.5858 3.75 12.25 3.41421 12.25 3C12.25 2.58579 12.5858 2.25 13 2.25H16C18.0711 2.25 19.75 3.92893 19.75 6V14C19.75 16.0711 18.0711 17.75 16 17.75H11.8107L12.5303 18.4697C12.8232 18.7626 12.8232 19.2374 12.5303 19.5303C12.2374 19.8232 11.7626 19.8232 11.4697 19.5303L9.46967 17.5303C9.39776 17.4584 9.34351 17.3755 9.30691 17.2871C9.27024 17.1987 9.25 17.1017 9.25 17C9.25 16.9871 9.25033 16.9742 9.25098 16.9614C9.25542 16.8738 9.27491 16.7901 9.30691 16.7129C9.34351 16.6245 9.39776 16.5416 9.46967 16.4697L11.4697 14.4697C11.7626 14.1768 12.2374 14.1768 12.5303 14.4697C12.8232 14.7626 12.8232 15.2374 12.5303 15.5303L11.8107 16.25H16C17.2426 16.25 18.25 15.2426 18.25 14V6C18.25 4.75736 17.2426 3.75 16 3.75Z" fill="#242628"/>
<path d="M12.5303 9.03033C12.8232 8.73744 12.8232 8.26256 12.5303 7.96967C12.2374 7.67678 11.7626 7.67678 11.4697 7.96967L9.5 9.93934L8.53033 8.96967C8.23744 8.67678 7.76256 8.67678 7.46967 8.96967C7.17678 9.26256 7.17678 9.73744 7.46967 10.0303L8.96967 11.5303C9.26256 11.8232 9.73744 11.8232 10.0303 11.5303L12.5303 9.03033Z" fill="#242628"/>
</svg> <?php esc_html_e( 'Changelog', 'wpzoom' ); ?> &#8599;</a></li><?php } ?>
               <?php if ( $theme_name == 'Foodica' || $theme_name == 'Foodica PRO' ) { ?> <li class="wpz-onboard_tab wpz-onboard_tab-dashboard"><a href="https://www.wpzoom.com/themes/foodica/changelog/" title="Changelog" target="_blank"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M8.53033 0.46967C8.23744 0.176777 7.76256 0.176777 7.46967 0.46967C7.17678 0.762563 7.17678 1.23744 7.46967 1.53033L8.18934 2.25H4C1.92893 2.25 0.25 3.92893 0.25 6V14C0.25 16.0711 1.92893 17.75 4 17.75H7C7.41421 17.75 7.75 17.4142 7.75 17C7.75 16.5858 7.41421 16.25 7 16.25H4C2.75736 16.25 1.75 15.2426 1.75 14V6C1.75 4.75736 2.75736 3.75 4 3.75H8.18934L7.46967 4.46967C7.17678 4.76256 7.17678 5.23744 7.46967 5.53033C7.76256 5.82322 8.23744 5.82322 8.53033 5.53033L10.5303 3.53033C10.6022 3.45842 10.6565 3.37555 10.6931 3.28709C10.7298 3.19866 10.75 3.10169 10.75 3C10.75 2.89831 10.7298 2.80134 10.6931 2.71291C10.6565 2.62445 10.6022 2.54158 10.5303 2.46967L8.53033 0.46967Z" fill="#242628"/>
<path d="M16 3.75H13C12.5858 3.75 12.25 3.41421 12.25 3C12.25 2.58579 12.5858 2.25 13 2.25H16C18.0711 2.25 19.75 3.92893 19.75 6V14C19.75 16.0711 18.0711 17.75 16 17.75H11.8107L12.5303 18.4697C12.8232 18.7626 12.8232 19.2374 12.5303 19.5303C12.2374 19.8232 11.7626 19.8232 11.4697 19.5303L9.46967 17.5303C9.39776 17.4584 9.34351 17.3755 9.30691 17.2871C9.27024 17.1987 9.25 17.1017 9.25 17C9.25 16.9871 9.25033 16.9742 9.25098 16.9614C9.25542 16.8738 9.27491 16.7901 9.30691 16.7129C9.34351 16.6245 9.39776 16.5416 9.46967 16.4697L11.4697 14.4697C11.7626 14.1768 12.2374 14.1768 12.5303 14.4697C12.8232 14.7626 12.8232 15.2374 12.5303 15.5303L11.8107 16.25H16C17.2426 16.25 18.25 15.2426 18.25 14V6C18.25 4.75736 17.2426 3.75 16 3.75Z" fill="#242628"/>
<path d="M12.5303 9.03033C12.8232 8.73744 12.8232 8.26256 12.5303 7.96967C12.2374 7.67678 11.7626 7.67678 11.4697 7.96967L9.5 9.93934L8.53033 8.96967C8.23744 8.67678 7.76256 8.67678 7.46967 8.96967C7.17678 9.26256 7.17678 9.73744 7.46967 10.0303L8.96967 11.5303C9.26256 11.8232 9.73744 11.8232 10.0303 11.5303L12.5303 9.03033Z" fill="#242628"/>
</svg> <?php esc_html_e( 'Changelog', 'wpzoom' ); ?> &#8599;</a></li><?php } ?>
            </ul>

        <?php echo <<<HERE
                <h4 class="{$class_prefix}framework-version">$framework_version</h4>

            </div>

            <div class="{$class_prefix}content-wrapper">
                <div class="{$class_prefix}content active">
                    <div class="{$class_prefix}content-main">

                        <div data-id="#dashboard" class="{$class_prefix}content-main-tab {$class_prefix}content-main-dashboard active">

            HERE; ?>

                    <div class="theme-info-wrap welcome-section">
                        <div class="section-content">
                            <div class="header-row">

                                <h3 class="wpz-onboard_content-main-title welcome"><?php echo $theme_name; ?> <?php esc_html_e( 'Dashboard', 'wpzoom' ); ?></h3>
                            </div>
                            <p class="wpz-onboard_content-main-intro"><?php esc_html_e( 'Thank you for installing our theme! Below you can find quick links to different sections where you can configure and customize the theme.', 'wpzoom' ); ?></p>
                            <p class="section_footer">
                                <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" target="_blank"
                                    class="button button-primary">
                                    <?php esc_html_e( 'Customize', 'wpzoom' ); ?>
                                </a>
                                <a href="<?php echo esc_url( __( 'https://www.wpzoom.com/documentation/', 'wpzoom' ) ); ?>"
                                    target="_blank" class="button button-secondary">
                                    <?php esc_html_e( 'Theme Documentation &#8599;', 'wpzoom' ); ?>
                                </a>
                            </p>
                        </div>

                    </div>


                        <div class="theme-info-wrap">
                            <h3 class="wpz-onboard_content-main-title"><?php esc_html_e( 'Customize & Configure', 'wpzoom' ); ?></h3>
                            <div class="wpz-grid-wrap three">
                                <div class="section">
                                    <h4>
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.550044 13.4C6.15004 9.2 13.85 9.2 19.45 13.4C19.7814 13.6485 19.8486 14.1186 19.6 14.45C19.3515 14.7814 18.8814 14.8485 18.55 14.6C13.4834 10.8 6.51671 10.8 1.45004 14.6C1.11867 14.8485 0.648572 14.7814 0.400044 14.45C0.151516 14.1186 0.218673 13.6485 0.550044 13.4Z" fill="#242628"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.00004 1.75C3.65283 1.75 1.75004 3.65279 1.75004 6V14C1.75004 16.3472 3.65283 18.25 6.00004 18.25H14C16.3473 18.25 18.25 16.3472 18.25 14V6C18.25 3.65279 16.3473 1.75 14 1.75H6.00004ZM0.250044 6C0.250044 2.82436 2.82441 0.25 6.00004 0.25H14C17.1757 0.25 19.75 2.82436 19.75 6V14C19.75 17.1756 17.1757 19.75 14 19.75H6.00004C2.82441 19.75 0.250044 17.1756 0.250044 14V6Z" fill="#242628"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15 5.9988C14.9994 5.44675 14.5515 4.99967 13.9994 5C13.4474 5.00033 13 5.44795 13 6C13 6.55205 13.4474 6.99967 13.9994 7C14.5508 7.00033 14.9983 6.55432 15 6.00318" fill="#242628"/>
                                        </svg>
                                        <?php esc_html_e( 'Site Logo', 'wpzoom' ); ?>
                                    </h4>
                                    <p class="about">
                                        <?php esc_html_e( 'Add a logo image in the Site Identity section via Customizer, and it will appear neatly in your website\'s header.', 'wpzoom' ); ?>
                                    </p>
                                    <p class="section_footer">
                                        <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ); ?>"
                                            target="_blank" class="button button-primary">
                                            <?php esc_html_e( 'Customize', 'wpzoom' ); ?>
                                        </a>
                                    </p>
                                </div>

                                <?php if ( current_theme_supports( 'zoom-portfolio' ) ) { ?>
                                <div class="section">
                                    <h4>
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3 0.25C1.48054 0.25 0.25 1.48203 0.25 3V13C0.25 14.5192 1.48079 15.75 3 15.75H4.89845C5.12126 16.7058 5.84908 17.5108 6.86495 17.7829L13.8549 19.6559C15.3222 20.0492 16.8293 19.1784 17.2225 17.7116L19.6555 8.63157C20.0486 7.16565 19.1784 5.65629 17.7112 5.26302L12.987 3.99698V3C12.987 1.48079 11.7562 0.25 10.237 0.25H3ZM11.487 4.55707C11.4868 4.56766 11.4868 4.57823 11.487 4.58877V13C11.487 13.6908 10.9278 14.25 10.237 14.25H3C2.30921 14.25 1.75 13.6908 1.75 13V3C1.75 2.30997 2.30946 1.75 3 1.75H10.237C10.9278 1.75 11.487 2.30921 11.487 3V4.55707ZM12.987 5.54991L17.3229 6.71189C17.9896 6.89061 18.3853 7.57734 18.2066 8.24334L15.7736 17.3233C15.5947 17.9904 14.9098 18.3857 14.2431 18.207L7.25308 16.334C6.91913 16.2445 6.65304 16.0279 6.49328 15.75H10.237C11.7562 15.75 12.987 14.5192 12.987 13V5.54991Z" fill="#242628"/>
                                        </svg>
                                        <?php esc_html_e( 'Portfolio', 'wpzoom' ); ?>
                                    </h4>
                                    <p class="about">
                                        <?php esc_html_e( 'Quickly create a Portfolio section on your site. Need Portfolio Blocks? Install our free WPZOOM Portfolio plugin.', 'wpzoom' ); ?>
                                    </p>
                                    <p class="section_footer">
                                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=portfolio_item' ) ); ?>"
                                           target="_blank" class="button button-primary">
                                            <?php esc_html_e( 'Add new post', 'wpzoom' ); ?>
                                        </a>
                                        <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=wpzoom%2520portfolio&tab=search&type=term' ) ); ?>"
                                               target="_blank" class="button button-secondary-gray">
                                                <?php esc_html_e( 'Install Portfolio Plugin', 'wpzoom' ); ?>
                                        </a>

                                    </p>
                                </div><?php } ?>

                                <div class="section quick-action-section">
                                    <h4>
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8.7062 1.79315C10.7847 -0.234165 14.1069 -0.213458 16.16 1.83961C18.2131 3.89268 18.2338 7.21491 16.2065 9.29341L16.1999 9.3001L14.008 11.492C13.7151 11.7849 13.2402 11.7849 12.9473 11.492C12.6544 11.1991 12.6544 10.7242 12.9473 10.4313L15.1358 8.24287C16.5866 6.75198 16.5707 4.37166 15.0993 2.90027C13.628 1.42889 11.2476 1.413 9.75676 2.86383L7.56829 5.05229C7.2754 5.34519 6.80053 5.34519 6.50763 5.05229C6.21474 4.7594 6.21474 4.28453 6.50763 3.99163L8.7062 1.79315Z" fill="#242628"/>
                                            <path d="M12.1982 5.8017C12.4911 6.09459 12.4911 6.56947 12.1982 6.86236L6.862 12.1986C6.5691 12.4915 6.09423 12.4915 5.80133 12.1986C5.50844 11.9057 5.50844 11.4308 5.80133 11.1379L11.1376 5.8017C11.4305 5.50881 11.9053 5.50881 12.1982 5.8017Z" fill="#242628"/>
                                            <path d="M5.05229 7.56842C5.34519 7.27552 5.34519 6.80065 5.05229 6.50776C4.7594 6.21486 4.28453 6.21486 3.99163 6.50776L1.79968 8.69963L1.79315 8.70632C-0.234165 10.7848 -0.213458 14.1071 1.83961 16.1601C3.89268 18.2132 7.21495 18.2339 9.29345 16.2066L11.492 14.0081C11.7849 13.7152 11.7849 13.2403 11.492 12.9474C11.1991 12.6545 10.7242 12.6545 10.4313 12.9474L8.24287 15.1359C6.75198 16.5867 4.37166 16.5709 2.90027 15.0995C1.42889 13.6281 1.413 11.2478 2.86383 9.75688L5.05229 7.56842Z" fill="#242628"/>
                                        </svg>
                                        <?php esc_html_e( 'Quick Links', 'wpzoom' ); ?>
                                    </h4>
                                    <p class="about about-quick-links">
                                        <a href="https://www.wpzoom.com/documentation/" target="_blank" class="description-link">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.1875 2C3.1875 1.27534 3.77534 0.6875 4.5 0.6875H6.75C7.67007 0.6875 8.48689 1.1292 9 1.81214C9.51311 1.1292 10.3299 0.6875 11.25 0.6875H13.5C14.2247 0.6875 14.8125 1.27534 14.8125 2V2.1875H15C16.1394 2.1875 17.0625 3.11059 17.0625 4.25V13.25C17.0625 14.3894 16.1394 15.3125 15 15.3125H3C1.86059 15.3125 0.9375 14.3894 0.9375 13.25V4.25C0.9375 3.11059 1.86059 2.1875 3 2.1875H3.1875V2ZM9.5625 12.4997C10.0325 12.1467 10.6168 11.9375 11.25 11.9375H13.5C13.6033 11.9375 13.6875 11.8533 13.6875 11.75V2C13.6875 1.89666 13.6033 1.8125 13.5 1.8125H11.25C10.3179 1.8125 9.5625 2.56791 9.5625 3.5V12.4997ZM8.4375 3.5C8.4375 2.56791 7.68209 1.8125 6.75 1.8125H4.5C4.39666 1.8125 4.3125 1.89666 4.3125 2V11.75C4.3125 11.8533 4.39666 11.9375 4.5 11.9375H6.75C7.38318 11.9375 7.96745 12.1467 8.4375 12.4997V3.5ZM15 3.3125H14.8125V11.75C14.8125 12.4747 14.2247 13.0625 13.5 13.0625H11.25C10.5152 13.0625 9.89012 13.532 9.65849 14.1875H15C15.5181 14.1875 15.9375 13.7681 15.9375 13.25V4.25C15.9375 3.73191 15.5181 3.3125 15 3.3125ZM6.75 13.0625C7.48485 13.0625 8.10988 13.532 8.34151 14.1875H3C2.48191 14.1875 2.0625 13.7681 2.0625 13.25V4.25C2.0625 3.73191 2.48191 3.3125 3 3.3125H3.1875V11.75C3.1875 12.4747 3.77534 13.0625 4.5 13.0625H6.75Z" fill="#3496FF"/>
                                            </svg>
                                            <?php esc_html_e( 'Theme Documentation', 'wpzoom' ); ?>
                                        </a>
                                        <a href="https://www.wpzoom.com/support/tickets/" target="_blank" class="description-link">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5.31723 0.502779C2.95867 0.421836 0.91239 2.11793 0.554361 4.45056C0.196331 6.7832 1.63974 9.01491 3.91401 9.64505C4.21339 9.728 4.52333 9.55254 4.60628 9.25316C4.68923 8.95378 4.51378 8.64384 4.2144 8.56089C2.48953 8.08298 1.3948 6.39038 1.66634 4.62124C1.93788 2.85209 3.48984 1.56573 5.27865 1.62712C7.06745 1.68851 8.52757 3.07824 8.67718 4.86184C8.70315 5.17141 8.97516 5.40132 9.28473 5.37535C9.59431 5.34938 9.82421 5.07737 9.79825 4.7678C9.60098 2.41611 7.67579 0.583722 5.31723 0.502779Z" fill="#3496FF"/>
                                                <path d="M6.68215 4.22861C6.90188 4.44822 6.90197 4.80438 6.68236 5.02411L5.27627 6.43094C5.05669 6.65064 4.70059 6.65077 4.48085 6.43122L3.636 5.58712C3.41623 5.36755 3.41607 5.0114 3.63564 4.79163C3.85522 4.57186 4.21137 4.5717 4.43114 4.79128L4.87814 5.23788L5.88665 4.22882C6.10626 4.00909 6.46242 4.009 6.68215 4.22861Z" fill="#3496FF"/>
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.75266 8.56606C5.75266 7.42662 6.67635 6.50293 7.81578 6.50293H13.068C14.2074 6.50293 15.1311 7.42662 15.1311 8.56606V11.5673C15.1311 12.7067 14.2074 13.6304 13.068 13.6304H11.4918L9.90848 14.9197C9.62785 15.1482 9.24076 15.1949 8.9139 15.0395C8.58705 14.8842 8.37875 14.5546 8.37875 14.1926V13.6304H7.81578C6.67635 13.6304 5.75266 12.7067 5.75266 11.5673V8.56606ZM7.81578 7.62793C7.29767 7.62793 6.87766 8.04794 6.87766 8.56606V10.3176V11.5673C6.87766 12.0854 7.29767 12.5054 7.81578 12.5054H8.28281H8.94125C9.25191 12.5054 9.50375 12.7573 9.50375 13.0679V13.7985L10.9365 12.6318C11.0369 12.55 11.1623 12.5054 11.2917 12.5054H13.068C13.5861 12.5054 14.0061 12.0854 14.0061 11.5673V8.56606C14.0061 8.04794 13.5861 7.62793 13.068 7.62793H7.81578Z" fill="#3496FF"/>
                                            </svg>
                                            <?php esc_html_e( 'Support Desk', 'wpzoom' ); ?>
                                        </a>
                                        <a href="https://www.facebook.com/groups/wpzoom" target="_blank" class="description-link">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M7.85925 7.29906C7.85925 6.04565 8.87609 5.02881 10.1295 5.02881H10.737C11.0477 5.02881 11.2995 5.28065 11.2995 5.59131C11.2995 5.90197 11.0477 6.15381 10.737 6.15381H10.1295C9.49741 6.15381 8.98425 6.66697 8.98425 7.29906V7.92334H10.7362C11.0469 7.92334 11.2987 8.17518 11.2987 8.48584C11.2987 8.7965 11.0469 9.04834 10.7362 9.04834H8.98425V12.4088C8.98425 12.7195 8.73241 12.9713 8.42175 12.9713C8.11109 12.9713 7.85925 12.7195 7.85925 12.4088V9.04834H7.26297C6.95231 9.04834 6.70047 8.7965 6.70047 8.48584C6.70047 8.17518 6.95231 7.92334 7.26297 7.92334H7.85925V7.29906Z" fill="#3496FF"/>
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9 0.9375C4.5472 0.9375 0.9375 4.5472 0.9375 9C0.9375 13.4528 4.5472 17.0625 9 17.0625C13.4528 17.0625 17.0625 13.4528 17.0625 9C17.0625 4.5472 13.4528 0.9375 9 0.9375ZM2.0625 9C2.0625 5.16852 5.16852 2.0625 9 2.0625C12.8315 2.0625 15.9375 5.16852 15.9375 9C15.9375 12.8315 12.8315 15.9375 9 15.9375C5.16852 15.9375 2.0625 12.8315 2.0625 9Z" fill="#3496FF"/>
                                            </svg>
                                            <?php esc_html_e( 'Join our Facebook group', 'wpzoom' ); ?>
                                        </a>
                                        <a href="https://www.wpzoom.com/plugins/" target="_blank" class="description-link">
                                            <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.98359 8.70108L5.58302 9.30884C6.78512 9.76562 7.73437 10.7149 8.19115 11.917L8.7989 13.5164C8.90953 13.8075 9.18856 14 9.49999 14C9.81143 14 10.0905 13.8075 10.2011 13.5164L10.8088 11.917C11.2656 10.7149 12.2149 9.76562 13.417 9.30884L15.0164 8.70108C15.3075 8.59046 15.5 8.31143 15.5 7.99999C15.5 7.68856 15.3075 7.40953 15.0164 7.2989L13.417 6.69115C12.2149 6.23437 11.2656 5.28512 10.8088 4.08302L10.2011 2.48359C10.0905 2.19247 9.81143 2 9.49999 2C9.18856 2 8.90953 2.19247 8.7989 2.48359L8.19115 4.08302C7.73437 5.28512 6.78513 6.23437 5.58302 6.69115L3.98359 7.2989C3.69247 7.40953 3.5 7.68856 3.5 7.99999C3.5 8.31143 3.69247 8.59046 3.98359 8.70108Z" stroke="#3496FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M3.5 14.75V11.75" stroke="#3496FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M2 13.25H5" stroke="#3496FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M2.75 4.25V1.25" stroke="#3496FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M1.25 2.75H4.25" stroke="#3496FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <?php esc_html_e( 'View our Plugins', 'wpzoom' ); ?>
                                        </a>
                                        <a href="https://www.trustpilot.com/review/wpzoom.com" target="_blank" class="description-link">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.19037 2.625C8.97642 2.625 8.78095 2.74628 8.68595 2.93798L8.68481 2.94028L7.08289 6.13586C7.00027 6.30067 6.84213 6.41453 6.65963 6.44062L3.1114 6.94783L3.10988 6.94805C2.89606 6.97802 2.71821 7.12764 2.6521 7.33318C2.58598 7.53872 2.64324 7.76397 2.7995 7.91298L2.80049 7.91392L5.35605 10.3629C5.49135 10.4926 5.5532 10.6811 5.52099 10.8657L4.91399 14.3449L4.91369 14.3466C4.87617 14.5579 4.96203 14.7721 5.13508 14.899C5.30813 15.0259 5.53826 15.0434 5.7285 14.9441L5.73064 14.943L8.93223 13.2893C9.09419 13.2056 9.28665 13.2057 9.4486 13.2894L12.6502 14.9438L12.6522 14.9449C12.8425 15.0442 13.0726 15.0267 13.2457 14.8998C13.4187 14.7729 13.5046 14.5586 13.467 14.3474L13.4667 14.3457L12.8597 10.8657C12.8275 10.6811 12.8894 10.4926 13.0247 10.3629L15.5803 7.91392L15.5812 7.91298C15.7375 7.76397 15.7948 7.53872 15.7286 7.33318C15.6625 7.12764 15.4847 6.97802 15.2709 6.94805L15.2693 6.94783L11.7211 6.44062C11.5386 6.41453 11.3805 6.30067 11.2979 6.13586L9.69478 2.93798C9.59978 2.74628 9.40432 2.625 9.19037 2.625ZM7.67857 2.43717C7.96366 1.86311 8.54933 1.5 9.19037 1.5C9.83137 1.5 10.417 1.86307 10.7021 2.43709C10.7023 2.43753 10.7026 2.43797 10.7028 2.43842L12.1717 5.36859L15.427 5.83394C15.4273 5.83398 15.4276 5.83402 15.4279 5.83405C16.0686 5.92419 16.6015 6.37268 16.7996 6.98869C16.9977 7.60453 16.8264 8.27938 16.3586 8.72617C16.3586 8.72618 16.3586 8.72616 16.3586 8.72617C16.3583 8.72647 16.358 8.72681 16.3576 8.72712L14.0195 10.9678L14.5747 14.1507C14.5748 14.151 14.5748 14.1513 14.5749 14.1517C14.6869 14.7848 14.4295 15.4267 13.9109 15.807C13.3924 16.1872 12.703 16.2398 12.1329 15.9428C12.1325 15.9426 12.1321 15.9424 12.1317 15.9422L9.19031 14.4222L6.24907 15.9414C6.24862 15.9417 6.24816 15.9419 6.2477 15.9421C5.67759 16.2391 4.98825 16.1864 4.46981 15.8062C3.95121 15.4259 3.69377 14.784 3.80586 14.1508C3.80591 14.1505 3.80597 14.1502 3.80602 14.1499L4.3612 10.9677L2.02309 8.72712C2.02288 8.72692 2.02268 8.72672 2.02247 8.72652C1.55444 8.27973 1.38299 7.60469 1.58114 6.98869C1.77929 6.37266 2.31218 5.92416 2.95294 5.83405C2.9532 5.83401 2.95346 5.83397 2.95373 5.83394L6.20907 5.36859L7.67857 2.43717Z" fill="#3496FF"/>
                                            </svg>
                                            <?php esc_html_e( 'Leave a Review', 'wpzoom' ); ?>
                                        </a>
                                    </p>
                                </div>

                                <?php echo <<<HERE

                                <div class="section quick-action-section">
                                    <h4><svg width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.6739 3.2469C10.5319 2.55135 10.1911 1.88827 9.65135 1.34856C8.18725 -0.116317 5.81231 -0.116058 4.34769 1.34856C2.8828 2.81346 2.8828 5.18733 4.34769 6.65222C5.81259 8.11712 8.18646 8.11712 9.65135 6.65222C10.1929 6.11072 10.5342 5.445 10.6754 4.74691L13.25 4.73629V6.60389C13.25 7.01811 13.5858 7.35389 14 7.35389C14.4142 7.35389 14.75 7.01811 14.75 6.60389V4.7301L17.25 4.71979V6.57069C17.25 6.9849 17.5858 7.32069 18 7.32069C18.4142 7.32069 18.75 6.9849 18.75 6.57069V3.96669C18.75 3.96565 18.75 3.96462 18.75 3.96358C18.7483 3.5508 18.4132 3.21669 18 3.21669M8.59047 2.409C7.71254 1.53032 6.28759 1.52999 5.40835 2.40922C4.52925 3.28833 4.52925 4.71246 5.40835 5.59156C6.28746 6.47067 7.71159 6.47067 8.59069 5.59156C9.02906 5.15319 9.24884 4.5793 9.25002 4.00504C9.25001 4.00429 9.25001 4.00354 9.25001 4.00278C9.25 4.00005 9.25 3.99732 9.25002 3.9946C9.24855 3.42072 9.02855 2.84708 8.59047 2.409Z" fill="#242628"/><path d="M17.9985 3.21669C17.9979 3.21669 17.9974 3.21669 17.9969 3.2167L10.6739 3.2469" fill="#242628"/><path fill-rule="evenodd" clip-rule="evenodd" d="M16.997 14.4433C17.5852 14.4405 18.0565 14.9172 18.0565 15.4988C18.0565 16.0813 17.5859 16.5563 17 16.5563C16.4163 16.5563 15.9435 16.0835 15.9435 15.4998C15.9435 14.917 16.4146 14.4449 16.997 14.4433ZM16.997 14.4433L16.994 14.4433L16.996 14.7248L17 14.7235V14.4433L16.997 14.4433Z" fill="#242628"/><path d="M14.0565 15.4988C14.0565 14.9172 13.5852 14.4405 12.997 14.4433L13 14.4433V14.7235L12.996 14.7248L12.994 14.4433L12.997 14.4433C12.4146 14.4449 11.9435 14.917 11.9435 15.4998C11.9435 16.0835 12.4163 16.5563 13 16.5563C13.5827 16.5563 14.0565 16.0835 14.0565 15.4988Z" fill="#242628"/><path fill-rule="evenodd" clip-rule="evenodd" d="M8.99697 14.4433C9.58515 14.4405 10.0565 14.9172 10.0565 15.4988C10.0565 16.0835 9.58271 16.5563 8.99998 16.5563C8.41625 16.5563 7.94348 16.0835 7.94348 15.4998C7.94348 14.917 8.41463 14.4449 8.99697 14.4433ZM8.99697 14.4433L8.99396 14.4433L8.99605 14.7248L8.99998 14.7235V14.4433L8.99697 14.4433Z" fill="#242628"/><path d="M6.05648 15.4988C6.05648 14.9172 5.58515 14.4405 4.99697 14.4433L4.99998 14.4433V14.7235L4.99605 14.7248L4.99396 14.4433L4.99697 14.4433C4.41463 14.4449 3.94348 14.917 3.94348 15.4998C3.94348 16.0835 4.41625 16.5563 4.99998 16.5563C5.58271 16.5563 6.05648 16.0835 6.05648 15.4988Z" fill="#242628"/><path fill-rule="evenodd" clip-rule="evenodd" d="M0.25 15.4999C0.25 13.1527 2.15279 11.2499 4.5 11.2499H17.5C19.8472 11.2499 21.75 13.1527 21.75 15.4999C21.75 17.8471 19.8472 19.7499 17.5 19.7499H4.5C2.15279 19.7499 0.25 17.8471 0.25 15.4999ZM4.5 12.7499C2.98121 12.7499 1.75 13.9811 1.75 15.4999C1.75 17.0187 2.98121 18.2499 4.5 18.2499H17.5C19.0188 18.2499 20.25 17.0187 20.25 15.4999C20.25 13.9811 19.0188 12.7499 17.5 12.7499H4.5Z" fill="#242628"/></svg> Theme License <span><strong class="{$class_prefix}content-main-license-label"></strong>{$label_badge}</span></h4>

                                        <p>{$label_license_note}</p>

                                        <p class="section_footer">
                                            <a href="#license" id="license-tab-link" class="button button-primary manage-license-btn">Manage License</a>
                                        </p>
                                </div>

                                HERE; ?>

                                <?php if ( in_array( $theme_slug, ['inspiro', 'wpzoom-inspiro-pro'] ) ) { ?>

                                <div class="section">
                                    <h4>
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6.55 18.65C6.55 19.1471 6.14706 19.55 5.65 19.55C5.15294 19.55 4.75 19.1471 4.75 18.65C4.75 18.1529 5.15294 17.75 5.65 17.75C6.14706 17.75 6.55 18.1529 6.55 18.65Z" fill="#242628"/>
                                            <path d="M10.55 18.65C10.55 19.1471 10.1471 19.55 9.65 19.55C9.15294 19.55 8.75 19.1471 8.75 18.65C8.75 18.1529 9.15294 17.75 9.65 17.75C10.1471 17.75 10.55 18.1529 10.55 18.65Z" fill="#242628"/>
                                            <path d="M14.55 18.65C14.55 19.1471 14.1471 19.55 13.65 19.55C13.1529 19.55 12.75 19.1471 12.75 18.65C12.75 18.1529 13.1529 17.75 13.65 17.75C14.1471 17.75 14.55 18.1529 14.55 18.65Z" fill="#242628"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0 4.75C0 2.12665 2.12665 0 4.75 0H14.75C17.3734 0 19.5 2.12665 19.5 4.75V10.75C19.5 13.3734 17.3734 15.5 14.75 15.5H4.75C2.12665 15.5 0 13.3734 0 10.75V4.75ZM4.75 1.5C2.95507 1.5 1.5 2.95507 1.5 4.75V10.75C1.5 12.5449 2.95507 14 4.75 14H14.75C16.5449 14 18 12.5449 18 10.75V4.75C18 2.95507 16.5449 1.5 14.75 1.5H4.75Z" fill="#242628"/>
                                        </svg>
                                        <?php esc_html_e( 'Homepage Slideshow', 'wpzoom' ); ?>
                                    </h4>
                                    <p class="about">
                                        <?php esc_html_e( 'Create a fully working slideshow with videos from YouTube or Vimeo, plus mobile video options, popups, and more.', 'wpzoom' ); ?>
                                    </p>
                                    <p class="section_footer">
                                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=slider' ) ); ?>"
                                           target="_blank" class="button button-primary">
                                            <?php esc_html_e( 'Edit Slides', 'wpzoom' ); ?>
                                        </a>
                                        <a href="https://www.wpzoom.com/documentation/inspiro/inspiro-homepage-slideshow/"
                                               target="_blank" class="button button-secondary-gray">
                                                <?php esc_html_e( 'Slideshow Documentation', 'wpzoom' ); ?>
                                        </a>
                                    </p>
                                </div>

                                <?php } ?>

                                <div class="section">
                                    <h4>
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.246582 9C0.246582 8.58579 0.582368 8.25 0.996582 8.25H13.0016C13.4158 8.25 13.7516 8.58579 13.7516 9C13.7516 9.41421 13.4158 9.75 13.0016 9.75H0.996582C0.582368 9.75 0.246582 9.41421 0.246582 9Z" fill="#242628"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.246582 13.002C0.246582 12.5877 0.582368 12.252 0.996582 12.252H16.0028C16.417 12.252 16.7528 12.5877 16.7528 13.002C16.7528 13.4162 16.417 13.752 16.0028 13.752H0.996582C0.582368 13.752 0.246582 13.4162 0.246582 13.002Z" fill="#242628"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.246582 17.0039C0.246582 16.5897 0.582368 16.2539 0.996582 16.2539H7.9995C8.41371 16.2539 8.7495 16.5897 8.7495 17.0039C8.7495 17.4181 8.41371 17.7539 7.9995 17.7539H0.996582C0.582368 17.7539 0.246582 17.4181 0.246582 17.0039Z" fill="#242628"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.246582 1.99707C0.246582 1.03057 1.03008 0.24707 1.99658 0.24707H16.0032C16.9697 0.24707 17.7532 1.03057 17.7532 1.99707V3.99874C17.7532 4.96523 16.9697 5.74874 16.0032 5.74874H1.99658C1.03008 5.74874 0.246582 4.96523 0.246582 3.99874V1.99707ZM1.99658 1.74707C1.85851 1.74707 1.74658 1.859 1.74658 1.99707V3.99874C1.74658 4.13681 1.85851 4.24874 1.99658 4.24874H16.0032C16.1413 4.24874 16.2532 4.13681 16.2532 3.99874V1.99707C16.2532 1.859 16.1413 1.74707 16.0032 1.74707H1.99658Z" fill="#242628"/>
                                        </svg>
                                        <?php esc_html_e( 'Header', 'wpzoom' ); ?>
                                    </h4>
                                    <p class="about">
                                        <?php esc_html_e( 'Configure your Header settings to suit your theme. Adjust menus, logos, and other elements for a polished, branded look.', 'wpzoom' ); ?>
                                    </p>
                                    <p class="section_footer">
                                        <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=header' ) ); ?>"
                                            target="_blank" class="button button-primary">
                                            <?php esc_html_e( 'Customize', 'wpzoom' ); ?>
                                        </a>
                                    </p>
                                </div>

                                <?php if ( 'Inspiro Premium' == $theme_name ) { ?>

                                <div class="section quick-action-section">
                                    <h4><svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.05786 8.66874C8.05786 7.33606 9.13912 6.26074 10.4679 6.26074C11.7978 6.26074 12.8769 7.33823 12.8769 8.66974C12.8769 9.72816 12.1823 10.2839 11.7247 10.5919L11.7239 10.5924C11.5356 10.7189 11.4111 10.824 11.331 10.9319C11.2635 11.0228 11.2169 11.1291 11.2169 11.2967V11.5037C11.2169 11.918 10.8811 12.2537 10.4669 12.2537C10.0526 12.2537 9.71686 11.918 9.71686 11.5037V11.2967C9.71686 10.2245 10.4191 9.66182 10.8873 9.34737C11.0706 9.22396 11.1908 9.12205 11.2679 9.01827C11.3324 8.93128 11.3769 8.83018 11.3769 8.66974C11.3769 8.16725 10.9699 7.76074 10.4679 7.76074C9.9646 7.76074 9.55786 8.16743 9.55786 8.66874C9.55786 9.08296 9.22207 9.41874 8.80786 9.41874C8.39365 9.41874 8.05786 9.08296 8.05786 8.66874Z" fill="#242628"/><path d="M11.3698 13.9898C11.3698 14.4869 10.9669 14.8898 10.4698 14.8898C9.97277 14.8898 9.56982 14.4869 9.56982 13.9898C9.56982 13.4928 9.97277 13.0898 10.4698 13.0898C10.9669 13.0898 11.3698 13.4928 11.3698 13.9898Z" fill="#242628"/><path fill-rule="evenodd" clip-rule="evenodd" d="M10.467 2.26105C5.93452 2.26105 2.26105 5.93452 2.26105 10.467C2.26105 14.9994 5.93452 18.6729 10.467 18.6729C11.9225 18.6729 13.2873 18.2895 14.4744 17.6234C14.6511 17.5243 14.859 17.4964 15.0555 17.5456L18.3756 18.3756L17.5456 15.0555C17.4964 14.8589 17.5243 14.6509 17.6236 14.4741C18.2895 13.2882 18.6729 11.9236 18.6729 10.467C18.6729 5.93452 14.9994 2.26105 10.467 2.26105ZM0.672852 10.467C0.672852 5.05738 5.05738 0.672852 10.467 0.672852C15.8765 0.672852 20.2611 5.05738 20.2611 10.467C20.2611 12.0916 19.8609 13.622 19.161 14.9692L20.2373 19.2744C20.305 19.545 20.2257 19.8312 20.0285 20.0285C19.8312 20.2257 19.545 20.305 19.2744 20.2373L14.9691 19.161C13.6212 19.8608 12.0909 20.2611 10.467 20.2611C5.05738 20.2611 0.672852 15.8765 0.672852 10.467Z" fill="#242628"/></svg> <?php esc_html_e( 'From the Documentation', 'wpzoom' ); ?></h4>

                                   <ul class="about-quick-links">
                                        <li><a href="https://www.wpzoom.com/documentation/inspiro/inspiro-setting-up-the-homepage/" target="_blank">How to Set up the Front Page</a></li>
                                        <li><a href="https://www.wpzoom.com/documentation/inspiro/inspiro-elementor-integration/" target="_blank">Elementor Integration</a></li>
                                        <li><a href="https://www.wpzoom.com/documentation/inspiro/inspiro-homepage-slideshow/" target="_blank">How to Configure the Homepage Slideshow</a></li>
                                        <li><a href="https://www.wpzoom.com/documentation/inspiro/adding-a-portfolio-page-or-section/" target="_blank">How to Create a Portfolio Page</a></li>
                                    </ul>

                                </div>

                                <?php } ?>

                                <div class="section">
                                    <h4>
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.246582 1C0.246582 0.585786 0.582368 0.25 0.996582 0.25H13.0016C13.4158 0.25 13.7516 0.585786 13.7516 1C13.7516 1.41421 13.4158 1.75 13.0016 1.75H0.996582C0.582368 1.75 0.246582 1.41421 0.246582 1Z" fill="#242628"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.246582 5.00195C0.246582 4.58774 0.582368 4.25195 0.996582 4.25195H16.0028C16.417 4.25195 16.7528 4.58774 16.7528 5.00195C16.7528 5.41617 16.417 5.75195 16.0028 5.75195H0.996582C0.582368 5.75195 0.246582 5.41617 0.246582 5.00195Z" fill="#242628"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.246582 9.00391C0.246582 8.58969 0.582368 8.25391 0.996582 8.25391H7.9995C8.41371 8.25391 8.7495 8.58969 8.7495 9.00391C8.7495 9.41812 8.41371 9.75391 7.9995 9.75391H0.996582C0.582368 9.75391 0.246582 9.41812 0.246582 9.00391Z" fill="#242628"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.246582 13.9971C0.246582 13.0306 1.03008 12.2471 1.99658 12.2471H16.0032C16.9697 12.2471 17.7532 13.0306 17.7532 13.9971V15.9987C17.7532 16.9652 16.9697 17.7487 16.0032 17.7487H1.99658C1.03008 17.7487 0.246582 16.9652 0.246582 15.9987V13.9971ZM1.99658 13.7471C1.85851 13.7471 1.74658 13.859 1.74658 13.9971V15.9987C1.74658 16.1368 1.85851 16.2487 1.99658 16.2487H16.0032C16.1413 16.2487 16.2532 16.1368 16.2532 15.9987V13.9971C16.2532 13.859 16.1413 13.7471 16.0032 13.7471H1.99658Z" fill="#242628"/>
                                        </svg>
                                        <?php esc_html_e( 'Footer', 'wpzoom' ); ?>
                                    </h4>
                                    <p class="about">
                                        <?php esc_html_e( 'Use the Footer settings to personalize your theme\'s layout. Add widgets in the Widgets section to enhance your site\'s footer.', 'wpzoom' ); ?>
                                    </p>
                                    <p class="section_footer">
                                        <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=footer-area' ) ); ?>"
                                           target="_blank" class="button button-primary">
                                            <?php esc_html_e( 'Customize', 'wpzoom' ); ?>
                                        </a>
                                    </p>
                                </div>

                                <div class="section">
                                    <h4>
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0 2.75083C0 1.23159 1.23159 0 2.75083 0H5.75208C7.27133 0 8.50292 1.23159 8.50292 2.75083V4.99292L10.9613 2.53581C12.0355 1.462 13.7768 1.46202 14.851 2.53588L16.9729 4.65784C18.0468 5.73204 18.0469 7.4734 16.973 8.5476L14.5157 11.0049H16.7567C18.2759 11.0049 19.5075 12.2365 19.5075 13.7557V16.757C19.5075 18.2762 18.2759 19.5078 16.7567 19.5078H4.25146C4.24358 19.5078 4.23572 19.5077 4.22789 19.5074C1.89072 19.4947 0 17.5962 0 15.256V2.75083ZM2.75083 1.5C2.06002 1.5 1.5 2.06002 1.5 2.75083V5.91602H7.00292V2.75083C7.00292 2.06002 6.4429 1.5 5.75208 1.5H2.75083ZM7.00292 7.41602H1.5V11.9619H7.00292V7.41602ZM7.00292 13.4619H1.5V15.256C1.5 16.7756 2.73187 18.0075 4.25146 18.0075C5.77105 18.0075 7.00292 16.7756 7.00292 15.256V13.4619ZM8.50292 14.8964V7.11367L12.0218 3.5967C12.5102 3.10842 13.302 3.10847 13.7905 3.5967L15.9122 5.71842C16.4005 6.20684 16.4004 6.99864 15.9122 7.4871L8.50292 14.8964ZM7.51279 18.0078H16.7567C17.4475 18.0078 18.0075 17.4478 18.0075 16.757V13.7557C18.0075 13.0649 17.4475 12.5049 16.7567 12.5049H13.0157L7.51279 18.0078Z" fill="#242628"/>
                                        </svg>
                                        <?php esc_html_e( 'Colors', 'wpzoom' ); ?>
                                    </h4>
                                    <p class="about">
                                        <?php esc_html_e( 'Align your site with your brand by setting up global colors. Customize the background, text, and link colors for consistency.', 'wpzoom' ); ?>
                                    </p>
                                    <p class="section_footer">
                                        <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=color-scheme' ) ); ?>"
                                           target="_blank" class="button button-primary">
                                            <?php esc_html_e( 'Customize', 'wpzoom' ); ?>
                                        </a>
                                    </p>
                                </div>

                                <div class="section">
                                    <h4>
                                        <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 14H17" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M13 18H1" stroke="#242628" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.91445 0.25C4.81536 0.249894 4.71462 0.269541 4.61781 0.311162C4.4314 0.391295 4.2941 0.538016 4.22176 0.711589L1.06661 8.02655C1.03983 8.07365 1.018 8.12394 1.00184 8.17671L0.337401 9.71715C0.173349 10.0975 0.348686 10.5388 0.729027 10.7029C1.10937 10.8669 1.55069 10.6916 1.71474 10.3112L2.21705 9.14668H7.59971L8.09998 10.3104C8.26357 10.6909 8.70467 10.8668 9.08521 10.7032C9.46576 10.5396 9.64163 10.0985 9.47804 9.71799L8.80159 8.14442C8.791 8.11476 8.77859 8.08597 8.76451 8.05817L5.60668 0.712449C5.53455 0.53878 5.39743 0.391885 5.21111 0.311523C5.11432 0.269771 5.01357 0.250002 4.91445 0.25ZM4.91289 2.8966L2.86404 7.64668H6.95488L4.91289 2.8966Z" fill="#242628"/>
                                        </svg>
                                        <?php esc_html_e( 'Fonts', 'wpzoom' ); ?>
                                    </h4>
                                    <p class="about">
                                        <?php esc_html_e( 'Reflect your brand\'s style by customizing global fonts. Select font families, sizes, and weights for a cohesive, professional look.', 'wpzoom' ); ?>
                                    </p>
                                    <p class="section_footer">
                                        <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=typography' ) ); ?>"
                                            target="_blank" class="button button-primary">
                                            <?php esc_html_e( 'Customize', 'wpzoom' ); ?>
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

            <?php
            echo <<<HERE

                        </div>

                        <div data-id="#demo-importer" class="{$class_prefix}content-main-tab {$class_prefix}content-main-demo-importer">
                            <div class="wpz-onboard_header"><h3 class="{$class_prefix}content-main-title">$label_content_title</h3>
                            <p class="{$class_prefix}content-main-intro">$label_content_intro</p></div>

                            <div class="theme-info-wrap"><ol class="{$class_prefix}content-main-steps">

                                <li id="step-choose-design" class="{$class_prefix}content-main-step step-1 step-choose-design {$panel_active}">
                                    <h4 class="{$class_prefix}content-main-step-title">$label_steps_title2</h4>
                                    <a href="#" class="{$class_prefix}content-main-step-toggle-link {$button_active}">close</a>
                                    <p class="{$class_prefix}content-main-step-intro">$label_steps_intro2</p>
                                    {$filter_designs}
                                    <div class="{$class_prefix}content-main-step-content"{$toggle_state}>$step2_content</div>
                                </li>

                                {$select_builder}

                                <li id="step-install-plugins" class="{$class_prefix}content-main-step step-2 step-install-plugins">
                                    <h4 class="{$class_prefix}content-main-step-title">$label_steps_title1</h4>
                                    <p class="{$class_prefix}content-main-step-intro">$label_steps_intro1</p>
                                    <div class="{$class_prefix}content-main-step-content">$step1_content</div>
                                </li>

                                <li id="step-import-demo" class="{$class_prefix}content-main-step step-3 step-import-demo">
                                    <h4 class="{$class_prefix}content-main-step-title">$label_steps_title3</h4>
                                    <p class="{$class_prefix}content-main-step-intro">$label_steps_intro3</p>
                                    <div class="{$class_prefix}content-main-step-content">$step3_content</div>
                                </li>
                            </ol></div>
                        </div>

                        <div data-id="#license" class="{$class_prefix}content-main-tab {$class_prefix}content-main-license">
                            <div class="wpz-onboard_header"><h3 class="{$class_prefix}content-main-title">$label_license_title</h3>
                            <p class="{$class_prefix}content-main-intro">$label_license_intro</p></div>

                            <div class="theme-info-wrap"><form method="post" action="{$post_url}">
                                <fieldset>
                                    <input type="hidden" name="action" value="wpzoom_set_license" />
                                    <input type="hidden" name="wpzoom_set_license_nonce" value="{$license_nonce}" />

                                    <label for="wpzoom_license_key"><strong class="{$class_prefix}content-main-license-label">{$label_license_field_name}</strong>{$label_badge}</label>
                                    <input type="password" name="license_key" id="wpzoom_license_key" value="{$license_key}" class="{$class_prefix}content-main-license-key" data-license-status="{$license_status}" />

                                    <em class="{$class_prefix}content-main-license-note">{$label_license_note}</em>
                                    {$license_activate_button}{$license_deactivate_button}
                                </fieldset>
                            </form></div>
                        </div>
                    </div>

                </div>

                <div class="{$class_prefix}content-overlay">
                    <div class="{$class_prefix}content-overlay-design-pages">
                        <a href="#" class="go-back-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19.9019 11H7.79167L13.3542 5.41L11.9412 4L3.98047 12L11.9412 20L13.3443 18.59L7.79167 13H19.9019V11Z"/>
                            </svg>

                            {$label_go_back}
                        </a>

                        <div class="{$class_prefix}content-container">
                            <div class="{$class_prefix}content-overlay-design-pages-left-pane">
                                <h5 class="{$class_prefix}content-overlay-design-pages-title"></h5>
                                <a href="#" rel="noopener" class="{$class_prefix}content-overlay-design-import-demo-content button button-primary">{$label_import_button}</a>&nbsp;&nbsp;
                                <a href="#" target="_blank" rel="noopener" class="{$class_prefix}content-overlay-design-pages-preview-link button button-secondary">{$label_live_preview}</a>

                                <h6 class="{$class_prefix}content-overlay-design-pages-subhead">{$label_page_preview}</h6>
                                <ul class="{$class_prefix}content-overlay-design-pages-thumbs"></ul>
                            </div>

                            <div class="{$class_prefix}content-overlay-design-pages-right-pane"><img src="" /></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="{$class_prefix}footer">
            <h3 class="{$class_prefix}footer-logo"><a href="https://www.wpzoom.com/" title="$label_wpzoom">$label_wpzoom</a></h3>

            <ul class="{$class_prefix}footer-links">
                <li class="{$class_prefix}footer-links-themes"><a href="https://www.wpzoom.com/themes/" target="_blank"  title="$label_themes">$label_themes</a></li>
                <li class="{$class_prefix}footer-links-plugins"><a href="https://www.wpzoom.com/plugins/" target="_blank"  title="$label_plugins">$label_plugins</a></li>
                <li class="{$class_prefix}footer-links-blog"><a href="https://www.wpzoom.com/blog/" target="_blank" title="$label_blog">$label_blog</a></li>
                <li class="{$class_prefix}footer-links-support"><a href="https://www.wpzoom.com/support/" target="_blank" title="$label_support">$label_support</a></li>
            </ul>

            <div class="wpz-onboard_footer-social">
                <a href="https://x.com/wpzoom" target="_blank" title="$label_follow_button1"><svg role="img" width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>X</title><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/></svg></a>&nbsp;&nbsp;
                <a href="https://facebook.com/wpzoom" target="_blank" title="$label_follow_button2"><svg role="img"  width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Facebook</title><path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z"/></svg></a>&nbsp;&nbsp;
                <a href="https://instagram.com/wpzoom" target="_blank" title="$label_follow_button3"><svg role="img" width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Instagram</title><path d="M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077"/></svg></a>
            </div>
        </div>
HERE;
    }

    /**
     * Returns the markup for Step 1 of the onboarding process.
     *
     * @return string
     */
    public function step_1_content() {
        $plugins  = apply_filters( 'wpz_onboarding_sort_required_plugins', WPZOOM_TGMPA_Assistance::get_instance()->get_plugins() );
        $output   = '';
        $button   = '';
        $post_url = esc_url( admin_url( 'admin-post.php' ) );
        $nonce    = wp_create_nonce( 'wpzoom_required_plugins' );

        $disable_buttons = false;
        $notice_count    = $this->get_compatibilities( array( 'count' => true ) );

        // Disable buttons.
        if ( ! $notice_count['install_required'] &&
            ! $notice_count['install_recommended'] &&
            ! $notice_count['activate_recommended'] &&
            ! $notice_count['activate_required'] ) {
            $disable_buttons = true;
        }

        if ( ! empty( $plugins ) ) {
            foreach ( $plugins as $plugin ) {

                $slug   = $plugin['slug'];
                $type   = $this->get_plugin_advise_type_text( $plugin['required'] );
                $status = $this->get_plugin_status_text( $slug );
                $data   = $this->get_plugin_data( $slug );

                $badge      = '';
                $classnames = array();

                if ( ! empty( $data ) ) {
                    $name      = $data->name;
                    $desc      = $data->short_description;
                    $installed = $this->tgmpa_assistance->is_plugin_installed( $slug );
                    $activated = $this->tgmpa_assistance->is_plugin_active( $slug );
                    $disabled  = $activated ? 'disabled' : '';
                    $plugin_data = '';

                    $classnames[] = 'plugin_' . $slug;

                    if( isset( $plugin['demos'] ) ) {
                        foreach( $plugin['demos'] as $demo ) {
                            $classnames[] =  'required-demo-' . $demo;
                        }
                        $plugin_data = 'data-plugin-sortable="true"';
                    }

                    if ( $installed ) {
                        $classnames[] = 'plugin-status_' . preg_replace( '/\W+/', '-', strtolower( wp_strip_all_tags( $status ) ) );
                        $badge       .= "<span class=\"plugin-badge\">{$status}</span>";
                    }

                    if ( ! $activated ) {
                        $classnames[] = 'plugin-level_' . preg_replace( '/\W+/', '-', strtolower( wp_strip_all_tags( $type ) ) );
                        $badge       .= "<span class=\"plugin-badge\">{$type}</span>";
                    }

                    // Prepare item class names.
                    $class = implode( ' ', $classnames );

                    $output .= <<<HERE
                    <li class="{$class}" {$plugin_data}>
                        <h5 class="plugin-name"><label><input type="checkbox" name="required_plugins[]" value="{$slug}" checked {$disabled} /> {$name}</label>{$badge}</h5>
                        <p>{$desc}</p>
                    </li>
HERE;
                }
            }

            $button  = '<input type="submit" name="button_submit" value="' . __( 'Install &amp; Activate', 'wpzoom' ) . '" class="button button-primary" ' . disabled( $disable_buttons, true, false ) . '/>';
            $button .= '<input type="button" name="button_checkall" value="' . __( 'Select all', 'wpzoom' ) . '" class="button button-secondary" style="margin-left:1em" ' . disabled( $disable_buttons, true, false ) . '/>';
        } else {
            $output .= '<li class="no-required-plugins"><h5><span class="dashicons dashicons-dismiss"></span> ' . __( 'There are no required plugins&hellip;', 'wpzoom' ) . '</h5></li>';
        }

        return <<<HERE
        <form method="post" action="{$post_url}">
            <fieldset>
                <input type="hidden" name="action" value="wpzoom_required_plugins" />
                <input type="hidden" name="wpzoom_required_plugins_nonce" value="{$nonce}" />

                <ul id="plugins-list" class="plugins-list">{$output}</ul>

                {$button}
            </fieldset>
        </form>
HERE;
    }

    public function get_selected_design_builder( $design_id ) {

        if( ! empty( $design_id ) ) {
            $design_id = preg_replace( '/^inspiro-/', '', $design_id );
        }

        $designs  = $this->get_theme_designs();
        $results = [];

        foreach ($designs as $design_key => $design) {
            if ( isset( $design['builders'] ) ) {
                foreach ( $design['builders'] as $builder_key => $builder_id ) {
                    if ( $builder_id == $design_id ) {
                        $results = [
                            'design_key'  => $design_key,
                            'builder_key' => $builder_key
                        ];
                    }
                    continue;
                }
            }
        }

        return $results;

    }

    /**
     * Returns the markup for Step Builder of the onboarding process.
     *
     * @return string
     */
    public function step_builders() {

        $output = '';

        $builders_xml = array();
        $demos = get_demos_details();
        $selected_demos = $demos['selected'];

        $designs  = $this->get_theme_designs();
        $selected_design = $this->get_selected_design();
        $selected_builder_design = $this->get_selected_design_builder( $selected_design );
        $checked_key = 'elementor';
        if( isset( $selected_builder_design['design_key'] ) ) {
            $selected_design = $selected_builder_design['design_key'];
            $checked_key     = $selected_builder_design['builder_key'];
        }
        $selected_data = $designs[ $selected_design ];

        if( ! isset( $selected_data['builders'] ) ) {
            return '';
        }

        foreach( $selected_data['builders'] as $key => $builder ) {

            $design_id   = count( $selected_data['builders'] ) > 1 ? $this->get_design_id( $builder ) : $selected_demos;
            $design_name = isset( $designs[ $builder ]['name'] ) ? $designs[ $builder ]['name'] : $selected_data['name'];
            $builders_xml = 'https://www.wpzoom.com/downloads/xml/' . $design_id . '.xml';
            $label = $key === 'elementor' ? 'Elementor' : 'Block Editor (Gutenberg)';
            $checked = $key == $checked_key ? 'checked' : '';

            $output .= <<<HERE
                        <radio class="{$key}-builder">
                            <input type="radio" data-theme-design="{$builder}" data-design-id="{$design_id}" data-design-name="{$design_name}" id="$key" name="builder" value="{$builders_xml}" {$checked}>
                            <label for="$key">{$label}</label>
                        </radio>
                        HERE;
        }


        return $output;

    }



    /**
     * Returns the markup for Step 2 of the onboarding process.
     *
     * @return string
     */
    public function step_2_content() {
        $designs  = $this->get_theme_designs();
        $output   = '';
        $post_url = esc_url( admin_url( 'admin-post.php' ) );
        $nonce    = wp_create_nonce( 'wpzoom_theme_design' );

        extract( $this->strings ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

        $attr_live_preview = esc_attr( $label_live_preview );
        $attr_view_pages   = esc_attr( $label_view_pages );

        if ( ! empty( $designs ) ) {
            $array_designs_keys = array_keys( $designs );
            $template_data      = $this->get_template_data();
            $imported_demo_id   = $this->get_imported_demo_id();

            foreach ( $designs as $id => $details ) {
                $selected_design = ( is_array( $template_data ) && isset( $template_data['name'] ) ? $template_data['name'] : $array_designs_keys[0] );
                $wxr_url         = ( is_array( $template_data ) && isset( $template_data['wxr_url'] ) ? $template_data['wxr_url'] : '' );
                $wxr_type        = ( is_array( $template_data ) && isset( $template_data['wxr_type'] ) ? $template_data['wxr_type'] : '' );
                $checked         = $id === $selected_design ? 'checked' : '';
                $name            = esc_html( $details['name'] );
                $name_attr       = esc_attr( $name );
                $thumbnail_url   = esc_url( $details['thumbnail'] );
                $preview_url     = esc_url( $details['preview_url'] );
                $classnames      = "design_{$id}";
                $design_id       = $this->get_design_id( $id );
                $delete_imported = '';
                $builders_data   = array();

                if( isset( $details['builders'] ) && is_array( $details['builders'] ) ) {
                    foreach( $details['builders'] as $key => $builder ) {
                        $builders_data[ $key ] = array(
                            'id'        => $builder,
                            'name'      =>  $designs[ $builder ]['name'],
                            'design_id' => $this->get_design_id( $builder ),
                        );
                    }
                }

                $builders = ! empty( $builders_data ) ? wp_json_encode( $builders_data ) : array();
                $encoded_builders = ! empty( $builders ) ? "data-builders-support='{$builders}'" : '';

                if( isset( $details['is_hidden'] ) ) {
                    $classnames .= ' hidden';
                }

                if( isset( $details['is_block'] ) ) {
                    $classnames .= ' is-block-design';
                }

                $supported_by = '';
                if( isset( $details['supported_by'] ) ) {

                    $icons = '';
                    $icons_dir = WPZOOM::get_root_uri() . '/components/onboarding/assets/icons/';

                    if( is_array( $details['supported_by'] ) ) {
                        foreach( $details['supported_by'] as $builder ) {
                            $icons .= '<img class="wpzoom-icon-' . esc_attr( $builder ) . '" src="' . $icons_dir . $builder . '.svg" alt="' . esc_attr( $builder ) . '" title="' . esc_attr( $builder ) . '" />';
                        }
                    } else {
                        $icons .= '<img class="wpzoom-icon-' . esc_attr( $details['supported_by'] ) . '" src="' . $icons_dir . $details['supported_by'] . '.svg" alt="' . esc_attr( $details['supported_by'] ) . '" title="' . esc_attr( $details['supported_by'] ) . '" />';
                    }

                    $supported_by = sprintf( '<span class="supported-by">%s</span>',
                        esc_html__( 'Available for:', 'wpzoom' ) . ' ' . $icons
                    );
                }

                $selected_builder_design = $this->get_selected_design_builder( $selected_design );
                if( isset( $selected_builder_design['design_key'] ) ) {
                    $selected_design = $selected_builder_design['design_key'];
                }

                $imported_builder_design = $this->get_selected_design_builder( $imported_demo_id );
                if( isset( $imported_builder_design['design_key'] ) ) {
                    $imported_demo_id =  $this->get_design_id( $imported_builder_design['design_key'] );
                }

                if ( $id !== $selected_design ) {
                    $label_selected = $this->strings['localize_strings']['select_template'];
                } elseif ( $design_id === $imported_demo_id ) {
                    $classnames     .= ' selected-template imported-demo-content';
                    $label_selected  = $this->strings['localize_strings']['imported'];
                    $delete_imported = '<a href="#" class="delete-imported-demo-content" title="' . esc_html__( 'Delete imported demo content', 'wpzoom' ) . '" data-design="' . esc_attr( $id ) . '" data-wxr-url="' . esc_url( $wxr_url ) . '" data-wxr-type="' . esc_attr( $wxr_type ) . '"></a>';
                } else {
                    $classnames    .= ' selected-template';
                    $label_selected = $this->strings['label_selected'];
                }

                $output .= <<<HERE
                <li class="{$classnames}" data-design-id="{$design_id}" {$encoded_builders}>
                    <figure title="{$name_attr}">
                        <div class="preview-thumbnail" data-bg="{$thumbnail_url}"><button type="button" class="button-select-template">{$label_selected}</button></div>
                        <figcaption>
                            <h5>{$name}</h5>
                            {$supported_by}
                            {$delete_imported}
                            <p>
                                <a href="{$preview_url}" target="_blank" class="button button-primary" rel="noopener" title="{$attr_live_preview}">{$label_live_preview}</a>
                                <a href="#" title="{$attr_view_pages}" class="view-pages-open button button-secondary-gray">{$label_view_pages}</a>
                            </p>
                            <input type="radio" name="theme_designs" value="{$id}" {$checked} />
                        </figcaption>
                    </figure>
                </li>
HERE;
            }
        } else {
            $output .= '<li class="no-theme-designs"><h5><span class="dashicons dashicons-dismiss"></span> ' . __( 'There are no theme designs&hellip;', 'wpzoom' ) . '</h5></li>';
        }

        return <<<HERE
        <form method="post" action="{$post_url}">
            <fieldset>
                <input type="hidden" name="action" value="wpzoom_theme_design" />
                <input type="hidden" name="wpzoom_theme_design_nonce" value="{$nonce}" />

                <ul>{$output}</ul>
            </fieldset>
        </form>
HERE;
    }

    /**
     * Returns the markup for Step 3 of the onboarding process.
     *
     * @return string
     */
    public function step_3_content() {
        $output              = '';
        $class_prefix        = $this->classname_prefix;
        $designs             = $this->get_theme_designs();
        $label_template      = $this->strings['label_template'];
        $label_import_button = $this->strings['label_import_button'];

        extract( $this->strings ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

        $import_button_disabled = '';
        $array_designs_keys     = array_keys( $designs );
        $template               = option::get( 'layout-site' ) ?: $array_designs_keys[0]; // phpcs:ignore WordPress.PHP.DisallowShortTernary
        $template_name          = ( isset( $designs[ $template ] ) ? esc_html( $designs[ $template ]['name'] ) : $template );
        $compatibilities        = $this->get_compatibilities();
        $compatibility_notice   = '';
        $notice_items           = '';
        $notice_actions         = array();
        $recommended_notice     = 0;
        $template_data          = $this->get_template_data();

        $selected_design = ( is_array( $template_data ) && isset( $template_data['name'] ) ? $template_data['name'] : $template );
        $wxr_type        = ( is_array( $template_data ) && isset( $template_data['wxr_type'] ) ? $template_data['wxr_type'] : 'invalid' );
        $wxr_url         = ( is_array( $template_data ) && isset( $template_data['wxr_url'] ) ? $template_data['wxr_url'] : '' );

        foreach ( $compatibilities as $type => $compatibility ) {
            if ( ! empty( $compatibility ) ) {
                if ( 'errors' === $type ) {
                    $status = __( 'Required', 'wpzoom' );
                    $level  = 'required';
                } else {
                    $status             = __( 'Recommended', 'wpzoom' );
                    $level              = 'recommended';
                    $recommended_notice = count( $compatibility );
                }
                if ( empty( $compatibility_notice ) ) {
                    $compatibility_notice .= '<ul>##LIST##</ul>';
                }
                foreach ( $compatibility as $prop => $data ) {
                    $notice_item  = '<h3>' . $data['title'] . '<span class="plugin-badge">' . $status . '</span></h3>';
                    $notice_item .= $data['description'];

                    $notice_items .= WPZOOM_Onboarding_Utils::wrap_in_li(
                        $notice_item,
                        array(
                            'class'       => "plugin-level_{$level}",
                            'data-notice' => $prop,
                        )
                    );
                }
            }
        }

        if ( $recommended_notice > 0 ) {
            $link_template                      = '<a id="wpz-onboard-skip-notice" href="#">%s</a>';
            $notice_actions['skip-recommended'] = sprintf(
                $link_template,
                translate_nooped_plural( $this->strings['notice_skip_recommended_link'], $recommended_notice, 'wpzoom' )
            );
        }

        if ( ! empty( $compatibility_notice ) ) {
            $import_button_disabled = 'disabled';
            $compatibility_notice   = str_replace( '##LIST##', $notice_items, $compatibility_notice );
            $compatibility_notice  .= $notice_actions['skip-recommended'];

            $output .= <<<HERE
            <div class="{$class_prefix}notice">
                {$compatibility_notice}
            </div>
HERE;
        }

        if ( ! $wxr_url ) {
            $import_button_disabled = 'disabled';
            $label_import_button    = esc_html__( 'Invalid .xml URL provided', 'wpzoom' );
        }

        $output .= <<<HERE
        <h3 class="{$class_prefix}selected-template">{$label_template} <strong>{$template_name}</strong></h3>
        <p class="{$class_prefix}import-button-wrap">
            <a href="#" class="button button-primary {$import_button_disabled} {$class_prefix}import-button">{$label_import_button}</a>
            <input type="hidden" name="wpzoom-selected-design" value="{$selected_design}"/>
            <input type="hidden" name="wpzoom-wxr-url" value="{$wxr_url}"/>
            <input type="hidden" name="wpzoom-wxr-type" value="{$wxr_type}"/>
        </p>
HERE;

        return $output;
    }

    /**
     * Import Compatibility Errors
     *
     * @return mixed
     */
    public function get_compatibilities_data() {
        return array(
            'xmlreader'                       => array(
                'title'       => esc_html__( 'XMLReader Support Missing', 'wpzoom' ),
                /* translators: %s doc link. */
                'description' => '<p>' . esc_html__( "You're close to importing the template. To complete the process, enable XMLReader support on your website.", 'wpzoom' ) . '</p><p>' . esc_html__( 'Just get in touch with your service administrator and request them to enable XMLReader on your website. Once this is done you can try importing template again.', 'wpzoom' ) . '</p>',
            ),
            'child-theme'                     => array(
                'title'       => esc_html__( 'Child Theme is active', 'wpzoom' ),
                /* translators: %s Parent theme name. */
                'description' => '<p>' . sprintf( esc_html__( 'Please switch to the parent theme %s to import the demo content.', 'wpzoom' ), $this->theme->get( 'Name' ) ) . '</p><p>' . esc_html__( 'The importer works only when the parent theme is active. It doesn\'t support Child Themes or themes which folders were renamed to something different.', 'wpzoom' ) . '</p>',
            ),
            'wp-debug'                        => array(
                'title'       => esc_html__( 'Disable Debug Mode', 'wpzoom' ),
                /* translators: %s doc link. */
                'description' => '<p>' . esc_html__( 'WordPress debug mode is currently enabled on your website. With this, any errors from third-party plugins might affect the import process.', 'wpzoom' ) . '</p><p>' . esc_html__( 'Please disable it to continue importing demo content. To do so, you can add the following code into the wp-config.php file.', 'wpzoom' ) . '</p><p><code>define(\'WP_DEBUG\', false);</code></p>',
            ),
            'image-resizing-not-supported'    => array(
                'title'       => esc_html__( "Image Editor doesn't support image resizing", 'wpzoom' ),
                'description' => esc_html__( "The regenerate attachments tool won't be able to do anything because your server doesn't support image editing which means that WordPress can't create thumbnail images. Please ask your host to install the Imagick or GD PHP extensions.", 'wpzoom' ),
            ),
            'notice_ask_to_update_maybe'      => array(
                'title'       => '', // retrieve title from $this->srtings.
                /* translators: %s update page link. */
                'description' => '<p>##LIST##<p>' . sprintf( __( 'It is recommended that you <a href="%s" target="_blank">update</a> all plugins before importing the demo content.', 'wpzoom' ), esc_url( network_admin_url( 'update-core.php' ) ) ) . '</p>',
            ),
            'notice_ask_to_update'            => array(
                'title'       => '', // retrieve title from $this->srtings.
                /* translators: %s update page link. */
                'description' => '<p>##LIST##<p>' . sprintf( __( 'Please <a href="%s" target="_blank">update</a> all plugins before importing the demo content. Skipping this step might break the template design/feature.', 'wpzoom' ), esc_url( network_admin_url( 'update-core.php' ) ) ) . '</p>',
            ),
            'notice_can_install_required'     => array(
                'title'       => '', // retrieve title from $this->srtings.
                /* translators: %s required plugins. */
                'description' => '<p><strong>' . esc_html__( 'IMPORTANT', 'wpzoom' ) . ':</strong> ##LIST##</p>',
            ),
            'notice_can_activate_required'    => array(
                'title'       => '', // retrieve title from $this->srtings.
                /* translators: %s required plugins. */
                'description' => '<p>##LIST##</p>',
            ),
            'notice_can_install_recommended'  => array(
                'title'       => '', // retrieve title from $this->srtings.
                /* translators: %s required plugins. */
                'description' => '<p>##LIST##</p>',
            ),
            'notice_can_activate_recommended' => array(
                'title'       => '', // retrieve title from $this->srtings.
                'description' => '<p>##LIST##</p>',
            ),
            'empty_license_key'               => array(
                'title'       => esc_html__( 'Activate Your License Key', 'wpzoom' ),
                /* translators: %1$s License tab url. %2$s WPZOOM Members Area -> Licenses */
                'description' => '<p>' . sprintf( __( 'Please %1$s and activate your license key to import the demo content. You can find your license in the <a href="%2$s" target="_blank">Members Area → License Keys</a>.', 'wpzoom' ), '<a href="#license" id="license-tab-link">' . esc_html_x( 'enter', 'License text link', 'wpzoom' ) . '</a>', esc_url( 'https://www.wpzoom.com/account/licenses/' ) ) . '</p>',
            ),
            'inactive_license_key'            => array(
                'title'       => esc_html__( 'Inactive License Key', 'wpzoom' ),
                /* translators: %s License tab url. */
                'description' => '<p>' . sprintf( __( 'Please %s your license key to import the demo content.', 'wpzoom' ), '<a href="#license" id="license-tab-link">' . esc_html_x( 'activate', 'License text link', 'wpzoom' ) . '</a>' ) . '</p>',
            ),
            'expired_license_key'             => array(
                'title'       => esc_html__( 'Expired License Key', 'wpzoom' ),
                /* translators: %1$s License tab url. %2$s Renew license url. */
                'description' => '<p>' . sprintf( __( "Your license key has been expired and now you can't import the demo content. Please %1\$s or %2\$s your license key to be able to import the demo content.", 'wpzoom' ), '<a href="#license" id="license-tab-link">' . esc_html_x( 'activate', 'License text link', 'wpzoom' ) . '</a>', '<a href="' . esc_url( WPZOOM_Onboarding_License::get_instance()->get_renewal_link() ) . '" target="_blank">' . esc_html_x( 'renew', 'License renew link', 'wpzoom' ) . '</a>' ) . '</p>',
            ),
            'woocomerce-enabled' => array(
                'title'       => esc_html__( 'WooCommerce needs to be disabled', 'wpzoom' ),
                'description' => '<p>' . esc_html__( 'The demo content can\'t be imported if WooCommerce is enabled. Please deactivate it temporarily and refresh this page.') . '</p>',
            ),
        );
    }

    /**
     * Get all compatibilities
     *
     * @param array $args Array of arguments to get.
     * @return array
     */
    public function get_compatibilities( $args = array() ) {
        // Run tgmpa register action when doing ajax request.
        if ( wp_doing_ajax() ) {
            do_action( 'tgmpa_register' );
        }

        $return  = array();
        $data    = $this->get_compatibilities_data();
        $plugins = apply_filters( 'wpz_onboarding_sort_required_plugins', WPZOOM_TGMPA_Assistance::get_instance()->get_plugins() );

        $compatibilities = array(
            'errors'   => array(),
            'warnings' => array(),
        );
        $message         = array();
        $count           = array(
            'plugin_group'         => 0,
            'install_required'     => 0,
            'install_recommended'  => 0,
            'update'               => 0,
            'update_maybe'         => 0,
            'activate_recommended' => 0,
            'activate_required'    => 0,
        );

        $license_key    = WPZOOM_Onboarding_License::get_instance()->get_license_key();
        $license_status = WPZOOM_Onboarding_License::get_instance()->get_license_status();
        if ( ! $license_key ) {
            $compatibilities['errors']['empty_license_key'] = $data['empty_license_key'];
        } else {
            if ( 'invalid' === $license_status || 'site_inactive' === $license_status || 'inactive' === $license_status ) {
                $compatibilities['errors']['inactive_license_key'] = $data['inactive_license_key'];
            } elseif ( 'expired' === $license_status ) {
                $compatibilities['errors']['expired_license_key'] = $data['expired_license_key'];
            } elseif ( 'valid' !== $license_status ) {
                $compatibilities['errors']['inactive_license_key'] = $data['inactive_license_key'];
            }
        }

        if ( ! empty( $plugins ) ) {
            foreach ( $plugins as $slug => $plugin ) {
                if ( $this->tgmpa_assistance->is_plugin_active( $slug ) && false === $this->tgmpa_assistance->does_plugin_have_update( $slug ) ) {
                    continue;
                }

                if ( ! $this->tgmpa_assistance->is_plugin_installed( $slug ) ) {
                    if ( current_user_can( 'install_plugins' ) ) {
                        if ( true === $plugin['required'] ) {
                            $count['install_required']               += 1;
                            $message['notice_can_install_required'][] = $slug;
                        } else {
                            $count['install_recommended']               += 1;
                            $message['notice_can_install_recommended'][] = $slug;
                        }
                    }
                } else {
                    if ( ! $this->tgmpa_assistance->is_plugin_active( $slug ) && $this->tgmpa_assistance->can_plugin_activate( $slug ) ) {
                        if ( current_user_can( 'activate_plugins' ) ) {
                            if ( true === $plugin['required'] ) {
                                $count['activate_required']               += 1;
                                $message['notice_can_activate_required'][] = $slug;
                            } else {
                                $count['activate_recommended']               += 1;
                                $message['notice_can_activate_recommended'][] = $slug;
                            }
                        }
                    }

                    if ( $this->tgmpa_assistance->does_plugin_require_update( $slug ) || false !== $this->tgmpa_assistance->does_plugin_have_update( $slug ) ) {
                        if ( current_user_can( 'update_plugins' ) ) {
                            if ( $this->tgmpa_assistance->does_plugin_require_update( $slug ) ) {
                                $count['update']                  += 1;
                                $message['notice_ask_to_update'][] = $slug;
                            } elseif ( false !== $this->tgmpa_assistance->does_plugin_have_update( $slug ) ) {
                                $count['update_maybe']                  += 1;
                                $message['notice_ask_to_update_maybe'][] = $slug;
                            }
                        }
                    }
                }
            }
            unset( $slug, $plugin );

            // If we have notices to display, we move forward.
            if ( ! empty( $message ) ) {
                krsort( $message ); // Sort messages.
                $rendered       = '';
                $rendered_title = '';

                if ( ! current_user_can( 'activate_plugins' ) && ! current_user_can( 'install_plugins' ) && ! current_user_can( 'update_plugins' ) ) {
                    $rendered = esc_html( $this->strings['notice_cannot_install_activate'] ) . ' ' . esc_html( $this->strings['contact_admin'] );
                } else {
                    // Render the individual message lines for the notice.
                    foreach ( $message as $type => $plugin_group ) {

                        if ( ! isset( $data[ $type ] ) ) {
                            continue;
                        }
                        $list_plugins = array();

                        // Get the external info link for a plugin if one is available.
                        foreach ( $plugin_group as $plugin_slug ) {
                            $plugin_data = $this->get_plugin_data( $plugin_slug );
                            if ( is_object( $plugin_data ) && isset( $plugin_data->name ) ) {
                                $list_plugins[] = WPZOOM_Onboarding_Utils::wrap_in_li( $plugin_data->name, array( 'data-plugin-slug' => $plugin_slug ) );
                            }
                        }
                        unset( $plugin_slug );

                        $count['plugin_group'] = count( $plugin_group );
                        $list                  = ! empty( $list_plugins ) ? WPZOOM_Onboarding_Utils::wrap_in_ul( implode( '', $list_plugins ) ) : '';

                        $rendered = sprintf(
                            translate_nooped_plural( $this->strings[ $type ], $count['plugin_group'], 'wpzoom' ),
                            $list,
                            $count['plugin_group']
                        );

                        $count_type = '';
                        $title_type = '';

                        if ( false !== strpos( $type, 'notice_can_' ) ) {
                            $count_type = str_replace( 'notice_can_', '', $type );
                            $title_type = str_replace( 'notice_can', 'title', $type );
                        } elseif ( false !== strpos( $type, 'notice_ask_to_' ) ) {
                            $count_type = str_replace( 'notice_ask_to_', '', $type );
                            $title_type = str_replace( 'notice_ask_to', 'title', $type );
                        }

                        if ( $title_type && isset( $this->strings[ $title_type ] ) && isset( $count[ $count_type ] ) ) {
                            $rendered_title = translate_nooped_plural( $this->strings[ $title_type ], $count[ $count_type ], 'wpzoom' );
                        }
                        if ( ! empty( $rendered_title ) ) {
                            $data[ $type ]['title'] = $rendered_title;
                        }
                        $data[ $type ]['description'] = str_replace( '##LIST##', $rendered, $data[ $type ]['description'] );

                        if ( 'notice_can_install_required' === $type || 'notice_can_activate_required' == $type ) {
                            $compatibilities['errors'][ $type ] = $data[ $type ];
                        } else {
                            $compatibilities['warnings'][ $type ] = $data[ $type ];
                        }
                    }
                    unset( $type, $plugin_group, $list_plugins, $list );
                }
            }
        }

        //Check if the WooCommerce plugin is enabled
        if( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
            $compatibilities['errors']['woocomerce-enabled'] = $data['woocomerce-enabled'];
        }

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $compatibilities['warnings']['wp-debug'] = $data['wp-debug'];
        }

        if ( ! wp_image_editor_supports( array( 'methods' => array( 'resize' ) ) ) ) {
            $compatibilities['warning']['image-resizing-not-supported'] = $data['image-resizing-not-supported'];
        }

        if ( ! class_exists( 'XMLReader' ) ) {
            $compatibilities['errors']['xmlreader'] = $data['xmlreader'];
        }

        if ( is_child_theme() ) {
            $compatibilities['errors']['child-theme'] = $data['child-theme'];
        }

        $return = $compatibilities;
        if ( isset( $args['errors'] ) ) {
            $return = $compatibilities['errors'];
        } elseif ( isset( $args['warnings'] ) ) {
            $return = $compatibilities['warnings'];
        } elseif ( isset( $args['count'] ) ) {
            $return = $count;
        } elseif ( isset( $args['message'] ) ) {
            $return = $message;
        }
        return $return;
    }

    /**
     * Handles AJAX requests to get all installed plugins.
     *
     * @return void
     */
    public function get_installed_plugins_ajax() {
        // phpcs:ignore
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpzoom_required_plugins' ) || ! current_user_can( 'install_plugins' ) ) {
            wp_send_json_error();
        }

        $installed_plugins = preg_replace( array( '/[\\/].+$/i', '/\.php$/i' ), '', array_keys( get_plugins() ) );
        wp_send_json_success( $installed_plugins );
    }

    /**
     * Handles AJAX requests to install plugins.
     *
     * @return void
     */
    public function plugin_install_ajax() {
        global $wp_filesystem;

        // phpcs:ignore
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpzoom_required_plugins' ) || ! current_user_can( 'install_plugins' ) || ! isset( $_POST['plugins'] ) || empty( $_POST['plugins'] ) ) {
            wp_send_json_error();
        }

        $plugins = array_filter( array_map( 'sanitize_key', (array) $_POST['plugins'] ) );

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );

        do_action( 'tgmpa_register' );

        $plugins_to_activate = array();
        $json_data           = array(
            'success' => array(),
            'error'   => array(),
        );

        // Grab the file paths for the selected & inactive plugins from the registration array.
        foreach ( $plugins as $slug ) {
            if ( $this->tgmpa_assistance->is_plugin_installed( $slug ) ) {
                if ( $this->tgmpa_assistance->can_plugin_activate( $slug ) ) {
                    $plugins_to_activate[] = $this->tgmpa_assistance->plugins[ $slug ]['file_path'];

                    $json_data['success'][ $slug ] = array(
                        'type'     => 'activate',
                        'filePath' => $this->tgmpa_assistance->plugins[ $slug ]['file_path'],
                    );
                }
            } else {
                $plugin_data = $this->get_plugin_data( $slug, array( 'clear_update_cache' => true ) );
                $source      = is_object( $plugin_data ) && isset( $plugin_data->download_link ) ? $plugin_data->download_link : $this->tgmpa_assistance->get_download_url( $slug );

                if ( $source ) {

                    $result = $upgrader->install( $source );

                    if ( is_wp_error( $result ) ) {
                        $result->add_data( array( 'status' => 500 ) );

                        return $result;
                    }

                    // This should be the same as $result above.
                    if ( is_wp_error( $skin->result ) ) {
                        $skin->result->add_data( array( 'status' => 500 ) );

                        return $skin->result;
                    }

                    if ( $skin->get_errors()->has_errors() ) {
                        $error = $skin->get_errors();
                        $error->add_data( array( 'status' => 500 ) );

                        return $error;
                    }

                    if ( is_null( $result ) ) {
                        // Pass through the error from WP_Filesystem if one was raised.
                        if ( $wp_filesystem instanceof WP_Filesystem_Base
                            && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors()
                        ) {
                            return new WP_Error(
                                'unable_to_connect_to_filesystem',
                                $wp_filesystem->errors->get_error_message(),
                                array( 'status' => 500 )
                            );
                        }

                        return new WP_Error(
                            'unable_to_connect_to_filesystem',
                            __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'wpzoom' ),
                            array( 'status' => 500 )
                        );
                    }

                    $file_path = $upgrader->plugin_info(); // Grab the plugin info from the Plugin_Upgrader method.

                    if ( ! $file_path ) {
                        return new WP_Error(
                            'unable_to_determine_installed_plugin',
                            __( 'Unable to determine what plugin was installed.', 'wpzoom' ),
                            array( 'status' => 500 )
                        );
                    }

                    if ( $this->tgmpa_assistance->can_plugin_activate( $slug ) ) {
                        $plugins_to_activate[] = $file_path;

                        $json_data['success'][ $slug ] = array(
                            'type'     => 'activate',
                            'filePath' => $file_path,
                        );
                    }
                } else {
                    $json_data['error'][ $slug ] = array(
                        'type'    => 'invalid_download_link',
                        'message' => __( 'Invalid download link.', 'wpzoom' ),
                    );
                }
            }

            $json_data['success'][ $slug ]['required'] = $this->tgmpa_assistance->plugins[ $slug ]['required'];

            if ( isset( $json_data['error'][ $slug ] ) ) {
                $json_data['error'][ $slug ]['required'] = $this->tgmpa_assistance->plugins[ $slug ]['required'];
            }
        }
        unset( $slug );

        // Return early if there are no plugins to activate.
        if ( empty( $plugins_to_activate ) ) {
            wp_send_json_error( esc_html__( 'No plugins are available to be activated at this time.', 'wpzoom' ) );
        }

        // Now we are good to go - let's start activating plugins.
        $activate = activate_plugins( $plugins_to_activate );

        if ( is_wp_error( $activate ) ) {
            $error_data = $activate->get_error_data();
            foreach ( $error_data as $plugin => $data ) {
                $slug = preg_replace( array( '/[\\/].+$/i', '/\.php$/i' ), '', $plugin );

                $json_data['error'][ $slug ]['message'] = wp_kses_post( $data->get_error_message() );
            }
        }

        wp_send_json_success( $json_data );
    }

    /**
     * Handles AJAX requests to get the details of a given theme design.
     *
     * @return void
     */
    public function get_theme_design_ajax() {
        // phpcs:ignore
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpzoom_theme_design' ) || ! current_user_can( 'manage_options' ) || ! isset( $_POST['design'] ) || empty( $_POST['design'] ) ) {
            wp_send_json_error();
        }

        $design  = sanitize_key( $_POST['design'] );
        $designs = $this->get_theme_designs();

        if ( array_key_exists( $design, $designs ) ) {
            wp_send_json_success( $designs[ $design ] );
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Receive plugin data by plugin slug.
     *
     * @param string $plugin_slug Plugin slug to get data for.
     * @param array  $args Other arguments for get a plugin data.
     * @return object|array The plugin data object on success (name, short_description, version, author). Otherwise empty array.
     */
    public function get_plugin_data( $plugin_slug, $args = array() ) {
        $defaults    = array(
            'clear_update_cache' => false,
        );
        $parsed_args = wp_parse_args( $args, $defaults );

        if ( $parsed_args['clear_update_cache'] ) {
            // Clear cache so we knows about the new plugin.
            $this->clear_plugins_cache();
        }

        $data               = new stdClass();
        $installed_plugins  = $this->tgmpa_assistance->get_plugins();
        $cache_plugins_data = WPZOOM_Onboarding_Utils::onboarding_cache_get( 'wpz_onboarding', 'plugins_data' );

        if ( ! $cache_plugins_data ) {
            $cache_plugins_data = array();
        }

        foreach ( $this->tgmpa_assistance->plugins as $slug => $plugin ) {
            if ( $plugin_slug === $slug ) {
                if ( isset( $cache_plugins_data[ $slug ] ) ) { // Get cached plugins data to prevent calling function plugins_api() on each page load.
                    $data = $cache_plugins_data[ $slug ];
                } elseif ( isset( $installed_plugins[ $plugin['file_path'] ] ) ) { // Get plugin data only for installed plugins.
                    $plugin_data = $installed_plugins[ $plugin['file_path'] ];

                    // Store plugin data object.
                    $data->name              = $plugin_data['Name'];
                    $data->slug              = $slug;
                    $data->short_description = $plugin_data['Description'];
                    $data->version           = $plugin_data['Version'];
                    $data->author            = sprintf( '<a href="%s">%s</a>', $plugin_data['AuthorURI'], $plugin_data['Author'] );
                    $data->download_link     = '';

                    $cache_plugins_data[ $slug ] = $data;
                } else { // Get plugin data for not installed yet plugins.
                    if ( ! function_exists( 'plugins_api' ) ) {
                        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
                    }
                    $api_data = plugins_api(
                        'plugin_information',
                        array(
                            'slug'   => $slug,
                            'fields' => array(
                                'short_description' => true,
                                'sections'          => false,
                            ),
                        )
                    );

                    if ( ! is_wp_error( $api_data ) ) {
                        $data->name              = $plugin['name'];
                        $data->slug              = $api_data->slug;
                        $data->short_description = $api_data->short_description;
                        $data->version           = $api_data->version;
                        $data->author            = $api_data->author;
                        $data->download_link     = $api_data->download_link;

                        // Cache plugin data.
                        $cache_plugins_data[ $slug ] = $data;
                    } elseif ( 'plugins_api_failed' === $api_data->get_error_code() ) { // Plugin not found.
                        $data->name              = $plugin['name'];
                        $data->slug              = $slug;
                        $data->short_description = '';

                        // Cache plugin data.
                        $cache_plugins_data[ $slug ] = $data;
                    }
                }
            }
        }

        // Update cached plugins data.
        if ( $parsed_args['clear_update_cache'] ) {
            WPZOOM_Onboarding_Utils::onboarding_cache_set( 'wpz_onboarding', $cache_plugins_data, 'plugins_data' );
        }

        // Check if empty data.
        $tmp = (array) $data;
        if ( empty( $tmp ) ) {
            return array();
        }

        return $data;
    }

    /**
     * Sort plugins by Required/Recommended type and by alphabetical plugin name within each type.
     *
     * @param array $items Plugins array.
     * @return array Sorted plugins items.
     */
    public function sort_plugins( $items ) {
        $type = array();
        $name = array();

        foreach ( $items as $i => $plugin ) {
            $type[ $i ] = $plugin['required'];
            $name[ $i ] = $plugin['name'];
        }

        array_multisort( $type, SORT_DESC, $name, SORT_ASC, $items );

        return $items;
    }

    /**
     * Receive theme designs registered to the filter 'wpz_onboarding_theme_designs'
     *
     * @param array $designs Custom theme designs.
     * @return array Filtered theme designs.
     */
    public function get_theme_designs( $designs = array() ) {
        $designs = array_filter( apply_filters( 'wpz_onboarding_theme_designs', $designs ) );
        foreach ( $designs as $design => $props ) {
            if ( isset( $props['preview_pages'] ) && is_array( $props['preview_pages'] ) ) {
                foreach ( $props['preview_pages'] as $key => $page ) {
                    /* translators: %1$s Page image src, %2$s Page title */
                    $designs[ $design ]['preview_pages'][ $key ]['preview_img'] = sprintf( '<img loading="lazy" src="%1$s" alt="%2$s"/>', esc_url( $page['fullsize'] ), esc_attr( $page['name'] ) );
                }
            }
        }
        return $designs;
    }

    /**
     * Get template data
     *
     * @param string $template_name Template name.
     * @return mixed The template data if exists, otherwise false.
     */
    public function get_template_data( $template_name = '' ) {
        $cache_template_data = WPZOOM_Onboarding_Utils::onboarding_cache_get( 'wpz_demo_import', 'template_data' );

        if ( ! $cache_template_data || ( is_array( $cache_template_data ) && ! $cache_template_data['wxr_url'] ) ) {
            // Set default design if template data cache is empty.
            $this->set_selected_design();

            // Overwrite cached value.
            $cache_template_data = WPZOOM_Onboarding_Utils::onboarding_cache_get( 'wpz_demo_import', 'template_data' );
        }

        if ( '' !== $template_name && $cache_template_data['name'] !== $template_name ) {
            return false;
        }

        return $cache_template_data;
    }

    /**
     * Get selected design
     *
     * @return string
     */
    public function get_selected_design() {
        $cache_template_data = WPZOOM_Onboarding_Utils::onboarding_cache_get( 'wpz_demo_import', 'template_data' );
        $designs             = $this->get_theme_designs();
        $array_designs_keys  = array_keys( $designs );
        $selected_design     = get_theme_mod( 'layout-site', $array_designs_keys[0] );

        if ( is_array( $cache_template_data ) ) {
            return $cache_template_data['name'];
        } else {
            return $selected_design;
        }
    }

    /**
     * Set selected design
     *
     * @param string $design The design slug to set as selected.
     * @return mixed The passed design if is not present in the designs array, otherwise return void.
     */
    public function set_selected_design( $design = '' ) {
        $designs            = $this->get_theme_designs();
        $array_designs_keys = array_keys( $designs );

        if ( empty( $design ) && isset( $array_designs_keys[0] ) ) {
            $design = option::get( 'layout-site' ) ?: $array_designs_keys[0]; // phpcs:ignore WordPress.PHP.DisallowShortTernary
        }
        if ( ! array_key_exists( $design, $designs ) ) {
            return $design;
        }

        $cache_template_data = WPZOOM_Onboarding_Utils::onboarding_cache_get( 'wpz_demo_import', 'template_data' );

        if ( ! $cache_template_data || ( is_array( $cache_template_data ) && ( $cache_template_data['name'] !== $design || ! $cache_template_data['wxr_url'] ) ) ) {
            option::set( 'layout-site', $design );
            set_theme_mod( 'wpz_multiple_demo_importer', $design );

            $design_id        = $this->get_design_id( $design );
            $imported_demo_id = $this->get_imported_demo_id();
            $xml_data         = get_demo_xml_data();

            if ( $xml_data['local']['response'] ) {
                $wxr_url  = esc_url( $xml_data['local']['url'] );
                $wxr_type = 'local';
            } elseif ( $xml_data['remote']['response'] ) {
                $wxr_url  = esc_url( $xml_data['remote']['url'] );
                $wxr_type = 'remote';
            } else {
                $wxr_url  = '';
                $wxr_type = 'invalid';
            }

            if ( is_child_theme() ) {
                $wxr_type = 'invalid';
                $wxr_url  = '';
            }

            $data = array(
                'name'     => $design,
                'wxr_type' => $wxr_type,
                'wxr_url'  => $wxr_url,
                'imported' => ( $design_id === $imported_demo_id ),
            );

            WPZOOM_Onboarding_Utils::onboarding_cache_set( 'wpz_demo_import', $data, 'template_data' );
        }
    }

    /**
     * Get the plugin required/recommended text string.
     *
     * @param string $required Plugin required setting.
     * @return string
     */
    protected function get_plugin_advise_type_text( $required ) {
        if ( true === $required ) {
            return __( 'Required', 'wpzoom' );
        }

        return __( 'Recommended', 'wpzoom' );
    }

    /**
     * Determine the plugin status message.
     *
     * @param string $slug Plugin slug.
     * @return string
     */
    protected function get_plugin_status_text( $slug ) {
        if ( ! $this->tgmpa_assistance->is_plugin_installed( $slug ) ) {
            return __( 'Not Installed', 'wpzoom' );
        }

        if ( ! $this->tgmpa_assistance->is_plugin_active( $slug ) ) {
            $install_status = __( 'Installed, Not Activated', 'wpzoom' );
        } else {
            $install_status = __( 'Active', 'wpzoom' );
        }

        return $install_status;
    }

    /**
     * Clears the template data before switch the theme.
     *
     * @return void
     */
    public function clear_template_data_cache() {
        option::delete('layout-site');
        set_theme_mod( 'wpz_multiple_demo_importer', '' );
        set_theme_mod( 'layout-site', '' );
        WPZOOM_Onboarding_Utils::onboarding_cache_delete( 'wpz_demo_import', 'template_data' );
    }

    /**
     * Clears the plugins cache used by WPZOOM_Onboarding::get_plugin_data().
     *
     * @return void
     */
    public function clear_plugins_cache() {
        WPZOOM_Onboarding_Utils::onboarding_cache_delete( 'wpz_onboarding', 'plugins_data' );
    }

    /**
     * Get imported demo id
     *
     * @return mixed Imported demo id or empty value if no one demo was imported, otherwise return false if the index not found in the demos data array.
     */
    public function get_imported_demo_id() {
        $theme_demos_data = get_demos_details();
        if ( isset( $theme_demos_data['imported'] ) ) {
            return $theme_demos_data['imported'];
        }
        return false;
    }

    /**
     * Get design id by provided design slug
     *
     * @param string $design_slug The registered design slug.
     * @return string
     */
    public function get_design_id( $design_slug ) {
        $design_id = $design_slug;

        // Get design id only if theme supports multiple designs.
        if ( current_theme_supports( 'wpz-multiple-demo-importer' ) ) {
            $theme_demos_data = get_demos_details();
            $demo_key         = array_search( $design_slug, array_column( $theme_demos_data['demos'], 'name' ) ); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_columnFound
            $design_id        = isset( $theme_demos_data['demos'][ $demo_key ] ) ? $theme_demos_data['demos'][ $demo_key ]['id'] : '';
        }
        return $design_id;
    }

    /**
     * Get design name by provided design slug
     *
     * @param string $design_slug The registered design slug.
     * @return string
     */
    public function get_design_name( $design_slug ) {
        $theme_designs = $this->get_theme_designs();
        if ( isset( $theme_designs[ $design_slug ] ) ) {
            return $theme_designs[ $design_slug ]['name'];
        }
        return false;
    }

    /**
     * Check if the demo design template has any demo with blocks
     *
     * @return bool
     */
    public function is_block_design() {
        $designs  = $this->get_theme_designs();
        foreach ( $designs as $design ) {
            if ( isset( $design['is_block'] ) && $design['is_block'] ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Unset option for Elementor if the demo doesn't support it
     *
     * @return array
     */
    public function demo_support_elementor( $advanced_settings ) {

        $plugins = WPZOOM_TGMPA_Assistance::get_instance()->get_plugins();
        $demo_support_elementor = array();

        if ( ! empty( $plugins ) ) {
            foreach ( $plugins as $plugin ) {
                if ( 'elementor' === $plugin['slug'] && isset( $plugin['demos'] ) ) {
                    $demo_support_elementor = $plugin['demos'];
                }
            }
        }

        $selected_design    = $this->get_selected_design();
        $selected_design_id = $this->get_design_id( $selected_design );

        if ( ! empty( $demo_support_elementor ) && ! in_array( $selected_design_id, $demo_support_elementor ) ) {
            $elementor_process = array_search( 'wpzoom-demo-importer_elementor-batch-process', array_column( $advanced_settings, 'id' ) );
            unset( $advanced_settings[ $elementor_process ] );
            $advanced_settings = array_values( $advanced_settings );
        }

        return $advanced_settings;

    }
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
WPZOOM_Onboarding::get_instance();
