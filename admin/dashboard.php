<?php
/**
 * ShopNest - Admin Dashboard
 * Admin dashboard with statistics and quick actions
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
$pageTitle = "Admin Dashboard";

// Get statistics
$stats = [];

// Total products
$products_query = "SELECT COUNT(*) as total FROM products";
$result = mysqli_query($dbconnection, $products_query);
$stats['products'] = mysqli_fetch_assoc($result)['total'];

// Total orders
$orders_query = "SELECT COUNT(*) as total FROM orders";
$result = mysqli_query($dbconnection, $orders_query);
$stats['orders'] = mysqli_fetch_assoc($result)['total'];

// Total users
$users_query = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($dbconnection, $users_query);
$stats['users'] = mysqli_fetch_assoc($result)['total'];

// Total revenue
$revenue_query = "SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'";
$result = mysqli_query($dbconnection, $revenue_query);
$revenue = mysqli_fetch_assoc($result);
$stats['revenue'] = $revenue['total'] ? $revenue['total'] : 0;

// Pending orders
$pending_query = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
$result = mysqli_query($dbconnection, $pending_query);
$stats['pending_orders'] = mysqli_fetch_assoc($result)['total'];

// Recent orders
$recent_orders_query = "SELECT o.*, u.name as user_name FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.order_date DESC LIMIT 10";
$recent_orders = mysqli_query($dbconnection, $recent_orders_query);

// Low stock products
$low_stock_query = "SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 10";
$low_stock = mysqli_query($dbconnection, $low_stock_query);

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">
                    <i class="bi bi-speedometer2"></i> Admin Dashboard
                </h1>
                <p class="mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            </div>
            <a href="../index.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Back to Site
            </a>
        </div>
    </div>
</section>

<!-- Statistics Cards -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="text-muted mb-1">Total Products</h6>
                                <h3 class="mb-0"><?php echo $stats['products']; ?></h3>
                            </div>
                            <div class="bg-primary text-white rounded-circle p-3">
                                <i class="bi bi-box-seam" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <a href="products.php" class="btn btn-sm btn-outline-primary mt-auto">
                            <i class="bi bi-eye"></i> View All
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="text-muted mb-1">Total Orders</h6>
                                <h3 class="mb-0"><?php echo $stats['orders']; ?></h3>
                            </div>
                            <div class="bg-success text-white rounded-circle p-3">
                                <i class="bi bi-cart-check" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <small class="text-danger d-block mb-2"><?php echo $stats['pending_orders']; ?> pending</small>
                            <a href="orders.php" class="btn btn-sm btn-outline-success w-100">
                                <i class="bi bi-eye"></i> View All
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="text-muted mb-1">Total Users</h6>
                                <h3 class="mb-0"><?php echo $stats['users']; ?></h3>
                            </div>
                            <div class="bg-info text-white rounded-circle p-3">
                                <i class="bi bi-people" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <a href="users.php" class="btn btn-sm btn-outline-info mt-auto">
                            <i class="bi bi-eye"></i> View All
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="text-muted mb-1">Total Revenue</h6>
                                <h3 class="mb-0">৳<?php echo number_format($stats['revenue'], 2); ?></h3>
                            </div>
                            <div class="bg-warning text-white rounded-circle p-3">
                                <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <a href="orders.php?status=completed" class="btn btn-sm btn-outline-warning mt-auto">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Orders -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-receipt"></i> Recent Orders</h5>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($recent_orders && mysqli_num_rows($recent_orders) > 0): ?>
                                        <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                                <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $order['status'] == 'completed' ? 'success' : 
                                                            ($order['status'] == 'pending' ? 'warning' : 'secondary'); 
                                                    ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No orders yet</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions & Low Stock -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="add_product.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add New Product
                            </a>
                            <a href="products.php" class="btn btn-outline-primary">
                                <i class="bi bi-box-seam"></i> Manage Products
                            </a>
                            <a href="orders.php" class="btn btn-outline-success">
                                <i class="bi bi-receipt"></i> View All Orders
                            </a>
                            <a href="users.php" class="btn btn-outline-info">
                                <i class="bi bi-people"></i> Manage Users
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Low Stock Alert -->
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Low Stock Alert</h5>
                    </div>
                    <div class="card-body">
                        <?php if($low_stock && mysqli_num_rows($low_stock) > 0): ?>
                            <ul class="list-unstyled mb-0">
                                <?php while($product = mysqli_fetch_assoc($low_stock)): ?>
                                    <li class="mb-2 pb-2 border-bottom">
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                        <small class="text-danger">Stock: <?php echo $product['stock']; ?></small>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                            <a href="products.php?filter=low_stock" class="btn btn-sm btn-warning w-100 mt-3">
                                View All
                            </a>
                        <?php else: ?>
                            <p class="text-muted mb-0">All products are well stocked!</p>
                        <?php endif; ?>
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

