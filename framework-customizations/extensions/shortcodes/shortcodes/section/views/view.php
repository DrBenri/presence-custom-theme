<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$bg_color = $bg_image = $bg_video_data_attr = $extra_classes = '';
$container_class = ( isset( $atts['is_fullwidth'] ) && $atts['is_fullwidth'] ) ? 'fw-container-fluid' : 'fw-container';

if ( isset( $atts['background_options']['background'] ) && $atts['background_options']['background'] == 'default' ) {
	$extra_classes .= ' fw-main-row';
} elseif ( isset( $atts['is_fullwidth'] ) && isset( $atts['auto_generated'] ) && $atts['auto_generated'] == '' ) {
	$extra_classes .= ' fw-main-row-custom';
} else {
	$extra_classes .= ' fw-main-row';
}

if ( isset( $atts['first_in_builder'] ) && $atts['first_in_builder'] ) {
	$extra_classes .= ' fw-main-row-top';
}

if ( $atts['background_options']['background'] == 'image' && ! empty( $atts['background_options']['image']['background_image']['data'] ) ) {
	$bg_image = 'background-image:url(' . $atts['background_options']['image']['background_image']['data']['icon'] . ');';
	if ( isset( $atts['background_options']['image']['background_color']['id'] ) && $atts['background_options']['image']['background_color']['id'] == 'fw-custom' && ! empty( $atts['background_options']['image']['background_color']['color'] ) ) {
		$bg_color = 'background-color:' . $atts['background_options']['image']['background_color']['color'] . ';';
	} elseif ( isset( $atts['background_options']['image']['background_color']['id'] ) ) {
		$extra_classes .= ' fw_theme_bg_' . $atts['background_options']['image']['background_color']['id'];
	}
	$extra_classes .= ' fw-section-image';
} elseif ( $atts['background_options']['background'] == 'video' ) {
	if($atts['background_options']['video']['video_type']['selected'] == 'uploaded' ){
		$video_url = $atts['background_options']['video']['video_type']['uploaded']['video']['url'];
	}
	else{
		$video_url = $atts['background_options']['video']['video_type']['youtube']['video'];
	}
	$filetype  = wp_check_filetype( $video_url );
	$filetypes = array( 'mp4' => 'mp4', 'ogv' => 'ogg', 'webm' => 'webm', 'jpg' => 'poster' );
	$filetype  = array_key_exists( (string) $filetype['ext'], $filetypes ) ? $filetypes[ $filetype['ext'] ] : 'video';
	$bg_video_data_attr = 'data-wallpaper-options="' . fw_htmlspecialchars( json_encode( array( 'source' => array( $filetype => $video_url ) ) ) ) . '"';
	$extra_classes .= ' background-video';
} elseif ( $atts['background_options']['background'] == 'color' ) {
	if ( isset( $atts['background_options']['color']['background_color']['id'] ) && $atts['background_options']['color']['background_color']['id'] == 'fw-custom' ) {
		if ( ! empty( $atts['background_options']['color']['background_color']['color'] ) ) {
			$bg_color = 'background-color:' . $atts['background_options']['color']['background_color']['color'] . ';';
		}
	} elseif ( isset( $atts['background_options']['color']['background_color']['id'] ) ) {
		$extra_classes .= ' fw_theme_bg_' . $atts['background_options']['color']['background_color']['id'];
	}
}

?>
<section class="<?php echo esc_attr( $extra_classes . ' fw-section-no-padding' ); ?>" style="<?php echo ($bg_color); ?> <?php echo ($bg_image); ?>"<?php echo ($bg_video_data_attr); ?> >
	<div class="<?php echo esc_attr( $container_class ); ?>">
		<?php echo do_shortcode( $content ); ?>
	</div>
</section>