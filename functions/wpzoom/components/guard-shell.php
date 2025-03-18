<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main WPZOOM_Guard_Shell class.
 */
class WPZOOM_Guard_Shell {

    /**
     * Constructor.
     */
    public function __construct() {
        if( ! self::is_guard_active() ) {
            remove_action( 'admin_notices', 'WPZOOM_Onboarding_License::license_notice', 99 );
        }
    }

    public static function is_guard_active() {

        $active_themes = array(
            'academica_pro_3',
            'angle',
            'anchor',
            'capital',
            'compass',
            'delicio',
            'domino',
            'edublock-pro',
            'foodie-blocks',
            'foodica',
            'foodica-pro',
            'insight',
            'inspiro',
            'inspiro-blocks',
            'meeta',
            'monte',
            'presence',
            'tempo',
            'tribune',
            'uniblock-pro',
            'venture',
            'videobox',
            'videozoom',
            'wpzoom-balance',
            'wpzoom-cookbook',
            'wpzoom-cookely',
            'wpzoom-derive',
            'wpzoom-diamond',
            'wpzoom-eclipse',
            'wpzoom-gourmand',
            'wpzoom-indigo',
            'wpzoom-inspiro-pro',
            'wpzoom-prime-news',
            'wpzoom-reel',
            'wpzoom-rezzo',
            'wpzoom-velure',
        );

        if( ! in_array( WPZOOM::$theme_raw_name, $active_themes ) ) {
            return false;
        }

        return true;

    }
}
new WPZOOM_Guard_Shell();
