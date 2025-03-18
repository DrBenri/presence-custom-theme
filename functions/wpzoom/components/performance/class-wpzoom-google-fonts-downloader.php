<?php
/**
 * Download Google Fonts localy and create css file.
 *
 * @package WPZOOM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WPZOOM_Google_Fonts_Downloader
 *
 * @package WPZOOM
 * @subpackage Google Fonts Downloader
 */
class WPZOOM_Google_Fonts_Downloader {

	/**
	 * Google Fonts instance.
	 *
	 * @var object
	 */
	private static $google_fonts_url;

	/**
	 * Instance of WPZOOM_Google_Fonts_Downloader
	 *
	 * @var WPZOOM_Google_Fonts_Downloader
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

		add_filter( 'zoom_get_google_font_uri', array( $this, 'get_used_google_fonts' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_google_fonts_css' ), 99 );

	}

	public function get_used_google_fonts( $data ) {

		if( ! empty( $data ) ) {
			self::$google_fonts_url = $data;
			return array();
		}

		return $data;

	}


	public function enqueue_google_fonts_css() {

		global $wp_customizer;

		$wpz_theme_name = wp_get_theme( get_template() );

		require_once WPZOOM_INC . '/components/wptt-webfont-loader.php';
		$local_google_fonts_url = wptt_get_webfont_url( self::$google_fonts_url );

		if ( ! $wp_customizer ) {
			wp_enqueue_style( 
				'zoom-google-fonts', 
				$local_google_fonts_url, 
				array(), 
				$wpz_theme_name->get( 'Version' ),
				'all' 
			);
		}

	}

}

/**
 * Kicking this off by calling 'get_instance()' method
 */
WPZOOM_Google_Fonts_Downloader::get_instance();
