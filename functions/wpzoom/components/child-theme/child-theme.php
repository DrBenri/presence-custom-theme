<?php
/**
 * Class WPZOOM Child Theme
 *
 * @since  2.0.0 Refactor class.
 *
 * @package WPZOOM
 * @subpackage Child Theme
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPZOOM Child Theme
 */
class ZOOM_Child_Theme {
	/**
	 * Current theme.
	 *
	 * @var object WP_Theme
	 */
	public $theme;

	/**
	 * Current theme slug.
	 *
	 * @var string Theme slug
	 */
	public $slug;

	/**
	 * The base path where parent theme is located.
	 *
	 * @var array $strings
	 */
	public $base_path = null;

	/**
	 * The base url where parent theme is located.
	 *
	 * @var array $strings
	 */
	public $base_url = null;

	/**
	 * Option name.
	 *
	 * @since 2.0.0
	 * @var string $option_name
	 */
	public $option_name;

	/**
	 * Instance of ZOOM_Child_Theme
	 *
	 * @since  2.0.0
	 * @var ZOOM_Child_Theme
	 */
	private static $instance = null;

	/**
	 * Instantiate ZOOM_Child_Theme
	 *
	 * @since  2.0.0
	 * @return (Object) ZOOM_Child_Theme.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since  2.0.0
	 */
	public function __construct() {
		$this->init();

		add_filter( 'zoom_option_files', array( $this, 'register_options' ) );
		add_action( 'wp_ajax_zoom_install_child_theme', array( $this, 'install_child_theme' ), 10, 0 );
		add_action( 'load-toplevel_page_wpzoom_options', array( $this, 'wpzoom_page_options_callback' ) );
		add_action( 'switch_theme', array( $this, 'switch_theme_update_mods' ) );
	}

	/**
	 * Register component options for zoom framework.
	 *
	 * @param  array $zoom_options Framework options.
	 * @return array
	 */
	public function register_options( $zoom_options ) {
		$child_theme_file_exists = $this->child_theme_file_exists();

		if ( $child_theme_file_exists ) {
			$zoom_options[] = sprintf( '%s/options.php', dirname( __FILE__ ) );
		}

		return $zoom_options;
	}

	/**
	 * Include admin scripts for wpzoom_option page.
	 *
	 * @return void
	 */
	public function wpzoom_page_options_callback() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'zoom-child-theme',
			$this->get_js_uri( 'general.js' ),
			array( 'jquery', 'underscore' ),
			WPZOOM::$wpzoomVersion, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			true
		);

		wp_localize_script(
			'zoom-child-theme',
			'zoomData',
			array(
				'_ajax_nonce' => wp_create_nonce( 'wpzoom-demo-importer' ),
			)
		);
	}

	/**
	 * Initialize all class variables.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {
		// Set arguments.
		$this->base_path = get_parent_theme_file_path();
		$this->base_url  = get_parent_theme_file_uri();

		// Retrieve a WP_Theme object.
		$this->theme = wp_get_theme();
		$this->slug  = strtolower( preg_replace( '#[^a-zA-Z-_]#', '', $this->theme->template ) );

		// Option name.
		$underscore        = sanitize_title_with_dashes( $this->slug );
		$this->option_name = 'zoom_' . $underscore . '_child';
	}

	/**
	 * Get child theme .zip file.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_zip_file() {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$list_files  = list_files( $this->base_path, 1 );
		$child_theme = $this->theme . ' Child';
		$filename    = sanitize_title( $child_theme );
		$template    = get_template() . '-child'; 
		$zip_file    = '';

		if ( $list_files ) {
			foreach ( $list_files as $file ) {
				if ( strpos( $file, $filename ) !== false ) {
					$zip_file = $file;
				}
				else if( strpos( $file, $template ) !== false ) {
					$zip_file = $file;
				}
			}
		}

		if ( ! $zip_file ) {
			/**
			 * The .zip file with child-theme doesn't exist in the theme root.
			 * In this case we need to check /functions folder.
			 */
			if ( file_exists( $this->base_path . '/functions' ) ) {
				$list_files = list_files( $this->base_path . '/functions', 1 );
				if ( $list_files ) {
					foreach ( $list_files as $file ) {
						if ( strpos( $file, $filename ) !== false ) {
							$zip_file = $file;
						}
					}
				}
			}
		}
		return $zip_file;
	}

	/**
	 * Get child theme slug.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_child_theme_slug() {
		return $this->slug . '-child';
	}

	/**
	 * Install the child theme via AJAX.
	 *
	 * @return void
	 */
	public function install_child_theme() {

		// Verify Nonce.
		check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

		$name                 = $this->theme . ' Child';
		$zip_file             = $this->get_zip_file();
		$child_theme_slug     = $this->get_child_theme_slug();
		$x_child_theme_slug   = get_template() . '-child';
		$auto_activate        = '';
		$keep_parent_settings = '';

		$location = isset( $_POST['location'] ) ? sanitize_text_field( $_POST['location'] ) : '';
		if ( 'zoomForm-tab-content' === $location ) {
			$auto_activate        = isset( $_POST['child_theme_auto_activate'] ) && 'true' === $_POST['child_theme_auto_activate'] ? 'on' : 'off';
			$keep_parent_settings = isset( $_POST['child_theme_keep_parent_settings'] ) && 'true' === $_POST['child_theme_keep_parent_settings'] ? 'on' : 'off';
		}
		if ( 'zoomForm-theme-setup' === $location ) {
			$advanced_settings_data = isset( $_POST['advanced_settings'] ) ? $_POST['advanced_settings'] : array();
			$auto_activate          = isset( $advanced_settings_data['child_theme_auto_activate'] ) && 'true' === $advanced_settings_data['child_theme_auto_activate'] ? 'on' : 'off';
			$keep_parent_settings   = isset( $advanced_settings_data['child_theme_keep_parent_settings'] ) && 'true' === $advanced_settings_data['child_theme_keep_parent_settings'] ? 'on' : 'off';
		}

		if ( $auto_activate ) {
			option::set( 'child_theme_auto_activate', $auto_activate );
		}
		if ( $keep_parent_settings ) {
			option::set( 'child_theme_keep_parent_settings', $keep_parent_settings );
		}

		$auto_activate = option::is_on( 'child_theme_auto_activate' );

		// Check if we don't have already child theme created.
		$themes_root = get_theme_root();
		$path        = $themes_root . '/' . $child_theme_slug;

		if ( ! file_exists( $path ) ) {
			$child_theme_file_exists = $this->child_theme_file_exists();
			$message                 = '';
			$debug                   = '';

			if ( ! $child_theme_file_exists ) {
				/* translators: %s The .zip file name */
				$message = sprintf( __( "The .zip file %s doesn't exists.", 'wpzoom' ), wp_basename( $zip_file ) );
				$debug   = __( 'The zip file could not be found in the parent theme directory.', 'wpzoom' );
			}

			// Unzip child theme file to themes folder.
			WP_Filesystem();
			$unzipfile = unzip_file( $zip_file, $themes_root );
			if ( is_wp_error( $unzipfile ) ) {
				$message = esc_html__( 'There was an error unzipping the file.', 'wpzoom' );
				/* translators: %s The .zip file name */
				$debug = sprintf( __( "The .zip file %s doesn't exist.", 'wpzoom' ), wp_basename( $zip_file ) );
			}

			if ( $message || $debug ) {
				wp_send_json_error(
					array(
						'done'    => 0,
						'message' => $message,
						'debug'   => $debug,
					)
				);
			}
		} else {
			$message = esc_html__( 'Your child theme has already been installed.', 'wpzoom' );
			$debug   = '';

			update_option( $this->option_name, $name );

			if ( $auto_activate ) {
				switch_theme( $child_theme_slug );
				$message = esc_html__( 'Your child theme has already been installed and is now activated.', 'wpzoom' );
				/* translators: %s The child theme slug */
				$debug = sprintf( __( 'The existing child theme %s was activated', 'wpzoom' ), $child_theme_slug );
			}

			wp_send_json_success(
				array(
					'done'    => 1,
					'message' => $message,
					'debug'   => $debug,
				)
			);
		}

		if ( $this->child_theme_exists() ) {
			$message = esc_html__( 'Your child theme has been installed.', 'wpzoom' );
			$debug   = '';

			update_option( $this->option_name, $name );

			if ( $auto_activate ) {
				switch_theme( $child_theme_slug );
				$message = esc_html__( 'Your child theme has been installed and is now activated.', 'wpzoom' );
				/* translators: %s The child theme slug */
				$debug = sprintf( __( 'The child theme %s was activated', 'wpzoom' ), $child_theme_slug );
			}

			wp_send_json_success(
				array(
					'done'    => 1,
					'message' => $message,
					'debug'   => $debug,
				)
			);
		}

		if( $this->x_child_theme_exists() ) {
			
			$message = esc_html__( 'Your child theme has been installed.', 'wpzoom' );
			$debug   = '';

			update_option( $this->option_name, $name );

			if ( $auto_activate ) {
				switch_theme( $x_child_theme_slug );
				$message = esc_html__( 'Your child theme has been installed and is now activated.', 'wpzoom' );
				/* translators: %s The child theme slug */
				$debug = sprintf( __( 'The child theme %s was activated', 'wpzoom' ), $x_child_theme_slug );
			}

			wp_send_json_success(
				array(
					'done'    => 1,
					'message' => $message,
					'debug'   => $debug,
				)
			);

		}

		wp_send_json_error(
			array(
				'done'    => 0,
				'message' => esc_html__( 'Something went wrong', 'wpzoom' ),
				/* translators: %s The child theme slug */
				'debug'   => sprintf( esc_html__( "The child theme %s doesn't exists", 'wpzoom' ), $child_theme_slug ),
			)
		);
	}

	/**
	 * Keep parent settings.
	 *
	 * @return void
	 */
	public function switch_theme_update_mods() {
		$keep_parent_settings = option::is_on( 'child_theme_keep_parent_settings' );

		if ( ! is_child_theme() && $keep_parent_settings ) {
			$mods = get_option( 'theme_mods_' . $this->slug );

			if ( false !== $mods ) {
				foreach ( (array) $mods as $mod => $value ) {
					set_theme_mod( $mod, $value );
				}
			}
		}
	}

	/**
	 * Get assets url.
	 *
	 * @param string $endpoint The file name.
	 * @return string
	 */
	public function get_assets_uri( $endpoint = '' ) {
		return WPZOOM::$wpzoomPath . '/components/child-theme/assets/' . $endpoint; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Get assets js url.
	 *
	 * @param string $endpoint The file name.
	 * @return string
	 */
	public function get_js_uri( $endpoint = '' ) {
		return $this->get_assets_uri( 'js/' . $endpoint );
	}

	/**
	 * Check child theme file exists.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function child_theme_file_exists() {
		$zip_file = $this->get_zip_file();
		return file_exists( $zip_file );
	}

	/**
	 * Check child theme exists.
	 *
	 * @return boolean
	 */
	public function child_theme_exists() {
		$stylesheet  = $this->get_child_theme_slug();
		$child_theme = wp_get_theme( $stylesheet );
		return method_exists( $child_theme, 'exists' ) && $child_theme->exists();
	}

	/**
	 * Check complex child theme exists.
	 *
	 * @return boolean
	 */
	public function x_child_theme_exists() {
		
		$child_theme_slug = get_template() . '-child';
		$child_theme = wp_get_theme( $child_theme_slug );
		
		return method_exists( $child_theme, 'exists' ) && $child_theme->exists();
	}

}

ZOOM_Child_Theme::instance();
