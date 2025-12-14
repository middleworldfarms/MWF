=== Map for WooCommerce ===
Contributors: endisha
Tags: google, maps, woocommerce, checkout
Requires at least: 6.0
Requires PHP: 8.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrate Google Maps with WooCommerce for easy location selection during checkout and in user account addresses to elevate the shopping experience.

== Description ==

Google Maps for WooCommerce is a powerful plugin that seamlessly integrates Google Maps with WooCommerce, enhancing your customers' shopping experience. It enables customers to effortlessly select their delivery location during the checkout process and modify it conveniently from their account address settings. With Google Maps for WooCommerce, you have full control over where and how the map is displayed, ensuring a tailored and user-friendly shopping journey.

= Features =

* **Checkout Location Picker:** Google Maps for WooCommerce is the ultimate solution for WooCommerce stores that offer delivery or pickups. It provides customers with a user-friendly Google map interface right on the checkout page, making it easy to choose their preferred location.

* **Automatic Location Detection:** Optionally, the plugin can automatically detect the customer's location upon checkout page load. This feature streamlines the process and allows customers to make any necessary adjustments effortlessly.

* **Flexible Configuration:** Tailor the plugin to your specific needs with advanced configuration options. You can determine where the map appears and control its visibility, ensuring it seamlessly integrates into your store's design.

* **Multiple Map Styles:** Choose from a variety of map styles to match your store's aesthetics and branding. Google Maps for WooCommerce offers a range of options to make sure your map looks just the way you want it.

* **Custom Markers:** Personalize the map experience by adding custom markers. You can even set a custom marker image directly from the plugin settings.

* **Set as Required:** Decide whether customers must select a location or if it's optional. Google Maps for WooCommerce gives you the flexibility to adapt to your business requirements.

* **Support for Localization:** Google Maps for WooCommerce supports localization.


== External Library Usage ==

This plugin utilizes the Google Maps JavaScript API, to enable this feature, you need to obtain a Google Maps API key.

1. Visit the [Google Cloud Console](https://console.cloud.google.com/).
2. Create a new project (or select an existing one).
3. Enable the "Maps JavaScript API" for your project.
4. Create API credentials and copy your API key.

= Requirements =

* WordPress 5.7 or newer.
* WooCommerce 6.0 or newer.
* PHP version 8.0 or newer.
* Google Maps API Key.

== Installation ==

= Minimum Requirements =

* PHP 8.0 or greater is recommended.
* MySQL 5.6 or greater is recommended.

= Automatic installation =

Automatic installation is the easiest option -- WordPress will handles the file transfer, and you won’t need to leave your web browser. To do an automatic install of Google Maps for WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”
 
In the search field type “Google Maps for WooCommerce” then click “Search Plugins.” Once you’ve found us,  you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by! Click “Install Now,” and WordPress will take it from there.

= Manual installation =

The manual installation method requires downloading the Google Maps for WooCommerce plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).

== Frequently Asked Questions ==

= How do I enable and configure Google Maps for WooCommerce? =

Once the plugin is activated, go to WooCommerce > Settings > Google Maps for WooCommerce in your WordPress admin dashboard to configure its settings.


= How to Add a Custom Map Style =

You can easily add custom map styles to your Google Maps for WooCommerce  by following code:

`
<?php
add_filter('maps_woocommerce_map_styles', function ($styles) {
    $styles['custom-style'] = 'Custom Style';
    return $styles;
});

add_filter('maps_woocommerce_map_style_file', function ($file, $style) {
    if ($style == 'custom-style') {
        $file = __DIR__ . '/custom-style.json'; // Update the path to your JSON file
    }
    return $file;
}, 10, 2);
`

Make sure to update `__DIR__ . '/custom-style.json'` with the actual path to your JSON file and replace `'Custom Style'` with the desired name for your custom map style.

== Changelog ==
= 1.0.0 2024-02-26 =
* Initial release.

== Upgrade Notice ==
= 1.0.0 =
This is the initial release of the Google Maps for WooCommerce plugin.

== Screenshots ==
1. Show during the checkout page in billing or shipping sections based on the [Map Display Location] setting.
2. Show in the user account - address page under billing or shipping sections based on the [Map Display Location] setting.
3. Show in the order details page.
4. Show in the admin order details page. 
5. Show in the customer profile when editing the user in the admin. 
6. Plugin Settings

== External Services ==

Map for WooCommerce relies on a 3rd party service for geolocation functionality. Here's what you need to know:

- **Service:** Google Maps API
  - Learn more: [Google Maps API Documentation](https://developers.google.com/maps/documentation)
  - Terms of Service: [Google Maps API Terms of Service](https://developers.google.com/maps/terms)
  - Privacy Policy: [Google Privacy Policy](https://policies.google.com/privacy)

Please review the terms of service and privacy policy of the external service provider to understand how your data is handled. By using Map for WooCommerce, you agree to abide by the terms and conditions set forth by the 3rd party service.

== License ==
Google Maps for WooCommerce is licensed under the GNU General Public License v2 or later.
