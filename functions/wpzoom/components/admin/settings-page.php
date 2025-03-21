<?php

class WPZOOM_Admin_Settings_Page {

	public static $remote_access = false;

	private static $xml_data = array();

	private static $installed_theme;

	public static function init() {
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'reset' ) {
			option::reset();
		}

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_assets' ) );
		add_action( 'admin_print_styles', array( __CLASS__, 'fonts_families_preview' ) );

		add_filter( 'wpzoom_field_misc_debug', array( __CLASS__, 'get_debug_text' ) );
		add_filter( 'wpzoom_field_misc_import', array( 'option', 'get_empty' ) );
		add_filter( 'wpzoom_field_misc_import_widgets', array( 'option', 'get_empty' ) );
		add_filter( 'wpzoom_field_misc_export', array( 'option', 'export_options' ) );
		add_filter( 'wpzoom_field_misc_export_widgets', array( 'option', 'export_widgets' ) );

		add_filter( 'admin_body_class', array( __CLASS__, 'check_mode' ) );

		self::$xml_data        = get_demo_xml_data();
		self::$installed_theme = wp_get_theme();

		if ( self::$xml_data['remote']['response'] ) {
			self::$remote_access = true;
		} elseif ( self::$xml_data['local']['response'] ) {
			self::$remote_access = true;
		}

	}

	public static function load_assets() {
		if ( function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		wp_register_script( 'wpzoom-js-cookie', WPZOOM::$assetsPath . '/js/js.cookie.min.js', array( 'jquery' ) );

		wp_enqueue_script( 'wpzoom-options', WPZOOM::$assetsPath . '/js/zoomAdmin.js', array( 'jquery', 'thickbox', 'wp-util', 'wpzoom-js-cookie' ), WPZOOM::$wpzoomVersion );
		wp_enqueue_style( 'wpzoom-options', WPZOOM::$assetsPath . '/options.css', array( 'thickbox' ), WPZOOM::$wpzoomVersion );

		// Enqueue our new navigation files
		wp_enqueue_script( 'wpzoom-nav', WPZOOM::$assetsPath . '/js/zoom-nav.js', array( 'jquery' ), WPZOOM::$wpzoomVersion, true );

		// Register the colourpicker JavaScript.
		wp_register_script( 'wpz-colourpicker', WPZOOM::$assetsPath . '/js/colorpicker.js', array( 'jquery' ), WPZOOM::$wpzoomVersion, true ); // Loaded into the footer.
		wp_enqueue_script( 'wpz-colourpicker' );

		// Register the colourpicker CSS.
		wp_register_style( 'wpz-colourpicker', WPZOOM::$assetsPath . '/css/colorpicker.css', array(), WPZOOM::$wpzoomVersion );
		wp_enqueue_style( 'wpz-colourpicker' );

		// Collect localization data
		$data = array(
			'themeName' => WPZOOM::$theme_raw_name,
		);

		// Localize the script
		wp_localize_script(
			'wpzoom-options',
			'WPZOOM_Theme_Options',
			$data
		);
	}

	public static function message_notice( $notice_type, $status = 'success', $echo = false ) {

		$content = '';

		switch ( $notice_type ) {
			case 'server':
					$content = '<strong>' . __( 'Error: Demo Content can\'t be imported.', 'wpzoom' ) . '</strong>
                                <p>' . __( 'WPZOOM servers are currently unavailable. Please try again later.', 'wpzoom' ) . '</p>';
				break;

			case 'child-theme':
					$content = '<strong>' . __( 'Error: This feature doesn\'t work when a Child Theme is active!', 'wpzoom' ) . '</strong>
                                <p>' . __( 'The importer works only with new copies of our themes. It doesn\'t support Child Themes or themes which folders were renamed to something different.', 'wpzoom' ) . '</p>';
				break;

			case 'child-theme-installed':
					$content = '<strong>' . __( 'You have Child Theme activated!', 'wpzoom' ) . '</strong>
                                <p>' . sprintf( __( 'You have already installed and activated Child Theme %s, in this case you can\'t create new.', 'wpzoom' ), '<strong>' . self::$installed_theme->get( 'Name' ) . '</strong>' ) . '</p>';
				break;
		}

		if ( $echo ) {
			echo '<div class="notice notice-' . esc_attr( $status ) . '">' . $content . '</div>';
		} else {
			return '<div class="notice notice-' . esc_attr( $status ) . '">' . $content . '</div>';
		}
	}

	/**
	 * Menu for theme/framework options page
	 */
	public static function menu() {
		$menu = option::$evoOptions['menu'];
		$out  = '<ul class="tabs">';

		foreach ( $menu as $key => $item ) {
			$class  = strtolower( str_replace( ' ', '_', preg_replace( '/[^a-zA-Z0-9\s]/', '', $item['name'] ) ) );
			$bubble = isset( $item['bubble'] ) ? $item['bubble'] : '';

			if ( isset( $item['id'] ) ) {
				$out .= '<li class="' . $class . ' wz-parent" id="wzm-' . $class . '"><a href="#tabid' . $item['id'] . '">' . $item['name'] . $bubble . '</a><em><svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.46967 0.46967C0.762563 0.176777 1.23744 0.176777 1.53033 0.46967L5.53033 4.46967C5.82322 4.76256 5.82322 5.23744 5.53033 5.53033L1.53033 9.53033C1.23744 9.82322 0.762563 9.82322 0.46967 9.53033C0.176777 9.23744 0.176777 8.76256 0.46967 8.46967L3.93934 5L0.46967 1.53033C0.176777 1.23744 0.176777 0.762563 0.46967 0.46967Z" fill="#242628"/></svg></em>';
			} else {
				$out .= '<li class="' . $class . ' wz-parent" id="wzm-' . $class . '"><a href="#tab' . $key . '">' . $item['name'] . $bubble . '</a><em><svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.46967 0.46967C0.762563 0.176777 1.23744 0.176777 1.53033 0.46967L5.53033 4.46967C5.82322 4.76256 5.82322 5.23744 5.53033 5.53033L1.53033 9.53033C1.23744 9.82322 0.762563 9.82322 0.46967 9.53033C0.176777 9.23744 0.176777 8.76256 0.46967 8.46967L3.93934 5L0.46967 1.53033C0.176777 1.23744 0.176777 0.762563 0.46967 0.46967Z" fill="#242628"/></svg></em>';
			}

			$out .= '<ul>';

			if ( ! is_string( $key ) ) {
				$sub_sections = option::$evoOptions[ 'id' . $item['id'] ];
			} else {
				$sub_sections = option::$evoOptions[ $key ];
			}

			foreach ( $sub_sections as $submenu ) {
				if ( $submenu['type'] == 'preheader' ) {
					$name = $submenu['name'];

					$stitle = 'wpz_' . substr( md5( $name ), 0, 8 );

					$bubble = isset( $submenu['bubble'] ) ? $submenu['bubble'] : '';

					$out .= '<li class="sub"><a href="#' . $stitle . '">' . $name . $bubble . '</a></li>';
				}
			}
			$out .= '</ul>';
			$out .= '</li>';
		}

		$out .= '</ul>';

		echo $out;
	}

	public static function content() {
		$options = option::$evoOptions;

		unset( $options['menu'] );

		$settings_ui = new WPZOOM_Admin_Settings_Interface();

		foreach ( $options as $tab_id => $tab_content ) {
			$settings_ui->add_tab( $tab_id );

			foreach ( $tab_content as $field ) {
				$defaults_args = array(
					'id'    => '',
					'type'  => '',
					'name'  => '',
					'std'   => '',
					'desc'  => '',
					'value' => '',
					'out'   => '',
				);

				$args = wp_parse_args( $field, $defaults_args );
				extract( $args );

				if ( option::get( $id ) != '' && ! is_array( option::get( $id ) ) ) {
					$value = $args['value'] = stripslashes( option::get( $id ) );
				} else {
					$value = $args['value'] = $std;
				}

				if ( $args['id'] == 'misc_load_demo_content' ) {
					// Show error notice when is child theme
					if ( self::$xml_data['is_child_theme'] ) {
						$type         = 'notice';
						$args['type'] = 'notice';
						$args['desc'] = self::message_notice( 'child-theme', 'error' );
					}
					// Show error notice when server not responding
					elseif ( ! self::$remote_access ) {
						$type         = 'notice';
						$args['type'] = 'notice';
						$args['desc'] = self::message_notice( 'server', 'error' );
					}
				}

				if ( $args['id'] == 'child_theme_notice' ) {

					if ( self::$xml_data['is_child_theme'] ) {
						$type         = 'notice';
						$args['type'] = 'notice';
						$args['desc'] = self::message_notice( 'child-theme-installed', 'error' );
					}
				}

				if ( self::$xml_data['is_child_theme'] ) {

					$blacklist_fields = array( 'child_theme_auto_activate', 'child_theme_keep_parent_settings', 'child_theme_install' );

					if ( in_array( $args['id'], $blacklist_fields ) ) {
						continue;
					}
				}

				$settings_ui->add_field( $type, array( $args ) );
			}

			$settings_ui->end_tab();
			$settings_ui->flush_content();
		}

	}

	public static function license_popup() {

		if( ! WPZOOM_Guard_Shell::is_guard_active() ) {
			return;
		}

		$license = trim( get_option( get_template() . '_license_key' ) );
		$status  = get_option( get_template() . '_license_key_status', false );

		$popup_head    = '';
		$popup_html    = '';
		$popup_message = '<p>' . wp_kses_post( __( 'Your theme is not licensed for use on this website, or your license key has expired. <br/>Please activate or renew your license key to use this theme.', 'wpzoom' ) ) . '</p>';
		$popup_btn     = sprintf( '<a href="%s" class="button button-primary">%s</a>', esc_url( admin_url( 'admin.php?page=wpzoom_license#license' ) ), esc_html__( 'Enter your License Key', 'wpzoom' ) );
		$popup_reset   = '<form id="zoomReset" method="post">
							<p class="submit">
								<input name="reset" class="button-secondary" type="submit" value="' . __( 'Reset settings', 'wpzoom' ) . '" />
								<input type="hidden" name="action" value="reset" />
							</p>
						</form>';

		if ( ! empty( $license ) ) {
			if ( 'invalid' === $status ) {
				$popup_head = '<h3>' . esc_html__( 'Invalid License Key!', 'wpzoom' ) . '</h3>';
			} elseif ( 'expired' === $status ) {
				$popup_head = '<h3>' . esc_html__( 'Expired License Key!', 'wpzoom' ) . '</h3>';
			} elseif ( 'valid' !== $status ) {
				$popup_head = '<h3>' . esc_html__( 'Inactive License Key!', 'wpzoom' ) . '</h3>';
			}
		} else {
			$popup_head = '<h3>' . esc_html__( 'Activate Your License Key!', 'wpzoom' ) . '</h3>';
		}

		if ( ! empty( $popup_head ) ) {
			$popup_html = '<div class="wpzoom-license-popup"><div class="wpzoom-license-popup-inner">' . $popup_head . $popup_message . $popup_btn . $popup_reset . '</div></div>';
		}

		echo $popup_html;

	}

	public static function check_button() {

		if( ! WPZOOM_Guard_Shell::is_guard_active() ) {
			return true;
		}
		
		$license = trim( get_option( get_template() . '_license_key' ) );
		$status  = get_option( get_template() . '_license_key_status', false );

		if ( ! empty( $license ) ) {
			if ( 'invalid' == $status || 'expired' == $status || 'valid' !== $status ) {
				return false;
			}
		} else {
			return false;
		}

		return true;
	}

	public static function check_mode( $classname ) {

		if( ! WPZOOM_Guard_Shell::is_guard_active() ) {
			return $classname;
		}

		$license = trim( get_option( get_template() . '_license_key' ) );
		$status  = get_option( get_template() . '_license_key_status', false );

		if ( ! empty( $license ) ) {
			if ( 'invalid' == $status || 'expired' == $status ) {
				$classname .= ' wpzoom-restricted-mode';
				return $classname;
			}
		} else {
			$classname .= ' wpzoom-restricted-mode';
			return $classname;
		}

		return $classname;
	}

	/**
	 * Handle Ajax calls for option updates.
	 *
	 * @return void
	 */
	public static function ajax_options() {
		parse_str( $_POST['data'], $data );

		check_ajax_referer( 'wpzoom-ajax-save', '_ajax_nonce' );

		if ( $data['misc_import'] ) {
			option::setupOptions( $data['misc_import'], true );
			wp_send_json_success();
		}

		if ( $data['misc_import_widgets'] ) {
			option::setupWidgetOptions( $data['misc_import_widgets'], true );
			wp_send_json_success();
		}

		new WPZOOM_Admin_Settings_Sanitization();

		foreach ( option::$options as $name => $null ) {
			$ignored = array( 'misc_export', 'misc_export_widgets', 'misc_debug' );
			if ( in_array( $name, $ignored ) ) {
				continue;
			}

			if ( isset( $data[ $name ] ) ) {
				$value = $data[ $name ];

				if ( ! is_array( $data[ $name ] ) ) {
					$value = stripslashes( $value );
				}
			} else {
				$value = 'off';
			}

			/*
			 * Filter for custom options validators.
			 */
			$value = apply_filters( 'zoom_field_save_' . $name, $value );

			option::set( $name, $value );
		}

		do_action( 'zoom_after_options_save' );

		wp_send_json_success();
	}

	/**
	 * Handle Ajax calls for widgets default.
	 *
	 * @return void
	 */
	public static function ajax_widgets_default() {
		check_ajax_referer( 'wpzoom-ajax-save', '_ajax_nonce' );

		$settingsFile = FUNC_INC . '/widgets/default.json';

		if ( current_theme_supports( 'wpz-multiple-demo-importer' ) ) {
			$get_theme_support  = get_theme_support( 'wpz-multiple-demo-importer' );
			$demos              = array_pop( $get_theme_support );
			$selected           = get_theme_mod( 'wpz_multiple_demo_importer', $demos['default'] );
			$demo_settings_file = FUNC_INC . '/widgets/' . $selected . '.json';
			$settingsFile       = file_exists( $demo_settings_file ) ? $demo_settings_file : $settingsFile;
		}

		/* backwards compatibility */
		if ( ! file_exists( $settingsFile ) && defined( 'THEME_INC' ) ) {
			$settingsFile = THEME_INC . '/widgets/default.json';
		}

		if ( file_exists( $settingsFile ) ) {
			$settings = file_get_contents( $settingsFile );

			option::setupWidgetOptions( $settings, true );
		}

		wp_send_json_success();
	}

	/**
	 * Handle Ajax calls for loading demo content.
	 *
	 * @return void
	 */
	public static function ajax_demo_content() {
		check_ajax_referer( 'wpzoom-ajax-save', '_ajax_nonce' );

		if ( ! class_exists( 'ZOOM_Demo_Importer' ) ) {
			require_once WPZOOM_INC . '/components/demo-importer/demo-importer.php';
		}
		ZOOM_Demo_Importer::do_import();
		exit;
	}

	/**
	 * Handle Ajax calls for loading demo content.
	 *
	 * @return void
	 */
	public static function ajax_erase_demo_content() {
		check_ajax_referer( 'wpzoom-ajax-save', '_ajax_nonce' );

		if ( ! class_exists( 'ZOOM_Demo_Importer' ) ) {
			require_once WPZOOM_INC . '/components/demo-importer/demo-importer.php';
		}
		ZOOM_Demo_Importer::do_erase();
		exit;
	}

	/**
	 * Handle Ajax calls for update nav menu location.
	 *
	 * @return void
	 */
	public static function ajax_update_nav_menu_location() {
		check_ajax_referer( 'update_nav_menu_location', '_ajax_nonce' );

		// Get existing menu locations assignments
		$locations      = get_registered_nav_menus();
		$menu_locations = get_nav_menu_locations();
		$nav_menus      = wp_get_nav_menus(); // Get created nav menus

		if ( isset( $_POST['settings'] ) && ! empty( $_POST['settings'] ) ) {
			$menu_locations = array_merge( $menu_locations, $_POST['settings'] );
		}

		// Set menu locations.
		set_theme_mod( 'nav_menu_locations', $menu_locations );
	}

	/**
	 * Generates CSS to preview Typography Fonts families
	 *
	 * @return void
	 */
	public static function fonts_families_preview() {
		if ( ! option::is_on( 'framework_fonts_preview' ) ) {
			return;
		}

		$css   = '';
		$fonts = '';

		$font_families        = ui::recognized_font_families();
		$google_font_families = ui::recognized_google_webfonts_families();

		foreach ( $font_families as $slug => $font ) {
			$css .= '.selectBox-dropdown-menu a[rel=' . $slug . ']{font-family:' . $font . ';}';
		}

		foreach ( $google_font_families as $font ) {
			if ( isset( $font['separator'] ) ) {
				continue;
			}

			$slug   = str_replace( ' ', '-', strtolower( $font['name'] ) );
			$css   .= '.selectBox-dropdown-menu a[rel=' . $slug . ']{font-family:' . $font['name'] . ';}';
			$fonts .= $font['name'] . '|';
		}

		$fonts      = str_replace( ' ', '+', $fonts );
		$google_css = '@import url("http' . ( is_ssl() ? 's' : '' ) . '://fonts.googleapis.com/css?family=' . $fonts . "\");\n";
		$google_css = str_replace( '|"', '"', $google_css );

		echo '<style type="text/css">';
			echo $google_css;
			echo $css;
		echo '</style>';
	}

	/**
	 * Get debug information
	 *
	 * Usually when someone have a problem, for a faster resolve we need to
	 * know what theme version is, what framework is running and what WordPress
	 * is installed. Also most problems are related to 3rd party plugins,
	 * so let's also keep track them.
	 *
	 * This information is private and is displayed only on framework admin
	 * page `/wp-admin/admin.php?page=wpzoom_options`
	 *
	 * @return string
	 */
	public static function get_debug_text() {
		// we'll need access to WordPress version
		global $wp_version;
		global $wpdb;

		$suhosin = extension_loaded( 'suhosin' ) ? __( 'Enabled', 'wpzoom' ) : __( 'Disabled', 'wpzoom' );
		$curl    = extension_loaded( 'curl' ) ? __( 'Enabled', 'wpzoom' ) : __( 'Disabled', 'wpzoom' );
		$soap    = extension_loaded( 'soap' ) ? __( 'Enabled', 'wpzoom' ) : __( 'Disabled', 'wpzoom' );

		$debug = "\n# Debug\n";

		// site url, theme info
		$debug .= "\nSite URL: " . get_home_url();
		$debug .= "\nTheme Name: " . WPZOOM::$themeName;
		$debug .= "\nIs Child Theme: " . ( is_child_theme() ? 'true' : 'false' );
		$debug .= "\nTheme Version: " . WPZOOM::$themeVersion;
		$debug .= "\nWPZOOM Version: " . WPZOOM::$wpzoomVersion;
		$debug .= "\nWordPress Version: " . $wp_version;

		$debug .= "\n\n# PHP Configuration\n";

		$debug .= "\nPHP Version: " . phpversion();
		$debug .= "\nMySQL version: " . $wpdb->db_version();
		$debug .= "\nMax Execution time (seconds): " . ini_get( 'max_execution_time' );
		$debug .= "\nUpload Max Filesize: " . ini_get( 'upload_max_filesize' );
		$debug .= "\nPost Max Size: " . ini_get( 'post_max_size' );
		$debug .= "\nMax Input Time (seconds): " . ini_get( 'max_input_time' );
		$debug .= "\nMax Input Vars: " . ini_get( 'max_input_vars' );
		$debug .= "\nMemory Limit: " . ini_get( 'memory_limit' );
		$debug .= "\nDisplay Errors: " . ( ini_get( 'display_errors' ) ? 'true' : 'false' );

		$debug .= "\n\n# PHP Extensions\n";

		$debug .= "\nSuhosin: " . $suhosin;
		$debug .= "\nCurl: " . $curl;
		$debug .= "\nSOAP: " . $soap;

		$debug .= "\n\n# Plugins\n";

		// active plugins
		$active_plugins = get_option( 'active_plugins' );

		// in order to be able to intersect plugins vs. active plugins by
		// keys, we need to change keys with values
		$active_plugins = array_flip( $active_plugins );

		if ( ! function_exists( 'get_plugins' ) ) {
			include 'wp-admin/includes/plugin.php';
		}

		// get all installed plugins
		$plugins = get_plugins();

		// select only active plugins
		$active_plugins = array_intersect_key( $plugins, $active_plugins );

		$i = 1;
		if ( $active_plugins && is_array( $active_plugins ) ) {
			// if there are active plugins, get their name, version.
			foreach ( $active_plugins as $id => $plugin ) {
				$debug .= "\n$i. " . $plugin['Name'] . ' ' . $plugin['Version'];
				$debug .= "\n   " . $id;
				$i ++;
			}
		}

		// return debug text
		return $debug;
	}
}
