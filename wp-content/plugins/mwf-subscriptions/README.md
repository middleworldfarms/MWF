# MWF Custom Subscriptions

A WordPress/WooCommerce subscription management system powered by a Laravel backend. This plugin provides a complete alternative to WooCommerce Subscriptions addon, with automatic payment processing via Stripe.

## Features

- ✅ Automatic subscription creation from WooCommerce checkout
- ✅ Stripe payment integration
- ✅ Configurable delivery days based on shipping method
- ✅ Product variation support (Weekly, Fortnightly, Monthly)
- ✅ Customer subscription management portal
- ✅ Laravel backend for powerful subscription management
- ✅ Automatic payment retries with grace periods
- ✅ Email notifications for payment failures and renewals

## Architecture

This system uses a **split architecture**:
- **WordPress/WooCommerce**: Handles checkout, product catalog, and customer-facing portal
- **Laravel Backend**: Manages subscription lifecycle, payment processing, and admin interface

## Requirements

### WordPress/WooCommerce Server
- PHP 8.0+
- WordPress 6.0+
- WooCommerce 8.0+
- WooCommerce Payments (for Stripe integration)

### Laravel Backend Server
- PHP 8.2+
- MySQL/MariaDB 8.0+
- Composer
- Laravel 11+
- Access to WordPress database (read/write)


## Server Sizing Guide

Choose your server size based on expected customer volume and traffic:

### Small Deployment (1-500 customers)
**Perfect for**: Starting CSAs, small farms, community box schemes

- **CPU**: 2-4 cores
- **RAM**: 4-8GB
- **Storage**: 40-80GB SSD
- **Bandwidth**: 1-2TB/month
- **Monthly Cost**: $10-40

**Recommended Providers**:
- Vultr High Frequency (4 vCore / 8GB) - $24/month
- DigitalOcean Basic (4 vCPU / 8GB) - $48/month
- Linode Shared (4 vCPU / 8GB) - $36/month
- Hetzner CPX31 (4 vCPU / 8GB) - €13.90/month (~$15)

**Expected Performance**:
- Handles 50-100 concurrent users
- Response times under 200ms
- Up to 500 active subscriptions
- Adequate for weekly order processing

### Medium Deployment (500-2,000 customers)
**Perfect for**: Growing CSAs, multi-farm cooperatives, regional distributors

- **CPU**: 4-8 cores
- **RAM**: 8-16GB
- **Storage**: 80-160GB SSD
- **Bandwidth**: 2-4TB/month
- **Monthly Cost**: $40-100

**Recommended Providers**:
- Vultr High Frequency (8 vCore / 16GB) - $96/month
- DigitalOcean General Purpose (8 vCPU / 16GB) - $112/month
- Linode Dedicated (8 vCPU / 16GB) - $120/month

**Expected Performance**:
- Handles 200-400 concurrent users
- Response times under 150ms
- Up to 2,000 active subscriptions
- Smooth during peak ordering times

### Large Deployment (2,000+ customers)
**Perfect for**: Major CSA networks, food hubs, enterprise operations

- **CPU**: 8-16+ cores
- **RAM**: 16-32GB+
- **Storage**: 160-320GB+ SSD
- **Bandwidth**: 4-8TB/month
- **Monthly Cost**: $100-300+

**Consider**: Managed services, load balancers, separate database servers

### Resource Usage Breakdown

**Typical Usage by Component:**

| Component | CPU (cores) | RAM (GB) | Notes |
|-----------|-------------|----------|-------|
| Laravel Admin | 0.5-1 | 0.5-1 | Mostly idle, spikes during API calls |
| WordPress/WooCommerce | 0.5-1 | 1-2 | Higher during customer checkout |
| MySQL/MariaDB | 0.5-1 | 1-2 | Scales with database size |
| Nginx/Apache | 0.1-0.2 | 0.1 | Lightweight |
| Plesk (optional) | 0.2-0.3 | 1 | Control panel overhead |
| System (Ubuntu) | 0.2 | 0.5-1 | Base OS |
| **Total Minimum** | **2-3** | **4-6** | Safe operating range |

### Performance Optimization Tips

**For 4 Core / 8GB Servers** (most common):
1. Enable OPcache for PHP (50-100% faster)
2. Use Redis for Laravel cache/sessions
3. Configure MySQL InnoDB buffer pool to 2-3GB
4. Enable Gzip compression in Nginx
5. Use CloudFlare or similar CDN for static assets

**Monitoring Thresholds:**
- CPU: Consistently above 70% = time to upgrade
- RAM: Using swap space regularly = need more RAM
- Disk: Above 80% full = add storage or cleanup
- Load Average: Above core count = performance degradation

### Cost Optimization

**Money-Saving Tips:**
- Start with 4 cores / 8GB (under $40/month) - plenty for most farms
- Hetzner offers best price/performance in Europe
- Vultr High Frequency plans offer good balance
- DigitalOcean has excellent documentation and community
- Add monitoring (free tier of UptimeRobot, Datadog)

**Don't Over-Provision:**
- 8 cores / 16GB is overkill for most small-medium farms
- You can upgrade later if needed (downtime: 5-15 minutes)
- Save $20-60/month with right-sized server

### Real-World Example

**Middle World Farms Production Setup:**
- **Server**: 8 cores / 16GB (could run on 4 cores / 8GB)
- **Customers**: ~100-200 active subscriptions
- **Usage**: 25-35% CPU, 40-50% RAM during normal operation
- **Peak Load**: Order processing days hit 50-60% CPU briefly
- **Verdict**: 4 cores / 8GB would be sufficient


## Server Setup Options

### Recommended: Hybrid Approach (Plesk + Direct)

**If your VPS includes Plesk** (common with Vultr, Bitnami, AWS Lightsail, etc.):

✅ **Use Plesk for WordPress/WooCommerce**
- Install WordPress via WP Toolkit in Plesk
- Benefits: Easy SSL, automatic backups, one-click updates
- Domain: `yourdomain.com` or `www.yourdomain.com`
- Location: `/var/www/vhosts/yourdomain.com/httpdocs/`

✅ **Run Laravel Admin OUTSIDE Plesk**
- Install in `/opt/sites/admin.yourdomain.com/`
- Configure nginx/Apache directly (bypass Plesk)
- Subdomain: `admin.yourdomain.com`
- Full control, no Plesk limitations

**Why This Hybrid Approach?**
- Laravel apps don't benefit from Plesk control panel
- Avoids file permission conflicts
- Allows Laravel-specific deployment tools
- **Battle-tested in production** - this is how Middle World Farms runs it
- WordPress gets Plesk WP Toolkit benefits (genuinely useful)

**Directory Structure:**
```
/opt/sites/
└── admin.yourdomain.com/          # Laravel (outside Plesk)
    ├── app/
    ├── config/
    ├── database/
    ├── public/
    └── .env

/var/www/vhosts/                   # Plesk-managed
└── yourdomain.com/
    └── httpdocs/                  # WordPress (via Plesk WP Toolkit)
        ├── wp-content/
        │   └── plugins/
        │       └── mwf-subscriptions/  # This plugin
        └── wp-config.php
```

### Alternative: Pure LEMP/LAMP Stack

**If Plesk is NOT available** or you prefer full manual control:

All sites in `/var/www/` with manual nginx/Apache configuration:
```
/var/www/
├── admin.yourdomain.com/          # Laravel
├── yourdomain.com/                # WordPress
└── staging.yourdomain.com/        # Staging (optional)
```

**Pros**: Complete control, no abstraction layers  
**Cons**: Requires more Linux/server knowledge  
**Setup**: Manual nginx configs, Certbot for SSL, cron for backups

## Installation

### Step 1: Install Laravel Backend (Outside Plesk)

1. **SSH into your server** and create directory:
   ```bash
   mkdir -p /opt/sites/admin.yourdomain.com
   cd /opt/sites/admin.yourdomain.com
   ```

2. **Clone or upload your Laravel application**:
   ```bash
   # Option A: From Git
   git clone https://github.com/yourusername/vegbox-admin.git .
   
   # Option B: Upload files via SFTP/SCP
   # Upload to /opt/sites/admin.yourdomain.com/
   ```

3. **Install dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```

4. **Configure environment**:
   ```bash
   cp .env.example .env
   nano .env
   ```

5. **Edit `.env` file**:
   ```env
   # App Configuration
   APP_NAME="Vegbox Admin"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://admin.yourdomain.com
   
   # Primary Database (Laravel)
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=admin_laravel
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_secure_password
   
   # WordPress Database Connection
   WORDPRESS_DB_HOST=127.0.0.1
   WORDPRESS_DB_PORT=3306
   WORDPRESS_DB_DATABASE=wordpress_db
   WORDPRESS_DB_USERNAME=wp_user
   WORDPRESS_DB_PASSWORD=wp_password
   WORDPRESS_DB_PREFIX=wp_
   
   # API Authentication (generate random secure string)
   MWF_API_KEY=your-random-secure-key-here-use-php-artisan-key-generate-output
   
   # Stripe Configuration
   STRIPE_KEY=pk_test_your_publishable_key
   STRIPE_SECRET=sk_test_your_secret_key
   ```

6. **Generate application key**:
   ```bash
   php artisan key:generate
   ```

7. **Run migrations**:
   ```bash
   php artisan migrate --force
   ```

8. **Set permissions**:
   ```bash
   chown -R www-data:www-data /opt/sites/admin.yourdomain.com
   chmod -R 755 /opt/sites/admin.yourdomain.com
   chmod -R 775 /opt/sites/admin.yourdomain.com/storage
   chmod -R 775 /opt/sites/admin.yourdomain.com/bootstrap/cache
   ```

9. **Configure Nginx** (create `/etc/nginx/sites-available/admin.yourdomain.com`):
   ```nginx
   server {
       listen 80;
       listen [::]:80;
       server_name admin.yourdomain.com;
       return 301 https://$server_name$request_uri;
   }

   server {
       listen 443 ssl http2;
       listen [::]:443 ssl http2;
       server_name admin.yourdomain.com;
       root /opt/sites/admin.yourdomain.com/public;

       index index.php index.html;

       # SSL certificates (use certbot)
       ssl_certificate /etc/letsencrypt/live/admin.yourdomain.com/fullchain.pem;
       ssl_certificate_key /etc/letsencrypt/live/admin.yourdomain.com/privkey.pem;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.ht {
           deny all;
       }
   }
   ```

10. **Enable site and get SSL**:
    ```bash
    ln -s /etc/nginx/sites-available/admin.yourdomain.com /etc/nginx/sites-enabled/
    certbot --nginx -d admin.yourdomain.com
    nginx -t && systemctl reload nginx
    ```

11. **Create admin user**:
    ```bash
    php artisan tinker
    >>> App\Models\User::create(['name' => 'Admin', 'email' => 'admin@yourdomain.com', 'password' => bcrypt('your-secure-password')]);
    >>> exit
    ```

### Step 2: Install WordPress via Plesk

1. **Log into Plesk**: `https://your-server-ip:8443`

2. **Add Domain**:
   - Click **Add Domain**
   - Enter: `yourdomain.com`
   - Enable SSL (Let's Encrypt)

3. **Install WordPress via WP Toolkit**:
   - Go to **WordPress** in Plesk sidebar
   - Click **Install**
   - Select your domain
   - Set admin credentials
   - Click **Install**

4. **Install WooCommerce**:
   - Log into WordPress admin
   - **Plugins → Add New**
   - Search "WooCommerce"
   - Install and activate
   - Follow WooCommerce setup wizard

5. **Install WooCommerce Payments**:
   - **Plugins → Add New**
   - Search "WooCommerce Payments"
   - Install and activate
   - Connect Stripe account

### Step 3: Install MWF Subscriptions Plugin

1. **Upload Plugin**:
   - Download/clone the `mwf-subscriptions` plugin
   - Via Plesk File Manager: Upload to `/httpdocs/wp-content/plugins/`
   - Or via SFTP: `/var/www/vhosts/yourdomain.com/httpdocs/wp-content/plugins/`

2. **Activate Plugin**:
   - WordPress admin → **Plugins**
   - Find "MWF Custom Subscriptions"
   - Click **Activate**

3. **Configure Plugin**:
   - Go to **Settings → MWF Subscriptions**
   - **Laravel API URL**: `https://admin.yourdomain.com/api/subscriptions`
   - **API Key**: Copy from Laravel `.env` → `MWF_API_KEY` value
   - **Default Delivery Day**: `thursday` (or your preference)
   - **Collection Day**: `saturday` (or your preference)
   - Click **Save Settings**

4. **Test Connection**:
   - On settings page, click **Test API Connection**
   - Should show green ✓ "Connection successful!"
   - If red ✗, verify API URL and key match Laravel

### Step 4: Configure Subscription Products

1. **Create Vegbox Plan in Laravel**:
   - Log into Laravel: `https://admin.yourdomain.com/admin`
   - Go to **Plans → Create New**
   - Name: "Weekly Vegbox"
   - Note the **Plan ID** (e.g., `1`)

2. **Create Product in WooCommerce**:
   - WordPress → **Products → Add New**
   - Product name: "Vegetable Box Subscription"
   - Product type: **Variable product**
   - Add variations:
     - **Weekly** - £20/week
     - **Fortnightly** - £38/2 weeks
     - **Monthly** - £75/month

3. **Add Custom Fields** (scroll down on product edit page):
   - Click **Custom Fields → Add New**
   - Name: `_is_vegbox_subscription`, Value: `yes`
   - Add another field
   - Name: `_vegbox_plan_id`, Value: `1` (the Plan ID from Laravel)

4. **Set up Shipping Methods**:
   - **WooCommerce → Settings → Shipping**
   - Create zones and methods:
     - "Delivery" (for home delivery)
     - "Collection" (for pickup)

### Step 5: Configure Stripe

1. **WordPress/WooCommerce**:
   - Already done in Step 2 with WooCommerce Payments
   - Test with Stripe test card: `4242 4242 4242 4242`

2. **Laravel Backend**:
   - Stripe keys already in `.env` from Step 1
   - **Test mode**: Use `pk_test_` and `sk_test_` keys
   - **Live mode**: Switch to `pk_live_` and `sk_live_` keys

## Configuration Reference

### WordPress Options
Configurable via **Settings → MWF Subscriptions**:

- `mwf_api_url` - Laravel API endpoint
- `mwf_api_key` - Authentication key (must match Laravel)
- `mwf_default_delivery_day` - Delivery day for "Delivery" shipping
- `mwf_collection_delivery_day` - Day for "Collection" shipping

### Laravel Environment Variables

```env
# Required
MWF_API_KEY=your-secure-key
STRIPE_KEY=pk_xxx
STRIPE_SECRET=sk_xxx

# WordPress Database Access
WORDPRESS_DB_HOST=127.0.0.1
WORDPRESS_DB_DATABASE=wordpress
WORDPRESS_DB_USERNAME=wp_user
WORDPRESS_DB_PASSWORD=wp_pass
WORDPRESS_DB_PREFIX=wp_
```

### Product Custom Fields

Each subscription product needs:
- `_is_vegbox_subscription` = `yes`
- `_vegbox_plan_id` = `[Laravel Plan ID]`

## How It Works

### Customer Flow
1. Customer adds vegbox subscription to cart
2. Selects variation (Weekly/Fortnightly/Monthly)
3. Chooses shipping method (Delivery/Collection)
4. Completes checkout with Stripe payment
5. **Plugin automatically**:
   - Detects subscription product
   - Sets delivery day based on shipping method
   - Calls Laravel API to create subscription
   - Stores subscription ID in order meta

### Behind the Scenes
1. WordPress hooks: `woocommerce_checkout_update_order_meta`
2. Delivery day auto-set: Collection→Saturday, Delivery→Thursday
3. WordPress hooks: `woocommerce_order_status_processing`
4. API call to Laravel: `POST /api/subscriptions/create`
5. Laravel creates subscription, Stripe customer, payment schedule
6. Returns subscription ID to WordPress
7. Customer can manage via **My Account → Subscriptions**

## Usage

### For Customers

**Purchase Subscription**:
1. Browse products
2. Select frequency (Weekly/Fortnightly/Monthly)
3. Choose Delivery or Collection
4. Checkout with card

**Manage Subscription**:
- **My Account → Subscriptions**
- View next delivery date
- Pause/resume
- Change delivery day
- Update payment method
- Cancel subscription

### For Admins

**Laravel Dashboard**: `https://admin.yourdomain.com/admin`
- View all active subscriptions
- See upcoming renewals (7 days)
- Monitor failed payments (24h)
- Retry failed payments
- Issue refunds
- View payment history

**WordPress Orders**: Still visible in WooCommerce as regular orders

## Troubleshooting

### Subscription Not Creating

1. **Check WordPress debug log**: `/wp-content/debug.log`
   ```bash
   tail -f /var/www/vhosts/yourdomain.com/httpdocs/wp-content/debug.log
   ```

2. **Verify API connection**:
   - Settings → MWF Subscriptions
   - Click "Test API Connection"

3. **Check product has custom fields**:
   - Edit product in WordPress
   - Scroll to Custom Fields
   - Verify `_is_vegbox_subscription` = `yes`
   - Verify `_vegbox_plan_id` = valid plan ID

4. **Check Laravel logs**:
   ```bash
   tail -f /opt/sites/admin.yourdomain.com/storage/logs/laravel.log
   ```

### API Connection Failed

**Error**: "Connection failed: 401" or "Connection failed: 403"
- **Cause**: API key mismatch
- **Fix**: Ensure WordPress API Key matches Laravel `MWF_API_KEY` in `.env`

**Error**: "Connection failed: 404"
- **Cause**: Wrong API URL
- **Fix**: Should be `https://admin.yourdomain.com/api/subscriptions` (note `/api/subscriptions`)

**Error**: "Connection failed: SSL certificate problem"
- **Cause**: Self-signed or invalid SSL cert
- **Fix**: Get valid SSL with `certbot --nginx`

### Payment Failures

1. **Check Stripe Dashboard**: `https://dashboard.stripe.com`
   - View failed payments
   - Check error messages

2. **Verify Stripe keys** in Laravel `.env`:
   ```bash
   grep STRIPE /opt/sites/admin.yourdomain.com/.env
   ```

3. **Check Laravel payment logs**:
   ```bash
   cd /opt/sites/admin.yourdomain.com
   php artisan tinker
   >>> DB::table('vegbox_payment_attempts')->orderBy('id', 'desc')->limit(10)->get();
   ```

### Database Connection Errors

**Error**: "SQLSTATE[HY000] [2002] Connection refused"
- **Cause**: WordPress database not accessible from Laravel
- **Fix**: Check Laravel `.env` → `WORDPRESS_DB_*` settings
- **Fix**: Ensure MySQL allows connections from localhost

**Error**: "Access denied for user"
- **Cause**: Wrong database credentials
- **Fix**: Verify username/password in `.env`
- **Fix**: Grant permissions: `GRANT ALL ON wordpress_db.* TO 'wp_user'@'localhost';`

### Wrong Delivery Day

- Check **Settings → MWF Subscriptions**
- Verify delivery day configuration
- Check product has correct shipping method assigned

## Development

### Enable Debug Mode

**WordPress** (`wp-config.php`):
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**Laravel** (`.env`):
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Local Development

Use **Laravel Sail** or **Valet** for local Laravel development:
```bash
# Laravel Valet
cd /path/to/admin.yourdomain.local
valet link admin
valet secure
```

Use **Local by Flywheel** or **XAMPP** for local WordPress.

Update WordPress settings to use local Laravel:
```
API URL: https://admin.yourdomain.local/api/subscriptions
```

## Customization

### Hooks & Filters

**Customize delivery day logic**:
```php
// In your theme's functions.php
add_filter('mwf_auto_delivery_day', function($delivery_day, $shipping_method) {
    if (stripos($shipping_method, 'express') !== false) {
        return 'tuesday';
    }
    return $delivery_day;
}, 10, 2);
```

**Before subscription creation**:
```php
add_action('mwf_before_create_subscription', function($order_id, $api_data) {
    // Log or modify data before API call
    error_log("Creating subscription for order: $order_id");
}, 10, 2);
```

**After subscription creation**:
```php
add_action('mwf_after_create_subscription', function($order_id, $subscription_id) {
    // Send custom email, update CRM, etc.
    do_action('custom_crm_update', $subscription_id);
}, 10, 2);
```

## Security Best Practices

1. **Use strong API key**: Generate with `openssl rand -hex 32`
2. **HTTPS only**: Never run without SSL certificates
3. **Keep plugins updated**: WordPress, WooCommerce, this plugin
4. **Database permissions**: Grant only necessary privileges
5. **File permissions**: 
   - Directories: `755`
   - Files: `644`
   - Laravel storage: `775`
6. **Hide Laravel `.env`**: Ensure web server doesn't serve it
7. **Use Stripe webhooks**: For production payment monitoring

## Performance Tips

1. **Enable Redis** for Laravel cache:
   ```env
   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   ```

2. **Use Laravel Horizon** for queue management
3. **Enable OPcache** in PHP for better performance
4. **Use CDN** for WordPress static assets
5. **Database indexing**: Ensure migrations create proper indexes

## License

GPL v3 or later

## Support & Contributing

- **Issues**: https://github.com/yourusername/mwf-subscriptions/issues
- **Documentation**: https://docs.yourdomain.com
- **Email**: support@yourdomain.com

## Credits

Developed by **Middle World Farms CIC**  
Battle-tested in production serving real customers since 2024

## Changelog

### v1.1.0 (Current)
- ✅ Automatic subscription creation
- ✅ Configurable delivery days via admin settings
- ✅ Response parsing fixed
- ✅ Product variation name support
- ✅ Admin settings page with connection test

### v1.0.0
- Initial release
- Basic subscription creation
- Manual configuration
