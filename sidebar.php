<?php
/**
 * The sidebar.
 */
?>

<div id="sidebar" class="site-sidebar">

    <?php if (option::get('ad_side') == 'on' && option::get('ad_side_pos') == 'Before widgets') { ?>
        <div class="adv_side">

            <?php if ( option::get('ad_side_imgpath') <> "") {
                echo stripslashes(option::get('ad_side_imgpath'));
            } else { ?>
                <a href="<?php echo option::get('banner_sidebar_url'); ?>"><img src="<?php echo option::get('banner_sidebar'); ?>" alt="<?php echo option::get('banner_sidebar_alt'); ?>" /></a>
            <?php } ?>

        </div><!-- /.banner -->
    <?php } ?>

    <?php dynamic_sidebar('Sidebar'); ?>

    <?php if (option::get('ad_side') == 'on' && option::get('ad_side_pos') == 'After widgets') { ?>
        <div class="adv_side">

            <?php if ( option::get('ad_side_imgpath') <> "") {
                echo stripslashes(option::get('ad_side_imgpath'));
            } else { ?>
                <a href="<?php echo option::get('banner_sidebar_url'); ?>"><img src="<?php echo option::get('banner_sidebar'); ?>" alt="<?php echo option::get('banner_sidebar_alt'); ?>" /></a>
            <?php } ?>

        </div><!-- /.banner -->
    <?php } ?>
</div>
