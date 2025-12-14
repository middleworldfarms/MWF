<?php
/**
 * Admin Settings Page
 */

if (!defined('ABSPATH')) exit;

// Save settings
if (isset($_POST['mwf_solidarity_save'])) {
    check_admin_referer('mwf_solidarity_settings');
    
    // Update all settings
    $fields = [
        'mwf_solidarity_enabled',
        'mwf_solidarity_headline',
        'mwf_solidarity_subheadline',
        'mwf_solidarity_label',
        'mwf_solidarity_desc',
        'mwf_solidarity_price',
        'mwf_standard_label',
        'mwf_standard_desc',
        'mwf_standard_price',
        'mwf_supporter_label',
        'mwf_supporter_desc',
        'mwf_supporter_price',
        'mwf_solidarity_bg_image',
        'mwf_solidarity_learn_more',
        'mwf_solidarity_families_count',
        'mwf_solidarity_cookie_days',
        'mwf_solidarity_show_shop'
    ];
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_option($field, sanitize_text_field($_POST[$field]));
        }
    }
    
    echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
}

$settings = [
    'enabled' => get_option('mwf_solidarity_enabled', 'yes'),
    'headline' => get_option('mwf_solidarity_headline', 'Food Belongs to Everyone'),
    'subheadline' => get_option('mwf_solidarity_subheadline', 'Choose what you can afford. Everyone receives the same quality.'),
    'solidarity_label' => get_option('mwf_solidarity_label', 'Solidarity Price'),
    'solidarity_desc' => get_option('mwf_solidarity_desc', 'For those who need support'),
    'solidarity_price' => get_option('mwf_solidarity_price', '£10.50+'),
    'standard_label' => get_option('mwf_standard_label', 'Standard Price'),
    'standard_desc' => get_option('mwf_standard_desc', 'True cost of growing'),
    'standard_price' => get_option('mwf_standard_price', '£15'),
    'supporter_label' => get_option('mwf_supporter_label', 'Supporter Price'),
    'supporter_desc' => get_option('mwf_supporter_desc', 'Helps subsidize others'),
    'supporter_price' => get_option('mwf_supporter_price', '£18+'),
    'background_image' => get_option('mwf_solidarity_bg_image', ''),
    'learn_more_text' => get_option('mwf_solidarity_learn_more', "Growing food biologically—without chemicals and with deep care for soil life—takes time, craft, and labour.\n\nOur solidarity model:\n• Creates fair wages for farmers\n• Keeps the farm stable and community-owned\n• Ensures everyone can eat nutrient-rich produce\n• Builds a food system based on trust, not profit\n\nThis is not charity. It's shared responsibility and shared abundance."),
    'families_count' => get_option('mwf_solidarity_families_count', '23'),
    'cookie_days' => get_option('mwf_solidarity_cookie_days', '30'),
    'show_shop' => get_option('mwf_solidarity_show_shop', 'no')
];
?>

<div class="wrap">
    <h1>🥬 Solidarity Pricing Settings</h1>
    
    <p>Configure the pay-what-you-can solidarity pricing modal that appears when customers view vegetable box products.</p>
    
    <form method="post" action="">
        <?php wp_nonce_field('mwf_solidarity_settings'); ?>
        
        <table class="form-table">
            
            <!-- Enable/Disable -->
            <tr>
                <th scope="row">
                    <label for="mwf_solidarity_enabled">Enable Modal</label>
                </th>
                <td>
                    <select name="mwf_solidarity_enabled" id="mwf_solidarity_enabled">
                        <option value="yes" <?php selected($settings['enabled'], 'yes'); ?>>Yes</option>
                        <option value="no" <?php selected($settings['enabled'], 'no'); ?>>No</option>
                    </select>
                    <p class="description">Turn the solidarity pricing modal on or off</p>
                </td>
            </tr>
            
            <!-- Headline -->
            <tr>
                <th scope="row">
                    <label for="mwf_solidarity_headline">Main Headline</label>
                </th>
                <td>
                    <input type="text" 
                           name="mwf_solidarity_headline" 
                           id="mwf_solidarity_headline" 
                           value="<?php echo esc_attr($settings['headline']); ?>" 
                           class="regular-text" />
                    <p class="description">Main message at top of modal</p>
                </td>
            </tr>
            
            <!-- Subheadline -->
            <tr>
                <th scope="row">
                    <label for="mwf_solidarity_subheadline">Subheadline</label>
                </th>
                <td>
                    <textarea name="mwf_solidarity_subheadline" 
                              id="mwf_solidarity_subheadline" 
                              rows="2" 
                              class="large-text"><?php echo esc_textarea($settings['subheadline']); ?></textarea>
                    <p class="description">Supporting text below headline</p>
                </td>
            </tr>
            
        </table>
        
        <h2>💚 Price Tiers</h2>
        <table class="form-table">
            
            <!-- Solidarity Tier -->
            <tr>
                <th scope="row">Solidarity Price Tier</th>
                <td>
                    <p>
                        <label>Label: 
                            <input type="text" name="mwf_solidarity_label" value="<?php echo esc_attr($settings['solidarity_label']); ?>" class="regular-text" />
                        </label>
                    </p>
                    <p>
                        <label>Price Display: 
                            <input type="text" name="mwf_solidarity_price" value="<?php echo esc_attr($settings['solidarity_price']); ?>" class="regular-text" />
                        </label>
                    </p>
                    <p>
                        <label>Description: 
                            <input type="text" name="mwf_solidarity_desc" value="<?php echo esc_attr($settings['solidarity_desc']); ?>" class="regular-text" />
                        </label>
                    </p>
                </td>
            </tr>
            
            <!-- Standard Tier -->
            <tr>
                <th scope="row">Standard Price Tier</th>
                <td>
                    <p>
                        <label>Label: 
                            <input type="text" name="mwf_standard_label" value="<?php echo esc_attr($settings['standard_label']); ?>" class="regular-text" />
                        </label>
                    </p>
                    <p>
                        <label>Price Display: 
                            <input type="text" name="mwf_standard_price" value="<?php echo esc_attr($settings['standard_price']); ?>" class="regular-text" />
                        </label>
                    </p>
                    <p>
                        <label>Description: 
                            <input type="text" name="mwf_standard_desc" value="<?php echo esc_attr($settings['standard_desc']); ?>" class="regular-text" />
                        </label>
                    </p>
                </td>
            </tr>
            
            <!-- Supporter Tier -->
            <tr>
                <th scope="row">Supporter Price Tier</th>
                <td>
                    <p>
                        <label>Label: 
                            <input type="text" name="mwf_supporter_label" value="<?php echo esc_attr($settings['supporter_label']); ?>" class="regular-text" />
                        </label>
                    </p>
                    <p>
                        <label>Price Display: 
                            <input type="text" name="mwf_supporter_price" value="<?php echo esc_attr($settings['supporter_price']); ?>" class="regular-text" />
                        </label>
                    </p>
                    <p>
                        <label>Description: 
                            <input type="text" name="mwf_supporter_desc" value="<?php echo esc_attr($settings['supporter_desc']); ?>" class="regular-text" />
                        </label>
                    </p>
                </td>
            </tr>
            
        </table>
        
        <h2>🖼️ Visual & Content</h2>
        <table class="form-table">
            
            <!-- Background Image -->
            <tr>
                <th scope="row">
                    <label for="mwf_solidarity_bg_image">Background Image URL</label>
                </th>
                <td>
                    <input type="url" 
                           name="mwf_solidarity_bg_image" 
                           id="mwf_solidarity_bg_image" 
                           value="<?php echo esc_url($settings['background_image']); ?>" 
                           class="large-text" 
                           placeholder="https://yourdomain.com/wp-content/uploads/farmer.jpg" />
                    <p class="description">Upload an image via Media Library and paste URL here. Shows farmers/community.</p>
                </td>
            </tr>
            
            <!-- Families Count -->
            <tr>
                <th scope="row">
                    <label for="mwf_solidarity_families_count">Families Supported Count</label>
                </th>
                <td>
                    <input type="number" 
                           name="mwf_solidarity_families_count" 
                           id="mwf_solidarity_families_count" 
                           value="<?php echo esc_attr($settings['families_count']); ?>" 
                           class="small-text" />
                    <p class="description">Number shown in "X families supported this month"</p>
                </td>
            </tr>
            
            <!-- Learn More Text -->
            <tr>
                <th scope="row">
                    <label for="mwf_solidarity_learn_more">"Learn More" Content</label>
                </th>
                <td>
                    <textarea name="mwf_solidarity_learn_more" 
                              id="mwf_solidarity_learn_more" 
                              rows="10" 
                              class="large-text"><?php echo esc_textarea($settings['learn_more_text']); ?></textarea>
                    <p class="description">Text shown when user clicks "Learn More". Use • for bullet points, double line breaks for paragraphs.</p>
                </td>
            </tr>
            
        </table>
        
        <h2>⚙️ Behavior Settings</h2>
        <table class="form-table">
            
            <!-- Cookie Days -->
            <tr>
                <th scope="row">
                    <label for="mwf_solidarity_cookie_days">Cookie Duration (Days)</label>
                </th>
                <td>
                    <input type="number" 
                           name="mwf_solidarity_cookie_days" 
                           id="mwf_solidarity_cookie_days" 
                           value="<?php echo esc_attr($settings['cookie_days']); ?>" 
                           class="small-text" />
                    <p class="description">How long before showing modal again (if not permanently dismissed)</p>
                </td>
            </tr>
            
            <!-- Show on Shop Page -->
            <tr>
                <th scope="row">
                    <label for="mwf_solidarity_show_shop">Trigger on Shop Page Clicks</label>
                </th>
                <td>
                    <select name="mwf_solidarity_show_shop" id="mwf_solidarity_show_shop">
                        <option value="yes" <?php selected($settings['show_shop'], 'yes'); ?>>Yes</option>
                        <option value="no" <?php selected($settings['show_shop'], 'no'); ?>>No</option>
                    </select>
                    <p class="description">Show modal when clicking vegbox products from shop page (not just on product page load)</p>
                </td>
            </tr>
            
        </table>
        
        <p class="submit">
            <input type="submit" name="mwf_solidarity_save" class="button button-primary" value="Save Settings" />
        </p>
        
    </form>
    
    <hr>
    
    <h2>📊 Preview</h2>
    <p>
        <a href="<?php echo home_url('/shop/'); ?>" target="_blank" class="button">View Shop Page</a>
        <button type="button" class="button" onclick="document.cookie='mwf_solidarity_seen=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'; alert('Cookie cleared! Visit a vegbox product to see modal.');">Clear Test Cookie</button>
    </p>
    
</div>

<style>
.form-table th {
    width: 200px;
}
.form-table input[type="text"],
.form-table input[type="url"],
.form-table textarea {
    width: 100%;
    max-width: 600px;
}
</style>
