<?php
/**
 * WPZOOM Demo Import
 *
 * @since  2.0.0
 * @package WPZOOM
 * @subpackage Demo Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPZOOM_Demo_Import' ) ) {

	/**
	 * WPZOOM Demo Importer
	 */
	final class WPZOOM_Demo_Import {
		/**
		 * Instance of WPZOOM_Demo_Import
		 *
		 * @since  2.0.0
		 * @var (Object) WPZOOM_Demo_Import
		 */
		private static $instance = null;

		/**
		 * Localization variable
		 *
		 * @since  2.0.0
		 * @var (Array) $wp_upload_url
		 */
		public $wp_upload_url = '';

		/**
		 * Ajax
		 *
		 * @since  2.0.0
		 * @var (Array) $ajax
		 */
		private $ajax = array();

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
		 * Holds configurable array of advanced settings.
		 *
		 * @since 2.0.0
		 *
		 * @var array
		 */
		public $advanced_settings = array();

		/**
		 * Instance of WPZOOM_Demo_Import.
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
		private function __construct() {
			$this->includes();
			$this->init();

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ), 99 );

			// AJAX.
			$this->ajax = array(
				'wpzoom-demo-importer-set-reset-data'  => 'get_reset_data',
				'wpzoom-demo-importer-backup-settings' => 'backup_settings',
				'wpzoom-demo-importer-process-template-data' => 'process_template_data',
				'wpzoom-demo-importer-prepare-template-options' => 'prepare_template_options',
				'wpzoom-demo-importer-before-elementor-batch-process' => 'before_elementor_batch_process',
				'wpzoom-demo-importer-elementor-batch-process' => 'elementor_batch_process',
			);

			foreach ( $this->ajax as $ajax_hook => $ajax_callback ) {
				add_action( 'wp_ajax_' . $ajax_hook, array( $this, $ajax_callback ) );
			}

			add_action( 'wpzoom_demo_importer_import_complete', array( $this, 'import_complete' ), 9, 1 ); // Run before after_batch_complete().
			add_filter( 'wpzoom_demo_import_advanced_settings', array( $this, 'check_advanced_settings_compatibility' ) );
		}

		/**
		 * Enqueue admin scripts
		 *
		 * @param string $hook_suffix The current admin page.
		 * @return void
		 */
		public function admin_enqueue( $hook_suffix ) {
			global $is_IE, $is_edge;

			if ( 'wpzoom_page_wpzoom_license' !== $hook_suffix ) {
				return;
			}

			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			wp_enqueue_style( 'wpzoom-demo-import-admin', WPZOOM::get_root_uri() . '/components/demo-importer/assets/css/admin.css', array(), WPZOOM::$wpzoomVersion );

			wp_enqueue_script( 'zoom-retry-ajax', WPZOOM::get_root_uri() . '/components/theme-setup/assets/js/jquery.ajax.retry.js', array(), WPZOOM::$wpzoomVersion, true );
			wp_enqueue_script( 'wpzoom-demo-import-admin', WPZOOM::get_root_uri() . '/components/demo-importer/assets/js/admin.js', array( 'jquery', 'wp-util' ), WPZOOM::$wpzoomVersion, true );

			if ( $is_IE || $is_edge ) {
				wp_enqueue_script( 'wpzoom-demo-import-eventsource', WPZOOM::get_root_uri() . '/components/demo-importer/assets/js/eventsource.min.js', array( 'jquery', 'wp-util', 'updates' ), WPZOOM::$wpzoomVersion, true );
			}

			$report_problem_url = add_query_arg(
				array(
					'website-url' => site_url(),
					'theme-name'  => WPZOOM::$themeName,
					'subject'     => '#SUBJECT#',
				),
				'https://www.wpzoom.com/support/tickets/create/'
			);

			wp_localize_script(
				'wpzoom-demo-import-admin',
				'wpzoomDemoImporterVars',
				array(
					'_ajax_nonce'                  => wp_create_nonce( 'wpzoom-demo-importer' ),
					'_regenerate_thumbnails_nonce' => wp_create_nonce( 'regenerate_thumbnail' ),
					'debug'                        => defined( 'WP_DEBUG' ) ? true : false,
					'view_site_button'             => '<a class="wpz-onboard_demo-import-view-site button button-primary" href="' . site_url() . '" target="_blank">' . esc_html__( 'View site', 'wpzoom' ) . '</a>',
					'close_button'                 => '<a class="wpz-onboard_demo-import-close-modal button button-secondary" href="' . admin_url( 'admin.php?page=wpzoom_license' ) . '">' . esc_html__( 'Close', 'wpzoom' ) . '</a>',
					'customize_theme_button'       => '<a class="wpz-onboard_demo-import-customize-theme button button-secondary" href="' . admin_url( 'customize.php?return=' . rawurlencode( admin_url( 'admin.php?page=wpzoom_license' ) ) ) . '">' . esc_html__( 'Customize theme', 'wpzoom' ) . '</a>',
					'demo_import_successfully'     => '<span class="wpz-onboard_success-icon-header"></span><p>' . esc_html__( 'The demo content has been successfully imported!', 'wpzoom' ) . '</p><p>' . esc_html__( 'Go ahead, customize the text, images and design to make it yours!', 'wpzoom' ) . '</p>',
					'deleted_successfully'         => '<span class="wpz-onboard_success-icon-header"></span><p>' . esc_html__( 'The demo content has been successfully deleted!', 'wpzoom' ) . '</p>',
					'labels'                       => array_merge(
						WPZOOM_Onboarding::get_instance()->strings['localize_strings'],
						array(
							'successfully_configured'      => __( 'Demo content imported!', 'wpzoom' ) . ' &#127881;',
							'successfully_deleted'         => __( 'Demo content deleted!', 'wpzoom' ),
							'all_done'                     => _x( 'All done!', 'All options and demo content imported 100%', 'wpzoom' ),
							'import_content_done'          => _x( 'Imported demo content', 'Import demo content done 100%', 'wpzoom' ),
							'regenerated_attachments_done' => _x( 'Regenerated attachments', 'Regenerate attachments done 100%', 'wpzoom' ),
							'import_failed'                => __( 'Import failed!', 'wpzoom' ),
							'import_failed_message_due_to_debug' => __( '<p>WordPress debug mode is currently enabled on your website. This has interrupted the import process..</p><p>Please disable debug mode and try importing Demo Content again.</p><p>You can add the following code into the wp-config.php file to disable debug mode.</p><p><code>define(\'WP_DEBUG\', false);</code></p>', 'wpzoom' ),
							/* translators: %s The documentation URL */
							'import_failed_message'        => sprintf( __( '<p>We are facing a temporary issue in importing this template.</p><p>Read <a href="%s" target="_blank">article</a> to resolve the issue and continue importing template.</p>', 'wpzoom' ), esc_url( 'https://wpzoom.com/' ) ), // TODO: provide link to docs how to fix template import issues.
							'import_interrupted'           => __( 'Process Interrupted!', 'wpzoom' ),
							'importing'                    => __( 'Importing', 'wpzoom' ),
							'deleting'                     => __( 'Deleting', 'wpzoom' ),
							'progress_imported'            => _x( 'Imported', 'The imported progress percentage', 'wpzoom' ),
							'progress_regenerated'         => _x( 'Regenerated', 'The regenerate attachments progress percentage', 'wpzoom' ),
							'next'                         => _x( 'Next', 'Next step', 'wpzoom' ),
							'xml_import_interrupted_error' => __( 'Looks like your host probably could not store XML file in /wp-content/ folder.', 'wpzoom' ),
							'xml_import_interrupted_primary' => __( 'There was an error while importing the content.', 'wpzoom' ),
							'xml_import_interrupted_secondary' => __( 'To import content, WordPress needs to store XML file in /wp-content/ folder. Please get in touch with your hosting provider.', 'wpzoom' ),
							'xml_required_files_missing'   => __( 'Some of the files required during the import process are missing.<br/><br/>Please try again after some time.', 'wpzoom' ),
							'xml_prepare_import_failed'    => __( 'Error Processing the XML File!', 'wpzoom' ),
							/* translators: %s HTML tags */
							'ajax_request_failed_primary'  => sprintf( __( '%1$sWe could not start the import process due to failed AJAX request and this is the message from WordPress:%2$s', 'wpzoom' ), '<p>', '</p>' ),
							/* translators: %s URL to document. */
							'ajax_request_failed_secondary' => sprintf( __( '%1$sRead <a href="%2$s" target="_blank">article</a> to resolve the issue and continue importing template.%3$s', 'wpzoom' ), '<p>', esc_url( 'https://wpzoom.com/docs/internal-server-error-during-demo-import/' ), '</p>' ), // TODO: provide link to docs how to fix template import issues.
							/* translators: %s URL to document. */
							'process_failed_primary'       => sprintf( __( '%1$sWe could not complete the import process due to failed AJAX request and this is the message:%2$s', 'wpzoom' ), '<p>', '</p>' ),
							/* translators: %s URL to document. */
							'process_failed_secondary'     => sprintf( __( '%1$sPlease report this <a href="%2$s" target="_blank">here</a>.%3$s', 'wpzoom' ), '<p>', esc_url( $report_problem_url ), '</p>' ),
							'invalid_xml_url'              => __( 'Invalid .xml URL provided', 'wpzoom' ),
							'on_leave_alert'               => __( 'Please note that the import process has not yet been completed! Click OK to stop the process. Click Cancel to resume the process.', 'wpzoom' ),
						)
					),
				)
			);
		}

		/**
		 * Initialise the class configurations.
		 *
		 * @return void
		 */
		protected function init() {

			// Set memory limit.
			if( function_exists( 'ini_set' ) ) {
				ini_set( 'memory_limit', apply_filters( 'wpzoom_demo_import/import_memory_limit', '350M' ) );
			}
			
			// Set time limit.
			if( function_exists( 'set_time_limit' ) ) {
				set_time_limit( apply_filters( 'wpzoom_demo_import/set_time_limit_for_demo_data_import', 300 ) );
			}
			
			// Load class strings.
			$this->strings = array(
				'modal_header'              => __( 'Demo content importer', 'wpzoom' ),
				'modal_header_description'  => __( 'Importing the Demo Content will not delete your current posts, pages or anything else. The importing process can take up to 5 minutes or longer depending on your server configuration and the number of images included in the demo. Please do not leave this screen until you see “All done!” in the modal.', 'wpzoom' ),
				'modal_step_import'         => __( 'Import demo content', 'wpzoom' ),
				'modal_step_menus'          => __( 'Template menus', 'wpzoom' ),
				'modal_step_setup_homepage' => __( 'Setup homepage', 'wpzoom' ),
				'advanced_settings'         => __( 'Advanced Settings', 'wpzoom' ),
				'server_requirements'       => __( 'View Server Requirements', 'wpzoom' ),
				'system_details'            => __( 'View System Details', 'wpzoom' ),
				'cancel_importing'          => _x( 'Cancel', 'Cancel demo import modal', 'wpzoom' ),
				'start_importing'           => _x( 'Start importing', 'Start importing demo modal', 'wpzoom' ),
			);

			$this->advanced_settings = array(
				array(
					'id'          => 'wpzoom-demo-importer_delete-imported-demo',
					'label'       => __( 'Delete Previously Imported Demo', 'wpzoom' ),
					'description' => __( 'WARNING: Selecting this option will delete all data from the previous import. Choose this option only if this is intended.', 'wpzoom' ),
					'default'     => true,
				),
				array(
					'id'          => 'wpzoom-demo-importer_import-widgets',
					'label'       => __( 'Import Widgets', 'wpzoom' ),
					'description' => __( 'All previous changes made to widgets will be replaced with data from demo content!', 'wpzoom' ),
					'default'     => true,
				),
				array(
					'id'          => 'wpzoom-demo-importer_import-customizer-settings',
					'label'       => __( 'Import Customizer Settings', 'wpzoom' ),
					'description' => __( 'All previous changes made to customizer will be replaced with data from demo content!', 'wpzoom' ),
					'default'     => true,
				),
				array(
					'id'          => 'wpzoom-demo-importer_import-template-options',
					'label'       => __( 'Import Template Options', 'wpzoom' ),
					'description' => '',
					'default'     => true,
				),
				array(
					'id'          => 'wpzoom-demo-importer_install-child-theme',
					'label'       => __( 'Install Child Theme', 'wpzoom' ),
					'description' => __( 'A WordPress child theme allows you to apply custom code changes to your site. Using a child theme ensures that all your custom changes will not be overwritten even when you update the parent theme.', 'wpzoom' ),
					'default'     => true,
				),
				array(
					'id'          => 'wpzoom-demo-importer_activate-child-theme',
					'label'       => __( 'Activate Child Theme', 'wpzoom' ),
					'description' => __( 'Activate Child Theme after it was installed.', 'wpzoom' ),
					'default'     => false,
				),
				array(
					'id'          => 'wpzoom-demo-importer_copy-data-to-child-theme',
					'label'       => __( 'Copy existing Widgets, Menus and Customizer options to Child Theme', 'wpzoom' ),
					'description' => __( 'This option replaces the Child Theme\'s existing Widgets, Menus and Customizer options with those from the Parent Theme. You should only need to use this option the first time you install a Child Theme.', 'wpzoom' ),
					'default'     => true,
				),
				array(
					'id'          => 'wpzoom-demo-importer_elementor-batch-process',
					'label'       => __( 'Process Elementor Pages', 'wpzoom' ),
					'description' => __( 'Selecting this option will process all Elementor pages and replace WPForms shortcodes and demo site URL with your site URL. <br />Note: If you enable this option, the importing process may take longer.', 'wpzoom' ),
					'default'     => true,
				),
				array(
					'id'          => 'wpzoom-demo-importer_delete-wp-defaults',
					'label'       => __( 'Delete "Hello World!" post and "Sample Page"', 'wpzoom' ),
					'description' => __( 'WordPress creates by default a new page "Sample page" and a post "Hello World!". If you check this option, they will be deleted automatically. <br />Note: If you have edited the "Sample page" or "Hello World!", disable this option to skip deletion!', 'wpzoom' ),
					'default'     => true,
				),
				array(
					'id'          => 'wpzoom-demo-importer_regenerate-thumbnails',
					'label'       => __( 'Regenerate all thumbnails', 'wpzoom' ),
					'description' => __( 'Regenerate Thumbnails allows you to regenerate all thumbnail sizes for one or more images that have been uploaded to your Media Library.<br/>Note: If you enable this option, the importing process will take more time.', 'wpzoom' ),
					'default'     => false,
				),
				array(
					'id'          => 'wpzoom-demo-importer_regenerate-featured',
					'label'       => __( 'Regenerate only featured images', 'wpzoom' ),
					'description' => __( 'This option will regenerate only featured images on your site. This is a good feature if you have a lot of images uploaded, but want to rebuild only post thumbnails.', 'wpzoom' ),
					'default'     => true,
				),
			);

			do_action( 'wpzoom_demo_import_init' );
		}

		/**
		 * Load all the required files in the importer.
		 *
		 * @since  2.0.0
		 */
		private function includes() {
			// Core Helpers - Image.
			// @todo 	This file is required for Elementor.
			// Once we implement our logic for updating elementor data then we'll delete this file.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Core Helpers - Image Downloader.
			require_once WPZOOM_INC . '/components/demo-importer/helpers/class-wpzoom-demo-image-importer.php';

			// Demo Importer class.
			require_once WPZOOM_INC . '/components/demo-importer/importers/class-wpzoom-demo-importer.php';
		}

		/**
		 * Demo importer modal content
		 *
		 * @param array $data The modal data.
		 * @return string Raw HTML.
		 */
		public function modal_content( $data ) {
			$advanced_settings   = $this->get_advanced_settings_content( $data );
			$server_requirements = $this->get_server_requirements_content();
			$system_details      = $this->get_system_details_content();
			$action_type         = ( isset( $data['action_type'] ) ? sanitize_text_field( $data['action_type'] ) : 'prepare-xml' );

			$modal_header         = $this->strings['modal_header'];
			$modal_description    = $this->strings['modal_header_description'];
			$modal_attrs          = 'data-xml-processing="no"';
			$modal_footer_buttons = <<<MODAL_BUTTONS
			<button type="button" class="wpz-onboard_demo-import-cancel button button-secondary">{$this->strings['cancel_importing']}</button>
			<button type="submit" class="wpz-onboard_demo-import-start button button-primary">{$this->strings['start_importing']}</button>
MODAL_BUTTONS;

			if ( 'delete-imported-demo-content' === $action_type ) {
				$modal_header         = esc_html__( 'Delete imported demo content', 'wpzoom' );
				$modal_description    = esc_html__( 'Are you sure you want to delete the imported demo content? This action will delete all pages and posts that have been imported, whether or not they have been edited later.', 'wpzoom' );
				$modal_attrs         .= ' data-delete-imported-demo="yes"';
				$modal_footer_buttons = <<<MODAL_BUTTONS
			<button type="button" class="wpz-onboard_demo-import-cancel button button-secondary">{$this->strings['cancel_importing']}</button>
			<button type="submit" class="wpz-onboard_demo-import-delete button button-danger">Delete?</button>
MODAL_BUTTONS;
			}

			return <<<HERE
			<div class="wpz-onboard_demo-import-modal" {$modal_attrs}>
				<div class="inner-demo-import-modal">
					<div class="wpz-onboard_demo-import-modal-header">
						<h3>{$modal_header}</h3>
						<a href="#" class="wpz-onboard_demo-import-close"></a>
					</div>
					<div class="wpz-onboard_demo-import-modal-content">
						<div class="wpz-onboard_demo-import-steps">
							<p class="description">{$modal_description}</p>
						</div>
						<div class="wpz-onboard_demo-import-main-content">
							<div class="current-importing-status-wrap">
								<div class="current-importing-status">
									<h3 class="current-importing-status-title"></h3>
									<p class="current-importing-status-description"></p>
								</div>
							</div>
						</div>
					</div>
					<div class="wpz-onboard_demo-import-advanced-settings">
						<ul>
							<li class="demo-import-advanced-settings-item">
								<label for="demo-import-advanced-settings">{$this->strings['advanced_settings']}</label>
								<div id="demo-import-advanced-settings">{$advanced_settings}</div>
							</li>
							<li class="demo-import-advanced-settings-item">
								<label for="demo-import-server-requirements">{$this->strings['server_requirements']}</label>
								<div id="demo-import-server-requirements">{$server_requirements}</div>
							</li>
							<li class="demo-import-advanced-settings-item">
								<label for="demo-import-system-details">{$this->strings['system_details']}</label>
								<div id="demo-import-system-details">{$system_details}</div>
							</li>
						</ul>
					</div>
					<div class="wpz-onboard_demo-import-modal-footer">
						{$modal_footer_buttons}
					</div>
				</div>
			</div>
HERE;
		}

		/**
		 * Get an instance of WP_Filesystem_Direct.
		 *
		 * @since 2.0.0
		 * @return object A WP_Filesystem_Direct instance.
		 */
		public static function get_filesystem() {
			global $wp_filesystem;

			require_once ABSPATH . '/wp-admin/includes/file.php';

			WP_Filesystem();

			return $wp_filesystem;
		}

		/**
		 * Get server requirements content
		 *
		 * @return string
		 */
		public function get_server_requirements_content() {
			$content             = '';
			$server_requirements = WPZOOM_Demo_Importer_Log::get_server_requirements();

			if ( is_array( $server_requirements ) ) {
				$content .= '<table class="widefat">
					<colgroup>
						<col style="width: 60%">
						<col style="width: 20%">
						<col style="width: 20%">
					</colgroup>
					<tr>
						<th>' . __( 'Server Environment', 'wpzoom' ) . '</th>
						<th>' . __( 'Recommended', 'wpzoom' ) . '</th>
						<th>' . __( 'Current', 'wpzoom' ) . '</th>
					</tr>';

				$whitelist_bytes_ini = array( 'max-upload-size', 'php-max-post-size', 'memory-limit' );

				foreach ( $server_requirements as $key => $requirement ) {
					$title_has_badge = strpos( $this->strings['server_requirements'], 'server-requirement-bad-badge' ) !== false;

					if ( in_array( $key, $whitelist_bytes_ini ) ) {
						$r_bytes             = wp_convert_hr_to_bytes( $requirement['recommended'] );
						$c_bytes             = wp_convert_hr_to_bytes( $requirement['current'] );
						$dont_meet_condition = $c_bytes < $r_bytes;

						if ( $dont_meet_condition && ! $title_has_badge ) {
							$this->strings['server_requirements'] .= '<span class="server-requirement-bad-badge"></span>';
						}

						$content .= '<tr>
							<td>' . $requirement['label'] . '</td>
							<td>' . size_format( $r_bytes ) . '</td>
							<td><strong class="' . ( $dont_meet_condition ? 'bad' : 'good' ) . '">' . size_format( $c_bytes ) . '</strong></td>
						</tr>';
					}
					if ( 'php-max-exec-time' === $key || 'php-max-input-time' === $key ) {
						$dont_meet_condition = ( '-1' !== $requirement['current'] && $requirement['current'] < $requirement['recommended'] );

						if ( $dont_meet_condition && ! $title_has_badge ) {
							$this->strings['server_requirements'] .= '<span class="server-requirement-bad-badge"></span>';
						}

						$content .= '<tr>
							<td>' . $requirement['label'] . '</td>
							<td>' . $requirement['recommended'] . '</td>
							<td><strong class="' . ( $dont_meet_condition ? 'bad' : 'good' ) . '">' . $requirement['current'] . '</strong></td>
						</tr>';
					}
					if ( 'php-extension-gd' === $key ) {
						$dont_meet_condition = $requirement['current'] !== $requirement['recommended'];

						if ( $dont_meet_condition && ! $title_has_badge ) {
							$this->strings['server_requirements'] .= '<span class="server-requirement-bad-badge"></span>';
						}

						$content .= '<tr>
							<td>' . $requirement['label'] . '</td>
							<td>' . $requirement['recommended'] . '</td>
							<td><strong class="' . ( $dont_meet_condition ? 'bad' : 'good' ) . '">' . $requirement['current'] . '</strong></td>
						</tr>';
					}
					if ( 'php-version' === $key ) {
						$dont_meet_condition = version_compare( $requirement['current'], $requirement['recommended'], '<' );

						if ( $dont_meet_condition && ! $title_has_badge ) {
							$this->strings['server_requirements'] .= '<span class="server-requirement-bad-badge"></span>';
						}

						$content .= '<tr>
							<td>' . $requirement['label'] . '</td>
							<td>' . $requirement['recommended'] . '</td>
							<td><strong class="' . ( $dont_meet_condition ? 'bad' : 'good' ) . '">' . $requirement['current'] . '</strong></td>
						</tr>';
					}
				}
				$content .= '</table>';
			}
			return apply_filters( 'wpzoom_demo_import_server_requirements_content', $content );
		}

		/**
		 * Get system details content
		 *
		 * @return string
		 */
		public function get_system_details_content() {
			$content        = '';
			$system_details = WPZOOM_Demo_Importer_Log::get_system_details();

			if ( is_array( $system_details ) ) {
				$content .= '<pre class="code" style="white-space: pre-wrap;">';
				foreach ( $system_details as $detail ) {
					$content .= $detail;
				}
				$content .= '</pre>';
			}
			return apply_filters( 'wpzoom_demo_import_system_details_content', $content );
		}

		/**
		 * Get advanced settings content
		 *
		 * @param array $data The modal data.
		 * @return string
		 */
		public function get_advanced_settings_content( $data = array() ) {
			$content           = '';
			$advanced_settings = apply_filters( 'wpzoom_demo_import_advanced_settings', $this->advanced_settings );
			$action_type       = ( isset( $data['action_type'] ) ? sanitize_text_field( $data['action_type'] ) : 'prepare-xml' );

			if ( is_array( $advanced_settings ) ) {
				$content .= '<ul>';
				foreach ( $advanced_settings as $setting ) {
					/**
					 * Disable all settings and keep enabled only the "Delete Previously Imported Demo".
					 *
					 * @since 2.0.0
					 */
					if ( 'delete-imported-demo-content' === $action_type && 'wpzoom-demo-importer_delete-imported-demo' !== $setting['id'] ) {
						$setting['disabled'] = true;
						$setting['default']  = false;
					}

					$input_value = str_replace( 'wpzoom-demo-importer_', '', $setting['id'] );
					$disabled    = isset( $setting['disabled'] ) ? wp_validate_boolean( $setting['disabled'] ) : false;

					$content .= '<li id="' . esc_attr( $setting['id'] ) . '">';
					$content .= '<h5>';
					$content .= '<label for="' . esc_attr( $input_value ) . '">';
					$content .= '<input id="' . esc_attr( $input_value ) . '" type="checkbox" name="advanced_settings[]" value="' . esc_attr( $input_value ) . '" ' . checked( $setting['default'], true, false ) . ' ' . disabled( $disabled, true, false ) . '/> ' . wp_kses_post( $setting['label'] ) . '';
					$content .= '</label>';
					$content .= ! empty( $setting['description'] ) ? '<span data-toggle-tooltip="' . esc_attr( $setting['id'] ) . '"></span>' : '';
					$content .= '</h5>';
					$content .= ! empty( $setting['description'] ) ? '<p class="wpzoom-tooltip" data-tooltip-id="' . esc_attr( $setting['id'] ) . '">' . wp_kses_post( $setting['description'] ) . '</p>' : '';
					$content .= '</li>';
				}
				$content .= '</ul>';
			}
			return apply_filters( 'wpzoom_demo_import_advanced_settings_content', $content );
		}

		/**
		 * Process template data
		 *
		 * @return void
		 */
		public function process_template_data() {

			// Verify Nonce.
			check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

			if ( ! current_user_can( 'customize' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
			}

			$cache_template_data = WPZOOM_Onboarding_Utils::onboarding_cache_get( 'wpz_demo_import', 'template_data' );
			$design              = isset( $_POST['design'] ) ? sanitize_text_field( $_POST['design'] ) : '';

			// Prefix log file name with $design.
			$upload_dir  = WPZOOM_Demo_Importer_Log::log_dir();
			$upload_path = trailingslashit( $upload_dir['path'] );
			$log_file    = $upload_path . $design . '-import-' . gmdate( 'd-M-Y-h-i-s' ) . '.txt';

			// Update log file.
			update_option( 'wpzoom_demo_importer_recent_import_log_file', $log_file, 'no' );

			if ( is_array( $cache_template_data ) && $cache_template_data['name'] === $design ) {
				wp_send_json_success( $cache_template_data );
			} else {
				WPZOOM_Onboarding::get_instance()->set_selected_design( $design );
				$cache_template_data = WPZOOM_Onboarding_Utils::onboarding_cache_get( 'wpz_demo_import', 'template_data' );
			}

			if ( ! empty( $design ) && $cache_template_data ) {
				wp_send_json_success( $cache_template_data );
			}

			wp_send_json_error( __( 'Template design was not found', 'wpzoom' ) );
		}

		/**
		 * Prepare template options
		 *
		 * @return void
		 */
		public function prepare_template_options() {

			// Verify Nonce.
			check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

			if ( ! current_user_can( 'customize' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
			}

			$cache_template_data = WPZOOM_Onboarding_Utils::onboarding_cache_get( 'wpz_demo_import', 'template_data' );
			$design              = isset( $_POST['design'] ) ? sanitize_text_field( $_POST['design'] ) : '';

			if ( $design === $cache_template_data['name'] ) {
				$template_data               = array();
				$is_process_widgets          = isset( $_POST['widgets'] ) ? wp_validate_boolean( $_POST['widgets'] ) : false;
				$is_customizer_settings      = isset( $_POST['customizer_settings'] ) ? wp_validate_boolean( $_POST['customizer_settings'] ) : false;
				$is_process_template_options = isset( $_POST['template_options'] ) ? wp_validate_boolean( $_POST['template_options'] ) : false;
				$wpforms_path                = get_parent_theme_file_path( '/functions/wpforms/wpforms-form-' . $design . '.json' );
				$theme_designs               = WPZOOM_Onboarding::get_instance()->get_theme_designs();
				$design_url                  = $theme_designs[ $design ]['preview_url'];

				$template_data['design_slug'] = $design;
				$template_data['design_id']   = WPZOOM_Onboarding::get_instance()->get_design_id( $design );
				$template_data['design_url']  = esc_url( $design_url );

				// Provide wpforms path url.
				if ( self::get_filesystem()->exists( $wpforms_path ) ) {
					$template_data['wpforms_path'] = $wpforms_path;
				}

				if ( $is_process_widgets ) {
					$widgets_data_default  = get_parent_theme_file_path( '/functions/widgets/default.json' );
					$template_widgets_data = get_parent_theme_file_path( '/functions/widgets/' . $design . '.json' );

					if ( self::get_filesystem()->exists( $template_widgets_data ) ) {
						$widgets_data_default = $template_widgets_data;
					}

					/* backwards compatibility */
					if ( ! self::get_filesystem()->exists( $widgets_data_default ) && defined( 'THEME_INC' ) ) {
						$widgets_data_default = THEME_INC . '/widgets/default.json';
					}

					if ( self::get_filesystem()->exists( $widgets_data_default ) ) {
						$template_data['widgets_data'] = self::get_filesystem()->get_contents( $widgets_data_default );
					}
				}

				if ( $is_customizer_settings ) {
					
					$customizer_settings_default  = get_parent_theme_file_path( '/functions/customizer/default.dat' );
					$customizer_settings_data = get_parent_theme_file_path( '/functions/customizer/' . $design . '.dat' );

					if ( self::get_filesystem()->exists( $customizer_settings_data ) ) {
						$customizer_settings_default = $customizer_settings_data;
					}

					/* backwards compatibility */
					if ( ! self::get_filesystem()->exists( $customizer_settings_default ) && defined( 'THEME_INC' ) ) {
						$customizer_settings_default = THEME_INC . '/functions/customizer/default.dat';
					}

					if ( self::get_filesystem()->exists( $customizer_settings_default ) ) {
						$template_data['customizer_data'] = $customizer_settings_default;
					}
				}

				if ( $is_process_template_options ) {
					$options_data_default  = get_parent_theme_file_path( '/functions/customizer/template-options-default.json' );
					$template_options_data = get_parent_theme_file_path( '/functions/customizer/template-options-' . $design . '.json' );

					if ( self::get_filesystem()->exists( $template_options_data ) ) {
						$options_data_default = $template_options_data;
					}

					/* backwards compatibility */
					if ( ! self::get_filesystem()->exists( $options_data_default ) && defined( 'THEME_INC' ) ) {
						$options_data_default = THEME_INC . '/customizer/template-options-default.json';
					}

					if ( self::get_filesystem()->exists( $options_data_default ) ) {
						$template_data['options_data'] = self::get_filesystem()->get_contents( $options_data_default );
					}
					
				}

				if ( ! empty( $template_data ) ) {
					update_option( 'wpzoom_demo_importer_import_data', $template_data, 'no' );
					wp_send_json_success( $template_data );
				}
				wp_send_json_error( __( 'Template data are empty!', 'wpzoom' ) );
			} else {
				wp_send_json_error( __( 'Invalid template design!', 'wpzoom' ) );
			}
		}

		/**
		 * Set reset data.
		 *
		 * @since 2.0.0
		 */
		public function get_reset_data() {
			if ( wp_doing_ajax() ) {
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
			}

			global $wpdb;

			$post_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wpzoom_demo_importer_imported_post'" );
			$form_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wpzoom_demo_importer_imported_wp_forms'" );
			$term_ids = $wpdb->get_col( "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key='_wpzoom_demo_importer_imported_term'" );

			$data = array(
				'reset_posts'    => $post_ids,
				'reset_wp_forms' => $form_ids,
				'reset_terms'    => $term_ids,
			);

			if ( wp_doing_ajax() ) {
				wp_send_json_success( $data );
			}

			return $data;
		}

		/**
		 * Backup our existing settings.
		 *
		 * @since 2.0.0
		 */
		public function backup_settings() {
			if ( ! defined( 'WP_CLI' ) && wp_doing_ajax() ) {
				check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( __( 'User does not have permission!', 'wpzoom' ) );
				}
			}

			$selected_design = WPZOOM_Onboarding::get_instance()->get_selected_design();
			$file_name       = $selected_design . '-backup-' . gmdate( 'd-M-Y-h-i-s' ) . '.json';
			$old_settings    = $this->export_data_for_backup();
			$upload_dir      = WPZOOM_Demo_Importer_Log::get_instance()->log_dir();
			$upload_path     = trailingslashit( $upload_dir['path'] );
			$log_file        = $upload_path . $file_name;
			$file_system     = self::get_instance()->get_filesystem();

			// If file system fails? Then take a backup in site option.
			if ( false === $file_system->put_contents( $log_file, wp_json_encode( $old_settings ), FS_CHMOD_FILE ) ) {
				update_option( 'wpzoom_demo_importer_' . $file_name, $old_settings, 'no' );
			}

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( 'File generated at ' . $log_file );
			} elseif ( wp_doing_ajax() ) {
				wp_send_json_success();
			}
		}

		/**
		 * Import complete
		 *
		 * @param array $demo_data Demo data including widgets, options, design url...
		 * @return void
		 */
		public function import_complete( $demo_data ) {
			$wxr_id = get_site_option( 'wpzoom_demo_importer_imported_wxr_id', 0 );
			if ( $wxr_id ) {
				wp_delete_attachment( $wxr_id, true );
				zoom_error_log( 'Deleted Temporary WXR file ' . $wxr_id );
				delete_option( 'wpzoom_demo_importer_imported_wxr_id' );
				zoom_error_log( 'Option `wpzoom_demo_importer_imported_wxr_id` Deleted.' );

				/**
				 * Update imported demo option.
				 */
				set_theme_mod( 'wpz_demo_imported', $demo_data['design_id'] );
				set_theme_mod( 'wpz_demo_imported_timestamp', current_time( 'timestamp' ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
				zoom_error_log( 'Options `wpz_demo_imported` and `wpz_demo_imported_timestamp` Updated.' );
			}
		}

		/**
		 * Export data for backup
		 *
		 * @return array
		 */
		protected function export_data_for_backup() {
			$old_settings = get_option( 'wpzoom-settings', array() );
			if ( empty( $old_settings ) ) {
				$mods           = base64_encode( serialize( get_theme_mods() ) ); // phpcs:ignore
				$options        = base64_encode( serialize( wp_load_alloptions() ) ); // phpcs:ignore
				$theme_options  = option::export_options();
				$widget_options = option::export_widgets();

				return compact( 'mods', 'options', 'theme_options', 'widget_options' );
			}
			return $old_settings;
		}

		/**
		 * Parse Elementor Data before starts batch process
		 *
		 * @since 2.0.0
		 */
		public function before_elementor_batch_process() {

			// Verify Nonce.
			check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
			}

			$pages = get_option( 'wpzoom_demo_importer_elementor_pages', array() );

			if ( ! $pages ) {
				wp_send_json_error( __( 'Elementor Pages are empty!', 'wpzoom' ) );
			}

			$page_ids = array_values( $pages );

			WPZOOM_Demo_Importer_Log::add( PHP_EOL . '----' . PHP_EOL );

			wp_send_json_success( $page_ids );
		}

		/**
		 * Elementor Batch Process via AJAX
		 *
		 * @since 2.0.0
		 */
		public function elementor_batch_process() {

			// Verify Nonce.
			check_ajax_referer( 'wpzoom-demo-importer', '_ajax_nonce' );

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'wpzoom' ) );
			}

			$meta           = '';
			$post_id        = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : 0;
			$post_title     = get_the_title( $post_id );
			$post_type      = get_post_type( $post_id );
			$elementor_data = get_post_meta( $post_id, '_elementor_data', true );

			if( 'product' == $post_type ) {
				wp_send_json_success( $post_type );	
			}

			if ( ! $post_id ) {
				$message = __( 'Invalid Post ID', 'wpzoom' );
				$data    = compact( 'post_title', 'message' );
				wp_send_json_error( $data );
			}

			if ( ! empty( $elementor_data ) && is_string( $elementor_data ) ) {
				$meta = json_decode( $elementor_data, true );
			}
			if ( ! is_array( $meta ) ) {
				/* translators: %s The type of meta */
				$message = sprintf( __( 'Elementor Meta should be an Array, %s given.', 'wpzoom' ), gettype( $meta ) );
				$data    = compact( 'post_title', 'message' );
				wp_send_json_error( $data );
			}

			$import      = new \Elementor\TemplateLibrary\WPZOOM_Elementor_Pages();
			$import_data = $import->import( $post_id, $meta );
			$post_title  = get_the_title( $post_id );

			$data = compact( 'post_title', 'import_data' );
			wp_send_json_success( $data );
		}

		/**
		 * First of all wee need to check all advanced settings compatibility
		 * For example: If Image Editor doesn't support image resize then we need to disable regenerate attachments from advanced settings.
		 *
		 * @since 2.0.0
		 *
		 * @param array $advanced_settings The main array of advanced settings.
		 * @return array The modified advanced settings array.
		 */
		public function check_advanced_settings_compatibility( $advanced_settings ) {
			// phpcs:disable PHPCompatibility.FunctionUse.NewFunctions.array_columnFound

			/**
			 * Image Editor doesn't support image resize method.
			 */
			if ( ! wp_image_editor_supports( array( 'methods' => array( 'resize' ) ) ) ) {
				$regenerate_thumbs   = array_search( 'wpzoom-demo-importer_regenerate-thumbnails', array_column( $advanced_settings, 'id' ) );
				$regenerate_featured = array_search( 'wpzoom-demo-importer_regenerate-featured', array_column( $advanced_settings, 'id' ) );

				$advanced_settings[ $regenerate_thumbs ]['default']    = false;
				$advanced_settings[ $regenerate_thumbs ]['disabled']   = true;
				$advanced_settings[ $regenerate_featured ]['default']  = false;
				$advanced_settings[ $regenerate_featured ]['disabled'] = true;
			}

			/**
			 * Child theme is already installed.
			 */
			if ( ZOOM_Child_Theme::instance()->child_theme_exists() ) {
				$install_child_theme   = array_search( 'wpzoom-demo-importer_install-child-theme', array_column( $advanced_settings, 'id' ) );
				$copy_content_to_child = array_search( 'wpzoom-demo-importer_copy-data-to-child-theme', array_column( $advanced_settings, 'id' ) );

				$advanced_settings[ $copy_content_to_child ]['default']   = false;
				$advanced_settings[ $install_child_theme ]['default']     = false;
				$advanced_settings[ $install_child_theme ]['disabled']    = true;
				$advanced_settings[ $install_child_theme ]['description'] = esc_html__( 'Child Theme is already installed', 'wpzoom' );
			}

			/**
			 * We have previously imported demo content.
			 */
			$theme_demos_data = get_demos_details();
			$theme_designs_id = array_column( $theme_demos_data['demos'], 'id' );

			if ( '' === $theme_demos_data['imported'] || ! in_array( $theme_demos_data['imported'], $theme_designs_id ) ) {
				$delete_imported_demo = array_search( 'wpzoom-demo-importer_delete-imported-demo', array_column( $advanced_settings, 'id' ) );
				unset( $advanced_settings[ $delete_imported_demo ] );
				$advanced_settings = array_values( $advanced_settings );
			}

			/**
			 * Theme is not builded with Elementor.
			 */
			$is_elementor_recommended = WPZOOM_TGMPA_Assistance::get_instance()->is_recommended_plugin( 'elementor' );
			if ( ! $is_elementor_recommended ) {
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
	WPZOOM_Demo_Import::get_instance();
}