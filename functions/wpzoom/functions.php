<?php
/**
 * General WP and WPZOOM functions.
 *
 * @package WPZOOM
 */

define( 'WPZOOM_INC_URI', get_template_directory_uri() . '/wpzoom' );

if ( ! function_exists( 'get_deprecated_themes' ) ) {

	function get_deprecated_themes() {
		return array(
            'artistica',
			'bizpress',
			'bonpress',
			'business-bite',
			'cadabrapress',
			'convention',
            'wpzoom-chronicle',
			'wpzoom-convention',
			'delicious',
			'domestica',
			'edupress',
			'elegance',
			'eventina',
			'evertis',
			'gallery',
			'graphix',
			'horizon',
			'hotelia',
			'impulse',
			'medicus',
            'magnet',
            'modena',
			'magnific',
			'manifesto',
			'monograph',
			'newsley',
			'photoblog',
			'photoland',
			'photoria',
			'polaris',
			'prime',
            'petit',
            'seasons',
			'professional',
			'proudfolio',
			'pulse',
			'sensor',
			'splendid',
			'sportpress',
			'techcompass',
			'technologic',
			'telegraph',
			'virtuoso',
			'voyage',
            'vimes',
			'yamidoo-pro',
			'yamidoo_pro',
			'zenko',
			'elastik',
			'momentum',
			'insider',
			'magazine_explorer',
			'onplay',
			'daily_headlines',
			'litepress',
			'morning',
			'digital',
			'wpzoom-digital',
			'photoframe',
            'discovery',
            'wpzoom-expedition',
            'wpzoom-discovery',
		);
	}
}

function get_demo_xml_data( $target = 'selected' ) {
	$xml_data = array(
		'remote'         => array(
			'url'      => '',
			'response' => false,
		),
		'local'          => array(
			'url'      => '',
			'response' => false,
		),
		'is_child_theme' => is_child_theme(),
	);

	// Stop when child theme is active
	if ( is_child_theme() ) {
		return $xml_data;
	}

	$demos = get_demos_details();

	$url       = 'https://www.wpzoom.com/downloads/xml/' . $demos['selected'] . '.xml';
	$local_url = get_template_directory() . '/theme-includes/demo-content/' . $demos['selected'] . '.xml';

	if ( $target == 'imported' ) {
		$url       = 'https://www.wpzoom.com/downloads/xml/' . $demos['imported'] . '.xml';
		$local_url = get_template_directory() . '/theme-includes/demo-content/' . $demos['imported'] . '.xml';
	}

	// Check for local file
	if ( is_file( $local_url ) ) {
		$xml_data['local']['url']      = $local_url;
		$xml_data['local']['response'] = true;
	}
	// Check for remote file
	else {
		$transient_id = 'get_demo_xml_transient_' . $demos['theme'] . '_' . $demos['selected'];

		if ( $target == 'imported' ) {
			$transient_id = 'get_demo_xml_transient_' . $demos['theme'] . '_' . $demos['imported'];
		}

		$transient = get_site_transient( $transient_id );

		if ( ! $transient ) {
			$response      = wp_remote_get( esc_url_raw( $url ) );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( ! is_wp_error( $response ) && $response_code === 200 ) {
				$xml_data['remote']['url']      = $url;
				$xml_data['remote']['response'] = true;

				set_site_transient( $transient_id, $xml_data, YEAR_IN_SECONDS );

				$transient = $xml_data;
			}
		}

		if ( is_array( $transient ) ) {
			$xml_data = array_merge( $xml_data, $transient );
		}
	}

	$xml_data = apply_filters( 'wpzoom_before_get_demo_xml_data', $xml_data );

	return $xml_data;
}


/**
 * Get demo details
 *
 * @since   1.7.0
 *
 * @return  array   Demo details
 */
function get_demos_details() {
	$raw_theme_name = WPZOOM::$themeName;

	if ( current_theme_supports( 'wpz-theme-info' ) ) {
		$theme_info = get_theme_support( 'wpz-theme-info' );
		$theme_info = array_pop( $theme_info );

		if ( ! empty( $theme_info['name'] ) ) {
			$raw_theme_name = $theme_info['name'];
		}
	}

	$themeName = str_replace( array( '_', ' ' ), '-', strtolower( $raw_theme_name ) );

	$data = array(
		'demos'         => array(),
		'theme'         => $themeName,
		'selected'      => $themeName,
		'default'       => $themeName,
		'imported'      => get_theme_mod( 'wpz_demo_imported' ),
		'imported_date' => get_theme_mod( 'wpz_demo_imported_timestamp' ),
		'multiple-demo' => false,
	);

	$arr_keys = array( 'name', 'id', 'thumbnail' );

	if ( current_theme_supports( 'wpz-multiple-demo-importer' ) ) {
		$wrapped_demos = get_theme_support( 'wpz-multiple-demo-importer' );
		$demos         = array_pop( $wrapped_demos );
		$selected      = get_theme_mod( 'wpz_multiple_demo_importer' );

		// Check if demos array has needed keys
		// If not, we need to change array by pushing keys into new demos array
		foreach ( $demos['demos'] as $key => $demo ) {
			if ( ! is_array( $demo ) ) {
				unset( $demos['demos'][ $key ] );

				$demos['demos'][ $key ][ $arr_keys[0] ] = $demo; // name
				$demos['demos'][ $key ][ $arr_keys[1] ] = $themeName . '-' . $demo; // id
				$demos['demos'][ $key ][ $arr_keys[2] ] = ''; // thumbnail
			}
		}

		if ( empty( $selected ) && isset( $demos['default'] ) ) {
			$selected        = $demos['default'];
			$data['default'] = $demos['default'];
		}

		$data['demos']         = $demos['demos'];
		$data['multiple-demo'] = true;
		$data['selected']      = $themeName . '-' . $selected;
	}

	return $data;
}


if ( ! function_exists( 'zoom_array_key_exists' ) ) {
	function zoom_array_key_exists( $keys, $search_arr ) {
		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $search_arr ) ) {
				return false;
			}
		}

		return true;
	}
}

/**
 *
 * Hook function called after the erase demo content process has finished.
 */
if ( ! function_exists( 'zoom_after_erase_demo' ) ) {
	function zoom_after_erase_demo() {
		$demos = get_demos_details();

		remove_theme_mod( 'wpz_demo_imported' );
		remove_theme_mod( 'wpz_demo_imported_timestamp' );
		delete_option( 'wpzoom_' . $demos['imported'] . '_theme_setup_complete' );
	}

	add_action( 'erase_demo_end', 'zoom_after_erase_demo' );
}

/**
 *
 * Hook function called before add partial to the customizer.
 */
if ( ! function_exists( 'zoom_before_add_partial' ) ) {
	function zoom_before_add_partial( $wp, $setting_id ) {
		if ( ! is_object( $wp ) ) {
			return false;
		}

		$remove_partial = array( 'custom_logo' );

		if ( in_array( $setting_id, $remove_partial ) ) {
			$wp->selective_refresh->remove_partial( $setting_id );
		}
	}

	add_action( 'wpzoom_remove_partial', 'zoom_before_add_partial', 10, 2 );
}

function zoom_get_beauty_demo_title( $name ) {
	return ucwords( str_replace( array( '-', '_' ), ' ', $name ) );
}

/**
 * Get the ID of an attachment from its image URL.
 *
 * @author  Taken from reverted change to WordPress core http://core.trac.wordpress.org/ticket/23831
 *
 * @param   string $url The path to an image.
 *
 * @return  int|bool            ID of the attachment or 0 on failure.
 */

if ( ! function_exists( 'zoom_get_attachment_id_from_url' ) ) {
	function zoom_get_attachment_id_from_url( $url = '' ) {
		// If there is no url, return.
		if ( '' === $url ) {
			return false;
		}

		global $wpdb;
		$attachment_id = 0;

		// Function introduced in 4.0
		if ( function_exists( 'attachment_url_to_postid' ) ) {
			$attachment_id = absint( attachment_url_to_postid( $url ) );
			if ( 0 !== $attachment_id ) {
				return $attachment_id;
			}
		}

		// First try this
		if ( preg_match( '#\.[a-zA-Z0-9]+$#', $url ) ) {
			$sql           = $wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND guid = %s",
				esc_url_raw( $url )
			);
			$attachment_id = absint( $wpdb->get_var( $sql ) );

			if ( 0 !== $attachment_id ) {
				return $attachment_id;
			}
		}

		// Then try this
		$upload_dir_paths = wp_upload_dir();
		if ( false !== strpos( $url, $upload_dir_paths['baseurl'] ) ) {
			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $url );

			// Remove the upload path base directory from the attachment URL
			$url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$sql           = $wpdb->prepare(
				"SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'",
				esc_url_raw( $url )
			);
			$attachment_id = absint( $wpdb->get_var( $sql ) );
		}

		return $attachment_id;
	}
}

if ( ! function_exists( 'inject_wpzoom_plugins' ) ) :
	function inject_wpzoom_plugins( $res, $action, $args ) {

		// remove filter to avoid infinite loop.
		remove_filter( 'plugins_api_result', 'inject_wpzoom_plugins', 10, 3 );

		foreach (
			array(
				'social-icons-widget-by-wpzoom',
				'instagram-widget-by-wpzoom',
                'wpzoom-elementor-addons',
                'wpzoom-forms',
                'wpzoom-portfolio',
                'wpzoom-video-popup-block',
				'recipe-card-blocks-by-wpzoom',
                'block-patterns-for-food-bloggers',
                'customizer-reset-by-wpzoom',
                'secondary-product-image-for-woocommerce',
                'woocommerce',
                'wordpress-seo'
			) as $plugin_slug
		) {
			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $plugin_slug,
					'is_ssl' => is_ssl(),
					'fields' => array(
						'banners'           => true,
						'reviews'           => true,
						'downloaded'        => true,
						'active_installs'   => true,
						'icons'             => true,
						'short_description' => true,
					),
				)
			);

			if ( ! is_wp_error( $api ) ) {
				$res->plugins[] = $api;
			}
		}

		return $res;
	}
endif;

if ( ! function_exists( 'zoom_callback_for_featured_plugins_tab' ) ) :
	function zoom_callback_for_featured_plugins_tab( $args ) {
		add_filter( 'plugins_api_result', 'inject_wpzoom_plugins', 10, 3 );

		return $args;
	}
endif;


/**
 * Enqueue theme utils css.
 */
if ( ! function_exists( 'wpz_theme_enqueue_theme_utils_css' ) ) :
    function wpz_theme_enqueue_theme_utils_css() {
        $theme = wp_get_theme();
        if ( 'inspiro' === $theme->get_template() ) {
            return; // Don't load if theme is 'inspiro'
        }
        wp_enqueue_style( 'zoom-theme-utils-css', WPZOOM::$assetsPath . '/css/theme-utils.css' );
    }
endif;

add_action( 'wp_enqueue_scripts', 'wpz_theme_enqueue_theme_utils_css' );


if ( ! function_exists( 'init_video_background_on_hover_module' ) ) {
	function init_video_background_on_hover_module() {
		require_once WPZOOM_INC . '/components/video-background-on-hover/wpzoom-video-background-on-hover.php';

		static $instance = null;

		if ( is_null( $instance ) ) {
			$wrapped_settings = get_theme_support( 'wpz-background-video-on-hover' );
			$args             = array_pop( $wrapped_settings );
			$instance         = new WPZOOM_Video_Background_On_Hover( $args );
		}

		return $instance;
	}
}

if ( ! function_exists( 'zoom_error_log' ) ) :

	/**
	 * Error Log
	 *
	 * A wrapper function for the error_log() function.
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed $message Error message.
	 * @return void
	 */
	function zoom_error_log( $message = '' ) {
		if ( apply_filters( 'wpzoom_demo_importer_debug_logs', false ) ) {
			if ( is_array( $message ) ) {
				$message = wp_json_encode( $message );
			}

			error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

endif;

if ( ! function_exists( 'zoom_is_valid_image' ) ) :
	/**
	 * Check for the valid image
	 *
	 * @param string $link  The Image link.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	function zoom_is_valid_image( $link = '' ) {
		return preg_match( '/^((https?:\/\/)|(www\.))([a-z0-9-].?)+(:[0-9]+)?\/[\w\-]+\.(jpg|png|gif|jpeg|svg)\/?$/i', $link );
	}
endif;


if ( ! function_exists( 'zoom_current_theme_supports' ) ) {
	/**
	 * Check theme supports passed feature.
	 *
	 * @param string $feature The feature to check.
	 * @param mixed $args Extra arguments to be checked against certain features.
	 * @return boolean
	 */
	function zoom_current_theme_supports( $feature, ...$args ) {
		global $_wp_theme_features;
		if ( ! isset( $_wp_theme_features[ $feature ] ) ) {
			return false;
		}
	
		if ( ! $args ) {
			return $_wp_theme_features[ $feature ];
		}
	
		switch ( $feature ) {
			case 'wpz-onboarding':
				if ( isset( $_wp_theme_features[ $feature ][0][ $args[0] ] ) ) {
					return $_wp_theme_features[ $feature ][0][ $args[0] ];
				}
				return false;
	
			default:
				return $_wp_theme_features[ $feature ];
		}
	}
}

if ( ! function_exists( 'zoom_set_users_google_font_api_key') ) {
	
	function zoom_set_users_google_font_api_key( $api_key ) {
		
		$google_fonts_api_key = option::get( 'google_fonts_api_key' );
		
		if( ! empty( $google_fonts_api_key ) ) {
			return $google_fonts_api_key;
		}

		return $api_key;
	
	}
}

add_filter( 'zoom_customizer_google_fonts_api_key', 'zoom_set_users_google_font_api_key' );

