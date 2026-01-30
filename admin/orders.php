<?php
/**
 * ShopNest - Admin Orders Management
 * View and manage all customer orders
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
$pageTitle = "Manage Orders";

// Handle status update
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $new_status = isset($_POST['status']) ? $_POST['status'] : '';

    $allowed_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

    if ($order_id > 0 && in_array($new_status, $allowed_statuses)) {
        $new_status_esc = mysqli_real_escape_string($dbconnection, $new_status);
        $update_query = "UPDATE orders SET status = '$new_status_esc' WHERE id = $order_id";

        if (mysqli_query($dbconnection, $update_query)) {
            $success = "Order #$order_id status updated to " . ucfirst($new_status) . ".";
        } else {
            $error = "Failed to update order status. Please try again.";
        }
    } else {
        $error = "Invalid order or status.";
    }
}

// Filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT o.*, u.name AS user_name, u.email AS user_email 
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id";
$conditions = [];

if (!empty($status_filter)) {
    $status_esc = mysqli_real_escape_string($dbconnection, $status_filter);
    $conditions[] = "o.status = '$status_esc'";
}

if (!empty($search)) {
    $search_esc = mysqli_real_escape_string($dbconnection, $search);
    $conditions[] = "(o.id LIKE '%$search_esc%' OR u.name LIKE '%$search_esc%' OR u.email LIKE '%$search_esc%')";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY o.order_date DESC";

$orders_result = mysqli_query($dbconnection, $query);

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">
                    <i class="bi bi-receipt"></i> Manage Orders
                </h1>
                <p class="mb-0">View and update all customer orders</p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-light btn-sm">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Orders Management -->
<section class="py-5">
    <div class="container">
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

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form class="row g-3" method="GET" action="orders.php">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by order ID, customer name or email" 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <?php
                            $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
                            foreach ($statuses as $status) {
                                $selected = ($status_filter === $status) ? 'selected' : '';
                                echo "<option value=\"$status\" $selected>" . ucfirst($status) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <a href="orders.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Items</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($orders_result && mysqli_num_rows($orders_result) > 0): ?>
                                <?php 
                                $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
                                while($order = mysqli_fetch_assoc($orders_result)): 
                                    $order_id = $order['id'];
                                    $items_count_query = "SELECT COUNT(*) AS item_count FROM order_items WHERE order_id = $order_id";
                                    $items_count_result = mysqli_query($dbconnection, $items_count_query);
                                    $items_count_row = mysqli_fetch_assoc($items_count_result);
                                    $items_count = $items_count_row ? $items_count_row['item_count'] : 0;
                                ?>
                                    <tr>
                                        <td>#<?php echo $order_id; ?></td>
                                        <td><?php echo htmlspecialchars($order['user_name'] ?: $order['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['user_email'] ?: $order['email']); ?></td>
                                        <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $order['status'] == 'completed' ? 'success' : 
                                                    ($order['status'] == 'shipped' ? 'info' : 
                                                    ($order['status'] == 'processing' ? 'primary' : 
                                                    ($order['status'] == 'cancelled' ? 'danger' : 'warning'))); 
                                            ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $items_count; ?></td>
                                        <td>
                                            <div class="d-flex gap-2 align-items-center">
                                                <a href="view_order.php?id=<?php echo $order_id; ?>" class="btn btn-outline-primary" style="font-size: 1rem; padding: 0.6rem 1rem;">
                                                    <i class="bi bi-eye me-1"></i> View
                                                </a>
                                                <form method="POST" action="orders.php" class="d-flex align-items-center">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                                    <select name="status" class="form-select me-2" style="font-size: 1rem; padding: 0.6rem 1rem; min-width: 140px;" onchange="this.form.submit()">
                                                        <?php
                                                        foreach ($statuses as $status) {
                                                            $selected = ($order['status'] === $status) ? 'selected' : '';
                                                            echo "<option value=\"$status\" $selected>" . ucfirst($status) . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0">No orders found.</p>
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
