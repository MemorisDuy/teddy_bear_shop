/**
 * TEDDY BEAR SHOP - LOADING FIX ULTIMATE 🐻
 * File riêng để fix hoàn toàn vấn đề loading spinner
 * Phải load TRƯỚC tất cả file JS khác
 */

(function() {
    'use strict';
    
    console.log('🚨 ULTIMATE LOADING FIX ACTIVATED!');
    
    // ===== IMMEDIATE CSS INJECTION =====
    function injectKillCSS() {
        const style = document.createElement('style');
        style.id = 'loading-kill-css';
        style.innerHTML = `
            /* FORCE KILL ALL LOADING ELEMENTS */
            .loading, .spinner, .preloader, 
            #loadingOverlay, .loading-overlay,
            [class*="loading"], [id*="loading"],
            [class*="spinner"], [id*="spinner"],
            [class*="preloader"], [id*="preloader"],
            .cute-loading, .bear-loading,
            .bootstrap-loading, .page-loading,
            .site-loading, .main-loading {
                display: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
                position: absolute !important;
                top: -9999px !important;
                left: -9999px !important;
                width: 0 !important;
                height: 0 !important;
                z-index: -9999 !important;
            }
            
            /* FORCE BODY VISIBLE */
            body, body.loading, body[class*="loading"] {
                overflow: visible !important;
                overflow-x: auto !important;
                overflow-y: auto !important;
                pointer-events: auto !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* KILL ALL LOADING ANIMATIONS */
            *[class*="loading"] *,
            *[id*="loading"] *,
            *[class*="spinner"] *,
            *[id*="spinner"] * {
                animation: none !important;
                transition: none !important;
            }
            
            /* FORCE SHOW MAIN CONTENT */
            main, .main, #main,
            .container, .content, .page-content,
            .hero-section, .navbar, nav,
            header, footer, .footer {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
        `;
        
        // Inject vào head ngay lập tức
        if (document.head) {
            document.head.appendChild(style);
        } else {
            // Nếu head chưa có, inject vào body
            document.addEventListener('DOMContentLoaded', () => {
                if (!document.getElementById('loading-kill-css')) {
                    document.head.appendChild(style);
                }
            });
        }
        
        console.log('💥 Kill CSS injected!');
    }
    
    // ===== NUCLEAR LOADING DESTROYER =====
    function nuclearLoadingDestroy() {
        try {
            // Destroy by selectors
            const killSelectors = [
                '.loading', '.spinner', '.preloader',
                '#loadingOverlay', '.loading-overlay',
                '[class*="loading"]', '[id*="loading"]',
                '[class*="spinner"]', '[id*="spinner"]',
                '[class*="preloader"]', '[id*="preloader"]',
                '.cute-loading', '.bear-loading',
                '.bootstrap-loading', '.page-loading',
                '.site-loading', '.main-loading'
            ];
            
            killSelectors.forEach(selector => {
                try {
                    document.querySelectorAll(selector).forEach(el => {
                        // Multiple destruction methods
                        el.style.display = 'none';
                        el.style.opacity = '0';
                        el.style.visibility = 'hidden';
                        el.style.pointerEvents = 'none';
                        el.style.position = 'absolute';
                        el.style.top = '-9999px';
                        el.style.left = '-9999px';
                        el.style.zIndex = '-9999';
                        el.remove(); // Final destruction
                    });
                } catch (e) {
                    // Silent fail individual selectors
                }
            });
            
            // Destroy by scanning all elements
            document.querySelectorAll('*').forEach(el => {
                try {
                    const classList = el.className;
                    const id = el.id;
                    
                    if ((typeof classList === 'string' && 
                        (classList.includes('loading') || classList.includes('spinner') || classList.includes('preloader'))) ||
                        (typeof id === 'string' && 
                        (id.includes('loading') || id.includes('Loading') || id.includes('spinner') || id.includes('preloader')))) {
                        
                        el.style.display = 'none';
                        el.remove();
                    }
                } catch (e) {
                    // Silent fail individual elements
                }
            });
            
            console.log('🔥 Nuclear destruction completed!');
            
        } catch (error) {
            console.warn('⚠️ Some loading elements survived:', error);
        }
    }
    
    // ===== BODY RESTORATION =====
    function restoreBody() {
        try {
            if (document.body) {
                // Remove loading classes
                const loadingClasses = [];
                document.body.classList.forEach(cls => {
                    if (cls.includes('loading') || cls.includes('spinner')) {
                        loadingClasses.push(cls);
                    }
                });
                loadingClasses.forEach(cls => document.body.classList.remove(cls));
                
                // Force visible styles
                document.body.style.overflow = 'visible';
                document.body.style.overflowX = 'auto';
                document.body.style.overflowY = 'auto';
                document.body.style.pointerEvents = 'auto';
                document.body.style.opacity = '1';
                document.body.style.visibility = 'visible';
                
                console.log('✅ Body restored!');
            }
        } catch (error) {
            console.warn('⚠️ Body restoration partial:', error);
        }
    }
    
    // ===== CONTENT FORCE SHOW =====
    function forceShowContent() {
        try {
            const contentSelectors = [
                'main', '.main', '#main',
                '.container', '.content', '.page-content',
                '.hero-section', '.navbar', 'nav',
                'header', 'footer', '.footer',
                '.row', '.col', '[class*="col-"]'
            ];
            
            contentSelectors.forEach(selector => {
                try {
                    document.querySelectorAll(selector).forEach(el => {
                        el.style.display = el.tagName === 'NAV' || el.tagName === 'HEADER' || el.tagName === 'FOOTER' ? 'block' : '';
                        el.style.visibility = 'visible';
                        el.style.opacity = '1';
                    });
                } catch (e) {
                    // Silent fail
                }
            });
            
            console.log('👁️ Content force shown!');
            
        } catch (error) {
            console.warn('⚠️ Content show partial:', error);
        }
    }
    
    // ===== MAIN FIX FUNCTION =====
    function executeLoadingFix() {
        nuclearLoadingDestroy();
        restoreBody();
        forceShowContent();
        
        console.log('✅ Loading fix cycle completed!');
    }
    
    // ===== EXECUTION STRATEGY =====
    
    // 1. Inject CSS immediately
    injectKillCSS();
    
    // 2. Execute fix immediately
    executeLoadingFix();
    
    // 3. Execute on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', executeLoadingFix);
    } else {
        executeLoadingFix();
    }
    
    // 4. Execute on window load
    window.addEventListener('load', executeLoadingFix);
    
    // 5. Continuous execution for first 5 seconds
    let fixCount = 0;
    const continuousFix = setInterval(() => {
        executeLoadingFix();
        fixCount++;
        
        if (fixCount >= 25) { // 25 * 200ms = 5 seconds
            clearInterval(continuousFix);
            console.log('🏁 Continuous loading fix completed!');
        }
    }, 200);
    
    // 6. Backup execution after 1, 3, 5 seconds
    setTimeout(executeLoadingFix, 1000);
    setTimeout(executeLoadingFix, 3000);
    setTimeout(executeLoadingFix, 5000);
    
    // ===== MUTATION OBSERVER (Kill new loading elements) =====
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver((mutations) => {
            let foundLoading = false;
            
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        const element = node;
                        const classList = element.className;
                        const id = element.id;
                        
                        if ((typeof classList === 'string' && 
                            (classList.includes('loading') || classList.includes('spinner') || classList.includes('preloader'))) ||
                            (typeof id === 'string' && 
                            (id.includes('loading') || id.includes('Loading') || id.includes('spinner')))) {
                            
                            foundLoading = true;
                            element.remove();
                        }
                        
                        // Check children
                        element.querySelectorAll && element.querySelectorAll('[class*="loading"], [id*="loading"], [class*="spinner"]').forEach(child => {
                            foundLoading = true;
                            child.remove();
                        });
                    }
                });
            });
            
            if (foundLoading) {
                console.log('🔍 New loading elements detected and destroyed!');
                executeLoadingFix();
            }
        });
        
        // Start observing after DOM is ready
        setTimeout(() => {
            observer.observe(document.body || document.documentElement, {
                childList: true,
                subtree: true
            });
            console.log('👀 Mutation observer started!');
        }, 1000);
        
        // Stop observing after 30 seconds
        setTimeout(() => {
            observer.disconnect();
            console.log('👀 Mutation observer stopped!');
        }, 30000);
    }
    
    // ===== JQUERY COMPATIBILITY =====
    function waitForJQuery() {
        if (typeof $ !== 'undefined' && typeof jQuery !== 'undefined') {
            $(document).ready(function() {
                $('.loading, .spinner, .preloader, #loadingOverlay, .loading-overlay').remove();
                $('[class*="loading"], [id*="loading"]').remove();
                $('body').removeClass('loading').css({
                    'overflow': 'visible',
                    'pointer-events': 'auto',
                    'opacity': '1',
                    'visibility': 'visible'
                });
                
                console.log('✅ jQuery cleanup completed!');
            });
        } else {
            setTimeout(waitForJQuery, 100);
        }
    }
    
    // Start jQuery compatibility after 1 second
    setTimeout(waitForJQuery, 1000);
    
    // ===== OVERRIDE POTENTIAL LOADING FUNCTIONS =====
    setTimeout(() => {
        // Override common loading functions
        window.showLoading = function() { console.log('🚫 showLoading() blocked!'); };
        window.hideLoading = function() { console.log('✅ hideLoading() called!'); };
        
        // Override jQuery loading functions
        if (typeof $ !== 'undefined') {
            $.fn.loading = function() { return this; };
            $.loading = function() {};
        }
        
        // Override TeddyBearShop loading methods
        if (window.TeddyBearShop && window.TeddyBearShop.prototype) {
            const proto = window.TeddyBearShop.prototype;
            if (proto.showLoading) proto.showLoading = function() {};
            if (proto.waitForDOM) proto.waitForDOM = function() { return Promise.resolve(); };
        }
        
        console.log('🔒 Loading functions overridden!');
        
    }, 2000);
    
    // ===== FINAL STATUS =====
    setTimeout(() => {
        const remainingLoading = document.querySelectorAll('[class*="loading"], [id*="loading"], .spinner, .preloader');
        
        if (remainingLoading.length === 0) {
            console.log('🎉 SUCCESS: All loading elements eliminated!');
            console.log('🐻 Teddy Bear Shop is now loading-free!');
        } else {
            console.warn(`⚠️ ${remainingLoading.length} loading elements still exist:`, remainingLoading);
            // Final destruction attempt
            remainingLoading.forEach(el => el.remove());
        }
        
        // Final body check
        if (document.body.style.overflow !== 'visible') {
            document.body.style.overflow = 'visible';
        }
        
        console.log('🏆 ULTIMATE LOADING FIX COMPLETED!');
        
    }, 6000);
    
    // ===== EXPORT FOR DEBUG =====
    window.LoadingFix = {
        destroy: executeLoadingFix,
        status: function() {
            const loading = document.querySelectorAll('[class*="loading"], [id*="loading"], .spinner, .preloader');
            return {
                loadingElementsFound: loading.length,
                bodyOverflow: document.body.style.overflow,
                fixActive: true
            };
        },
        nuclear: nuclearLoadingDestroy
    };
    
})();

console.log('🚨 Ultimate Loading Fix Script Loaded! 🚨');