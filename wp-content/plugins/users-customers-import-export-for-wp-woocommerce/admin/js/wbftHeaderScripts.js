(function ($) {
    'use strict';

    $(function () {
        if ($('.wbtf_users_top_header').length) {
            window.closeTopHeader = function () {
                jQuery.ajax({
                    url: wt_uiew_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wt_uiew_top_header_loaded',
                    },
                    success: function (response) {
                        if (response.success) {
                            
                            $('.wbtf_users_top_header').remove();
                            $('.wbte_uimpexp_header').css('top', '0');
                            $('#wpbody-content').css('margin-top', '80px');

                        }
                    },

                });
            }
        }
    });


})(jQuery);
