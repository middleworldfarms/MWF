<?php
/**
 * The sidebar containing the main widget area
 *
 * @package WooCommerce/Templates
 * @version 3.7.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Don't display the default sidebar on pages with no widgets
$sidebar_display = false;

// Get the sidebar widgets
ob_start();
dynamic_sidebar('sidebar-woocommerce');
$sidebar_content = ob_get_clean();

// If sidebar has widgets (excluding search)
if ($sidebar_content && !empty(strip_tags($sidebar_content))) {
    $sidebar_display = true;
}

if ($sidebar_display) :
?>

<div id="secondary" class="widget-area" role="complementary">
    <?php
    // Manually output widgets - this avoids the search widget
    $sidebar_widgets = wp_get_sidebars_widgets();
    
    if (!empty($sidebar_widgets['sidebar-woocommerce'])) {
        foreach ($sidebar_widgets['sidebar-woocommerce'] as $widget_id) {
            // Skip if it's a product search widget
            if (strpos($widget_id, 'woocommerce_product_search') !== false) {
                continue;
            }
            
            // Output the widget
            the_widget(get_class($GLOBALS['wp_registered_widgets'][$widget_id]['callback'][0]), 
                get_option('widget_' . $GLOBALS['wp_registered_widgets'][$widget_id]['callback'][0]->id_base)[$GLOBALS['wp_registered_widgets'][$widget_id]['params'][0]['number']],
                array('before_widget' => '<aside id="' . $widget_id . '" class="widget">', 'after_widget' => '</aside>'));
        }
    }
    ?>
</div>

<?php endif; ?>