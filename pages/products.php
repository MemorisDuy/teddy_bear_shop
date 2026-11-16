<?php
require_once "../config/config.php";
$page_title = "Sản phẩm - Teddy Bear Shop";

try {
    // Get category filter if exists
    $category_slug = isset($_GET['category']) ? trim($_GET['category']) : '';
    $category_id = null;
    $category = null;
    
    // PHÂN TRANG ĐỘNG - Teddy Bear Shop
    $products_per_page = 14;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $products_per_page;

    // Lấy danh mục
    $categories = $db->fetchAll("SELECT id, name, slug FROM categories WHERE status = 'active' ORDER BY name");
    
    // Nếu có filter category
    if ($category_slug) {
        // Tìm category_id từ slug hoặc id
        foreach ($categories as $cat) {
            if (trim($cat['slug']) === trim($category_slug) || (is_numeric($category_slug) && intval($cat['id']) === intval($category_slug))) {
                $category_id = intval($cat['id']);
                $category = $cat;
                $category_slug = trim($cat['slug']);
                break;
            }
        }
    }
    
    // Query sản phẩm
    if ($category_id !== null) {
        // Lọc theo danh mục
        $count_row = $db->fetch(
            "SELECT COUNT(*) as total FROM products WHERE status = 'active' AND category_id = ?", 
            [$category_id]
        );
        $total_products = $count_row ? intval($count_row['total']) : 0;
        
        if ($total_products > 0) {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.status = 'active' AND p.category_id = ? 
                    ORDER BY p.created_at DESC LIMIT " . intval($products_per_page) . " OFFSET " . intval($offset);
            $products = $db->fetchAll($sql, [$category_id]);
        } else {
            $products = [];
        }
    } else {
        // Hiển thị tất cả sản phẩm
        $count_row = $db->fetch("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
        $total_products = $count_row ? intval($count_row['total']) : 0;
        
        if ($total_products > 0) {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.status = 'active' 
                    ORDER BY p.created_at DESC LIMIT " . intval($products_per_page) . " OFFSET " . intval($offset);
            $products = $db->fetchAll($sql);
        } else {
            $products = [];
        }
    }
    
    $total_pages = max(1, ceil($total_products / $products_per_page));
} catch (Exception $e) {
    $products = [];
    $categories = [];
    if (APP_DEBUG) {
        error_log("Products page error: " . $e->getMessage());
    }
}

include "../includes/header.php";
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-custom">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="../index.php">🏠 Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="products.php">🧸 Sản phẩm</a></li>
            <?php if ($category): ?>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($category['name']); ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1 class="mb-0">
            🧸 <?php echo $category ? htmlspecialchars($category['name']) : 'Tất cả sản phẩm'; ?>
        </h1>
        <?php if ($category && !empty($category['description'])): ?>
        <p class="mt-2 mb-0"><?php echo htmlspecialchars($category['description']); ?></p>
        <?php endif; ?>
    </div>

    <div class="row">
        <!-- Sidebar Categories -->
        <div class="col-lg-3 mb-4">
            <div class="category-sidebar">
                <h5 class="mb-3 fw-bold">Danh mục sản phẩm</h5>
                <div class="category-list">
                    <a href="products.php" 
                       class="category-item <?php echo empty($category_slug) ? 'active' : ''; ?>">
                        Tất cả sản phẩm
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="products.php?category=<?php echo urlencode(trim($cat['slug'])); ?>" 
                       class="category-item <?php echo (trim($category_slug) === trim($cat['slug'])) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="row">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="product-card">
                            <!-- Product Image -->
                            <div class="product-image">
                                <a href="product-detail.php?slug=<?php echo urlencode($product['slug']); ?>">
                                    <img src="<?php echo IMG_PATH . htmlspecialchars($product['main_image']); ?>"
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         onerror="this.src='<?php echo IMG_PATH; ?>placeholder.jpg'">
                                </a>

                                <!-- Hover Overlay -->
                                <div class="product-overlay">
                                    <button class="btn btn-light btn-sm add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>"
                                            title="Thêm vào giỏ hàng">
                                        <span style="font-size: 1.1rem;">➕</span>
                                    </button>
                                    <a href="product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" 
                                       class="btn btn-primary btn-sm"
                                       title="Xem chi tiết">
                                        <span style="font-size: 1.1rem;">👁️</span>
                                    </a>
                                    <button class="btn btn-light btn-sm" 
                                            onclick="addToWishlist(<?php echo $product['id']; ?>)"
                                            title="Thêm vào yêu thích">
                                        <span style="font-size: 1.1rem;">💖</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="product-info">
                                <!-- Category -->
                                <?php if (!empty($product['category_name'])): ?>
                                <div class="product-category mb-2">
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($product['category_name']); ?>
                                    </small>
                                </div>
                                <?php endif; ?>

                                <!-- Product Title -->
                                <a href="product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" 
                                   class="product-title">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>

                                <!-- Product Description -->
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($product['short_description'] ?? '', 0, 100)); ?>
                                    <?php if (strlen($product['short_description'] ?? '') > 100): ?>...<?php endif; ?>
                                </p>

                                <!-- Rating -->
                                <?php if (!empty($product['rating']) && $product['rating'] > 0): ?>
                                <div class="product-rating">
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span style="font-size: 0.9rem; color: <?php echo $i <= $product['rating'] ? '#ffd700' : '#ddd'; ?>;">⭐</span>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted">
                                        (<?php echo $product['review_count'] ?? 0; ?> đánh giá)
                                    </small>
                                </div>
                                <?php endif; ?>

                                <!-- Price -->
                                <div class="product-price">
                                    <?php if (!empty($product['sale_price'])): ?>
                                        <span class="sale-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                        <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php else: ?>
                                        <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Stock Status -->
                                <div class="stock-status mb-3">
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <small class="text-success fw-bold">
                                            Còn hàng (<?php echo $product['stock_quantity']; ?>)
                                        </small>
                                    <?php else: ?>
                                        <small class="text-danger fw-bold">
                                            Hết hàng
                                        </small>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons -->
                                <div class="product-actions-bottom">
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                    <button class="btn btn-primary w-100 mb-2 add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                        <span style="font-size: 1.1rem;">➕</span>
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-outline-secondary w-100 mb-2" disabled>
                                        <span style="font-size: 1rem; margin-right: 5px;">❌</span> Hết hàng
                                    </button>
                                    <?php endif; ?>
                                    
                                    <a href="product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" 
                                       class="btn btn-outline-primary w-100">
                                        <span style="font-size: 1rem; margin-right: 5px;">ℹ️</span> Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <span style="font-size: 1rem;">🔍</span>
                            <h4>🤔 Không tìm thấy sản phẩm nào</h4>
                            <p class="text-muted">
                                <?php if ($category): ?>
                                    Danh mục "<?php echo htmlspecialchars($category['name']); ?>" chưa có sản phẩm nào.
                                <?php else: ?>
                                    Hiện tại chưa có sản phẩm nào trong cửa hàng.
                                <?php endif; ?>
                            </p>
                            <a href="products.php" class="btn btn-primary">
                                <span style="font-size: 1rem; margin-right: 5px;">⬅️</span> Xem tất cả sản phẩm
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination (dynamic) -->
            <?php if ($total_pages > 1): ?>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <nav aria-label="Product pagination">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $category_slug ? '&category='.urlencode($category_slug) : ''; ?>">Trước</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_slug ? '&category='.urlencode($category_slug) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $category_slug ? '&category='.urlencode($category_slug) : ''; ?>">Sau</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../js/cart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🛒 Initializing cart on products page...');
    if (typeof TeddyBearCart !== 'undefined') {
        window.teddyCart = new TeddyBearCart();
        console.log('✅ Cart initialized successfully');
    } else {
        console.error('❌ TeddyBearCart class not found');
    }
});
</script>

<?php include "../includes/footer.php"; ?>