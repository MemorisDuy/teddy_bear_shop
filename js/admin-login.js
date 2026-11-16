// Admin Login JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Auto focus on username field
    const usernameInput = document.querySelector('input[name="username"]');
    if (usernameInput) {
        usernameInput.focus();
    }

    // Handle form submission
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    
    if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function() {
            loginBtn.disabled = true;
            loginBtn.innerHTML = '⏳ Đang đăng nhập...';
        });
    }

    // Enter key support
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && loginForm) {
            loginForm.submit();
        }
    });

    // Fill demo credentials on double click icon
    const loginIcon = document.querySelector('.login-icon');
    if (loginIcon) {
        loginIcon.addEventListener('dblclick', function() {
            const username = document.querySelector('input[name="username"]');
            const password = document.querySelector('input[name="password"]');
            
            if (username && password) {
                username.value = 'admin';
                password.value = 'admin123';
                username.focus();
            }
        });
    }

    // Form validation
    const validateForm = function() {
        const username = document.querySelector('input[name="username"]').value.trim();
        const password = document.querySelector('input[name="password"]').value;
        
        if (!username || !password) {
            showAlert('Vui lòng nhập đầy đủ thông tin!', 'danger');
            return false;
        }
        
        if (password.length < 3) {
            showAlert('Mật khẩu quá ngắn!', 'danger');
            return false;
        }
        
        return true;
    };

    // Show alert function
    const showAlert = function(message, type = 'danger') {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <span>${type === 'danger' ? '⚠️' : '✅'}</span>
            ${message}
        `;
        
        // Insert before form
        const loginBody = document.querySelector('.login-body');
        const form = document.querySelector('#loginForm');
        if (loginBody && form) {
            loginBody.insertBefore(alertDiv, form);
        }
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    };

    // Client-side validation on submit
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<span>🔐</span> <span>Đăng nhập</span>';
            }
        });
    }

    // Remember me tooltip
    const rememberCheckbox = document.querySelector('input[name="remember"]');
    if (rememberCheckbox) {
        rememberCheckbox.title = 'Ghi nhớ đăng nhập trong 30 ngày';
    }

    // Back link confirmation
    const backLink = document.querySelector('.back-link a');
    if (backLink) {
        backLink.addEventListener('click', function(e) {
            // Only confirm if user has entered some data
            const username = document.querySelector('input[name="username"]').value;
            const password = document.querySelector('input[name="password"]').value;
            
            if (username || password) {
                const confirmed = confirm('Bạn có chắc muốn quay lại trang chủ? Dữ liệu đã nhập sẽ bị mất.');
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });
    }

    // Animate form elements on load
    const animateElements = function() {
        const elements = document.querySelectorAll('.form-group');
        elements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.5s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });
    };

    // Run animations after short delay
    setTimeout(animateElements, 300);

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            if (loginForm) {
                loginForm.submit();
            }
        }
        
        // Escape key to clear form
        if (e.key === 'Escape') {
            const username = document.querySelector('input[name="username"]');
            const password = document.querySelector('input[name="password"]');
            const remember = document.querySelector('input[name="remember"]');
            
            if (username) username.value = '';
            if (password) password.value = '';
            if (remember) remember.checked = false;
            
            if (username) username.focus();
        }
    });

    // Auto-fill demo on triple-click username field
    if (usernameInput) {
        let clickCount = 0;
        usernameInput.addEventListener('click', function() {
            clickCount++;
            setTimeout(() => {
                if (clickCount === 3) {
                    const password = document.querySelector('input[name="password"]');
                    usernameInput.value = 'admin';
                    if (password) password.value = 'admin123';
                }
                clickCount = 0;
            }, 500);
        });
    }
});

// Utility object for external access
const AdminLogin = {
    
    // Reset form
    resetForm: function() {
        const form = document.getElementById('loginForm');
        if (form) {
            form.reset();
            const username = document.querySelector('input[name="username"]');
            if (username) username.focus();
        }
    },
    
    // Fill demo credentials
    fillDemo: function() {
        const username = document.querySelector('input[name="username"]');
        const password = document.querySelector('input[name="password"]');
        
        if (username && password) {
            username.value = 'admin';
            password.value = 'admin123';
        }
    },
    
    // Show loading state
    showLoading: function() {
        const btn = document.getElementById('loginBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '⏳ Đang đăng nhập...';
        }
    },
    
    // Hide loading state
    hideLoading: function() {
        const btn = document.getElementById('loginBtn');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>🔐</span> <span>Đăng nhập</span>';
        }
    },
    
    // Focus username
    focusUsername: function() {
        const username = document.querySelector('input[name="username"]');
        if (username) {
            username.focus();
            username.select();
        }
    }
};