<?php

/**
 * Request a Feature
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('WT_IEW_Request_Feature')) {

    class WT_IEW_Request_Feature
    {

        public $module_base = 'request_feature';
        public $module_id = '';
        public static $module_id_static = '';
        public $module_version = '';
        private static $instance = null;

        private $end_point = 'https://feedback.webtoffee.com/wp-json/feature-suggestion/v1';

        public function __construct()
        {
            add_action('admin_enqueue_scripts', array($this, 'enqueue_styles_and_scripts'));
            add_action('wt_iew_plugin_settings_after_wrap', array($this, 'add_request_button'));
            add_action('admin_footer', array($this, 'add_feature_popup'));
            add_action('wp_ajax_wt_iew_request_a_feature', array($this, 'send_suggestion'));
        }

        public static function get_instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function send_suggestion()
        {
            $out = array(
                'status' => false,
                'msg' => __('Error', 'users-customers-import-export-for-wp-woocommerce'),
            );


            $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

            if ("" !== $nonce && wp_verify_nonce($nonce, WT_U_IEW_PLUGIN_NAME)) {
                $er_msg = '';
                $msg = isset($_POST['wt_iew_request_a_feature_msg']) ? sanitize_textarea_field(wp_unslash($_POST['wt_iew_request_a_feature_msg'])) : '';
                $take_email = isset($_POST['wt_iew_request_a_feature_take_email']) ? sanitize_text_field(wp_unslash($_POST['wt_iew_request_a_feature_take_email'])) : 'no';
                $email = isset($_POST['wt_iew_request_a_feature_email']) ? sanitize_email(wp_unslash($_POST['wt_iew_request_a_feature_email'])) : '';


                if ('' === $msg) {
                    $er_msg = esc_html__('Please enter your message.', 'users-customers-import-export-for-wp-woocommerce');
                }

                if ('' === $er_msg && 'yes' === $take_email && '' === $email) {
                    $er_msg = esc_html__('We need your email address to contact you back.', 'users-customers-import-export-for-wp-woocommerce');
                }

                $plugin_name = isset($_POST['plugin_name']) ? sanitize_textarea_field(wp_unslash($_POST['plugin_name'])) : 'user_import';

                //no error
                if ('' === $er_msg) {

                    $data = array(
                        'msg' => $msg,
                        'user_email' => $email,
                        'plugin_version' => WT_U_IEW_VERSION,
                        'plugin' => $plugin_name,
                    );

                    $resp = wp_remote_retrieve_body(wp_remote_post(
                        $this->end_point,
                        array(
                            'method' => 'POST',
                            'timeout' => 45,
                            'redirection' => 5,
                            'httpversion' => '1.0',
                            'blocking' => false,
                            'body' => $data,
                            'cookies' => array(),
                        )
                    ));

                    if (!is_wp_error($resp)) {
                        $out['status'] = true;
                        $out['msg'] = __('Success', 'users-customers-import-export-for-wp-woocommerce');
                    }
                } else {
                    $out['msg'] = $er_msg;
                }
            }

            echo json_encode($out);
            exit();
        }

        public function enqueue_styles_and_scripts()
        {
            wp_enqueue_style($this->module_id . '-css', plugin_dir_url(__FILE__) . 'assets/css/request_feature.css', array(), $this->module_version, 'all');
            $params = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'enter_message' => esc_html__('Please enter a message', 'users-customers-import-export-for-wp-woocommerce'),
                'email_message' => esc_html__('We need your email address to contact you back.', 'users-customers-import-export-for-wp-woocommerce'),
                'sending' => esc_html__('Sending...', 'users-customers-import-export-for-wp-woocommerce'),
                'unable_to_submit' => esc_html__('Unable to submit. Please try again later.', 'users-customers-import-export-for-wp-woocommerce'),
                'success_msg' => esc_html__('Thank you for your valuable suggestion.', 'users-customers-import-export-for-wp-woocommerce'),
            );
            wp_enqueue_script($this->module_id . '-js', plugin_dir_url(__FILE__) . 'assets/js/request_feature.js', array('jquery'), $this->module_version, false);
            wp_localize_script($this->module_id . '-js', 'wt_iew_request_feature_js_params', $params);
        }


        /**
         * Add request a feature button and popup
         */
        public function add_feature_popup()
        {
?>
            <!-- Popup form -->
            <div id="wt_iew_request_a_feature_popup" class="wt_iew_request_a_feature_popup wt_iew_popup" style=" display: none;">
                <div class="wt_iew_popup_hd">
                    <div class="wt_iew_popup_title">
                        <?php esc_html_e('Missing a feature?', 'users-customers-import-export-for-wp-woocommerce'); ?>
                        <div class="wt_iew_popup_title_caption"><?php esc_html_e('Drop a message to let us know!', 'users-customers-import-export-for-wp-woocommerce'); ?></div>
                    </div>
                    <div class="wt_iew_popup_close" id="wt_iew_request_a_feature_close">X</div>
                </div>
                <div class="wt_iew_popup_body">
                    <form id="wt_iew_request_a_feature_form" method="post">

                        <?php wp_nonce_field(WT_U_IEW_PLUGIN_NAME); ?>
                        <input type="hidden" name="action" value="wt_iew_request_a_feature">
                        <input type="hidden" name='plugin_name' value="user_import">
                        <!-- Message field -->
                        <label class="form_label"><?php esc_html_e('What would you like to add as a new feature?', 'users-customers-import-export-for-wp-woocommerce'); ?></label>
                        <span class="form_label_caption"><?php esc_html_e('More the details you share, the better.', 'users-customers-import-export-for-wp-woocommerce'); ?></span>
                        <textarea name="wt_iew_request_a_feature_msg" placeholder="<?php esc_attr_e('I would like...', 'users-customers-import-export-for-wp-woocommerce'); ?>"></textarea>

                        <!-- Email option -->
                        <div class="wt_iew_request_a_feature_checkbox_container">
                            <input type="checkbox" name="wt_iew_request_a_feature_take_email" id="wt_iew_request_a_feature_take_email" value="yes">
                            <label for="wt_iew_request_a_feature_take_email"><?php esc_html_e('Webtoffee can contact me about this feedback.', 'users-customers-import-export-for-wp-woocommerce'); ?></label>
                        </div>

                        <!-- Email field -->
                        <div class="wt_iew_request_a_feature_email_container">
                            <label class="form_label"><?php esc_html_e('Enter your email address.', 'users-customers-import-export-for-wp-woocommerce'); ?></label>
                            <input type="email" name="wt_iew_request_a_feature_email" class="wt_iew_request_a_feature_input" placeholder="<?php esc_attr_e('Enter email address', 'users-customers-import-export-for-wp-woocommerce'); ?>">
                        </div>

                        <!-- Submit and Cancel buttons -->
                        <div class="wt_iew_request_a_feature_btn_box">
                            <button type="submit" class="button-primary" name="wt_iew_request_feature_sbmt_btn"><?php esc_html_e('Send feature request', 'users-customers-import-export-for-wp-woocommerce'); ?></button>
                            <button type="button" id="wt_iew_request_a_feature_cancel" class="button-secondary"><?php esc_html_e('Cancel', 'users-customers-import-export-for-wp-woocommerce'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
<?php

        }
    }

    WT_IEW_Request_Feature::get_instance();
}
