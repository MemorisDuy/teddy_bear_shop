<?php
/**
 * Modal: Yêu cầu đặt hàng theo mẫu riêng
 * Giao diện hiện đại, sạch sẽ, icon chuẩn FontAwesome
 */
?>

<!-- MODAL: ĐẶT HÀNG THEO MẪU RIÊNG -->
<div class="modal fade" id="customOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-pink text-white py-3 px-4 border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">
                    <i class="fas fa-palette me-2"></i> Đặt hàng theo mẫu riêng của bạn
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4 p-md-5">
                
                <!-- BƯỚC 1: NHẬP THÔNG TIN VÀ YÊU CẦU -->
                <div id="step1-form" style="display: block;">
                    <div class="alert alert-info rounded-3 mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Bạn chưa tìm thấy mẫu ưng ý?</strong> Đừng ngần ngại miêu tả chú gấu bông bạn muốn, xưởng của chúng tôi sẽ thiết kế và may riêng cho bạn!
                    </div>
                    
                    <form id="customOrderForm" enctype="multipart/form-data" novalidate>
                        <div class="row g-3">
                            <!-- Tên khách hàng -->
                            <div class="col-md-6">
                                <label for="customerName" class="form-label fw-semibold text-secondary small">
                                    Họ và tên <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control rounded-3" id="customerName" name="customer_name" 
                                       placeholder="Nguyễn Văn A" required>
                                <div class="invalid-feedback">Vui lòng nhập tên của bạn</div>
                            </div>
                            
                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="customerEmail" class="form-label fw-semibold text-secondary small">
                                    Địa chỉ Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control rounded-3" id="customerEmail" name="customer_email" 
                                       placeholder="email@example.com" required>
                                <div class="invalid-feedback">Vui lòng nhập email hợp lệ</div>
                            </div>
                            
                            <!-- Điện thoại -->
                            <div class="col-md-6">
                                <label for="customerPhone" class="form-label fw-semibold text-secondary small">
                                    Số điện thoại <span class="text-danger">*</span>
                                </label>
                                <input type="tel" class="form-control rounded-3" id="customerPhone" name="customer_phone" 
                                       placeholder="0912345678" required>
                                <div class="invalid-feedback">Vui lòng nhập số điện thoại</div>
                            </div>
                            
                            <!-- Từ khóa tìm kiếm -->
                            <div class="col-md-6">
                                <label for="searchKeyword" class="form-label fw-semibold text-secondary small">
                                    Tên mẫu gấu cần đặt <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control rounded-3" id="searchKeyword" name="search_keyword" 
                                       required placeholder="Ví dụ: Gấu Brown, Capybara, Gấu dâu...">
                            </div>
                            
                            <!-- Mô tả chi tiết -->
                            <div class="col-12">
                                <label for="description" class="form-label fw-semibold text-secondary small">
                                    Mô tả chi tiết yêu cầu <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control rounded-3" id="description" name="description" rows="3" 
                                          placeholder="Màu sắc, kích thước (cm), phụ kiện đi kèm (nơ, áo, thêu tên)..." required></textarea>
                                <div class="invalid-feedback">Vui lòng mô tả chi tiết yêu cầu</div>
                            </div>
                            
                            <!-- Upload ảnh -->
                            <div class="col-12">
                                <label for="customImage" class="form-label fw-semibold text-secondary small">
                                    Hình ảnh mẫu tham khảo (nếu có)
                                </label>
                                <input type="file" class="form-control rounded-3" id="customImage" name="image" accept="image/*">
                                <small class="text-muted">Hỗ trợ JPG, PNG, GIF tối đa 5MB</small>
                                
                                <div id="imagePreview" class="mt-2" style="display: none;">
                                    <img id="previewImage" src="" alt="Preview" class="rounded-3 border" style="max-height: 180px;">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Thông báo lỗi hoặc kết quả -->
                        <div id="formMessage" class="alert mt-3" role="alert" style="display: none;">
                            <span id="formMessageText"></span>
                        </div>
                        
                        <!-- Nút thao tác -->
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">
                                Hủy bỏ
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4" id="submitBtn">
                                <span id="submitBtnText"><i class="fas fa-paper-plane me-1"></i> Gửi yêu cầu</span>
                                <span id="submitBtnSpinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display: none;"></span>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- BƯỚC 2: CHỌN HÌNH ẢNH GỢI Ý TỪ AI/KHO ẢNH -->
                <div id="step2-images" style="display: none;">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="fas fa-images text-pink me-2"></i> Chọn mẫu gợi ý phù hợp nhất với bạn:
                    </h6>
                    
                    <div id="imagesContainer" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;"></div>
                    
                    <form id="confirmOrderForm" novalidate>
                        <input type="hidden" id="customOrderIdConfirm" name="custom_order_id">
                        <input type="hidden" id="selectedImageUrl" name="selected_image_url">
                        
                        <!-- Đánh giá mức độ hài lòng -->
                        <div class="mb-3 p-3 bg-light rounded-3 text-center">
                            <label class="fw-semibold text-secondary small d-block mb-1">
                                Đánh giá độ chính xác của mẫu gợi ý:
                            </label>
                            <div id="ratingStars" class="fs-3 text-warning cursor-pointer">
                                <span class="star" data-rating="1">★</span>
                                <span class="star" data-rating="2">★</span>
                                <span class="star" data-rating="3">★</span>
                                <span class="star" data-rating="4">★</span>
                                <span class="star" data-rating="5">★</span>
                            </div>
                            <input type="hidden" id="ratingValue" name="rating" value="5">
                            <small class="text-muted" id="ratingText">Rất hài lòng</small>
                        </div>
                        
                        <div id="confirmMessage" class="alert mt-3" role="alert" style="display: none;">
                            <span id="confirmMessageText"></span>
                        </div>
                        
                        <div class="d-flex justify-content-between gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="backBtn">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4" id="confirmBtn">
                                <span id="confirmBtnText"><i class="fas fa-check-circle me-1"></i> Hoàn tất gửi yêu cầu</span>
                                <span id="confirmBtnSpinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display: none;"></span>
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
    
    // Đọc từ khóa tìm kiếm nếu có
    const urlParams = new URLSearchParams(window.location.search);
    const keyword = urlParams.get('q');
    if (keyword) {
        document.getElementById('searchKeyword').value = keyword;
    }
    
    // Preview ảnh
    if (imageInput) {
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
    }
    
    // BƯỚC 1: SUBMIT YÊU CẦU
    if (customOrderForm) {
        customOrderForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!customOrderForm.checkValidity()) {
                formMessage.style.display = 'block';
                formMessage.className = 'alert alert-danger';
                formMessageText.textContent = 'Vui lòng điền đầy đủ các thông tin bắt buộc!';
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
                
                const responseText = await response.text();
                const data = JSON.parse(responseText);
                
                if (data.success) {
                    customOrderIdConfirm.value = data.custom_order_id;
                    displayImages(data.images || []);
                    showStep2();
                } else {
                    formMessage.style.display = 'block';
                    formMessage.className = 'alert alert-danger';
                    formMessageText.textContent = data.message || 'Có lỗi xảy ra, vui lòng thử lại!';
                }
            } catch (error) {
                formMessage.style.display = 'block';
                formMessage.className = 'alert alert-danger';
                formMessageText.textContent = 'Lỗi hệ thống: ' + error.message;
            } finally {
                submitBtn.disabled = false;
                document.getElementById('submitBtnSpinner').style.display = 'none';
            }
        });
    }
    
    // BƯỚC 2: HIỂN THỊ DANH SÁCH ẢNH
    function displayImages(images) {
        imagesContainer.innerHTML = '';
        if (!images || images.length === 0) {
            imagesContainer.innerHTML = '<div class="text-center text-muted p-4" style="grid-column: 1/-1;">Không tìm thấy ảnh mẫu tự động. Đội ngũ thiết kế sẽ liên hệ trực tiếp với bạn!</div>';
            selectedImageUrl.value = 'default_custom';
            return;
        }
        
        images.forEach((img, index) => {
            const imageCard = document.createElement('div');
            imageCard.className = 'border rounded-3 overflow-hidden cursor-pointer position-relative';
            imageCard.innerHTML = `
                <img src="${img.thumb}" style="width: 100%; height: 160px; object-fit: cover;" alt="Gợi ý ${index + 1}">
                <div class="p-2 bg-light text-center small fw-semibold">
                    <input type="radio" name="selected_image_radio" value="${img.full}" class="me-1"> Chọn mẫu này
                </div>
            `;
            
            imageCard.addEventListener('click', function() {
                document.querySelectorAll('#imagesContainer > div').forEach(c => c.classList.remove('border-pink'));
                imageCard.classList.add('border-pink');
                const radio = imageCard.querySelector('input[type="radio"]');
                radio.checked = true;
                selectedImageUrl.value = img.full;
            });
            
            imagesContainer.appendChild(imageCard);
        });
    }
    
    // Đánh giá sao
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingValue.value = rating;
            ratingStars.forEach((s, idx) => {
                s.style.opacity = (idx + 1) <= rating ? '1' : '0.3';
            });
            const ratings = ['', 'Chưa ưng ý', 'Tạm ổn', 'Hài lòng', 'Rất hài lòng', 'Tuyệt vời!'];
            ratingText.textContent = ratings[rating];
        });
    });
    
    // BƯỚC 2: XÁC NHẬN YÊU CẦU
    if (confirmOrderForm) {
        confirmOrderForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            confirmBtn.disabled = true;
            document.getElementById('confirmBtnSpinner').style.display = 'inline-block';
            
            const formData = new FormData();
            formData.append('custom_order_id', customOrderIdConfirm.value);
            formData.append('selected_image_url', selectedImageUrl.value || 'default');
            formData.append('rating', ratingValue.value || 5);
            
            try {
                const response = await fetch('<?php echo SITE_URL; ?>/pages/api-confirm-custom-order.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    confirmMessage.style.display = 'block';
                    confirmMessage.className = 'alert alert-success';
                    confirmMessageText.textContent = 'Yêu cầu của bạn đã được tiếp nhận thành công! Shop sẽ liên hệ bạn sớm nhất.';
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('customOrderModal'));
                        if (modal) modal.hide();
                    }, 2500);
                } else {
                    confirmMessage.style.display = 'block';
                    confirmMessage.className = 'alert alert-danger';
                    confirmMessageText.textContent = data.message || 'Có lỗi xảy ra!';
                }
            } catch (err) {
                confirmMessage.style.display = 'block';
                confirmMessage.className = 'alert alert-danger';
                confirmMessageText.textContent = 'Lỗi kết nối máy chủ!';
            } finally {
                confirmBtn.disabled = false;
                document.getElementById('confirmBtnSpinner').style.display = 'none';
            }
        });
    }
    
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            showStep1();
        });
    }
    
    function showStep1() {
        document.getElementById('step1-form').style.display = 'block';
        document.getElementById('step2-images').style.display = 'none';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-palette me-2"></i> Đặt hàng theo mẫu riêng - Mô tả yêu cầu';
    }
    
    function showStep2() {
        document.getElementById('step1-form').style.display = 'none';
        document.getElementById('step2-images').style.display = 'block';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-images me-2"></i> Đặt hàng theo mẫu riêng - Chọn mẫu phù hợp';
    }
});
</script>
