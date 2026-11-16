<?php
/**
 * Search Page - Tìm kiếm sản phẩm
 * Bố cục sạch sẽ, tối ưu responsive, loại bỏ icon rác
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../includes/functions.php';

$page_title = 'Tìm kiếm sản phẩm - ' . SITE_NAME;
$page_description = 'Tìm kiếm các sản phẩm gấu bông đáng yêu tại Teddy Bear Paradise';

// Validate và sanitize input
$keyword = '';
if (isset($_GET['q'])) {
    $keyword = htmlspecialchars(trim($_GET['q']), ENT_QUOTES, 'UTF-8');
    $keyword = strip_tags($keyword);
}

$no_results = isset($_GET['no_results']) && $_GET['no_results'] === '1';

$category_filter = isset($_GET['category']) ? htmlspecialchars(trim($_GET['category']), ENT_QUOTES, 'UTF-8') : '';
$sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort'], ENT_QUOTES, 'UTF-8') : 'newest';

$products = [];
$total_count = 0;
$error_message = '';

// Truy vấn sản phẩm theo từ khóa
if ($keyword !== '') {
    try {
        if (!isset($db) || !$db) {
            throw new Exception('Database connection failed');
        }

        $where_conditions = ["products.status = 'active'"];
        $params = [];
        
        $where_conditions[] = "(products.name LIKE ? OR products.description LIKE ?)";
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
        
        // Lọc theo danh mục
        if ($category_filter) {
            $category = $db->fetch("SELECT id FROM categories WHERE slug = ? AND status = 'active'", [$category_filter]);
            if ($category) {
                $where_conditions[] = "products.category_id = ?";
                $params[] = $category['id'];
            }
        }
        
        // Sắp xếp
        $order_clause = '';
        switch ($sort) {
            case 'price_asc':
                $order_clause = 'ORDER BY COALESCE(products.sale_price, products.price) ASC';
                break;
            case 'price_desc':
                $order_clause = 'ORDER BY COALESCE(products.sale_price, products.price) DESC';
                break;
            case 'name':
                $order_clause = 'ORDER BY products.name ASC';
                break;
            default:
                $order_clause = 'ORDER BY products.created_at DESC';
        }
        
        $sql = "SELECT products.*, categories.name as category_name 
                FROM products 
                LEFT JOIN categories ON products.category_id = categories.id 
                WHERE " . implode(' AND ', $where_conditions) . " 
                $order_clause 
                LIMIT 50";
        
        $products = $db->fetchAll($sql, $params);
        $total_count = count($products);
        
    } catch (Exception $e) {
        error_log('Search error: ' . $e->getMessage());
    }
}

// Lấy danh sách danh mục phục vụ bộ lọc
$categories = [];
try {
    if (isset($db) && $db) {
        $categories = $db->fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    }
} catch (Exception $e) {
    error_log('Categories fetch error: ' . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Header Tìm kiếm -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="search.php" class="search-form">
                <div class="input-group input-group-lg">
                    <input type="text" name="q" class="form-control rounded-start-pill ps-4" 
                           placeholder="Nhập tên gấu bông hoặc loại bạn muốn tìm..." 
                           value="<?php echo htmlspecialchars($keyword); ?>"
                           autocomplete="off">
                    <button class="btn btn-primary rounded-end-pill px-4" type="submit">
                        <i class="fas fa-search me-2"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Bộ Lọc -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-filter text-pink me-2"></i> Bộ lọc tìm kiếm
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="search.php" id="filterForm">
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($keyword); ?>">
                        
                        <!-- Lọc danh mục -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">Danh mục sản phẩm</label>
                            <select name="category" class="form-select rounded-3" onchange="this.form.submit();">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['slug']); ?>" 
                                        <?php echo ($category_filter !== '' && $category_filter === $cat['slug']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Sắp xếp -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Sắp xếp theo</label>
                            <select name="sort" class="form-select rounded-3" onchange="this.form.submit();">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                                <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-outline-primary w-100 rounded-pill">
                            Áp dụng bộ lọc
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Kết Quả Tìm Kiếm -->
        <div class="col-lg-9">
            <?php if ($keyword !== ''): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0">
                    Kết quả cho: "<span class="text-pink"><?php echo htmlspecialchars($keyword); ?></span>"
                </h5>
                <?php if (!$no_results && !empty($products)): ?>
                <span class="text-muted small">Tìm thấy <strong><?php echo $total_count; ?></strong> sản phẩm</span>
                <?php endif; ?>
            </div>
            
            <?php if ($error_message): ?>
            <div class="alert alert-danger rounded-4 p-4 text-center">
                <i class="fas fa-exclamation-triangle fs-3 text-danger mb-2"></i>
                <div><?php echo $error_message; ?></div>
            </div>
            <?php elseif (empty($products) || $no_results): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center" id="noResultsMessage">
                <div class="rounded-circle bg-pink-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-search text-pink fs-2"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Không tìm thấy sản phẩm phù hợp</h4>
                <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
                    Rất tiếc sản phẩm bạn đang tìm hiện chưa có sẵn. Bạn có thể xem các mẫu tương tự khác hoặc gửi yêu cầu thiết kế theo ý muốn!
                </p>
                
                <!-- Gợi ý từ AI -->
                <div id="aiSuggestionsContainer" class="mb-4">
                    <p class="text-secondary small fw-semibold mb-2">Gợi ý từ khóa tìm kiếm phổ biến:</p>
                    <div id="aiSuggestionsTags" class="d-flex flex-wrap justify-content-center gap-2">
                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                    </div>
                </div>
                
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary px-4 py-2 rounded-pill">
                        <i class="fas fa-th-large me-2"></i> Xem tất cả sản phẩm
                    </a>
                    <button type="button" class="btn btn-outline-primary px-4 py-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#customOrderModal">
                        <i class="fas fa-palette me-2"></i> Đặt mẫu riêng
                    </button>
                    <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="btn btn-light px-4 py-2 rounded-pill border">
                        <i class="fas fa-phone-alt me-2"></i> Liên hệ tư vấn
                    </a>
                </div>
            </div>
            
            <script>
            // Tải gợi ý từ khóa thông minh
            (function() {
                const keyword = '<?php echo addslashes($keyword); ?>';
                if (keyword) {
                    fetch('<?php echo SITE_URL; ?>/pages/api-ai-search.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'q=' + encodeURIComponent(keyword)
                    })
                    .then(res => res.json())
                    .then(data => {
                        const container = document.getElementById('aiSuggestionsTags');
                        if (data.ai_suggestions && data.ai_suggestions.length > 0) {
                            container.innerHTML = '';
                            data.ai_suggestions.forEach(suggestion => {
                                const tag = document.createElement('a');
                                tag.className = 'badge bg-white text-secondary border px-3 py-2 text-decoration-none rounded-pill shadow-xs hover-pink';
                                tag.textContent = suggestion;
                                tag.href = 'search.php?q=' + encodeURIComponent(suggestion);
                                container.appendChild(tag);
                            });
                        } else {
                            container.innerHTML = '<span class="text-muted small">Không có gợi ý thêm</span>';
                        }
                    })
                    .catch(() => {
                        const container = document.getElementById('aiSuggestionsTags');
                        container.innerHTML = '<span class="text-muted small">Chưa có gợi ý</span>';
                    });
                }
            })();
            </script>
            <?php else: ?>
            
            <!-- Danh Sách Sản Phẩm -->
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card product-card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="position-relative overflow-hidden" style="height: 240px; background: #fff5f7;">
                            <img src="<?php echo SITE_URL; ?>/images/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                 class="card-img-top w-100 h-100 object-fit-cover" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='<?php echo SITE_URL; ?>/images/placeholder.jpg'">
                            
                            <?php if ($product['sale_price'] > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 end-0 m-3 px-2 py-1 rounded-pill">
                                -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                            </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['is_featured'])): ?>
                            <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3 px-2 py-1 rounded-pill">
                                Nổi bật
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body p-3 d-flex flex-column">
                            <span class="text-muted small mb-1">
                                <?php echo htmlspecialchars($product['category_name'] ?? 'Gấu bông'); ?>
                            </span>
                            <h6 class="card-title fw-bold text-dark mb-2 text-truncate" title="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h6>
                            
                            <div class="price-section mt-auto mb-3">
                                <?php if ($product['sale_price'] > 0): ?>
                                    <span class="fs-5 fw-bold text-danger"><?php echo number_format($product['sale_price']); ?>đ</span>
                                    <span class="text-decoration-line-through text-muted small ms-2">
                                        <?php echo number_format($product['price']); ?>đ
                                    </span>
                                <?php else: ?>
                                    <span class="fs-5 fw-bold text-primary"><?php echo number_format($product['price']); ?>đ</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $product['slug']; ?>" 
                                   class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill">
                                    Chi tiết
                                </a>
                                <button type="button"
                                        data-product-id="<?php echo $product['id']; ?>"
                                        class="btn btn-primary btn-sm flex-grow-1 rounded-pill add-to-cart-btn">
                                    <i class="fas fa-shopping-cart me-1"></i> Thêm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Đặt Hàng Theo Mẫu -->
<?php include 'custom-order-modal.php'; ?>

<script src="../js/cart.js"></script>
<?php include '../includes/footer.php'; ?>
