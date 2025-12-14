<?php
/**
 * Plugin Name: MWF Team Members
 * Plugin URI: https://middleworldfarms.org
 * Description: Simple, lightweight team member management with shortcode support
 * Version: 1.0.0
 * Author: Middleworld Farms
 * Author URI: https://middleworldfarms.org
 * License: GPL2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class MWF_Team_Members {
    
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_team_member', array($this, 'save_meta_boxes'));
        add_shortcode('mwf_team', array($this, 'team_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }
    
    /**
     * Register Team Member post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => 'Team Members',
            'singular_name'      => 'Team Member',
            'menu_name'          => 'Team Members',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Team Member',
            'edit_item'          => 'Edit Team Member',
            'new_item'           => 'New Team Member',
            'view_item'          => 'View Team Member',
            'search_items'       => 'Search Team Members',
            'not_found'          => 'No team members found',
            'not_found_in_trash' => 'No team members found in trash',
        );
        
        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-groups',
            'menu_position'       => 25,
            'supports'            => array('title', 'editor', 'thumbnail'),
            'has_archive'         => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
        );
        
        register_post_type('team_member', $args);
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'team_member_details',
            'Team Member Details',
            array($this, 'render_meta_box'),
            'team_member',
            'normal',
            'high'
        );
    }
    
    /**
     * Render meta box
     */
    public function render_meta_box($post) {
        wp_nonce_field('mwf_team_meta_box', 'mwf_team_meta_box_nonce');
        
        $role = get_post_meta($post->ID, '_team_role', true);
        $email = get_post_meta($post->ID, '_team_email', true);
        $phone = get_post_meta($post->ID, '_team_phone', true);
        $order = get_post_meta($post->ID, '_team_order', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="team_role">Role/Position</label></th>
                <td><input type="text" id="team_role" name="team_role" value="<?php echo esc_attr($role); ?>" class="regular-text" placeholder="e.g., Farm Manager, Head Grower"></td>
            </tr>
            <tr>
                <th><label for="team_email">Email</label></th>
                <td><input type="email" id="team_email" name="team_email" value="<?php echo esc_attr($email); ?>" class="regular-text" placeholder="email@middleworldfarms.org"></td>
            </tr>
            <tr>
                <th><label for="team_phone">Phone</label></th>
                <td><input type="text" id="team_phone" name="team_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" placeholder="Optional"></td>
            </tr>
            <tr>
                <th><label for="team_order">Display Order</label></th>
                <td><input type="number" id="team_order" name="team_order" value="<?php echo esc_attr($order ?: '0'); ?>" class="small-text" min="0" step="1"> <span class="description">Lower numbers appear first</span></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function save_meta_boxes($post_id) {
        // Check nonce
        if (!isset($_POST['mwf_team_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['mwf_team_meta_box_nonce'], 'mwf_team_meta_box')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save fields
        if (isset($_POST['team_role'])) {
            update_post_meta($post_id, '_team_role', sanitize_text_field($_POST['team_role']));
        }
        if (isset($_POST['team_email'])) {
            update_post_meta($post_id, '_team_email', sanitize_email($_POST['team_email']));
        }
        if (isset($_POST['team_phone'])) {
            update_post_meta($post_id, '_team_phone', sanitize_text_field($_POST['team_phone']));
        }
        if (isset($_POST['team_order'])) {
            update_post_meta($post_id, '_team_order', intval($_POST['team_order']));
        }
    }
    
    /**
     * Enqueue frontend styles
     */
    public function enqueue_styles() {
        if (has_shortcode(get_post()->post_content ?? '', 'mwf_team')) {
            wp_enqueue_style('mwf-team-members', plugin_dir_url(__FILE__) . 'css/style.css', array(), '1.0.0');
        }
    }
    
    /**
     * Team shortcode
     * Usage: [mwf_team columns="3" show_email="yes" show_phone="no"]
     */
    public function team_shortcode($atts) {
        $atts = shortcode_atts(array(
            'columns' => '3',
            'show_email' => 'yes',
            'show_phone' => 'no',
            'show_bio' => 'yes',
        ), $atts);
        
        $args = array(
            'post_type'      => 'team_member',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value_num',
            'meta_key'       => '_team_order',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        );
        
        $team_query = new WP_Query($args);
        
        if (!$team_query->have_posts()) {
            return '<p>No team members found.</p>';
        }
        
        $columns = intval($atts['columns']);
        $output = '<div class="mwf-team-grid mwf-team-cols-' . $columns . '">';
        
        while ($team_query->have_posts()) {
            $team_query->the_post();
            
            $role = get_post_meta(get_the_ID(), '_team_role', true);
            $email = get_post_meta(get_the_ID(), '_team_email', true);
            $phone = get_post_meta(get_the_ID(), '_team_phone', true);
            
            $output .= '<div class="mwf-team-member">';
            
            // Photo
            if (has_post_thumbnail()) {
                $output .= '<div class="mwf-team-photo">';
                $output .= get_the_post_thumbnail(get_the_ID(), 'medium', array('class' => 'mwf-team-image'));
                $output .= '</div>';
            }
            
            $output .= '<div class="mwf-team-info">';
            
            // Name
            $output .= '<h3 class="mwf-team-name">' . get_the_title() . '</h3>';
            
            // Role
            if ($role) {
                $output .= '<p class="mwf-team-role">' . esc_html($role) . '</p>';
            }
            
            // Bio
            if ($atts['show_bio'] === 'yes' && get_the_content()) {
                $output .= '<div class="mwf-team-bio">' . wpautop(get_the_content()) . '</div>';
            }
            
            // Contact info
            if (($atts['show_email'] === 'yes' && $email) || ($atts['show_phone'] === 'yes' && $phone)) {
                $output .= '<div class="mwf-team-contact">';
                
                if ($atts['show_email'] === 'yes' && $email) {
                    $output .= '<a href="mailto:' . esc_attr($email) . '" class="mwf-team-email">' . esc_html($email) . '</a>';
                }
                
                if ($atts['show_phone'] === 'yes' && $phone) {
                    $output .= '<a href="tel:' . esc_attr($phone) . '" class="mwf-team-phone">' . esc_html($phone) . '</a>';
                }
                
                $output .= '</div>';
            }
            
            $output .= '</div>'; // .mwf-team-info
            $output .= '</div>'; // .mwf-team-member
        }
        
        $output .= '</div>'; // .mwf-team-grid
        
        wp_reset_postdata();
        
        return $output;
    }
}

// Initialize plugin
new MWF_Team_Members();
