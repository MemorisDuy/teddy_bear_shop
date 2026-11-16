/**
 * ADDRESS AUTO-COMPLETE JS
 * File: js/address-autocomplete.js
 * Xử lý gợi ý địa chỉ từ Goong + Gemini
 */

class AddressAutocomplete {
    constructor(inputSelector, containerSelector) {
        this.input = document.querySelector(inputSelector);
        this.container = document.querySelector(containerSelector);
        this.debounceTimer = null;
        this.resultsContainer = null;
        
        if (!this.input || !this.container) {
            console.error('❌ AddressAutocomplete: Không tìm thấy input hoặc container');
            return;
        }
        
        this.init();
    }
    
    init() {
        // Tạo HTML cho suggestions
        this.resultsContainer = document.createElement('div');
        this.resultsContainer.className = 'address-suggestions-container';
        this.resultsContainer.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-top: none;
            background: white;
            z-index: 1000;
            display: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        `;
        
        this.input.parentElement.style.position = 'relative';
        this.input.parentElement.appendChild(this.resultsContainer);
        
        // Event listeners
        this.input.addEventListener('input', (e) => this.onInput(e));
        this.input.addEventListener('blur', () => this.onBlur());
        this.input.addEventListener('keydown', (e) => this.onKeydown(e));
        
        // Click outside để đóng
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.address-suggestions-container') && e.target !== this.input) {
                this.hide();
            }
        });
    }
    
    onInput(e) {
        clearTimeout(this.debounceTimer);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            this.hide();
            return;
        }
        
        // Debounce: chờ 300ms mới gọi API
        this.debounceTimer = setTimeout(() => {
            this.fetchSuggestions(query);
        }, 300);
    }
    
    onBlur() {
        // Chờ 200ms để cho user kịp click vào suggestion
        setTimeout(() => this.hide(), 200);
    }
    
    onKeydown(e) {
        if (e.key === 'Escape') {
            this.hide();
        }
    }
    
    async fetchSuggestions(query) {
        try {
            this.input.style.borderColor = '#1890ff';
            
            const response = await fetch(`../pages/api-address-autocomplete.php?q=${encodeURIComponent(query)}`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            this.displaySuggestions(data.results || []);
            
        } catch (error) {
            console.error('❌ Lỗi fetch gợi ý:', error);
            this.input.style.borderColor = '#ff4d4f';
            this.showError('Không thể tải gợi ý địa chỉ');
        }
    }
    
    displaySuggestions(results) {
        this.resultsContainer.innerHTML = '';
        
        if (results.length === 0) {
            this.showError('Không tìm thấy địa chỉ phù hợp');
            return;
        }
        
        results.forEach((result, index) => {
            const item = document.createElement('div');
            item.className = 'address-suggestion-item';
            item.style.cssText = `
                padding: 12px 15px;
                border-bottom: 1px solid #f0f0f0;
                cursor: pointer;
                transition: background 0.2s;
                font-size: 14px;
            `;
            
            // Hiển thị địa chỉ chính
            let html = `<div style="font-weight: 500; color: #1890ff;">${this.escapeHtml(result.address)}</div>`;
            
            // Hiển thị thông tin chuẩn hóa nếu có
            if (result.normalized) {
                const norm = result.normalized;
                const badge = norm.is_new_format ? 
                    '<span style="background: #52c41a; color: white; padding: 2px 6px; font-size: 11px; border-radius: 3px; margin-left: 5px;">34 Tỉnh Mới</span>' :
                    '<span style="background: #faad14; color: white; padding: 2px 6px; font-size: 11px; border-radius: 3px; margin-left: 5px;">64 Tỉnh Cũ</span>';
                
                html += `
                    <div style="margin-top: 5px; color: #666; font-size: 12px;">
                        <strong>Tỉnh:</strong> ${this.escapeHtml(norm.province)} ${badge}<br>
                        ${norm.district ? `<strong>Quận/Huyện:</strong> ${this.escapeHtml(norm.district)}<br>` : ''}
                        ${norm.ward ? `<strong>Phường/Xã:</strong> ${this.escapeHtml(norm.ward)}<br>` : ''}
                        ${norm.street ? `<strong>Đường:</strong> ${this.escapeHtml(norm.street)}` : ''}
                    </div>
                `;
            }
            
            item.innerHTML = html;
            
            item.addEventListener('mouseover', () => {
                item.style.background = '#f5f5f5';
            });
            
            item.addEventListener('mouseout', () => {
                item.style.background = 'white';
            });
            
            item.addEventListener('click', () => {
                this.selectAddress(result);
            });
            
            this.resultsContainer.appendChild(item);
        });
        
        this.show();
    }
    
    selectAddress(result) {
        // Cập nhật input
        this.input.value = result.address;
        
        // Dispatch custom event để form có thể xử lý
        const event = new CustomEvent('addressSelected', {
            detail: {
                address: result.address,
                full_address: result.full_address,
                lat: result.lat,
                lng: result.lng,
                normalized: result.normalized
            }
        });
        
        this.input.dispatchEvent(event);
        
        // Debug log
        console.log('✅ Địa chỉ đã chọn:', result);
        
        this.hide();
    }
    
    showError(message) {
        this.resultsContainer.innerHTML = `
            <div style="padding: 15px; text-align: center; color: #ff4d4f; font-size: 13px;">
                ⚠️ ${this.escapeHtml(message)}
            </div>
        `;
        this.show();
    }
    
    show() {
        this.resultsContainer.style.display = 'block';
    }
    
    hide() {
        this.resultsContainer.style.display = 'none';
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Export cho browser
if (typeof window !== 'undefined') {
    window.AddressAutocomplete = AddressAutocomplete;
}
