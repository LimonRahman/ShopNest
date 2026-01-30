<?php
/**
 * ShopNest - Product Details Page
 * Displays detailed information about a single product
 */

// Start session
session_start();

// Include database connection
require_once 'includes/DbConnection.php';

// Set page title
$pageTitle = "Product Details";

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Check if this page was opened from admin panel
$from_admin = isset($_GET['from']) && $_GET['from'] === 'admin';

if ($product_id <= 0) {
    header("Location: products.php");
    exit();
}

// Fetch product details from database
$query = "SELECT * FROM products WHERE id = $product_id";
$result = mysqli_query($dbconnection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: products.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

// Set page title with product name
$pageTitle = htmlspecialchars($product['name']);

// Include header
include 'includes/header.php';

// Fetch related products (same category, excluding current product)
$category_escaped = mysqli_real_escape_string($dbconnection, $product['category']);
$related_query = "SELECT * FROM products WHERE category = '$category_escaped' AND id != $product_id LIMIT 4";
$related_result = mysqli_query($dbconnection, $related_query);
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </div>
</nav>

<!-- Product Details Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Product Image -->
            <div class="col-md-6 mb-4">
                <div class="product-image-main">
                    <?php 
                    // Handle image path - use assets/images/ if it's a local file
                    $image_path = $product['image'];
                    if (!empty($image_path) && strpos($image_path, 'http') !== 0 && strpos($image_path, '/') !== 0) {
                        $image_path = 'assets/images/' . $image_path;
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($image_path); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="img-fluid rounded shadow">
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-md-6">
                <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <!-- Category Badge -->
                <p class="mb-3">
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($product['category']); ?></span>
                    <?php if($product['stock'] > 0): ?>
                        <span class="badge bg-success">In Stock</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Out of Stock</span>
                    <?php endif; ?>
                </p>
                
                <!-- Price -->
                <div class="mb-4">
                    <h3 class="text-primary mb-1">
                        ৳<?php echo number_format($product['price'], 2); ?>
                    </h3>
                    <?php if(isset($product['old_price']) && $product['old_price'] > 0): ?>
                        <span class="text-muted text-decoration-line-through">
                            ৳<?php echo number_format($product['old_price'], 2); ?>
                        </span>
                        <span class="badge bg-danger ms-2">
                            <?php 
                            $discount = (($product['old_price'] - $product['price']) / $product['old_price']) * 100;
                            echo number_format($discount, 0) . '% OFF';
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Description -->
                <div class="mb-4">
                    <h5>Description</h5>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <!-- Stock Info -->
                <div class="mb-4">
                    <p class="mb-2">
                        <strong>Stock Available:</strong> 
                        <span class="text-<?php echo $product['stock'] > 0 ? 'success' : 'danger'; ?>">
                            <?php echo $product['stock']; ?> units
                        </span>
                    </p>
                </div>
                
                <!-- Add to Cart Form -->
                <?php if($product['stock'] > 0): ?>
                    <form action="cart.php" method="POST" class="mb-4">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Quantity:</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="quantity" 
                                       name="quantity" 
                                       value="1" 
                                       min="1" 
                                       max="<?php echo $product['stock']; ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                            <?php if($from_admin): ?>
                                <a href="admin/products.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-arrow-left"></i> Back to Admin Products
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary btn-lg" onclick="window.history.back()">
                                    <i class="bi bi-arrow-left"></i> Back
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> This product is currently out of stock.
                    </div>
                    <?php if($from_admin): ?>
                        <a href="admin/products.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Admin Products
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                            <i class="bi bi-arrow-left"></i> Back to Products
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Product Features -->
                <?php if(isset($product['features']) && !empty($product['features'])): ?>
                    <div class="mt-4">
                        <h5>Features</h5>
                        <ul class="list-unstyled">
                            <?php 
                            $features = explode("\n", $product['features']);
                            foreach($features as $feature):
                                if(trim($feature)):
                            ?>
                                <li><i class="bi bi-check-circle text-success"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if($related_result && mysqli_num_rows($related_result) > 0): ?>
            <hr class="my-5">
            <div class="row">
                <div class="col-12">
                    <h3 class="mb-4">Related Products</h3>
                </div>
                <?php while($related = mysqli_fetch_assoc($related_result)): 
                    // Handle image path
                    $related_image = $related['image'];
                    if (!empty($related_image) && strpos($related_image, 'http') !== 0 && strpos($related_image, '/') !== 0) {
                        $related_image = 'assets/images/' . $related_image;
                    }
                ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card product-card shadow-sm h-100">
                            <img src="<?php echo htmlspecialchars($related_image); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h5>
                                <p class="text-muted small mb-2"><?php echo htmlspecialchars($related['category']); ?></p>
                                <div class="mt-auto">
                                    <div class="mb-2">
                                        <span class="price">৳<?php echo number_format($related['price'], 2); ?></span>
                                        <?php if(isset($related['old_price']) && $related['old_price'] > 0): ?>
                                            <span class="old-price ms-2">৳<?php echo number_format($related['old_price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="product_details.php?id=<?php echo $related['id']; ?>" 
                                       class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>

