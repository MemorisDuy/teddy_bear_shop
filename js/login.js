/**
 * LOGIN PAGE JAVASCRIPT - Teddy Bear Shop 🔐
 * Complete & Final Version - Production Ready
 * Path: js/login.js
 * Version: 3.0 - Full Featured
 */

// ===== LOCKOUT MANAGEMENT =====
function checkLockoutStatus() {
    const lockoutTime = localStorage.getItem('lockout_time');
    const attempts = localStorage.getItem('login_attempts');
    
    if (lockoutTime && Date.now() < parseInt(lockoutTime)) {
        const remainingTime = Math.ceil((parseInt(lockoutTime) - Date.now()) / 60000);
        showNotification(
            `🔒 Tài khoản bị khóa. Vui lòng thử lại sau ${remainingTime} phút.`, 
            'error'
        );
        disableLoginForm();
        
        const timeUntilUnlock = parseInt(lockoutTime) - Date.now();
        setTimeout(() => {
            enableLoginForm();
            localStorage.removeItem('lockout_time');
            localStorage.removeItem('login_attempts');
            showNotification('🔓 Tài khoản đã được mở khóa. Bạn có thể đăng nhập lại.', 'success');
        }, timeUntilUnlock);
        
        showLockoutCountdown(remainingTime);
    } else if (attempts) {
        loginAttempts = parseInt(attempts);
        
        if (loginAttempts > 0) {
            const remaining = CONFIG.maxLoginAttempts - loginAttempts;
            showNotification(
                `⚠️ Bạn còn ${remaining} lần thử đăng nhập.`, 
                'info'
            );
        }
    }
}

function showLockoutCountdown(minutes) {
    const countdownElement = document.createElement('div');
    countdownElement.className = 'lockout-countdown';
    countdownElement.innerHTML = `
        <div style="text-align: center; padding: 15px; background: rgba(220, 53, 69, 0.1); border-radius: 10px; margin: 10px 0;">
            <span style="color: #dc3545; margin-right: 5px;">⏰</span>
            <span id="countdown-timer">${minutes}:00</span>
        </div>
    `;
    
    const form = document.querySelector('.login-form');
    if (form) {
        form.insertBefore(countdownElement, form.firstChild);
        startCountdown(minutes * 60, countdownElement);
    }
}

function startCountdown(seconds, element) {
    const timer = setInterval(() => {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        const display = `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        
        const timerElement = element.querySelector('#countdown-timer');
        if (timerElement) {
            timerElement.textContent = display;
        }
        
        if (seconds <= 0) {
            clearInterval(timer);
            element.remove();
        }
        
        seconds--;
    }, 1000);
}

function isLockedOut() {
    const lockoutTime = localStorage.getItem('lockout_time');
    return lockoutTime && Date.now() < parseInt(lockoutTime);
}

function disableLoginForm() {
    const form = document.querySelector('.login-form');
    const inputs = form.querySelectorAll('input, button');
    const submitBtn = form.querySelector('.btn-login');
    
    inputs.forEach(input => {
        input.disabled = true;
        input.style.opacity = '0.5';
    });
    
    if (submitBtn) {
        submitBtn.innerHTML = '🔒 Tài khoản bị khóa';
        submitBtn.style.background = '#6c757d';
    }
}

function enableLoginForm() {
    const form = document.querySelector('.login-form');
    const inputs = form.querySelectorAll('input, button');
    const submitBtn = form.querySelector('.btn-login');
    
    inputs.forEach(input => {
        input.disabled = false;
        input.style.opacity = '1';
    });
    
    if (submitBtn) {
        submitBtn.innerHTML = '🔐 Đăng nhập';
        submitBtn.style.background = '';
    }
    
    const countdown = document.querySelector('.lockout-countdown');
    if (countdown) countdown.remove();
}

// ===== FORM ANIMATIONS =====
function initializeFormAnimations() {
    if (!CONFIG.enableAnimations) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach((group, index) => {
        group.style.animationDelay = `${index * 0.1}s`;
        observer.observe(group);
    });
    
    const logo = document.querySelector('.logo-circle');
    if (logo) {
        setTimeout(() => {
            logo.style.animation = 'bounceIn 0.8s ease';
        }, 200);
    }
    
    const card = document.querySelector('.login-card');
    if (card) {
        setTimeout(() => {
            card.style.animation = 'slideInUp 0.6s ease, float 6s ease-in-out infinite 1s';
        }, 100);
    }
    
    console.log('✅ Form animations initialized');
}

// ===== ACCESSIBILITY =====
function initializeAccessibility() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => notification.remove());
            
            const modal = document.querySelector('.modal.show');
            if (modal && typeof bootstrap !== 'undefined') {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) modalInstance.hide();
            }
        }
        
        if (e.key === 'Enter' && e.target.type === 'checkbox') {
            e.target.checked = !e.target.checked;
            e.target.dispatchEvent(new Event('change'));
        }
        
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            const submitBtn = document.querySelector('.btn-login');
            if (submitBtn && !submitBtn.disabled && !isFormSubmitting) {
                submitBtn.click();
            }
        }
        
        if (e.altKey && e.key === 'u') {
            e.preventDefault();
            quickLogin('user');
        }
        
        if (e.altKey && e.key === 'a') {
            e.preventDefault();
            quickLogin('admin');
        }
    });
    
    setupFocusManagement();
    
    if (!document.getElementById('announcer')) {
        const announcer = document.createElement('div');
        announcer.id = 'announcer';
        announcer.className = 'sr-only';
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        document.body.appendChild(announcer);
    }
    
    if (window.matchMedia && window.matchMedia('(prefers-contrast: high)').matches) {
        document.body.classList.add('high-contrast');
    }
    
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        CONFIG.enableAnimations = false;
        document.body.classList.add('reduced-motion');
    }
    
    console.log('✅ Accessibility features initialized');
}

function setupFocusManagement() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            trapFocus(this);
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            const trigger = document.querySelector('[data-bs-target="#' + this.id + '"]');
            if (trigger) trigger.focus();
        });
    });
    
    addSkipLink();
}

function trapFocus(element) {
    const focusableElements = element.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    if (focusableElements.length === 0) return;
    
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    
    element.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        }
    });
    
    firstElement.focus();
}

function addSkipLink() {
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.textContent = 'Chuyển đến nội dung chính';
    skipLink.className = 'skip-link sr-only';
    skipLink.style.cssText = `
        position: fixed;
        top: -40px;
        left: 6px;
        background: #000;
        color: #fff;
        padding: 8px;
        z-index: 10000;
        text-decoration: none;
        border-radius: 4px;
        transition: top 0.3s;
    `;
    
    skipLink.addEventListener('focus', function() {
        this.style.top = '6px';
        this.classList.remove('sr-only');
    });
    
    skipLink.addEventListener('blur', function() {
        this.style.top = '-40px';
        this.classList.add('sr-only');
    });
    
    document.body.insertBefore(skipLink, document.body.firstChild);
    
    const loginCard = document.querySelector('.login-card');
    if (loginCard) {
        loginCard.id = 'main-content';
        loginCard.setAttribute('tabindex', '-1');
    }
}

// ===== SECURITY FEATURES =====
function initializeSecurityFeatures() {
    if (!CONFIG.enableSecurityFeatures) return;
    
    monitorSecurityEvents();
    setupInputSanitization();
    setupCSPHandling();
    
    console.log('✅ Security features initialized');
}

function monitorSecurityEvents() {
    let rapidClicks = 0;
    let lastClickTime = 0;
    
    document.addEventListener('click', function() {
        const now = Date.now();
        if (now - lastClickTime < 100) {
            rapidClicks++;
            if (rapidClicks > 10) {
                console.warn('Rapid clicking detected');
                trackEvent('security_rapid_clicks');
            }
        } else {
            rapidClicks = 0;
        }
        lastClickTime = now;
    });
    
    let keystrokes = [];
    document.addEventListener('keydown', function(e) {
        keystrokes.push(Date.now());
        keystrokes = keystrokes.slice(-20);
        
        if (keystrokes.length >= 10) {
            const intervals = [];
            for (let i = 1; i < keystrokes.length; i++) {
                intervals.push(keystrokes[i] - keystrokes[i - 1]);
            }
            
            const avgInterval = intervals.reduce((a, b) => a + b) / intervals.length;
            const variance = intervals.reduce((sum, interval) => sum + Math.pow(interval - avgInterval, 2), 0) / intervals.length;
            
            if (variance < 100 && avgInterval < 50) {
                console.warn('Automated typing detected');
                trackEvent('security_automated_typing');
            }
        }
    });
}

function setupInputSanitization() {
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.type !== 'password') {
                this.value = this.value.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
                this.value = this.value.replace(/javascript:/gi, '');
                this.value = this.value.replace(/on\w+\s*=/gi, '');
            }
        });
    });
}

function setupCSPHandling() {
    document.addEventListener('securitypolicyviolation', function(e) {
        console.warn('CSP Violation:', e);
        trackEvent('security_csp_violation', {
            blocked_uri: e.blockedURI,
            violated_directive: e.violatedDirective
        });
    });
}

// ===== PERFORMANCE OPTIMIZATIONS =====
function initializePerformanceOptimizations() {
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResize, 250);
    });
    
    setupLazyLoading();
    
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (!scrollTimeout) {
            scrollTimeout = setTimeout(function() {
                handleScroll();
                scrollTimeout = null;
            }, 100);
        }
    });
    
    preconnectToCommonDomains();
    
    console.log('✅ Performance optimizations initialized');
}

function handleResize() {
    const card = document.querySelector('.login-card');
    if (card && window.innerWidth < 768) {
        card.style.margin = '10px';
    } else if (card) {
        card.style.margin = '0 auto';
    }
}

function setupLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    if (images.length === 0) return;
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

function handleScroll() {
    const scrollY = window.scrollY;
    const card = document.querySelector('.login-card');
    
    if (card && CONFIG.enableAnimations) {
        const parallaxEffect = scrollY * 0.1;
        card.style.transform = `translateY(${parallaxEffect}px)`;
    }
}

function preconnectToCommonDomains() {
    const domains = [
        'https://fonts.googleapis.com',
        'https://cdnjs.cloudflare.com',
        'https://cdn.jsdelivr.net'
    ];
    
    domains.forEach(domain => {
        const link = document.createElement('link');
        link.rel = 'preconnect';
        link.href = domain;
        document.head.appendChild(link);
    });
}

// ===== GLOBAL EVENT LISTENERS =====
function initializeGlobalEventListeners() {
    window.addEventListener('online', function() {
        showNotification('🌐 Kết nối internet đã được khôi phục', 'success');
    });
    
    window.addEventListener('offline', function() {
        showNotification('⚠️ Mất kết nối internet. Một số tính năng có thể không hoạt động.', 'error');
    });
    
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            trackEvent('page_hidden');
        } else {
            trackEvent('page_visible');
            checkSessionTimeout();
        }
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (isFormSubmitting) {
            e.preventDefault();
            e.returnValue = 'Đang xử lý đăng nhập. Bạn có chắc muốn rời khỏi trang?';
            return e.returnValue;
        }
    });
    
    document.addEventListener('paste', function(e) {
        const target = e.target;
        if (target.type === 'password') {
            setTimeout(() => {
                showNotification('⚠️ Hãy cẩn thận khi dán mật khẩu từ clipboard', 'info');
            }, 100);
        }
    });
    
    console.log('✅ Global event listeners initialized');
}

function checkSessionTimeout() {
    const sessionData = sessionStorage.getItem('user_session');
    if (sessionData) {
        try {
            const session = JSON.parse(sessionData);
            const now = Date.now();
            const lastActivity = session.last_activity || session.login_time;
            const sessionTimeout = 30 * 60 * 1000;
            
            if (now - lastActivity > sessionTimeout) {
                sessionStorage.removeItem('user_session');
                showNotification('⏰ Phiên đăng nhập đã hết hạn', 'info');
            } else {
                session.last_activity = now;
                sessionStorage.setItem('user_session', JSON.stringify(session));
            }
        } catch (error) {
            console.error('Error checking session timeout:', error);
        }
    }
}

// ===== URL PARAMETERS HANDLING =====
function handleURLParameters() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('registered') === 'true') {
        showNotification('🎉 Đăng ký thành công! Vui lòng đăng nhập.', 'success');
        
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    if (urlParams.get('logout') === 'true') {
        showNotification('👋 Đã đăng xuất thành công!', 'info');
        
        sessionStorage.removeItem('user_session');
        
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    const errorMessage = urlParams.get('error');
    if (errorMessage) {
        showNotification(decodeURIComponent(errorMessage), 'error');
        
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    const emailParam = urlParams.get('email');
    if (emailParam && isValidEmail(emailParam)) {
        const emailField = document.getElementById('email');
        if (emailField) {
            emailField.value = emailParam;
            validateField(emailField);
            
            const passwordField = document.getElementById('password');
            if (passwordField) {
                setTimeout(() => passwordField.focus(), 500);
            }
        }
    }
}

// ===== DEMO DATA SETUP =====
function setupDemoData() {
    if (!CONFIG.enableDemoAccounts) return;
    
    localStorage.setItem('demo_accounts', JSON.stringify(DEMO_ACCOUNTS));
    
    const demoInfo = document.createElement('div');
    demoInfo.className = 'demo-info';
    demoInfo.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 20px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 0.8rem;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
        max-width: 300px;
    `;
    
    demoInfo.innerHTML = `
        <div style="font-weight: 600; margin-bottom: 5px;">🎭 Demo Mode Active</div>
        <div>Alt+U: User Login | Alt+A: Admin Login</div>
    `;
    
    document.body.appendChild(demoInfo);
    
    setTimeout(() => {
        demoInfo.style.opacity = '1';
        setTimeout(() => {
            demoInfo.style.opacity = '0';
        }, 5000);
    }, 2000);
    
    console.log('✅ Demo data setup complete');
}

// ===== CLEANUP =====
function cleanup() {
    if (formValidationTimeout) {
        clearTimeout(formValidationTimeout);
    }
    
    console.log('🧹 Cleanup completed');
}

window.addEventListener('beforeunload', cleanup);

// ===== INITIALIZATION COMPLETE =====
console.log('🚀 Login JavaScript fully loaded and ready!');
console.log('📋 Available demo accounts:');
DEMO_ACCOUNTS.forEach(account => {
    console.log(`   ${account.avatar} ${account.role}: ${account.email} / ${account.password}`);
});

console.log('⌨️ Keyboard shortcuts:');
console.log('   Ctrl/Cmd + Enter: Submit form');
console.log('   Alt + U: Quick login as user');
console.log('   Alt + A: Quick login as admin');
console.log('   Escape: Close modals/notifications');

if (performance && performance.mark) {
    performance.mark('login-js-loaded');
}

if (typeof window !== 'undefined') {
    window.TeddyLogin = {
        version: '3.0',
        showNotification,
        trackEvent,
        quickLogin,
        validateEmail: isValidEmail,
        checkLockout: isLockedOut,
        getDemoAccounts: () => DEMO_ACCOUNTS,
        getConfig: () => CONFIG,
        cleanup
    };
}

// End of JavaScript file== GLOBAL VARIABLES =====
let loginAttempts = 0;
const maxLoginAttempts = 5;
const lockoutTime = 15 * 60 * 1000;
let formValidationTimeout;
let isFormSubmitting = false;
let currentNotificationId = 0;

// ===== CONFIGURATION =====
const CONFIG = {
    enableSecurityFeatures: true,
    enableAnimations: true,
    enableSounds: false,
    autoFocusDelay: 500,
    notificationDuration: 5000,
    lockoutDuration: 15 * 60 * 1000,
    maxLoginAttempts: 5,
    passwordMinLength: 6,
    enableDemoAccounts: true,
    enableRealLogin: true
};

// ===== DEMO ACCOUNTS =====
const DEMO_ACCOUNTS = [
    { email: 'user@demo.com', password: '123456', role: 'user', name: 'Demo User', avatar: '👤' },
    { email: 'admin@teddyshop.com', password: 'admin123', role: 'admin', name: 'Admin User', avatar: '👑' },
    { email: 'manager@teddyshop.com', password: 'manager123', role: 'manager', name: 'Manager User', avatar: '👨‍💼' }
];

// ===== DOCUMENT READY =====
document.addEventListener('DOMContentLoaded', initializeLoginPage);

// ===== MAIN INITIALIZATION =====
function initializeLoginPage() {
    console.log('🔐 Login page loaded - Teddy Bear Shop v3.0!');
    
    try {
        initializeFormValidation();
        initializeFormAnimations();
        initializePasswordToggle();
        initializeAccessibility();
        initializeQuickLogin();
        initializeSecurityFeatures();
        initializePerformanceOptimizations();
        
        const emailField = document.getElementById('email');
        if (emailField) {
            setTimeout(() => {
                emailField.focus();
                announceToScreenReader('Trang đăng nhập đã sẵn sàng');
            }, CONFIG.autoFocusDelay);
        }
        
        checkLockoutStatus();
        initializeRememberMe();
        setupDemoData();
        initializeGlobalEventListeners();
        handleURLParameters();
        
        console.log('✅ Login system fully initialized!');
        
        if (CONFIG.enableDemoAccounts) {
            console.log('🎭 Demo accounts available:', DEMO_ACCOUNTS.map(acc => `${acc.email} / ${acc.password}`));
        }
        
    } catch (error) {
        console.error('❌ Error initializing login page:', error);
        showNotification('Có lỗi xảy ra khi tải trang. Vui lòng tải lại.', 'error');
    }
}

// ===== FORM VALIDATION =====
function initializeFormValidation() {
    const form = document.querySelector('.login-form');
    if (!form) {
        console.warn('Login form not found');
        return;
    }
    
    form.addEventListener('submit', function(event) {
        console.log('Form submitted');
        
        if (!validateLoginForm()) {
            event.preventDefault();
            event.stopPropagation();
            showFormErrors();
            if (CONFIG.enableAnimations) shakeForm();
            form.classList.add('was-validated');
            return;
        }
        
        if (isFormSubmitting) {
            event.preventDefault();
            console.log('Form already submitting, ignoring...');
            return;
        }
        
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        
        // ALWAYS let PHP handle login FIRST - only use demo for quick login buttons
        if (CONFIG.enableRealLogin) {
            console.log('Real database login - letting PHP handle');
            
            // Check if this was triggered by a quick login button
            const isQuickLoginButton = event.target.closest('.demo-btn') || 
                                      event.submitter?.closest('.demo-btn') ||
                                      document.activeElement?.closest('.demo-btn');
            
            if (isQuickLoginButton) {
                // Only handle demo accounts if explicitly using quick login
                const isDemoAccount = CONFIG.enableDemoAccounts && 
                                     DEMO_ACCOUNTS.some(acc => 
                                         acc.email.toLowerCase() === email.toLowerCase() && 
                                         acc.password === password
                                     );
                
                if (isDemoAccount) {
                    event.preventDefault();
                    event.stopPropagation();
                    console.log('Quick login demo account, handling with JavaScript');
                    handleDemoLogin(email, password);
                } else {
                    console.log('Not a demo account, letting PHP handle');
                    showFormLoadingState();
                }
            } else {
                // Normal form submission - ALWAYS let PHP handle (for real accounts)
                console.log('Normal form submission - PHP will handle everything');
                showFormLoadingState();
                // Don't prevent default - let form submit to PHP
            }
        } else {
            event.preventDefault();
            showNotification('Tính năng đăng nhập chưa được kích hoạt', 'error');
        }
        
        form.classList.add('was-validated');
    });
    
    const inputs = form.querySelectorAll('.form-control-custom');
    inputs.forEach(input => {
        input.addEventListener('blur', () => validateField(input));
        input.addEventListener('input', () => {
            clearTimeout(formValidationTimeout);
            formValidationTimeout = setTimeout(() => validateField(input), 300);
        });
        input.addEventListener('paste', () => setTimeout(() => validateField(input), 100));
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const nextInput = getNextInput(input);
                if (nextInput) {
                    nextInput.focus();
                } else {
                    form.dispatchEvent(new Event('submit', { bubbles: true }));
                }
            }
        });
    });
    
    console.log('✅ Form validation initialized');
}

function validateLoginForm() {
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    
    let isValid = true;
    
    if (!validateField(email)) isValid = false;
    if (!validateField(password)) isValid = false;
    
    if (isValid && CONFIG.enableSecurityFeatures) {
        if (detectSuspiciousActivity()) isValid = false;
    }
    
    return isValid;
}

function validateField(field) {
    if (!field) return false;
    
    const value = field.value.trim();
    const fieldType = field.type;
    let isValid = true;
    let errorMessage = '';
    
    clearFieldState(field);
    
    if (field.hasAttribute('required') && !value) {
        errorMessage = `${getFieldLabel(field)} là bắt buộc`;
        isValid = false;
    }
    
    if (value && isValid) {
        switch (fieldType) {
            case 'email':
                if (!isValidEmail(value)) {
                    errorMessage = 'Email không hợp lệ';
                    isValid = false;
                } else if (value.length > 100) {
                    errorMessage = 'Email quá dài';
                    isValid = false;
                }
                break;
                
            case 'password':
                if (value.length < CONFIG.passwordMinLength) {
                    errorMessage = `Mật khẩu phải có ít nhất ${CONFIG.passwordMinLength} ký tự`;
                    isValid = false;
                } else if (value.length > 50) {
                    errorMessage = 'Mật khẩu quá dài';
                    isValid = false;
                } else if (!/^[\x20-\x7E]*$/.test(value)) {
                    errorMessage = 'Mật khẩu chứa ký tự không hợp lệ';
                    isValid = false;
                }
                break;
        }
    }
    
    if (isValid && value) {
        showFieldSuccess(field);
    } else if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function showFieldSuccess(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    
    const feedback = field.parentNode.querySelector('.valid-feedback');
    if (feedback) feedback.style.display = 'block';
    
    if (CONFIG.enableAnimations) {
        field.classList.add('pulse-success');
        setTimeout(() => field.classList.remove('pulse-success'), 600);
    }
    
    announceToScreenReader(`${getFieldLabel(field)} hợp lệ`);
}

function showFieldError(field, message) {
    field.classList.remove('is-valid');
    field.classList.add('is-invalid');
    
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
        feedback.style.display = 'block';
    }
    
    if (CONFIG.enableAnimations) {
        field.classList.add('shake');
        setTimeout(() => field.classList.remove('shake'), 500);
    }
    
    announceToScreenReader(`Lỗi: ${message}`);
}

function clearFieldState(field) {
    field.classList.remove('is-valid', 'is-invalid');
    const feedbacks = field.parentNode.querySelectorAll('.valid-feedback, .invalid-feedback');
    feedbacks.forEach(fb => fb.style.display = 'none');
}

function showFormErrors() {
    const firstInvalidField = document.querySelector('.form-control-custom.is-invalid');
    if (firstInvalidField) {
        firstInvalidField.focus();
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function getFieldLabel(field) {
    const placeholder = field.placeholder;
    if (placeholder) return placeholder;
    const label = document.querySelector(`label[for="${field.id}"]`);
    return label ? label.textContent.trim() : field.name || 'Trường';
}

function getNextInput(currentInput) {
    const inputs = Array.from(document.querySelectorAll('.form-control-custom'));
    const currentIndex = inputs.indexOf(currentInput);
    return inputs[currentIndex + 1] || null;
}

function shakeForm() {
    const form = document.querySelector('.login-card');
    if (form) {
        form.classList.add('shake');
        setTimeout(() => form.classList.remove('shake'), 500);
    }
}

// ===== DEMO LOGIN HANDLER =====
function handleDemoLogin(email, password) {
    if (isLockedOut()) {
        showNotification('Tài khoản tạm thời bị khóa. Vui lòng thử lại sau.', 'error');
        return;
    }
    
    const submitBtn = document.querySelector('.btn-login');
    if (!submitBtn) return;
    
    isFormSubmitting = true;
    showLoadingState(submitBtn);
    logLoginAttempt(email);
    
    const delay = Math.random() * 1000 + 1500;
    
    setTimeout(() => {
        try {
            const user = authenticateUser(email, password);
            
            if (user) {
                handleLoginSuccess(user, document.getElementById('remember')?.checked || false);
            } else {
                handleLoginFailure();
            }
        } catch (error) {
            console.error('Demo login error:', error);
            handleLoginError();
        } finally {
            hideLoadingState(submitBtn);
            isFormSubmitting = false;
        }
    }, delay);
}

function showFormLoadingState() {
    const submitBtn = document.querySelector('.btn-login');
    if (submitBtn) {
        showLoadingState(submitBtn);
        
        setTimeout(() => {
            if (submitBtn.classList.contains('loading')) {
                hideLoadingState(submitBtn);
                isFormSubmitting = false;
            }
        }, 10000);
    }
}

// ===== AUTHENTICATION =====
function authenticateUser(email, password) {
    if (!CONFIG.enableDemoAccounts) return null;
    
    return DEMO_ACCOUNTS.find(account => 
        account.email.toLowerCase() === email.toLowerCase() && 
        account.password === password
    );
}

function handleLoginSuccess(user, rememberMe) {
    loginAttempts = 0;
    localStorage.removeItem('login_attempts');
    localStorage.removeItem('lockout_time');
    
    const sessionData = {
        user_id: Date.now(),
        user_name: user.name,
        user_email: user.email,
        user_role: user.role,
        login_time: Date.now(),
        last_activity: Date.now()
    };
    
    sessionStorage.setItem('user_session', JSON.stringify(sessionData));
    
    if (rememberMe) {
        localStorage.setItem('saved_email', user.email);
        const rememberToken = btoa(`${user.email}|${Date.now()}`);
        localStorage.setItem('remember_token', rememberToken);
    } else {
        localStorage.removeItem('saved_email');
        localStorage.removeItem('remember_token');
    }
    
    const welcomeMessage = `🎉 Chào mừng ${user.avatar} ${user.name}! Đăng nhập thành công!`;
    showNotification(welcomeMessage, 'success');
    
    updateUIForSuccess(user);
    
    setTimeout(() => redirectAfterLogin(user), 2000);
    
    trackEvent('login_success', {
        user_role: user.role,
        remember_me: rememberMe
    });
}

function handleLoginFailure() {
    loginAttempts++;
    localStorage.setItem('login_attempts', loginAttempts.toString());
    
    if (loginAttempts >= CONFIG.maxLoginAttempts) {
        const lockoutUntil = Date.now() + CONFIG.lockoutDuration;
        localStorage.setItem('lockout_time', lockoutUntil.toString());
        
        const lockoutMinutes = CONFIG.lockoutDuration / 60000;
        showNotification(
            `⚠️ Quá nhiều lần đăng nhập sai. Tài khoản bị khóa ${lockoutMinutes} phút.`, 
            'error'
        );
        disableLoginForm();
        
        setTimeout(() => {
            enableLoginForm();
            localStorage.removeItem('lockout_time');
            localStorage.removeItem('login_attempts');
            showNotification('🔓 Tài khoản đã được mở khóa. Bạn có thể đăng nhập lại.', 'success');
        }, CONFIG.lockoutDuration);
    } else {
        const remaining = CONFIG.maxLoginAttempts - loginAttempts;
        showNotification(
            `❌ Email hoặc mật khẩu không chính xác! Còn ${remaining} lần thử.`, 
            'error'
        );
    }
    
    const passwordField = document.getElementById('password');
    if (passwordField) {
        passwordField.value = '';
        passwordField.focus();
    }
    
    trackEvent('login_failure', {
        attempts: loginAttempts,
        max_attempts: CONFIG.maxLoginAttempts
    });
}

function handleLoginError() {
    showNotification('🔧 Có lỗi hệ thống xảy ra. Vui lòng thử lại sau!', 'error');
    trackEvent('login_error');
}

function updateUIForSuccess(user) {
    const form = document.querySelector('.login-form');
    const card = document.querySelector('.login-card');
    
    if (form && card) {
        card.classList.add('login-success');
        
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => input.disabled = true);
        
        const successIndicator = document.createElement('div');
        successIndicator.className = 'success-indicator';
        successIndicator.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 3rem; margin-bottom: 10px;">${user.avatar}</div>
                <h4 style="color: #28a745; margin-bottom: 5px;">Đăng nhập thành công!</h4>
                <p style="color: #6c757d;">Đang chuyển hướng...</p>
                <div class="loading-spinner" style="margin: 20px auto; width: 30px; height: 30px;"></div>
            </div>
        `;
        
        form.appendChild(successIndicator);
    }
}

function redirectAfterLogin(user) {
    const redirectUrl = user.role === 'admin' ? '/admin/dashboard' : '/user/dashboard';
    
    showNotification(`🚀 Đang chuyển đến ${user.role === 'admin' ? 'Admin Dashboard' : 'User Dashboard'}...`, 'info');
    
    console.log(`🔄 Redirecting to: ${redirectUrl}`);
}

function showLoadingState(button) {
    button.disabled = true;
    button.classList.add('loading');
    const originalText = button.innerHTML;
    button.setAttribute('data-original-text', originalText);
    button.innerHTML = '⏳ Đang đăng nhập...';
}

function hideLoadingState(button) {
    button.disabled = false;
    button.classList.remove('loading');
    const originalText = button.getAttribute('data-original-text');
    if (originalText) {
        button.innerHTML = originalText;
    } else {
        button.innerHTML = '🔐 Đăng nhập';
    }
}

// ===== PASSWORD TOGGLE =====
function initializePasswordToggle() {
    const passwordField = document.getElementById('password');
    if (!passwordField) return;
    
    const wrapper = passwordField.parentNode;
    const toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.className = 'password-toggle';
    toggleBtn.innerHTML = '👁️';
    toggleBtn.title = 'Hiện/ẩn mật khẩu';
    toggleBtn.setAttribute('aria-label', 'Hiện mật khẩu');
    toggleBtn.setAttribute('tabindex', '0');
    
    toggleBtn.addEventListener('click', function() {
        const isPassword = passwordField.type === 'password';
        passwordField.type = isPassword ? 'text' : 'password';
        this.innerHTML = isPassword ? '🙈' : '👁️';
        this.setAttribute('aria-label', isPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        
        announceToScreenReader(isPassword ? 'Mật khẩu đã được hiển thị' : 'Mật khẩu đã được ẩn');
        
        passwordField.focus();
        
        setTimeout(() => {
            passwordField.setSelectionRange(passwordField.value.length, passwordField.value.length);
        }, 0);
    });
    
    toggleBtn.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
        }
    });
    
    wrapper.appendChild(toggleBtn);
    passwordField.style.paddingRight = '50px';
    
    console.log('✅ Password toggle initialized');
}

// ===== QUICK LOGIN DEMO =====
function initializeQuickLogin() {
    window.quickLogin = function(type) {
        const emailField = document.getElementById('email');
        const passwordField = document.getElementById('password');
        
        if (!emailField || !passwordField) {
            console.error('Form fields not found');
            return;
        }
        
        const account = DEMO_ACCOUNTS.find(acc => acc.role === type);
        if (!account) {
            showNotification(`❌ Không tìm thấy tài khoản demo cho ${type}`, 'error');
            return;
        }
        
        emailField.value = '';
        passwordField.value = '';
        
        clearFieldState(emailField);
        clearFieldState(passwordField);
        
        typeText(emailField, account.email, () => {
            typeText(passwordField, account.password, () => {
                validateField(emailField);
                validateField(passwordField);
                
                const loginBtn = document.querySelector('.btn-login');
                if (loginBtn) {
                    setTimeout(() => {
                        loginBtn.focus();
                        loginBtn.style.transform = 'scale(1.05)';
                        setTimeout(() => {
                            loginBtn.style.transform = '';
                        }, 200);
                    }, 300);
                }
            });
        });
        
        const message = `✨ Đã điền thông tin ${account.avatar} ${account.name}! Nhấn "Đăng nhập" để tiếp tục.`;
        showNotification(message, 'info');
        
        trackEvent('demo_login_used', { account_type: type });
    };
    
    console.log('✅ Quick login initialized');
}

function typeText(element, text, callback) {
    let index = 0;
    const typeInterval = setInterval(() => {
        element.value += text[index];
        element.dispatchEvent(new Event('input', { bubbles: true }));
        index++;
        
        if (index >= text.length) {
            clearInterval(typeInterval);
            if (callback) callback();
        }
    }, 100);
}

// ===== UTILITY FUNCTIONS =====
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email) && email.length <= 100;
}

function showNotification(message, type = 'info', duration = CONFIG.notificationDuration) {
    const existingNotifications = document.querySelectorAll(`.notification.${type}`);
    existingNotifications.forEach(notification => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    });
    
    const notificationId = ++currentNotificationId;
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.id = `notification-${notificationId}`;
    notification.setAttribute('role', 'alert');
    notification.setAttribute('aria-live', 'polite');
    
    const icon = type === 'success' ? '✅' : (type === 'error' ? '❌' : (type === 'warning' ? '⚠️' : 'ℹ️'));
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 1.2rem; flex-shrink: 0;">${icon}</span>
            <span style="flex: 1; line-height: 1.4;">${message}</span>
            <button class="close-btn" onclick="removeNotification('${notificationId}')" 
                    aria-label="Đóng thông báo" title="Đóng">
                &times;
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    
    if (duration > 0) {
        setTimeout(() => {
            removeNotification(notificationId);
        }, duration);
    }
    
    announceToScreenReader(message);
    
    trackEvent('notification_shown', { type, message: message.substring(0, 50) });
    
    return notificationId;
}

function removeNotification(notificationId) {
    const notification = document.getElementById(`notification-${notificationId}`);
    if (notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }
}

window.removeNotification = removeNotification;

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-triangle',
        info: 'info-circle',
        warning: 'exclamation-circle'
    };
    return icons[type] || 'info-circle';
}

function announceToScreenReader(message) {
    const announcer = document.getElementById('announcer');
    if (announcer) {
        announcer.textContent = '';
        
        setTimeout(() => {
            announcer.textContent = message;
        }, 100);
        
        setTimeout(() => {
            announcer.textContent = '';
        }, 3000);
    }
}

function showLoadingOverlay(message = 'Đang xử lý...') {
    hideLoadingOverlay();
    
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div style="text-align: center; color: #2c3e50;">
            <div class="loading-spinner"></div>
            <p style="margin-top: 15px; font-weight: 500;">${message}</p>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    setTimeout(() => {
        overlay.style.opacity = '1';
    }, 10);
}

function hideLoadingOverlay() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            if (overlay.parentElement) {
                overlay.remove();
            }
        }, 300);
    }
}

function trackEvent(eventName, data = {}) {
    const event = {
        name: eventName,
        data: data,
        timestamp: Date.now(),
        page: 'login',
        userAgent: navigator.userAgent,
        url: window.location.href
    };
    
    console.log('📊 Event tracked:', event);
    
    const events = JSON.parse(localStorage.getItem('tracked_events') || '[]');
    events.push(event);
    localStorage.setItem('tracked_events', JSON.stringify(events.slice(-100)));
}

function logLoginAttempt(email) {
    const attempts = JSON.parse(localStorage.getItem('login_history') || '[]');
    attempts.push({
        email: email,
        timestamp: Date.now(),
        userAgent: navigator.userAgent,
        ip: 'unknown'
    });
    
    localStorage.setItem('login_history', JSON.stringify(attempts.slice(-50)));
}

function detectSuspiciousActivity() {
    const submitTimes = JSON.parse(localStorage.getItem('submit_times') || '[]');
    const now = Date.now();
    const recentSubmits = submitTimes.filter(time => now - time < 10000);
    
    if (recentSubmits.length > 3) {
        showNotification('⚠️ Phát hiện hoạt động bất thường. Vui lòng chờ trước khi thử lại.', 'error');
        trackEvent('security_suspicious_activity', { recent_submits: recentSubmits.length });
        return true;
    }
    
    submitTimes.push(now);
    localStorage.setItem('submit_times', JSON.stringify(submitTimes.slice(-10)));
    
    return false;
}

// ===== SOCIAL LOGIN =====
function loginWithGoogle() {
    showNotification('🚧 Đăng nhập với Google đang được phát triển!', 'info');
    trackEvent('social_login_attempted', { provider: 'google' });
}

function loginWithFacebook() {
    showNotification('🚧 Đăng nhập với Facebook đang được phát triển!', 'info');
    trackEvent('social_login_attempted', { provider: 'facebook' });
}

window.loginWithGoogle = loginWithGoogle;
window.loginWithFacebook = loginWithFacebook;

// ===== FORGOT PASSWORD =====
function showForgotPasswordModal() {
    const modal = document.getElementById('forgotPasswordModal');
    if (modal && typeof bootstrap !== 'undefined') {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = modal.querySelector('input');
            if (firstInput) firstInput.focus();
        });
    } else {
        showNotification('🚧 Tính năng quên mật khẩu đang được phát triển!', 'info');
    }
    
    trackEvent('forgot_password_modal_opened');
}

function sendResetEmail() {
    const emailInput = document.getElementById('resetEmail');
    if (!emailInput) return;
    
    const email = emailInput.value.trim();
    
    if (!email || !isValidEmail(email)) {
        showNotification('❌ Vui lòng nhập email hợp lệ!', 'error');
        emailInput.focus();
        return;
    }
    
    showLoadingOverlay();
    
    setTimeout(() => {
        hideLoadingOverlay();
        showNotification('📧 Link đặt lại mật khẩu đã được gửi đến email của bạn!', 'success');
        
        if (typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
            if (modal) modal.hide();
        }
        
        emailInput.value = '';
        
        trackEvent('password_reset_requested', { email });
    }, 2000);
}

window.showForgotPasswordModal = showForgotPasswordModal;
window.sendResetEmail = sendResetEmail;

// ===== REMEMBER ME =====
function initializeRememberMe() {
    const savedEmail = localStorage.getItem('saved_email');
    const rememberToken = localStorage.getItem('remember_token');
    const rememberCheckbox = document.getElementById('remember');
    
    if (savedEmail) {
        const emailField = document.getElementById('email');
        if (emailField) {
            emailField.value = savedEmail;
            
            if (rememberCheckbox) {
                rememberCheckbox.checked = true;
            }
            
            setTimeout(() => {
                validateField(emailField);
            }, 100);
        }
    }
    
    if (rememberToken && savedEmail) {
        try {
            const tokenData = atob(rememberToken);
            const [email, timestamp] = tokenData.split('|');
            
            if (Date.now() - parseInt(timestamp) < 30 * 24 * 60 * 60 * 1000) {
                showNotification('🔄 Đã nhớ thông tin đăng nhập từ lần trước', 'info');
            } else {
                localStorage.removeItem('remember_token');
                localStorage.removeItem('saved_email');
            }
        } catch (error) {
            console.error('Error parsing remember token:', error);
            localStorage.removeItem('remember_token');
        }
    }
    
    console.log('✅ Remember me initialized');
}

// ===== LOCKOUT MANAGEMENT =====
function checkLockoutStatus() {
    const lockoutTime = localStorage.getItem('lockout_time');
    const attempts = localStorage.getItem('login_attempts');
    
    if (lockoutTime && Date.now() < parseInt(lockoutTime)) {
        const remainingTime = Math.ceil((parseInt(lockoutTime) - Date.now()) / 60000);
        showNotification(
            `🔒 Tài khoản bị khóa. Vui lòng thử lại sau ${remainingTime} phút.`, 
            'error'
        );
        disableLoginForm();
        
        const timeUntilUnlock = parseInt(lockoutTime) - Date.now();
        setTimeout(() => {
            enableLoginForm();
            localStorage.removeItem('lockout_time');
            localStorage.removeItem('login_attempts');
            showNotification('🔓 Tài khoản đã được mở khóa. Bạn có thể đăng nhập lại.', 'success');
        }, timeUntilUnlock);
        
        showLockoutCountdown(remainingTime);
    } else if (attempts) {
        loginAttempts = parseInt(attempts);
        
        if (loginAttempts > 0) {
            const remaining = CONFIG.maxLoginAttempts - loginAttempts;
            showNotification(
                `⚠️ Bạn còn ${remaining} lần thử đăng nhập.`, 
                'info'
            );
        }
    }
}

function showLockoutCountdown(minutes) {
    const countdownElement = document.createElement('div');
    countdownElement.className = 'lockout-countdown';
    countdownElement.innerHTML = `
        <div style="text-align: center; padding: 15px; background: rgba(220, 53, 69, 0.1); border-radius: 10px; margin: 10px 0;">
            <span style="color: #dc3545; margin-right: 5px;">⏰</span>
            <span id="countdown-timer">${minutes}:00</span>
        </div>
    `;
    
    const form = document.querySelector('.login-form');
    if (form) {
        form.insertBefore(countdownElement, form.firstChild);
        startCountdown(minutes * 60, countdownElement);
    }
}

function startCountdown(seconds, element) {
    const timer = setInterval(() => {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        const display = `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        
        const timerElement = element.querySelector('#countdown-timer');
        if (timerElement) {
            timerElement.textContent = display;
        }
        
        if (seconds <= 0) {
            clearInterval(timer);
            element.remove();
        }
        
        seconds--;
    }, 1000);
}

function isLockedOut() {
    const lockoutTime = localStorage.getItem('lockout_time');
    return lockoutTime && Date.now() < parseInt(lockoutTime);
}

function disableLoginForm() {
    const form = document.querySelector('.login-form');
    const inputs = form.querySelectorAll('input, button');
    const submitBtn = form.querySelector('.btn-login');
    
    inputs.forEach(input => {
        input.disabled = true;
        input.style.opacity = '0.5';
    });
    
    if (submitBtn) {
        submitBtn.innerHTML = '🔒 Tài khoản bị khóa';
        submitBtn.style.background = '#6c757d';
    }
}

function enableLoginForm() {
    const form = document.querySelector('.login-form');
    const inputs = form.querySelectorAll('input, button');
    const submitBtn = form.querySelector('.btn-login');
    
    inputs.forEach(input => {
        input.disabled = false;
        input.style.opacity = '1';
    });
    
    if (submitBtn) {
        submitBtn.innerHTML = '🔐 Đăng nhập';
        submitBtn.style.background = '';
    }
    
    const countdown = document.querySelector('.lockout-countdown');
    if (countdown) countdown.remove();
}

// ===== FORM ANIMATIONS =====
function initializeFormAnimations() {
    if (!CONFIG.enableAnimations) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach((group, index) => {
        group.style.animationDelay = `${index * 0.1}s`;
        observer.observe(group);
    });
    
    const logo = document.querySelector('.logo-circle');
    if (logo) {
        setTimeout(() => {
            logo.style.animation = 'bounceIn 0.8s ease';
        }, 200);
    }
    
    const card = document.querySelector('.login-card');
    if (card) {
        setTimeout(() => {
            card.style.animation = 'slideInUp 0.6s ease, float 6s ease-in-out infinite 1s';
        }, 100);
    }
    
    console.log('✅ Form animations initialized');
}

// ===== ACCESSIBILITY =====
function initializeAccessibility() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => notification.remove());
            
            const modal = document.querySelector('.modal.show');
            if (modal && typeof bootstrap !== 'undefined') {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) modalInstance.hide();
            }
        }
        
        if (e.key === 'Enter' && e.target.type === 'checkbox') {
            e.target.checked = !e.target.checked;
            e.target.dispatchEvent(new Event('change'));
        }
        
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            const submitBtn = document.querySelector('.btn-login');
            if (submitBtn && !submitBtn.disabled && !isFormSubmitting) {
                submitBtn.click();
            }
        }
        
        if (e.altKey && e.key === 'u') {
            e.preventDefault();
            quickLogin('user');
        }
        
        if (e.altKey && e.key === 'a') {
            e.preventDefault();
            quickLogin('admin');
        }
    });
    
    setupFocusManagement();
    
    if (!document.getElementById('announcer')) {
        const announcer = document.createElement('div');
        announcer.id = 'announcer';
        announcer.className = 'sr-only';
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        document.body.appendChild(announcer);
    }
    
    if (window.matchMedia && window.matchMedia('(prefers-contrast: high)').matches) {
        document.body.classList.add('high-contrast');
    }
    
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        CONFIG.enableAnimations = false;
        document.body.classList.add('reduced-motion');
    }
    
    console.log('✅ Accessibility features initialized');
}

function setupFocusManagement() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            trapFocus(this);
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            const trigger = document.querySelector('[data-bs-target="#' + this.id + '"]');
            if (trigger) trigger.focus();
        });
    });
    
    addSkipLink();
}

function trapFocus(element) {
    const focusableElements = element.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    if (focusableElements.length === 0) return;
    
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    
    element.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        }
    });
    
    firstElement.focus();
}

function addSkipLink() {
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.textContent = 'Chuyển đến nội dung chính';
    skipLink.className = 'skip-link sr-only';
    skipLink.style.cssText = `
        position: fixed;
        top: -40px;
        left: 6px;
        background: #000;
        color: #fff;
        padding: 8px;
        z-index: 10000;
        text-decoration: none;
        border-radius: 4px;
        transition: top 0.3s;
    `;
    
    skipLink.addEventListener('focus', function() {
        this.style.top = '6px';
        this.classList.remove('sr-only');
    });
    
    skipLink.addEventListener('blur', function() {
        this.style.top = '-40px';
        this.classList.add('sr-only');
    });
    
    document.body.insertBefore(skipLink, document.body.firstChild);
    
    const loginCard = document.querySelector('.login-card');
    if (loginCard) {
        loginCard.id = 'main-content';
        loginCard.setAttribute('tabindex', '-1');
    }
}

// ===== SECURITY FEATURES =====
function initializeSecurityFeatures() {
    if (!CONFIG.enableSecurityFeatures) return;
    
    monitorSecurityEvents();
    setupInputSanitization();
    setupCSPHandling();
    
    console.log('✅ Security features initialized');
}

function monitorSecurityEvents() {
    let rapidClicks = 0;
    let lastClickTime = 0;
    
    document.addEventListener('click', function() {
        const now = Date.now();
        if (now - lastClickTime < 100) {
            rapidClicks++;
            if (rapidClicks > 10) {
                console.warn('Rapid clicking detected');
                trackEvent('security_rapid_clicks');
            }
        } else {
            rapidClicks = 0;
        }
        lastClickTime = now;
    });
    
    let keystrokes = [];
    document.addEventListener('keydown', function(e) {
        keystrokes.push(Date.now());
        keystrokes = keystrokes.slice(-20);
        
        if (keystrokes.length >= 10) {
            const intervals = [];
            for (let i = 1; i < keystrokes.length; i++) {
                intervals.push(keystrokes[i] - keystrokes[i - 1]);
            }
            
            const avgInterval = intervals.reduce((a, b) => a + b) / intervals.length;
            const variance = intervals.reduce((sum, interval) => sum + Math.pow(interval - avgInterval, 2), 0) / intervals.length;
            
            if (variance < 100 && avgInterval < 50) {
                console.warn('Automated typing detected');
                trackEvent('security_automated_typing');
            }
        }
    });
}

function setupInputSanitization() {
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.type !== 'password') {
                this.value = this.value.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
                this.value = this.value.replace(/javascript:/gi, '');
                this.value = this.value.replace(/on\w+\s*=/gi, '');
            }
        });
    });
}

function setupCSPHandling() {
    document.addEventListener('securitypolicyviolation', function(e) {
        console.warn('CSP Violation:', e);
        trackEvent('security_csp_violation', {
            blocked_uri: e.blockedURI,
            violated_directive: e.violatedDirective
        });
    });
}

// ===== PERFORMANCE OPTIMIZATIONS =====
function initializePerformanceOptimizations() {
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResize, 250);
    });
    
    setupLazyLoading();
    
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (!scrollTimeout) {
            scrollTimeout = setTimeout(function() {
                handleScroll();
                scrollTimeout = null;
            }, 100);
        }
    });
    
    preconnectToCommonDomains();
    
    console.log('✅ Performance optimizations initialized');
}

function handleResize() {
    const card = document.querySelector('.login-card');
    if (card && window.innerWidth < 768) {
        card.style.margin = '10px';
    } else if (card) {
        card.style.margin = '0 auto';
    }
}

function setupLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    if (images.length === 0) return;
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

function handleScroll() {
    const scrollY = window.scrollY;
    const card = document.querySelector('.login-card');
    
    if (card && CONFIG.enableAnimations) {
        const parallaxEffect = scrollY * 0.1;
        card.style.transform = `translateY(${parallaxEffect}px)`;
    }
}

function preconnectToCommonDomains() {
    const domains = [
        'https://fonts.googleapis.com',
        'https://cdnjs.cloudflare.com',
        'https://cdn.jsdelivr.net'
    ];
    
    domains.forEach(domain => {
        const link = document.createElement('link');
        link.rel = 'preconnect';
        link.href = domain;
        document.head.appendChild(link);
    });
}

// ===== GLOBAL EVENT LISTENERS =====
function initializeGlobalEventListeners() {
    window.addEventListener('online', function() {
        showNotification('🌐 Kết nối internet đã được khôi phục', 'success');
    });
    
    window.addEventListener('offline', function() {
        showNotification('⚠️ Mất kết nối internet. Một số tính năng có thể không hoạt động.', 'error');
    });
    
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            trackEvent('page_hidden');
        } else {
            trackEvent('page_visible');
            checkSessionTimeout();
        }
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (isFormSubmitting) {
            e.preventDefault();
            e.returnValue = 'Đang xử lý đăng nhập. Bạn có chắc muốn rời khỏi trang?';
            return e.returnValue;
        }
    });
    
    document.addEventListener('paste', function(e) {
        const target = e.target;
        if (target.type === 'password') {
            setTimeout(() => {
                showNotification('⚠️ Hãy cẩn thận khi dán mật khẩu từ clipboard', 'info');
            }, 100);
        }
    });
    
    console.log('✅ Global event listeners initialized');
}

function checkSessionTimeout() {
    const sessionData = sessionStorage.getItem('user_session');
    if (sessionData) {
        try {
            const session = JSON.parse(sessionData);
            const now = Date.now();
            const lastActivity = session.last_activity || session.login_time;
            const sessionTimeout = 30 * 60 * 1000;
            
            if (now - lastActivity > sessionTimeout) {
                sessionStorage.removeItem('user_session');
                showNotification('⏰ Phiên đăng nhập đã hết hạn', 'info');
            } else {
                session.last_activity = now;
                sessionStorage.setItem('user_session', JSON.stringify(session));
            }
        } catch (error) {
            console.error('Error checking session timeout:', error);
        }
    }
}

// ===== URL PARAMETERS HANDLING =====
function handleURLParameters() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('registered') === 'true') {
        showNotification('🎉 Đăng ký thành công! Vui lòng đăng nhập.', 'success');
        
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    if (urlParams.get('logout') === 'true') {
        showNotification('👋 Đã đăng xuất thành công!', 'info');
        
        sessionStorage.removeItem('user_session');
        
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    const errorMessage = urlParams.get('error');
    if (errorMessage) {
        showNotification(decodeURIComponent(errorMessage), 'error');
        
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    const emailParam = urlParams.get('email');
    if (emailParam && isValidEmail(emailParam)) {
        const emailField = document.getElementById('email');
        if (emailField) {
            emailField.value = emailParam;
            validateField(emailField);
            
            const passwordField = document.getElementById('password');
            if (passwordField) {
                setTimeout(() => passwordField.focus(), 500);
            }
        }
    }
}

// ===== DEMO DATA SETUP =====
function setupDemoData() {
    if (!CONFIG.enableDemoAccounts) return;
    
    localStorage.setItem('demo_accounts', JSON.stringify(DEMO_ACCOUNTS));
    
    const demoInfo = document.createElement('div');
    demoInfo.className = 'demo-info';
    demoInfo.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 20px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 0.8rem;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
        max-width: 300px;
    `;
    
    demoInfo.innerHTML = `
        <div style="font-weight: 600; margin-bottom: 5px;">🎭 Demo Mode Active</div>
        <div>Alt+U: User Login | Alt+A: Admin Login</div>
    `;
    
    document.body.appendChild(demoInfo);
    
    setTimeout(() => {
        demoInfo.style.opacity = '1';
        setTimeout(() => {
            demoInfo.style.opacity = '0';
        }, 5000);
    }, 2000);
    
    console.log('✅ Demo data setup complete');
}

// ===== CLEANUP =====
function cleanup() {
    if (formValidationTimeout) {
        clearTimeout(formValidationTimeout);
    }
    
    console.log('🧹 Cleanup completed');
}

window.addEventListener('beforeunload', cleanup);

// ===== INITIALIZATION COMPLETE =====
console.log('🚀 Login JavaScript fully loaded and ready!');
console.log('📋 Available demo accounts:');
DEMO_ACCOUNTS.forEach(account => {
    console.log(`   ${account.avatar} ${account.role}: ${account.email} / ${account.password}`);
});

console.log('⌨️ Keyboard shortcuts:');
console.log('   Ctrl/Cmd + Enter: Submit form');
console.log('   Alt + U: Quick login as user');
console.log('   Alt + A: Quick login as admin');
console.log('   Escape: Close modals/notifications');

if (performance && performance.mark) {
    performance.mark('login-js-loaded');
}

if (typeof window !== 'undefined') {
    window.TeddyLogin = {
        version: '3.0',
        showNotification,
        trackEvent,
        quickLogin,
        validateEmail: isValidEmail,
        checkLockout: isLockedOut,
        getDemoAccounts: () => DEMO_ACCOUNTS,
        getConfig: () => CONFIG,
        cleanup
    };
}

// End of JavaScript file

// ===