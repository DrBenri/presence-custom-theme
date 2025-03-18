<?php
$wpz_theme_name = wp_get_theme( get_template() );
$theme = wp_get_theme();
?>

<div id="zoomWrap">

    <div class="admin_main">
        <div id="zoomNav">

             <h3><?php if ( 'inspiro' === $theme->get_template() ) { ?><svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32ZM13.4982 7.17576C13.4982 5.78333 14.627 4.65455 16.0194 4.65455H18.2885C18.4277 4.65455 18.5406 4.76742 18.5406 4.90667V9.39303C18.5406 9.47618 18.4962 9.55301 18.4242 9.59458L13.8473 12.2371C13.6921 12.3267 13.4982 12.2147 13.4982 12.0355V7.17576ZM13.6145 16.7383L18.1915 14.0958C18.3467 14.0062 18.5406 14.1182 18.5406 14.2974V24.8242C18.5406 26.2167 17.4118 27.3455 16.0194 27.3455H13.7503C13.6111 27.3455 13.4982 27.2326 13.4982 27.0933V16.9399C13.4982 16.8567 13.5425 16.7799 13.6145 16.7383Z" fill="#242628"/>
                </svg><?php } ?><?php echo $wpz_theme_name->get( 'Name' ); ?> <span><?php echo $wpz_theme_name->get( 'Version' ); ?></span></h3>

            <div class="zoom-nav-wrapper">


                <?php get_template_part( 'functions/wpzoom/pages/side-menu-main' ); ?>

            </div>

            <h4><?php printf( __( 'WPZOOM Framework <strong>%s</strong>', 'wpzoom' ), WPZOOM::$wpzoomVersion ); ?></h4>

        </div><!-- end #zoomNav -->


        <div class="tab_container">

            <div id="zoomHead">

                 <div class="head_meta">

                    <h3><?php _e( 'Framework Update', 'wpzoom' ); ?></h3>
                    <p><?php _e( 'Update your WPZOOM Framework to access the latest features, improvements, and ensure optimal performance for your theme.', 'wpzoom' ); ?></p>

                    <div id="zoomInfo">
                        <ul>
                            <li>
                                <?php printf( __( 'Your Framework version: <strong>%s</strong>', 'wpzoom' ), WPZOOM::$wpzoomVersion ); ?>
                            </li>

                        </ul>
                    </div>

                </div>
             </div><!-- /#zoomHead -->

            <div id="zoomForm">

                        <?php
                        $isUpdated = false;

                        $remoteVersion = WPZOOM_Framework_Updater::get_remote_version();
                        $localVersion  = WPZOOM_Framework_Updater::get_local_version();

                        // Check if license is active
                        // $isValidLicense = WPZOOM_Framework_Updater::check_license_status();

                        if ( preg_match( '/[0-9]*\.?[0-9]+/', $remoteVersion ) ) {
                            if ( version_compare( $localVersion, $remoteVersion ) < 0 ) {
                                $isUpdated = true;
                            }
                        } else {
                            echo '<p>' . $remoteVersion . '</p>';
                        }

                        ?>

                        <?php if ( $isUpdated ) : ?>

                            <?php // if( $isValidLicense ) {  // not in use yet ?>

                                <div class="framework-notice">
                                    <h3>New Update Available!</h3>
                                    <?php _e( 'A new update for the framework of your <strong>WPZOOM</strong> theme is available!', 'wpzoom' ); ?>
                                    <p><?php _e( '<strong>NOTICE:</strong> Updating the framework will not affect any of the changes or customization you have made to the theme or to your website. For more information visit this <a href="https://www.wpzoom.com/docs/using-the-zoom-framework-automatic-updates/" target="_blank">tutorial</a>.', 'wpzoom' ); ?></p>

                                    <form method="post" id="wpzoom-update">
                                        <input type="hidden" name="wpzoom-update-do" value="update" />
                                        <?php
                                        printf(
                                            __( '%1$sUpdate Framework%2$s ', 'wpzoom' ),
                                            '<input type="submit" class="button button-primary" value="',
                                            '" />'
                                        );
                                        ?>
                                    </form>
                                </div>

                            <?php // } else {

                                 /* ?>
                                <div id="update-nag" class="notice notice-error settings-error wpz-error notice notice-error notice-alt">
                                    <div class="wpz-notice-aside">
                                        <span class="wpz-circle wpz-pulse"> ! </span>
                                    </div>
                                    <div class="wpz-notice-content"><p><?php _e( 'To receive updates for the <strong>WPZOOM Framework</strong>, ensure your theme license is both activated and not expired.', 'wpzoom' ) ?></p></div>
                                </div>
                            <?php // }

                             */ ?>


                            <?php if ( method_exists( 'WPZOOM_Framework_Updater', 'get_changelog' ) ) : ?>
                            <h3>Framework Changelog</h3>
                            <div style="word-wrap: break-word; white-space: pre-wrap; max-width: 100%; height: 430px; overflow: auto; border: 1px solid #ccc; border-radius:3px; padding: 0 20px; background: #F2F4F5; font-size: 13px;">
                                <?php
                                $start     = false;
                                $changelog = WPZOOM_Framework_Updater::get_changelog();
                                $changelog = explode( "\n", $changelog );
                                foreach ( $changelog as $line ) {
                                    if ( preg_match( '/v ((?:\d+(?!\.\*)\.)+)(\d+)?(\.\*)?/i', $line ) ) {
                                        $start = true;
                                        echo '<br/><h3>' . $line . '</h3>';
                                    } elseif ( $start && trim( $line ) ) {
                                        echo '<p>' . $line . '</p>';
                                    }
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                        <?php else : ?>
                            <p><?php printf( __( '&rarr; <strong>You are using the latest framework version:</strong> %s', 'wpzoom' ), $localVersion ); ?></p>
                            <?php option::delete( 'framework_status' ); ?>
                        <?php endif; ?>
                    </div>

        </div><!-- end .tab_container -->
    </div> <!-- /.admin_main -->

    <?php get_template_part( 'functions/wpzoom/pages/footer' ); ?>

</div><!-- end #zoomWrap -->