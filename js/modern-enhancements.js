/**
 * Modern JavaScript Enhancements - Teddy Bear Shop 🐻
 * ES6+ Features và Modern Web APIs
 */

// ===== MODERN NOTIFICATION SYSTEM =====
class ModernNotification {
    constructor() {
        this.container = this.createContainer();
        this.notifications = new Map();
        this.maxNotifications = 5;
    }

    createContainer() {
        const container = document.createElement('div');
        container.className = 'notification-container-modern';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            pointer-events: none;
        `;
        document.body.appendChild(container);
        return container;
    }

    show(message, type = 'info', duration = 5000) {
        const id = Date.now() + Math.random();
        
        // Remove oldest notification if limit exceeded
        if (this.notifications.size >= this.maxNotifications) {
            const oldestId = this.notifications.keys().next().value;
            this.remove(oldestId);
        }

        const notification = this.createNotification(id, message, type);
        this.container.appendChild(notification);
        this.notifications.set(id, notification);

        // Animate in
        requestAnimationFrame(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        });

        // Auto remove
        if (duration > 0) {
            setTimeout(() => this.remove(id), duration);
        }

        return id;
    }

    createNotification(id, message, type) {
        const notification = document.createElement('div');
        notification.className = `notification-modern ${type}`;
        notification.style.cssText = `
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 10px;
            pointer-events: auto;
        `;

        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        notification.innerHTML = `
            <div class="notification-content-modern">
                <span class="notification-icon-modern">${icons[type] || icons.info}</span>
                <div class="notification-text-modern">${message}</div>
                <button class="notification-close-modern" onclick="notificationSystem.remove(${id})">×</button>
            </div>
        `;

        return notification;
    }

    remove(id) {
        const notification = this.notifications.get(id);
        if (notification) {
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
                this.notifications.delete(id);
            }, 400);
        }
    }

    clear() {
        this.notifications.forEach((_, id) => this.remove(id));
    }
}

// Initialize global notification system
const notificationSystem = new ModernNotification();

// ===== MODERN CART SYSTEM =====
class ModernCart {
    constructor() {
        this.items = new Map();
        this.listeners = new Set();
        this.storage = new CartStorage();
        this.api = new CartAPI();
        this.init();
    }

    async init() {
        await this.loadFromStorage();
        this.createFloatingCart();
        this.bindEvents();
    }

    async loadFromStorage() {
        try {
            const cartData = await this.storage.load();
            cartData.forEach(item => {
                this.items.set(item.id, item);
            });
            this.notify('loaded');
        } catch (error) {
            console.error('Failed to load cart:', error);
        }
    }

    async addItem(productId, quantity = 1) {
        try {
            // Validate product first
            const product = await this.api.getProduct(productId);
            if (!product || product.stock_quantity < quantity) {
                throw new Error('Sản phẩm không có sẵn hoặc không đủ số lượng');
            }

            const existingItem = this.items.get(productId);
            const newQuantity = existingItem ? existingItem.quantity + quantity : quantity;

            if (newQuantity > product.stock_quantity) {
                throw new Error(`Chỉ còn ${product.stock_quantity} sản phẩm`);
            }

            const item = {
                id: productId,
                name: product.name,
                price: product.sale_price || product.price,
                image: product.main_image,
                quantity: newQuantity,
                slug: product.slug
            };

            this.items.set(productId, item);
            await this.saveToStorage();
            
            this.notify('itemAdded', item);
            notificationSystem.show(`🛒 Đã thêm "${product.name}" vào giỏ hàng!`, 'success');
            
            return true;
        } catch (error) {
            notificationSystem.show(error.message, 'error');
            return false;
        }
    }

    async updateQuantity(productId, quantity) {
        if (quantity <= 0) {
            return this.removeItem(productId);
        }

        const item = this.items.get(productId);
        if (item) {
            item.quantity = quantity;
            await this.saveToStorage();
            this.notify('itemUpdated', item);
        }
    }

    async removeItem(productId) {
        const item = this.items.get(productId);
        if (item) {
            this.items.delete(productId);
            await this.saveToStorage();
            this.notify('itemRemoved', item);
            notificationSystem.show(`🗑️ Đã xóa "${item.name}" khỏi giỏ hàng`, 'info');
        }
    }

    async clear() {
        this.items.clear();
        await this.saveToStorage();
        this.notify('cleared');
        notificationSystem.show('🛒 Đã xóa tất cả sản phẩm khỏi giỏ hàng', 'info');
    }

    getItems() {
        return Array.from(this.items.values());
    }

    getCount() {
        return Array.from(this.items.values()).reduce((sum, item) => sum + item.quantity, 0);
    }

    getTotal() {
        return Array.from(this.items.values()).reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }

    createFloatingCart() {
        const floatingCart = document.createElement('div');
        floatingCart.className = 'cart-floating';
        floatingCart.innerHTML = `
            <span>🛒</span>
            <span class="cart-count-floating">${this.getCount()}</span>
        `;
        
        floatingCart.addEventListener('click', () => {
            window.location.href = 'cart/cart.php';
        });

        document.body.appendChild(floatingCart);
        this.floatingCart = floatingCart;
    }

    updateFloatingCart() {
        if (this.floatingCart) {
            const countElement = this.floatingCart.querySelector('.cart-count-floating');
            const count = this.getCount();
            countElement.textContent = count;
            countElement.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    async saveToStorage() {
        await this.storage.save(this.getItems());
    }

    bindEvents() {
        // Listen for storage changes (for multi-tab sync)
        window.addEventListener('storage', (e) => {
            if (e.key === 'cart_items') {
                this.loadFromStorage();
            }
        });
    }

    // Observer pattern for cart changes
    subscribe(callback) {
        this.listeners.add(callback);
    }

    unsubscribe(callback) {
        this.listeners.delete(callback);
    }

    notify(event, data = null) {
        this.updateFloatingCart();
        this.listeners.forEach(callback => {
            try {
                callback(event, data, this);
            } catch (error) {
                console.error('Cart listener error:', error);
            }
        });
    }
}

// ===== CART STORAGE =====
class CartStorage {
    constructor() {
        this.key = 'teddy_cart_items';
    }

    async save(items) {
        try {
            // Save to localStorage
            localStorage.setItem(this.key, JSON.stringify(items));
            
            // Also sync to server if user is logged in
            if (window.isUserLoggedIn) {
                await this.syncToServer(items);
            }
        } catch (error) {
            console.error('Failed to save cart:', error);
        }
    }

    async load() {
        try {
            // Load from server if logged in, otherwise from localStorage
            if (window.isUserLoggedIn) {
                return await this.loadFromServer();
            } else {
                const data = localStorage.getItem(this.key);
                return data ? JSON.parse(data) : [];
            }
        } catch (error) {
            console.error('Failed to load cart:', error);
            return [];
        }
    }

    async syncToServer(items) {
        try {
            const response = await fetch('api/cart-sync.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                },
                body: JSON.stringify({ items })
            });

            if (!response.ok) {
                throw new Error('Sync failed');
            }
        } catch (error) {
            console.error('Failed to sync cart to server:', error);
        }
    }

    async loadFromServer() {
        try {
            const response = await fetch('api/cart-load.php');
            if (!response.ok) {
                throw new Error('Load failed');
            }
            
            const data = await response.json();
            return data.items || [];
        } catch (error) {
            console.error('Failed to load cart from server:', error);
            // Fallback to localStorage
            const data = localStorage.getItem(this.key);
            return data ? JSON.parse(data) : [];
        }
    }
}

// ===== CART API =====
class CartAPI {
    constructor() {
        this.baseURL = window.location.origin;
        this.cache = new Map();
    }

    async getProduct(productId) {
        // Check cache first
        if (this.cache.has(productId)) {
            return this.cache.get(productId);
        }

        try {
            const response = await fetch(`api/product.php?id=${productId}`);
            if (!response.ok) {
                throw new Error('Product not found');
            }

            const product = await response.json();
            
            // Cache for 5 minutes
            this.cache.set(productId, product);
            setTimeout(() => this.cache.delete(productId), 5 * 60 * 1000);

            return product;
        } catch (error) {
            console.error('Failed to fetch product:', error);
            throw error;
        }
    }
}

// ===== MODERN SEARCH SYSTEM =====
class ModernSearch {
    constructor(inputSelector) {
        this.input = document.querySelector(inputSelector);
        this.suggestions = null;
        this.cache = new Map();
        this.debounceTime = 300;
        this.minQueryLength = 2;
        
        if (this.input) {
            this.init();
        }
    }

    init() {
        this.createSuggestionContainer();
        this.bindEvents();
    }

    createSuggestionContainer() {
        this.suggestions = document.createElement('div');
        this.suggestions.className = 'search-suggestions-modern';
        this.suggestions.style.display = 'none';
        
        this.input.parentNode.style.position = 'relative';
        this.input.parentNode.appendChild(this.suggestions);
    }

    bindEvents() {
        let debounceTimer;
        
        this.input.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.handleSearch(e.target.value);
            }, this.debounceTime);
        });

        this.input.addEventListener('focus', () => {
            if (this.input.value.length >= this.minQueryLength) {
                this.showSuggestions();
            }
        });

        this.input.addEventListener('blur', () => {
            // Delay to allow clicking on suggestions
            setTimeout(() => this.hideSuggestions(), 150);
        });

        // Keyboard navigation
        this.input.addEventListener('keydown', (e) => {
            this.handleKeyNavigation(e);
        });

        // Close suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.input.parentNode.contains(e.target)) {
                this.hideSuggestions();
            }
        });
    }

    async handleSearch(query) {
        if (query.length < this.minQueryLength) {
            this.hideSuggestions();
            return;
        }

        // Check cache
        if (this.cache.has(query)) {
            this.displaySuggestions(this.cache.get(query));
            return;
        }

        try {
            const response = await fetch(`api/search-suggestions.php?q=${encodeURIComponent(query)}`);
            if (!response.ok) throw new Error('Search failed');

            const results = await response.json();
            
            // Cache results for 2 minutes
            this.cache.set(query, results);
            setTimeout(() => this.cache.delete(query), 2 * 60 * 1000);

            this.displaySuggestions(results);
        } catch (error) {
            console.error('Search error:', error);
            this.hideSuggestions();
        }
    }

    displaySuggestions(results) {
        if (!results || results.length === 0) {
            this.hideSuggestions();
            return;
        }

        this.suggestions.innerHTML = results.map(item => `
            <div class="suggestion-item-modern" data-url="${item.url}">
                <img src="${item.image}" alt="${item.name}" class="suggestion-image-modern">
                <div class="suggestion-info-modern">
                    <div class="suggestion-name-modern">${item.name}</div>
                    <div class="suggestion-price-modern">${item.price}</div>
                </div>
            </div>
        `).join('');

        // Bind click events
        this.suggestions.querySelectorAll('.suggestion-item-modern').forEach(item => {
            item.addEventListener('click', () => {
                window.location.href = item.dataset.url;
            });
        });

        this.showSuggestions();
    }

    showSuggestions() {
        this.suggestions.style.display = 'block';
        requestAnimationFrame(() => {
            this.suggestions.classList.add('show');
        });
    }

    hideSuggestions() {
        this.suggestions.classList.remove('show');
        setTimeout(() => {
            this.suggestions.style.display = 'none';
        }, 200);
    }

    handleKeyNavigation(e) {
        const items = this.suggestions.querySelectorAll('.suggestion-item-modern');
        const currentActive = this.suggestions.querySelector('.suggestion-item-modern.active');
        let newActive = null;

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (currentActive) {
                    newActive = currentActive.nextElementSibling || items[0];
                } else {
                    newActive = items[0];
                }
                break;

            case 'ArrowUp':
                e.preventDefault();
                if (currentActive) {
                    newActive = currentActive.previousElementSibling || items[items.length - 1];
                } else {
                    newActive = items[items.length - 1];
                }
                break;

            case 'Enter':
                e.preventDefault();
                if (currentActive) {
                    window.location.href = currentActive.dataset.url;
                } else {
                    // Submit search form
                    this.input.closest('form')?.submit();
                }
                break;

            case 'Escape':
                this.hideSuggestions();
                this.input.blur();
                break;
        }

        if (newActive) {
            items.forEach(item => item.classList.remove('active'));
            newActive.classList.add('active');
        }
    }
}

// ===== MODERN IMAGE LAZY LOADING =====
class ModernLazyLoader {
    constructor() {
        this.imageObserver = null;
        this.init();
    }

    init() {
        if ('IntersectionObserver' in window) {
            this.createObserver();
            this.observeImages();
        } else {
            this.loadAllImages();
        }
    }

    createObserver() {
        this.imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadImage(entry.target);
                    this.imageObserver.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });
    }

    observeImages() {
        document.querySelectorAll('img[data-src]').forEach(img => {
            this.imageObserver.observe(img);
        });
    }

    loadImage(img) {
        const src = img.dataset.src;
        if (!src) return;

        // Create a new image to preload
        const imageLoader = new Image();
        
        imageLoader.onload = () => {
            img.src = src;
            img.classList.remove('lazy');
            img.classList.add('loaded');
        };

        imageLoader.onerror = () => {
            img.classList.add('error');
            console.error('Failed to load image:', src);
        };

        imageLoader.src = src;
    }

    loadAllImages() {
        document.querySelectorAll('img[data-src]').forEach(img => {
            this.loadImage(img);
        });
    }

    // Method to add new images to observer
    observe(img) {
        if (this.imageObserver && img.dataset.src) {
            this.imageObserver.observe(img);
        }
    }
}

// ===== MODERN FORM VALIDATION =====
class ModernFormValidator {
    constructor(form) {
        this.form = typeof form === 'string' ? document.querySelector(form) : form;
        this.rules = new Map();
        this.errors = new Map();
        
        if (this.form) {
            this.init();
        }
    }

    init() {
        this.bindEvents();
        this.setupDefaultRules();
    }

    setupDefaultRules() {
        // Email validation
        this.addRule('email', (value) => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(value) || 'Email không hợp lệ';
        });

        // Phone validation
        this.addRule('phone', (value) => {
            const phoneRegex = /^[0-9]{10,11}$/;
            return phoneRegex.test(value) || 'Số điện thoại không hợp lệ';
        });

        // Required validation
        this.addRule('required', (value) => {
            return value.trim() !== '' || 'Trường này là bắt buộc';
        });

        // Minimum length
        this.addRule('minLength', (value, min) => {
            return value.length >= min || `Tối thiểu ${min} ký tự`;
        });

        // Maximum length
        this.addRule('maxLength', (value, max) => {
            return value.length <= max || `Tối đa ${max} ký tự`;
        });
    }

    addRule(name, validator) {
        this.rules.set(name, validator);
    }

    bindEvents() {
        // Real-time validation
        this.form.addEventListener('input', (e) => {
            if (e.target.matches('input, textarea, select')) {
                this.validateField(e.target);
            }
        });

        // Form submission
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                this.showValidationSummary();
            }
        });
    }

    validateField(field) {
        const value = field.value;
        const rules = this.getFieldRules(field);
        const errors = [];

        rules.forEach(rule => {
            const result = rule.validator(value, rule.param);
            if (result !== true) {
                errors.push(result);
            }
        });

        this.updateFieldUI(field, errors);
        
        if (errors.length > 0) {
            this.errors.set(field.name, errors);
        } else {
            this.errors.delete(field.name);
        }

        return errors.length === 0;
    }

    getFieldRules(field) {
        const rules = [];
        
        // Check data attributes
        Object.keys(field.dataset).forEach(key => {
            if (key.startsWith('validate')) {
                const ruleName = key.replace('validate', '').toLowerCase();
                const rule = this.rules.get(ruleName);
                
                if (rule) {
                    rules.push({
                        validator: rule,
                        param: field.dataset[key]
                    });
                }
            }
        });

        // Check HTML5 attributes
        if (field.required) {
            rules.push({ validator: this.rules.get('required') });
        }

        if (field.type === 'email') {
            rules.push({ validator: this.rules.get('email') });
        }

        if (field.minLength) {
            rules.push({ 
                validator: this.rules.get('minLength'),
                param: field.minLength
            });
        }

        if (field.maxLength) {
            rules.push({ 
                validator: this.rules.get('maxLength'),
                param: field.maxLength
            });
        }

        return rules;
    }

    updateFieldUI(field, errors) {
        const container = field.closest('.form-group, .form-group-modern');
        if (!container) return;

        // Remove existing error classes and messages
        field.classList.remove('is-valid', 'is-invalid');
        container.querySelectorAll('.field-error').forEach(el => el.remove());

        if (errors.length > 0) {
            field.classList.add('is-invalid');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error text-danger small mt-1';
            errorDiv.textContent = errors[0]; // Show first error
            container.appendChild(errorDiv);
        } else if (field.value.trim() !== '') {
            field.classList.add('is-valid');
        }
    }

    validateForm() {
        const fields = this.form.querySelectorAll('input, textarea, select');
        let isValid = true;

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    showValidationSummary() {
        if (this.errors.size > 0) {
            const errorMessages = Array.from(this.errors.values()).flat();
            notificationSystem.show(
                `Vui lòng sửa ${this.errors.size} lỗi trong form`,
                'error'
            );
        }
    }
}

// ===== MODERN SMOOTH SCROLL =====
class ModernSmoothScroll {
    constructor() {
        this.init();
    }

    init() {
        // Handle anchor links
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href^="#"]');
            if (link && link.hash) {
                e.preventDefault();
                this.scrollToTarget(link.hash);
            }
        });

        // Handle back to top
        this.createBackToTop();
    }

    scrollToTarget(target) {
        const element = document.querySelector(target);
        if (element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    createBackToTop() {
        const backToTop = document.createElement('button');
        backToTop.className = 'back-to-top-modern';
        backToTop.innerHTML = '⬆️';
        backToTop.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-primary);
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        `;

        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        document.body.appendChild(backToTop);

        // Show/hide based on scroll position
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTop.style.opacity = '1';
                backToTop.style.visibility = 'visible';
            } else {
                backToTop.style.opacity = '0';
                backToTop.style.visibility = 'hidden';
            }
        });
    }
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
    // Initialize modern features
    window.modernCart = new ModernCart();
    window.modernSearch = new ModernSearch('.search-input, .search-input-modern');
    window.modernLazyLoader = new ModernLazyLoader();
    window.modernSmoothScroll = new ModernSmoothScroll();

    // Initialize form validators
    document.querySelectorAll('form[data-validate]').forEach(form => {
        new ModernFormValidator(form);
    });

    // Set global variables
    window.isUserLoggedIn = document.body.dataset.userLoggedIn === 'true';
    window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    console.log('🐻 Modern enhancements loaded successfully!');
});

// ===== GLOBAL UTILITIES =====
window.TeddyShopModern = {
    // Modern notification
    notify: (message, type = 'info', duration = 5000) => {
        return notificationSystem.show(message, type, duration);
    },

    // Modern cart operations
    addToCart: async (productId, quantity = 1) => {
        return await window.modernCart.addToCart(productId, quantity);
    },

    // Modern image loading
    loadImage: (img) => {
        window.modernLazyLoader.observe(img);
    },

    // Modern smooth scroll
    scrollTo: (target) => {
        window.modernSmoothScroll.scrollToTarget(target);
    },

    // Format price
    formatPrice: (price) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    },

    // Debounce utility
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Throttle utility
    throttle: (func, limit) => {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        }
    }
};