<!-- Cute Footer -->
<footer class="footer text-center py-4">
    <div class="footer-brand mb-2">
        <span style="font-size:2rem;">🧸</span>
        <span class="footer-title" style="font-family: 'Fredoka One',cursive; font-size:2rem; color:#e84393; font-weight:bold;">Teddy Bear Paradise</span>
        <span style="font-size:2rem;">🧸</span>
    </div>
    <div class="footer-desc mb-2">Gấu bông xinh xắn - Quà tặng yêu thương!</div>
    <div class="footer-icons mb-3">
        <span style="font-size:1.5rem;">📱</span>
        <span style="font-size:1.5rem;">📷</span>
        <span style="font-size:1.5rem;">🎵</span>
    </div>
    <div class="footer-copyright">
        <span style="color:#e84393; font-weight:bold;">
            © 2026 Teddy Bear Paradise - Đồ án cá nhân. 🧸
        </span>
        <span class="text-muted" style="font-size:13px;"> All rights reserved.</span>
    </div>
</footer>

<!-- FIXED: JavaScript files với proper loading order -->
<script src="<?php echo SITE_URL; ?>/js/search.js"></script>
<script src="<?php echo SITE_URL; ?>/js/main.js"></script>

<!-- FIXED: Footer JavaScript với proper error handling -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Scroll to top functionality
        const scrollBtn = document.getElementById('scrollToTop');
        if (scrollBtn) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 300) {
                    scrollBtn.style.opacity = '1';
                    scrollBtn.style.visibility = 'visible';
                    scrollBtn.style.transform = 'translateY(0)';
                } else {
                    scrollBtn.style.opacity = '0';
                    scrollBtn.style.visibility = 'hidden';
                    scrollBtn.style.transform = 'translateY(20px)';
                }
            });
            
            scrollBtn.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
        
        // Scroll progress bar
        const progressBar = document.querySelector('.scroll-progress');
        if (progressBar) {
            window.addEventListener('scroll', function() {
                const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                progressBar.style.width = scrolled + '%';
            });
        }
        
        // Animation for footer links
        const footerLinks = document.querySelectorAll('footer a');
        footerLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
        
        // Social links hover effect
        const socialLinks = document.querySelectorAll('.social-links a');
        socialLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.2) translateY(-3px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1) translateY(0)';
            });
        });
        
        // Cart count update animation
        const cartCount = document.getElementById('cartCount');
        if (cartCount) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' || mutation.type === 'characterData') {
                        cartCount.style.animation = 'bounce 0.5s ease';
                        setTimeout(() => {
                            cartCount.style.animation = '';
                        }, 500);
                    }
                });
            });
            
            observer.observe(cartCount, {
                childList: true,
                characterData: true,
                subtree: true
            });
        }
        
        // Initialize cart count on page load
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }
        
        // Add pulse animation to notification badges
        const badges = document.querySelectorAll('.badge');
        badges.forEach(badge => {
            if (parseInt(badge.textContent) > 0) {
                badge.style.animation = 'pulse 2s infinite';
            }
        });
        
        console.log('✅ Footer JavaScript initialized successfully!');
        
    } catch (error) {
        console.error('❌ Footer JavaScript error:', error);
    }
});

// Global cart functions
window.updateCartCount = function(count) {
    try {
        const cartBadge = document.getElementById('cartCount');
        if (cartBadge && typeof count !== 'undefined') {
            cartBadge.textContent = count;
            cartBadge.style.animation = 'bounce 0.5s ease';
            setTimeout(() => {
                cartBadge.style.animation = '';
            }, 500);
        }
    } catch (error) {
        console.error('Update cart count error:', error);
    }
};

// Global notification function for backward compatibility
if (!window.showCuteNotification) {
    window.showCuteNotification = function(message, type = 'info') {
        try {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible`;
            notification.style.cssText = `
                position: fixed; 
                top: 20px; 
                right: 20px; 
                z-index: 10000; 
                max-width: 400px; 
                animation: slideInRight 0.3s ease;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            `;
            notification.innerHTML = `
                <span class="me-2">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
            
        } catch (error) {
            console.error('Notification error:', error);
        }
    };
}

// CSS animations for notifications
if (!document.getElementById('footer-animations')) {
    const animationStyles = document.createElement('style');
    animationStyles.id = 'footer-animations';
    animationStyles.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
    }

    @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        @keyframes bounce {
            0%, 20%, 60%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            80% { transform: translateY(-5px); }
        }
        
        footer .social-links a {
            transition: all 0.3s ease;
        }
        
        footer ul li a {
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .payment-methods i {
            font-size: 1.5rem;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }
        
        .payment-methods i:hover {
            opacity: 1;
        }
    `;
    document.head.appendChild(animationStyles);
}

// Global cart count updater
function forceUpdateCartCount() {
    fetch('<?php echo SITE_URL; ?>/cart/get-cart-count.php')
    .then(response => response.json())
    .then(data => {
        console.log('[CartCount] API response:', data);
        const cartBadge = document.getElementById('cartCount');
        if (!cartBadge) {
            console.error('[CartCount] Badge element not found!');
            return;
        }
        const count = parseInt(data.cart_count) || 0;
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'flex' : 'none';
        cartBadge.style.visibility = 'visible';
        cartBadge.style.opacity = '1';
        cartBadge.style.background = 'red';
        cartBadge.style.color = 'white';
        cartBadge.style.zIndex = '1001';
        console.log('[CartCount] Badge updated:', cartBadge, 'Count:', count);
    })
    .catch(error => {
        console.error('[CartCount] Fetch error:', error);
    });
}
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(forceUpdateCartCount, 500);
});
</script>

<?php include __DIR__ . '/ai-chatbot.php'; ?>