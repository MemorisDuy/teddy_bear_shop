/*
 * TEDDY AI CHATBOT JAVASCRIPT - FIXED VERSION
 * File: js/ai-chatbot.js
 * Version: 2.2 - Fixed for unique IDs
 */

console.log('🤖 Loading Teddy AI Chatbot v2.2 (Fixed)...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing AI Chatbot...');
    
    // FIXED: Use correct unique IDs
    const aiBtn = document.getElementById('uniqueAiBtn');
    const aiChat = document.getElementById('uniqueAiChat');
    const aiClose = document.getElementById('uniqueAiClose');
    const messages = document.getElementById('uniqueAiMessages');
    const input = document.getElementById('uniqueAiInput');
    const form = document.getElementById('uniqueAiForm');
    const sendBtn = form ? form.querySelector('button[type="submit"]') : null;
    
    console.log('🔍 Element check:', {
        aiBtn: !!aiBtn,
        aiChat: !!aiChat, 
        aiClose: !!aiClose,
        messages: !!messages,
        input: !!input,
        form: !!form,
        sendBtn: !!sendBtn
    });
    
    if (!aiBtn || !aiChat || !aiClose || !messages || !input || !form) {
        console.error('❌ AI Chatbot: Missing elements! Check HTML IDs.');
        return;
    }
    
    console.log('✅ All AI elements found');
    
    let isTyping = false;
    let messageCount = 0;
    
    // Initialize display states
    aiBtn.style.display = 'flex';
    aiChat.style.display = 'none';
    
    // Show welcome message
    messages.innerHTML = `
        <div style="background:white;padding:10px;border-radius:10px;margin:5px 0;">
            <strong>Teddy AI:</strong> Xin chào! Tôi là trợ lý AI của Teddy Bear Paradise. 
            Bạn muốn tìm gấu bông gì hôm nay? 🧸
            <div style="font-size: 0.7rem; color: #999; margin-top: 4px;">
                ⚡ Powered by Intelligent Engine
            </div>
        </div>
    `;
    
    // Show/Hide functions
    function showChat() {
        console.log('📱 Opening chat...');
        aiBtn.style.display = 'none';
        aiChat.style.display = 'block';
        setTimeout(() => {
            input.focus();
        }, 100);
    }
    
    function hideChat() {
        console.log('📱 Closing chat...');
        aiChat.style.display = 'none';
        aiBtn.style.display = 'flex';
    }
    
    // Add message function
    function addMessage(message, isUser = false, responseData = null) {
        messageCount++;
        
        let responseTimeInfo = '';
        let poweredByInfo = '';
        
        if (!isUser && responseData) {
            if (responseData.response_time) {
                responseTimeInfo = `<div style="font-size: 0.7rem; color: #999; margin-top: 4px;">⚡ ${(responseData.response_time/1000).toFixed(1)}s</div>`;
            }
            if (responseData.powered_by) {
                poweredByInfo = `<div style="font-size: 0.7rem; color: #999; margin-top: 4px;">🤖 ${responseData.powered_by}</div>`;
            }
            if (responseData.cached) {
                poweredByInfo = `<div style="font-size: 0.7rem; color: #999; margin-top: 4px;">📋 Cached response</div>`;
            }
        }
        
        const msgStyle = isUser ? 
            'background:#e75480;color:white;padding:10px;border-radius:10px;margin:5px 0 5px 40px;text-align:right;' :
            'background:white;padding:10px;border-radius:10px;margin:5px 40px 5px 0;';
            
        const msgPrefix = isUser ? '<strong>Bạn:</strong>' : '<strong>Teddy AI:</strong>';
        
        messages.innerHTML += `
            <div style="${msgStyle}">
                ${msgPrefix} ${message}
                ${responseTimeInfo}
                ${poweredByInfo}
            </div>
        `;
        
        // Auto scroll
        messages.scrollTop = messages.scrollHeight;
        
        console.log(`💬 Message added: ${isUser ? 'User' : 'AI'} - ${messageCount}`);
    }
    
    // Show loading
    function showLoading() {
        const loadingId = 'loading-' + Date.now();
        messages.innerHTML += `
            <div id="${loadingId}" style="text-align:center;padding:10px;color:#666;">
                🤖 Đang trả lời...
            </div>
        `;
        messages.scrollTop = messages.scrollHeight;
        return loadingId;
    }
    
    // Remove loading
    function removeLoading(loadingId) {
        const loadingElement = document.getElementById(loadingId);
        if (loadingElement) {
            loadingElement.remove();
        }
    }
    
    // Send message to API
    async function sendMessage(question) {
        if (isTyping || !question.trim()) {
            console.log('⚠️ Cannot send: typing or empty message');
            return;
        }
        
        isTyping = true;
        if (sendBtn) sendBtn.disabled = true;
        
        console.log('📤 Sending message:', question);
        
        // Add user message
        addMessage(question, true);
        
        // Clear input
        input.value = '';
        
        // Show loading
        const loadingId = showLoading();
        
        try {
            console.log('🌐 Calling API...');
            
            // Xác định đường dẫn API động
            let apiPath = 'pages/api-ai-bot.php';
            if (window.location.pathname.includes('/pages/')) {
                apiPath = '../pages/api-ai-bot.php';
            }
            const response = await fetch(apiPath, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'question=' + encodeURIComponent(question)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('✅ API Response:', data);
            
            // Remove loading
            removeLoading(loadingId);
            
            // Add AI response with metadata
            const answer = data.answer || 'Teddy AI chưa có câu trả lời, bạn thử lại nhé! 🧸';
            addMessage(answer, false, data);
            
        } catch (error) {
            console.error('❌ API Error:', error);
            
            // Remove loading
            removeLoading(loadingId);
            
            // Add error message
            addMessage(`⚠️ Lỗi kết nối! Chi tiết: ${error.message}<br><small>Vui lòng thử lại sau hoặc liên hệ hỗ trợ.</small>`);
        }
        
        isTyping = false;
        if (sendBtn) sendBtn.disabled = false;
        input.focus();
    }
    
    // FIXED: Event listeners with proper error handling
    try {
        aiBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showChat();
        });
        console.log('✅ Button click event bound');
    } catch (error) {
        console.error('❌ Button event error:', error);
    }
    
    try {
        aiClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            hideChat();
        });
        console.log('✅ Close click event bound');
    } catch (error) {
        console.error('❌ Close event error:', error);
    }
    
    // Form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = input.value.trim();
        
        if (message.length > 200) {
            input.style.borderColor = '#dc3545';
            setTimeout(() => {
                input.style.borderColor = '';
            }, 2000);
            return;
        }
        
        if (message) {
            sendMessage(message);
        }
    });
    
    // Input validation
    input.addEventListener('input', function() {
        if (this.value.length > 200) {
            this.style.borderColor = '#dc3545';
        } else {
            this.style.borderColor = '';
        }
    });
    
    // Enter key submit
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });
    
    // Escape key close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && aiChat.style.display === 'block') {
            hideChat();
        }
    });
    
    console.log('✅ Teddy AI Chatbot initialized successfully!');
});

// FIXED: Global functions with unique IDs
window.testAI = function() {
    const input = document.getElementById('uniqueAiInput');
    const form = document.getElementById('uniqueAiForm');
    if (input && form) {
        input.value = 'test từ console';
        form.dispatchEvent(new Event('submit'));
    }
};

window.showAI = function() {
    const btn = document.getElementById('uniqueAiBtn');
    if (btn) btn.click();
};

window.hideAI = function() {
    const close = document.getElementById('uniqueAiClose');
    if (close) close.click();
};

window.clearAIChat = function() {
    const messages = document.getElementById('uniqueAiMessages');
    if (messages) {
        messages.innerHTML = `
            <div style="background:white;padding:10px;border-radius:10px;margin:5px 0;">
                <strong>Teddy AI:</strong> Chat đã được xóa! Bạn muốn hỏi gì? 🧸
            </div>
        `;
    }
};

console.log('✅ AI Chatbot script loaded successfully!');
console.log('🔧 Debug commands: testAI(), showAI(), hideAI(), clearAIChat()');