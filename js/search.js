/**
 * Search JavaScript - FIXED VERSION
 * Tách riêng khỏi main.js để tránh conflict
 */

// FIXED: Namespace để tránh conflict
window.TeddySearch = (function() {
    'use strict';
    
    // FIXED: Helper function to get base path (project root only, no subdirectories)
    function getBasePath() {
        const path = window.location.pathname;
        const parts = path.split('/').filter(p => p);
        
        // Find teddy_bear_shop in the path (exact match first)
        let projectIndex = parts.findIndex(p => p === 'teddy_bear_shop');
        
        if (projectIndex === -1) {
            // Try fuzzy match
            projectIndex = parts.findIndex(p => (p.includes('teddy') && p.includes('bear')) || (p.includes('teddy') && p.includes('shop')));
        }
        
        if (projectIndex !== -1) {
            // Return ONLY the project root, not including any subdirectories
            // If project is at index 1 (e.g., /Lap_Trinh_Web/teddy_bear_shop/), include parent
            if (projectIndex > 0) {
                return '/' + parts.slice(0, projectIndex + 1).join('/');
            }
            return '/' + parts[projectIndex];
        }
        
        // If not found, try to detect from common patterns
        // Pattern: /Lap_Trinh_Web/teddy_bear_shop/... 
        if (parts.length >= 2) {
            // Check if second part might be the project
            const secondPart = parts[1];
            if (secondPart.includes('teddy') || secondPart.includes('bear') || secondPart.includes('shop')) {
                return '/' + parts[0] + '/' + parts[1];
            }
            // Otherwise just return first part
            return '/' + parts[0];
        }
        
        if (parts.length >= 1) {
            return '/' + parts[0];
        }
        
        // Fallback
        return '/teddy_bear_shop';
    }
    
    // FIXED: Helper to build absolute URL from root (always from domain root)
    function buildAbsoluteUrl(path) {
        // Normalize path - remove leading/trailing slashes first
        path = path.replace(/^\/+|\/+$/g, '');
        
        // If path already contains full path, extract just the relative part
        if (path.includes('/pages/')) {
            // Extract everything after /pages/
            const pagesIndex = path.indexOf('/pages/');
            path = path.substring(pagesIndex + 1); // Get 'pages/...'
        }
        
        // Get base path (project root only, no pages/)
        const basePath = getBasePath();
        
        // Build full path: basePath + /pages/ + path
        // But ensure we don't add /pages/ if path already starts with pages/
        let fullPath;
        if (path.startsWith('pages/')) {
            fullPath = basePath + '/' + path;
        } else if (path.startsWith('/pages/')) {
            fullPath = basePath + path;
        } else {
            fullPath = basePath + '/pages/' + path;
        }
        
        // Remove any double slashes (except after http://)
        fullPath = fullPath.replace(/([^:]\/)\/+/g, '$1');
        
        // CRITICAL: Remove double pages/ - do this multiple times to catch all cases
        while (fullPath.includes('/pages/pages/')) {
            fullPath = fullPath.replace(/\/pages\/pages\//g, '/pages/');
        }
        
        // Always create URL from origin to ensure absolute path
        return window.location.origin + fullPath;
    }
    
    // FIXED: Constants với absolute path từ root - CRITICAL FIX
    const BASE_PATH = getBasePath();
    
    // CRITICAL: Remove ALL instances of /pages/ from base path
    let cleanBasePath = BASE_PATH.replace(/\/pages\/?/g, '').replace(/\/pages$/g, '');
    
    // Extract just the project root - find teddy_bear_shop
    const pathParts = cleanBasePath.split('/').filter(p => p);
    let projectIndex = pathParts.findIndex(p => p === 'teddy_bear_shop');
    
    if (projectIndex === -1) {
        projectIndex = pathParts.findIndex(p => (p.includes('teddy') && p.includes('bear')) || (p.includes('teddy') && p.includes('shop')));
    }
    
    if (projectIndex !== -1) {
        // Get path up to and including project name only
        cleanBasePath = '/' + pathParts.slice(0, projectIndex + 1).join('/');
    } else if (pathParts.length > 0) {
        // Fallback: use first part
        cleanBasePath = '/' + pathParts[0];
    } else {
        cleanBasePath = '/teddy_bear_shop';
    }
    
    // FINAL CHECK: Ensure no pages/ in base path
    cleanBasePath = cleanBasePath.replace(/\/pages\/?/g, '');
    if (!cleanBasePath.startsWith('/')) {
        cleanBasePath = '/' + cleanBasePath;
    }
    
    // Final validation: ensure cleanBasePath doesn't have pages/
    const finalBasePath = cleanBasePath.replace(/\/pages\/?/g, '');
    const validatedBasePath = finalBasePath.startsWith('/') ? finalBasePath : '/' + finalBasePath;
    
    const CONFIG = {
        API_URL: validatedBasePath + '/pages/api-search-single.php',
        AI_SEARCH_API: validatedBasePath + '/pages/api-ai-search.php', // AI Search với Gemini
        SEARCH_URL: validatedBasePath + '/pages/search.php',
        MIN_SEARCH_LENGTH: 2,
        DEBOUNCE_DELAY: 300,
        USE_AI_SEARCH: true // Bật tìm kiếm AI
    };
    
    
    // Debug: Log final config
    console.log('🔍 Final Search Config:', {
        basePath: BASE_PATH,
        cleanBasePath: cleanBasePath,
        searchUrl: CONFIG.SEARCH_URL,
        currentPath: window.location.pathname,
        origin: window.location.origin,
        finalUrl: window.location.origin + CONFIG.SEARCH_URL
    });
    
    let searchTimeout = null;
    let isSearching = false;
    
    // FIXED: Utility functions với null check
    function getElement(selector) {
        const element = document.querySelector(selector);
        if (!element) {
            // Don't warn for optional elements
            if (!selector.includes('filterForm')) {
                console.warn('Element not found:', selector);
            }
        }
        return element;
    }
    
    function getAllElements(selector) {
        return document.querySelectorAll(selector) || [];
    }
    
    // FIXED: Show loading state
    function showLoading(button) {
        if (!button) return;
        
        const originalText = button.innerHTML;
        button.dataset.originalText = originalText;
        button.innerHTML = '⏳ Đang tìm...';
        button.disabled = true;
    }
    
    // FIXED: Hide loading state  
    function hideLoading(button) {
        if (!button) return;
        
        const originalText = button.dataset.originalText;
        if (originalText) {
            button.innerHTML = originalText;
        }
        button.disabled = false;
    }
    
    // FIXED: Show notification with proper error handling
    function showNotification(message, type = 'info') {
        try {
            if (typeof window.showCuteNotification === 'function') {
                window.showCuteNotification(message, type);
                return;
            }
            
            // Fallback notification
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible`;
            notification.style.cssText = `
                position: fixed; 
                top: 20px; 
                right: 20px; 
                z-index: 10000; 
                max-width: 400px; 
                animation: slideInRight 0.3s ease;
            `;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
            
        } catch (e) {
            console.error('Notification error:', e);
        }
    }
    
    // FIXED: Perform search với proper error handling
    function performSearch(event) {
        event.preventDefault();
        
        try {
            const form = event.target.closest('.search-form-modal') || 
                        event.target.closest('.search-form') ||
                        getElement('.search-form-modal');
            
            if (!form) {
                console.error('Search form not found');
                return;
            }
            
            const input = form.querySelector('.search-input-modal') || 
                         form.querySelector('input[name="q"]');
            
            if (!input) {
                console.error('Search input not found');
                return;
            }
            
            const keyword = input.value.trim();
            
            if (keyword === '') {
                showNotification('Vui lòng nhập từ khóa tìm kiếm', 'warning');
                input.focus();
                return;
            }
            
            if (keyword.length < CONFIG.MIN_SEARCH_LENGTH) {
                showNotification(`Từ khóa phải có ít nhất ${CONFIG.MIN_SEARCH_LENGTH} ký tự`, 'warning');
                input.focus();
                return;
            }
            
            const submitBtn = form.querySelector('button[type="submit"]') || 
                             form.querySelector('button');
            
            if (isSearching) {
                return;
            }
            
            isSearching = true;
            showLoading(submitBtn);
            
            // Use AI Search if enabled
            const useAISearch = CONFIG.USE_AI_SEARCH && keyword.length >= 3;
            const apiEndpoint = useAISearch ? CONFIG.AI_SEARCH_API : CONFIG.API_URL;
            
            // Prepare request
            let fetchOptions = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            };
            
            // For AI Search, use POST
            if (useAISearch) {
                fetchOptions.method = 'POST';
                fetchOptions.headers['Content-Type'] = 'application/x-www-form-urlencoded';
                fetchOptions.body = 'q=' + encodeURIComponent(keyword);
            }
            
            // Build URL - Use absolute path from root
            const apiUrl = new URL(apiEndpoint, window.location.origin);
            if (!useAISearch) {
                apiUrl.searchParams.set('q', keyword);
            }
            
            fetch(apiUrl.toString(), fetchOptions)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    hideLoading(submitBtn);
                    isSearching = false;
                    
                    if (!data.success || data.count === 0) {
                        // Redirect đến search page để hiển thị thông báo và gợi ý AI
                        // CONFIG.SEARCH_URL is already validated, just add query params
                        const urlObj = new URL(CONFIG.SEARCH_URL, window.location.origin);
                        urlObj.searchParams.set('q', keyword);
                        // Add flag to indicate no results
                        urlObj.searchParams.set('no_results', '1');
                        console.log('🔍 Redirecting to (no results):', urlObj.toString());
                        window.location.href = urlObj.toString();
                        return;
                    }
                    
                    // Handle AI search results
                    if (useAISearch && data.products && data.products.length > 0) {
                        // Show AI results in modal or redirect
                        if (data.count === 1) {
                            // Single result - go directly
                            const productUrl = new URL(CONFIG.SEARCH_URL.replace('search.php', 'product-detail.php'), window.location.origin);
                            const urlObj = new URL(productUrl);
                            urlObj.searchParams.set('slug', data.products[0].slug);
                            window.location.href = urlObj.toString();
                        } else {
                            // Multiple results - redirect to search page
                            // CONFIG.SEARCH_URL is already absolute, just add query params
                            const urlObj = new URL(CONFIG.SEARCH_URL, window.location.origin);
                            urlObj.searchParams.set('q', keyword);
                            if (data.ai_suggested) {
                                urlObj.searchParams.set('ai', '1');
                            }
                            window.location.href = urlObj.toString();
                        }
                        return;
                    }
                    
                    // Handle regular search results
                    if (data.count === 1 && (data.slug || data.id)) {
                        const productUrl = new URL(CONFIG.SEARCH_URL.replace('search.php', 'product-detail.php'), window.location.origin);
                        if (data.slug) {
                            productUrl.searchParams.set('slug', data.slug);
                        } else {
                            productUrl.searchParams.set('id', data.id);
                        }
                        window.location.href = productUrl.toString();
                    } else {
                        // CONFIG.SEARCH_URL is already absolute, just add query params
                        const urlObj = new URL(CONFIG.SEARCH_URL, window.location.origin);
                        urlObj.searchParams.set('q', keyword);
                        window.location.href = urlObj.toString();
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    hideLoading(submitBtn);
                    isSearching = false;
                    
                    // Fallback to regular search
                    // CONFIG.SEARCH_URL is already absolute, just add query params
                    const urlObj = new URL(CONFIG.SEARCH_URL, window.location.origin);
                    urlObj.searchParams.set('q', keyword);
                    window.location.href = urlObj.toString();
                });
                
        } catch (error) {
            console.error('Search function error:', error);
            isSearching = false;
            showNotification('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        }
    }
    
    // FIXED: Search suggestion với correct URL
    function searchSuggestion(keyword) {
        try {
            // CONFIG.SEARCH_URL is already absolute, just add query params
            const urlObj = new URL(CONFIG.SEARCH_URL, window.location.origin);
            urlObj.searchParams.set('q', keyword);
            console.log('🔍 Suggestion redirect to:', urlObj.toString());
            window.location.href = urlObj.toString();
            
        } catch (error) {
            console.error('Search suggestion error:', error);
        }
    }
    
    // FIXED: Update sort với proper URL handling
    function updateSort(value) {
        try {
            const url = new URL(window.location);
            url.searchParams.set('sort', value);
            window.location.href = url.toString();
        } catch (error) {
            console.error('Update sort error:', error);
        }
    }
    
    // FIXED: Open search overlay với animation
    function openSearchOverlay() {
        try {
            const overlay = getElement('#searchOverlay');
            if (!overlay) {
                console.warn('Search overlay not found');
                return;
            }
            
            overlay.style.opacity = '1';
            overlay.style.visibility = 'visible';
            
            setTimeout(() => {
                const input = overlay.querySelector('.search-input-modal');
                if (input) {
                    input.focus();
                }
            }, 100);
            
            // Re-bind events
            bindSearchEvents();
            
        } catch (error) {
            console.error('Open search overlay error:', error);
        }
    }
    
    // FIXED: Close search overlay
    function closeSearchOverlay() {
        try {
            const overlay = getElement('#searchOverlay');
            if (overlay) {
                overlay.style.opacity = '0';
                overlay.style.visibility = 'hidden';
            }
        } catch (error) {
            console.error('Close search overlay error:', error);
        }
    }
    
    // FIXED: Bind search events với null check
    function bindSearchEvents() {
        try {
            // Search form submissions
            getAllElements('.search-form-modal, .search-form').forEach(form => {
                // Remove existing listeners to prevent duplicates
                const newForm = form.cloneNode(true);
                form.parentNode.replaceChild(newForm, form);
                
                newForm.addEventListener('submit', performSearch);
                
                const input = newForm.querySelector('input[name="q"], .search-input-modal');
                const button = newForm.querySelector('button[type="submit"], button');
                
                if (input) {
                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            performSearch(e);
                        }
                    });
                }
                
                if (button) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        performSearch(e);
                    });
                }
            });
            
            // FIXED: Filter form auto-submit với null check
            const filterForm = getElement('#filterForm');
            if (filterForm) {
                filterForm.addEventListener('change', function() {
                    this.submit();
                });
            } else {
                console.log('ℹ️ Filter form not found (normal on non-search pages)');
            }
            
        } catch (error) {
            console.error('Bind search events error:', error);
        }
    }
    
    // FIXED: Initialize search functionality
    function init() {
        try {
            console.log('🔍 Initializing Teddy Search...');
            
            bindSearchEvents();
            
            // Keyboard shortcut (Ctrl+K)
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'k') {
                    e.preventDefault();
                    openSearchOverlay();
                }
            });
            
            console.log('✅ Teddy Search initialized successfully!');
            
        } catch (error) {
            console.error('❌ Teddy Search initialization error:', error);
        }
    }
    
    // Public API
    return {
        init: init,
        performSearch: performSearch,
        searchSuggestion: searchSuggestion,
        updateSort: updateSort,
        openSearchOverlay: openSearchOverlay,
        closeSearchOverlay: closeSearchOverlay,
        bindSearchEvents: bindSearchEvents
    };
    
})();

// FIXED: Auto-initialize when DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', TeddySearch.init);
} else {
    TeddySearch.init();
}

// FIXED: Global functions for backward compatibility
window.performSearch = TeddySearch.performSearch;
window.searchSuggestion = TeddySearch.searchSuggestion;
window.openSearchOverlay = TeddySearch.openSearchOverlay;
window.closeSearch = TeddySearch.closeSearchOverlay;