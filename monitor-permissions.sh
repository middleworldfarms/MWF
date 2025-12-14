#!/bin/bash
# Updated to correct production path
SITE_PATH="/var/www/vhosts/middleworldfarms.org/httpdocs"
LOG_FILE="$SITE_PATH/permission-changes.log"

echo "$(date): Starting permission monitor" >> "$LOG_FILE"

# Set WooCommerce permissions (focus on SVG issue)
mkdir -p "$SITE_PATH/wp-content/plugins/woocommerce/assets/client/admin"
find "$SITE_PATH/wp-content/plugins/woocommerce/assets" -type d -exec chmod 775 {} \;
find "$SITE_PATH/wp-content/plugins/woocommerce/assets" -type f -exec chmod 664 {} \;
chown -R www-data:www-data "$SITE_PATH/wp-content/plugins/woocommerce/assets"

# Fix upgrades directory for WP updates
mkdir -p "$SITE_PATH/wp-content/upgrade"
chmod 775 "$SITE_PATH/wp-content/upgrade"
chown www-data:www-data "$SITE_PATH/wp-content/upgrade"

# Set general upload permissions
find "$SITE_PATH/wp-content/uploads" -type d -exec chmod 755 {} \;
find "$SITE_PATH/wp-content/uploads" -type f -exec chmod 644 {} \;
chown -R www-data:psacln "$SITE_PATH/wp-content/uploads"

echo "$(date): Permissions reset completed" >> "$LOG_FILE"