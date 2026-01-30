<?php
/**
 * ShopEase - Homepage
 * This is the main landing page that shows categories and featured products
 */

// Start session (needed for cart and user authentication)
session_start();

// Set page title
$pageTitle = "Home";

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1>Welcome to ShopNest</h1>
                <p>Discover amazing products at unbeatable prices. Shop with confidence!</p>
                <a href="products.php" class="btn btn-light btn-lg">
                    <i class="bi bi-bag"></i> Shop Now
                </a>
            </div>
            <div class="col-lg-6 text-center">
                <i class="bi bi-shop" style="font-size: 15rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <h2>Shop by Category</h2>
            <p>Browse our wide range of product categories</p>
        </div>
        
        <div class="row g-4">
            <!-- Electronics Category -->
            <div class="col-md-3 col-sm-6">
                <a href="products.php?category=electronics" class="category-card card shadow-sm">
                    <i class="bi bi-laptop"></i>
                    <h5>Electronics</h5>
                    <p class="text-muted mb-0">Latest gadgets & devices</p>
                </a>
            </div>
            
            <!-- Clothing Category -->
            <div class="col-md-3 col-sm-6">
                <a href="products.php?category=clothing" class="category-card card shadow-sm">
                    <i class="bi bi-person-workspace"></i>
                    <h5>Clothing</h5>
                    <p class="text-muted mb-0">Fashion for everyone</p>
                </a>
            </div>
            
            <!-- Home & Kitchen Category -->
            <div class="col-md-3 col-sm-6">
                <a href="products.php?category=home" class="category-card card shadow-sm">
                    <i class="bi bi-house-door"></i>
                    <h5>Home & Kitchen</h5>
                    <p class="text-muted mb-0">Everything for your home</p>
                </a>
            </div>
            
            <!-- Sports Category -->
            <div class="col-md-3 col-sm-6">
                <a href="products.php?category=sports" class="category-card card shadow-sm">
                    <i class="bi bi-trophy"></i>
                    <h5>Sports</h5>
                    <p class="text-muted mb-0">Fitness & outdoor gear</p>
                </a>
            </div>
            
            <!-- Books Category -->
            <div class="col-md-3 col-sm-6">
                <a href="products.php?category=books" class="category-card card shadow-sm">
                    <i class="bi bi-book"></i>
                    <h5>Books</h5>
                    <p class="text-muted mb-0">Knowledge & entertainment</p>
                </a>
            </div>
            
            <!-- Beauty Category -->
            <div class="col-md-3 col-sm-6">
                <a href="products.php?category=beauty" class="category-card card shadow-sm">
                    <i class="bi bi-heart"></i>
                    <h5>Beauty</h5>
                    <p class="text-muted mb-0">Cosmetics & skincare</p>
                </a>
            </div>
            
            <!-- Toys Category -->
            <div class="col-md-3 col-sm-6">
                <a href="products.php?category=toys" class="category-card card shadow-sm">
                    <i class="bi bi-emoji-smile"></i>
                    <h5>Toys</h5>
                    <p class="text-muted mb-0">Fun for kids of all ages</p>
                </a>
            </div>
            
            <!-- Accessories Category -->
            <div class="col-md-3 col-sm-6">
                <a href="products.php?category=accessories" class="category-card card shadow-sm">
                    <i class="bi bi-watch"></i>
                    <h5>Accessories</h5>
                    <p class="text-muted mb-0">Complete your look</p>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-header">
            <h2>Featured Products</h2>
            <p>Check out our most popular items</p>
        </div>
        
        <?php
        // Fetch featured products from database
        require_once 'includes/DbConnection.php';
        $featured_query = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
        $featured_result = mysqli_query($dbconnection, $featured_query);
        ?>
        
        <div class="row g-4">
            <?php if($featured_result && mysqli_num_rows($featured_result) > 0): ?>
                <?php while($product = mysqli_fetch_assoc($featured_result)): 
                    // Handle image path
                    $image_path = $product['image'];
                    if (!empty($image_path) && strpos($image_path, 'http') !== 0 && strpos($image_path, '/') !== 0) {
                        $image_path = 'assets/images/' . $image_path;
                    }
                ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card product-card shadow-sm">
                            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="height: 250px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="text-muted small mb-2"><?php echo htmlspecialchars($product['category']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="price">৳<?php echo number_format($product['price'], 2); ?></span>
                                        <?php if(isset($product['old_price']) && $product['old_price'] > 0): ?>
                                            <span class="old-price ms-2">৳<?php echo number_format($product['old_price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm w-100 mb-2">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                    <form action="cart.php" method="POST" class="d-inline w-100">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                            <i class="bi bi-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-muted text-center">No products available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- View All Products Button -->
        <div class="text-center mt-5">
            <a href="products.php" class="btn btn-primary btn-lg">
                <i class="bi bi-grid"></i> View All Products
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <h2>Why Choose ShopNest?</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <i class="bi bi-truck" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h5 class="mt-3">Free Shipping</h5>
                <p class="text-muted">On orders over $50</p>
            </div>
            
            <div class="col-md-3 text-center">
                <i class="bi bi-arrow-repeat" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h5 class="mt-3">Easy Returns</h5>
                <p class="text-muted">30-day return policy</p>
            </div>
            
            <div class="col-md-3 text-center">
                <i class="bi bi-shield-check" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h5 class="mt-3">Secure Payment</h5>
                <p class="text-muted">100% secure transactions</p>
            </div>
            
            <div class="col-md-3 text-center">
                <i class="bi bi-headset" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h5 class="mt-3">24/7 Support</h5>
                <p class="text-muted">Always here to help</p>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>

