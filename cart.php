<?php
/**
 * ShopNest - Shopping Cart Page
 * Handles adding, updating, and removing items from cart
 */

// Start session
session_start();

// Include database connection
require_once 'includes/DbConnection.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    switch ($action) {
        case 'add':
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            
            if ($product_id > 0 && $quantity > 0) {
                // Check if product exists and is in stock
                $query = "SELECT * FROM products WHERE id = $product_id";
                $result = mysqli_query($dbconnection, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $product = mysqli_fetch_assoc($result);
                    
                    if ($product['stock'] >= $quantity) {
                        // Check if product already in cart
                        if (isset($_SESSION['cart'][$product_id])) {
                            $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
                            if ($new_quantity <= $product['stock']) {
                                $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                            } else {
                                $_SESSION['cart'][$product_id]['quantity'] = $product['stock'];
                                $_SESSION['cart_message'] = 'Maximum stock reached for ' . $product['name'];
                            }
                        } else {
                            $_SESSION['cart'][$product_id] = [
                                'id' => $product['id'],
                                'name' => $product['name'],
                                'price' => $product['price'],
                                'image' => $product['image'],
                                'quantity' => $quantity,
                                'stock' => $product['stock']
                            ];
                        }
                        $_SESSION['cart_success'] = $product['name'] . ' added to cart!';
                    } else {
                        $_SESSION['cart_error'] = 'Insufficient stock available.';
                    }
                }
            }
            break;
            
        case 'update':
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            
            if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
                if ($quantity > 0 && $quantity <= $_SESSION['cart'][$product_id]['stock']) {
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                    $_SESSION['cart_success'] = 'Cart updated successfully!';
                } else {
                    $_SESSION['cart_error'] = 'Invalid quantity or insufficient stock.';
                }
            }
            break;
            
        case 'remove':
            if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
                $product_name = $_SESSION['cart'][$product_id]['name'];
                unset($_SESSION['cart'][$product_id]);
                $_SESSION['cart_success'] = $product_name . ' removed from cart!';
            }
            break;
            
        case 'clear':
            $_SESSION['cart'] = [];
            $_SESSION['cart_success'] = 'Cart cleared successfully!';
            break;
    }
    
    // Redirect to prevent form resubmission
    header("Location: cart.php");
    exit();
}

// Set page title
$pageTitle = "Shopping Cart";

// Include header
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <h1 class="mb-0">
            <i class="bi bi-cart"></i> Shopping Cart
        </h1>
        <p class="mb-0">Review your items before checkout</p>
    </div>
</section>

<!-- Cart Messages -->
<section class="py-3">
    <div class="container">
        <?php if(isset($_SESSION['cart_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo $_SESSION['cart_success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['cart_success']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['cart_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> <?php echo $_SESSION['cart_error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['cart_error']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['cart_message'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle"></i> <?php echo $_SESSION['cart_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['cart_message']); ?>
        <?php endif; ?>
    </div>
</section>

<!-- Cart Content -->
<section class="py-5">
    <div class="container">
        <?php if(empty($_SESSION['cart'])): ?>
            <!-- Empty Cart -->
            <div class="row">
                <div class="col-12">
                    <div class="empty-state text-center py-5">
                        <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
                        <h3 class="mt-3">Your cart is empty</h3>
                        <p class="text-muted">Start adding items to your cart!</p>
                        <a href="products.php" class="btn btn-primary mt-3">
                            <i class="bi bi-bag"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Cart Items (<?php echo count($_SESSION['cart']); ?>)</h5>
                            <form action="cart.php" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to clear your cart?')">
                                    <i class="bi bi-trash"></i> Clear Cart
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            <?php 
                            $total = 0;
                            foreach($_SESSION['cart'] as $item): 
                                $item_total = $item['price'] * $item['quantity'];
                                $total += $item_total;
                            ?>
                                <div class="row border-bottom py-3 align-items-center">
                                    <div class="col-md-2 mb-3 mb-md-0">
                                        <?php 
                                        // Handle image path
                                        $item_image = $item['image'];
                                        if (!empty($item_image) && strpos($item_image, 'http') !== 0 && strpos($item_image, '/') !== 0) {
                                            $item_image = 'assets/images/' . $item_image;
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($item_image); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="img-fluid rounded"
                                             style="width: 100%; max-width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <p class="text-muted small mb-0">Price: ৳<?php echo number_format($item['price'], 2); ?></p>
                                    </div>
                                    <div class="col-md-2 mb-3 mb-md-0">
                                        <form action="cart.php" method="POST" class="d-flex align-items-center">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                            <input type="number" 
                                                   name="quantity" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="1" 
                                                   max="<?php echo $item['stock']; ?>" 
                                                   class="form-control form-control-sm" 
                                                   style="width: 80px;"
                                                   onchange="this.form.submit()">
                                        </form>
                                        <small class="text-muted d-block mt-1">Max: <?php echo $item['stock']; ?></small>
                                    </div>
                                    <div class="col-md-2 mb-3 mb-md-0 text-md-center">
                                        <strong>৳<?php echo number_format($item_total, 2); ?></strong>
                                    </div>
                                    <div class="col-md-2 text-md-end">
                                        <form action="cart.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Remove this item from cart?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>৳<?php echo number_format($total, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <strong>FREE</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span><strong>Total:</strong></span>
                                <strong class="text-primary fs-5">৳<?php echo number_format($total, 2); ?></strong>
                            </div>
                            
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <a href="checkout.php" class="btn btn-primary w-100 btn-lg">
                                    <i class="bi bi-credit-card"></i> Proceed to Checkout
                                </a>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Please <a href="login.php">login</a> to proceed with checkout.
                                </div>
                                <a href="login.php" class="btn btn-primary w-100">
                                    <i class="bi bi-box-arrow-in-right"></i> Login to Checkout
                                </a>
                            <?php endif; ?>
                            
                            <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="bi bi-arrow-left"></i> Continue Shopping
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

