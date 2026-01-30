<?php
/**
 * ShopNest - Delete Product Page
 * Delete products from the store
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

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    $delete_query = "DELETE FROM products WHERE id = $product_id";
    
    if (mysqli_query($dbconnection, $delete_query)) {
        $_SESSION['delete_success'] = "Product deleted successfully!";
        header("Location: products.php");
        exit();
    } else {
        $error = "Error deleting product: " . mysqli_error($dbconnection);
    }
}

// Set page title
$pageTitle = "Delete Product";

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">
                    <i class="bi bi-trash"></i> Delete Product
                </h1>
                <p class="mb-0">Remove product from store</p>
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

<!-- Delete Confirmation -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Confirm Deletion</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This action cannot be undone. The product will be permanently deleted from the store.
                        </div>
                        
                        <div class="mb-4">
                            <h5>Product Details:</h5>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <?php 
                                    // Handle image path
                                    $delete_image = $product['image'];
                                    if (!empty($delete_image) && strpos($delete_image, 'http') !== 0 && strpos($delete_image, '/') !== 0) {
                                        $delete_image = '../assets/images/' . $delete_image;
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($delete_image); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="img-fluid rounded">
                                </div>
                                <div class="col-md-8">
                                    <h6><?php echo htmlspecialchars($product['name']); ?></h6>
                                    <p class="text-muted mb-1">Category: <?php echo htmlspecialchars($product['category']); ?></p>
                                    <p class="text-muted mb-1">Price: ৳<?php echo number_format($product['price'], 2); ?></p>
                                    <p class="text-muted mb-0">Stock: <?php echo $product['stock']; ?> units</p>
                                </div>
                            </div>
                        </div>
                        
                        <form action="delete_product.php?id=<?php echo $product_id; ?>" method="POST">
                            <div class="d-grid gap-2">
                                <button type="submit" name="confirm_delete" class="btn btn-danger btn-lg">
                                    <i class="bi bi-trash"></i> Yes, Delete Product
                                </button>
                                <a href="products.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
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

