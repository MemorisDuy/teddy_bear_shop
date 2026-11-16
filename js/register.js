/**
 * REGISTER PAGE JAVASCRIPT - Teddy Bear Shop 🐻
 * External JS file for register functionality
 * Path: js/register.js
 */

// ===== GLOBAL VARIABLES =====
let passwordStrengthTimeout;
let formValidationTimeout;

// ===== DOCUMENT READY =====
document.addEventListener('DOMContentLoaded', function() {
    initializeRegisterPage();
});

// ===== MAIN INITIALIZATION =====
function initializeRegisterPage() {
    console.log('🐻 Register page loaded!');
    
    // Initialize all components
    initializeFormValidation();
    initializePasswordStrength();
    initializePhoneFormatting();
    initializeFormAnimations();
    initializeAccessibility();
    
    // Auto focus first field
    const firstField = document.getElementById('fullName');
    if (firstField) {
        setTimeout(() => firstField.focus(), 500);
    }
    
    // Show welcome message if from homepage
    checkWelcomeMessage();
    
    // Initialize tooltips if needed
    initializeTooltips();
}

// ===== FORM VALIDATION =====
function initializeFormValidation() {
    const form = document.querySelector('.register-form');
    if (!form) return;
    
    // Bootstrap validation
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        if (validateForm()) {
            handleFormSubmission();
        } else {
            showFormErrors();
        }
        
        form.classList.add('was-validated');
    });
    
    // Real-time validation
    const inputs = form.querySelectorAll('.form-control-custom');
    inputs.forEach(input => {
        input.addEventListener('blur', () => validateField(input));
        input.addEventListener('input', () => clearTimeout(formValidationTimeout));
        input.addEventListener('input', () => {
            formValidationTimeout = setTimeout(() => validateField(input), 500);
        });
    });
    
    // Confirm password validation
    const confirmPassword = document.getElementById('confirmPassword');
    const password = document.getElementById('password');
    
    if (confirmPassword && password) {
        confirmPassword.addEventListener('input', () => validatePasswordMatch());
        password.addEventListener('input', () => validatePasswordMatch());
    }
}

function validateForm() {
    const form = document.querySelector('.register-form');
    const inputs = form.querySelectorAll('.form-control-custom[required]');
    const agreeTerms = document.getElementById('agreeTerms');
    
    let isValid = true;
    
    // Validate all required fields
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    // Validate password match
    if (!validatePasswordMatch()) {
        isValid = false;
    }
    
    // Validate terms agreement
    if (agreeTerms && !agreeTerms.checked) {
        showFieldError(agreeTerms, 'Bạn phải đồng ý với điều khoản sử dụng');
        isValid = false;
    }
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name;
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous states
    clearFieldState(field);
    
    // Required field check
    if (field.hasAttribute('required') && !value) {
        errorMessage = 'Trường này là bắt buộc';
        isValid = false;
    }
    
    // Specific field validations
    switch (fieldName) {
        case 'full_name':
            if (value && value.length < 2) {
                errorMessage = 'Họ tên phải có ít nhất 2 ký tự';
                isValid = false;
            }
            break;
            
        case 'email':
            if (value && !isValidEmail(value)) {
                errorMessage = 'Email không hợp lệ';
                isValid = false;
            }
            break;
            
        case 'phone':
            if (value && !isValidPhone(value)) {
                errorMessage = 'Số điện thoại phải có 10-11 chữ số';
                isValid = false;
            }
            break;
            
        case 'password':
            if (value && value.length < 6) {
                errorMessage = 'Mật khẩu phải có ít nhất 6 ký tự';
                isValid = false;
            }
            break;
    }
    
    // Show result
    if (isValid && value) {
        showFieldSuccess(field);
    } else if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function validatePasswordMatch() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    
    if (!password || !confirmPassword) return true;
    
    const isMatch = password.value === confirmPassword.value;
    const hasConfirmValue = confirmPassword.value.length > 0;
    
    clearFieldState(confirmPassword);
    
    if (hasConfirmValue) {
        if (isMatch) {
            showFieldSuccess(confirmPassword);
        } else {
            showFieldError(confirmPassword, 'Mật khẩu xác nhận không khớp');
            return false;
        }
    }
    
    return true;
}

function showFieldSuccess(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    
    const feedback = field.parentNode.querySelector('.valid-feedback');
    if (feedback) {
        feedback.style.display = 'block';
    }
    
    // Add success animation
    field.classList.add('pulse-success');
    setTimeout(() => field.classList.remove('pulse-success'), 600);
}

function showFieldError(field, message) {
    field.classList.remove('is-valid');
    field.classList.add('is-invalid');
    
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
        feedback.style.display = 'block';
    }
    
    // Add shake animation
    field.classList.add('shake');
    setTimeout(() => field.classList.remove('shake'), 500);
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

// ===== PASSWORD STRENGTH =====
function initializePasswordStrength() {
    const passwordField = document.getElementById('password');
    if (!passwordField) return;
    
    passwordField.addEventListener('input', function() {
        clearTimeout(passwordStrengthTimeout);
        passwordStrengthTimeout = setTimeout(() => {
            updatePasswordStrength(this.value);
        }, 300);
    });
}

function updatePasswordStrength(password) {
    const strengthContainer = document.querySelector('.password-strength');
    const progressBar = strengthContainer?.querySelector('.progress-bar');
    const strengthText = strengthContainer?.querySelector('.strength-text');
    
    if (!strengthContainer) return;
    
    if (password.length === 0) {
        strengthContainer.style.display = 'none';
        return;
    }
    
    strengthContainer.style.display = 'block';
    
    let strength = 0;
    let text = '';
    let colorClass = '';
    
    // Length checks
    if (password.length >= 6) strength += 25;
    if (password.length >= 10) strength += 25;
    
    // Character variety checks
    if (/[a-z]/.test(password)) strength += 12.5;
    if (/[A-Z]/.test(password)) strength += 12.5;
    if (/[0-9]/.test(password)) strength += 12.5;
    if (/[^A-Za-z0-9]/.test(password)) strength += 12.5;
    
    // Determine strength level
    if (strength < 30) {
        text = 'Yếu 😰';
        colorClass = 'bg-danger';
    } else if (strength < 60) {
        text = 'Trung bình 😐';
        colorClass = 'bg-warning';
    } else if (strength < 90) {
        text = 'Mạnh 😊';
        colorClass = 'bg-info';
    } else {
        text = 'Rất mạnh 🔒';
        colorClass = 'bg-success';
    }
    
    // Update UI
    if (progressBar) {
        progressBar.style.width = strength + '%';
        progressBar.className = 'progress-bar ' + colorClass;
    }
    
    if (strengthText) {
        strengthText.textContent = text;
        strengthText.className = 'strength-text small text-' + colorClass.replace('bg-', '');
    }
}

// ===== PHONE FORMATTING =====
function initializePhoneFormatting() {
    const phoneField = document.getElementById('phone');
    if (!phoneField) return;
    
    phoneField.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, ''); // Remove non-digits
        
        // Limit to 11 digits
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        
        this.value = value;
    });
}

// ===== FORM ANIMATIONS =====
function initializeFormAnimations() {
    // Animate form groups on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    });
    
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach(group => {
        observer.observe(group);
    });
}

// ===== ACCESSIBILITY =====
function initializeAccessibility() {
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Escape key closes modals
        if (e.key === 'Escape') {
            const modal = document.querySelector('.modal.show');
            if (modal) {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) modalInstance.hide();
            }
        }
        
        // Enter key on checkboxes
        if (e.key === 'Enter' && e.target.type === 'checkbox') {
            e.target.checked = !e.target.checked;
            e.target.dispatchEvent(new Event('change'));
        }
    });
    
    // Screen reader announcements
    setupScreenReaderAnnouncements();
}

function setupScreenReaderAnnouncements() {
    const form = document.querySelector('.register-form');
    if (!form) return;
    
    // Create announcement element
    const announcer = document.createElement('div');
    announcer.setAttribute('aria-live', 'polite');
    announcer.setAttribute('aria-atomic', 'true');
    announcer.className = 'sr-only';
    document.body.appendChild(announcer);
    
    // Announce validation results
    form.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-valid')) {
            announcer.textContent = 'Trường hợp lệ';
        } else if (e.target.classList.contains('is-invalid')) {
            const errorMsg = e.target.parentNode.querySelector('.invalid-feedback');
            if (errorMsg) {
                announcer.textContent = 'Lỗi: ' + errorMsg.textContent;
            }
        }
    });
}

// ===== FORM SUBMISSION =====
function handleFormSubmission() {
    const submitBtn = document.querySelector('.btn-register');
    if (!submitBtn) return;
    
    // Show loading state
    showLoadingState(submitBtn);
    
    // Get form data
    const formData = new FormData(document.querySelector('.register-form'));
    
    // Submit form
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(data => {
        // Handle response
        if (data.includes('success')) {
            showSuccessMessage('Đăng ký thành công! Chuyển hướng...');
            setTimeout(() => {
                window.location.href = 'login.php?registered=success';
            }, 2000);
        } else {
            hideLoadingState(submitBtn);
            // Parse and show errors
            const errorMatch = data.match(/alert-danger[^>]*>([^<]+)/);
            if (errorMatch) {
                showErrorMessage(errorMatch[1]);
            }
        }
    })
    .catch(error => {
        hideLoadingState(submitBtn);
        showErrorMessage('Có lỗi xảy ra. Vui lòng thử lại!');
        console.error('Submit error:', error);
    });
}

function showLoadingState(button) {
    button.disabled = true;
    button.classList.add('loading');
    button.innerHTML = '⏳ Đang tạo tài khoản...';
}

function hideLoadingState(button) {
    button.disabled = false;
    button.classList.remove('loading');
    button.innerHTML = '➕ Tạo tài khoản';
}

// ===== SOCIAL REGISTRATION =====
function registerWithGoogle() {
    showNotification('🚧 Tính năng đăng ký với Google đang được phát triển!', 'info');
}

function registerWithFacebook() {
    showNotification('🚧 Tính năng đăng ký với Facebook đang được phát triển!', 'info');
}

// ===== MODAL FUNCTIONS =====
function showTermsModal() {
    const modal = new bootstrap.Modal(document.getElementById('termsModal'));
    modal.show();
}

function showPrivacyModal() {
    showNotification('📋 Chính sách bảo mật đang được cập nhật!', 'info');
}

function acceptTerms() {
    const checkbox = document.getElementById('agreeTerms');
    if (checkbox) {
        checkbox.checked = true;
        checkbox.dispatchEvent(new Event('change'));
    }
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('termsModal'));
    if (modal) modal.hide();
    
    showNotification('✅ Bạn đã đồng ý với điều khoản sử dụng!', 'success');
}

// ===== NOTIFICATION SYSTEM =====
function showNotification(message, type = 'info', duration = 5000) {
    const container = getNotificationContainer();
    
    const notification = createNotificationElement(message, type);
    container.appendChild(notification);
    
    // Auto remove
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, duration);
}

function showSuccessMessage(message) {
    showNotification(message, 'success');
}

function showErrorMessage(message) {
    showNotification(message, 'error');
}

function getNotificationContainer() {
    let container = document.getElementById('notificationContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notificationContainer';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; max-width: 400px;';
        document.body.appendChild(container);
    }
    return container;
}

function createNotificationElement(message, type) {
    const typeConfig = {
        'success': { class: 'alert-success', icon: '✅' },
        'error': { class: 'alert-danger', icon: '❌' },
        'warning': { class: 'alert-warning', icon: '⚠️' },
        'info': { class: 'alert-info', icon: 'ℹ️' }
    };
    
    const config = typeConfig[type] || typeConfig['info'];
    
    const notification = document.createElement('div');
    notification.className = `alert ${config.class} alert-dismissible fade show`;
    notification.style.cssText = `
        margin-bottom: 15px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border: none;
        animation: slideInRight 0.3s ease;
    `;
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <span style="font-size: 1.2rem; margin-right: 10px;">${config.icon}</span>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    return notification;
}

// ===== UTILITY FUNCTIONS =====
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[0-9]{10,11}$/;
    return phoneRegex.test(phone.replace(/\D/g, ''));
}

function checkWelcomeMessage() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('welcome') === 'true') {
        showNotification('🎉 Chào mừng bạn đến với Teddy Bear Paradise!', 'success');
    }
}

function initializeTooltips() {
    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

// ===== CSS ANIMATIONS =====
function addAnimationCSS() {
    if (document.getElementById('register-animations')) return;
    
    const style = document.createElement('style');
    style.id = 'register-animations';
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .sr-only {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }
    `;
    document.head.appendChild(style);
}

// ===== ERROR HANDLING =====
window.addEventListener('error', function(e) {
    console.error('Register page error:', e.error);
    
    if (typeof showNotification === 'function') {
        showNotification('Có lỗi xảy ra. Vui lòng tải lại trang!', 'error');
    }
});

// ===== PERFORMANCE MONITORING =====
window.addEventListener('load', function() {
    const loadTime = performance.now();
    console.log(`🚀 Register page loaded in ${Math.round(loadTime)}ms`);
    
    // Add animation CSS
    addAnimationCSS();
    
    // Performance optimization
    optimizeFormPerformance();
});

function optimizeFormPerformance() {
    // Debounce scroll events
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(handleScroll, 100);
    });
    
    // Preload next page
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = 'login.php';
    document.head.appendChild(link);
}

function handleScroll() {
    // Handle scroll-based animations or effects here
    const scrolled = window.pageYOffset;
    const parallax = document.querySelector('.register-container');
    
    if (parallax && scrolled < 500) {
        parallax.style.transform = `translateY(${scrolled * 0.1}px)`;
    }
}

// ===== FORM AUTO-SAVE (DRAFT) =====
function initializeAutoSave() {
    const form = document.querySelector('.register-form');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]');
    
    inputs.forEach(input => {
        // Load saved value
        const savedValue = localStorage.getItem(`register_${input.name}`);
        if (savedValue && !input.value) {
            input.value = savedValue;
        }
        
        // Save on input
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                localStorage.setItem(`register_${this.name}`, this.value);
            } else {
                localStorage.removeItem(`register_${this.name}`);
            }
        });
    });
    
    // Clear saved data on successful submission
    form.addEventListener('submit', function() {
        if (this.checkValidity()) {
            inputs.forEach(input => {
                localStorage.removeItem(`register_${input.name}`);
            });
        }
    });
}

// ===== ADVANCED FORM FEATURES =====
function initializeAdvancedFeatures() {
    // Password visibility toggle
    addPasswordVisibilityToggle();
    
    // Copy to clipboard for generated passwords
    addPasswordGenerator();
    
    // Email domain suggestions
    addEmailSuggestions();
    
    // Auto-save functionality
    initializeAutoSave();
    
    // Keyboard shortcuts
    initializeKeyboardShortcuts();
}

function addPasswordVisibilityToggle() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        const wrapper = field.parentNode;
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'password-toggle';
        toggleBtn.innerHTML = '👁️';
        toggleBtn.style.cssText = `
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            z-index: 3;
            padding: 5px;
            border-radius: 3px;
            transition: color 0.3s ease;
        `;
        
        toggleBtn.addEventListener('click', function() {
            const isPassword = field.type === 'password';
            field.type = isPassword ? 'text' : 'password';
            this.innerHTML = isPassword ? '🙈' : '👁️';
            this.style.color = isPassword ? '#ff6b96' : '#999';
        });
        
        toggleBtn.addEventListener('mouseenter', () => toggleBtn.style.color = '#ff6b96');
        toggleBtn.addEventListener('mouseleave', () => {
            if (field.type === 'password') toggleBtn.style.color = '#999';
        });
        
        wrapper.appendChild(toggleBtn);
        field.style.paddingRight = '50px';
    });
}

function addPasswordGenerator() {
    const passwordField = document.getElementById('password');
    if (!passwordField) return;
    
    const wrapper = passwordField.parentNode;
    const generateBtn = document.createElement('button');
    generateBtn.type = 'button';
    generateBtn.className = 'password-generate';
    generateBtn.innerHTML = '✨';
    generateBtn.title = 'Tạo mật khẩu mạnh';
    generateBtn.style.cssText = `
        position: absolute;
        right: 50px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        z-index: 3;
        padding: 5px;
        border-radius: 3px;
        transition: all 0.3s ease;
    `;
    
    generateBtn.addEventListener('click', function() {
        const generatedPassword = generateStrongPassword();
        passwordField.value = generatedPassword;
        
        const confirmField = document.getElementById('confirmPassword');
        if (confirmField) {
            confirmField.value = generatedPassword;
        }
        
        // Trigger validation
        passwordField.dispatchEvent(new Event('input'));
        if (confirmField) confirmField.dispatchEvent(new Event('input'));
        
        // Visual feedback
        this.style.color = '#28a745';
        this.innerHTML = '✅';
        setTimeout(() => {
            this.style.color = '#999';
            this.innerHTML = '✨';
        }, 2000);
        
        showNotification('✨ Đã tạo mật khẩu mạnh!', 'success', 3000);
    });
    
    generateBtn.addEventListener('mouseenter', () => generateBtn.style.color = '#ff6b96');
    generateBtn.addEventListener('mouseleave', () => {
        if (!generateBtn.innerHTML.includes('check')) generateBtn.style.color = '#999';
    });
    
    wrapper.appendChild(generateBtn);
}

function generateStrongPassword(length = 12) {
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const numbers = '0123456789';
    const symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    
    const allChars = lowercase + uppercase + numbers + symbols;
    let password = '';
    
    // Ensure at least one character from each category
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += symbols[Math.floor(Math.random() * symbols.length)];
    
    // Fill remaining length
    for (let i = 4; i < length; i++) {
        password += allChars[Math.floor(Math.random() * allChars.length)];
    }
    
    // Shuffle the password
    return password.split('').sort(() => Math.random() - 0.5).join('');
}

function addEmailSuggestions() {
    const emailField = document.getElementById('email');
    if (!emailField) return;
    
    const commonDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com'];
    
    emailField.addEventListener('input', function() {
        const value = this.value;
        const atIndex = value.indexOf('@');
        
        if (atIndex > 0 && atIndex === value.length - 1) {
            // User just typed @, show suggestions
            showEmailSuggestions(this, commonDomains);
        }
    });
}

function showEmailSuggestions(field, domains) {
    // Remove existing suggestions
    const existingSuggestions = document.querySelector('.email-suggestions');
    if (existingSuggestions) {
        existingSuggestions.remove();
    }
    
    const wrapper = field.parentNode;
    const suggestions = document.createElement('div');
    suggestions.className = 'email-suggestions';
    suggestions.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
    `;
    
    const username = field.value.split('@')[0];
    
    domains.forEach(domain => {
        const suggestion = document.createElement('div');
        suggestion.className = 'email-suggestion';
        suggestion.textContent = `${username}@${domain}`;
        suggestion.style.cssText = `
            padding: 10px 15px;
            cursor: pointer;
            transition: background 0.2s ease;
            border-bottom: 1px solid #eee;
        `;
        
        suggestion.addEventListener('mouseenter', () => {
            suggestion.style.background = '#f8f9fa';
        });
        
        suggestion.addEventListener('mouseleave', () => {
            suggestion.style.background = 'white';
        });
        
        suggestion.addEventListener('click', () => {
            field.value = suggestion.textContent;
            field.dispatchEvent(new Event('input'));
            suggestions.remove();
            field.focus();
        });
        
        suggestions.appendChild(suggestion);
    });
    
    wrapper.appendChild(suggestions);
    
    // Remove suggestions when clicking outside
    setTimeout(() => {
        document.addEventListener('click', function handler(e) {
            if (!wrapper.contains(e.target)) {
                suggestions.remove();
                document.removeEventListener('click', handler);
            }
        });
    }, 100);
}

function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit form
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            const submitBtn = document.querySelector('.btn-register');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.click();
            }
        }
        
        // Ctrl/Cmd + K to focus search (if on page)
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const firstInput = document.querySelector('.form-control-custom');
            if (firstInput) firstInput.focus();
        }
        
        // Alt + G to generate password
        if (e.altKey && e.key === 'g') {
            e.preventDefault();
            const generateBtn = document.querySelector('.password-generate');
            if (generateBtn) generateBtn.click();
        }
    });
}

// ===== ANALYTICS & TRACKING =====
function trackFormInteraction(action, field = null) {
    if (typeof gtag !== 'undefined') {
        gtag('event', action, {
            'event_category': 'Registration',
            'event_label': field || 'form',
            'value': 1
        });
    }
    
    console.log(`📊 Tracked: ${action}${field ? ` - ${field}` : ''}`);
}

// ===== INITIALIZATION WITH ADVANCED FEATURES =====
document.addEventListener('DOMContentLoaded', function() {
    // Initialize basic features
    initializeRegisterPage();
    
    // Initialize advanced features
    setTimeout(() => {
        initializeAdvancedFeatures();
    }, 100);
    
    // Track page view
    trackFormInteraction('page_view');
});

// ===== EXPORT FUNCTIONS FOR GLOBAL USE =====
window.registerPageFunctions = {
    showNotification,
    showSuccessMessage,
    showErrorMessage,
    validateForm,
    registerWithGoogle,
    registerWithFacebook,
    showTermsModal,
    showPrivacyModal,
    acceptTerms,
    generateStrongPassword
};

// ===== FINAL CONSOLE MESSAGE =====
console.log('🎉 Register.js loaded successfully!');
console.log('💡 Available shortcuts:');
console.log('   - Ctrl/Cmd + Enter: Submit form');
console.log('   - Ctrl/Cmd + K: Focus first input');
console.log('   - Alt + G: Generate strong password');
console.log('   - Escape: Close modals');

// ===== SERVICE WORKER REGISTRATION (PWA) =====
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('👷‍♂️ ServiceWorker registered successfully');
            })
            .catch(function(error) {
                console.log('❌ ServiceWorker registration failed');
            });
    });
}