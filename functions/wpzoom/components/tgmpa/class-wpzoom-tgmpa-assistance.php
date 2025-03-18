<?php
/**
 * TGM Plugin Activation Assistance
 *
 * @since  2.0.0
 * @package WPZOOM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TGM Plugin Activation Assistance class.
 */
class WPZOOM_TGMPA_Assistance {
	/**
	 * TGMPA instance.
	 *
	 * @var object
	 */
	protected $tgmpa;

	/**
	 * Instance of WPZOOM_TGMPA_Assistance
	 *
	 * @var WPZOOM_TGMPA_Assistance
	 */
	private static $instance = null;

	/**
	 * Instance
	 *
	 * @return object
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
		// References TGMPA constructor.
		if ( class_exists( 'TGM_Plugin_Activation' ) ) {
			$this->tgmpa = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		}

		add_action( 'tgmpa_register', array( $this, 'hide_notices_from_onboarding' ) );
	}

	/**
	 * TGMPA instance.
	 *
	 * @return TGM_Plugin_Activation instance.
	 */
	public function tgmpa_instance() {
		return $this->tgmpa;
	}

	/**
	 * Hide notices displayed by TGMPA plugin from Onboarding page.
	 *
	 * @return void
	 */
	public function hide_notices_from_onboarding() {
		$current_screen = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if ( 'wpzoom_license' === $current_screen ) {
			$this->tgmpa->has_notices = false;
		}
	}

	/**
	 * Get all recommended plugins.
	 *
	 * @return array
	 */
	public function get_plugins() {
		$plugins = array();
		if ( is_object( $this->tgmpa ) && isset( $this->tgmpa->plugins ) ) {
			$plugins = $this->parse_plugins( $this->tgmpa->plugins );
		}
		return $plugins;
	}




	/**
	 * Merges theme defined plugins into defaults array.
	 *
	 * @param string|array|object $plugins  Value to merge with $defaults.
	 * @param array               $defaults Optional. Array that serves as the defaults.
	 *                                      Default empty array.
	 * @return array Merged theme defined plugins with defaults.
	 */
	public function parse_plugins( $plugins, $defaults = array() ) {
		
		$theme_plugins = apply_filters( 'zoom_register_theme_required_plugins', $plugins );

		if ( ! empty( $theme_plugins ) ) {
			// Fill array keys with slug.
			$theme_plugins = array_column( $theme_plugins, null, 'slug' );
		}
		if ( is_array( $defaults ) && $defaults ) {
			return array_merge( $defaults, $theme_plugins );
		}
		return $theme_plugins;
	}

	/**
	 * Get all inactive recommended plugins.
	 *
	 * @return array
	 */
	public function get_inactive_plugins() {
		$tgmpa_plugins    = $this->get_plugins();
		$inactive_plugins = array();
		foreach ( $tgmpa_plugins as $id => $plugin ) {
			if ( ! is_plugin_active( $plugin['file_path'] ) ) {
				$inactive_plugins[ $id ] = $plugin;
			}
		}
		return $inactive_plugins;
	}

	/**
	 * Check if plugin are present in the recommended plugins array.
	 *
	 * @param string $plugin_slug The plugin slug to check for.
	 * @return boolean
	 */
	public function is_recommended_plugin( $plugin_slug ) {
		$plugins = $this->get_plugins();
		return isset( $plugins[ $plugin_slug ] );
	}
}
