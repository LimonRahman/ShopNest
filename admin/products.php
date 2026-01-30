<?php
/**
 * ShopNest - Admin Products Management
 * List all products with edit/delete options
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
$pageTitle = "Manage Products";

// Handle delete success message
$success = '';
if (isset($_SESSION['delete_success'])) {
    $success = $_SESSION['delete_success'];
    unset($_SESSION['delete_success']);
}

// Get search parameter
$search = isset($_GET['search']) ? mysqli_real_escape_string($dbconnection, $_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Build query
$query = "SELECT * FROM products";
$conditions = [];
if (!empty($search)) {
    $conditions[] = "(name LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($filter == 'low_stock') {
    $conditions[] = "stock < 10";
}
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY created_at DESC";

$result = mysqli_query($dbconnection, $query);

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">
                    <i class="bi bi-box-seam"></i> Manage Products
                </h1>
                <p class="mb-0">View and manage all products</p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="add_product.php" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Add Product
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Products Management -->
<section class="py-5">
    <div class="container">
        <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Search and Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="products.php" method="GET" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search products by name or description..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="filter" onchange="this.form.submit()">
                            <option value="">All Products</option>
                            <option value="low_stock" <?php echo $filter == 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Products Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result && mysqli_num_rows($result) > 0): ?>
                                <?php while($product = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            // Handle image path
                                            $admin_image = $product['image'];
                                            if (!empty($admin_image) && strpos($admin_image, 'http') !== 0 && strpos($admin_image, '/') !== 0) {
                                                $admin_image = '../assets/images/' . $admin_image;
                                            }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($admin_image); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 style="width: 60px; height: 60px; object-fit: cover;" 
                                                 class="rounded">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($product['category']); ?></span>
                                        </td>
                                        <td>
                                            ৳<?php echo number_format($product['price'], 2); ?>
                                            <?php if(isset($product['old_price']) && $product['old_price'] > 0): ?>
                                                <br><small class="text-muted text-decoration-line-through">
                                                    ৳<?php echo number_format($product['old_price'], 2); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock'] < 10 ? 'danger' : 'success'; ?>">
                                                <?php echo $product['stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="../product_details.php?id=<?php echo $product['id']; ?>&from=admin" 
                                                   class="btn btn-outline-primary" 
                                                   title="View" 
                                                   target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-outline-warning" 
                                                   title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-outline-danger" 
                                                   title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-2">No products found</p>
                                        <a href="add_product.php" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Add First Product
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include '../includes/footer.php';
?>

