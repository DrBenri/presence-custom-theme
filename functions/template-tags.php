<?php
/**
 * Custom template tags.
 */


/* Comments Custom Template
==================================== */


if ( ! function_exists( 'presence_comment' ) ) :

	function presence_comment( $comment, $args, $depth ) {
	    $GLOBALS['comment'] = $comment;
	    switch ( $comment->comment_type ) :
	        case 'comment' :
	            ?>
	            <li <?php comment_class( 'clearfix' ); ?> id="li-comment-<?php comment_ID(); ?>">
	            <div id="comment-<?php comment_ID(); ?>">

	                <?php echo get_avatar( $comment, 72 ); ?>

	                <div class="comment-main">
	                    <div class="comment-author vcard">
	                        <?php printf( '<cite class="fn">%s</cite>', get_comment_author_link() ); ?>

	                        <div class="comment-meta commentmetadata"><a
	                                href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
	                                <?php printf( __( '%s at %s', 'wpzoom' ), get_comment_date(), get_comment_time() ); ?></a>
	                                <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'reply_text' => __( 'Reply', 'wpzoom' ), 'before' => '&nbsp;&middot;&nbsp;&nbsp;' ) ) ); ?>
	                                <?php edit_comment_link( __( 'Edit', 'wpzoom' ), '&nbsp;&middot;&nbsp;&nbsp;' ); ?>

	                        </div>
	                        <!-- .comment-meta .commentmetadata -->
	                    </div>
	                    <!-- .comment-author .vcard -->

	                    <?php if ( $comment->comment_approved == '0' ) : ?>
	                        <em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'wpzoom' ); ?></em>
	                        <br/>
	                    <?php endif; ?>

	                    <div class="comment-body"><?php comment_text(); ?></div>
	                </div>

	            </div><!-- #comment-##  -->

	            <?php
	            break;
	        case 'pingback'  :
	        case 'trackback' :
	            ?>
	            <li class="post pingback">
	            <p><?php _e( 'Pingback:', 'wpzoom' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'wpzoom' ), ' ' ); ?></p>
	            <?php
	            break;
	    endswitch;
	}

endif;



/* Count number of widgets in a sidebar
=========================================== */
function presence_widgets_count_class( $index = 1 ) {
    global $wp_registered_sidebars, $wp_registered_widgets;

    if ( is_int($index) ) {
        $index = "sidebar-$index";
    } else {
        $index = sanitize_title($index);
        foreach ( (array) $wp_registered_sidebars as $key => $value ) {
            if ( sanitize_title($value['name']) == $index ) {
                $index = $key;
                break;
            }
        }
    }

    $sidebars_widgets = wp_get_sidebars_widgets();

    if ( empty( $wp_registered_sidebars[ $index ] ) || empty( $sidebars_widgets[ $index ] ) || ! is_array( $sidebars_widgets[ $index ] ) ) {
        return '';
    }

    $count = 0;

    foreach ( (array) $sidebars_widgets[ $index ] as $id ) {
        if ( ! isset( $wp_registered_widgets[ $id ] ) ) continue;

        $count++;
    }

    return ' widgets-' . $count;
}