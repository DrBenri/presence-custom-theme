<?php
/**
 * Onboarding Activate License.
 *
 * @package WPZOOM
 * @subpackage Onboarding
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPZOOM_Onboarding_License' ) ) {

	/**
	 * Activate License Key.
	 *
	 * @since 2.0.0
	 *
	 * @package WPZOOM
	 */
	#[AllowDynamicProperties]
	class WPZOOM_Onboarding_License {

		/**
		 * WP_Theme object.
		 *
		 * @var object
		 */
		public $theme;

		/**
		 * EDD theme updater remote api url.
		 *
		 * @var string
		 */
		protected $remote_api_url;

		/**
		 * Theme slug.
		 *
		 * @var string
		 */
		public $theme_slug;

		/**
		 * Theme version.
		 *
		 * @var string
		 */
		public $version;

		/**
		 * Author.
		 *
		 * @var object
		 */
		protected $author;

		/**
		 * EDD Download id.
		 *
		 * @var int
		 */
		protected $download_id;

		/**
		 * Renew url.
		 *
		 * @var string
		 */
		protected $renew_url;

		/**
		 * EDD configuration strings.
		 *
		 * @var array
		 */
		public $strings;

		/**
		 * EDD configuration arguments.
		 *
		 * @var array
		 */
		protected $config;

		/**
		 * WPZOOM_Onboarding class instance.
		 *
		 * @access private
		 * @var object
		 */
		private static $instance;

		/**
		 * Initiator.
		 *
		 * @param array $config The updater configuration arguments.
		 * @param array $strings The strings for class.
		 */
		public static function get_instance( $config = array(), $strings = array() ) {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self( $config, $strings );
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @param array $config The updater configuration arguments.
		 * @param array $strings The strings for class.
		 */
		private function __construct( $config, $strings ) {
			// Update class strings for our needs.
			add_filter( 'wpzoom_onboarding_license_strings', array( $this, 'update_class_strings' ) );

			// We're ready to init our class configs.
			$this->init( $config, $strings );

			// Register schedule event for checking license.
			add_action( 'init', array( $this, 'set_schedule' ) );
			add_action( 'wpzoom_onboarding_check_license_cron_hook', array( $this, 'execute_cron' ) );

			// Setup theme updater.
			add_action( 'admin_init', array( $this, 'updater_setup' ) );

			// Actions.
			add_action( 'wp_ajax_wpzoom_set_license', array( $this, 'set_license_key_ajax' ) );
			add_action( 'wp_ajax_wpzoom_deactivate_license', array( $this, 'deactivate_license_key_ajax' ) );

			// Disable wp.org request.
			add_filter( 'http_request_args', array( $this, 'disable_wporg_request' ), 5, 2 );

			// Admin notices.
			add_action( 'admin_notices', array( $this, 'license_notice' ) );
		}

		/**
		 * Init class configurations
		 *
		 * @param array $config The updater configuration arguments.
		 * @param array $strings The strings for class.
		 * @return void
		 */
		public function init( $config, $strings ) {

			// Parse config arguments.
			$this->config = wp_parse_args(
				$config,
				array(
					'remote_api_url' => 'http://easydigitaldownloads.com',
					'theme_slug'     => get_template(),
					'item_name'      => '',
					'license'        => '',
					'version'        => '',
					'author'         => '',
					'download_id'    => '',
					'renew_url'      => '',
				)
			);

			// Strings passed in from the updater config.
			$this->strings = apply_filters( 'wpzoom_onboarding_license_strings', $strings );

			/**
			 * Fires after the theme $config is setup.
			 *
			 * @param array $config Array of EDD SL theme data.
			 */
			do_action( 'wpzoom_activate_license_setup', $config );

			// Set config arguments.
			$this->remote_api_url = $config['remote_api_url'];
			$this->item_name      = $config['item_name'];
			$this->theme_slug     = $config['theme_slug'];
			$this->download_id    = $config['download_id'];
			$this->renew_url      = $config['renew_url'];

			if ( $this->theme_slug ) {
				$theme       = wp_get_theme( $this->theme_slug );
				$this->theme = $theme;
			}

			// Populate version fallback.
			if ( '' === $config['version'] ) {
				$this->config['version'] = $theme->get( 'Version' );
			}
		}

		/**
		 * Update class strings provided by Theme Updater
		 *
		 * @since 2.0.0
		 *
		 * @param array $strings The strings for class.
		 * @return array The changed strings array.
		 */
		public function update_class_strings( $strings ) {
			if ( is_array( $strings ) ) {
				$strings['license-is-inactive'] = __( 'License is <strong>inactive</strong>. Click to <strong>Save &amp; Activate</strong> button to activate it.', 'wpzoom' );
				$strings['site-is-inactive']    = __( 'This license is inactive on this website. Click to <strong>Save &amp; Activate</strong> button to activate it.', 'wpzoom' );
			}
			return $strings;
		}

		/**
		 * Register schedule event
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function set_schedule() {
			if ( ! wp_next_scheduled( 'wpzoom_onboarding_check_license_cron_hook' ) ) {
				wp_schedule_event( time(), 'daily', 'wpzoom_onboarding_check_license_cron_hook' );
			}
		}

		/**
		 * Execute cron event
		 *
		 * @since 2.0.0
		 * @return boolean Return true.
		 */
		public function execute_cron() {
			$license_key = $this->get_license_key();
			$message     = $this->check_license( $license_key );
			return true;
		}

		/**
		 * License notices.
		 *
		 * @return string License notice depending by license status.
		 */
		public function license_notice() {
			if ( ! current_user_can( 'update_themes' ) ) {
				return;
			}

			$current_screen = get_current_screen();

            // Hide license notice from onboarding page.
            if ( is_object( $current_screen ) && 'wpzoom_page_wpzoom_license' === $current_screen->base ) {
                return;
            }

            $license_status = $this->get_license_status();

			$notice = '';
			if ( 'expired' === $license_status ) {
				/* translators: 1: theme name 2: license status 3: renewal link */
				$notice = sprintf( __( '<div class="wpz-notice-aside"><span class="wpz-circle wpz-pulse"> ! </span></div> <div class="wpz-notice-content"><p><strong>Your %1$s theme license has %2$s</strong>. Please renew it to continue receiving theme updates and support. <em><strong>Your website may be at security risk!</strong></em></p> <p><a href="%3$s" target="_blank" class="button button-primary">Renew License</a></p></div>', 'wpzoom' ), $this->theme, $license_status, $this->get_renewal_link() );
			} elseif ( 'invalid' === $license_status ) {
				/* translators: 1: theme name 2: admin license page 3: purchase license link */
				$notice = sprintf( __( '<div class="wpz-notice-aside"><span class="wpz-circle wpz-pulse"> ! </span></div> <div class="wpz-notice-content"><p>If you want to receive updates for the <strong>%1$s theme</strong>, please <strong><a href="%2$s">enter your license key</a></strong> or <strong><a href="%3$s" target="_blank">purchase a new license</a></strong> if you don\'t have one yet.</p></div>', 'wpzoom' ), $this->theme, admin_url( 'admin.php?page=wpzoom_license#license' ), 'https://www.wpzoom.com/themes/' . WPZOOM::$theme_raw_name . '/' );
			} elseif ( 'inactive' === $license_status || 'site_inactive' === $license_status ) {
				/* translators: 1: theme name 2: admin license page */
				$notice = sprintf( __( '<div class="wpz-notice-aside"><span class="wpz-circle wpz-pulse"> ! </span></div> <div class="wpz-notice-content"><p>Your <strong>%1$s theme</strong> license key is <strong>inactive</strong>. Please <strong><a href="%2$s">go to this page</a></strong> and click the <strong>Save &amp; Activate</strong> button to enable theme updates.</p></div>', 'wpzoom' ), $this->theme, admin_url( 'admin.php?page=wpzoom_license#license' ) );
            } elseif ( 'disabled' === $license_status ) {
                /* translators: 1: theme name 2: license status 3: renewal link */
                $notice = sprintf( __( '<div class="wpz-notice-aside"><span class="wpz-circle wpz-pulse"> ! </span></div> <div class="wpz-notice-content"><p>Your <strong>%1$s theme</strong> license key is <strong>%2$s</strong>, please <strong><a href="https://www.wpzoom.com/account/licenses/" target="_blank">check the status</a></strong> of your license or get in touch with the WPZOOM team for more details.</p></div>', 'wpzoom' ), $this->theme, $license_status );
			} elseif ( 'item_name_mismatch' === $license_status ) {
				/* translators: 1: theme name 2: admin license page 3: purchase license link */
				$notice = sprintf( __( '<div class="wpz-notice-aside"><span class="wpz-circle wpz-pulse"> ! </span></div> <div class="wpz-notice-content"><p>You have entered an incorrect license key for <strong>%1$s theme</strong>. Please <strong><a href="%2$s">enter a correct key</a></strong> or <strong><a href="%3$s" target="_blank">purchase a new license</a></strong> for theme you\'re currently using.</p></div>', 'wpzoom' ), $this->theme, admin_url( 'admin.php?page=wpzoom_license#license' ), 'https://www.wpzoom.com/themes/' . WPZOOM::$theme_raw_name . '/' );
			} elseif ( 'valid' !== $license_status ) {
				/* translators: 1: theme name 2: license status 3: renewal link */
				$notice = sprintf( __( '<div class="wpz-notice-aside"><span class="wpz-circle wpz-pulse"> ! </span></div> <div class="wpz-notice-content"><p>Your <strong>%1$s theme</strong> license is <strong>%2$s</strong>, please <strong><a href="%3$s">activate your license key</a></strong> to enable theme updates.</p></div>', 'wpzoom' ), $this->theme, $license_status, $this->get_renewal_link() );
			}

			if ( ! empty( $notice ) ) {
				echo '<div id="update-nag" class="notice notice-error settings-error wpz-error notice notice-error notice-alt">';
				// echo $license_status;
                echo wp_kses_post( $notice );
				echo '</div>';
			}

			return $notice;
		}

		/**
		 * Creates the updater class.
		 *
		 * @since 2.0.0
		 */
		public function updater_setup() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// If there is no valid license key status, don't allow updates.
			if ( 'valid' !== $this->get_license_status() ) {
				return;
			}

			if ( ! class_exists( 'EDD_Theme_Updater' ) ) {
				// Load our custom theme updater.
				require_once WPZOOM_INC . '/components/theme-updater/theme-updater-class.php';
			}

			if ( empty( $this->config['license'] ) ) {
				$this->config['license'] = $this->get_license_key();
			}

			new EDD_Theme_Updater( $this->config, $this->strings );
		}

		/**
		 * Handles AJAX requests to set the theme license key.
		 *
		 * @return void
		 */
		public function set_license_key_ajax() {
			$data = array();

			// phpcs:ignore
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpzoom_set_license' ) ) {
				$data = array(
					'message' => __( 'Invalid nonce.', 'wpzoom' ),
					'status'  => 'invalid_nonce',
				);
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				$data = array(
					'message' => __( 'You can\'t access this action.', 'wpzoom' ),
					'status'  => 'denied_permissions',
				);
			}

			if ( ! empty( $data ) ) {
				wp_send_json_error( $data );
			}

			$license_key = isset( $_POST['license'] ) ? sanitize_text_field( $_POST['license'] ) : '';

			update_option( $this->theme_slug . '_license_key', $license_key );

			$license_message = $this->activate_license( $license_key );
			$license_status  = $this->get_license_status();
			$label           = '';

			if ( empty( $license_message ) ) {
				$license_message = $this->check_license( $license_key );
			}
			if ( 'valid' === $license_status ) {
				$label = _x( 'Active', 'License status', 'wpzoom' );
			} elseif ( 'inactive' === $license_status ) {
				$label = _x( 'Inactive', 'License status', 'wpzoom' );
			}

			$notice = array();
			$errors = WPZOOM_Onboarding::get_instance()->get_compatibilities( array( 'errors' => true ) );
			foreach ( $errors as $error => $error_data ) {
				if ( 'empty_license_key' === $error || 'inactive_license_key' === $error || 'expired_license_key' === $error ) {
					$notice[ $error ] = $error_data;
				}
			}

			$data = array(
				'message' => $license_message,
				'status'  => $license_status,
				'label'   => $label,
			);

			if ( ! empty( $notice ) ) {
				$data['notice'] = $notice;
			}

			do_action( 'wpzoom_onboarding_set_license', $data );

			if ( 'invalid' === $license_status ) {
				wp_send_json_error( $data );
			}

			wp_send_json_success( $data );
		}

		/**
		 * Handles AJAX requests to deactivate the theme license key.
		 *
		 * @return void
		 */
		public function deactivate_license_key_ajax() {
			$data = array();

			// phpcs:ignore
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpzoom_set_license' ) ) {
				$data = array(
					'message' => __( 'Invalid nonce.', 'wpzoom' ),
					'status'  => 'invalid_nonce',
				);
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				$data = array(
					'message' => __( 'You can\'t access this action.', 'wpzoom' ),
					'status'  => 'denied_permissions',
				);
			}

			if ( ! empty( $data ) ) {
				wp_send_json_error( $data );
			}

			$license_key      = isset( $_POST['license'] ) ? sanitize_text_field( $_POST['license'] ) : '';
			$prev_license_key = isset( $_POST['prev_license_key'] ) ? sanitize_text_field( $_POST['prev_license_key'] ) : '';

			$license_message = $this->deactivate_license( $license_key, $prev_license_key );
			$license_status  = $this->get_license_status();
			$label           = '';

			if ( 'valid' === $license_status ) {
				$label = _x( 'Active', 'License status', 'wpzoom' );
			} elseif ( 'inactive' === $license_status ) {
				$label = _x( 'Inactive', 'License status', 'wpzoom' );
			}

			$notice = array();
			$errors = WPZOOM_Onboarding::get_instance()->get_compatibilities( array( 'errors' => true ) );
			foreach ( $errors as $error => $error_data ) {
				if ( 'empty_license_key' === $error || 'inactive_license_key' === $error || 'expired_license_key' === $error ) {
					$notice[ $error ] = $error_data;
				}
			}

			$data = array(
				'message' => $license_message,
				'status'  => $license_status,
				'label'   => $label,
			);

			if ( ! empty( $notice ) ) {
				$data['notice'] = $notice;
			}

			do_action( 'wpzoom_onboarding_deactivate_license', $data );

			if ( 'inactive' !== $license_status ) {
				wp_send_json_error( $data );
			}

			wp_send_json_success( $data );
		}

		/**
		 * Get license key
		 *
		 * @return mixed License key, otherwise false if not exists.
		 */
		public function get_license_key() {
			return trim( get_option( $this->theme_slug . '_license_key' ) );
		}

		/**
		 * Get license status
		 *
		 * @return mixed License status, otherwise 'inactive' as default value.
		 */
		public function get_license_status() {
			return get_option( $this->theme_slug . '_license_key_status', 'inactive' );
		}

		/**
		 * Makes a call to the API.
		 *
		 * @since 2.0.0
		 *
		 * @param array $api_params to be used for wp_remote_get.
		 * @return array $response decoded JSON response.
		 */
		public function get_api_response( $api_params ) {

			// Call the custom API.
			$verify_ssl = (bool) apply_filters( 'edd_sl_api_request_verify_ssl', true );
			$response   = wp_remote_post(
				$this->remote_api_url,
				array(
					'timeout'   => 30,
					'sslverify' => $verify_ssl,
					'body'      => $api_params,
				)
			);

			// Make sure the response came back okay.
			if ( is_wp_error( $response ) ) {
				/* translators: %s: error code */
				wp_die( $response->get_error_message(), sprintf( __( 'Error %s', 'wpzoom' ), $response->get_error_code() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			return $response;
		}

		/**
		 * Activates the license key.
		 *
		 * @since 2.0.0
		 * @param string $license_key License key.
		 * @return string Response error message. Empty if license key is succesfully activated.
		 */
		public function activate_license( $license_key ) {
			$license_data = '';
			$message      = '';

			// Data to send in our API request.
			if ( ! empty( $license_key ) ) {
				$api_params = array(
					'edd_action' => 'activate_license',
					'license'    => $license_key,
					'item_name'  => rawurlencode( $this->item_name ),
					'url'        => home_url(),
				);

				$response = $this->get_api_response( $api_params );
			} else {
				$response = new WP_Error( 'empty_license_key', __( 'Please enter a value for license key.', 'wpzoom' ) );
			}

			// make sure the response came back okay.
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.', 'wpzoom' );
				}

				$license_data          = new stdClass();
				$license_data->license = 'invalid';
			} else {
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {
					switch ( $license_data->error ) {
						case 'expired':
							$message = sprintf(
								/* translators: %s: expires license date */
								__( 'Your license key expired on %s.', 'wpzoom' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, time() ) )
							);
							break;

						case 'disabled':
						case 'revoked':
							$message = __( 'Your license key has been disabled.', 'wpzoom' );
							break;

						case 'missing':
							$message = __( 'Invalid license.', 'wpzoom' );
							break;

						case 'invalid':
						case 'site_inactive':
							$message = __( 'Your license is not active for this URL.', 'wpzoom' );
							break;

						case 'item_name_mismatch':
							/* translators: %s: theme name */
							$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'wpzoom' ), $this->item_name );
							break;

						case 'no_activations_left':
							/* translators: %s: wpzoom account licenses link */
							$message = sprintf( __( 'Your theme license key has reached its activation limit. Please <a href="%s" target="_blank">upgrade</a> your license or contact WPZOOM if you have an unlimited license.', 'wpzoom' ), esc_url( 'https://www.wpzoom.com/account/licenses/' ) );
							break;

						default:
							$message = __( 'An error occurred, please try again.', 'wpzoom' );
							break;
					}
				}
			}

			// $response->license will be either "active" or "inactive".
			if ( ! empty( $license_data ) && is_object( $license_data ) && isset( $license_data->license ) ) {
				update_option( $this->theme_slug . '_license_key_status', $license_data->license );
				delete_transient( $this->theme_slug . '_license_message' );
			}

			return $message;
		}

		/**
		 * Deactivates the license key.
		 *
		 * @since 2.0.0
		 * @param string $license_key License key.
		 * @param string $prev_license_key Previous license key. This variable meant to be used when run action via AJAX.
		 * @return string Response error message. Empty if license key is succesfully deactivated.
		 */
		public function deactivate_license( $license_key, $prev_license_key = '' ) {
			$message = '';

			// Data to send in our API request.
			if ( ! empty( $license_key ) ) {
				$api_params = array(
					'edd_action' => 'deactivate_license',
					'license'    => $license_key,
					'item_name'  => rawurlencode( $this->item_name ),
					'url'        => home_url(),
				);

				$response = $this->get_api_response( $api_params );
			} else {
				$response = new WP_Error( 'empty_license_key', __( 'Please enter a value for license key.', 'wpzoom' ) );
			}

			// make sure the response came back okay.
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.', 'wpzoom' );
				}

				$license_data          = new stdClass();
				$license_data->license = 'invalid';
			} else {
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "deactivated" or "failed".
				if ( $license_data && is_object( $license_data ) ) {
					if ( 'deactivated' === $license_data->license ) {
						delete_option( $this->theme_slug . '_license_key_status' );
						delete_transient( $this->theme_slug . '_license_message' );
						$message = __( 'License key was deactivated', 'wpzoom' );
					} elseif ( 'failed' === $license_data->license ) {
						$message = $this->check_license( $prev_license_key );
					}
				}
			}

			return $message;
		}

		/**
		 * Constructs a renewal link
		 *
		 * @since 2.0.0
		 * @return string Renewal link, otherwise remote api url.
		 */
		public function get_renewal_link() {

			// If download_id was passed in the config, a renewal link can be constructed.
			$license_key = trim( get_option( $this->theme_slug . '_license_key', false ) );
			if ( '' != $license_key ) {
				$url  = esc_url( $this->remote_api_url );
				$url .= 'checkout/?edd_license_key=' . $license_key;
				return $url;
			}

            // If a renewal link was passed in the config, use that
            if ( '' !== $this->renew_url ) {
                return $this->renew_url;
            }

			// Otherwise return the remote_api_url.
			return $this->remote_api_url;
		}

		/**
		 * Checks if license is valid and gets expire date.
		 *
		 * @since 2.0.0
		 * @param string $license_key License key to check.
		 * @return string $message License status message after it was checked.
		 */
		public function check_license( $license_key ) {
			$message = '';

			if ( ! empty( $license_key ) ) {
				$api_params = array(
					'edd_action' => 'check_license',
					'license'    => $license_key,
					'item_name'  => rawurlencode( $this->item_name ),
					'url'        => home_url(),
				);

				$response = $this->get_api_response( $api_params );
			} else {
				$response = new WP_Error( 'empty_license_key', __( 'Please enter a value for license key.', 'wpzoom' ) );
			}

			// make sure the response came back okay.
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = $this->strings['license-status-unknown'];
				}

				$license_data          = new stdClass();
				$license_data->license = 'invalid';
			} else {
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// If response doesn't include license data, return.
				if ( ! isset( $license_data->license ) ) {
					$message = $this->strings['license-status-unknown'];
					return $message;
				}

				// We need to update the license status at the same time the message is updated.
				if ( $license_data && isset( $license_data->license ) ) {
					update_option( $this->theme_slug . '_license_key_status', $license_data->license );
				}

				// Get expire date.
				$expires = false;
				if ( isset( $license_data->expires ) && 'lifetime' !== $license_data->expires ) {
					$expires    = date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, time() ) );
                    $renew_link = '<a href="' . esc_url( $this->get_renewal_link() ) . '" target="_blank">' . $this->strings['renew'] . '</a>';
				} elseif ( isset( $license_data->expires ) && 'lifetime' === $license_data->expires ) {
					$expires = 'lifetime';
				}

				// Get site counts.
				$site_count    = isset( $license_data->site_count ) ? $license_data->site_count : 0;
				$license_limit = isset( $license_data->license_limit ) ? $license_data->license_limit : 0;

				// If unlimited.
				if ( 0 == $license_limit ) {
					$license_limit = $this->strings['unlimited'];
				}

				if ( 'valid' === $license_data->license ) {
					$message = $this->strings['license-key-is-active'] . ' ';
					if ( isset( $expires ) && 'lifetime' !== $expires ) {
						$message .= sprintf( $this->strings['expires%s'], $expires ) . ' ';
					}
					if ( isset( $expires ) && 'lifetime' === $expires ) {
						$message .= $this->strings['expires-never'];
					}
					if ( $site_count && $license_limit ) {
						$message .= sprintf( $this->strings['%1$s/%2$-sites'], $site_count, $license_limit );
					}
				} elseif ( 'expired' === $license_data->license ) {
					if ( $expires ) {
						$message = sprintf( $this->strings['license-key-expired-%s'], $expires );
					} else {
						$message = $this->strings['license-key-expired'];
					}
					if ( $renew_link ) {
						$message .= ' ' . $renew_link;
					}
				} elseif ( 'invalid' === $license_data->license ) {
					$message = $this->strings['license-keys-do-not-match'];
				} elseif ( 'inactive' === $license_data->license ) {
					$message = $this->strings['license-is-inactive'];
				} elseif ( 'disabled' === $license_data->license ) {
					$message = $this->strings['license-key-is-disabled'];
				} elseif ( 'site_inactive' === $license_data->license ) {
					$message = $this->strings['site-is-inactive'];
				} else {
					$message = $this->strings['license-status-unknown'];
				}
			}

			set_transient( $this->theme_slug . '_license_message', $message, ( 60 * 60 * 24 ) );

			return $message;
		}

		/**
		 * Disable requests to wp.org repository for this theme.
		 *
		 * @since 2.0.0
		 * @param array  $parsed_args An array of HTTP request arguments.
		 * @param string $url The request URL.
		 *
		 * @return array Parsed arguments.
		 */
		public function disable_wporg_request( $parsed_args, $url ) {

			// If it's not a theme update request, bail.
			if ( 0 !== strpos( $url, 'https://api.wordpress.org/themes/update-check/1.1/' ) ) {
				return $parsed_args;
			}

			// Decode the JSON response.
			$themes = json_decode( $parsed_args['body']['themes'] );

			// Remove the active parent and child themes from the check.
			$parent = get_option( 'template' );
			$child  = get_option( 'stylesheet' );
			unset( $themes->themes->$parent );
			unset( $themes->themes->$child );

			// Encode the updated JSON response.
			$parsed_args['body']['themes'] = wp_json_encode( $themes );

			return $parsed_args;
		}
	}
}
