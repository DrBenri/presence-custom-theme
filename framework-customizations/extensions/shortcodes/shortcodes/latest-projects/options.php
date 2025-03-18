<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$admin_url           = admin_url();


if ( ! post_type_exists( 'jetpack-portfolio' ) )  {

    $options = array(

        'category'     => array(
            'type'    => 'html-fixed',
            'width' => 'full',
            'label' => '',
            'html'  => '<strong>Jetpack Portfolio</strong> is not enabled. <br/><br/>Please install Jetpack, and activate <strong>Custom Content Types</strong> feature in settings page. <a href="http://www.wpzoom.com/wp-content/uploads/2016/09/jetpack-portfolio.png" target="_blank">View instructions</a>',
        )
    );

} else {

$options = array(

    'category'     => array(
        'label'   => esc_html__( 'Display From', 'fw' ),
        'desc'    => esc_html__( 'Select a portfolio category', 'fw' ),
        'type'    => 'select',
        'value'   => '',
        'choices' => fw_get_category_term_list( 'jetpack-portfolio' ),
    ),

    'posts_number' => array(
        'label' => esc_html__( 'Number of Posts', 'fw' ),
        'desc'  => esc_html__( 'Enter the number of posts to display. Ex: 3, 6, 9', 'fw' ),
        'type'  => 'short-text',
        'value' => '6'
    ),


    'postcategory' => array(
        'type'  => 'switch',
        'label'   => __( 'Category', 'fw' ),
        'value' => 'category_show',
        'right-choice' => array(
            'value' => 'category_show',
            'label' => __('Show', 'fw'),
        ),
        'left-choice' => array(
            'value' => 'category_hide',
            'label' => __('Hide', 'fw'),
        ),
    ),

    'excerpt' => array(
        'type'  => 'switch',
        'label'   => __( 'Post Excerpts', 'fw' ),
        'value' => 'excerpt_show',
        'right-choice' => array(
            'value' => 'excerpt_show',
            'label' => __('Show', 'fw'),
        ),
        'left-choice' => array(
            'value' => 'excerpt_hide',
            'label' => __('Hide', 'fw'),
        ),
    ),

    'button_all' => array(
        'type'  => 'switch',
        'label'   => __( 'View All Button', 'fw' ),
        'value' => 'button_all_show',
        'right-choice' => array(
            'value' => 'button_all_show',
            'label' => __('Show', 'fw'),
        ),
        'left-choice' => array(
            'value' => 'button_all_hide',
            'label' => __('Hide', 'fw'),
        ),
    ),

    'label'  => array(
        'label' => '',
        'desc'  => __( 'Button Label', 'fw' ),
        'type'  => 'text',
        'value' => 'View All'
    ),
    'link'   => array(
        'label' => '',
        'desc'  => __( 'Link', 'fw' ),
        'type'  => 'text',
        'value' => '#'
    ),
    'target' => array(
        'label' => '',
        'type'  => 'switch',
        'value' => '_self',
        'desc'    => __( 'Open Link in New Window', 'fw' ),
        'right-choice' => array(
            'value' => '_blank',
            'label' => __('Yes', 'fw'),
        ),
        'left-choice' => array(
            'value' => '_self',
            'label' => __('No', 'fw'),
        ),
    ),

);

}