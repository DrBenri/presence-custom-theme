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
                <button class="zoom-nav-back visible" type="button">
                    <!-- <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 11H7.83L13.42 5.41L12 4L4 12L12 20L13.41 18.59L7.83 13H20V11Z" fill="currentColor"/></svg> -->
                    <span class="btn-text-default"><?php _e('&larr; Dashboard Menu', 'wpzoom'); ?></span>
                    <span class="btn-text-alternate" style="display: none;"><?php _e('Theme Options &rarr;', 'wpzoom'); ?></span>
                </button>

                <div class="zoom-nav-main active">
                    <?php WPZOOM_Admin_Settings_Page::menu(); ?>
                </div>

                <div class="zoom-nav-secondary">

                    <?php get_template_part( 'functions/wpzoom/pages/side-menu-main' ); ?>

                </div>
            </div>

            <h4><?php printf( __( 'WPZOOM Framework <strong>%s</strong>', 'wpzoom' ), WPZOOM::$wpzoomVersion ); ?></h4>

		</div><!-- end #zoomNav -->

		<div class="tab_container">

            <div id="zoomHead">
                <script type="text/javascript">
                var wpzoom_ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                </script>
                <div id="zoomLoading">
                    <p><?php _e( 'Loading', 'wpzoom' ); ?></p>
                </div>
                <div id="zoomSuccess">
                    <p><?php _e( 'Options successful saved', 'wpzoom' ); ?></p>
                </div>
                <div id="zoomFail">
                    <p><?php _e( 'Can\'t save options. Please contact <a href="https://wpzoom.com/support/">WPZOOM Support</a>.', 'wpzoom' ); ?></p>
                </div>
                <div id="zoomTheme">
                    <?php
                        $name       = 'Demo Content';
                        $xml_data   = get_demo_xml_data();
                        $has_access = false;
                        $link_href  = 'wpz_' . substr( md5( $name ), 0, 8 );

                    if ( $xml_data['remote']['response'] ) {
                        $has_access = true;
                    } elseif ( $xml_data['local']['response'] ) {
                        $has_access = true;
                    }
                    ?>

                </div>

                 <div class="head_meta">

                    <h3><?php _e( 'Theme Options', 'wpzoom' ); ?></h3>
                    <p><?php _e( 'Adjust your theme settings to match your style and preferences here', 'wpzoom' ); ?></p>

                    <div id="zoomInfo">
                        <ul>
                            <li>
                                <a href="<?php echo admin_url( 'customize.php' ); ?>" target="_blank"><?php _e( 'Theme Customizer', 'wpzoom' ); ?></a>
                            </li>

                            <?php if ( $has_access && !zoom_current_theme_supports( 'wpz-onboarding' ) && WPZOOM_Guard_Shell::is_guard_active() ) : ?>
                                <li class="demo-import" id="zoomInfo-demoimport"><a href="#<?php echo esc_attr( $link_href ); ?>" id="wpz-demo-content-icon" title="<?php echo __( 'Import Demo Content', 'wpzoom' ); ?>"><i class="fa fa-download"></i> <?php _e( 'Import Demo Content', 'wpzoom' ); ?></a></li>
                            <?php endif ?>

                            <?php if ( ! wpzoom::$tf ) : ?>
                            <li class="documentation" id="zoomInfo-documentation">
                                <a href="https://www.wpzoom.com/documentation/<?php echo str_replace( '_', '-', WPZOOM::$theme_raw_name ); ?>" target="_blank"><?php _e( 'Theme Documentation', 'wpzoom' ); ?> &#8599;</a>
                            </li>
                            <?php endif; ?>

                            <li class="support" id="zoomInfo-support">
                                <a href="https://www.wpzoom.com/support/" target="_blank"><?php _e( 'Support Desk', 'wpzoom' ); ?> &#8599;</a>
                            </li>


                        </ul>
                    </div>

                </div>
             </div><!-- /#zoomHead -->


			<form id="zoomForm" method="post">
				<?php WPZOOM_Admin_Settings_Page::content(); ?>

				<input type="hidden" name="action" value="save" />
				<input type="hidden" id="zoom-nonce" name="_ajax_nonce" value="<?php echo wp_create_nonce( 'wpzoom-ajax-save' ); ?>" />
			</form>

            <div class="zoomActionButtons">

                <?php if ( WPZOOM_Admin_Settings_Page::check_button() ) : ?>
                <p class="submit">
                    <input id="submitZoomForm" name="save" class="button button-primary button-large" type="submit" value="<?php _e( 'Save all changes', 'wpzoom' ); ?>" />
                </p>
                <?php endif; ?>

                <form id="zoomReset" method="post">
                    <p class="submit" />
                        <input name="reset" class="button-secondary" type="submit" value="<?php _e( 'Reset settings', 'wpzoom' ); ?>" />
                        <input type="hidden" name="action" value="reset" />
                    </p>
                </form>

            </div><!-- end of .zoomActionButtons -->
		</div><!-- end .tab_container -->
		<?php WPZOOM_Admin_Settings_Page::license_popup(); ?>
	</div> <!-- /.admin_main -->

    <?php get_template_part( 'functions/wpzoom/pages/footer' ); ?>

</div><!-- end #zoomWrap -->