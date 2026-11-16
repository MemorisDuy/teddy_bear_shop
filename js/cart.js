/**
 * Fixed Cart JavaScript Handler - Teddy Bear Shop
 * Khắc phục lỗi nút icon ẩn và tính năng xóa không hoạt động
 */

console.log('🐻 Cart JavaScript Loading...');

// ===== CART MANAGEMENT CLASS =====
class TeddyBearCart {
    constructor() {
        this.apiBasePath = this.getApiBasePath();
        this.init();
    }

    getApiBasePath() {
        const path = window.location.pathname;
        console.log('🔍 Detecting path:', path);
        if (path.includes('/pages/')) return '../';
        if (path.includes('/cart/')) return './';
        if (path.includes('/admin/')) return '../';
        return '/teddy_bear_shop/';
    }

    init() {
        console.log('🚀 Initializing Cart System...');
        this.bindEvents();
        this.loadCartCount();
        this.setupIconVisibility();
        this.fixDeleteButtons();
    }

    // ===== FIX ICON VISIBILITY =====
    setupIconVisibility() {
        // Đảm bảo tất cả icon hiển thị đúng cách
        const allIcons = document.querySelectorAll('i[class*="fas"], i[class*="far"], i[class*="fab"]');
        allIcons.forEach(icon => {
            icon.style.display = 'inline-block';
            icon.style.visibility = 'visible';
            icon.style.opacity = '1';
        });

        // Fix cart icon specifically
        const cartIcons = document.querySelectorAll('.cart-link i, .fa-shopping-cart, .fa-cart-plus');
        cartIcons.forEach(icon => {
            icon.style.display = 'inline-block !important';
            icon.style.visibility = 'visible !important';
        });

        // Fix delete/remove icons
        const deleteIcons = document.querySelectorAll('.fa-trash, .fa-times, .fa-remove, .fa-trash-alt');
        deleteIcons.forEach(icon => {
            icon.style.display = 'inline-block !important';
            icon.style.visibility = 'visible !important';
            icon.style.color = '#dc3545';
        });

        console.log('✅ Icon visibility fixed');
    }

    // ===== FIX DELETE BUTTONS =====
    fixDeleteButtons() {
        // Tìm tất cả nút xóa và đảm bảo chúng hoạt động
        const deleteSelectors = [
            '.remove-cart-item',
            '.btn-remove-item',
            '[data-action="remove-item"]',
            '.delete-btn',
            '.remove-btn',
            '.fa-trash',
            '.fa-times',
            '.fa-remove'
        ];

        deleteSelectors.forEach(selector => {
            const buttons = document.querySelectorAll(selector);
            buttons.forEach(btn => {
                // Đảm bảo button có thể click được
                btn.style.pointerEvents = 'auto';
                btn.style.cursor = 'pointer';
                btn.style.opacity = '1';
                btn.style.visibility = 'visible';
                
                // Thêm data attribute nếu chưa có
                if (!btn.dataset.productId && !btn.closest('[data-product-id]')) {
                    const productRow = btn.closest('tr, .cart-item, .product-item');
                    if (productRow) {
                        const productIdElement = productRow.querySelector('[data-product-id], [data-id]');
                        if (productIdElement) {
                            btn.dataset.productId = productIdElement.dataset.productId || productIdElement.dataset.id;
                        }
                    }
                }
                
                // Force add event listener
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.removeFromCart(btn);
                });
            });
        });

        console.log('✅ Delete buttons fixed');
    }

    // ===== EVENT BINDING =====
    bindEvents() {
        // Add to cart
        document.addEventListener('click', (e) => {
            const addBtn = e.target.closest('.add-to-cart-btn, .btn-add-to-cart, [data-action="add-to-cart"]');
            if (addBtn) {
                e.preventDefault();
                e.stopPropagation();
                this.addToCart(addBtn);
            }
        });

        // Remove from cart - Multiple selectors
        document.addEventListener('click', (e) => {
            const removeBtn = e.target.closest(
                '.remove-cart-item, .btn-remove-item, [data-action="remove-item"], ' +
                '.delete-btn, .remove-btn, .btn-delete, .fa-trash, .fa-times, .fa-remove'
            );
            if (removeBtn) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🗑️ Remove button clicked:', removeBtn);
                this.removeFromCart(removeBtn);
            }
        });

        // Update quantity
        document.addEventListener('change', (e) => {
            if (e.target.matches('.cart-quantity-input, .quantity-input')) {
                this.updateQuantity(e.target);
            }
        });

        // Nút cộng/trừ số lượng
        document.addEventListener('click', (e) => {
            if (e.target.closest('.quantity-plus')) {
                const btn = e.target.closest('.quantity-plus');
                const cartItem = btn.closest('.cart-item, tr');
                const input = cartItem.querySelector('.quantity-input');
                if (input) {
                    let val = parseInt(input.value) || 1;
                    const max = parseInt(input.max) || 99;
                    if (val < max) {
                        input.value = val + 1;
                        this.updateQuantity(input);
                    }
                }
                e.preventDefault();
            }
            if (e.target.closest('.quantity-minus')) {
                const btn = e.target.closest('.quantity-minus');
                const cartItem = btn.closest('.cart-item, tr');
                const input = cartItem.querySelector('.quantity-input');
                if (input) {
                    let val = parseInt(input.value) || 1;
                    const min = parseInt(input.min) || 1;
                    if (val > min) {
                        input.value = val - 1;
                        this.updateQuantity(input);
                    }
                }
                e.preventDefault();
            }
        });

        // Clear cart
        document.addEventListener('click', (e) => {
            if (e.target.matches('.clear-cart-btn, [data-action="clear-cart"]')) {
                e.preventDefault();
                this.clearCart();
            }
        });

        console.log('✅ Events bound successfully');
    }

    // ===== HELPER FUNCTIONS =====
    getProductId(element) {
        // Try multiple ways to get product ID
        const productId = 
            element.dataset.productId ||
            element.dataset.id ||
            element.getAttribute('data-product-id') ||
            element.getAttribute('data-id') ||
            element.closest('[data-product-id]')?.dataset.productId ||
            element.closest('[data-id]')?.dataset.id ||
            element.closest('tr')?.dataset.productId ||
            element.closest('.cart-item')?.dataset.productId ||
            element.closest('.product-item')?.dataset.productId;

        console.log('🔍 Product ID found:', productId, 'from element:', element);
        return productId;
    }

    getQuantity(button) {
        const container = button.closest('.product-item, .product-card, .cart-item, tr');
        const quantityInput = container?.querySelector('.quantity-input, .cart-quantity-input, input[type="number"]');
        const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
        console.log('📊 Quantity:', quantity);
        return quantity;
    }

    // ===== ADD TO CART =====
    async addToCart(button) {
        const productId = this.getProductId(button);
        const quantity = this.getQuantity(button);
        console.log('🛒 Adding to cart:', {productId, quantity});
        if (!productId) {
            this.showNotification('❌ Không tìm thấy ID sản phẩm!', 'error');
            return;
        }
        this.setButtonLoading(button, true);
        try {
            const apiUrl = this.apiBasePath + 'cart/add-to-cart.php';
            console.log('🔗 API URL:', apiUrl);
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `product_id=${productId}&quantity=${quantity}`
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('✅ Add response:', data);
            
            if (data.success) {
                this.showNotification('✅ ' + data.message, 'success');
                if (data.cart_count !== undefined) {
                    this.updateCartCount(data.cart_count);
                }
                // Ép fetch lại cart count từ server sau 300ms để đảm bảo đồng bộ
                setTimeout(() => this.loadCartCount(), 300);
                this.animateButton(button, 'success');
            } else {
                const errorMsg = data.message || 'Có lỗi xảy ra khi thêm vào giỏ hàng!';
                this.showNotification('❌ ' + errorMsg, 'error');
                this.animateButton(button, 'error');
            }
        } catch (error) {
            console.error('❌ Add to cart error:', error);
            this.showNotification('❌ Có lỗi xảy ra: ' + error.message, 'error');
            this.animateButton(button, 'error');
        } finally {
            this.setButtonLoading(button, false);
        }
    }

    // ===== REMOVE FROM CART (FIXED) =====
    async removeFromCart(button) {
        const productId = this.getProductId(button);
        const apiUrl = this.apiBasePath + 'cart/remove-from-cart.php';
        console.log('🗑️ Remove from cart triggered');
        console.log('🔗 API URL:', apiUrl);
        if (!productId) {
            this.showNotification('❌ Không tìm thấy ID sản phẩm!', 'error');
            return;
        }
        this.setButtonLoading(button, true);
        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            const response = await fetch(apiUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            console.log('📦 Response:', data);
            if (data.success) {
                this.showNotification(data.message, 'success');
                // Xóa phần tử sản phẩm khỏi DOM
                const row = button.closest('tr, .cart-item, .product-item');
                if (row) row.remove();
                // Cập nhật badge và tổng tiền
                this.updateCartCount(data.cart_count);
                if (typeof data.cart_total !== 'undefined') {
                    this.updateCartTotal(data.cart_total);
                }
                // Nếu giỏ hàng trống, reload hoặc hiển thị thông báo trống
                setTimeout(() => {
                    if (data.cart_count === 0) {
                        location.reload();
                    }
                }, 600);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('❌ Remove from cart error:', error);
        }
        this.setButtonLoading(button, false);
    }

    // ===== UPDATE QUANTITY =====
    async updateQuantity(input) {
        const productId = this.getProductId(input);
        const quantity = parseInt(input.value) || 1;
        const apiUrl = this.apiBasePath + 'cart/update-cart.php';
        console.log('🔄 Update quantity triggered');
        console.log('🔗 API URL:', apiUrl);
        if (!productId) {
            this.showNotification('❌ Không tìm thấy ID sản phẩm!', 'error');
            return;
        }
        this.setButtonLoading(input, true);
        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            const response = await fetch(apiUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            console.log('📦 Response:', data);
            if (data.success) {
                this.showNotification(data.message, 'success');
                // Cập nhật thành tiền của sản phẩm
                const cartItem = input.closest('.cart-item, tr[data-product-id]');
                if (cartItem && data.item_total !== undefined) {
                    const itemTotal = cartItem.querySelector('.item-total');
                    if (itemTotal) itemTotal.textContent = this.formatPrice(data.item_total);
                }
                // Cập nhật tổng tiền, tạm tính, phí ship
                if (data.cart_total !== undefined) {
                    const totalEl = document.getElementById('total');
                    if (totalEl) totalEl.textContent = this.formatPrice(data.cart_total + (typeof data.shipping_fee !== 'undefined' ? data.shipping_fee : 0));
                }
                if (data.cart_subtotal !== undefined) {
                    const subtotalEl = document.getElementById('subtotal');
                    if (subtotalEl) subtotalEl.textContent = this.formatPrice(data.cart_subtotal);
                }
                // Nếu có cập nhật phí ship (nếu backend trả về)
                if (typeof data.shipping_fee !== 'undefined') {
                    const shippingEl = document.getElementById('shipping');
                    if (shippingEl) shippingEl.textContent = this.formatPrice(data.shipping_fee);
                }
                // Cập nhật badge số lượng giỏ hàng
                if (data.cart_count !== undefined) {
                    this.updateCartCount(data.cart_count);
                }
                // Nếu số lượng về 0 thì xóa sản phẩm khỏi DOM
                if (quantity === 0 && cartItem) {
                    cartItem.remove();
                }
                // Nếu giỏ hàng trống thì reload hoặc hiển thị thông báo trống
                if (data.cart_count === 0) {
                    setTimeout(() => location.reload(), 600);
                }
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('❌ Update quantity error:', error);
        }
        this.setButtonLoading(input, false);
    }

    // ===== CLEAR CART =====
    async clearCart() {
        const apiUrl = this.apiBasePath + 'cart/clear-cart.php';
        console.log('🧹 Clear cart triggered');
        console.log('🔗 API URL:', apiUrl);
        if (!confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?')) return;
        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            console.log('📦 Response:', data);
            if (data.success) {
                this.showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('❌ Clear cart error:', error);
        }
    }

    // ===== UI HELPER FUNCTIONS =====
    setButtonLoading(button, isLoading) {
        if (!button || !button.style) return;
        if (isLoading) {
            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '⏳ Đang xử lý...';
            button.classList.add('loading');
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || button.innerHTML;
            button.classList.remove('loading');
        }
    }

    animateButton(button, type) {
        if (!button || !button.classList) return;
        button.classList.add(`animate-${type}`);
        setTimeout(() => button.classList.remove(`animate-${type}`), 1000);
        
        if (type === 'success') {
            const originalText = button.innerHTML;
            button.innerHTML = '✅ Thành công!';
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
            }, 2000);
        }
    }

    updateCartCount(count) {
        const cartBadge = document.getElementById('cartCount');
        const numCount = parseInt(count) || 0;
        console.log('🛒 Updating cart count to:', numCount);
        if (cartBadge) {
            cartBadge.textContent = numCount;
            if (numCount > 0) {
                cartBadge.style.display = 'flex'; // Đảm bảo đồng bộ với CSS header
            } else {
                cartBadge.style.display = 'none';
            }
        }
    }

    updateCartTotal(total) {
        const cartTotalElements = document.querySelectorAll('.cart-total, #cart-total, [data-cart-total]');
        cartTotalElements.forEach(el => {
            el.textContent = this.formatPrice(total);
        });
    }

    updateItemTotal(productId, total) {
        const itemTotalElement = document.querySelector(`[data-item-total="${productId}"]`);
        if (itemTotalElement) {
            itemTotalElement.textContent = this.formatPrice(total);
        }
    }

    formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
    }

    checkEmptyCart() {
        const cartItems = document.querySelectorAll('.cart-item, tr[data-product-id]');
        if (cartItems.length <= 1) { // <= 1 because current item is being removed
            const cartContainer = document.querySelector('.cart-items, .cart-container, .table-responsive');
            if (cartContainer) {
                setTimeout(() => {
                    cartContainer.innerHTML = `
                        <div class="empty-cart text-center py-5">
                            <span style="font-size: 3rem; display: block; margin-bottom: 1rem;">🛒</span>
                            <h5>Giỏ hàng trống</h5>
                            <p class="text-muted">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                            <a href="${this.apiBasePath}pages/products.php" class="btn btn-primary">
                                🛍️ Tiếp tục mua sắm
                            </a>
                        </div>
                    `;
                }, 600);
            }
        }
    }

    async loadCartCount() {
        try {
            const response = await fetch(this.apiBasePath + 'cart/get-cart-count.php', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateCartCount(data.cart_count);
                console.log('📊 Cart count loaded:', data.cart_count);
            } else {
                console.log('⚠️ Cart count response:', data);
                this.updateCartCount(0);
            }
        } catch (error) {
            console.log('⚠️ Could not load cart count:', error);
            this.updateCartCount(0);
        }
    }

    showNotification(message, type = 'info', duration = 5000) {
        // Remove existing notifications of same type
        document.querySelectorAll(`.notification-${type}`).forEach(n => n.remove());
        
        let container = document.getElementById('notificationContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificationContainer';
            container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; max-width: 400px;';
            document.body.appendChild(container);
        }
        
        const colors = { 
            success: '#28a745', 
            error: '#dc3545', 
            info: '#17a2b8', 
            warning: '#ffc107'
        };
        
        const notification = document.createElement('div');
        notification.className = `notification-${type} alert alert-${type === 'error' ? 'danger' : type}`;
        notification.style.cssText = `
            background: ${colors[type] || colors.info}; 
            color: white; 
            padding: 15px 20px; 
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
            margin-bottom: 10px;
            transform: translateX(100%); 
            opacity: 0; 
            transition: all 0.3s ease;
            display: flex; 
            align-items: center; 
            gap: 10px; 
            font-weight: 500;
            border: none;
        `;
        
        notification.innerHTML = `
            <span style="flex: 1;">${message}</span>
            <button onclick="this.parentElement.remove()" 
                    style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; padding: 0;">×</button>
        `;
        
        container.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 10);
        
        if (duration > 0) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.transform = 'translateX(100%)';
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, duration);
        }
    }
}

// ===== INITIALIZATION =====
let teddyCart = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM Loaded - Initializing Cart...');
    
    // Initialize cart system
    teddyCart = new TeddyBearCart();
    
    // Make cart available globally
    window.teddyCart = teddyCart;
    
    // Add CSS animations
    addCartCSS();
    
    // Force show all icons after short delay
    setTimeout(() => {
        forceShowIcons();
        fixAllDeleteButtons();
    }, 100);
    
    console.log('✅ Cart system fully initialized!');
});

// ===== FORCE SHOW ICONS =====
function forceShowIcons() {
    console.log('🔧 Force showing all icons...');
    
    const iconSelectors = [
        'i[class*="fa"]',
        '.fas', '.far', '.fab', '.fal',
        '.fa-shopping-cart', '.fa-trash', '.fa-times', '.fa-plus', '.fa-minus',
        '.fa-edit', '.fa-save', '.fa-cancel', '.fa-check'
    ];
    
    iconSelectors.forEach(selector => {
        const icons = document.querySelectorAll(selector);
        icons.forEach(icon => {
            icon.style.setProperty('display', 'inline-block', 'important');
            icon.style.setProperty('visibility', 'visible', 'important');
            icon.style.setProperty('opacity', '1', 'important');
            icon.style.setProperty('font-family', '"Font Awesome 6 Free", "Font Awesome 5 Free"', 'important');
            icon.style.setProperty('font-weight', '900', 'important');
        });
    });
    
    console.log('✅ All icons forced to show');
}

// ===== FIX ALL DELETE BUTTONS =====
function fixAllDeleteButtons() {
    console.log('🔧 Fixing all delete buttons...');
    
    const deleteSelectors = [
        '.fa-trash', '.fa-times', '.fa-remove', '.fa-trash-alt',
        '.remove-cart-item', '.btn-remove-item', '[data-action="remove-item"]',
        '.delete-btn', '.remove-btn', '.btn-delete'
    ];
    
    deleteSelectors.forEach(selector => {
        const buttons = document.querySelectorAll(selector);
        buttons.forEach(btn => {
            // Make sure button is clickable
            btn.style.setProperty('pointer-events', 'auto', 'important');
            btn.style.setProperty('cursor', 'pointer', 'important');
            btn.style.setProperty('opacity', '1', 'important');
            btn.style.setProperty('visibility', 'visible', 'important');
            
            // Add product ID if missing
            if (!btn.dataset.productId) {
                const productRow = btn.closest('tr, .cart-item, .product-item');
                if (productRow) {
                    const productId = productRow.dataset.productId || 
                                    productRow.querySelector('[data-product-id]')?.dataset.productId ||
                                    productRow.querySelector('[data-id]')?.dataset.id;
                    if (productId) {
                        btn.dataset.productId = productId;
                    }
                }
            }
            
            // Force click event
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🗑️ Delete button clicked:', this);
                if (window.teddyCart) {
                    window.teddyCart.removeFromCart(this);
                }
            });
        });
    });
    
    console.log('✅ All delete buttons fixed');
}

// ===== ADD CSS ANIMATIONS =====
function addCartCSS() {
    const css = `
        <style id="cart-animations">
        @keyframes cartPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        @keyframes cartBounce {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.1); }
            50% { transform: scale(1.2); }
            75% { transform: scale(1.1); }
        }

        @keyframes successPulse {
            0% { background-color: initial; }
            50% { background-color: #28a745; color: white; }
            100% { background-color: initial; }
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .animate-success {
            animation: successPulse 1s ease;
        }

        .animate-error {
            animation: errorShake 0.5s ease;
        }

        /* Force icon visibility */
        i[class*="fa"], .fas, .far, .fab, .fal {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free" !important;
            font-weight: 900 !important;
        }

        /* Fix delete buttons */
        .fa-trash, .fa-times, .fa-remove, .fa-trash-alt,
        .remove-cart-item, .btn-remove-item, .delete-btn, .remove-btn {
            pointer-events: auto !important;
            cursor: pointer !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Cart count badge */
        #cartCount {
            position: absolute;
            top: -8px;
            right: -8px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            border-radius: 50%;
            background: #dc3545;
            color: white;
        }

        .cart-link {
            position: relative;
        }

        /* Loading state */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Notification styles */
        .notification-success, .notification-error, .notification-info, .notification-warning {
            font-size: 14px;
            line-height: 1.4;
        }
        </style>
    `;
    
    if (!document.getElementById('cart-animations')) {
        document.head.insertAdjacentHTML('beforeend', css);
    }
}

// ===== GLOBAL FUNCTIONS =====
window.addToCart = function(buttonOrId) {
    if (window.teddyCart) {
        if (typeof buttonOrId === 'number' || typeof buttonOrId === 'string') {
            const fakeBtn = document.createElement('button');
            fakeBtn.dataset.productId = buttonOrId;
            window.teddyCart.addToCart(fakeBtn);
        } else {
            window.teddyCart.addToCart(buttonOrId);
        }
    }
};

window.removeFromCart = function(button) {
    if (window.teddyCart) {
        window.teddyCart.removeFromCart(button);
    }
};

window.updateCartQuantity = function(input) {
    if (window.teddyCart) {
        window.teddyCart.updateQuantity(input);
    }
};

window.clearCart = function() {
    if (window.teddyCart) {
        window.teddyCart.clearCart();
    }
};

console.log('🐻✅ Fixed Cart JavaScript Loaded Successfully!');