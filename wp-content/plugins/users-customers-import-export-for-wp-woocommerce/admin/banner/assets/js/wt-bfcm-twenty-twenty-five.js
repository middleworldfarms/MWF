(function ($) {
    'use strict';

    $(function () {
        const wt_bfcm_twenty_twenty_five_banner = {
            init: function () { 
                const data_obj = {
                    _wpnonce: wt_bfcm_twenty_twenty_five_banner_js_params.nonce,
                    action: wt_bfcm_twenty_twenty_five_banner_js_params.action,
                    wt_bfcm_twenty_twenty_five_banner_action_type: '',
                };

                $(document).on('click', '.wt-bfcm-banner-2025 .bfcm_cta_button', function (e) { 
                    e.preventDefault(); 
                    const elm = $(this);
                    window.open(wt_bfcm_twenty_twenty_five_banner_js_params.cta_link, '_blank'); 
                    elm.parents('.wt-bfcm-banner-2025').hide();
                    data_obj['wt_bfcm_twenty_twenty_five_banner_action_type'] = 3; /** Clicked the button. */
                    
                    $.ajax({
                        url: wt_bfcm_twenty_twenty_five_banner_js_params.ajax_url,
                        data: data_obj,
                        type: 'POST'
                    });
                }).on('click', '.wt-bfcm-banner-2025 .notice-dismiss', function(e) {
                    e.preventDefault();
                    data_obj['wt_bfcm_twenty_twenty_five_banner_action_type'] = 2; /** Closed by user. */
                    
                    $.ajax({
                        url: wt_bfcm_twenty_twenty_five_banner_js_params.ajax_url,
                        data: data_obj,
                        type: 'POST',
                    });
                });
            }
        };
        wt_bfcm_twenty_twenty_five_banner.init();
    });

})(jQuery);