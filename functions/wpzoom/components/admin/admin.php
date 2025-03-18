<?php
/**
 * WPZOOM_Admin
 *
 * @package WPZOOM
 * @subpackage Admin
 */

new WPZOOM_Admin();

class WPZOOM_Admin {

	/**
	 * Initialize wp-admin options page
	 */
	public function __construct() {
		add_action( 'after_switch_theme', array( $this, 'start_page_redirect' ) );

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wpzoom_options' ) {
			add_action( 'init', array( 'WPZOOM_Admin_Settings_Page', 'init' ) );
		}

		add_action( 'admin_menu', array( $this, 'register_admin_pages' ) );
		add_action( 'admin_footer', array( $this, 'activate' ) );
		add_action( 'admin_init', array( $this, 'handle_demo_importer_redirect' ) );
		add_action( 'admin_footer', array( $this, 'demo_importer_menu_highlight' ) );

		add_action( 'wp_ajax_wpzoom_ajax_post', array( 'WPZOOM_Admin_Settings_Page', 'ajax_options' ) );
		add_action( 'wp_ajax_wpzoom_widgets_default', array( 'WPZOOM_Admin_Settings_Page', 'ajax_widgets_default' ) );
		add_action( 'wp_ajax_wpzoom_demo_content', array( 'WPZOOM_Admin_Settings_Page', 'ajax_demo_content' ) );
		add_action( 'wp_ajax_wpzoom_erase_demo_content', array( 'WPZOOM_Admin_Settings_Page', 'ajax_erase_demo_content' ) );
		add_action( 'wp_ajax_wpzoom_update_nav_menu_location', array( 'WPZOOM_Admin_Settings_Page', 'ajax_update_nav_menu_location' ) );

		add_action( 'admin_print_scripts-widgets.php', array( $this, 'widgets_styling_script' ) );
		add_action( 'admin_print_scripts-widgets.php', array( $this, 'widgets_styling_css' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'wpadmin_script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wpadmin_css' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_gutenberg_scripts' ) );

        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wpzoom_update' ) {
            add_action( 'admin_enqueue_scripts', array( $this, 'framework_update_css' ) );
        }
	}

	function start_page_redirect() {
		$ignored_themes = get_deprecated_themes();
		$redirect_page  = in_array( WPZOOM::$theme_raw_name, $ignored_themes ) ? 'wpzoom_options' : 'wpzoom_license';
		header( 'Location: admin.php?page=' . $redirect_page );
	}
	public function widgets_styling_script() {
		wp_enqueue_script( 'wpzoom_widgets_styling', WPZOOM::$assetsPath . '/js/widgets-styling.js', array( 'jquery' ) );
	}

	public function widgets_styling_css() {
		wp_enqueue_style( 'wpzoom_widgets_styling', WPZOOM::$assetsPath . '/css/widgets-styling.css' );
	}

    public function framework_update_css() {
        wp_enqueue_style( 'wpzoom-options', WPZOOM::$assetsPath . '/options.css', array( 'thickbox' ), WPZOOM::$wpzoomVersion );
    }

	public function wpadmin_script() {
		wp_enqueue_script( 'zoom-wp-admin', WPZOOM::$assetsPath . '/js/wp-admin.js', array( 'jquery', 'wp-util' ), WPZOOM::$wpzoomVersion );
		wp_localize_script(
			'zoom-wp-admin',
			'zoomFramework',
			array(
				'rootUri'   => WPZOOM::get_root_uri(),
				'assetsUri' => WPZOOM::get_assets_uri(),
			)
		);
		wp_enqueue_style( 'zoom-font-awesome', WPZOOM::$assetsPath . '/css/font-awesome.min.css' );
	}

	public function wpadmin_css() {
		wp_enqueue_style( 'zoom-wp-admin', WPZOOM::get_assets_uri() . '/css/wp-admin.css', array(), WPZOOM::$wpzoomVersion );
	}

	/**
	 * Load Gutenberg Metaboxes script compatibility
	 *
	 * @package WPZOOM
	 * @subpackage Admin
	 **/
	public function load_gutenberg_scripts() {
		if ( function_exists( 'gutenberg_get_block_categories' ) || function_exists( 'get_block_categories' ) ) {
			wp_enqueue_script(
				'zoom-wp-admin-gutenberg-metaboxes',
				WPZOOM::$assetsPath . '/js/admin.gutenberg-metabox-compatibility.js',
				array( 'jquery' ),
				WPZOOM::$wpzoomVersion
			);
		}
	}

	public function activate() {
		if ( option::get( 'wpzoom_activated' ) != 'yes' ) {
			option::set( 'wpzoom_activated', 'yes' );
			option::set( 'wpzoom_activated_time', time() );
		} else {
			$activated_time = option::get( 'wpzoom_activated_time' );
			if ( ( time() - $activated_time ) < 2592000 ) {
				return;
			}
		}

		option::set( 'wpzoom_activated_time', time() );

		$ignored_themes = get_deprecated_themes();

		if ( ! in_array( WPZOOM::$theme_raw_name, $ignored_themes ) ) {
			require_once WPZOOM_INC . '/pages/welcome.php';
		}
	}

	public function admin() {
		require_once WPZOOM_INC . '/pages/admin.php';
	}

	public function themes() {
		require_once WPZOOM_INC . '/pages/themes.php';
	}

	public function update() {
		require_once WPZOOM_INC . '/pages/update.php';
	}

	/**
	 * WPZOOM custom menu for wp-admin
	 */
	public function register_admin_pages() {
		add_menu_page( __( 'Page Title', 'wpzoom' ), __( 'WPZOOM', 'wpzoom' ), 'manage_options', 'wpzoom_options', array( $this, 'admin' ), 'none', 40 );

		add_submenu_page( 'wpzoom_options', __( 'WPZOOM', 'wpzoom' ), __( 'Theme Options', 'wpzoom' ), 'manage_options', 'wpzoom_options', array( $this, 'admin' ) );

        if ( file_exists( get_template_directory() . '/functions/customizer' ) ) {
            $customize_url = add_query_arg( 'return', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'customize.php' );
            add_submenu_page( 'wpzoom_options', __( 'Customize', 'wpzoom' ), __( 'Customize', 'wpzoom' ), 'customize', esc_url( $customize_url ) );
        }

		if ( option::is_on( 'framework_update_enable' ) ) {
			add_submenu_page( 'wpzoom_options', __( 'Update Framework', 'wpzoom' ), __( 'Update Framework', 'wpzoom' ), 'update_themes', 'wpzoom_update', array( $this, 'update' ) );
		}
	}

	/**
	 * Handle redirect to the Demo Importer tab
	 *
	 * @return void
	 */
	public function handle_demo_importer_redirect() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'wpzoom_demo_importer' ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wpzoom_license#demo-importer' ) );
			exit;
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] === 'wpzoom_license_key' ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wpzoom_license#license' ) );
			exit;
		}
	}

	/**
	 * Placeholder function for the submenu registration
	 *
	 * @return void
	 */
	public function redirect_to_demo_importer() {
		// This is just a placeholder function and should never be called
		// The actual redirect happens in handle_demo_importer_redirect()
	}

	/**
	 * Placeholder function for the license key submenu registration
	 *
	 * @return void
	 */
	public function redirect_to_license_key() {
		// This is just a placeholder function and should never be called
		// The actual redirect happens in handle_demo_importer_redirect()
	}

	/**
	 * Add JavaScript to highlight the Demo Importer menu item when the demo-importer tab is active
	 *
	 * @return void
	 */
	public function demo_importer_menu_highlight() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'wpzoom_license' ) {
			?>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				// Function to update menu highlighting based on hash
				function updateMenuHighlighting() {
					if (window.location.hash === '#demo-importer') {
						// Remove active class from all menu items
						$('#toplevel_page_wpzoom_options li.current').removeClass('current');

						// Add active class to Demo Importer menu item
						$('#toplevel_page_wpzoom_options a[href$="page=wpzoom_demo_importer"]').parent().addClass('current');
					} else if (window.location.hash === '#license') {
						// If license hash, highlight the License Key menu
						$('#toplevel_page_wpzoom_options li.current').removeClass('current');
						$('#toplevel_page_wpzoom_options a[href$="page=wpzoom_license_key"]').parent().addClass('current');
					} else if (window.location.hash === '' || window.location.hash === '#' || window.location.hash === '#dashboard') {
						// If no hash or dashboard hash, highlight the Dashboard menu
						$('#toplevel_page_wpzoom_options li.current').removeClass('current');
						$('#toplevel_page_wpzoom_options a[href$="page=wpzoom_license"]').parent().addClass('current');
					}
				}

				// Run on page load
				updateMenuHighlighting();

				// Run when hash changes (tab navigation)
				$(window).on('hashchange', function() {
					updateMenuHighlighting();
				});

				// Also handle tab clicks within the page
				$(document).on('click', '.wpz-onboard_tab a', function() {
					// Wait a moment for the hash to update
					setTimeout(updateMenuHighlighting, 100);
				});
			});
			</script>
			<?php
		}
	}
}
