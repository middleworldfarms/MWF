(function ($) {
    'use strict';

    $(function () {
        var wt_cta_banner = {
            init: function () {
                this.moveBanner();
                this.initToggleFeatures();
                this.initDraggable();
                this.initDismissButtons();
            },

            moveBanner: function() {
                $('#wbte_product_import_export_pro').appendTo('#side-sortables').addClass('postbox');
            },

            initToggleFeatures: function() {
                const toggleBtn = $('.wbte-cta-toggle');
                const hiddenFeatures = $('.hidden-feature');
                
                // Set initial text
                toggleBtn.text(toggleBtn.data('show-text'));
                
                toggleBtn.on('click', function(e) {
                    e.preventDefault();
                    const $this = $(this);
                    
                    hiddenFeatures.slideToggle(100, function() {
                        // After animation completes, update the text based on visibility
                        if ($(this).is(':visible')) {
                            toggleBtn.text(toggleBtn.data('hide-text'));
                        } else {
                            toggleBtn.text(toggleBtn.data('show-text'));
                        }
                    });
                });
            },

            initDraggable: function() {
                const banner = $('.wbte-cta-banner');
                let originalPosition;
                
                banner.draggable({
                    handle: '.wbte-cta-header',
                    containment: 'window',
                    start: function(event, ui) {
                        originalPosition = ui.position;
                    },
                    stop: function(event, ui) {
                        $(this).animate(originalPosition, {
                            duration: 300,
                            easing: 'swing'
                        });
                    }
                });
            },

            initDismissButtons: function() {
                $('.wbte-cta-dismiss').on('click', function(e) {
                    e.preventDefault();
                    var $this = $(this);
                    var $banner = $this.closest('.postbox');
                    var bannerId = $banner.attr('id');
                    
                    // Determine which banner is being dismissed and use appropriate AJAX data
                    var ajaxData = {};
                    
                    if (bannerId === 'wbte_product_import_export_pro') {
                        ajaxData = {
                            action: 'wt_dismiss_product_ie_cta_banner',
                            nonce: typeof wt_product_ie_cta_banner_ajax !== 'undefined' ? wt_product_ie_cta_banner_ajax.nonce : ''
                        };
                    } else if (bannerId === 'wbte_pdf_invoice_pro') {
                        ajaxData = {
                            action: 'wt_dismiss_invoice_cta_banner',
                            nonce: typeof wt_invoice_cta_banner_ajax !== 'undefined' ? wt_invoice_cta_banner_ajax.nonce : ''
                        };
                    } else if (bannerId === 'wbte_coupon_import_export_pro') {
                        ajaxData = {
                            action: 'wt_dismiss_smart_coupon_cta_banner',
                            nonce: typeof wt_smart_coupon_cta_banner_ajax !== 'undefined' ? wt_smart_coupon_cta_banner_ajax.nonce : ''
                        };
                    }
                    
                    if (ajaxData.action && ajaxData.nonce) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: ajaxData,
                            success: function(response) {
                                if (response.success) {
                                    $banner.hide();
                                }
                            }
                        });
                    }
                });
            }
        };

        wt_cta_banner.init();

        // Hide hidden features by default
        $('.hidden-feature').hide();
    });
})(jQuery);