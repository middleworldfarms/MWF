=== Qyrr - simply and modern QR-Code creation ===
Contributors: patrickposner
Tags: qr code, qr, qr-code, qr code generator, qr code tracking
Requires at least: 6.5
Tested up to: 6.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 2.0.7

Create, manage and track fully customizable QR Codes without any Third-Party-APIs.

== Description ==

Create, manage and track QR Codes in WordPress with Qyrr.

Use the power of the QR Code editor to create a fully customizable QR Code without any Third-Party-APIs.

== Features ==

**Sources**

Create a QR Code for all your posts and pages or use an external URL.
We also support various other sources (texts, WhatsApp and SMS messages, E-Mails, Phone numbers, vCards) within Qyrr Pro.

**Embed QR Codes**

Easily embed QR codes into your website. Use the custom block or the integrated shortcode.

Forget about copying IDs into clunky shortcodes, we handle that automatically for you.
The only thing you have to do is copy and paste the QR Code or select the code within the block.

**Style your QR Code**

Qyrr offers an entire toolkit for styling your QR Code.

Adjust the size, background color, fill color, the minimum readable version, the quiet zone, add rounded corners and improve the error handling level for better results.

**Add your logo**

Qyrr enables you to add your own logo inside of the QR Code. Want to use some custom text instead? Qyrr can handle that too.

Adjust the size and position of the logo and use Google Fonts to match text on your QR Code with your website design.

**Download QR Codes**

You can easily download your QR code from the QR Code editor. Choose a format (PNG within the free version, SVG and PNG within Qyrr Pro).

**Manage QR Code campains**

Easily manage your QR Codes with campaigns.

Having an event coming up and you want to quickly see all QR Codes related to it? Create a campaign and assign your QR Codes to it.

Once done you can easily filter all your QR Codes for a specific campaign.

== Qyrr Pro ==

Qyrr Pro extends the feature set of the free version with some powerful features.

[youtube https://www.youtube.com/watch?v=zA72LmyITjc]

Get it now on [patrickposner.com/qyrr/](https://patrickposner.com/qyrr/)

**Sources**

In addition to external URLs and posts/pages Qyrr Pro offers:

- Texts
- WhatsApp
- SMS
- E-Mails
- Phone numbers
- vCards
- Support for custom post types (like products in WooCommerce)

**Dynamic QR Codes**

With Qyrr Pro you can create dynamic QR Codes.

A dynamic QR Code never needs to be re-printed. You can easily change the target of the QR Code without changing the QR Code itself.

**Difference between static QR Codes and dynamic QR Codes**

Static QR Code

- the source cannot be modified
- you can't track the usage of a static QR Code

Dynamic QR Code

- the embedded information can be changed anytime+
- you can easily track the QR code usage in WordPress (without external tools)

**Bulk Generate QR Codes**

Qyrr Pro offers a powerful bulk generator for QR Codes.

Create a basic QR Code that you use as a template and bulk generated hundreds or thousands of QR Codes based on that template.

We offer an CSV-based import where you can simply copy and paste a list of URLs that we use to generate the individual QR Codes.

We also offer a dynamic ID solution where you link to the exact same page/URL, but because of the ID you can track each QR Code individually.

**Track QR Code usage**

Easily track the usage of your QR Code within Qyrr Pro.

Each time a dynamic QR Code is scanned, we increase the usage value in the QR Codes overview accordingly.

Combined with the integration campaigns, you can immediately evaluate the effectiveness of your QR Code campaign.

**Export as SVG**

Qyrr Pro offers the ability to export your QR Codes as an SVG file (vector file).

It's often required to provide an vector file if you want to print your QR code professionally, because of that Qyrr Pro offers an unlimited scalable vector file as an export.

== QUICK COMPARISON (FREE VS. PRO) ==

**Free**

- Use external URLs or pages/posts as source
- customize background color, fill color, size and more
- Add your logo or custom text to the QR code
- embed the QR Code via shortcode or block

**Pro**

Everything from the free version plus:

- use text, e-mail, phone number, SMS, WhatsApp, Geolocation, Wifi and vCards and custom post types as a source
- export QR Codes as SVG / vector file
- create a dynamic QR Code
- bulk generate QR Codes
- track QR code usage
- auto-generate QR Codes for all kinds of custom post types
- global template for QR Codes and auto-apply it to new QR codes

Get it now on [patrickposner.com/qyrr/](https://patrickposner.com/qyrr/)

== Support ==

The free support is exclusively limited to the wordpress.org support forum.

=== CODING STANDARDS MADE IN GERMANY ===

Qyrr is coded with modern PHP and WordPress standards in mind. It’s fully OOP coded. It’s highly extendable for developers through several action and filter hooks.

Qyrr has your website performance in mind -  every script and style is minified and loaded conditionally.


=== MULTI-LANGUAGE ===

Qyrr is completly translatable with WPML and Polylang.
Simply use the language switcher and translate all settings.

== Installation ==

= Default Method =
1. Go to Settings > Plugins in your administrator panel.
1. Click `Add New`
1. Search for Qr
1. Click install.

= Easy Method =
1. Download the zip file.
1. Login to your `Dashboard`
1. Open your plugins bar and click `Add New`
1. Click the `upload tab`
1. Choose `qyrr` from your downloads folder
1. Click `Install Now`
1. All done, now just activate the plugin
1. Go to QR-Codes and create one.

= Old Method =
1. Upload `qyrr` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress


== Screenshots ==

1. QR Code creation
1. QR Code dashboard


== Changelog ==

= 2.0.7 =
* SDK upgrade

= 2.0.6 =

* fixed QR Code preview when editing
* updated dependencies to latest stable release

= 2.0.5 =

* upgrade dependencies
* WP 6.7 compatibility

= 2.0.4 =

* WordPress 6.6 compatibility
* latest dependencies
* bugfix for source when using dynamic QR codes

= 2.0.3 =

* update dependencies (NPM)
* some smaller security improvements
* checks + tested with WP 6.5

= 2.0.2 =

* improved checks for supported_post_types values
* additional check for metabox to only add on single edit screens

= 2.0.1 =

* additional check for array on supported post types

= 2.0 =

* auto-generate QR Codes (pro-only)
* global template for QR Codes (pro-only)
* improved CPT selection
* improved settings sanitization
* added WooCommerce support (pro-only)
* added filter for file name
* simplified admin settings
* fixed meta field assignments for text and email

= 1.5.6 =

* WordPress 6.4 compatibility
* fixed QR code selector block (uploads URL)

= 1.5.5 =

* cache-busting shortcode
* simplified previews
* server-side rendering for QR selector block
* fixed QR Code preview with cache busting URLs

= 1.5.4 =

* fixed QR Code selector without inline group argument (deprecated)
* better cache busting based on URL parameter instead of file name
* Fixed copy shortcode without <code> markup
* fixed Google API key check for empty keys
* fixed QR Code preview with cache busting URLs

= 1.5.3 =

* WP 6.3 compatibility

= 1.5.2 =

* fixed typos in readme
* fixed typos in language files
* adjusted Freemius integration

= 1.5.1 =

* bugfix dynamic QR codes with CPT selector

= 1.5 =

* admin UI improvements
* better defaults for min level and error handling
* pre-open source settings for better UX
* seperated shortcode settings area for easier access
* CPT support for source selection (pro-only)

= 1.4.4 =

* static admin sidebar for settings
* fixed logo sizing and file
* Freemius SDK update to 2.5.10

= 1.4.3 =

* fixed rerender if using dynamic QR Codes
* updated dependencies

= 1.4.2 =

* added a hint where to edit the QR Code (by clicking on the code)

= 1.4.1 =

* fixed preview generation with cache busting
* removed cache busting from original QR Codes

= 1.4 =

* fixed missing meta field for external URL
* transparent background/fill color as option
* npm packages updated

= 1.3 =

* cache-compatible QR Codes in selector block
* improved QR codes with logos by auto-setting better defaults
* fixed block icon svg files
* fixed QR code source if page/post but not dynamic QR Code is activated

= 1.2 =

* improved QR Code preview with cache busting
* improved block.json icons


= 1.1 =

* force Gutenberg for editing CPT "qr"

= 1.0 =

* reworked with ReactJS
* Block Editor for QR Code creation with live preview/generation
* React for admin settings (import/export/reset settings)
* Google Fonts API integration with key (required now)
* Bulk generation (pro-only)
* SVG generation and download (pro-only)
* basic tracking implementation (pro-only)
* dynamic QR-Codes (pro-only)

= 0.8 =

* removed freemius
* default size for QR code
* WP 5.9 compatibility

= 0.7 =

* improved security
* removed placeholder for width
* implement correct noce checkup for ajax

= 0.6 =

* latest freemius sdk
* dependency updates for qr code
* admin color picker bugfix

= 0.5 =
* Initial release