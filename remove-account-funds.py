#!/usr/bin/env python3
"""
Remove all account funds code from mwf-integration.php
"""

import re

# Read the file
with open('wp-content/plugins/mwf-integration/mwf-integration.php', 'r') as f:
    content = f.read()

# Remove API endpoint registrations
content = re.sub(
    r"    register_rest_route\('mwf/v1', '/funds',.*?\]\);",
    "    // Account funds endpoint removed - system discontinued",
    content, flags=re.DOTALL
)
content = re.sub(
    r"    register_rest_route\('mwf/v1', '/funds/add',.*?\]\);",
    "",
    content, flags=re.DOTALL
)

# Remove all account funds functions
functions_to_remove = [
    'mwf_process_funds_request',
    'mwf_add_funds_request',
    'mwf_get_user_balance',
    'mwf_customer_has_used_funds',
    'mwf_record_transaction',
    'mwf_user_funds_shortcode',
    'mwf_add_funds_menu_item',
    'mwf_add_funds_endpoint',
    'mwf_funds_endpoint_content',
    'mwf_add_account_funds_gateway',
    'mwf_init_subscription_funds_management',
    'mwf_check_account_funds_for_renewal',
    'mwf_process_subscription_renewal_payment',
    'mwf_add_account_funds_payment_button',
    'mwf_handle_subscription_payment_with_funds',
    'mwf_enqueue_subscription_scripts'
]

for func_name in functions_to_remove:
    # Match function declaration through its closing brace
    pattern = rf'/\*\*[\s\S]*?\*/\s*(?:add_\w+\([^)]+,\s*)?[\'"]?{re.escape(func_name)}[\'"]?\)?\s*{{[\s\S]*?^\}}'
    content = re.sub(pattern, f'// {func_name} removed - account funds system discontinued', content, flags=re.MULTILINE)

# Remove shortcode registration
content = re.sub(
    r"add_shortcode\('mwf_user_funds',\s*'mwf_user_funds_shortcode'\);",
    "// Account funds shortcode removed",
    content
)

# Remove filter/action hooks related to account funds
content = re.sub(
    r"add_filter\('woocommerce_account_menu_items',\s*'mwf_add_funds_menu_item'\);",
    "// Account funds menu removed",
    content
)
content = re.sub(
    r"add_action\('init',\s*'mwf_add_funds_endpoint'\);",
    "",
    content
)
content = re.sub(
    r"add_action\('woocommerce_account_funds_endpoint',\s*'mwf_funds_endpoint_content'\);",
    "",
    content
)
content = re.sub(
    r"add_filter\('woocommerce_payment_gateways',\s*'mwf_add_account_funds_gateway'\);",
    "// Account funds gateway removed",
    content
)
content = re.sub(
    r"add_action\('plugins_loaded',\s*'mwf_init_subscription_funds_management',\s*\d+\);",
    "",
    content
)

# Remove account_funds from user meta deletions
content = re.sub(
    r"\$wpdb->delete\(\$wpdb->usermeta,\s*array\('meta_key'\s*=>\s*'account_funds'\)\);",
    "// account_funds meta removed - system discontinued",
    content
)

# Remove account_funds from API responses
content = re.sub(
    r"'account_funds'\s*=>\s*number_format\(\(float\)mwf_get_user_balance\(\$user->ID\),\s*2\),?",
    "// 'account_funds' removed - system discontinued",
    content
)

# Clean up multiple blank lines
content = re.sub(r'\n{3,}', '\n\n', content)

# Write back
with open('wp-content/plugins/mwf-integration/mwf-integration.php', 'w') as f:
    f.write(content)

print("✅ Account funds code removed successfully")
print(f"File size reduced from ~2947 lines to {len(content.splitlines())} lines")
