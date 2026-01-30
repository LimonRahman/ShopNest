<?php
/**
 * ShopNest - Products Listing Page
 * Displays all products with filtering and search functionality
 */

// Start session
session_start();

// Include database connection
require_once 'includes/DbConnection.php';

// Check if database connection is successful
if (!$dbconnection) {
    die("Database connection failed. Please check your database settings in includes/DbConnection.php");
}

// Set page title
$pageTitle = "Products";

// Include header
include 'includes/header.php';

// Get search and category parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : 'latest';

// Escape search and category for database query
if (!empty($search)) {
    $search = mysqli_real_escape_string($dbconnection, $search);
}
if (!empty($category)) {
    $category = mysqli_real_escape_string($dbconnection, $category);
}

// Build query
$where_conditions = [];
if (!empty($search)) {
    $where_conditions[] = "(name LIKE '%$search%' OR description LIKE '%$search%')";
}
if (!empty($category)) {
    $where_conditions[] = "category = '$category'";
}
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Build order by clause
$order_by = "ORDER BY created_at DESC";
switch($sort) {
    case 'price-low':
        $order_by = "ORDER BY price ASC";
        break;
    case 'price-high':
        $order_by = "ORDER BY price DESC";
        break;
    case 'name':
        $order_by = "ORDER BY name ASC";
        break;
    case 'latest':
    default:
        $order_by = "ORDER BY created_at DESC";
        break;
}

// Check if products table exists, if not show helpful message
$table_check = mysqli_query($dbconnection, "SHOW TABLES LIKE 'products'");
if (mysqli_num_rows($table_check) == 0) {
    // Table doesn't exist - show setup message
    $products = [];
    $table_error = true;
} else {
    $query = "SELECT * FROM products $where_clause $order_by";
    $result = mysqli_query($dbconnection, $query);
    $products = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    } else {
        // Query error - show error message
        $query_error = mysqli_error($dbconnection);
        $products = [];
    }
}
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <h1 class="mb-0">
            <i class="bi bi-grid"></i> All Products
        </h1>
        <p class="mb-0">Discover our complete product catalog</p>
    </div>
</section>

<!-- Filters and Search Section -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row">
            <!-- Search Bar -->
            <div class="col-md-6 mb-3">
                <form action="products.php" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search products..." 
                               value="<?php echo $search; ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Category Filter -->
            <div class="col-md-3 mb-3">
                <form action="products.php" method="GET">
                    <select class="form-select" name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="electronics" <?php echo $category == 'electronics' ? 'selected' : ''; ?>>Electronics</option>
                        <option value="clothing" <?php echo $category == 'clothing' ? 'selected' : ''; ?>>Clothing</option>
                        <option value="home" <?php echo $category == 'home' ? 'selected' : ''; ?>>Home & Kitchen</option>
                        <option value="sports" <?php echo $category == 'sports' ? 'selected' : ''; ?>>Sports</option>
                        <option value="books" <?php echo $category == 'books' ? 'selected' : ''; ?>>Books</option>
                        <option value="beauty" <?php echo $category == 'beauty' ? 'selected' : ''; ?>>Beauty</option>
                        <option value="toys" <?php echo $category == 'toys' ? 'selected' : ''; ?>>Toys</option>
                        <option value="accessories" <?php echo $category == 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                    </select>
                </form>
            </div>
            
            <!-- Sort Options -->
            <div class="col-md-3 mb-3">
                <form action="products.php" method="GET">
                    <?php if($category): ?>
                        <input type="hidden" name="category" value="<?php echo $category; ?>">
                    <?php endif; ?>
                    <?php if($search): ?>
                        <input type="hidden" name="search" value="<?php echo $search; ?>">
                    <?php endif; ?>
                    <select class="form-select" name="sort" onchange="this.form.submit()">
                        <option value="latest" <?php echo $sort == 'latest' ? 'selected' : ''; ?>>Latest</option>
                        <option value="price-low" <?php echo $sort == 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price-high" <?php echo $sort == 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                    </select>
                </form>
            </div>
        </div>
        
        <!-- Active Filters Display -->
        <?php if($search || $category): ?>
            <div class="row">
                <div class="col-12">
                    <p class="mb-0">
                        <strong>Active Filters:</strong>
                        <?php if($search): ?>
                            <span class="badge bg-primary me-2">Search: <?php echo $search; ?></span>
                        <?php endif; ?>
                        <?php if($category): ?>
                            <span class="badge bg-primary me-2">Category: <?php echo ucfirst($category); ?></span>
                        <?php endif; ?>
                        <a href="products.php" class="text-danger text-decoration-none">
                            <i class="bi bi-x-circle"></i> Clear All
                        </a>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Products Grid -->
<section class="py-5">
    <div class="container">
        <?php if(isset($table_error)): ?>
            <div class="alert alert-warning">
                <h5><i class="bi bi-exclamation-triangle"></i> Database Setup Required</h5>
                <p>The products table doesn't exist yet. Please import the database schema:</p>
                <ol>
                    <li>Open phpMyAdmin</li>
                    <li>Select the <strong>shopNest</strong> database</li>
                    <li>Go to the <strong>Import</strong> tab</li>
                    <li>Select the file <code>database_schema.sql</code></li>
                    <li>Click <strong>Go</strong> to import</li>
                </ol>
            </div>
        <?php elseif(isset($query_error)): ?>
            <div class="alert alert-danger">
                <h5><i class="bi bi-exclamation-circle"></i> Database Error</h5>
                <p>Error: <?php echo htmlspecialchars($query_error); ?></p>
                <p>Please check your database connection and ensure the products table exists.</p>
            </div>
        <?php else: ?>
            <!-- Results Count -->
            <div class="row mb-4">
                <div class="col-12">
                    <p class="text-muted">
                        Showing <strong><?php echo count($products); ?></strong> product(s)
                        <?php if($category): ?>
                            in <strong><?php echo ucfirst($category); ?></strong>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="row g-4">
                <?php if(empty($products)): ?>
                    <div class="col-12">
                        <div class="empty-state text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 5rem; color: #ccc;"></i>
                            <h3>No products found</h3>
                            <p>Try adjusting your search or filter criteria.</p>
                            <a href="products.php" class="btn btn-primary">View All Products</a>
                        </div>
                    </div>
            <?php else: ?>
                <?php foreach($products as $product): 
                    // Handle image path - use assets/images/ if it's a local file, otherwise use the stored path
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
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>

