<?php
/**
 * Add Product - Admin Panel
 */

require_once '../config/config.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $short_description = sanitizeInput($_POST['short_description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $size = sanitizeInput($_POST['size'] ?? '');
    $color = sanitizeInput($_POST['color'] ?? '');
    $material = sanitizeInput($_POST['material'] ?? '');
    $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : null;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_limited = isset($_POST['is_limited']) ? 1 : 0;
    $meta_title = sanitizeInput($_POST['meta_title'] ?? '');
    $meta_description = sanitizeInput($_POST['meta_description'] ?? '');
    
    // Generate slug
    $slug = generateSlug($name);
    
    // Validate required fieldsb
    $errors = [];
    if (empty($name)) $errors[] = 'Tên sản phẩm không được để trống';
    if (empty($description)) $errors[] = 'Mô tả không được để trống';
    if ($price <= 0) $errors[] = 'Giá phải lớn hơn 0';
    if ($stock_quantity < 0) $errors[] = 'Số lượng kho không được âm';
    if ($category_id <= 0) $errors[] = 'Vui lòng chọn danh mục';
    
    // Handle image upload
    $main_image = 'default.jpg';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['main_image']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($extension, $allowedTypes)) {
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $uploadPath)) {
                $main_image = $fileName;
            } else {
                $errors[] = 'Không thể upload hình ảnh';
            }
        } else {
            $errors[] = 'Chỉ chấp nhận file hình ảnh (JPG, PNG, GIF)';
        }
    }
    
    // If no errors, insert product
    if (empty($errors)) {
        try {
            $result = $db->execute(
                "INSERT INTO products (name, slug, description, short_description, price, sale_price, 
                 stock_quantity, category_id, main_image, size, color, material, weight, 
                 is_featured, is_limited, meta_title, meta_description) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $name, $slug, $description, $short_description, $price, $sale_price,
                    $stock_quantity, $category_id, $main_image, $size, $color, $material, $weight,
                    $is_featured, $is_limited, $meta_title, $meta_description
                ]
            );
            
            if ($result) {
                setFlashMessage('success', 'Đã thêm sản phẩm thành công!');
                header('Location: manage-products.php');
                exit();
            } else {
                $errors[] = 'Có lỗi xảy ra khi thêm sản phẩm';
            }
        } catch (Exception $e) {
            $errors[] = 'Lỗi database: ' . $e->getMessage();
        }
    }
}

// Get categories for dropdown
try {
    $categories = $db->fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
} catch (Exception $e) {
    $categories = [];
}

$page_title = "Thêm sản phẩm - Admin";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-unified.css">
</head>
<body>
    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0">
                        <i class="fas fa-plus"></i> Thêm sản phẩm mới
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="manage-products.php" class="btn btn-light">
                        <span>⬅️</span> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 mb-4">
                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra:</h5>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Basic Info -->
                            <div class="col-lg-8">
                                <div class="form-section">
                                    <h4 class="section-title">
                                        <span>ℹ️</span> Thông tin cơ bản
                                    </h4>
                                    
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Tên sản phẩm <span class="required">*</span></label>
                                            <input type="text" name="name" class="form-control" required 
                                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                                   placeholder="Nhập tên sản phẩm...">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Danh mục <span class="required">*</span></label>
                                            <select name="category_id" class="form-select" required>
                                                <option value="">Chọn danh mục...</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>" 
                                                            <?php echo (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Giá bán <span class="required">*</span></label>
                                            <input type="number" name="price" class="form-control" required min="0" step="1000"
                                                   value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                                                   placeholder="0">
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Giá khuyến mãi</label>
                                            <input type="number" name="sale_price" class="form-control" min="0" step="1000"
                                                   value="<?php echo htmlspecialchars($_POST['sale_price'] ?? ''); ?>"
                                                   placeholder="0">
                                        </div>
                                        
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Mô tả ngắn</label>
                                            <textarea name="short_description" class="form-control" rows="3"
                                                      placeholder="Mô tả ngắn về sản phẩm..."><?php echo htmlspecialchars($_POST['short_description'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Mô tả chi tiết <span class="required">*</span></label>
                                            <textarea name="description" class="form-control" rows="6" required
                                                      placeholder="Mô tả chi tiết về sản phẩm..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Product Details -->
                                <div class="form-section">
                                    <h4 class="section-title">
                                        <i class="fas fa-cog"></i> Chi tiết sản phẩm
                                    </h4>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Kích thước</label>
                                            <select name="size" class="form-select">
                                                <option value="">Chọn size...</option>
                                                <option value="S" <?php echo (($_POST['size'] ?? '') == 'S') ? 'selected' : ''; ?>>S - Nhỏ</option>
                                                <option value="M" <?php echo (($_POST['size'] ?? '') == 'M') ? 'selected' : ''; ?>>M - Vừa</option>
                                                <option value="L" <?php echo (($_POST['size'] ?? '') == 'L') ? 'selected' : ''; ?>>L - Lớn</option>
                                                <option value="XL" <?php echo (($_POST['size'] ?? '') == 'XL') ? 'selected' : ''; ?>>XL - Rất lớn</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Màu sắc</label>
                                            <input type="text" name="color" class="form-control"
                                                   value="<?php echo htmlspecialchars($_POST['color'] ?? ''); ?>"
                                                   placeholder="Ví dụ: Nâu, Hồng, Trắng...">
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Số lượng kho <span class="required">*</span></label>
                                            <input type="number" name="stock_quantity" class="form-control" required min="0"
                                                   value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? ''); ?>"
                                                   placeholder="0">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Chất liệu</label>
                                            <input type="text" name="material" class="form-control"
                                                   value="<?php echo htmlspecialchars($_POST['material'] ?? ''); ?>"
                                                   placeholder="Ví dụ: Cotton, Polyester...">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Trọng lượng (kg)</label>
                                            <input type="number" name="weight" class="form-control" min="0" step="0.1"
                                                   value="<?php echo htmlspecialchars($_POST['weight'] ?? ''); ?>"
                                                   placeholder="0.0">
                                        </div>
                                    </div>
                                </div>

                                <!-- SEO -->
                                <div class="form-section">
                                    <h4 class="section-title">
                                        <i class="fas fa-search"></i> SEO
                                    </h4>
                                    
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Tiêu đề SEO</label>
                                            <input type="text" name="meta_title" class="form-control"
                                                   value="<?php echo htmlspecialchars($_POST['meta_title'] ?? ''); ?>"
                                                   placeholder="Tiêu đề cho SEO...">
                                        </div>
                                        
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Mô tả SEO</label>
                                            <textarea name="meta_description" class="form-control" rows="3"
                                                      placeholder="Mô tả cho SEO..."><?php echo htmlspecialchars($_POST['meta_description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-lg-4">
                                <!-- Image Upload -->
                                <div class="form-section">
                                    <h4 class="section-title">
                                        <i class="fas fa-image"></i> Hình ảnh sản phẩm
                                    </h4>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Hình ảnh chính</label>
                                        <div class="image-preview" onclick="document.getElementById('main_image').click()">
                                            <div class="text-center text-muted">
                                                <span style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"><i class="fas fa-camera"></i></span>
                                                <p>Click để chọn hình ảnh</p>
                                            </div>
                                        </div>
                                        <input type="file" id="main_image" name="main_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                        <small class="text-muted">Chấp nhận JPG, PNG, GIF. Kích thước tối đa 5MB.</small>
                                    </div>
                                </div>

                                <!-- Product Options -->
                                <div class="form-section">
                                    <h4 class="section-title">
                                        <span>⭐</span> Tùy chọn
                                    </h4>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured"
                                                   <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_featured">
                                                Sản phẩm nổi bật
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="is_limited" class="form-check-input" id="is_limited"
                                                   <?php echo isset($_POST['is_limited']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_limited">
                                                Phiên bản giới hạn
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="form-section">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save"></i> Thêm sản phẩm
                                        </button>
                                        <a href="manage-products.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Hủy bỏ
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.image-preview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Auto generate slug from name
        document.querySelector('input[name="name"]').addEventListener('input', function() {
            // You can add slug generation logic here if needed
        });
    </script>
</body>
</html>