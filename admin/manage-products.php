<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

// Get products with stats
try {
    $products = $db->fetchAll(
        "SELECT p.*, c.name as category_name 
         FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         ORDER BY p.created_at DESC 
         LIMIT 20"
    );
    
    $stats = [
        'total_products' => $db->fetch("SELECT COUNT(*) as count FROM products")['count'] ?? 0,
        'active_products' => $db->fetch("SELECT COUNT(*) as count FROM products WHERE status = 'active'")['count'] ?? 0,
        'featured_products' => $db->fetch("SELECT COUNT(*) as count FROM products WHERE is_featured = 1")['count'] ?? 0,
        'out_of_stock' => $db->fetch("SELECT COUNT(*) as count FROM products WHERE stock_quantity = 0")['count'] ?? 0
    ];
} catch (Exception $e) {
    $products = [];
    $stats = ['total_products' => 0, 'active_products' => 0, 'featured_products' => 0, 'out_of_stock' => 0];
}

include 'partials/menu.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Quicksand', 'Nunito', sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
        .main-content { margin-left: 260px; padding: 30px; }
        .page-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; padding: 25px; border-radius: 15px; margin-bottom: 25px; 
        }
        .page-title { font-size: 28px; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 12px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { 
            background: white; padding: 25px; border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-number { font-size: 32px; font-weight: bold; color: #667eea; margin-bottom: 8px; }
        .stat-label { color: #666; font-weight: 500; }
        
        .content-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 20px; font-weight: 600; color: #333; margin: 0; }
        
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; padding: 20px; }
        .product-card { 
            background: white; border-radius: 12px; overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s;
        }
        .product-card:hover { transform: translateY(-5px); }
        .product-image { 
            width: 100%; height: 200px; background: #f8f9fa; 
            display: flex; align-items: center; justify-content: center; position: relative;
        }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-placeholder { font-size: 48px; color: #ccc; }
        
        .product-info { padding: 15px; }
        .product-name { font-size: 16px; font-weight: 600; color: #333; margin-bottom: 8px; }
        .product-category { font-size: 12px; color: #666; margin-bottom: 10px; }
        .product-price { font-size: 18px; font-weight: 700; color: #28a745; margin-bottom: 10px; }
        .product-stock { font-size: 12px; margin-bottom: 10px; }
        .stock-in { color: #28a745; }
        .stock-out { color: #dc3545; }
        
        .btn { 
            padding: 8px 12px; border: none; border-radius: 6px; 
            cursor: pointer; font-size: 12px; font-weight: 500; 
            text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
            transition: all 0.3s; margin: 2px;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn:hover { transform: translateY(-1px); opacity: 0.9; }
        
        @media (max-width: 768px) { 
            .main-content { margin-left: 0; padding: 15px; }
            .products-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-box"></i>
                Quản lý sản phẩm
            </h1>
        </div>

        <?php if ($flash_success = getFlashMessage('success')): ?>
            <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flash_success); ?>
            </div>
        <?php endif; ?>
        <?php if ($flash_error = getFlashMessage('error')): ?>
            <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                <i class="fas fa-times"></i> <?php echo htmlspecialchars($flash_error); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_products']); ?></div>
                <div class="stat-label">Tổng sản phẩm</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['active_products']); ?></div>
                <div class="stat-label">Đang bán</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['featured_products']); ?></div>
                <div class="stat-label">Sản phẩm nổi bật</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['out_of_stock']); ?></div>
                <div class="stat-label">Hết hàng</div>
            </div>
        </div>

        <!-- Products -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Danh sách sản phẩm (<?php echo count($products); ?>)</h3>
                <a href="add-product.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </a>
            </div>
            
            <div class="products-grid">
                <?php if (empty($products)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666;">
                    <span style="font-size: 48px; display: block; margin-bottom: 15px;"><i class="fas fa-box"></i></span>
                    Chưa có sản phẩm nào. Hãy thêm sản phẩm đầu tiên!
                </div>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['main_image'] && file_exists("../images/{$product['main_image']}")): ?>
                            <img src="../images/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder">
                                <i class="fas fa-heart"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-category">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name'] ?? 'Chưa phân loại'); ?>
                        </div>
                        
                        <div class="product-price">
                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                <?php echo formatPrice($product['sale_price']); ?>
                                <small style="text-decoration: line-through; color: #999; margin-left: 8px;">
                                    <?php echo formatPrice($product['price']); ?>
                                </small>
                            <?php else: ?>
                                <?php echo formatPrice($product['price']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-stock <?php echo $product['stock_quantity'] > 0 ? 'stock-in' : 'stock-out'; ?>">
                            <i class="fas fa-store"></i> 
                            <?php echo $product['stock_quantity']; ?> trong kho
                        </div>
                        
                        <div style="margin-top: 15px; display: flex; gap: 8px;">
                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <a href="../pages/product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" target="_blank" class="btn btn-success">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>