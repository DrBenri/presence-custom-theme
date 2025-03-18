<?php
/**
 * WPZOOM Demo Importer Log
 *
 * @since 2.0.0
 * @package WPZOOM
 * @subpackage Demo Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Demo_Importer_Log' ) ) :

	/**
	 * WPZOOM Demo Importer
	 */
	class WPZOOM_Demo_Importer_Log {

		/**
		 * Instance
		 *
		 * @since 2.0.0
		 * @var (Object) Class object
		 */
		private static $instance = null;

		/**
		 * Log File
		 *
		 * @since 2.0.0
		 * @var (Object) Class object
		 */
		private static $log_file = null;

		/**
		 * Set Instance
		 *
		 * @since 2.0.0
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
		 * @since 2.0.0
		 */
		private function __construct() {

			// Check file read/write permissions.
			add_action( 'admin_init', array( $this, 'has_file_read_write' ) );
		}

		/**
		 * Check file read/write permissions and process.
		 *
		 * @since 2.0.0
		 * @return null
		 */
		public function has_file_read_write() {
			$upload_dir = self::log_dir();

			$file_created = WPZOOM_Demo_Import::get_instance()->get_filesystem()->put_contents( $upload_dir['path'] . 'index.html', '' );
			if ( ! $file_created ) {
				add_action( 'admin_notices', array( $this, 'file_permission_notice' ) );
				return;
			}

			// Set log file.
			self::set_log_file();

			// Initial AJAX Import Hooks.
			add_action( 'wpzoom_demo_importer_import_start', array( $this, 'start' ), 10, 2 );
		}

		/**
		 * File Permission Notice
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function file_permission_notice() {
			$upload_dir = self::log_dir();
			?>
			<div class="notice notice-error wpzoom-demo-importer-must-notices wpzoom-demo-importer-file-permission-issue">
				<p><?php esc_html_e( 'Required File Permissions to import the templates are missing.', 'wpzoom' ); ?></p>
				<?php if ( defined( 'FS_METHOD' ) ) { ?>
					<p><?php esc_html_e( 'This is usually due to inconsistent file permissions.', 'wpzoom' ); ?></p>
					<p><code><?php echo esc_html( $upload_dir['path'] ); ?></code></p>
				<?php } else { ?>
					<p><?php esc_html_e( 'You can easily update permissions by adding the following code into the wp-config.php file.', 'wpzoom' ); ?></p>
					<p><code>define( 'FS_METHOD', 'direct' );</code></p>
				<?php } ?>
			</div>
			<?php
		}

		/**
		 * Add log file URL in UI response.
		 *
		 * @since 2.0.0
		 */
		public static function add_log_file_url() {
			$upload_dir   = self::log_dir();
			$upload_path  = trailingslashit( $upload_dir['url'] );
			$file_abs_url = get_option( 'wpzoom_demo_importer_recent_import_log_file', self::$log_file );
			$file_url     = $upload_path . basename( $file_abs_url );

			return array(
				'abs_url' => $file_abs_url,
				'url'     => $file_url,
			);
		}

		/**
		 * Current Time for log.
		 *
		 * @since 2.0.0
		 * @return string Current time with time zone.
		 */
		public static function current_time() {
			return gmdate( 'H:i:s' ) . ' ' . date_default_timezone_get();
		}

		/**
		 * Import Start
		 *
		 * @since 2.0.0
		 * @param  array  $data         Import Data.
		 * @param  string $demo_api_uri Import site API URL.
		 * @return void
		 */
		public function start( $data = array(), $demo_api_uri = '' ) {
			self::add( 'Started Import Process' );

			self::add( '# System Details: ' );
			self::add( "Debug Mode \t\t: " . self::get_debug_mode() );
			self::add( "Operating System \t: " . self::get_os() );
			self::add( "Software \t\t: " . self::get_software() );
			self::add( "MySQL version \t\t: " . self::get_mysql_version() );
			self::add( "XML Reader \t\t: " . self::get_xmlreader_status() );
			self::add( "PHP Version \t\t: " . self::get_php_version() );
			self::add( "PHP Max Input Vars \t: " . self::get_php_max_input_vars() );
			self::add( "PHP Max Post Size \t: " . self::get_php_max_post_size() );
			self::add( "PHP Extension GD \t: " . self::get_php_extension_gd() );
			self::add( "PHP Max Execution Time \t: " . self::get_max_execution_time() );
			self::add( "Max Upload Size \t\t: " . size_format( wp_max_upload_size() ) );
			self::add( "Memory Limit \t\t: " . self::get_memory_limit() );
			self::add( "Timezone \t\t: " . self::get_timezone() );
			self::add( PHP_EOL . '-----' . PHP_EOL );
			self::add( 'Importing Started! - ' . self::current_time() );

			self::add( '---' . PHP_EOL );
			self::add( 'WHY IMPORT PROCESS CAN FAIL? READ THIS - ' );
			self::add( 'https://wpzoom.com/#url' . PHP_EOL ); // TODO: add informative link.
			self::add( '---' . PHP_EOL );
		}

		/**
		 * Get system details
		 *
		 * @return array
		 */
		public static function get_system_details() {
			$data = array(
				"Debug Mode \t\t: " . self::get_debug_mode() . "\n",
				"Operating System \t: " . self::get_os() . "\n",
				"Software \t\t: " . self::get_software() . "\n",
				"MySQL version \t\t: " . self::get_mysql_version() . "\n",
				"XML Reader \t\t: " . self::get_xmlreader_status() . "\n",
				"Timezone \t\t: " . self::get_timezone() . "\n",
			);
			return apply_filters( 'wpzoom_demo_importer_system_details', $data );
		}

		/**
		 * Get server requirements
		 *
		 * @return array
		 */
		public static function get_server_requirements() {
			$data = array(
				'php-max-exec-time'  => array(
					'recommended' => 90,
					'current'     => self::get_max_execution_time(),
					'label'       => 'PHP Max Execution Time (seconds)',
				),
				'max-upload-size'    => array(
					'recommended' => '8M',
					'current'     => size_format( wp_max_upload_size() ),
					'label'       => 'Max Upload Size',
				),
				'php-max-input-time' => array(
					'recommended' => 60,
					'current'     => self::get_php_max_input_time(),
					'label'       => 'PHP Max Input Time',
				),
				'php-max-post-size'  => array(
					'recommended' => '5M',
					'current'     => self::get_php_max_post_size(),
					'label'       => 'PHP Max Post Size',
				),
				'php-extension-gd'   => array(
					'recommended' => 'Yes',
					'current'     => self::get_php_extension_gd(),
					'label'       => 'PHP Extension GD',
				),
				'memory-limit'       => array(
					'recommended' => '256M',
					'current'     => self::get_memory_limit(),
					'label'       => 'Memory Limit',
				),
				'php-version'        => array(
					'recommended' => '7.2',
					'current'     => self::get_php_version(),
					'label'       => 'PHP Version',
				),
			);
			return apply_filters( 'wpzoom_demo_importer_server_requirements', $data );
		}

		/**
		 * Get Log File
		 *
		 * @since 2.0.0
		 * @return string log file URL.
		 */
		public static function get_log_file() {
			return self::$log_file;
		}

		/**
		 * Log file directory
		 *
		 * @since 2.0.0
		 * @param  string $dir_name Directory Name.
		 * @return array    Uploads directory array.
		 */
		public static function log_dir( $dir_name = 'wpzoom' ) {
			$upload_dir = wp_upload_dir();

			// Build the paths.
			$dir_info = array(
				'path' => $upload_dir['basedir'] . '/' . $dir_name . '/',
				'url'  => $upload_dir['baseurl'] . '/' . $dir_name . '/',
			);

			// Create the upload dir if it doesn't exist.
			if ( ! file_exists( $dir_info['path'] ) ) {

				// Create the directory.
				wp_mkdir_p( $dir_info['path'] );

				// Add an index file for security.
				WPZOOM_Demo_Import::get_instance()->get_filesystem()->put_contents( $dir_info['path'] . 'index.html', '' );
			}

			return $dir_info;
		}

		/**
		 * Set log file
		 *
		 * @since 2.0.0
		 */
		public static function set_log_file() {
			$upload_dir = self::log_dir();

			$upload_path     = trailingslashit( $upload_dir['path'] );
			$selected_design = WPZOOM_Onboarding::get_instance()->get_selected_design();

			// File format e.g. '{selected_design}-import-30-Aug-2021-06-39-12.txt'.
			self::$log_file = $upload_path . $selected_design . '-import-' . gmdate( 'd-M-Y-h-i-s' ) . '.txt';

			if ( ! get_option( 'wpzoom_demo_importer_recent_import_log_file', false ) ) {
				update_option( 'wpzoom_demo_importer_recent_import_log_file', self::$log_file, 'no' );
			}
		}

		/**
		 * Write content to a file.
		 *
		 * @since 2.0.0
		 * @param string $content content to be saved to the file.
		 */
		public static function add( $content ) {
			if ( get_option( 'wpzoom_demo_importer_recent_import_log_file', false ) ) {
				$log_file = get_option( 'wpzoom_demo_importer_recent_import_log_file', self::$log_file );
			} else {
				$log_file = self::$log_file;
			}

			$existing_data = '';
			if ( file_exists( $log_file ) ) {
				$existing_data = WPZOOM_Demo_Import::get_instance()->get_filesystem()->get_contents( $log_file );
			}

			// Style separator.
			$separator = PHP_EOL;

			if ( apply_filters( 'wpzoom_demo_importer_debug_logs', false ) ) {
				zoom_error_log( $content );

				WPZOOM_Demo_Import::get_instance()->get_filesystem()->put_contents( $log_file, $existing_data . $separator . $content, FS_CHMOD_FILE );
			}
		}

		/**
		 * Debug Mode
		 *
		 * @since 2.0.0
		 * @return string Enabled for Debug mode ON and Disabled for Debug mode Off.
		 */
		public static function get_debug_mode() {
			if ( WP_DEBUG ) {
				return __( 'Enabled', 'wpzoom' );
			}

			return __( 'Disabled', 'wpzoom' );
		}

		/**
		 * Memory Limit
		 *
		 * @since 2.0.0
		 * @return string Memory limit.
		 */
		public static function get_memory_limit() {
			return ini_get( 'memory_limit' ); // Maybe WP_MEMORY_LIMIT?
		}

		/**
		 * Timezone
		 *
		 * @since 2.0.0
		 * @see https://codex.wordpress.org/Option_Reference/
		 *
		 * @return string Current timezone.
		 */
		public static function get_timezone() {
			$timezone = get_option( 'timezone_string' );

			if ( ! $timezone ) {
				return get_option( 'gmt_offset' );
			}

			return $timezone;
		}

		/**
		 * Operating System
		 *
		 * @since 2.0.0
		 * @return string Current Operating System.
		 */
		public static function get_os() {
			return PHP_OS;
		}

		/**
		 * Server Software
		 *
		 * @since 2.0.0
		 * @return string Current Server Software.
		 */
		public static function get_software() {
			return $_SERVER['SERVER_SOFTWARE'];
		}

		/**
		 * MySql Version
		 *
		 * @since 2.0.0
		 * @return string Current MySql Version.
		 */
		public static function get_mysql_version() {
			global $wpdb;
			return $wpdb->db_version();
		}

		/**
		 * XML Reader
		 *
		 * @since 1.2.8
		 * @return string Current XML Reader status.
		 */
		public static function get_xmlreader_status() {
			if ( class_exists( 'XMLReader' ) ) {
				return __( 'Yes', 'wpzoom' );
			}

			return __( 'No', 'wpzoom' );
		}

		/**
		 * PHP Version
		 *
		 * @since 2.0.0
		 * @return string Current PHP Version.
		 */
		public static function get_php_version() {
			return PHP_VERSION;
		}

		/**
		 * PHP Max Input Vars
		 *
		 * @since 2.0.0
		 * @return string Current PHP Max Input Vars
		 */
		public static function get_php_max_input_vars() {
			// @codingStandardsIgnoreStart
			return ini_get( 'max_input_vars' ); // phpcs:disable PHPCompatibility.IniDirectives.NewIniDirectives.max_input_varsFound
			// @codingStandardsIgnoreEnd
		}

		/**
		 * PHP Max Input Time
		 *
		 * @since 2.0.0
		 * @return string Current PHP Max Input Time
		 */
		public static function get_php_max_input_time() {
			return ini_get( 'max_input_time' );
		}

		/**
		 * PHP Max Post Size
		 *
		 * @since 2.0.0
		 * @return string Current PHP Max Post Size
		 */
		public static function get_php_max_post_size() {
			return ini_get( 'post_max_size' );
		}

		/**
		 * PHP Max Execution Time
		 *
		 * @since 2.0.0
		 * @return string Current Max Execution Time
		 */
		public static function get_max_execution_time() {
			return ini_get( 'max_execution_time' );
		}

		/**
		 * PHP GD Extension
		 *
		 * @since 2.0.0
		 * @return string Current PHP GD Extension
		 */
		public static function get_php_extension_gd() {
			if ( extension_loaded( 'gd' ) ) {
				return __( 'Yes', 'wpzoom' );
			}

			return __( 'No', 'wpzoom' );
		}

		/**
		 * Display Data
		 *
		 * @since 2.0.0
		 * @return string The raw HTML snapshots table.
		 */
		public function display_data() {
			$upload_dir   = self::log_dir();
			$list_files   = list_files( $upload_dir['path'] );
			$backup_files = array();
			$log_files    = array();
			$snapshots    = array();

			foreach ( $list_files as $file ) {
				if ( strpos( $file, '.json' ) ) {
					$file_name                      = basename( $file );
					$design_slug                    = WPZOOM_Onboarding_Utils::extract_design_slug_from_filename( $file_name );
					$backup_files[ $design_slug ][] = $file;
				}
				if ( strpos( $file, '.txt' ) ) {
					$file_name                   = basename( $file );
					$design_slug                 = WPZOOM_Onboarding_Utils::extract_design_slug_from_filename( $file_name );
					$log_files[ $design_slug ][] = $file;
				}
			}

			/**
			 * Get the latest created file by type.
			 */
			foreach ( $backup_files as $files ) {
				$file           = $this->get_latest_created_file( $files );
				$file_name      = basename( $file );
				$file_size      = WPZOOM_Onboarding_Utils::get_file_size( $file );
				$file_date_time = WPZOOM_Onboarding_Utils::get_file_date_time( $file );
				$design_slug    = WPZOOM_Onboarding_Utils::extract_design_slug_from_filename( $file_name );
				$design_name    = WPZOOM_Onboarding::get_instance()->get_design_name( $design_slug );
				$file           = str_replace( $upload_dir['path'], $upload_dir['url'], $file );

				$snapshots[ $design_slug ]['design_slug']           = $design_slug;
				$snapshots[ $design_slug ]['design_name']           = $design_name;
				$snapshots[ $design_slug ]['backup_file']           = $file;
				$snapshots[ $design_slug ]['backup_file_name']      = $file_name;
				$snapshots[ $design_slug ]['backup_file_size']      = size_format( $file_size );
				$snapshots[ $design_slug ]['backup_file_date_time'] = $file_date_time;
			}

			foreach ( $log_files as $files ) {
				$file           = $this->get_latest_created_file( $files );
				$file_name      = basename( $file );
				$file_size      = WPZOOM_Onboarding_Utils::get_file_size( $file );
				$file_date_time = WPZOOM_Onboarding_Utils::get_file_date_time( $file );
				$design_slug    = WPZOOM_Onboarding_Utils::extract_design_slug_from_filename( $file_name );
				$design_name    = WPZOOM_Onboarding::get_instance()->get_design_name( $design_slug );
				$file           = str_replace( $upload_dir['path'], $upload_dir['url'], $file );

				$snapshots[ $design_slug ]['design_slug']        = $design_slug;
				$snapshots[ $design_slug ]['design_name']        = $design_name;
				$snapshots[ $design_slug ]['log_file']           = $file;
				$snapshots[ $design_slug ]['log_file_name']      = $file_name;
				$snapshots[ $design_slug ]['log_file_size']      = size_format( $file_size );
				$snapshots[ $design_slug ]['log_file_date_time'] = $file_date_time;
			}

			// Sort the array.
			if ( ! empty( $snapshots ) && count( $snapshots ) > 1 ) {
				usort( $snapshots, array( $this, 'date_compare' ) );
			}

			ob_start();
			?>
			<table class="wpz-onboard_snapshots-table widefat">
				<tr>
					<th><?php esc_html_e( 'Title', 'wpzoom' ); ?></th>
					<th><?php esc_html_e( 'Date', 'wpzoom' ); ?></th>
					<th><?php esc_html_e( 'Log file', 'wpzoom' ); ?></th>
					<th><?php esc_html_e( 'Backup file', 'wpzoom' ); ?></th>
					<th><?php esc_html_e( 'Size', 'wpzoom' ); ?></th>
					<th class="text-right"><?php esc_html_e( 'Actions', 'wpzoom' ); ?></th>
				</tr>
				<?php if ( ! empty( $snapshots ) ) : ?>
					<?php foreach ( $snapshots as $key => $snapshot ) : ?>
						<?php $row_id = md5( $snapshot['backup_file_name'] ); ?>
						<tr class="wpz-onboard_snapshot-row" data-row-id="<?php echo esc_attr( $row_id ); ?>">
							<td title="Design name"><?php echo esc_html( $snapshot['design_name'] ); ?></td>
							<td title="Log file date modified"><?php echo esc_html( $snapshot['log_file_date_time'] ); ?></td>
							<td><a target="_blank" href="<?php echo esc_attr( $snapshot['log_file'] ); ?>"><?php echo esc_html( $snapshot['log_file_name'] ); ?></a></td>
							<td><a target="_blank" href="<?php echo esc_attr( $snapshot['backup_file'] ); ?>"><?php echo esc_html( $snapshot['backup_file_name'] ); ?></a></td>
							<td title="Backup file size"><?php echo esc_html( $snapshot['backup_file_size'] ); ?></td>
							<td class="text-right">
								<a href="#" class="button button-secondary button-toggle-dropdown-actions" data-toggle="dropdown-actions-<?php echo esc_attr( $row_id ); ?>"></a>
								<div class="wpz-onboard_snapshot-dropdown-actions" id="dropdown-actions-<?php echo esc_attr( $row_id ); ?>">
									<a href="#" class="dropdown-actions-item" data-action="download"><?php echo esc_html_x( 'Download log file', 'Download snapshot log file', 'wpzoom' ); ?></a>
									<a href="#" class="dropdown-actions-item" data-action="restore"><?php echo esc_html_x( 'Restore backup', 'Restore snapshot backup', 'wpzoom' ); ?></a>
									<a href="#" class="dropdown-actions-item" data-action="delete"><?php echo esc_html_x( 'Delete snapshot', 'Delete log & backup files', 'wpzoom' ); ?></a>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td colspan="6"><?php esc_html_e( 'There are no created snapshots. The snapshot include log and backup files which are automatically generated on demo import process.', 'wpzoom' ); ?></td></tr>
				<?php endif; ?>
			</table>
			<?php
			$output = ob_get_contents();
			ob_get_clean();
			return $output;
		}

		/**
		 * Receive the latest created file by given $files array.
		 *
		 * @param array $files The files array to check the latest created one.
		 * @return string
		 */
		protected function get_latest_created_file( $files ) {
			$files = array_combine( $files, array_map( 'filectime', $files ) );
			arsort( $files );
			$latest_file = key( $files );
			return $latest_file;
		}

		/**
		 * Compare dates to sort.
		 *
		 * @param array $date1 Date to compare.
		 * @param array $date2 Date to compare.
		 * @return int
		 */
		protected function date_compare( $date1, $date2 ) {
			$datetime1 = strtotime( $date1['log_file_date_time'] );
			$datetime2 = strtotime( $date2['log_file_date_time'] );
			return $datetime1 - $datetime2;
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	WPZOOM_Demo_Importer_Log::get_instance();
endif;
