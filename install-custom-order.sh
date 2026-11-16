#!/bin/bash
# Installation Script for Custom Order System
# Run: bash install-custom-order.sh

echo "🎨 Installing Custom Order System..."
echo ""

# Step 1: Create migrations folder
mkdir -p migrations

echo "✅ Step 1: Folder structure created"

# Step 2: Notify user to run migration
echo ""
echo "✅ Step 2: Please run the migration manually:"
echo "   URL: http://localhost/teddy_bear_shop/migrations/001_create_custom_orders_table.php"
echo ""

# Step 3: Create upload directory
mkdir -p images/custom-orders
chmod 755 images/custom-orders

echo "✅ Step 3: Upload directory created and permissions set"

# Step 4: Test system
echo ""
echo "✅ Step 4: Test system:"
echo "   URL: http://localhost/teddy_bear_shop/test-custom-order.php"
echo ""

echo "🎉 Installation complete!"
echo ""
echo "📋 Next steps:"
echo "1. Go to: http://localhost/teddy_bear_shop/migrations/001_create_custom_orders_table.php"
echo "2. Test at: http://localhost/teddy_bear_shop/test-custom-order.php"
echo "3. Read guide: CUSTOM_ORDER_QUICK_START.md"
echo ""
echo "💡 Try the feature:"
echo "   - Go to search page: http://localhost/teddy_bear_shop/pages/search.php?q=gau-that-to"
echo "   - Click green button: 🎨 Đặt mẫu riêng"
echo "   - Fill form and submit"
echo ""
echo "Admin manage: http://localhost/teddy_bear_shop/admin/manage-custom-orders.php"
