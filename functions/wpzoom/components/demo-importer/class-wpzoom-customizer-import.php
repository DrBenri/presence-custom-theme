<?php
/**
 * Customizer Data importer class.
 *
 * @since  2.0.0
 * @package WPZOOM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Customizer Data importer class.
 *
 * @since  2.0.0
 */
class WPZOOM_Customizer_Import {

	/**
	 * Instance of WPZOOM_Customizer_Import
	 *
	 * @since  2.0.0
	 * @var WPZOOM_Customizer_Import
	 */
	private static $instance = null;

	/**
	 * Instantiate WPZOOM_Customizer_Import
	 *
	 * @since  2.0.0
	 * @return (Object) WPZOOM_Customizer_Import
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Import customizer options.
	 *
	 * @since  2.0.0
	 *
	 * @param  (Array) $options customizer options from the demo.
	 */
	public function import( $options ) {


		if( file_exists( $options ) && !empty( $options ) ) {

			$dat_file_content = file_get_contents( $options );

			// Get the settings data.
			$data = @unserialize( $dat_file_content );

			// Return if something is wrong with the data.
			if ( 'array' != gettype( $data ) || ! isset( $data['mods'] ) ) {
				return $data;
			}

			//Update Theme customizer settings.
			if ( isset( $data['wpzoom-settings'] ) ) {
				self::import_settings( $data['wpzoom-settings'] );
			}

			//Add Custom CSS.
			if ( isset( $data['custom-css'] ) ) {
				wp_update_custom_css_post( $data['custom-css'] );
			}

			// Import options.
			if ( isset( $data['options'] ) ) {
				foreach ( $data['options'] as $option_key => $option_value ) {
					update_option( $option_key, $option_value );
				}
			}

			// If wp_css is set then import it.
			if( function_exists( 'wp_update_custom_css_post' ) && isset( $data['wp_css'] ) && '' !== $data['wp_css'] ) {
				wp_update_custom_css_post( $data['wp_css'] );
			}

			// Import mods.
			foreach ( $data['mods'] as $key => $val ) {
				set_theme_mod( $key, $val );
			}

			return $dat_file_content;

		}
	}

	/**
	 * Import Theme Setting's
	 *
	 * Download & Import images from Theme Customizer Settings.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $options Theme Customizer setting array.
	 * @return void
	 */
	public static function import_settings( $options = array() ) {
		array_walk_recursive(
			$options,
			function ( &$value ) {
				if ( ! is_array( $value ) && zoom_is_valid_image( $value ) ) {
					$downloaded_image = WPZOOM_Demo_Image_Importer::get_instance()->import(
						array(
							'url' => $value,
							'id'  => 0,
						)
					);
					$value            = $downloaded_image['url'];
				}
			}
		);

		// Updated settings.
		update_option( 'wpzoom-settings', $options );
	}
}
