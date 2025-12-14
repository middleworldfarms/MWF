/* EMA Banner Analytics Page Script */

jQuery(document).ready(function($) {
    // Handle dismiss button click using event delegation
    jQuery(document).on('click', '.wbte_ema_banner_analytics_page_dismiss', function(e) {
        e.preventDefault();
   
        var $banner = jQuery(this).closest('.wbte_ema_banner_analytics_page');
        var $button = jQuery(this);
        
        // Reduce banner opacity for instant visual feedback
        $banner.css('opacity', '0.5');
        
        // Disable button to prevent multiple clicks
        $button.prop('disabled', true);
        
        // Send AJAX request
        $.ajax({
            url: wbte_ema_banner_params.ajaxurl,
            type: 'POST',
            data: {
                action: 'wbte_ema_banner_analytics_page_dismiss',
                nonce: wbte_ema_banner_params.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Slide up banner on success
                    $banner.slideUp();
                } else {
                    // If AJAX fails, restore opacity and re-enable button
                    $banner.css('opacity', '1');
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                // If AJAX error, restore opacity and re-enable button
                $banner.css('opacity', '1');
                $button.prop('disabled', false);
            }
        });
    });

});

