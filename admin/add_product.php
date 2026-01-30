<?php
/**
 * ShopNest - Add Product Page
 * Form to add new products to the store
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
$pageTitle = "Add Product";

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
        $old_price_sql = $old_price !== null ? $old_price : 'NULL';
        
        // Insert product
        $insert_query = "INSERT INTO products (name, description, category, price, old_price, stock, image, features, created_at) 
                         VALUES ('$name', '$description', '$category', $price, $old_price_sql, $stock, '$image', '$features', NOW())";
        
        if (mysqli_query($dbconnection, $insert_query)) {
            $success = "Product added successfully!";
            // Clear form data
            $_POST = [];
        } else {
            $error = "Error adding product: " . mysqli_error($dbconnection);
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
                    <i class="bi bi-plus-circle"></i> Add New Product
                </h1>
                <p class="mb-0">Add a new product to the store</p>
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

<!-- Add Product Form -->
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
                        
                        <form action="add_product.php" method="POST">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                           required>
                                </div>
                                <div class="col-md-4">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="electronics" <?php echo (isset($_POST['category']) && $_POST['category'] == 'electronics') ? 'selected' : ''; ?>>Electronics</option>
                                        <option value="clothing" <?php echo (isset($_POST['category']) && $_POST['category'] == 'clothing') ? 'selected' : ''; ?>>Clothing</option>
                                        <option value="home" <?php echo (isset($_POST['category']) && $_POST['category'] == 'home') ? 'selected' : ''; ?>>Home & Kitchen</option>
                                        <option value="sports" <?php echo (isset($_POST['category']) && $_POST['category'] == 'sports') ? 'selected' : ''; ?>>Sports</option>
                                        <option value="books" <?php echo (isset($_POST['category']) && $_POST['category'] == 'books') ? 'selected' : ''; ?>>Books</option>
                                        <option value="beauty" <?php echo (isset($_POST['category']) && $_POST['category'] == 'beauty') ? 'selected' : ''; ?>>Beauty</option>
                                        <option value="toys" <?php echo (isset($_POST['category']) && $_POST['category'] == 'toys') ? 'selected' : ''; ?>>Toys</option>
                                        <option value="accessories" <?php echo (isset($_POST['category']) && $_POST['category'] == 'accessories') ? 'selected' : ''; ?>>Accessories</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                           value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" 
                                           min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="old_price" class="form-label">Old Price ($) <span class="text-muted">(Optional)</span></label>
                                    <input type="number" step="0.01" class="form-control" id="old_price" name="old_price" 
                                           value="<?php echo isset($_POST['old_price']) ? htmlspecialchars($_POST['old_price']) : ''; ?>" 
                                           min="0">
                                </div>
                                <div class="col-md-4">
                                    <label for="stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="stock" name="stock" 
                                           value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : '0'; ?>" 
                                           min="0" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Image Filename <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="image" name="image" 
                                       value="<?php echo isset($_POST['image']) ? htmlspecialchars($_POST['image']) : ''; ?>" 
                                       placeholder="product1.jpg" required>
                                <small class="text-muted">Enter the image filename (e.g., product1.jpg). Image should be placed in assets/images/ folder.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="features" class="form-label">Features <span class="text-muted">(Optional)</span></label>
                                <textarea class="form-control" id="features" name="features" rows="3" 
                                          placeholder="Enter one feature per line"><?php echo isset($_POST['features']) ? htmlspecialchars($_POST['features']) : ''; ?></textarea>
                                <small class="text-muted">Enter one feature per line</small>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Add Product
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

