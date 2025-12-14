=== GPLVault Update Manager ===
Contributors: gplvault
Tags: updates, automatic updates, plugin updates, theme updates, update manager, gplvault, license management, premium plugins, wordpress updates, auto updater
Requires at least: 5.9
Tested up to: 6.8.2
Requires PHP: 7.4
Stable tag: 5.3.3
License: GPL v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Network: true

Keep your WordPress site in sync with all plugins and themes from GPLVault.com through automated updates and license management.

== Description ==

GPLVault Update Manager is a powerful WordPress plugin that seamlessly integrates your website with the GPLVault.com repository, providing automatic updates for premium WordPress plugins and themes. This plugin ensures your site stays current with the latest versions of GPLVault items while maintaining security and stability.

= Key Features =

* **Automatic Updates**: Receive automatic updates for all GPLVault plugins and themes
* **License Management**: Easy license activation and management through the WordPress admin
* **Native WordPress Integration**: Updates are delivered through WordPress's native update system
* **Selective Updates**: Exclude specific plugins or themes from automatic updates
* **Update Logs**: Comprehensive logging system for tracking update history and troubleshooting
* **Weekly Catalog Refresh**: Automatic weekly synchronization with the GPLVault catalog
* **Security First**: All updates are delivered securely with proper authentication

= How It Works =

1. Install and activate the GPLVault Update Manager plugin
2. Enter your GPLVault license key in the settings
3. The plugin automatically checks for updates and notifies you through WordPress
4. Update your plugins and themes directly from the WordPress admin interface

= Requirements =

* WordPress 5.9 or higher
* PHP 7.4 or higher
* Active GPLVault license
* SSL-enabled hosting (recommended)

== Installation ==

= From WordPress Admin =

1. Navigate to Plugins > Add New
2. Upload the `gplvault-updater.zip` file
3. Click "Install Now" and then "Activate"
4. Go to GPLVault > Settings to configure your license

= Manual Installation =

1. Upload the `gplvault-updater` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to GPLVault > Settings
4. Enter your GPLVault license key and click "Activate"

= For Multisite Networks =

1. Upload the plugin files to `/wp-content/plugins/`
2. Network activate the plugin from the Network Admin > Plugins page
3. Configure the license key from the Network Admin > GPLVault > Settings

== Frequently Asked Questions ==

= How do I get a GPLVault license key? =

You can purchase a license key from [GPLVault.com](https://www.gplvault.com). Different subscription plans are available based on your needs.

= Why is my license showing as inactive? =

Common reasons include:
* Incorrect license key entry
* Expired subscription
* License key used on too many sites
* Connection issues with GPLVault servers

Check your license status at GPLVault.com or contact support for assistance.

= Can I exclude certain plugins or themes from updates? =

Yes! Navigate to GPLVault > Disable Updates where you can select specific plugins or themes to exclude from automatic updates.

= How often does the plugin check for updates? =

The plugin checks for updates according to WordPress's standard update schedule. Additionally, a weekly full catalog refresh ensures all available items are synchronized.

= Where can I view update logs? =

Go to GPLVault > Logs to view detailed logs of all update activities, API communications, and system events.

= Is my license key secure? =

Yes, your license key is stored securely in the WordPress database and is masked in the admin interface for additional security.

= What happens if I deactivate my license? =

Deactivating your license will stop automatic updates from GPLVault. You can reactivate it at any time with a valid subscription.

== Screenshots ==

1. **Settings Page** - Material Design interface for license activation and management
2. **Plugin Exclusions** - Interactive interface with search and bulk selection for excluding plugins
3. **Theme Exclusions** - Modern card-based layout for managing theme update exclusions
4. **Logs Page** - Comprehensive logging interface with environment info and export options
5. **Update Integration** - GPLVault updates seamlessly integrated into WordPress updates page

== Changelog ==

For older versions, please see the full changelog included with the plugin.

= 5.3.3 - 2025-11-07 =
* Fixed: Resolved conflict with WooCommerce where screen options (column visibility) would not save
* Improved: Plugin scripts and styles now only load on GPLVault admin pages, improving performance
* Improved: Enhanced WordPress coding standards compliance

= 5.3.2 - 2025-08-22 =
* Updated: New custom SVG admin menu icon for better brand consistency
* Updated: Changed all references from "Master Key" to "User License Key" for clarity
* Improved: Consistent terminology throughout the interface

= 5.3.1 - 2025-07-24 =
* Fixed: Confirmation dialogs for "Deactivate License" and "Clear Local Settings" buttons now use native browser confirm dialogs
* Fixed: CSS rendering issue after AJAX license operations - styles now persist correctly
* Fixed: Progress bar and metric cards display properly after Check License, Activate, and Deactivate operations
* Fixed: WordPress 6.7+ translation warning by removing translation calls from cron schedule registration

= 5.3.0 - 2025-07-23 =
* Added: Complete Material Design-inspired UI refresh for all admin pages
* Added: Interactive search and filter functionality for plugin/theme exclusion lists
* Added: Bulk selection actions (Select All/Select None) for easier management
* Added: Real-time item counting and visual feedback
* Improved: Enhanced user experience with modern card-based layouts
* Improved: Better visual hierarchy and typography throughout the plugin
* Improved: Responsive design for mobile and tablet devices
* Improved: Smooth animations and transitions for better interactivity
* Fixed: Multiple phpcs warnings and code quality issues
* Fixed: Replaced deprecated PHP functions with WordPress-specific alternatives

= 5.2.7 - 2025-05-30 =
* Fixed: Resolved WordPress 6.7+ compatibility issues with translation loading
* Improved: Updated code to use modern PHP standards (replaced dirname(__FILE__) with __DIR__)
* Improved: Enhanced plugin initialization process for better performance
* Improved: Removed unnecessary translation calls in internal functions

= 5.2.6 - 2025-05-05 =
* Improved: Enhanced text clarity and grammar in admin notices for better user experience
* Improved: Strengthened security for exception handling throughout the plugin
* Improved: Verified compatibility with WordPress version 6.8

= 5.2.5 - 2025-04-21 =
* Added: Weekly full catalog refresh (includes all plugins, not just active ones) via WordPress cron
* Improved: Activation and deactivation logic now schedules/unschedules the weekly refresh event
* Changed: Restored updater code to clean state (removed WooCommerce debug code)
* Improved: Enhanced the logs page UI by removing duplicate environment information
* Improved: Updated JavaScript to dynamically generate plain text from table data for clipboard and download operations
* Fixed: Corrected grammatical error in license activation message on the Disable Updates page
* Improved: Restyled the "Go to Settings" button on the Disable Updates page to match material design standards
* Improved: Enhanced English text in admin footer for better clarity
* Tested: Verified compatibility with WordPress version 6.8

= 5.2.4.2 - 2025-02-05 =
* Added: Improved error suppression for WordPress 6.7+ textdomain JIT loading notices

= 5.2.4.1 - 2025-02-04 =
* Fixed: Custom error handler implemented to suppress translation loading notices
* Fixed: Removal of HTML markup from the plugin description
* Added: Inclusion of the GPL v2 license file


== Upgrade Notice ==

= 5.3.0 =
Major UI/UX update! Complete Material Design refresh with interactive search, bulk actions, and improved user experience. Recommended for all users.

= 5.2.7 =
Important update for WordPress 6.7+ compatibility. Fixes translation loading issues and improves performance.

= 5.2.6 =
Security enhancements and improved admin interface text. Recommended update for all users.

== Additional Information ==

= Support =

For support, please visit [GPLVault.com](https://www.gplvault.com) or contact our support team.

= Privacy =

This plugin communicates with GPLVault.com servers to:
* Verify license validity
* Retrieve update information
* Download plugin and theme updates

No personal data is collected beyond the license key and site URL necessary for license verification.

= Contributing =

For bug reports and feature requests, please contact GPLVault support.
