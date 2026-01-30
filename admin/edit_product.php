<?php
/**
 * ShopNest - Edit Product Page
 * Update existing products
 */

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
require_once '../includes/DbConnection.php';

// Set page title
$pageTitle = "Edit Product";

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: products.php");
    exit();
}

// Fetch product details
$query = "SELECT * FROM products WHERE id = $product_id";
$result = mysqli_query($dbconnection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: products.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $old_price = isset($_POST['old_price']) && !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $image = isset($_POST['image']) ? trim($_POST['image']) : '';
    $features = isset($_POST['features']) ? trim($_POST['features']) : '';
    
    // Validation
    if (empty($name) || empty($description) || empty($category) || $price <= 0 || $stock < 0) {
        $error = "Please fill in all required fields correctly.";
    } else {
        // Escape inputs
        $name = mysqli_real_escape_string($dbconnection, $name);
        $description = mysqli_real_escape_string($dbconnection, $description);
        $category = mysqli_real_escape_string($dbconnection, $category);
        $image = mysqli_real_escape_string($dbconnection, $image);
        $features = mysqli_real_escape_string($dbconnection, $features);
        
        // Prepare old_price for query
        if ($old_price !== null) {
            $old_price_sql = $old_price;
            $update_query = "UPDATE products SET 
                            name = '$name', 
                            description = '$description', 
                            category = '$category', 
                            price = $price, 
                            old_price = $old_price_sql, 
                            stock = $stock, 
                            image = '$image', 
                            features = '$features',
                            updated_at = NOW()
                            WHERE id = $product_id";
        } else {
            $update_query = "UPDATE products SET 
                            name = '$name', 
                            description = '$description', 
                            category = '$category', 
                            price = $price, 
                            old_price = NULL, 
                            stock = $stock, 
                            image = '$image', 
                            features = '$features',
                            updated_at = NOW()
                            WHERE id = $product_id";
        }
        
        if (mysqli_query($dbconnection, $update_query)) {
            $success = "Product updated successfully!";
            // Refresh product data
            $query = "SELECT * FROM products WHERE id = $product_id";
            $result = mysqli_query($dbconnection, $query);
            $product = mysqli_fetch_assoc($result);
        } else {
            $error = "Error updating product: " . mysqli_error($dbconnection);
        }
    }
}

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Edit Product
                </h1>
                <p class="mb-0">Update product information</p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="products.php" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Edit Product Form -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <?php if($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="electronics" <?php echo $product['category'] == 'electronics' ? 'selected' : ''; ?>>Electronics</option>
                                        <option value="clothing" <?php echo $product['category'] == 'clothing' ? 'selected' : ''; ?>>Clothing</option>
                                        <option value="home" <?php echo $product['category'] == 'home' ? 'selected' : ''; ?>>Home & Kitchen</option>
                                        <option value="sports" <?php echo $product['category'] == 'sports' ? 'selected' : ''; ?>>Sports</option>
                                        <option value="books" <?php echo $product['category'] == 'books' ? 'selected' : ''; ?>>Books</option>
                                        <option value="beauty" <?php echo $product['category'] == 'beauty' ? 'selected' : ''; ?>>Beauty</option>
                                        <option value="toys" <?php echo $product['category'] == 'toys' ? 'selected' : ''; ?>>Toys</option>
                                        <option value="accessories" <?php echo $product['category'] == 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                           value="<?php echo $product['price']; ?>" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="old_price" class="form-label">Old Price ($) <span class="text-muted">(Optional)</span></label>
                                    <input type="number" step="0.01" class="form-control" id="old_price" name="old_price" 
                                           value="<?php echo isset($product['old_price']) && $product['old_price'] > 0 ? $product['old_price'] : ''; ?>" 
                                           min="0">
                                </div>
                                <div class="col-md-4">
                                    <label for="stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="stock" name="stock" 
                                           value="<?php echo $product['stock']; ?>" min="0" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Image Filename <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="image" name="image" 
                                       value="<?php echo htmlspecialchars($product['image']); ?>" 
                                       placeholder="product1.jpg" required>
                                <small class="text-muted">Enter the image filename (e.g., product1.jpg). Image should be placed in assets/images/ folder.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="features" class="form-label">Features <span class="text-muted">(Optional)</span></label>
                                <textarea class="form-control" id="features" name="features" rows="3" 
                                          placeholder="Enter one feature per line"><?php echo isset($product['features']) ? htmlspecialchars($product['features']) : ''; ?></textarea>
                                <small class="text-muted">Enter one feature per line</small>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Update Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include '../includes/footer.php';
?>

