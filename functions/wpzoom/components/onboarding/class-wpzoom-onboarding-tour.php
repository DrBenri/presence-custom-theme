<?php
/**
 * Onboarding Tour.
 *
 * @package WPZOOM
 * @subpackage Onboarding
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPZOOM_Onboarding_Tour' ) && class_exists( 'WPZOOM_Theme_Tour' ) ) {

	/**
	 * WPZOOM Onboarding Tour.
	 *
	 * @since 2.0.0
	 *
	 * @package WPZOOM
	 */
	class WPZOOM_Onboarding_Tour extends WPZOOM_Theme_Tour {
		/**
		 * Enqueue styles and scripts needed for the pointers.
		 */
		public function enqueue() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Assume pointer shouldn't be shown.
			$enqueue_pointer_script_style = false;

			// Get array list of dismissed pointers for current user and convert it to array.
			$dismissed_pointers = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

			// Check if our pointer is not among dismissed ones.
			if ( ! in_array( $this->pointer_close_id, $dismissed_pointers ) ) {
				$enqueue_pointer_script_style = true;
			}

			// Enqueue pointer CSS and JS files, if needed.
			if ( $enqueue_pointer_script_style ) {
				wp_enqueue_style( 'wp-pointer' );
				wp_enqueue_script(
					'wpzoom-onboarding-tour-script',
					WPZOOM::get_root_uri() . '/components/onboarding/assets/js/onboarding-tour.js',
					array( 'wp-pointer' ),
					WPZOOM::$wpzoomVersion, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					true
				);

				wp_localize_script(
					'wpzoom-onboarding-tour-script',
					'wpzoomOnboardingTourVars',
					array(
						'pointer_close_id' => $this->pointer_close_id,
						'whitelist_pages'  => $this->whitelist_pages(),
						'pointers'         => $this->parse_pointers_for_js(),
					)
				);
			}
		}

		/**
		 * The allowed pages to display pointers on.
		 *
		 * @return array
		 */
		public function whitelist_pages() {
			$theme_tour_pointers = $this->get_theme_tour_pointers();
			$whitelist_pages     = array_keys( $theme_tour_pointers );
			return $whitelist_pages;
		}

		/**
		 * Get current page
		 *
		 * @return string
		 */
		public function get_current_page() {
			$page   = '';
			$screen = get_current_screen();

			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			// Check which page the user is on.
			if ( isset( $_GET['page'] ) ) {
				$page = sanitize_text_field( $_GET['page'] );
			}
			if ( empty( $page ) ) {
				$page = $screen->id;
			}

			if ( isset( $_GET['welcome_tour'] ) ) {
				$param = sanitize_text_field( $_GET['welcome_tour'] );
			}
			if ( isset( $param ) && ! empty( $param ) ) {
				$page = $page . '_' . $param;
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			return $page;
		}

		/**
		 * Get theme tour pointers array
		 *
		 * @since 2.0.0
		 * @return array
		 */
		public function get_theme_tour_pointers() {
			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$theme_tour_pointers = array(
				// array name is the unique ID of the screen @see: http://codex.wordpress.org/Function_Reference/get_current_screen.
				'wpzoom_license'  => array(
					array(
						'initial_open' => true,
						/* translators: %s Theme name */
						'content'      => '<h3>' . sprintf( esc_html__( 'Welcome to %s!', 'wpzoom' ), WPZOOM::$themeName ) . '</h3>'
						/* translators: %s Theme name */
							. '<p>' . sprintf( esc_html__( 'Congratulations! You have just installed %s. Please take the onboarding tour.', 'wpzoom' ), WPZOOM::$themeName ) . '</p>', // Content for this pointer.
						'selector'     => '#step-choose-design .wpz-onboard_content-main-step-title', // ID of element where the pointer will point.
						'tab_id'       => 'demo-importer',
						'position'     => array(
							'edge'  => 'left', // Arrow position; change depending on where the element is located.
							'align' => 'center', // Alignment of Pointer.
						),
						'scroll_to'    => '#step-choose-design', // where to take the user.
					),
					array(
						'content'   => '<h3>' . esc_html__( 'Choose design', 'wpzoom' ) . '</h3>'
							. '<p>' . esc_html__( 'Choose the design which suits your needs. You can view a demo of the design or view pages directly here.', 'wpzoom' ) . '</p>',
						'selector'  => '#step-choose-design fieldset ul > li:last-child',
						'tab_id'    => 'demo-importer',
						'position'  => array(
							'edge'          => 'left',
							'align'         => 'center',
							//'defer_loading' => true, // wait for previous pointer process.
						),
						'button1'   => esc_html__( 'View selected design', 'wpzoom' ),
						'function1' => 'document.querySelector("#step-choose-design .selected-template .view-pages-open").click();',
						'scroll_to' => '#plugins-list',
					),
					array(
						'content'   => '<h3>' . esc_html__( 'Install & Activate plugins', 'wpzoom' ) . '</h3>'
							. '<p>' . esc_html__( 'Please install & activate all required and recommended plugins to enable all the features from imported demo content.', 'wpzoom' ) . '</p>',
						'selector'  => '#step-install-plugins fieldset input[name="button_checkall"]:not(:disabled)',
						'tab_id'    => 'demo-importer',
						'position'  => array(
							'edge'  => 'left',
							'align' => 'center',
						),
						'button1'   => esc_html__( 'Unselect recommended', 'wpzoom' ),
						'button2'   => esc_html__( 'Install & Activate', 'wpzoom' ),
						'function1' => '$(document).find("#plugins-list .plugin-level_recommended").each(function(){
							const checkbox = $(this).find("input[type=\"checkbox\"]");
							checkbox.prop("checked", !checkbox.is(":checked"));
							const checkboxes = $(document).find("#plugins-list .plugin-level_recommended input[type=\"checkbox\"]");
							if ( checkboxes.is(":checked") ) {
								$(document).find("a#pointer-secondary-1").html("' . esc_html__( 'Unselect recommended', 'wpzoom' ) . '");
							} else {
								$(document).find("a#pointer-secondary-1").html("' . esc_html__( 'Select recommended', 'wpzoom' ) . '");
							}
						});',
						'function2' => 'document.querySelector("#step-install-plugins fieldset input[name=\"button_submit\"]").click();',
					),
					array(
						'content'   => '<h3>' . esc_html__( 'Meet the requirements', 'wpzoom' ) . '</h3>'
							. '<p>' . esc_html__( 'Please make sure you meet the required requirements and follow our recommendations. You can also skip our recommendations and start importing the demo content.', 'wpzoom' ) . '</p>',
						'selector'  => '#step-import-demo #wpz-onboard-skip-notice',
						'tab_id'    => 'demo-importer',
						'position'  => array(
							'edge'  => 'bottom',
							'align' => 'left',
							'defer_loading' => true, // wait for previous pointer process.
						),
						'button1'   => esc_html__( 'Skip recommended notices', 'wpzoom' ),
						'button2'   => false,
						'function1' => 'document.querySelector("#step-import-demo #wpz-onboard-skip-notice").click();',
					),
					array(
						'content'   => '<h3>' . esc_html__( 'Import Demo Content', 'wpzoom' ) . '</h3>'
							. '<p>' . esc_html__( 'Now you are ready to import the demo content.', 'wpzoom' ) . '</p>',
						'selector'  => '#step-import-demo .wpz-onboard_import-button:not(:disabled)',
						'tab_id'    => 'demo-importer',
						'position'  => array(
							'edge'          => 'bottom',
							'align'         => 'left',
							'defer_loading' => true, // wait for previous pointer process.
						),
						'button2'   => esc_html__( 'Import demo content', 'wpzoom' ),
						'function2' => 'document.querySelector("#step-import-demo .wpz-onboard_import-button").click();',
					),
				),
				'content-overlay' => array(
					'content'   => '<h3>' . esc_html__( 'Quick actions', 'wpzoom' ) . '</h3>'
							. '<p>' . esc_html__( 'You can Import the Demo Content or Live Preview the chosen design in a new tab. Also, you can easily view the pages by clicking on thumbnails below and the preview will appear in the right section.', 'wpzoom' ) . '</p>',
					'selector'  => '.wpz-onboard_content-overlay-design-pages .wpz-onboard_content-overlay-design-pages-preview-link',
					'tab_id'    => 'demo-importer',
					'position'  => array(
						'edge'  => 'bottom',
						'align' => 'left',
					),
					'button1'   => esc_html__( 'Live preview', 'wpzoom' ),
					'button2'   => esc_html__( 'Import demo content', 'wpzoom' ),
					'function1' => 'document.querySelector(".wpz-onboard_content-overlay-design-pages .wpz-onboard_content-overlay-design-pages-preview-link").click();',
					'function2' => 'document.querySelector(".wpz-onboard_content-overlay-design-pages .wpz-onboard_content-overlay-design-import-demo-content").click();',
				),
			);
			// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			return apply_filters( 'wpzoom_theme_tour_pointers', $theme_tour_pointers );
		}

		/**
		 * Parse pointer data
		 *
		 * @param string $page The page id.
		 * @param string $selector The slector id.
		 * @param string $pointer The pointer data.
		 * @return array
		 */
		public function parse_pointer( $page, $selector, $pointer ) {
			$data      = array();
			$options   = array();
			$button1   = esc_html__( 'Dismiss', 'wpzoom' );
			$button2   = esc_html__( 'Next', 'wpzoom' );
			$function1 = '';
			$function2 = '';

			if ( ! empty( $pointer ) && is_array( $pointer ) ) {
				$align = ( is_rtl() ) ? 'right' : 'left';

				if ( isset( $pointer['content'] ) && ! empty( $pointer['content'] ) ) {
					$options = array(
						'initial_open' => ( isset( $pointer['initial_open'] ) ) ? $pointer['initial_open'] : false,
						'scroll_to'    => ( isset( $pointer['scroll_to'] ) ) ? $pointer['scroll_to'] : '',
						'tab_id'       => ( isset( $pointer['tab_id'] ) ) ? $pointer['tab_id'] : '', // If no provided tab_id, then the pointer will display on all tabs panels.
						'content'      => $pointer['content'],
						'position'     => array(
							'edge'          => ( isset( $pointer['position']['edge'] ) ) ? $pointer['position']['edge'] : 'left',
							'align'         => ( isset( $pointer['position']['align'] ) ) ? $pointer['position']['align'] : $align,
							'defer_loading' => ( isset( $pointer['position']['defer_loading'] ) ) ? $pointer['position']['defer_loading'] : false,
						),
						'pointerWidth' => 400,
					);
				}

				if ( isset( $pointer['scroll_to'] ) ) {
					$scroll_to = $pointer['scroll_to'];
				}

				if ( isset( $pointer['button1'] ) ) {
					$button1 = $pointer['button1'];
				}

				if ( isset( $pointer['button2'] ) ) {
					$button2 = $pointer['button2'];
				}

				// Button1 callback function.
				if ( isset( $pointer['function1'] ) ) {
					$function1 = $pointer['function1'];
				}

				// Button2 callback function.
				if ( isset( $pointer['function2'] ) ) {
					$function2 = $pointer['function2'];
				}
			}

			$data[ $page ] = compact( 'selector', 'options', 'button1', 'button2', 'function1', 'function2' );

			return $data;
		}

		/**
		 * Parse pointers for javascript
		 *
		 * @return array
		 */
		public function parse_pointers_for_js() {
			$theme_tour_pointers = $this->get_theme_tour_pointers();
			$whitelist_pages     = $this->whitelist_pages();
			$screen              = get_current_screen();
			$pointers            = array();

			// Location the pointer points.
			if ( is_array( $theme_tour_pointers ) ) {
				foreach ( $theme_tour_pointers as $page => $pointer ) {
					if ( in_array( $page, $whitelist_pages ) ) {
						if ( isset( $pointer[0] ) && is_array( $pointer[0] ) ) {
							foreach ( $pointer as $options ) {
								if ( ! isset( $options['selector'] ) ) {
									continue;
								}
								$selector   = $options['selector'];
								$pointers[] = $this->parse_pointer( $page, $selector, $options );
							}
						} elseif ( ! empty( $pointer['selector'] ) ) {
							$selector   = $pointer['selector'];
							$pointers[] = $this->parse_pointer( $page, $selector, $pointer );
						} else {
							$selector   = '#' . $screen->id;
							$pointers[] = $this->parse_pointer( $page, $selector, $pointer );
						}
					}
				}
			}

			return $pointers;
		}
	}

	new WPZOOM_Onboarding_Tour();
}
