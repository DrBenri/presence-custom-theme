<?php
/**
 * @package WPZOOM
 */

/**
 * Class WPZOOM_Customizer_Control_Copyright_WP_Editor
 *
 * Extended Customize Control WP editor class.
 *
 * @since 1.9.0
 */
class WPZOOM_Customizer_Control_Copyright_WP_Editor extends WPZOOM_Customize_Control {
	/**
	 * The control type.
	 *
	 * @since 1.8.5
	 *
	 * @var string $type
	 */
	public $type = 'copyright-wp-editor';

	/**
	 * WPZOOM_Customizer_Control_Color constructor.
	 *
	 * @param   WP_Customize_Manager  $manager
	 * @param   string                $id
	 * @param   array                 $args
	 *
	 * @since 1.8.5
	 *
	 */
	public function __construct( WP_Customize_Manager $manager, $id, array $args ) {
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * Send data to _s
	 *
	 * @return array
	 * @see WP_Customize_Control::to_json()
	 */
	public function json() {
		$json                = parent::json();
		$this->json['value'] = $this->value();

		return $json;
	}

	/**
	 * Get settings for WP Editor
	 *
	 * @return array
	 */
	private function get_editor_settings() {
		return array(
			//	'textarea_name' => $this->id,
			'media_buttons' => false,    // Show media upload buttons
			'textarea_rows' => 4,
			'teeny'         => true,     // Output the minimal editor config
			'quicktags'     => true,     // Show Quicktags (Text Mode),
			'tinymce'       => array(
				'toolbar1' => 'bold,italic,underline,|,link,unlink', // Customize the toolbar
				'toolbar2' => '',        // Leave empty to remove the second toolbar
				'wpautop'  => false,     // Disable auto-paragraphs, don't had effect
			),
		);
	}

	/**
	 * Prepare Custom WP Editor function
	 * @return mixed
	 *
	 * todo: improve to work without prepare_and_return_wp_editor_content(), directly in render or true JS
	 */
	public function prepare_and_return_wp_editor_content() {
		if ( ! function_exists( 'wp_editor' ) ) {
			require_once( ABSPATH . WPINC . '/class-wp-editor.php' );
		}

		$get_default_value = get_option( 'blogcopyright', 'Copyright {copyright} {current-year} &mdash; {site-title}' );
		$settings          = $this->get_editor_settings();

		return wp_editor( $get_default_value, 'blogcopyright', $settings );
	}


	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
	 *
	 * @see    WP_Customize_Control::print_template()
	 *
	 * @access protected
	 */
	protected function content_template() {
		?>

        <label>
            <span class="customize-control-title">{{ data.label }}</span>
        </label>

		<?php $this->prepare_and_return_wp_editor_content(); ?>
        <p class="description customize-control-description">
            {{ data.description }}
        </p>
		<?php
	}

}
