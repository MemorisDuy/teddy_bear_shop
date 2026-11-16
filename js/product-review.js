/**
 * Product Review Handler - DEBUG VERSION
 * Xử lý form đánh giá sản phẩm
 */

document.addEventListener('DOMContentLoaded', function() {
    const reviewForm = document.getElementById('customerReviewForm');
    
    if (reviewForm) {
        console.log('✅ Review form found');
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('📤 Form submitted');
            submitProductReview();
        });
    } else {
        console.error('❌ Review form NOT found');
    }
});

function submitProductReview() {
    console.log('🔍 Starting submitProductReview...');
    
    const form = document.getElementById('customerReviewForm');
    if (!form) {
        console.error('❌ Form không tìm thấy');
        return;
    }
    
    // Get form values
    const reviewerName = document.getElementById('reviewerName')?.value.trim() || '';
    const reviewerEmail = document.getElementById('reviewerEmail')?.value.trim() || '';
    const reviewerRating = document.getElementById('reviewerRating')?.value || '';
    const reviewerComment = document.getElementById('reviewerComment')?.value.trim() || '';
    
    console.log('📝 Form Data:', {
        name: reviewerName,
        email: reviewerEmail,
        rating: reviewerRating,
        comment: reviewerComment
    });
    
    // Get product ID from page
    const productId = document.querySelector('[data-product-id]')?.getAttribute('data-product-id') || 
                      window.productId || 
                      extractProductIdFromUrl();
    
    console.log('🆔 Product ID:', productId);
    
    if (!productId) {
        showNotification('❌ Không tìm thấy ID sản phẩm', 'error');
        return;
    }
    
    // Validate
    if (!reviewerName) {
        showNotification('❌ Vui lòng nhập tên của bạn', 'error');
        return;
    }
    
    if (!reviewerRating) {
        showNotification('❌ Vui lòng chọn số sao đánh giá', 'error');
        return;
    }
    
    if (!reviewerComment) {
        showNotification('❌ Vui lòng nhập nội dung đánh giá', 'error');
        return;
    }
    
    if (reviewerComment.length < 5) {
        showNotification('❌ Nội dung phải ít nhất 5 ký tự', 'error');
        return;
    }
    
    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Đang gửi...';
    }
    
    // Prepare data
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('reviewer_name', reviewerName);
    formData.append('reviewer_email', reviewerEmail);
    formData.append('rating', reviewerRating);
    formData.append('comment', reviewerComment);
    
    console.log('📤 Sending to ../pages/api-add-review.php');
    
    // Send to API
    fetch('../pages/api-add-review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('✅ Response data:', data);
        
        if (data.success) {
            // Clear form
            form.reset();
            
            // Show success message
            showNotification(data.message, 'success');
            
            // Add review to list (optional)
            if (data.review) {
                addReviewToList(data.review);
            }
            
            // Wait 1 second then reload page to show updated reviews
            setTimeout(() => {
                console.log('🔄 Reloading page...');
                location.reload(true); // Force hard reload to bypass cache
            }, 1000);
            
        } else {
            showNotification(data.message || '❌ Có lỗi xảy ra', 'error');
            console.error('❌ API Error:', data.message);
        }
    })
    .catch(error => {
        console.error('❌ Fetch Error:', error);
        showNotification('❌ Lỗi kết nối: ' + error.message, 'error');
    })
    .finally(() => {
        // Re-enable submit button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Gửi đánh giá';
        }
    });
}

function addReviewToList(review) {
    const reviewsList = document.getElementById('reviewsList');
    if (!reviewsList) return;
    
    const stars = '⭐'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
    const statusBadge = review.status === 'pending' ? 
        '<span class="badge bg-warning">⏳ Chờ duyệt</span>' : 
        '<span class="badge bg-success">✓ Đã duyệt</span>';
    
    const reviewHTML = `
        <div class="review-item mb-3 p-3 border rounded">
            <div class="review-header d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="mb-1">${review.customer_name || review.reviewer_name}</h6>
                    <small class="text-muted">${review.created_at}</small>
                </div>
                <div>
                    ${statusBadge}
                </div>
            </div>
            <div class="review-rating mb-2">
                <span class="text-warning" style="font-size: 1.1rem;">${stars}</span>
            </div>
            <p class="review-comment mb-0">${review.comment}</p>
        </div>
    `;
    
    // Insert at top
    reviewsList.insertAdjacentHTML('afterbegin', reviewHTML);
}

function extractProductIdFromUrl() {
    // Get from URL parameter
    const params = new URLSearchParams(window.location.search);
    const slug = params.get('slug');
    
    // Try to find data attribute in page
    const productElement = document.querySelector('[data-product-id]');
    if (productElement) {
        return productElement.getAttribute('data-product-id');
    }
    
    return null;
}

// Ensure showNotification function exists
if (typeof showNotification !== 'function') {
    window.showNotification = function(message, type = 'info') {
        const notification = document.createElement('div');
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 'alert-warning';
        
        notification.className = `alert ${alertClass} alert-dismissible fade show`;
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
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    };
}

console.log('✅ product-review.js loaded');
