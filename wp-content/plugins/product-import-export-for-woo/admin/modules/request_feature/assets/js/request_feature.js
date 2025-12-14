
(function ($) {
    "use strict";

    $(document).ready(function () {
        // Show popup
        $('.wbte_pimpexp_help-widget_popupover.wbte_pimpexp_help-widget_click ul li:nth-child(4) a').on('click', function (e) {
            e.preventDefault(); // Prevent the default action (if needed)
            $('#wt_iew_request_a_feature_popup').fadeIn('fast');
            $('.wt_iew_overlay').css('display', 'block');
        });

        // Hide popup
        $('#wt_iew_request_a_feature_close, #wt_iew_request_a_feature_cancel').on('click', function () {
            $('#wt_iew_request_a_feature_popup').fadeOut('fast');
            $('.wt_iew_overlay').css('display', 'none');

        });

        // Toggle email field visibility
        $('#wt_iew_request_a_feature_take_email').on('change', function () {
            if ($(this).is(':checked')) {
                $('.wt_iew_request_a_feature_email_container').slideDown('fast');
            } else {
                $('.wt_iew_request_a_feature_email_container').slideUp('fast');
            }
        });

        $('.wt_iew_request_a_feature_popup form').on('submit', function (e) {

            e.preventDefault();

            /* Validation */
            if ("" === $('[name="wt_iew_request_a_feature_msg"]').val().trim()) {
                wt_iew_notify_msg.error(wt_iew_request_feature_js_params.enter_message, false);
                $('[name="wt_iew_request_a_feature_msg"]').trigger('focus');
                return false;
            }

            if ($('#wt_iew_request_a_feature_take_email').is(':checked') && "" === $('[name="wt_iew_request_a_feature_email"]').val().trim()) {
                wt_iew_notify_msg.error(wt_iew_request_feature_js_params.email_message, false);
                $('[name="wt_iew_request_a_feature_email"]').trigger('focus');
                return false;
            }


            /* Ajax request */
            var btn_html_bckup = $('[name="wt_iew_request_feature_sbmt_btn"]').text();
            $('[name="wt_iew_request_feature_sbmt_btn"]').prop({ 'disabled': true }).text(wt_iew_request_feature_js_params.sending);

            $.ajax({
                url: wt_iew_request_feature_js_params.ajax_url,
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',

                success: function (data) {
                    if (data.status) {
                        wt_iew_notify_msg.success(wt_iew_request_feature_js_params.success_msg, true);
                        $('#wt_iew_request_a_feature_popup').fadeOut('fast');
                        $('.wt_iew_overlay').css('display', 'none');
                        $('#wt_iew_request_a_feature_form')[0].reset();

                    } else {
                        wt_iew_notify_msg.error(data.msg, true);
                    }
                },
                error: function () {
                    wt_iew_notify_msg.error(wt_iew_request_feature_js_params.unable_to_submit, false);
                },
                complete: function () {
                    $('[name="wt_iew_request_feature_sbmt_btn"]').prop({ 'disabled': false }).text(btn_html_bckup);
                }
            });

        });

        $(document).on('click', '.wt_iew_post-type-cards .wt_iew_post-type-card', function () {
            var cardHeader = $(this).find('.wt_iew_post-type-card-hd');
            var postTypeName = cardHeader.text().trim();
        
            // Arrays for different post types
            var productsPostsArr = ['Product', 'Product Review', 'Product Categories', 'Product Tags'];
            var orderPostsArr = ['Order', 'Coupon'];
            var userPostsArr = ['User/Customer'];
        
            // Corresponding plugin names
            var pluginNames = {
                product_import: productsPostsArr,
                order_import: orderPostsArr,
                user_import: userPostsArr
            };
        
            // Determine the plugin name based on the matching array
            var matchedPluginName = Object.keys(pluginNames).find(function (key) {
                return pluginNames[key].includes(postTypeName);
            });
            var pluginName=matchedPluginName || 'No match found';
            $('#wt_iew_request_a_feature_popup').find("input[name='plugin_name']").val(pluginName);

        });


        $(document).on('click', '.wt_iew_post-type-card', function () {

            var datType = $(this).data('post-type');
            var pluginMap = {
                product: ['product', 'product_review', 'product_categories', 'product_tags'],
                user: ['user'],
                order: ['order', 'coupon']
            };

            var faqLinks = {
                product: 'https://wordpress.org/plugins/product-import-export-for-woo/#:~:text=Export%20for%20WooCommerce-,FAQ,-Import%20of%20attributes',
                user: 'https://wordpress.org/plugins/users-customers-import-export-for-wp-woocommerce/#:~:text=import%20export%20log-,FAQ,-Does%20this%20plugin',
                order: 'https://wordpress.org/plugins/order-import-export-for-woocommerce/#:~:text=Exported%20coupon%20CSV-,FAQ,-Does%20this%20plugin'
            };

            var setupGuideLinks = {
                product: 'https://www.webtoffee.com/category/basic-plugin-documentation/#:~:text=Product%20Import/Export',
                user: 'https://www.webtoffee.com/category/basic-plugin-documentation/#:~:text=View%20All-,User%20Import/Export,-User%20Import%20Export',
                order: 'https://www.webtoffee.com/category/basic-plugin-documentation/#:~:text=WooCommerce%20customers%20list-,Order%20Import/Export,-Order/Coupon/Subscription'
            };

            var contactSupportLinks = {
                product: 'https://wordpress.org/support/plugin/product-import-export-for-woo/',
                user: 'https://wordpress.org/plugins/users-customers-import-export-for-wp-woocommerce/',
                order: 'https://wordpress.org/support/plugin/order-import-export-for-woocommerce/'
            };

            if (!$(this).find('.wt_iew_free_addon_warn').text().trim()) {
                Object.keys(pluginMap).forEach(function (key) {
                    if (pluginMap[key].includes(datType)) {
                        // Update help section links
                        var helpWidgetLinks = $('.wbte_pimpexp_help-widget_popupover.wbte_pimpexp_help-widget_click ul a');
                        // Update the links based on the order
                        $(helpWidgetLinks[0]).attr('href', faqLinks[key]); // FAQ
                        $(helpWidgetLinks[1]).attr('href', setupGuideLinks[key]); // Setup Guide
                        $(helpWidgetLinks[2]).attr('href', contactSupportLinks[key]); // Contact Support
                    }
                });
            }
        });
        
    });
})(jQuery);
