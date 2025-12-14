#!/bin/bash
# Clean up trashed product variations
# This script permanently deletes trashed variations to keep WooCommerce and Laravel in sync

echo "=== WooCommerce Variation Cleanup Script ==="
echo ""
echo "This script will permanently delete trashed product variations."
echo "Trashed variations inflate counts in Laravel sync and cause inconsistencies."
echo ""

# Check current trash count
TRASH_COUNT=$(wp post list --post_type=product_variation --post_status=trash --format=count --allow-root)

echo "Found $TRASH_COUNT trashed variations:"
echo ""
wp post list --post_type=product_variation --post_status=trash --format=table --allow-root --fields=ID,post_title,post_parent

echo ""
read -p "Do you want to PERMANENTLY DELETE these variations? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Aborted. No changes made."
    exit 0
fi

echo ""
echo "Permanently deleting trashed variations..."

# Get all trashed variation IDs
TRASHED_IDS=$(wp post list --post_type=product_variation --post_status=trash --format=csv --fields=ID --allow-root | tail -n +2)

if [ -z "$TRASHED_IDS" ]; then
    echo "No trashed variations found."
    exit 0
fi

# Delete each variation permanently
DELETED_COUNT=0
for ID in $TRASHED_IDS; do
    echo "Deleting variation ID: $ID"
    wp post delete $ID --force --allow-root >/dev/null 2>&1
    if [ $? -eq 0 ]; then
        ((DELETED_COUNT++))
    else
        echo "  ⚠️  Failed to delete ID $ID"
    fi
done

echo ""
echo "✅ Deleted $DELETED_COUNT variations permanently"
echo ""

# Show remaining published variations count
PUBLISHED_COUNT=$(wp post list --post_type=product_variation --post_status=publish --format=count --allow-root)
echo "📊 Current published variations: $PUBLISHED_COUNT"

# Verify no trash remains
REMAINING_TRASH=$(wp post list --post_type=product_variation --post_status=trash --format=count --allow-root)
if [ "$REMAINING_TRASH" -eq 0 ]; then
    echo "✅ All trashed variations cleaned up successfully!"
else
    echo "⚠️  Warning: $REMAINING_TRASH trashed variations still remain"
fi

echo ""
echo "=== Cleanup Complete ==="
echo ""
echo "Next steps:"
echo "1. Re-run your Laravel sync command to update counts"
echo "2. Verify the published variation count matches in Laravel"
