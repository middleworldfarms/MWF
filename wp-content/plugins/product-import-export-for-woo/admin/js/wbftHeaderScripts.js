(function ($) {
    'use strict';

    $(function () {
        if ($('.wbtf_top_header').length) {

            window.closeTopHeader = function () {
                jQuery.ajax({
                    url: wt_piew_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wt_piew_top_header_loaded',
                    },
                    success: function (response) {
                        if (response.success) {
                            $('.wbtf_top_header').remove();
                            $('.wbte_pimpexp_header').css('top', '0');
                            $('#wpbody-content').css('margin-top', '80px');

                        }
                    },

                });
            }
        }
    });

})(jQuery);

