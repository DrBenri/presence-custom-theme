<?php
/**
 * WPZOOM Demo Importer
 *
 * @since  2.0.0
 * @package WPZOOM
 * @subpackage Demo Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPZOOM_Demo_Importer' ) ) {

	/**
	 * WPZOOM Demo Importer
	 */
	class WPZOOM_Demo_Importer {

		/**
		 * Instance
		 *
		 * @since  2.0.0
		 * @var (Object) Class object
		 */
		public static $instance = null;

		/**
		 * Set Instance
		 *
		 * @since  2.0.0
		 *
		 * @return object Class object.
		 */
		public static function get_instance() {
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
			require_once WPZOOM_INC . '/components/demo-importer/importers/class-wpzoom-demo-importer-log.php';
			require_once WPZOOM_INC . '/components/demo-importer/helpers/class-wpzoom-demo-importer-helper.php';
			require_once WPZOOM_INC . '/components/demo-importer/class-wpzoom-widgets-import.php';
			require_once WPZOOM_INC . '/components/demo-importer/class-wpzoom-customizer-import.php';
			require_once WPZOOM_INC . '/components/demo-importer/class-wpzoom-template-options-import.php';

			// Import AJAX.
			add_action( 'wp_ajax_wpzoom-demo-importer-import-wpforms', array( $this, 'import_wpforms' ) );
			add_action( 'wp_ajax_wpzoom-demo-importer-import-customizer-settings', array( $this, 'import_customizer_settings' ) );
			add_action( 'wp_ajax_wpzoom-demo-importer-import-prepare-xml', array( $this, 'prepare_xml_data' ) );
			add_action( 'wp_ajax_wpzoom-demo-importer-import-options', array( $this, 'import_options' ) );
			add_action( 'wp_ajax_wpzoom-demo-importer-import-widgets', array( $this, 'import_widgets' ) );
			add_action( 'wp_ajax_wpzoom-demo-importer-import-end', array( $this, 'import_end' ) );
			add_action( 'wp_ajax_wpzoom-demo-importer-delete-end', array( $this, 'delete_end' ) );

			// Hooks in AJAX.
			add_action( 'wpzoom_demo_importer_import_complete', array( $this, 'after_batch_complete' ) );
			add_action( 'wpzoom_demo_importer_delete_complete', array( $this, 'after_delete_imported_complete' ) );
			add_action( 'init', array( $this, 'load_importer' ) );

			// Reset Customizer Data.
			add_action( 'wp_ajax_wpzoom-demo-importer-reset-customizer-data', array( $this, 'reset_customizer_data' ) );
			add_action( 'wp_ajax_wpzoom-demo-importer-reset-template-options', array( $this, 'reset_template_options' ) );
			add_action( 'wp_ajax_wpzoom-demo-importer-reset-widgets-data', array( $this, 'reset_widgets_data' ) );

			// Reset Post & Terms.
			add_action( 'wp_ajax_wpzoom-demo-importer-delete-posts', array( $this, 'delete_imported_posts' ) );
			add_action( 'wp_ajax_wpzoom-demo-importer-delete-wp-forms', array( $this, 'delete_imported_wp_forms' ) );
			add_action( 'wp_ajax_wpzoom-demo-importer-delete-terms', array( $this, 'delete_imported_terms' ) );

			// Delete "Hello World" & "Sample Page".
			add_action( 'wp_ajax_wpzoom-demo-importer-delete-wp-default-posts', array( $this, 'delete_wp_default_posts' ) );

			if ( version_compare( get_bloginfo( 'version' ), '5.1.0', '>=' ) ) {
				add_filter( 'http_request_timeout', array( $this, 'set_timeout_for_images' ), 10, 2 );
			}
		}

		/**
		 * Set the timeout for the HTTP request by request URL.
		 *
		 * E.g. If URL is images (jpg|png|gif|jpeg) are from the domain `https://demo.wpzoom.com` then we have set the timeout by 30 seconds. Default 5 seconds.
		 *
		 * @since 2.0.0
		 *
		 * @param int    $timeout_value Time in seconds until a request times out. Default 5.
		 * @param string $url           The request URL.
		 */
		public function set_timeout_for_images( $timeout_value, $url ) {

			// URL not contain `https://demo.wpzoom.com` then return $timeout_value.
			if ( strpos( $url, 'https://demo.wpzoom.com' ) === false || strpos( $url, 'demo.wpzoom.com' ) === false ) {
				return $timeout_value;
			}

			// Check is image URL of type jpg|png|gif|jpeg.
			if ( zoom_is_valid_image( $url ) ) {
				$timeout_value = 30;
			}

			return $timeout_value;
		}

		/**
		 * Load WordPress WXR importer.
		 */
		public function load_importer() {
			require_once WPZOOM_INC . '/components/demo-importer/importers/wxr-importer/class-wpzoom-importer.php';
		}

		/**
		 * Change flow status
		 *
		 * @since 2.0.0
		 *
		 * @param  array $args Flow query args.
		 * @return array Flow query args.
		 */
		public function change_flow_status( $args ) {
			$args['post_status'] = 'publish';
			return $args;
		}

		/**
		 * Track Flow
		 *
		 * @since 2.0.0
		 *
		 * @param  integer $flow_id Flow ID.
		 * @return void
		 */
		public function track_flows( $flow_id ) {
			zoom_error_log( 'Flow ID ' . $flow_id );
			WPZOOM_Importer::instance()->track_post( $flow_id );
		}

		/**
		 * Import WP Forms
		 *
		 * @since 1.2.14
		 * @since 1.4.0 The `$wpforms_url` was added.
		 *
		 * @param  string $wpforms_url WP Forms JSON file URL.
		 * @return void
		 */
		public function import_wpforms( $wpforms_url = '' ) {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce.
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$wpforms_url = ( isset( $_REQUEST['wpforms_url'] ) ) ? urldecode( $_REQUEST['wpforms_url'] ) : $wpforms_url;
			$ids_mapping = array();

			if ( ! empty( $wpforms_url ) && function_exists( 'wpforms_encode' ) ) {

				// Check for local file path.
				if ( WPZOOM_Demo_Import::get_instance()->get_filesystem()->exists( $wpforms_url ) ) {
					$file_path['success']      = true;
					$file_path['data']['file'] = $wpforms_url;
				} else {
					// Download JSON file.
					$file_path = WPZOOM_Demo_Importer_Helper::download_file( $wpforms_url );
				}

				if ( isset( $file_path['success'] ) && $file_path['success'] ) {
					if ( isset( $file_path['data']['file'] ) ) {
						$ext = strtolower( pathinfo( $file_path['data']['file'], PATHINFO_EXTENSION ) );

						if ( 'json' === $ext ) {
							$forms = json_decode( WPZOOM_Demo_Import::get_instance()->get_filesystem()->get_contents( $file_path['data']['file'] ), true );

							if ( ! empty( $forms ) ) {
								foreach ( $forms as $form ) {
									$title = ! empty( $form['settings']['form_title'] ) ? $form['settings']['form_title'] : '';
									$desc  = ! empty( $form['settings']['form_desc'] ) ? $form['settings']['form_desc'] : '';

									$new_id = post_exists( $title );

									if ( ! $new_id ) {
										$new_id = wp_insert_post(
											array(
												'post_title'   => $title,
												'post_status'  => 'publish',
												'post_type'    => 'wpforms',
												'post_excerpt' => $desc,
											)
										);

										if ( defined( 'WP_CLI' ) ) {
											WP_CLI::line( 'Imported Form ' . $title );
										}

										// Set meta for tracking the post.
										update_post_meta( $new_id, '_wpzoom_demo_importer_imported_wp_forms', true );
										WPZOOM_Demo_Importer_Log::add( 'Inserted WP Form ' . $new_id );
									}

									if ( $new_id ) {

										// ID mapping.
										$ids_mapping[ $form['id'] ] = $new_id;

										$form['id'] = $new_id;
										wp_update_post(
											array(
												'ID' => $new_id,
												'post_content' => wpforms_encode( $form ),
											)
										);
									}
								}
							}
						}
					}
				}
			}

			update_option( 'wpzoom_demo_importer_wpforms_ids_mapping', $ids_mapping, 'no' );

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( 'WP Forms Imported.' );
			} elseif ( wp_doing_ajax() ) {
				wp_send_json_success( $ids_mapping );
			}
		}

		/**
		 * Import Customizer Settings.
		 *
		 * @since 2.0.0
		 *
		 * @param  array $customizer_data Customizer Data.
		 * @return void
		 */
		public function import_customizer_settings( $customizer_data = '' ) {

			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce.
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$customizer_data = ( isset( $_REQUEST['customizer_data'] ) ) ? urldecode( $_REQUEST['customizer_data'] ) : $customizer_data;
			$wpforms_url = ( isset( $_REQUEST['wpforms_url'] ) ) ? urldecode( $_REQUEST['wpforms_url'] ) : '';

			//print_r( $customizer_data );

			//wp_send_json_success( $customizer_data );

			if ( ! empty( $customizer_data ) ) {
				WPZOOM_Demo_Importer_Log::add( 'Imported Customizer Settings ' . $customizer_data );

				// Set meta for tracking the post.
				//zoom_error_log( 'Customizer Data ' . $customizer_data );

				//update_option( '_wpzoom_demo_importer_old_customizer_data', $customizer_data, 'no' );

				$import_process = WPZOOM_Customizer_Import::instance()->import( $customizer_data );

				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Imported Customizer Settings!' );
				} elseif ( wp_doing_ajax() ) {
					wp_send_json_success( $import_process );
				}
			} else {
				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Customizer data is empty!' );
				} elseif ( wp_doing_ajax() ) {
					wp_send_json_error( __( 'Customizer data is empty!', 'wpzoom' ) );
				}
			}
		}

		/**
		 * Prepare XML Data.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function prepare_xml_data() {

			// Verify Nonce.
			check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

			if ( ! current_user_can( 'customize' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
			}

			if ( ! class_exists( 'XMLReader' ) ) {
				wp_send_json_error( __( 'If XMLReader is not available, it imports all other settings and only skips XML import. This creates an incomplete website. We should bail early and not import anything if this is not present.', 'wpzoom' ) );
			}

			if ( 'valid' !== WPZOOM_Onboarding_License::get_instance()->get_license_status() ) {
				wp_send_json_error( __( 'You are not allowed to perform this action! Please make sure your license key is activated.', 'wpzoom' ) );
			}

			$wxr_url     = ( isset( $_REQUEST['wxr_url'] ) ) ? urldecode( $_REQUEST['wxr_url'] ) : '';
			$action_type = ( isset( $_REQUEST['action_type'] ) ) ? sanitize_text_field( $_REQUEST['action_type'] ) : 'prepare-xml';

			if ( isset( $wxr_url ) ) {

				/**
				 * Provide custom action hook before the import process has begun.
				 */
				do_action( 'wpzoom_demo_importer_import_start' );

				if ( 'prepare-xml' === $action_type ) {
					WPZOOM_Demo_Importer_Log::add( 'Importing from XML ' . $wxr_url );

					$overrides = array(
						'wp_handle_sideload' => 'upload',
					);

					// Download XML file.
					$xml_path = WPZOOM_Demo_Importer_Helper::download_file( $wxr_url, $overrides );

					if ( $xml_path['success'] ) {
						$post = array(
							'post_title'     => basename( $wxr_url ),
							'guid'           => $xml_path['data']['url'],
							'post_mime_type' => $xml_path['data']['type'],
						);

						zoom_error_log( wp_json_encode( $post ) );
						zoom_error_log( wp_json_encode( $xml_path ) );

						// as per wp-admin/includes/upload.php.
						$post_id = wp_insert_attachment( $post, $xml_path['data']['file'] );

						zoom_error_log( wp_json_encode( $post_id ) );

						if ( is_wp_error( $post_id ) ) {
							wp_send_json_error( __( 'There was an error downloading the XML file.', 'wpzoom' ) );
						} else {
							update_option( 'wpzoom_demo_importer_imported_wxr_id', $post_id, 'no' );
							$attachment_metadata = wp_generate_attachment_metadata( $post_id, $xml_path['data']['file'] );
							wp_update_attachment_metadata( $post_id, $attachment_metadata );
							$data          = WPZOOM_Importer::instance()->get_xml_data( $xml_path['data']['file'], $post_id );
							$data['xml']   = $xml_path['data'];
							$data['modal'] = WPZOOM_Demo_Import::get_instance()->modal_content( $data );
							wp_send_json_success( $data );
						}
					} else {
						wp_send_json_error( $xml_path['data'] );
					}
				} elseif ( 'delete-imported-demo-content' === $action_type ) {
					WPZOOM_Demo_Importer_Log::add( 'Delete imported demo content from XML ' . $wxr_url );

					$data          = array( 'action_type' => $action_type );
					$data['modal'] = WPZOOM_Demo_Import::get_instance()->modal_content( $data );
					wp_send_json_success( $data );
				}
			} else {
				wp_send_json_error( __( 'Invalid site XML file!', 'wpzoom' ) );
			}
		}

		/**
		 * Import Options.
		 *
		 * @since 2.0.0
		 * @since 1.4.0 The `$options_data` was added.
		 *
		 * @param  array $options_data Template Options.
		 * @return void
		 */
		public function import_options( $options_data = array() ) {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce .
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$options_data = ( isset( $_POST['options_data'] ) ) ? (array) unserialize( stripslashes( base64_decode( $_POST['options_data'] ) ) ) : $options_data; // phpcs:ignore

			if ( ! empty( $options_data ) ) {
				// Set meta for tracking the post.
				if ( is_array( $options_data ) ) {
					WPZOOM_Demo_Importer_Log::add( PHP_EOL . '----' );
					WPZOOM_Demo_Importer_Log::add( 'Imported - Template Options ' . wp_json_encode( $options_data ) );
					WPZOOM_Demo_Importer_Log::add( '----' . PHP_EOL );
					update_option( '_wpzoom_demo_importer_old_template_options', $options_data, 'no' );
				}

				$wxr_id   = get_site_option( 'wpzoom_demo_importer_imported_wxr_id', 0 );
				$xml_file = wp_get_attachment_url( $wxr_id );
				$data     = WPZOOM_Importer::instance()->get_importer()->get_template_options_based_on_xml( $xml_file );

				// Merge options data with parsed data form XML file.
				$options_data         = array_merge( $options_data, (array) $data );
				$encoded_options_data = base64_encode( serialize( $options_data ) ); // phpcs:ignore

				option::setupOptions( $encoded_options_data, true );

				$options_importer = WPZOOM_Template_Options_Import::instance();
				$options_importer->import_options( $options_data );

				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Imported Template Options!' );
				} elseif ( wp_doing_ajax() ) {
					wp_send_json_success( $options_data );
				}
			} else {
				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Template options are empty!' );
				} elseif ( wp_doing_ajax() ) {
					wp_send_json_error( __( 'Template options are empty!', 'wpzoom' ) );
				}
			}
		}

		/**
		 * Import Widgets.
		 *
		 * @since 2.0.0
		 * @since 1.4.0 The `$widgets_data` was added.
		 *
		 * @param  string $widgets_data Widgets Data.
		 * @return void
		 */
		public function import_widgets( $widgets_data = '' ) {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce .
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$widgets_data = ( isset( $_POST['widgets_data'] ) ) ? (object) unserialize( stripslashes( base64_decode( $_POST['widgets_data'] ) ) ) : (object) $widgets_data; // phpcs:ignore

			if ( ! empty( $widgets_data ) ) {
				$widgets_data = WPZOOM_Widgets_Import::instance()->parse_widget_options( $widgets_data );
				WPZOOM_Widgets_Import::instance()->import_widgets_data( (object) $widgets_data );

				$sidebars_widgets = get_option( 'sidebars_widgets', array() );
				update_option( '_wpzoom_demo_importer_old_widgets_data', $sidebars_widgets, 'no' );
				WPZOOM_Demo_Importer_Log::add( 'Imported - Widgets ' . wp_json_encode( $sidebars_widgets ) );

				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Widget Imported!' );
				} elseif ( wp_doing_ajax() ) {
					wp_send_json_success( $widgets_data );
				}
			} else {
				if ( defined( 'WP_CLI' ) ) {
					WP_CLI::line( 'Widget data is empty!' );
				} elseif ( wp_doing_ajax() ) {
					wp_send_json_error( __( 'Widget data is empty!', 'wpzoom' ) );
				}
			}
		}

		/**
		 * Import End.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function import_end() {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce.
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$demo_data = get_option( 'wpzoom_demo_importer_import_data', array() );

			do_action( 'wpzoom_demo_importer_import_complete', $demo_data );

			update_option( 'wpzoom_demo_importer_import_complete', 'yes', 'no' );

			if ( wp_doing_ajax() ) {
				wp_send_json_success();
			}
		}

		/**
		 * Delete End.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function delete_end() {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce.
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$data = isset( $_POST['data'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['data'] ) ) : array();

			do_action( 'wpzoom_demo_importer_delete_complete', $data );

			update_option( 'wpzoom_demo_importer_delete_complete', 'yes', 'no' );

			if ( wp_doing_ajax() ) {
				wp_send_json_success();
			}
		}

		/**
		 * Clear Cache.
		 *
		 * @since  2.0.0
		 */
		public function after_batch_complete() {

			// Clear 'Builder Builder' cache .
			if ( is_callable( 'FLBuilderModel::delete_asset_cache_for_all_posts' ) ) {
				FLBuilderModel::delete_asset_cache_for_all_posts();
			}

			$this->update_latest_checksums();

			// Flush permalinks .
			flush_rewrite_rules();

			delete_option( 'wpzoom_demo_importer_import_data' );

			WPZOOM_Demo_Importer_Log::add( PHP_EOL . '----' );
			WPZOOM_Demo_Importer_Log::add( 'Complete' );
			WPZOOM_Demo_Importer_Log::add( '----' . PHP_EOL );
		}

		/**
		 * After delete importe content complete.
		 *
		 * @since  2.0.0
		 */
		public function after_delete_imported_complete() {

			// Clear 'Builder Builder' cache .
			if ( is_callable( 'FLBuilderModel::delete_asset_cache_for_all_posts' ) ) {
				FLBuilderModel::delete_asset_cache_for_all_posts();
			}

			$this->update_latest_checksums();

			// Flush permalinks .
			flush_rewrite_rules();

			delete_option( 'wpzoom_demo_importer_import_data' );

			/**
			 * Complete the erase.
			 *
			 * Fires after the erase process has finished. If you need to update
			 * your cache or re-enable processing, do so here.
			 */
			do_action( 'erase_demo_end' );
		}

		/**
		 * Update Latest Checksums
		 *
		 * Store latest checksum after batch complete.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function update_latest_checksums() {
			$latest_checksums = get_site_option( 'wpzoom-demo-importer-last-export-checksums-latest', '' );
			update_site_option( 'wpzoom-demo-importer-last-export-checksums', $latest_checksums, 'no' );
		}

		/**
		 * Reset customizer data
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function reset_customizer_data() {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce .
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			WPZOOM_Demo_Importer_Log::add( 'Deleted customizer Settings ' . wp_json_encode( get_option( 'wpzoom-settings', array() ) ) );

			delete_option( 'wpzoom-settings' );

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( 'Deleted Customizer Settings!' );
			} elseif ( wp_doing_ajax() ) {
				wp_send_json_success();
			}
		}

		/**
		 * Reset template options
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function reset_template_options() {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce .
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$options = get_option( '_wpzoom_demo_importer_old_template_options', array() );

			WPZOOM_Demo_Importer_Log::add( 'Deleted - Site Options ' . wp_json_encode( $options ) );

			if ( $options ) {
				foreach ( $options as $option_key => $option_value ) {
					delete_option( $option_key );
				}
			}

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( 'Deleted Site Options!' );
			} elseif ( wp_doing_ajax() ) {
				wp_send_json_success();
			}
		}

		/**
		 * Reset widgets data
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function reset_widgets_data() {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce .
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			// Get all old widget ids .
			$old_widgets_data = (array) get_option( '_wpzoom_demo_importer_old_widgets_data', array() );
			$old_widget_ids   = array();
			foreach ( $old_widgets_data as $old_sidebar_key => $old_widgets ) {
				if ( ! empty( $old_widgets ) && is_array( $old_widgets ) ) {
					$old_widget_ids = array_merge( $old_widget_ids, $old_widgets );
				}
			}

			// Process if not empty .
			$sidebars_widgets = get_option( 'sidebars_widgets', array() );
			if ( ! empty( $old_widget_ids ) && ! empty( $sidebars_widgets ) ) {
				WPZOOM_Demo_Importer_Log::add( 'DELETED - WIDGETS ' . wp_json_encode( $old_widget_ids ) );

				foreach ( $sidebars_widgets as $sidebar_id => $widgets ) {
					$widgets = (array) $widgets;

					if ( ! empty( $widgets ) && is_array( $widgets ) ) {
						foreach ( $widgets as $widget_id ) {
							if ( in_array( $widget_id, $old_widget_ids, true ) ) {
								WPZOOM_Demo_Importer_Log::add( 'DELETED - WIDGET ' . $widget_id );

								// Move old widget to inacitve list .
								$sidebars_widgets['wp_inactive_widgets'][] = $widget_id;

								// Remove old widget from sidebar .
								$sidebars_widgets[ $sidebar_id ] = array_diff( $sidebars_widgets[ $sidebar_id ], array( $widget_id ) );
							}
						}
					}
				}

				update_option( 'sidebars_widgets', $sidebars_widgets );
			}

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( 'Deleted Widgets!' );
			} elseif ( wp_doing_ajax() ) {
				wp_send_json_success();
			}
		}

		/**
		 * Delete imported posts
		 *
		 * @since 2.0.0
		 * @since 1.4.0 The `$post_id` was added.
		 *
		 * @param  integer $post_id Post ID.
		 * @return void
		 */
		public function delete_imported_posts( $post_id = 0 ) {
			if ( wp_doing_ajax() ) {
				// Verify Nonce .
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : $post_id;

			$message = 'Deleted - Post ID ' . $post_id . ' - ' . get_post_type( $post_id ) . ' - ' . get_the_title( $post_id );

			$message = '';
			if ( $post_id ) {
				$post_type = get_post_type( $post_id );
				$message   = 'Deleted - Post ID ' . $post_id . ' - ' . $post_type . ' - ' . get_the_title( $post_id );

				do_action( 'wpzoom_demo_importer_before_delete_imported_posts', $post_id, $post_type );

				WPZOOM_Demo_Importer_Log::add( $message );
				wp_delete_post( $post_id, true );
			}

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( $message );
			} elseif ( wp_doing_ajax() ) {
				wp_send_json_success( $message );
			}
		}

		/**
		 * Delete imported WP forms
		 *
		 * @since 2.0.0
		 * @since 1.4.0 The `$post_id` was added.
		 *
		 * @param  integer $post_id Post ID.
		 * @return void
		 */
		public function delete_imported_wp_forms( $post_id = 0 ) {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce .
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : $post_id;

			$message = '';
			if ( $post_id ) {
				do_action( 'wpzoom_demo_importer_before_delete_imported_wp_forms', $post_id );

				$message = 'Deleted - Form ID ' . $post_id . ' - ' . get_post_type( $post_id ) . ' - ' . get_the_title( $post_id );
				WPZOOM_Demo_Importer_Log::add( $message );
				wp_delete_post( $post_id, true );
			}

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( $message );
			} elseif ( wp_doing_ajax() ) {
				wp_send_json_success( $message );
			}
		}

		/**
		 * Delete imported terms
		 *
		 * @since 2.0.0
		 * @since 1.4.0 The `$post_id` was added.
		 *
		 * @param  integer $term_id Term ID.
		 * @return void
		 */
		public function delete_imported_terms( $term_id = 0 ) {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce .
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'customize' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$term_id = isset( $_REQUEST['term_id'] ) ? absint( $_REQUEST['term_id'] ) : $term_id;

			$message = '';
			if ( $term_id ) {
				$term = get_term( $term_id );
				if ( ! is_wp_error( $term ) && is_object( $term ) ) {
					do_action( 'wpzoom_demo_importer_before_delete_imported_terms', $term_id, $term );

					$message = 'Deleted - Term ' . $term_id . ' - ' . $term->name . ' ' . $term->taxonomy;
					WPZOOM_Demo_Importer_Log::add( $message );
					wp_delete_term( $term_id, $term->taxonomy );
				}
			}

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( $message );
			} elseif ( wp_doing_ajax() ) {
				wp_send_json_success( $message );
			}
		}

		/**
		 * Delete default WP posts
		 *
		 * @return void
		 */
		public function delete_wp_default_posts() {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				// Verify Nonce.
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'edit_posts' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
				}
			}

			$message = '';

			$hello_world = wp_delete_post( 1, true ); // 'Hello World!' post.
			$sample_page = wp_delete_post( 2, true ); // 'Sample page' page.

			if ( $hello_world ) {
				$message .= 'Deleted - Post ' . $hello_world->ID . ' - ' . $hello_world->post_title . ' ' . $hello_world->post_type;
			}
			if ( $sample_page ) {
				$message .= "\nDeleted - Post " . $sample_page->ID . ' - ' . $sample_page->post_title . ' ' . $sample_page->post_type;
			}

			WPZOOM_Demo_Importer_Log::add( $message );

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( $message );
			} elseif ( wp_doing_ajax() ) {
				wp_send_json_success( $message );
			}
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	WPZOOM_Demo_Importer::get_instance();
}
