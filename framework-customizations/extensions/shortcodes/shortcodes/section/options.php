<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$colors             = array(
	'color_1' => '#161719',
	'color_2' => '#0700ce',
	'color_3' => '#F1F1F1'
);
$admin_url           = admin_url();

$options = array(
	'is_fullwidth'       => array(
		'label' => esc_html__( 'Full Width Content', 'fw' ),
		'type'  => 'switch',
		'desc'  => 'Make the content inside this section full width?',
	),

	'background_options' => array(
		'type'         => 'multi-picker',
		'label'        => false,
		'desc'         => false,
		'picker'       => array(
			'background' => array(
				'label'   => esc_html__( 'Background', 'fw' ),
				'desc'    => esc_html__( 'Select the background for your section', 'fw' ),
				'attr'    => array( 'class' => 'fw-checkbox-float-left' ),
				'type'    => 'radio',
				'choices' => array(
					'none'    => esc_html__( 'None', 'fw' ),
					'image'   => esc_html__( 'Image', 'fw' ),
					'video'   => esc_html__( 'Video', 'fw' ),
					'color'   => esc_html__( 'Color', 'fw' ),
				),
				'value'   => 'none'
			),
		),
		'choices'      => array(
			'none'  => array(),
			'image' => array(
				'background_image' => array(
					'label'   => esc_html__( '', 'fw' ),
					'type'    => 'background-image',
					'choices' => array(//	in future may will set predefined images
					)
				),
			),
			'video' => array(
				'video_type'      => array(
					'type'         => 'multi-picker',
					'label'        => false,
					'desc'         => false,
					'picker'       => array(
						'selected' => array(
							'label'   => esc_html__( 'Video Type', 'fw' ),
							'desc'    => esc_html__( 'Select the video type', 'fw' ),
							'attr'    => array( 'class' => 'fw-checkbox-float-left' ),
							'type'    => 'radio',
							'choices' => array(
								'youtube'  => esc_html__( 'Youtube', 'fw' ),
								'uploaded' => esc_html__( 'Video', 'fw' ),
							),
							'value'   => 'youtube'
						),
					),
					'choices'      => array(
						'youtube'  => array(
							'video' => array(
								'label' => esc_html__( '', 'fw' ),
								'desc'  => esc_html__( 'Insert a YouTube video URL', 'fw' ),
								'type'  => 'text',
							),
						),
						'uploaded' => array(
							'video' => array(
								'label'       => esc_html__( '', 'fw' ),
								'desc'        => esc_html__( 'Upload a video', 'fw' ),
								'images_only' => false,
								'type'        => 'upload',
							),
						),
					),
					'show_borders' => false,
				),
			),
			'color' => array(
				'background_color' => array(
					'label'   => esc_html__( '', 'fw' ),
					'desc'    => esc_html__( 'Select the background color', 'fw' ),
					'value'   => '',
					'choices' => $colors,
					'type'    => 'color-palette'
				),
			),
		),
		'show_borders' => false,
	)
);