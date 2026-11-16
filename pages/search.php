<?php
/**
 * Search Page - Tìm kiếm sản phẩm 🔍
 * FIXED: Chỉ HTML/PHP, không trộn API
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../includes/functions.php';

$page_title = 'Tìm kiếm sản phẩm - ' . SITE_NAME;
$page_description = 'Tìm kiếm sản phẩm gấu bông tại Teddy Bear Paradise';

// FIXED: Validate và sanitize input
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

// FIXED: Kiểm tra database connection
if ($keyword !== '') {
    try {
        if (!isset($db) || !$db) {
            throw new Exception('Database connection failed');
        }

        // FIXED: Prepared statement để tránh SQL injection
        $where_conditions = ["products.status = 'active'"];
        $params = [];
        
        $where_conditions[] = "(products.name LIKE ? OR products.description LIKE ?)";
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
        
        // Filter category - dùng category_id trực tiếp
        if ($category_filter) {
            $category = $db->fetch("SELECT id FROM categories WHERE slug = ? AND status = 'active'", [$category_filter]);
            if ($category) {
                $where_conditions[] = "products.category_id = ?";
                $params[] = $category['id'];
            }
        }
        
        // Sorting
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

// Get categories for filter
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

<div class="container my-4">
    <div class="row">
        <div class="col-md-3">
            <!-- SIDEBAR FILTERS -->
            <div class="card">
                <div class="card-header">
                    <h5 style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;"><span style="font-size: 1.1rem; margin-right: 8px;">🔍</span> Bộ lọc</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="search.php" id="filterForm">
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($keyword); ?>">
                        
                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important; color: #e91e63; font-weight: 600;">Danh mục</label>
                            <select name="category" class="form-select" onchange="this.form.submit();" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['slug']); ?>" 
                                        <?php echo ($category_filter !== '' && $category_filter === $cat['slug']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Sort -->
                        <div class="mb-3">
                            <label class="form-label" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important; color: #e91e63; font-weight: 600;">Sắp xếp</label>
                            <select name="sort" class="form-select" onchange="TeddySearch.updateSort(this.value)" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                                <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">
                            <span style="font-size: 1rem; margin-right: 5px;">🔍</span> Áp dụng
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- SEARCH FORM -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="search.php" class="search-form">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control form-control-lg" 
                                   placeholder="Tìm kiếm sản phẩm..." 
                                   value="<?php echo htmlspecialchars($keyword); ?>"
                                   autocomplete="off"
                                   style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">
                            <button class="btn btn-primary btn-lg" type="submit" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">
                                <span style="font-size: 1rem; margin-right: 5px;">🔍</span> Tìm kiếm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- SEARCH RESULTS -->
            <?php if ($keyword !== ''): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">Kết quả cho: "<strong><?php echo htmlspecialchars($keyword); ?></strong>"</h4>
                <?php if (!$no_results && !empty($products)): ?>
                <span class="text-muted" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">Tìm thấy <?php echo $total_count; ?> sản phẩm</span>
                <?php endif; ?>
            </div>
            
            <?php if ($error_message): ?>
            <div class="alert alert-danger" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">
                <span style="font-size: 1.2rem; margin-right: 8px;">⚠️</span> <?php echo $error_message; ?>
            </div>
            <?php elseif (empty($products) || $no_results): ?>
            <div class="alert alert-warning text-center" id="noResultsMessage" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">
                <span style="font-size: 3rem; color: #ff6b96;">🔍</span>
                <h5 style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important; color: #e91e63; margin-top: 15px;">
                    Sản phẩm chưa có trong shop
                </h5>
                <p style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important; font-size: 1.1rem; margin: 15px 0;">
                    Mời bạn tìm mẫu tương tự hoặc liên hệ shop để đặt hàng theo ý!
                </p>
                
                <!-- AI Suggestions sẽ được load bằng JavaScript -->
                <div id="aiSuggestionsContainer" style="margin-top: 20px;">
                    <h6 style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important; color: #666; font-weight: 600; margin-bottom: 15px;">
                        🤖 Gợi ý từ khóa tìm kiếm:
                    </h6>
                    <div id="aiSuggestionsTags" class="suggestion-tags" style="justify-content: center;">
                        <span class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </span>
                        <span style="margin-left: 10px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">
                            Đang tải gợi ý từ AI...
                        </span>
                    </div>
                </div>
                
                <div style="margin-top: 25px;">
                    <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important; margin-right: 10px;">
                        <span style="font-size: 1rem; margin-right: 5px;">📦</span> Xem tất cả sản phẩm
                    </a>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#customOrderModal" 
                            style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important; margin-right: 10px;">
                        <span style="font-size: 1rem; margin-right: 5px;">🎨</span> Đặt mẫu riêng
                    </button>
                    <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="btn btn-outline-primary" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;">
                        <span style="font-size: 1rem; margin-right: 5px;">📞</span> Liên hệ đặt hàng
                    </a>
                </div>
            </div>
            
            <script>
            // Load AI suggestions when no results
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
                                const tag = document.createElement('span');
                                tag.className = 'suggestion-tag';
                                tag.textContent = suggestion;
                                tag.style.cursor = 'pointer';
                                tag.onclick = function() {
                                    window.location.href = 'search.php?q=' + encodeURIComponent(suggestion);
                                };
                                container.appendChild(tag);
                            });
                        } else {
                            container.innerHTML = '<span style="font-family: \'UVN Van\', \'Nunito\', \'Poppins\', Arial, sans-serif !important; color: #999;">Không có gợi ý từ AI</span>';
                        }
                    })
                    .catch(err => {
                        const container = document.getElementById('aiSuggestionsTags');
                        container.innerHTML = '<span style="font-family: \'UVN Van\', \'Nunito\', \'Poppins\', Arial, sans-serif !important; color: #999;">Không thể tải gợi ý</span>';
                    });
                }
            })();
            </script>
            <?php else: ?>
            
            <!-- PRODUCTS GRID -->
            <div class="row">
                <?php foreach ($products as $product): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card product-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo SITE_URL; ?>/images/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="height: 250px; object-fit: cover;">
                            
                            <?php if ($product['sale_price'] > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">Sale</span>
                            <?php endif; ?>
                            
                            <?php if ($product['is_featured']): ?>
                            <span class="badge bg-warning position-absolute top-0 start-0 m-2">Hot</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <p class="card-text text-muted small flex-grow-1">
                                <?php echo htmlspecialchars(substr($product['short_description'] ?? '', 0, 80)); ?>...
                            </p>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <span style="font-size: 0.9rem; margin-right: 5px;">🏷️</span> <?php echo htmlspecialchars($product['category_name'] ?? 'Không có'); ?>
                                </small>
                            </div>
                            
                            <div class="price-section mb-3">
                                <?php if ($product['sale_price'] > 0): ?>
                                    <span class="h5 text-danger"><?php echo number_format($product['sale_price']); ?>đ</span>
                                    <small class="text-decoration-line-through text-muted ms-1">
                                        <?php echo number_format($product['price']); ?>đ
                                    </small>
                                <?php else: ?>
                                    <span class="h5 text-primary"><?php echo number_format($product['price']); ?>đ</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $product['slug']; ?>" 
                                   class="btn btn-outline-primary btn-sm flex-grow-1">
                                    <span style="font-size: 1rem; margin-right: 5px;">👁️</span> Xem
                                </a>
                                <button type="button"
                                        data-product-id="<?php echo $product['id']; ?>"
                                        class="btn btn-primary btn-sm flex-grow-1 add-to-cart-btn">
                                    <span style="font-size: 1rem; margin-right: 5px;">➕</span> Mua
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

<!-- Include Modal Đặt Hàng Theo Mẫu -->
<?php include 'custom-order-modal.php'; ?>

<script src="../js/cart.js"></script>
<?php include '../includes/footer.php'; ?>