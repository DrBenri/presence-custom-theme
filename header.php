<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

    <?php wp_head(); ?>
</head>

<?php $template = option::get( 'layout_global' ); ?>

<body <?php body_class(get_theme_mod('theme-layout-type', zoom_customizer_get_default_option_value('theme-layout-type', presence_customizer_data()))); ?>>

    <div class="page-wrap">

        <header class="site-header">

            <nav class="top-navbar" role="navigation">

                <div class="inner-wrap">

                    <div class="header_social">
                        <?php dynamic_sidebar('header_social'); ?>

                    </div>

                    <div id="navbar-top">

                        <?php if (has_nav_menu( 'secondary' )) {
                            wp_nav_menu( array(
                                'menu_class'     => 'navbar-wpz dropdown sf-menu',
                                'theme_location' => 'secondary'
                            ) );
                        } ?>

                    </div><!-- #navbar-top -->

                </div><!-- /.inner-wrap -->

            </nav><!-- .navbar -->

            <div class="clear"></div>

            <div class="brand-wrap <?php echo get_theme_mod('logo-align', zoom_customizer_get_default_option_value('logo-align', presence_customizer_data())); ?>">

                <div class="inner-wrap">

                    <div class="navbar-brand-wpz<?php if (option::is_on('ad_head_select')) { ?> left-align<?php } ?>">

                            <?php if (!has_custom_logo()) : ?><h1><?php endif; ?>

                            <?php presence_custom_logo() ?>

                            <?php if (!has_custom_logo()): ?></h1><?php endif; ?>

                        <p class="tagline"><?php bloginfo('description')  ?></p>

                    </div><!-- .navbar-brand-wpz -->


                    <?php if (option::is_on('ad_head_select')) { ?>
                        <div class="adv">

                            <?php if ( option::get('ad_head_code') <> "") {
                                echo stripslashes(option::get('ad_head_code'));
                            } else { ?>
                                <a href="<?php echo option::get('banner_top_url'); ?>"><img src="<?php echo option::get('banner_top'); ?>" alt="<?php echo option::get('banner_top_alt'); ?>" /></a>
                            <?php } ?>

                        </div><!-- /.adv --> <div class="clear"></div>
                    <?php } ?>

                </div><!-- .inner-wrap -->

            </div><!-- .brand-wrap -->


            <nav class="main-navbar <?php echo get_theme_mod('menu-align', zoom_customizer_get_default_option_value('menu-align', presence_customizer_data())); ?>" role="navigation">

                <div class="inner-wrap">

                    <div id="sb-search" class="sb-search">
                        <?php get_search_form(); ?>
                    </div>

                    <div class="navbar-header-main">
                        <?php if ( has_nav_menu( 'tertiary' ) ) { ?>

                           <?php wp_nav_menu( array(
                               'container_id'   => 'menu-main-slide',
                               'theme_location' => 'tertiary',
                               'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s' . presence_wc_menu_cartitem() . '</ul>'
                           ) );
                       }  ?>

                    </div>

                    <div id="navbar-main">

                        <?php if (has_nav_menu( 'primary' )) {
                            wp_nav_menu( array(
                                'menu_class'     => 'navbar-wpz dropdown sf-menu',
                                'theme_location' => 'primary',
                                'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s' . presence_wc_menu_cartitem() . '</ul>'
                            ) );
                        } ?>


                    </div><!-- #navbar-main -->

                </div><!-- .inner-wrap -->

            </nav><!-- .main-navbar -->

            <div class="clear"></div>

        </header><!-- .site-header -->

        <div class="inner-wrap">