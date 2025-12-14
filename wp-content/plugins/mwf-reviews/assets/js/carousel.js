/**
 * MWF Reviews Carousel
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialize carousel if enabled
        if (typeof mwfReviewsSettings !== 'undefined' && mwfReviewsSettings.enableCarousel) {
            $('.mwf-reviews-carousel[data-carousel="yes"]').each(function() {
                var $carousel = $(this);
                
                // Initialize Slick carousel
                $carousel.slick({
                    dots: true,
                    arrows: true,
                    infinite: true,
                    speed: 500,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    autoplay: mwfReviewsSettings.autoplay,
                    autoplaySpeed: mwfReviewsSettings.autoplaySpeed,
                    pauseOnHover: true,
                    pauseOnFocus: true,
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 1
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                arrows: false
                            }
                        }
                    ]
                });
            });
        }
    });
})(jQuery);
