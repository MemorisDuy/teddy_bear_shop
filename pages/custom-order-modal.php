<?php
/**
 * Modal: Yêu cầu đặt hàng theo mẫu riêng
 * Include vào search.php khi không tìm thấy kết quả
 */
?>

<!-- MODAL: ĐẶT HÀNG THEO MẪU RIÊNG -->
<div class="modal fade" id="customOrderModal" tabindex="-1" aria-hidden="true" style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ff6b9d, #ff8e72); color: white; border: none;">
                <h5 class="modal-title" id="modalTitle" style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important; font-weight: bold;">
                    🎨 Đặt hàng theo mẫu riêng của bạn
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                
                <!-- STEP 1: NHẬP YÊU CẦU -->
                <div id="step1-form" style="display: block;">
                    <p style="font-size: 1.05rem; margin-bottom: 20px;">
                        <span style="font-size: 1.2rem; margin-right: 8px;">😊</span>
                        <strong>Bạn không tìm thấy mẫu ưng ý?</strong> Không sao! Hãy mô tả chi tiết mẫu gấu bông bạn muốn, 
                        chúng tôi sẽ giúp bạn thực hiện nó!
                    </p>
                    
                    <form id="customOrderForm" enctype="multipart/form-data" novalidate>
                        
                        <!-- Tên khách hàng -->
                        <div class="mb-3">
                            <label for="customerName" class="form-label" style="font-weight: 600; color: #e91e63;">
                                👤 Tên của bạn <span style="color: red;">*</span>
                            </label>
                            <input type="text" class="form-control" id="customerName" name="customer_name" 
                                   placeholder="VD: Nguyễn Văn A" required 
                                   style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                            <div class="invalid-feedback" style="display: block; margin-top: 5px; color: #dc3545;">
                                Vui lòng nhập tên của bạn
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="customerEmail" class="form-label" style="font-weight: 600; color: #e91e63;">
                                📧 Email <span style="color: red;">*</span>
                            </label>
                            <input type="email" class="form-control" id="customerEmail" name="customer_email" 
                                   placeholder="vd@example.com" required 
                                   style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                            <div class="invalid-feedback" style="display: block; margin-top: 5px; color: #dc3545;">
                                Vui lòng nhập email hợp lệ
                            </div>
                        </div>
                        
                        <!-- Điện thoại -->
                        <div class="mb-3">
                            <label for="customerPhone" class="form-label" style="font-weight: 600; color: #e91e63;">
                                📱 Số điện thoại <span style="color: red;">*</span>
                            </label>
                            <input type="tel" class="form-control" id="customerPhone" name="customer_phone" 
                                   placeholder="0908123456" required 
                                   style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                            <div class="invalid-feedback" style="display: block; margin-top: 5px; color: #dc3545;">
                                Vui lòng nhập số điện thoại
                            </div>
                        </div>
                        
                        <!-- Từ khóa tìm kiếm -->
                        <div class="mb-3">
                            <label for="searchKeyword" class="form-label" style="font-weight: 600; color: #e91e63;">
                                🔍 Mẫu gấu bạn tìm <span style="color: red;">*</span>
                            </label>
                            <input type="text" class="form-control" id="searchKeyword" name="search_keyword" 
                                   required placeholder="VD: Gấu nâu, Gấu panda, Gấu teddy..."
                                   style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                            <small class="text-muted">Bạn có thể chỉnh sửa hoặc nhập lại nếu cần</small>
                        </div>
                        
                        <!-- Mô tả chi tiết -->
                        <div class="mb-3">
                            <label for="description" class="form-label" style="font-weight: 600; color: #e91e63;">
                                📝 Mô tả chi tiết mẫu gấu <span style="color: red;">*</span>
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="5" 
                                      placeholder="VD: Gấu nâu, kích thước 30cm, nên mềm mại, có dây buộc cổ màu hồng, mặt nên ngây thơ, mắt tròn to..."
                                      required style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important; resize: vertical;"></textarea>
                            <small class="text-muted">Hãy mô tả: màu sắc, kích thước, đặc điểm riêng, phong cách,...</small>
                            <div class="invalid-feedback" style="display: block; margin-top: 5px; color: #dc3545;">
                                Vui lòng mô tả chi tiết
                            </div>
                        </div>
                        
                        <!-- Upload ảnh -->
                        <div class="mb-3">
                            <label for="customImage" class="form-label" style="font-weight: 600; color: #e91e63;">
                                🖼️ Hình ảnh tham khảo
                            </label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="customImage" name="image" 
                                       accept="image/*" 
                                       style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                                <span class="input-group-text" style="background-color: #f5f5f5;">
                                    📁 Chọn ảnh
                                </span>
                            </div>
                            <small class="text-muted">Tối đa 5MB. Chất lượng ảnh: JPG, PNG, GIF</small>
                            
                            <!-- Preview ảnh -->
                            <div id="imagePreview" style="margin-top: 15px; display: none;">
                                <p style="font-weight: 600; margin-bottom: 10px;">📸 Xem trước:</p>
                                <img id="previewImage" src="" alt="Preview" 
                                     style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 1px solid #ddd; padding: 5px;">
                            </div>
                        </div>
                        
                        <!-- Thông báo -->
                        <div id="formMessage" class="alert" role="alert" style="display: none; margin-top: 15px;">
                            <span id="formMessageText"></span>
                        </div>
                        
                        <!-- Buttons - Step 1 -->
                        <div class="d-flex gap-2" style="margin-top: 25px;">
                            <button type="submit" class="btn btn-primary flex-grow-1" 
                                    id="submitBtn" 
                                    style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important; font-weight: bold; padding: 10px;">
                                <span id="submitBtnText">
                                    <span style="font-size: 1.1rem; margin-right: 5px;">📤</span> Gửi yêu cầu
                                </span>
                                <span id="submitBtnSpinner" class="spinner-border spinner-border-sm ms-2" role="status" 
                                      aria-hidden="true" style="display: none;"></span>
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                    id="closeBtn"
                                    style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                                Đóng
                            </button>
                        </div>
                        
                        <!-- Thông tin thêm -->
                        <div style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin-top: 20px; border-radius: 5px;">
                            <p style="margin: 0; color: #333;">
                                <strong style="color: #4caf50;">✨ Quy trình:</strong><br>
                                • Bước 1: Bạn mô tả mẫu (đang làm) ← Bạn ở đây<br>
                                • Bước 2: Chúng tôi tìm & gợi ý ảnh (tự động)<br>
                                • Bước 3: Bạn chọn ảnh yêu thích & đánh giá<br>
                                • Bước 4: Shop liên hệ để thảo luận & thực hiện<br>
                                • Bước 5: Giao hàng nhanh chóng
                            </p>
                        </div>
                    </form>
                </div>
                
                <!-- STEP 2: CHỌN & ĐÁNH GIÁ ẢNH -->
                <div id="step2-images" style="display: none;">
                    <p style="font-size: 1.05rem; margin-bottom: 15px;">
                        <span style="font-size: 1.2rem; margin-right: 8px;">🖼️</span>
                        <strong>Chọn ảnh gấu bông bạn thích nhất:</strong>
                    </p>
                    
                    <!-- Danh sách ảnh -->
                    <div id="imagesContainer" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0;"></div>
                    
                    <!-- Form chọn ảnh & đánh giá -->
                    <form id="confirmOrderForm" novalidate>
                        <input type="hidden" id="customOrderIdConfirm" name="custom_order_id">
                        <input type="hidden" id="selectedImageUrl" name="selected_image_url">
                        
                        <!-- Đánh giá sao -->
                        <div class="mb-3">
                            <label style="font-weight: 600; color: #e91e63;">
                                ⭐ Bạn cảm thấy thế nào về ảnh này? <span style="color: red;">*</span>
                            </label>
                            <div id="ratingStars" style="font-size: 2rem; margin: 10px 0; cursor: pointer;">
                                <span class="star" data-rating="1" style="margin: 0 5px; cursor: pointer;">☆</span>
                                <span class="star" data-rating="2" style="margin: 0 5px; cursor: pointer;">☆</span>
                                <span class="star" data-rating="3" style="margin: 0 5px; cursor: pointer;">☆</span>
                                <span class="star" data-rating="4" style="margin: 0 5px; cursor: pointer;">☆</span>
                                <span class="star" data-rating="5" style="margin: 0 5px; cursor: pointer;">☆</span>
                            </div>
                            <input type="hidden" id="ratingValue" name="rating" value="0">
                            <small class="text-muted" id="ratingText">Chưa có đánh giá</small>
                        </div>
                        
                        <!-- Thông báo lỗi/thành công -->
                        <div id="confirmMessage" class="alert" role="alert" style="display: none; margin-top: 15px;">
                            <span id="confirmMessageText"></span>
                        </div>
                        
                        <!-- Buttons - Step 2 -->
                        <div class="d-flex gap-2" style="margin-top: 25px;">
                            <button type="submit" class="btn btn-success flex-grow-1" 
                                    id="confirmBtn" 
                                    disabled
                                    style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important; font-weight: bold; padding: 10px;">
                                <span id="confirmBtnText">
                                    <span style="font-size: 1.1rem; margin-right: 5px;">✅</span> Xác nhận & Gửi
                                </span>
                                <span id="confirmBtnSpinner" class="spinner-border spinner-border-sm ms-2" role="status" 
                                      aria-hidden="true" style="display: none;"></span>
                            </button>
                            <button type="button" class="btn btn-secondary" 
                                    id="backBtn"
                                    style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                                Quay lại
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Xử lý Custom Order - 2 bước
document.addEventListener('DOMContentLoaded', function() {
    const customOrderForm = document.getElementById('customOrderForm');
    const confirmOrderForm = document.getElementById('confirmOrderForm');
    const submitBtn = document.getElementById('submitBtn');
    const confirmBtn = document.getElementById('confirmBtn');
    const backBtn = document.getElementById('backBtn');
    const imageInput = document.getElementById('customImage');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const formMessage = document.getElementById('formMessage');
    const formMessageText = document.getElementById('formMessageText');
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmMessageText = document.getElementById('confirmMessageText');
    const imagesContainer = document.getElementById('imagesContainer');
    const ratingStars = document.querySelectorAll('#ratingStars .star');
    const ratingValue = document.getElementById('ratingValue');
    const ratingText = document.getElementById('ratingText');
    const customOrderIdConfirm = document.getElementById('customOrderIdConfirm');
    const selectedImageUrl = document.getElementById('selectedImageUrl');
    
    // Set search keyword
    const urlParams = new URLSearchParams(window.location.search);
    const keyword = urlParams.get('q');
    if (keyword) {
        document.getElementById('searchKeyword').value = keyword;
    }
    
    // Preview ảnh
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                previewImage.src = event.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });
    
    // ===== STEP 1: SUBMIT YÊU CẦU =====
    customOrderForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!customOrderForm.checkValidity()) {
            formMessage.style.display = 'block';
            formMessage.className = 'alert alert-danger';
            formMessageText.textContent = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
            return;
        }
        
        submitBtn.disabled = true;
        document.getElementById('submitBtnSpinner').style.display = 'inline-block';
        
        const formData = new FormData(customOrderForm);
        
        try {
            const response = await fetch('<?php echo SITE_URL; ?>/pages/api-custom-order.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            
            const responseText = await response.text();
            const data = JSON.parse(responseText);
            
            if (data.success) {
                // Lưu custom_order_id
                customOrderIdConfirm.value = data.custom_order_id;
                
                // Hiển thị ảnh lên step 2
                displayImages(data.images || []);
                
                // Chuyển sang step 2
                showStep2();
                
                formMessage.style.display = 'block';
                formMessage.className = 'alert alert-success';
                formMessageText.innerHTML = `
                    <strong>✅ Yêu cầu đã gửi!</strong><br>
                    <span style="font-size: 0.95em;">Đang tìm ảnh gợi ý từ Pixabay...</span>
                `;
            } else {
                formMessage.style.display = 'block';
                formMessage.className = 'alert alert-danger';
                formMessageText.textContent = '❌ ' + (data.message || 'Có lỗi xảy ra!');
            }
            
        } catch (error) {
            console.error('Error:', error);
            formMessage.style.display = 'block';
            formMessage.className = 'alert alert-danger';
            formMessageText.textContent = '❌ ' + error.message;
        } finally {
            submitBtn.disabled = false;
            document.getElementById('submitBtnSpinner').style.display = 'none';
        }
    });
    
    // ===== STEP 2: CHỌN HÌNH & ĐÁNH GIÁ =====
    
    // Hiển thị danh sách ảnh
    function displayImages(images) {
        imagesContainer.innerHTML = '';
        
        if (!images || images.length === 0) {
            imagesContainer.innerHTML = '<div style="grid-column: 1/-1; padding: 20px; text-align: center; color: #999;">Không tìm thấy ảnh. Vui lòng thử lại sau!</div>';
            return;
        }
        
        images.forEach((img, index) => {
            const imageCard = document.createElement('div');
            imageCard.style.cssText = 'border: 3px solid #ddd; border-radius: 8px; overflow: hidden; cursor: pointer; transition: all 0.3s;';
            imageCard.innerHTML = `
                <img src="${img.thumb}" style="width: 100%; height: 200px; object-fit: cover; display: block;" alt="Image ${index + 1}">
                <div style="padding: 10px; background: #f5f5f5; text-align: center;">
                    <label style="cursor: pointer; margin: 0; display: flex; align-items: center; justify-content: center; gap: 5px;">
                        <input type="radio" name="selected_image_radio" value="${img.full}" style="cursor: pointer;" data-image-index="${index}">
                        <span style="margin: 0;">Chọn ảnh này</span>
                    </label>
                </div>
            `;
            
            imageCard.addEventListener('click', function() {
                console.log('Selected image ' + (index + 1) + ', URL:', img.full);
                // Deselect all
                document.querySelectorAll('input[name="selected_image_radio"]').forEach(r => r.checked = false);
                // Select this
                const radio = imageCard.querySelector('input[name="selected_image_radio"]');
                radio.checked = true;
                // Update selected_image_url
                selectedImageUrl.value = img.full;
                console.log('Updated selectedImageUrl to:', selectedImageUrl.value);
                // Highlight
                document.querySelectorAll('#imagesContainer > div').forEach(card => {
                    card.style.borderColor = '#ddd';
                });
                imageCard.style.borderColor = '#ff6b9d';
                // Enable confirm button if rating selected
                updateConfirmBtn();
            });
            
            imagesContainer.appendChild(imageCard);
        });
    }
    
    // Đánh giá sao
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            ratingValue.value = rating;
            
            // Update UI
            ratingStars.forEach((s, idx) => {
                s.textContent = (idx + 1) <= rating ? '⭐' : '☆';
            });
            
            const ratings = ['', 'Không thích', 'Hơi thích', 'Thích', 'Rất thích', 'Yêu thích! 💕'];
            ratingText.textContent = ratings[rating];
            
            updateConfirmBtn();
        });
    });
    
    function updateConfirmBtn() {
        const hasSelected = selectedImageUrl.value !== '';
        const hasRating = ratingValue.value > 0;
        confirmBtn.disabled = !(hasSelected && hasRating);
    }
    
    // ===== STEP 2: SUBMIT CONFIRM =====
    confirmOrderForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        console.log('Form submit - customOrderIdConfirm:', customOrderIdConfirm.value);
        console.log('Form submit - selectedImageUrl:', selectedImageUrl.value);
        console.log('Form submit - rating:', ratingValue.value);
        
        if (!selectedImageUrl.value) {
            confirmMessage.style.display = 'block';
            confirmMessage.className = 'alert alert-danger';
            confirmMessageText.textContent = '❌ Vui lòng chọn một ảnh!';
            return;
        }
        
        if (ratingValue.value < 1) {
            confirmMessage.style.display = 'block';
            confirmMessage.className = 'alert alert-danger';
            confirmMessageText.textContent = '❌ Vui lòng đánh giá ảnh!';
            return;
        }
        
        confirmBtn.disabled = true;
        document.getElementById('confirmBtnSpinner').style.display = 'inline-block';
        
        const formData = new FormData();
        formData.append('custom_order_id', customOrderIdConfirm.value);
        formData.append('selected_image_url', selectedImageUrl.value);
        formData.append('rating', ratingValue.value);
        
        console.log('Sending to API - custom_order_id:', customOrderIdConfirm.value, 'image:', selectedImageUrl.value, 'rating:', ratingValue.value);
        
        try {
            const response = await fetch('<?php echo SITE_URL; ?>/pages/api-confirm-custom-order.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            
            const responseText = await response.text();
            console.log('API Response:', responseText);
            const data = JSON.parse(responseText);
            
            if (data.success) {
                confirmMessage.style.display = 'block';
                confirmMessage.className = 'alert alert-success';
                confirmMessageText.innerHTML = `
                    <strong>✅ ${data.message}</strong><br>
                    <span style="font-size: 0.9em;">Cảm ơn bạn! Shop sẽ liên hệ sớm.</span>
                `;
                
                // Đóng modal sau 3 giây
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('customOrderModal'));
                    if (modal) modal.hide();
                }, 3000);
            } else {
                confirmMessage.style.display = 'block';
                confirmMessage.className = 'alert alert-danger';
                confirmMessageText.textContent = '❌ ' + (data.message || 'Có lỗi xảy ra!');
            }
            
        } catch (error) {
            console.error('Error:', error);
            confirmMessage.style.display = 'block';
            confirmMessage.className = 'alert alert-danger';
            confirmMessageText.textContent = '❌ Lỗi kết nối: ' + error.message;
        } finally {
            confirmBtn.disabled = false;
            document.getElementById('confirmBtnSpinner').style.display = 'none';
        }
    });
    
    // Back button
    backBtn.addEventListener('click', function() {
        showStep1();
        confirmMessage.style.display = 'none';
    });
    
    // Functions to show/hide steps
    function showStep1() {
        document.getElementById('step1-form').style.display = 'block';
        document.getElementById('step2-images').style.display = 'none';
        document.getElementById('modalTitle').textContent = '🎨 Đặt hàng theo mẫu riêng của bạn - Bước 1: Mô tả';
    }
    
    function showStep2() {
        document.getElementById('step1-form').style.display = 'none';
        document.getElementById('step2-images').style.display = 'block';
        document.getElementById('modalTitle').textContent = '🎨 Đặt hàng theo mẫu riêng của bạn - Bước 2: Chọn ảnh';
    }
});
</script>
