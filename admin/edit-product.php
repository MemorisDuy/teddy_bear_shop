<?php
/**
 * Edit Product - Admin Panel
 */

require_once '../config/config.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$product_id = intval($_GET['id'] ?? 0);
if ($product_id <= 0) {
    header('Location: manage-products.php');
    exit();
}

// Get product data
try {
    $product = $db->fetch("SELECT * FROM products WHERE id = ?", [$product_id]);
    if (!$product) {
        setFlashMessage('error', 'Sản phẩm không tồn tại!');
        header('Location: manage-products.php');
        exit();
    }
} catch (Exception $e) {
    setFlashMessage('error', 'Lỗi tải dữ liệu: ' . $e->getMessage());
    header('Location: manage-products.php');
    exit();
}

// Handle product deletion
if (isset($_GET['delete']) && intval($_GET['delete']) === $product_id) {
    try {
        $deleted = $db->execute("DELETE FROM products WHERE id = ?", [$product_id]);
        if (!$deleted) {
            // Nếu sản phẩm đã có trong order_items (ràng buộc khóa ngoại), chuyển sang ngừng bán
            $db->execute("UPDATE products SET status = 'inactive' WHERE id = ?", [$product_id]);
            setFlashMessage('success', 'Sản phẩm đã phát sinh đơn hàng nên được chuyển sang trạng thái Ngừng bán!');
        } else {
            setFlashMessage('success', 'Đã xóa sản phẩm thành công!');
        }
    } catch (Exception $e) {
        $db->execute("UPDATE products SET status = 'inactive' WHERE id = ?", [$product_id]);
        setFlashMessage('success', 'Đã chuyển sản phẩm sang trạng thái Ngừng bán!');
    }
    header('Location: manage-products.php');
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
    $status = sanitizeInput($_POST['status'] ?? 'active');
    
    // Generate slug if name changed
    $slug = (sanitizeInput($_POST['name']) !== $product['name']) ? generateSlug($name) : $product['slug'];
    
    // Validate required fields
    $errors = [];
    if (empty($name)) $errors[] = 'Tên sản phẩm không được để trống';
    if (empty($description)) $errors[] = 'Mô tả không được để trống';
    if ($price <= 0) $errors[] = 'Giá phải lớn hơn 0';
    if ($stock_quantity < 0) $errors[] = 'Số lượng kho không được âm';
    if ($category_id <= 0) $errors[] = 'Vui lòng chọn danh mục';
    
    // Handle image upload
    $main_image = $product['main_image']; // Keep current image by default
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
                // Delete old image if it's not default
                if ($product['main_image'] !== 'default.jpg' && file_exists($uploadDir . $product['main_image'])) {
                    unlink($uploadDir . $product['main_image']);
                }
                $main_image = $fileName;
            } else {
                $errors[] = 'Không thể upload hình ảnh';
            }
        } else {
            $errors[] = 'Chỉ chấp nhận file hình ảnh (JPG, PNG, GIF)';
        }
    }
    
    // If no errors, update product
    if (empty($errors)) {
        try {
            $result = $db->execute(
                "UPDATE products SET 
                 name = ?, slug = ?, description = ?, short_description = ?, price = ?, sale_price = ?, 
                 stock_quantity = ?, category_id = ?, main_image = ?, size = ?, color = ?, material = ?, 
                 weight = ?, is_featured = ?, is_limited = ?, meta_title = ?, meta_description = ?, 
                 status = ?, updated_at = NOW()
                 WHERE id = ?",
                [
                    $name, $slug, $description, $short_description, $price, $sale_price,
                    $stock_quantity, $category_id, $main_image, $size, $color, $material, $weight,
                    $is_featured, $is_limited, $meta_title, $meta_description, $status, $product_id
                ]
            );
            
            if ($result) {
                setFlashMessage('success', 'Đã cập nhật sản phẩm thành công!');
                header('Location: manage-products.php');
                exit();
            } else {
                $errors[] = 'Có lỗi xảy ra khi cập nhật sản phẩm';
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

$page_title = "Chỉnh sửa sản phẩm - Admin";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-management.css">
</head>
<body>
    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0">
                        <i class="fas fa-edit"></i> Chỉnh sửa sản phẩm
                    </h1>
                    <p class="mb-0 opacity-75">ID: #<?php echo $product['id']; ?> - <?php echo htmlspecialchars($product['name']); ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="manage-products.php" class="btn btn-light">
                        <span>⬅️</span> Quay lại
                    </a>
                    <a href="../pages/product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" 
                       class="btn btn-outline-light" target="_blank">
                        <i class="fas fa-eye"></i> Xem sản phẩm
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
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
                                       value="<?php echo htmlspecialchars($product['name']); ?>"
                                       placeholder="Nhập tên sản phẩm...">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Danh mục <span class="required">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Chọn danh mục...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?php echo ($product['status'] == 'active') ? 'selected' : ''; ?>>
                                        Đang bán
                                    </option>
                                    <option value="inactive" <?php echo ($product['status'] == 'inactive') ? 'selected' : ''; ?>>
                                        Ngừng bán
                                    </option>
                                    <option value="out_of_stock" <?php echo ($product['status'] == 'out_of_stock') ? 'selected' : ''; ?>>
                                        Hết hàng
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giá bán <span class="required">*</span></label>
                                <input type="number" name="price" class="form-control" required min="0" step="1000"
                                       value="<?php echo $product['price']; ?>"
                                       placeholder="0">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giá khuyến mãi</label>
                                <input type="number" name="sale_price" class="form-control" min="0" step="1000"
                                       value="<?php echo $product['sale_price'] ?? ''; ?>"
                                       placeholder="0">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Số lượng kho <span class="required">*</span></label>
                                <input type="number" name="stock_quantity" class="form-control" required min="0"
                                       value="<?php echo $product['stock_quantity']; ?>"
                                       placeholder="0">
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Mô tả ngắn</label>
                                <textarea name="short_description" class="form-control" rows="3"
                                          placeholder="Mô tả ngắn về sản phẩm..."><?php echo htmlspecialchars($product['short_description']); ?></textarea>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Mô tả chi tiết <span class="required">*</span></label>
                                <textarea name="description" class="form-control" rows="6" required
                                          placeholder="Mô tả chi tiết về sản phẩm..."><?php echo htmlspecialchars($product['description']); ?></textarea>
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
                                    <option value="S" <?php echo ($product['size'] == 'S') ? 'selected' : ''; ?>>S - Nhỏ</option>
                                    <option value="M" <?php echo ($product['size'] == 'M') ? 'selected' : ''; ?>>M - Vừa</option>
                                    <option value="L" <?php echo ($product['size'] == 'L') ? 'selected' : ''; ?>>L - Lớn</option>
                                    <option value="XL" <?php echo ($product['size'] == 'XL') ? 'selected' : ''; ?>>XL - Rất lớn</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Màu sắc</label>
                                <input type="text" name="color" class="form-control"
                                       value="<?php echo htmlspecialchars($product['color']); ?>"
                                       placeholder="Ví dụ: Nâu, Hồng, Trắng...">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Trọng lượng (kg)</label>
                                <input type="number" name="weight" class="form-control" min="0" step="0.1"
                                       value="<?php echo $product['weight'] ?? ''; ?>"
                                       placeholder="0.0">
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Chất liệu</label>
                                <input type="text" name="material" class="form-control"
                                       value="<?php echo htmlspecialchars($product['material']); ?>"
                                       placeholder="Ví dụ: Cotton, Polyester...">
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
                                       value="<?php echo htmlspecialchars($product['meta_title']); ?>"
                                       placeholder="Tiêu đề cho SEO...">
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Mô tả SEO</label>
                                <textarea name="meta_description" class="form-control" rows="3"
                                          placeholder="Mô tả cho SEO..."><?php echo htmlspecialchars($product['meta_description']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Current Image -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-image"></i> Hình ảnh sản phẩm
                        </h4>
                        
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh hiện tại</label>
                            <div class="current-image">
                                <img src="<?php echo IMG_PATH . htmlspecialchars($product['main_image']); ?>" 
                                     alt="Current Image" 
                                     class="img-fluid rounded"
                                     style="width: 100%; max-width: 200px; height: auto;"
                                     onerror="this.src='<?php echo IMG_PATH; ?>placeholder.jpg'">
                                <div class="change-image-overlay">
                                    <div class="text-center">
                                        <span style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"><i class="fas fa-camera"></i></span>
                                        <p class="mb-0">Click để thay đổi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Thay đổi hình ảnh</label>
                            <input type="file" name="main_image" class="form-control" accept="image/*">
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
                                       <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">
                                    Sản phẩm nổi bật
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_limited" class="form-check-input" id="is_limited"
                                       <?php echo $product['is_limited'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_limited">
                                    Phiên bản giới hạn
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Product Stats -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-chart-bar"></i> Thống kê
                        </h4>
                        
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <h5 class="text-primary"><?php echo number_format($product['views']); ?></h5>
                                    <small class="text-muted">Lượt xem</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <h5 class="text-warning"><?php echo $product['rating'] ?? 0; ?>/5</h5>
                                    <small class="text-muted">Đánh giá</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="small text-muted">
                            <p><strong>Tạo:</strong> <?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></p>
                            <p><strong>Cập nhật:</strong> <?php echo date('d/m/Y H:i', strtotime($product['updated_at'])); ?></p>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-section">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                            <a href="manage-products.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy bỏ
                            </a>
                            <hr>
                            <a href="?id=<?php echo $product['id']; ?>&delete=<?php echo $product['id']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                <span>🗑️</span> Xóa sản phẩm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>