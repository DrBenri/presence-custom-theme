<?php


/* Registering metaboxes
============================================*/

function wpzoom_options_box() {

    if (option::get('featured_type') == 'Featured Posts') {

        $FeaturedSource = 'post';

    } elseif (option::get('featured_type') == 'Featured Pages') {

        $FeaturedSource = 'page';

    } else {

        $FeaturedSource = 'listing';
    }

    $context = array( 'normal', 'side', 'side', 'side' );

    // when gutenberg page is enabled, change context to 'normal' for all custom metaboxes
    if ( function_exists('is_gutenberg_page') && is_gutenberg_page() ) {
        $context = array( 'normal', 'side', 'normal', 'normal' );
    }

    add_meta_box( 'wpzoom_post_layout', 'Post Layout', 'wpzoom_post_layout_options', 'post', $context[0], 'high' );
    add_meta_box( 'wpzoom_post_embed', 'Post Options', 'wpzoom_post_embed_info', $FeaturedSource, $context[1], 'high' );
    add_meta_box( 'wpzoom_testimonial_options', 'Testimonial Options', 'wpzoom_testimonial_options', 'testimonial', $context[2], 'high' );
    add_meta_box( 'wpzoom_top_button', 'Slideshow Options', 'wpzoom_top_button_options', 'slider', $context[3], 'high' );
}

add_action( 'add_meta_boxes', 'wpzoom_options_box' );


function wpz_newpost_head() {
    ?><style type="text/css">
        fieldset.fieldset-show { padding: 0.3em 0.8em 1em;margin-top:20px; border: 1px solid rgba(0, 0, 0, 0.2); -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px; }
        fieldset.fieldset-show p { margin: 0 0 1em; }
        fieldset.fieldset-show p:last-child { margin-bottom: 0; }

        .wpz_list { font-size: 12px; }

    </style><?php
}
add_action('admin_head-post-new.php', 'wpz_newpost_head', 100);
add_action('admin_head-post.php', 'wpz_newpost_head', 100);


/* Slideshow Options
============================================*/
function wpzoom_top_button_options() {
    global $post;

    ?>

    <div>
        <strong><label for="wpzoom_slide_url"><?php _e( 'Slide URL', 'wpzoom' ); ?></label></strong> (<?php _e('optional', 'wpzoom'); ?>)<br/>
        <p><input type="text" name="wpzoom_slide_url" id="wpzoom_slide_url" class="widefat" value="<?php echo esc_url( get_post_meta( $post->ID, 'wpzoom_slide_url', true ) ); ?>"/></p>
        <p class="description"><?php _e('When a URL is added, the title of the current slide will become clickable', 'wpzoom'); ?></p>

    </div>


    <fieldset class="fieldset-show">
        <legend><strong><?php _e( 'Slide Button', 'wpzoom' ); ?></strong> <?php _e( '(optional)', 'wpzoom' ); ?></legend>

        <p>
            <label>
                <strong><?php _e( 'Title', 'wpzoom' ); ?></strong>
                <input type="text" name="wpzoom_slide_button_title" id="wpzoom_slide_button_title" class="widefat" value="<?php echo esc_attr( get_post_meta( $post->ID, 'wpzoom_slide_button_title', true ) ); ?>" />
            </label>
        </p>

        <p>
            <label>
                <strong><?php _e( 'URL', 'wpzoom' ); ?></strong>
                <input type="text" name="wpzoom_slide_button_url" id="wpzoom_slide_button_url" class="widefat" value="<?php echo esc_url( get_post_meta( $post->ID, 'wpzoom_slide_button_url', true ) ); ?>" />
            </label>
        </p>
   </fieldset>


<?php }




/* Custom Post Layouts
==================================== */

function wpzoom_post_layout_options() {
    global $post;
    $postLayouts = array('side-right' => 'Sidebar on the right', 'full' => 'Full Width');
    ?>

    <style>
    .RadioClass { display: none !important; }
    .RadioLabelClass { margin-right: 10px; }
    img.layout-select { border: solid 3px #c0cdd6; border-radius: 5px; }
    .RadioSelected img.layout-select { border: solid 3px #3173b2; }
    #wpzoom_post_embed_code { color: #444444; font-size: 11px; margin: 3px 0 10px; padding: 5px; height:135px; font-family: Consolas,Monaco,Courier,monospace; }

    </style>

    <script type="text/javascript">
    jQuery(document).ready( function($) {
        $(".RadioClass").change(function(){
            if($(this).is(":checked")){
                $(".RadioSelected:not(:checked)").removeClass("RadioSelected");
                $(this).next("label").addClass("RadioSelected");
            }
        });
    });
    </script>

    <fieldset>
        <div>
            <p>
            <?php
            foreach ($postLayouts as $key => $value)
            {
                ?>
                <input id="<?php echo $key; ?>" type="radio" class="RadioClass" name="wpzoom_post_template" value="<?php echo $key; ?>"<?php if (get_post_meta($post->ID, 'wpzoom_post_template', true) == $key) { echo' checked="checked"'; } ?> />
                <label for="<?php echo $key; ?>" class="RadioLabelClass<?php if (get_post_meta($post->ID, 'wpzoom_post_template', true) == $key) { echo' RadioSelected"'; } ?>">
                <img src="<?php echo wpzoom::$wpzoomPath; ?>/assets/images/layout-<?php echo $key; ?>.png" alt="<?php echo $value; ?>" title="<?php echo $value; ?>" class="layout-select" /></label>
            <?php
            }
            ?>
            </p>
        </div>
    </fieldset>
    <?php
}

/* Options for regular posts */

function wpzoom_post_embed_info() {
    global $post;

    ?>
    <fieldset>
        <p class="wpz_border">
            <?php $isChecked = ( get_post_meta($post->ID, 'wpzoom_is_featured', true) == 1 ? 'checked="checked"' : '' ); // we store checked checkboxes as 1 ?>
            <input type="checkbox" name="wpzoom_is_featured" id="wpzoom_is_featured" value="1" <?php echo esc_attr($isChecked); ?> /> <label for="wpzoom_is_featured"><?php _e('Feature in Homepage Slider', 'wpzoom'); ?></label>
        </p>

    </fieldset>
    <?php
}


// Testimonials Options
function wpzoom_testimonial_options() {
    global $post;
    ?>
    <fieldset>
        <input type="hidden" name="saveTestimonial" id="saveTestimonial" value="1" />
        <div>
            <p>
                <label for="wpzoom_testimonial_author">Testimonial Author:</label><br />
                <input type="text" style="width:90%;" name="wpzoom_testimonial_author" id="wpzoom_testimonial_author" value="<?php echo get_post_meta($post->ID, 'wpzoom_testimonial_author', true); ?>"><br />
            </p>
            <p>
                <label for="wpzoom_testimonial_author_position">Author Position:</label><br />
                <input type="text" style="width:90%;" name="wpzoom_testimonial_author_position" id="wpzoom_testimonial_author_position" value="<?php echo get_post_meta($post->ID, 'wpzoom_testimonial_author_position', true); ?>"><br />
                <span class="description">Example: CEO &amp; Founder</span>
            </p>
            <p>
                <label for="wpzoom_testimonial_author_company">Author Company:</label><br />
                <input type="text" style="width:90%;" name="wpzoom_testimonial_author_company" id="wpzoom_testimonial_author_company" value="<?php echo get_post_meta($post->ID, 'wpzoom_testimonial_author_company', true); ?>"><br />
                <span class="description">Example: WPZOOM</span>
            </p>
            <p>
                <label for="wpzoom_testimonial_author_company_url">Author Company Link:</label><br />
                <input type="text" style="width:90%;" name="wpzoom_testimonial_author_company_url" id="wpzoom_testimonial_author_company_url" value="<?php echo get_post_meta($post->ID, 'wpzoom_testimonial_author_company_url', true); ?>"><br />
                <span class="description">Example: http://www.wpzoom.com</span>
            </p>

        </div>
    </fieldset>
    <?php
    }


add_action( 'save_post', 'custom_add_save' );

function custom_add_save( $post_id ) {

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
        return $post_id;
    }

    // called after a post or page is saved
    if ( $parent_id = wp_is_post_revision( $post_id ) ) {
        $post_id = $parent_id;
    }


    if ( isset( $_POST['post_type'] ) && ( $post_type_object = get_post_type_object( $_POST['post_type'] ) ) && $post_type_object->public ) {
        if ( current_user_can( 'edit_post', $post_id ) ) {

            if (isset($_POST['wpzoom_post_template']))
                update_custom_meta($post_id, $_POST['wpzoom_post_template'], 'wpzoom_post_template');

            update_custom_meta( $post_id, ( isset( $_POST['wpzoom_is_featured'] ) ? 1 : 0 ), 'wpzoom_is_featured' );


            // Testimonials

            if ( isset( $_POST['wpzoom_testimonial_author'] ) )
                update_custom_meta( $post_id, $_POST['wpzoom_testimonial_author'], 'wpzoom_testimonial_author' );

            if ( isset( $_POST['wpzoom_testimonial_author_position'] ) )
                update_custom_meta( $post_id, $_POST['wpzoom_testimonial_author_position'], 'wpzoom_testimonial_author_position' );

            if ( isset( $_POST['wpzoom_testimonial_author_company'] ) )
                update_custom_meta( $post_id, $_POST['wpzoom_testimonial_author_company'], 'wpzoom_testimonial_author_company' );

            if ( isset( $_POST['wpzoom_testimonial_author_company_url'] ) )
                update_custom_meta( $post_id, esc_url_raw( $_POST['wpzoom_testimonial_author_company_url'] ), 'wpzoom_testimonial_author_company_url' );

            // Slideshow

            if ( isset( $_POST['wpzoom_slide_url'] ) )
                update_custom_meta( $post_id, esc_url_raw( $_POST['wpzoom_slide_url'] ), 'wpzoom_slide_url' );

            if ( isset( $_POST['wpzoom_slide_button_title'] ) )
                update_custom_meta( $post_id, $_POST['wpzoom_slide_button_title'] , 'wpzoom_slide_button_title' );

            if ( isset( $_POST['wpzoom_slide_button_url'] ) )
                update_custom_meta( $post_id, esc_url_raw( $_POST['wpzoom_slide_button_url'] ), 'wpzoom_slide_button_url' );


        }
    }


}


function update_custom_meta( $postID, $value, $field ) {
    // To create new meta
    if ( ! get_post_meta( $postID, $field ) ) {
        add_post_meta( $postID, $field, $value );
    } else {
        // or to update existing meta
        update_post_meta( $postID, $field, $value );
    }
}