<!-- CLEAN AI CHATBOT -->
<div id="uniqueAiBtn" style="position:fixed;bottom:25px;right:20px;z-index:99999;background:#4ade80;border-radius:50%;width:56px;height:56px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 15px rgba(74,222,128,0.4);">
    <span style="font-size:24px;">🤖</span>
</div>

<div id="uniqueAiChat" style="position:fixed;bottom:20px;right:20px;width:350px;max-width:calc(100vw - 24px);background:white;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.2);z-index:99999;display:none;">
    <div style="background:#e75480;color:#fff !important;padding:15px;border-radius:15px 15px 0 0;display:flex;justify-content:space-between;align-items:center;">
        <span style='color:#fff !important;'><strong>🤖 Teddy AI</strong></span>
        <button id="uniqueAiClose" style="background:none;border:none;color:#fff !important;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div id="uniqueAiMessages" style="height:250px;overflow-y:auto;padding:15px;background:#f8f9fa;"></div>
    <div style="padding:15px;border-top:1px solid #eee;">
        <form id="uniqueAiForm" style="display:flex;gap:10px;">
            <input id="uniqueAiInput" type="text" placeholder="Hỏi về gấu bông..." style="flex:1;padding:10px;border:1px solid #ddd;border-radius:20px;outline:none;">
            <button type="submit" style="background:#e75480;color:white;border:none;padding:10px 15px;border-radius:20px;cursor:pointer;">Gửi</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const btn = document.getElementById('uniqueAiBtn');
        const chat = document.getElementById('uniqueAiChat');
        const close = document.getElementById('uniqueAiClose');
        const messages = document.getElementById('uniqueAiMessages');
        const form = document.getElementById('uniqueAiForm');
        const input = document.getElementById('uniqueAiInput');
        if (!btn || !chat || !close || !messages || !form || !input) return;
        messages.innerHTML = '<div style="background:white;padding:10px;border-radius:10px;margin:5px 0;"><strong>Teddy AI:</strong> Xin chào! Tôi là trợ lý AI của Teddy Bear Paradise 🧸</div>';
        let isTyping = false;
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            btn.style.display = 'none';
            chat.style.display = 'block';
            setTimeout(() => input.focus(), 100);
        });
        close.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            chat.style.setProperty('display', 'none', 'important');
            btn.style.setProperty('display', 'flex', 'important');
        });
        chat.addEventListener('click', function(e) {
            if (e.target === chat) close.click();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && chat.style.display === 'block') close.click();
        });
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (isTyping) return;
            const msg = input.value.trim();
            if (!msg) return;
            isTyping = true;
            messages.innerHTML += '<div style="background:#e75480;color:#fff !important;padding:10px;border-radius:10px;margin:5px 0 5px 40px;text-align:right;font-weight:bold;text-shadow:0 1px 2px rgba(0,0,0,0.12);">' + '<span style=\'color:#fff !important;\'><strong style=\'color:#fff !important;\'>Bạn:</strong> ' + msg + '</span></div>';
            input.value = '';
            messages.innerHTML += '<div id="loading-' + Date.now() + '" style="text-align:center;padding:10px;color:#666;">🤖 Đang trả lời...</div>';
            messages.scrollTop = messages.scrollHeight;
            try {
                const res = await fetch('<?php echo defined("SITE_URL") ? SITE_URL : "/teddy_bear_shop"; ?>/pages/api-ai-bot.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'question=' + encodeURIComponent(msg)
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                const loadings = messages.querySelectorAll('[id^="loading-"]');
                loadings.forEach(l => l.remove());
                const answer = data.answer || 'Lỗi API!';
                messages.innerHTML += '<div style="background:white;padding:10px;border-radius:10px;margin:5px 40px 5px 0;"><span style=\'color:#e75480 !important;\'><strong>Teddy AI:</strong></span> ' + answer + '</div>';
                messages.scrollTop = messages.scrollHeight;
            } catch (error) {
                const loadings = messages.querySelectorAll('[id^="loading-"]');
                loadings.forEach(l => l.remove());
                messages.innerHTML += '<div style="background:#ff6b6b;color:white;padding:10px;border-radius:10px;margin:5px;text-align:center;">❌ Lỗi: ' + error.message + '</div>';
            }
            isTyping = false;
            input.focus();
        });
    }, 500);
});
</script>
