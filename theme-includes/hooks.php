<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}
/**
 * Filters and Actions
 */


//custom theme options
if ( ! function_exists( '_action_theme_includes_additional_option_types' ) ) :
	/**
	 * Include the color-palette and tf-typography options
	 */
	function _action_theme_includes_additional_option_types() {
        require_once dirname( __FILE__ ) . '/includes/option-types/color-palette/class-fw-color-palette-new.php';
	}

	add_action( 'fw_option_types_init', '_action_theme_includes_additional_option_types' );
endif;


/**
 *
 */

add_filter(
	'fw_ext_builder:predefined_templates:page-builder:full',
	'_filter_theme_page_builder_predefined_templates_full'
);
function _filter_theme_page_builder_predefined_templates_full($templates) {
	$variables = fw_get_variables_from_file(
		dirname(__FILE__) .'/includes/builder-templates/page-builder/full.php',
		array('templates' => array())
	);

	return array_merge($templates, $variables['templates']);
}
