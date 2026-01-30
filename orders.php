<?php
/**
 * ShopNest - Order History Page
 * View past orders and order details
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'orders.php';
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'includes/DbConnection.php';

// Set page title
$pageTitle = "My Orders";

// Get user ID
$user_id = $_SESSION['user_id'];

// Fetch user orders
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$orders_result = mysqli_query($dbconnection, $orders_query);

// Include header
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <h1 class="mb-0">
            <i class="bi bi-receipt"></i> My Orders
        </h1>
        <p class="mb-0">View your order history</p>
    </div>
</section>

<!-- Orders Content -->
<section class="py-5">
    <div class="container">
        <?php if($orders_result && mysqli_num_rows($orders_result) > 0): ?>
            <div class="row">
                <div class="col-12">
                    <?php while($order = mysqli_fetch_assoc($orders_result)): 
                        // Get order items
                        $order_id = $order['id'];
                        $items_query = "SELECT oi.*, p.name as product_name, p.image 
                                        FROM order_items oi 
                                        LEFT JOIN products p ON oi.product_id = p.id 
                                        WHERE oi.order_id = $order_id";
                        $items_result = mysqli_query($dbconnection, $items_query);
                    ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="mb-0">
                                            Order #<?php echo $order['id']; ?>
                                        </h5>
                                        <small class="text-muted">
                                            Placed on <?php echo date('F d, Y h:i A', strtotime($order['order_date'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-3 text-md-center">
                                        <strong class="text-primary">
                                            ৳<?php echo number_format($order['total_amount'], 2); ?>
                                        </strong>
                                    </div>
                                    <div class="col-md-3 text-md-end">
                                        <span class="badge bg-<?php 
                                            echo $order['status'] == 'completed' ? 'success' : 
                                                ($order['status'] == 'shipped' ? 'info' : 
                                                ($order['status'] == 'processing' ? 'primary' : 
                                                ($order['status'] == 'cancelled' ? 'danger' : 'warning'))); 
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Order Items -->
                                <div class="mb-3">
                                    <h6>Order Items:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $item_count = 0;
                                                while($item = mysqli_fetch_assoc($items_result)): 
                                                    $item_count++;
                                                    $item_total = $item['price'] * $item['quantity'];
                                                    
                                                    // Handle image path
                                                    $item_image = $item['image'] ?? '';
                                                    if (!empty($item_image) && strpos($item_image, 'http') !== 0 && strpos($item_image, '/') !== 0) {
                                                        $item_image = 'assets/images/' . $item_image;
                                                    }
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php if(!empty($item_image)): ?>
                                                                    <img src="<?php echo htmlspecialchars($item_image); ?>" 
                                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                                         style="width: 50px; height: 50px; object-fit: cover;" 
                                                                         class="rounded me-2">
                                                                <?php endif; ?>
                                                                <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                            </div>
                                                        </td>
                                                        <td><?php echo $item['quantity']; ?></td>
                                                        <td>৳<?php echo number_format($item['price'], 2); ?></td>
                                                        <td><strong>৳<?php echo number_format($item_total, 2); ?></strong></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Shipping Information -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h6><i class="bi bi-truck"></i> Shipping Address</h6>
                                        <p class="mb-1">
                                            <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                                            <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                                            <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['zip']); ?><br>
                                            <?php echo htmlspecialchars($order['country']); ?>
                                        </p>
                                        <p class="mb-0">
                                            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($order['phone']); ?><br>
                                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($order['email']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6><i class="bi bi-credit-card"></i> Payment Information</h6>
                                        <p class="mb-1">
                                            <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?><br>
                                            <strong>Order Total:</strong> ৳<?php echo number_format($order['total_amount'], 2); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- No Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 5rem; color: #ccc;"></i>
                            <h3 class="mt-3">No Orders Yet</h3>
                            <p class="text-muted">You haven't placed any orders yet. Start shopping now!</p>
                            <a href="products.php" class="btn btn-primary mt-3">
                                <i class="bi bi-bag"></i> Browse Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>

