<?php
if (!class_exists('WT_ProdImpExp_Uninstall_Feedback')) :

    /**
     * Uninstall feedback class
     */
    class WT_ProdImpExp_Uninstall_Feedback {

        public function __construct() {
            add_action('admin_footer', array($this, 'deactivate_scripts'));
            add_action('wp_ajax_pipe_submit_uninstall_reason', array($this, "send_uninstall_reason"));
        }

        public function deactivate_scripts() {

            global $pagenow;
            if ('plugins.php' != $pagenow) {
                return;
            }
            $reasons = $this->get_uninstall_reasons();
            ?>
            <div class="pipe-modal" id="pipe-pipe-modal">
                <div class="pipe-modal-wrap">
                    <div class="pipe-modal-header">
                        <h3><?php esc_html_e('If you have a moment, please let us know why you are deactivating:', 'product-import-export-for-woo'); ?></h3>
                    </div>
                    <div class="pipe-modal-body">
                        <ul class="reasons">
                            <?php foreach ($reasons as $reason) { ?>
                                <li data-type="<?php echo esc_attr($reason['type']); ?>" data-placeholder="<?php echo esc_attr($reason['placeholder']); ?>">
                                    <label><input type="radio" name="selected-reason" value="<?php echo esc_attr($reason['id']); ?>"> <?php echo esc_html($reason['text']); ?></label>
                                </li>
                            <?php } ?>
                        </ul>
                        <div class="wt-uninstall-feedback-privacy-policy">
                            <?php esc_html_e('We do not collect any personal data when you submit this form. It\'s your feedback that we value.', 'product-import-export-for-woo'); ?>
                            <a href="https://www.webtoffee.com/privacy-policy/" target="_blank"><?php esc_html_e('Privacy Policy', 'product-import-export-for-woo'); ?></a>
                        </div>

                        <br>
                        <label>
                        <input type="checkbox" id="wt_wfproductimpexp_contact_me_checkbox" name="wt_wfproductimpexp_contact_me_checkbox" value="1">
                        <?php esc_html_e("Webtoffee can contact me about this feedback.", "product-import-export-for-woo"); ?>
                        </label>
                        <div id="wt_wfproductimpexp_email_field_wrap" style="display:none; margin-top:10px;">
                            <label for="wt_wfproductimpexp_contact_email" style="font-weight:bold;"><?php esc_html_e("Enter your email address.", "product-import-export-for-woo"); ?></label>
                            <br>
                            <input type="email" id="wt_wfproductimpexp_contact_email" name="wt_wfproductimpexp_contact_email" class="input-text" style="width:75%; height: 40px; padding:2px; margin-top:10px; border-radius:5px; border:2px solid #2874ba;" placeholder="<?php esc_attr_e("Enter email address", "product-import-export-for-woo"); ?>">
                            <div id="wt_wfproductimpexp_email_error" style="color:red; display:none; font-size:12px; margin-top:5px;"></div>
                        </div>
                        <br><br>

                    </div>
                    <div class="pipe-modal-footer">
                        <a href="#" class="dont-bother-me"><?php esc_html_e('I rather wouldn\'t say', 'product-import-export-for-woo'); ?></a>
                        <a class="button-primary" href="https://wordpress.org/support/plugin/product-import-export-for-woo/" target="_blank">
                        <span class="dashicons dashicons-external" style="margin-top:3px;"></span>
                        <?php esc_html_e('Get support', 'product-import-export-for-woo'); ?></a>
                        <button class="button-primary pipe-model-submit"><?php esc_html_e('Submit & Deactivate', 'product-import-export-for-woo'); ?></button>
                        <button class="button-secondary pipe-model-cancel"><?php esc_html_e('Cancel', 'product-import-export-for-woo'); ?></button>
                    </div>
                </div>
            </div>

            <style type="text/css">
                .pipe-modal {
                    position: fixed;
                    z-index: 99999;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    background: rgba(0,0,0,0.5);
                    display: none;
                }
                .pipe-modal.modal-active {display: block;}
                .pipe-modal-wrap {
                    width: 50%;
                    position: relative;
                    margin: 10% auto;
                    background: #fff;
                }
                .pipe-modal-header {
                    border-bottom: 1px solid #eee;
                    padding: 8px 20px;
                }
                .pipe-modal-header h3 {
                    line-height: 150%;
                    margin: 0;
                }
                .pipe-modal-body {padding: 5px 20px 20px 20px;}
                .pipe-modal-body .input-text,.pipe-modal-body textarea {width:75%;}
                .pipe-modal-body .reason-input {
                    margin-top: 5px;
                    margin-left: 20px;
                }
                .pipe-modal-footer {
                    border-top: 1px solid #eee;
                    padding: 12px 20px;
                    text-align: right;
                }
                .reviewlink, .supportlink{
                    padding:10px 0px 0px 35px !important;
                    font-size: 14px;
                }
                .review-and-deactivate{
                    padding:5px;
                }
                .wt-uninstall-feedback-privacy-policy {
                    text-align: left;
                    font-size: 12px;
                    color: #aaa;
                    line-height: 14px;
                    margin-top: 20px;
                    font-style: italic;
                }

                .wt-uninstall-feedback-privacy-policy a {
                    font-size: 11px;
                    color: #4b9cc3;
                    text-decoration-color: #99c3d7;
                }
            </style>
            <script type="text/javascript">
                (function ($) {
                    $(function () {
                        var modal = $('#pipe-pipe-modal');
                        var deactivateLink = '';


                        $('#the-list').on('click', 'a.pipe-deactivate-link', function (e) {
                            e.preventDefault();
                            modal.addClass('modal-active');
                            deactivateLink = $(this).attr('href');
                            modal.find('a.dont-bother-me').attr('href', deactivateLink).css('float', 'left');
                        });

                        modal.on('click', 'a.review-and-deactivate', function (e) {
                            e.preventDefault();
                            window.open("https://wordpress.org/support/plugin/product-import-export-for-woo/reviews/#new-post");
                            window.location.href = deactivateLink;
                        });
                        modal.on('click', 'a.doc-and-support-doc', function (e) {
                            e.preventDefault();
                            window.open("https://www.webtoffee.com/product-import-export-plugin-woocommerce-user-guide/");
                        });
                        modal.on('click', 'a.doc-and-support-forum', function (e) {
                            e.preventDefault();
                            window.open("https://www.webtoffee.com/contact/");
                        });							
                        modal.on('click', 'button.pipe-model-cancel', function (e) {
                            e.preventDefault();
                            modal.removeClass('modal-active');
                        });
                        modal.on('click', 'input[type="radio"]', function () {
                            var parent = $(this).parents('li:first');
                            modal.find('.reason-input').remove();
                            var inputType = parent.data('type'),
                                inputPlaceholder = parent.data('placeholder');
                                var reasonInputHtml = '';                                  
                            if ('reviewhtml' === inputType) {
                                if($('.reviewlink').length == 0){
                                    reasonInputHtml = '<div class="reviewlink"><a href="#" target="_blank" class="review-and-deactivate"><?php esc_html_e('Deactivate and leave a review', 'product-import-export-for-woo'); ?> <span class="xa-pipe-rating-link"> &#9733;&#9733;&#9733;&#9733;&#9733; </span></a></div>';
                                }
                            }else if('supportlink' === inputType){
							    if($('.supportlink').length == 0){
                                    reasonInputHtml = '<div class="supportlink"><?php esc_html_e('Please go through the', 'product-import-export-for-woo'); ?><a href="#" target="_blank" class="doc-and-support-doc"> <?php esc_html_e('documentation', 'product-import-export-for-woo'); ?></a> <?php esc_html_e('or contact us via', 'product-import-export-for-woo'); ?><a href="#" target="_blank" class="doc-and-support-forum"> <?php esc_html_e('support', 'product-import-export-for-woo'); ?></a></div>';
                                }
							}else {
                                if($('.reviewlink').length){
                                   $('.reviewlink'). remove();
                                }
                                if($('.supportlink').length){
                                   $('.supportlink'). remove();
                                }								
                                reasonInputHtml = '<div class="reason-input">' + (('text' === inputType) ? '<input type="text" class="input-text" size="40" />' : '<textarea rows="5" cols="45"></textarea>') + '</div>';
                            }
                            if (inputType !== '') {
                                parent.append($(reasonInputHtml));
                                parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                            }
                        });

                        modal.on('change', '#wt_wfproductimpexp_contact_me_checkbox', function (e) {
                            if ($(this).is(':checked')) {
                                $('#wt_wfproductimpexp_email_field_wrap').slideDown();
                            } else {
                                $('#wt_wfproductimpexp_email_field_wrap').slideUp();
                                $('#wt_wfproductimpexp_contact_email').val('');
                                $('#wt_wfproductimpexp_email_error').hide();
                            }
                        });

                        modal.on('click', 'button.pipe-model-submit', function (e) {
                            e.preventDefault();
                            var button = $(this);
                            if (button.hasClass('disabled')) {
                                return;
                            }

                            // Email validation
                            var emailCheckbox = $('#wt_wfproductimpexp_contact_me_checkbox');
                            var emailField = $('#wt_wfproductimpexp_contact_email');
                            var emailError = $('#wt_wfproductimpexp_email_error');
                            emailError.hide();
                            if (emailCheckbox.is(':checked')) {
                                var emailVal = emailField.val();
                                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                                if (!emailVal || !emailPattern.test(emailVal)) {
                                    emailError.text('<?php echo esc_js(__('Please enter a valid email address.', 'product-import-export-for-woo')); ?>').show();
                                    emailField.focus();
                                    return;
                                }
                            }



                            var $radio = $('input[type="radio"]:checked', modal);
                            var $selected_reason = $radio.parents('li:first'),
                                    $input = $selected_reason.find('textarea, input[type="text"]');

                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'pipe_submit_uninstall_reason',
                                    _wpnonce: '<?php echo esc_js(wp_create_nonce('productimport_submit_uninstall_reason')); ?>',
                                    reason_id: (0 === $radio.length) ? 'none' : $radio.val(),
                                    reason_info: (0 !== $input.length) ? $input.val().trim() : '',
                                    user_email: $('#wt_wfproductimpexp_contact_me_checkbox').is(':checked') ? $('#wt_wfproductimpexp_contact_email').val() : ''

                                },
                                beforeSend: function () {
                                    button.addClass('disabled');
                                    button.text('Processing...');
                                },
                                complete: function () {
                                    window.location.href = deactivateLink;
                                }
                            });
                        });
                    });
                }(jQuery));
            </script>
            <?php
        }

        public function send_uninstall_reason() {

            global $wpdb;

            // Verify nonce for security
            if ( ! check_ajax_referer( 'productimport_submit_uninstall_reason', '_wpnonce', false ) || ! current_user_can('manage_options') ) {
                wp_send_json_error();
            }

            if (!isset($_POST['reason_id'])) {
                wp_send_json_error();
            }



            $data = array(
                'reason_id' => sanitize_text_field( wp_unslash( $_POST['reason_id'] ) ),
                'plugin' => "wfpipe",
                'auth' => 'wfpipe_uninstall_1234#',
                'date' => gmdate("M d, Y h:i:s A"),
                'url' => '',
                'user_email' => isset($_POST['user_email']) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '',
                'reason_info' => isset($_REQUEST['reason_info']) ? sanitize_textarea_field( wp_unslash( $_REQUEST['reason_info'] ) ) : '',
                'software' => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
                'php_version' => phpversion(),
                'mysql_version' => $wpdb->db_version(),
                'wp_version' => get_bloginfo('version'),
                'wc_version' => (!defined('WC_VERSION')) ? '' : WC_VERSION,
                'locale' => get_locale(),
                'languages' => implode( ",", get_available_languages() ),
                'theme' => wp_get_theme()->get('Name'),
                'multisite' => is_multisite() ? 'Yes' : 'No',
                'wfpipe_version' => WT_P_IEW_VERSION
            );
            // Write an action/hook here in webtoffe to recieve the data
            $resp = wp_remote_post('https://feedback.webtoffee.com/wp-json/wfpipe/v1/uninstall', array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => false,
                'body' => $data,
                'cookies' => array()
                    )
            );
            wp_send_json_success($resp);
        }

        private function get_uninstall_reasons() {

            $reasons = array(
                array(
                    'id' => 'used-it',
                    'text' => __('Used it successfully. Don\'t need anymore.', 'product-import-export-for-woo'),
                    'type' => 'reviewhtml',
                    'placeholder' => __('Have used it successfully and aint in need of it anymore', 'product-import-export-for-woo')
                ),
                array(
                    'id' => 'temporary-deactivation',
                    'text' => __('Temporary deactivation', 'product-import-export-for-woo'),
                    'type' => '',
                    'placeholder' => __('Temporary de-activation. Will re-activate later.', 'product-import-export-for-woo')
                ),				
                array(
                    'id' => 'could-not-understand',
                    'text' => __('I couldn\'t understand how to make it work', 'product-import-export-for-woo'),
                    'type' => 'supportlink',
                    'placeholder' => __('Would you like us to assist you?', 'product-import-export-for-woo')
                ),
                array(
                    'id' => 'found-better-plugin',
                    'text' => __('I found a better plugin', 'product-import-export-for-woo'),
                    'type' => 'text',
                    'placeholder' => __('Which plugin?', 'product-import-export-for-woo')
                ),
                array(
                    'id' => 'not-have-that-feature',
                    'text' => __('The plugin is great, but I need specific feature that you don\'t support', 'product-import-export-for-woo'),
                    'type' => 'textarea',
                    'placeholder' => __('Could you tell us more about that feature?', 'product-import-export-for-woo')
                ),
                array(
                    'id' => 'is-not-working',
                    'text' => __('The plugin is not working', 'product-import-export-for-woo'),
                    'type' => 'textarea',
                    'placeholder' => __('Could you tell us a bit more whats not working?', 'product-import-export-for-woo')
                ),
                array(
                    'id' => 'looking-for-other',
                    'text' => __('It\'s not what I was looking for', 'product-import-export-for-woo'),
                    'type' => 'textarea',
                    'placeholder' => __('Could you tell us a bit more?', 'product-import-export-for-woo')
                ),
                array(
                    'id' => 'did-not-work-as-expected',
                    'text' => __('The plugin didn\'t work as expected', 'product-import-export-for-woo'),
                    'type' => 'textarea',
                    'placeholder' => __('What did you expect?', 'product-import-export-for-woo')
                ),
                array(
                    'id' => 'other',
                    'text' => __('Other', 'product-import-export-for-woo'),
                    'type' => 'textarea',
                    'placeholder' => __('Could you tell us a bit more?', 'product-import-export-for-woo')
                ),
            );

            return $reasons;
        }

    }

    new WT_ProdImpExp_Uninstall_Feedback();

endif;

