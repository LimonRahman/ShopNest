<?php
/**
 * ShopNest - Admin Order Details
 * Detailed view of a single customer order for admin
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

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header('Location: orders.php');
    exit();
}

// Fetch order details
$order_query = "SELECT o.*, u.name AS user_name, u.email AS user_email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = $order_id
                LIMIT 1";
$order_result = mysqli_query($dbconnection, $order_query);

if (!$order_result || mysqli_num_rows($order_result) === 0) {
    header('Location: orders.php');
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Fetch order items
$items_query = "SELECT oi.*, p.name AS product_name, p.image 
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = $order_id";
$items_result = mysqli_query($dbconnection, $items_query);

// Set page title
$pageTitle = "Order #{$order_id} Details";

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">
                    <i class="bi bi-receipt"></i> Order #<?php echo $order_id; ?>
                </h1>
                <p class="mb-0">Detailed information for this order</p>
            </div>
            <div>
                <a href="orders.php" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Orders
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Order Details -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($items_result && mysqli_num_rows($items_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $grand_total = 0;
                                        while ($item = mysqli_fetch_assoc($items_result)): 
                                            $item_total = $item['price'] * $item['quantity'];
                                            $grand_total += $item_total;

                                            // Handle image path
                                            $item_image = $item['image'] ?? '';
                                            if (!empty($item_image) && strpos($item_image, 'http') !== 0 && strpos($item_image, '/') !== 0) {
                                                $item_image = '../assets/images/' . $item_image;
                                            }
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($item_image)): ?>
                                                            <img src="<?php echo htmlspecialchars($item_image); ?>"
                                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                                 style="width: 50px; height: 50px; object-fit: cover;"
                                                                 class="rounded me-2">
                                                        <?php endif; ?>
                                                        <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center"><?php echo (int)$item['quantity']; ?></td>
                                                <td class="text-end">৳<?php echo number_format($item['price'], 2); ?></td>
                                                <td class="text-end"><strong>৳<?php echo number_format($item_total, 2); ?></strong></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Order Total:</th>
                                            <th class="text-end">৳<?php echo number_format($order['total_amount'], 2); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No items found for this order.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-person"></i> Customer Info</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">
                            <strong><?php echo htmlspecialchars($order['user_name'] ?: $order['full_name']); ?></strong>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-envelope"></i>
                            <?php echo htmlspecialchars($order['user_email'] ?: $order['email']); ?>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-telephone"></i>
                            <?php echo htmlspecialchars($order['phone']); ?>
                        </p>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </p>
                        <p class="mb-0">
                            <?php echo htmlspecialchars($order['city']); ?>,
                            <?php echo htmlspecialchars($order['state']); ?>
                            <?php echo htmlspecialchars($order['zip']); ?><br>
                            <?php echo htmlspecialchars($order['country']); ?>
                        </p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Order Info</h5>
                        <span class="badge bg-<?php 
                            echo $order['status'] == 'completed' ? 'success' : 
                                ($order['status'] == 'shipped' ? 'info' : 
                                ($order['status'] == 'processing' ? 'primary' : 
                                ($order['status'] == 'cancelled' ? 'danger' : 'warning'))); 
                        ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">
                            <strong>Order ID:</strong> #<?php echo $order_id; ?>
                        </p>
                        <p class="mb-1">
                            <strong>Date:</strong>
                            <?php echo date('F d, Y h:i A', strtotime($order['order_date'])); ?>
                        </p>
                        <p class="mb-1">
                            <strong>Payment Method:</strong>
                            <?php echo htmlspecialchars($order['payment_method']); ?>
                        </p>
                        <p class="mb-0">
                            <strong>Total Amount:</strong>
                            ৳<?php echo number_format($order['total_amount'], 2); ?>
                        </p>
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

