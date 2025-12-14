<?php
/**
 * Plugin Name: MWF Reviews
 * Plugin URI: https://middleworldfarms.org
 * Description: Lightweight Google reviews display with Facebook Page Plugin embed - no API keys required for Facebook!
 * Version: 1.0.0
 * Author: Middleworld Farms
 * Author URI: https://middleworldfarms.org
 * License: GPL2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class MWF_Reviews {
    
    private $cache_duration = 86400; // 24 hours
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_shortcode('mwf_reviews', array($this, 'reviews_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_ajax_mwf_refresh_reviews', array($this, 'ajax_refresh_reviews'));
        add_action('mwf_daily_review_refresh', array($this, 'refresh_all_reviews'));
        
        // Schedule daily refresh
        if (!wp_next_scheduled('mwf_daily_review_refresh')) {
            wp_schedule_event(time(), 'daily', 'mwf_daily_review_refresh');
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'MWF Reviews',
            'Reviews',
            'manage_options',
            'mwf-reviews',
            array($this, 'settings_page'),
            'dashicons-star-filled',
            26
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('mwf_reviews_settings', 'mwf_google_place_id');
        register_setting('mwf_reviews_settings', 'mwf_google_api_key');
        register_setting('mwf_reviews_settings', 'mwf_facebook_page_url');
        register_setting('mwf_reviews_settings', 'mwf_facebook_width');
        register_setting('mwf_reviews_settings', 'mwf_facebook_height');
        register_setting('mwf_reviews_settings', 'mwf_facebook_small_header');
        register_setting('mwf_reviews_settings', 'mwf_facebook_adapt_container');
        register_setting('mwf_reviews_settings', 'mwf_facebook_hide_cover');
        register_setting('mwf_reviews_settings', 'mwf_facebook_show_facepile');
        register_setting('mwf_reviews_settings', 'mwf_reviews_to_show');
        register_setting('mwf_reviews_settings', 'mwf_reviews_enable_carousel');
        register_setting('mwf_reviews_settings', 'mwf_reviews_autoplay');
        register_setting('mwf_reviews_settings', 'mwf_reviews_autoplay_speed');
        register_setting('mwf_reviews_settings', 'mwf_reviews_min_rating');
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>MWF Reviews Settings</h1>
            
            <div class="notice notice-info">
                <p><strong>How to get your credentials:</strong></p>
                <ul>
                    <li><strong>Google:</strong> Get Place ID from <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">Google Place ID Finder</a></li>
                    <li><strong>Google API Key:</strong> Create at <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a> (enable Places API)</li>
                    <li><strong>Facebook:</strong> Just enter your Facebook page URL (e.g., https://www.facebook.com/yourpage). No API setup needed!</li>
                </ul>
            </div>
            
            <form method="post" action="options.php">
                <?php settings_fields('mwf_reviews_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th colspan="2"><h2>Google Reviews</h2></th>
                    </tr>
                    <tr>
                        <th scope="row">Google Place ID</th>
                        <td>
                            <input type="text" name="mwf_google_place_id" value="<?php echo esc_attr(get_option('mwf_google_place_id')); ?>" class="regular-text">
                            <p class="description">Your Google Business Place ID</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Google API Key</th>
                        <td>
                            <input type="text" name="mwf_google_api_key" value="<?php echo esc_attr(get_option('mwf_google_api_key')); ?>" class="regular-text">
                            <p class="description">Google Places API key (enable Places API in Google Cloud Console)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2>Facebook Page Plugin</h2></th>
                    </tr>
                    <tr>
                        <th scope="row">Facebook Page URL</th>
                        <td>
                            <input type="url" name="mwf_facebook_page_url" value="<?php echo esc_attr(get_option('mwf_facebook_page_url')); ?>" class="regular-text" placeholder="https://www.facebook.com/yourpage">
                            <p class="description">Your Facebook page URL. The plugin will embed your page's content including reviews.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Embed Width</th>
                        <td>
                            <input type="number" name="mwf_facebook_width" value="<?php echo esc_attr(get_option('mwf_facebook_width', 500)); ?>" min="180" max="500" class="small-text">
                            <p class="description">Width of the Facebook embed in pixels (180-500)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Embed Height</th>
                        <td>
                            <input type="number" name="mwf_facebook_height" value="<?php echo esc_attr(get_option('mwf_facebook_height', 600)); ?>" min="70" class="small-text">
                            <p class="description">Height of the Facebook embed in pixels (minimum 70)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Small Header</th>
                        <td>
                            <label>
                                <input type="checkbox" name="mwf_facebook_small_header" value="1" <?php checked(get_option('mwf_facebook_small_header', '0'), '1'); ?>>
                                Use smaller header in the embed
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Adapt to Container Width</th>
                        <td>
                            <label>
                                <input type="checkbox" name="mwf_facebook_adapt_container" value="1" <?php checked(get_option('mwf_facebook_adapt_container', '1'), '1'); ?>>
                                Automatically adapt width to container
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Hide Cover Photo</th>
                        <td>
                            <label>
                                <input type="checkbox" name="mwf_facebook_hide_cover" value="1" <?php checked(get_option('mwf_facebook_hide_cover', '0'), '1'); ?>>
                                Hide the page cover photo
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Show Friend's Faces</th>
                        <td>
                            <label>
                                <input type="checkbox" name="mwf_facebook_show_facepile" value="1" <?php checked(get_option('mwf_facebook_show_facepile', '1'), '1'); ?>>
                                Show profile pictures of friends who like the page
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2>Display Settings</h2></th>
                    </tr>
                    <tr>
                        <th scope="row">Reviews to Show</th>
                        <td>
                            <input type="number" name="mwf_reviews_to_show" value="<?php echo esc_attr(get_option('mwf_reviews_to_show', 6)); ?>" min="1" max="20" class="small-text">
                            <p class="description">Number of reviews to display</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Minimum Rating</th>
                        <td>
                            <select name="mwf_reviews_min_rating">
                                <?php
                                $min_rating = get_option('mwf_reviews_min_rating', 4);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo '<option value="' . $i . '"' . selected($min_rating, $i, false) . '>' . $i . ' Stars</option>';
                                }
                                ?>
                            </select>
                            <p class="description">Only show reviews with this rating or higher</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable Carousel</th>
                        <td>
                            <label>
                                <input type="checkbox" name="mwf_reviews_enable_carousel" value="1" <?php checked(get_option('mwf_reviews_enable_carousel', '1'), '1'); ?>>
                                Display reviews in a carousel slider
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Autoplay Carousel</th>
                        <td>
                            <label>
                                <input type="checkbox" name="mwf_reviews_autoplay" value="1" <?php checked(get_option('mwf_reviews_autoplay', '1'), '1'); ?>>
                                Automatically slide through reviews
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Autoplay Speed</th>
                        <td>
                            <input type="number" name="mwf_reviews_autoplay_speed" value="<?php echo esc_attr(get_option('mwf_reviews_autoplay_speed', 5000)); ?>" min="1000" max="10000" step="1000" class="small-text">
                            <p class="description">Milliseconds between slides (1000 = 1 second)</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2>Manual Refresh</h2>
            <p>Reviews are automatically refreshed daily. Click below to refresh now:</p>
            <button type="button" class="button button-secondary" id="mwf-refresh-reviews">Refresh Reviews Now</button>
            <span id="mwf-refresh-status"></span>
            
            <script>
            jQuery(document).ready(function($) {
                $('#mwf-refresh-reviews').on('click', function() {
                    var $btn = $(this);
                    var $status = $('#mwf-refresh-status');
                    
                    $btn.prop('disabled', true).text('Refreshing...');
                    $status.text('');
                    
                    $.post(ajaxurl, {
                        action: 'mwf_refresh_reviews'
                    }, function(response) {
                        $btn.prop('disabled', false).text('Refresh Reviews Now');
                        if (response.success) {
                            $status.html('<span style="color: green;">✓ ' + response.data.message + '</span>');
                        } else {
                            $status.html('<span style="color: red;">✗ ' + response.data.message + '</span>');
                        }
                    });
                });
            });
            </script>
            
            <hr>
            
            <h2>Usage</h2>
            <p>Use the shortcode in any page or widget:</p>
            <code>[mwf_reviews source="all"]</code> - Show Google reviews + Facebook page embed<br>
            <code>[mwf_reviews source="google"]</code> - Show only Google reviews<br>
            <code>[mwf_reviews source="facebook"]</code> - Show only Facebook page embed<br>
            <code>[mwf_reviews limit="3"]</code> - Show only 3 Google reviews<br>
            <code>[mwf_reviews carousel="no"]</code> - Show reviews in a grid instead of carousel
        </div>
        <?php
    }
    
    /**
     * AJAX refresh reviews
     */
    public function ajax_refresh_reviews() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $result = $this->refresh_all_reviews();
        
        if ($result) {
            wp_send_json_success(array('message' => 'Reviews refreshed successfully!'));
        } else {
            wp_send_json_error(array('message' => 'Failed to refresh reviews. Check your API credentials.'));
        }
    }
    
    /**
     * Fetch Google reviews
     */
    private function fetch_google_reviews() {
        $place_id = get_option('mwf_google_place_id');
        $api_key = get_option('mwf_google_api_key');
        
        if (empty($place_id) || empty($api_key)) {
            error_log('[MWF Reviews] Missing Google Place ID or API key');
            return array();
        }
        
        $url = sprintf(
            'https://maps.googleapis.com/maps/api/place/details/json?place_id=%s&fields=reviews,rating,user_ratings_total&key=%s',
            urlencode($place_id),
            urlencode($api_key)
        );
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            error_log('[MWF Reviews] Google API request failed: ' . $response->get_error_message());
            return array();
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['error_message'])) {
            error_log('[MWF Reviews] Google API error: ' . $data['error_message']);
            return array();
        }
        
        if (empty($data['result']['reviews'])) {
            error_log('[MWF Reviews] No reviews found for place ID: ' . $place_id);
            return array();
        }
        
        $reviews = array();
        foreach ($data['result']['reviews'] as $review) {
            $reviews[] = array(
                'source' => 'google',
                'author' => $review['author_name'],
                'rating' => $review['rating'],
                'text' => $review['text'],
                'time' => $review['time'],
                'profile_photo' => $review['profile_photo_url'] ?? '',
            );
        }
        
        error_log('[MWF Reviews] Successfully fetched ' . count($reviews) . ' Google reviews');
        return $reviews;
    }
    
    /**
     * Fetch Facebook reviews
     */
    private function fetch_facebook_reviews() {
        $page_id = get_option('mwf_facebook_page_id');
        $access_token = get_option('mwf_facebook_access_token');
        
        if (empty($page_id) || empty($access_token)) {
            return array();
        }
        
        $url = sprintf(
            'https://graph.facebook.com/v18.0/%s/ratings?fields=reviewer,rating,review_text,created_time&access_token=%s',
            urlencode($page_id),
            urlencode($access_token)
        );
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return array();
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($data['data'])) {
            return array();
        }
        
        $reviews = array();
        foreach ($data['data'] as $review) {
            $reviews[] = array(
                'source' => 'facebook',
                'author' => $review['reviewer']['name'] ?? 'Facebook User',
                'rating' => $review['rating'],
                'text' => $review['review_text'] ?? '',
                'time' => strtotime($review['created_time']),
                'profile_photo' => '',
            );
        }
        
        return $reviews;
    }
    
    /**
     * Refresh all reviews
     */
    public function refresh_all_reviews() {
        $google_reviews = $this->fetch_google_reviews();
        
        $all_reviews = $google_reviews;
        
        // Sort by time (newest first)
        usort($all_reviews, function($a, $b) {
            return $b['time'] - $a['time'];
        });
        
        update_option('mwf_cached_reviews', $all_reviews);
        update_option('mwf_reviews_last_update', time());
        
        return !empty($all_reviews);
    }
    
    /**
     * Get cached reviews
     */
    private function get_cached_reviews() {
        $reviews = get_option('mwf_cached_reviews', array());
        $last_update = get_option('mwf_reviews_last_update', 0);
        
        // Refresh if cache is old
        if (empty($reviews) || (time() - $last_update) > $this->cache_duration) {
            $this->refresh_all_reviews();
            $reviews = get_option('mwf_cached_reviews', array());
        }
        
        return $reviews;
    }
    
    /**
     * Enqueue styles
     */
    public function enqueue_styles() {
        if (has_shortcode(get_post()->post_content ?? '', 'mwf_reviews')) {
            // Slick carousel CSS
            wp_enqueue_style('slick-carousel', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', array(), '1.8.1');
            wp_enqueue_style('slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css', array('slick-carousel'), '1.8.1');
            
            // Plugin CSS
            wp_enqueue_style('mwf-reviews', plugin_dir_url(__FILE__) . 'assets/css/style.css', array('slick-theme'), '1.0.1');
            
            // Slick carousel JS
            wp_enqueue_script('slick-carousel-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
            
            // Plugin JS
            wp_enqueue_script('mwf-reviews-js', plugin_dir_url(__FILE__) . 'assets/js/carousel.js', array('jquery', 'slick-carousel-js'), '1.0.1', true);
            
            // Facebook SDK if Facebook is configured
            if (!empty(get_option('mwf_facebook_page_url'))) {
                wp_enqueue_script('facebook-sdk', 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v18.0', array(), '18.0', false);
            }
            
            // Pass settings to JS
            wp_localize_script('mwf-reviews-js', 'mwfReviewsSettings', array(
                'enableCarousel' => get_option('mwf_reviews_enable_carousel', '1') === '1',
                'autoplay' => get_option('mwf_reviews_autoplay', '1') === '1',
                'autoplaySpeed' => intval(get_option('mwf_reviews_autoplay_speed', 5000)),
            ));
        }
    }
    
    /**
     * Reviews shortcode
     * Usage: [mwf_reviews source="all" limit="6" carousel="yes"]
     */
    public function reviews_shortcode($atts) {
        $atts = shortcode_atts(array(
            'source' => 'all', // all, google, facebook
            'limit' => get_option('mwf_reviews_to_show', 6),
            'carousel' => get_option('mwf_reviews_enable_carousel', '1') === '1' ? 'yes' : 'no',
        ), $atts);
        
        $output = '';
        
        // Handle Google reviews
        if ($atts['source'] === 'all' || $atts['source'] === 'google') {
            $all_reviews = $this->get_cached_reviews();
            
            if (!empty($all_reviews)) {
                // Filter by minimum rating
                $min_rating = intval(get_option('mwf_reviews_min_rating', 4));
                $all_reviews = array_filter($all_reviews, function($review) use ($min_rating) {
                    return $review['rating'] >= $min_rating;
                });
                
                // Limit results
                $reviews = array_slice($all_reviews, 0, intval($atts['limit']));
                
                $carousel_class = ($atts['carousel'] === 'yes') ? 'mwf-reviews-carousel' : 'mwf-reviews-grid';
                
                ob_start();
                ?>
                <div class="mwf-reviews-container">
                    <div class="<?php echo esc_attr($carousel_class); ?>" data-carousel="<?php echo esc_attr($atts['carousel']); ?>">
                        <?php foreach ($reviews as $review): ?>
                            <div class="mwf-review-card" data-source="<?php echo esc_attr($review['source']); ?>">
                                <div class="mwf-review-header">
                                    <?php if (!empty($review['profile_photo'])): ?>
                                        <img src="<?php echo esc_url($review['profile_photo']); ?>" alt="<?php echo esc_attr($review['author']); ?>" class="mwf-review-avatar">
                                    <?php else: ?>
                                        <div class="mwf-review-avatar-placeholder"><?php echo strtoupper(substr($review['author'], 0, 1)); ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="mwf-review-meta">
                                        <h4 class="mwf-review-author"><?php echo esc_html($review['author']); ?></h4>
                                        <div class="mwf-review-rating">
                                            <?php echo $this->render_stars($review['rating']); ?>
                                        </div>
                                        <span class="mwf-review-date"><?php echo human_time_diff($review['time'], time()) . ' ago'; ?></span>
                                    </div>
                                    
                                    <span class="mwf-review-source mwf-source-<?php echo esc_attr($review['source']); ?>">
                                        <?php echo ucfirst($review['source']); ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($review['text'])): ?>
                                    <div class="mwf-review-text">
                                        <?php echo esc_html($review['text']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
                $output .= ob_get_clean();
            } elseif ($atts['source'] === 'google') {
                $output .= '<p class="mwf-reviews-empty">No Google reviews available yet. Check back soon!</p>';
            }
        }
        
        // Handle Facebook embed
        if (($atts['source'] === 'all' || $atts['source'] === 'facebook') && !empty(get_option('mwf_facebook_page_url'))) {
            $fb_url = esc_url(get_option('mwf_facebook_page_url'));
            $fb_width = intval(get_option('mwf_facebook_width', 500));
            $fb_height = intval(get_option('mwf_facebook_height', 600));
            $fb_small_header = get_option('mwf_facebook_small_header', '0') === '1' ? 'true' : 'false';
            $fb_adapt = get_option('mwf_facebook_adapt_container', '1') === '1' ? 'true' : 'false';
            $fb_hide_cover = get_option('mwf_facebook_hide_cover', '0') === '1' ? 'true' : 'false';
            $fb_show_facepile = get_option('mwf_facebook_show_facepile', '1') === '1' ? 'true' : 'false';
            
            $output .= '<div class="mwf-facebook-embed" style="margin-top: 20px;">';
            $output .= '<div id="fb-root"></div>';
            $output .= '<div class="fb-page" ';
            $output .= 'data-href="' . $fb_url . '" ';
            $output .= 'data-width="' . $fb_width . '" ';
            $output .= 'data-height="' . $fb_height . '" ';
            $output .= 'data-small-header="' . $fb_small_header . '" ';
            $output .= 'data-adapt-container-width="' . $fb_adapt . '" ';
            $output .= 'data-hide-cover="' . $fb_hide_cover . '" ';
            $output .= 'data-show-facepile="' . $fb_show_facepile . '" ';
            $output .= 'data-tabs="timeline">';
            $output .= '</div>';
            $output .= '</div>';
        } elseif ($atts['source'] === 'facebook') {
            $output .= '<p class="mwf-reviews-empty">Facebook page URL not configured. Please set it in the Reviews settings.</p>';
        }
        
        return $output;
    }
    
    /**
     * Render star rating
     */
    private function render_stars($rating) {
        $output = '';
        $full_stars = floor($rating);
        $half_star = ($rating - $full_stars) >= 0.5;
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $full_stars) {
                $output .= '<span class="mwf-star mwf-star-full">★</span>';
            } elseif ($i == $full_stars + 1 && $half_star) {
                $output .= '<span class="mwf-star mwf-star-half">★</span>';
            } else {
                $output .= '<span class="mwf-star mwf-star-empty">☆</span>';
            }
        }
        
        return $output;
    }
}

// Initialize plugin
new MWF_Reviews();

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('mwf_daily_review_refresh');
});
