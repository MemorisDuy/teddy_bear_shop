<?php
/**
 * Danh sách sản phẩm - Teddy Bear Shop
 * Bố cục sạch sẽ, chuẩn Responsive, không emoji rác
 */
require_once "../config/config.php";
$page_title = "Sản phẩm - " . SITE_NAME;
$page_description = "Khám phá bộ sưu tập gấu bông cao cấp, đa dạng mẫu mã và an toàn tại Teddy Bear Paradise.";

try {
    $category_slug = isset($_GET['category']) ? trim($_GET['category']) : '';
    $category_id = null;
    $category = null;
    
    $products_per_page = 12;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $products_per_page;

    // Lấy danh sách danh mục
    $categories = $db->fetchAll("SELECT id, name, slug FROM categories WHERE status = 'active' ORDER BY name");
    
    // Nếu có lọc danh mục
    if ($category_slug) {
        foreach ($categories as $cat) {
            if (trim($cat['slug']) === trim($category_slug) || (is_numeric($category_slug) && intval($cat['id']) === intval($category_slug))) {
                $category_id = intval($cat['id']);
                $category = $cat;
                $category_slug = trim($cat['slug']);
                break;
            }
        }
    }
    
    // Lấy sản phẩm
    if ($category_id !== null) {
        $count_row = $db->fetch("SELECT COUNT(*) as total FROM products WHERE status = 'active' AND category_id = ?", [$category_id]);
        $total_products = $count_row ? intval($count_row['total']) : 0;
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.category_id = ? 
                ORDER BY p.created_at DESC LIMIT " . intval($products_per_page) . " OFFSET " . intval($offset);
        $products = $db->fetchAll($sql, [$category_id]);
    } else {
        $count_row = $db->fetch("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
        $total_products = $count_row ? intval($count_row['total']) : 0;
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' 
                ORDER BY p.created_at DESC LIMIT " . intval($products_per_page) . " OFFSET " . intval($offset);
        $products = $db->fetchAll($sql);
    }
    
    $total_pages = max(1, ceil($total_products / $products_per_page));
} catch (Exception $e) {
    $products = [];
    $categories = [];
    $total_pages = 1;
}

include "../includes/header.php";
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i> Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="products.php" class="text-decoration-none text-muted">Sản phẩm</a></li>
            <?php if ($category): ?>
            <li class="breadcrumb-item active text-pink fw-semibold" aria-current="page"><?php echo htmlspecialchars($category['name']); ?></li>
            <?php else: ?>
            <li class="breadcrumb-item active text-pink fw-semibold" aria-current="page">Tất cả sản phẩm</li>
            <?php endif; ?>
        </ol>
    </nav>

    <!-- Header Tiêu đề trang -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="fw-bold text-dark mb-1">
                    <?php echo $category ? htmlspecialchars($category['name']) : 'Tất Cả Sản Phẩm Gấu Bông'; ?>
                </h3>
                <p class="text-muted small mb-0">
                    <?php echo $category && !empty($category['description']) ? htmlspecialchars($category['description']) : 'Tuyển chọn những chú gấu bông mềm mại, an toàn và dễ thương nhất'; ?>
                </p>
            </div>
            <span class="badge bg-pink-light text-pink rounded-pill px-3 py-2 fw-semibold">
                <?php echo $total_products; ?> sản phẩm
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Danh Mục -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 20px;">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="fas fa-th-large text-pink me-2"></i> Danh mục sản phẩm
                </h6>
                <div class="d-flex flex-column gap-2">
                    <a href="products.php" 
                       class="btn text-start rounded-pill py-2 px-3 fw-semibold <?php echo empty($category_slug) ? 'btn-primary' : 'btn-light'; ?>">
                        Tất cả sản phẩm
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="products.php?category=<?php echo urlencode(trim($cat['slug'])); ?>" 
                       class="btn text-start rounded-pill py-2 px-3 fw-semibold <?php echo (trim($category_slug) === trim($cat['slug'])) ? 'btn-primary' : 'btn-light'; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Lưới Sản Phẩm -->
        <div class="col-lg-9">
            <?php if (!empty($products)): ?>
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                    <div class="col-xl-4 col-md-6 col-6">
                        <div class="card product-card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative bg-white">
                            <!-- Ảnh sản phẩm -->
                            <div class="position-relative overflow-hidden" style="height: 230px; background: #fff5f7;">
                                <a href="product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" class="d-block w-100 h-100">
                                    <img src="<?php echo IMG_PATH . htmlspecialchars($product['main_image']); ?>"
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="w-100 h-100 object-fit-cover"
                                         onerror="this.src='<?php echo IMG_PATH; ?>placeholder.jpg'">
                                </a>

                                <?php if (!empty($product['sale_price'])): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2 rounded-pill px-2 py-1">
                                    -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                                </span>
                                <?php endif; ?>
                            </div>

                            <!-- Thông tin sản phẩm -->
                            <div class="card-body p-3 d-flex flex-column">
                                <span class="text-muted small mb-1">
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Gấu bông'); ?>
                                </span>

                                <h6 class="card-title fw-bold text-dark mb-1 text-truncate" title="<?php echo htmlspecialchars($product['name']); ?>">
                                    <a href="product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" class="text-decoration-none text-dark hover-pink">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h6>

                                <!-- Giá bán -->
                                <div class="price-section mt-auto mb-3">
                                    <?php if (!empty($product['sale_price'])): ?>
                                        <span class="fw-bold text-danger fs-5"><?php echo formatPrice($product['sale_price']); ?></span>
                                        <small class="text-decoration-line-through text-muted ms-1"><?php echo formatPrice($product['price']); ?></small>
                                    <?php else: ?>
                                        <span class="fw-bold text-primary fs-5"><?php echo formatPrice($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Nút thao tác -->
                                <div class="d-flex gap-2">
                                    <a href="product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" 
                                       class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill">
                                        Chi tiết
                                    </a>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                    <button class="btn btn-primary btn-sm rounded-pill px-3 add-to-cart-btn" 
                                            data-product-id="<?php echo $product['id']; ?>"
                                            title="Thêm vào giỏ">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-light btn-sm rounded-pill px-3 text-muted" disabled title="Tạm hết hàng">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Phân trang -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-5 d-flex justify-content-center">
                    <ul class="pagination pagination-sm gap-1">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link rounded-circle text-center" style="width: 36px; height: 36px; line-height: 24px;" 
                               href="products.php?<?php echo $category_slug ? 'category=' . urlencode($category_slug) . '&' : ''; ?>page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>

            <?php else: ?>
                <!-- Không có sản phẩm -->
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <div class="rounded-circle bg-pink-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-box-open text-pink fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Chưa có sản phẩm trong danh mục này</h5>
                    <p class="text-muted mb-4">Vui lòng chọn danh mục khác hoặc quay lại xem tất cả sản phẩm.</p>
                    <div>
                        <a href="products.php" class="btn btn-primary rounded-pill px-4 py-2">
                            <i class="fas fa-arrow-left me-2"></i> Xem tất cả sản phẩm
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../js/cart.js"></script>
<?php include "../includes/footer.php"; ?>
