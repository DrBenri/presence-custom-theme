/**
 * Theme functions file
 */
(function ($) {
    'use strict';

    var $document = $(document);
    var $window = $(window);


    /**
    * Document ready (jQuery)
    */
    $(function () {

        /**
         * Activate superfish menu.
         */
        $('.sf-menu').superfish({
            'speed': 'fast',
            'delay' : 0,
            'animation': {
                'height': 'show'
            }
        });


        /**
         * SlickNav
         */

        $('#menu-main-slide').slicknav({
            prependTo:'.navbar-header-main',
            allowParentLinks: true,
            closedSymbol: "",
            openedSymbol: ""
            }
        );


        /**
         * FitVids - Responsive Videos in posts
         */
        $(".entry-content, .video_cover, .cover").fitVids();


        /**
         * Search form in header.
         */
        $(".sb-search").sbSearch();


        /**
         * Retina-ready images in Slider.
         */
        $.fn.responsiveSliderImages();

    });

    $window.on('load', function() {
        /**
         * Activate main slider.
         */
        $('#slider').sllider();


    });


    $.fn.sllider = function() {
        return this.each(function () {
            var $this = $(this);

            var $slides = $this.find('.slide');

            if ($slides.length <= 1) {
                $slides.addClass('is-selected');

                return;
            }

            var flky = new Flickity('.slides', {
                autoPlay: (zoomOptions.slideshow_auto ? parseInt(zoomOptions.slideshow_speed, 10) : false),
                cellAlign: 'center',
                contain: true,
                percentPosition: false,
                pageDots: true,
                wrapAround: true,
                arrowShape: {
                  x0: 10,
                  x1: 60, y1: 50,
                  x2: 65, y2: 45,
                  x3: 20
                },
                accessibility: false
            });
        });
    };


    $.fn.sbSearch = function() {
       return this.each(function() {
           new UISearch( this );
       });
    };


    $.fn.responsiveSliderImages = function () {
        $(window).on('resize orientationchange', update);

        function update() {
            var windowWidth = $(window).width();


            if (windowWidth > 900) {
                retinajs();
            }
        }

        update();
    };


})(jQuery);