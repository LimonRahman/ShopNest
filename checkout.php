<?php
/**
 * ShopNest - Checkout Page
 * Basic checkout system without payment gateway
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

// Check if cart is not empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Include database connection
require_once 'includes/DbConnection.php';

// Set page title
$pageTitle = "Checkout";

// Handle form submission
$order_placed = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $state = isset($_POST['state']) ? trim($_POST['state']) : '';
    $zip = isset($_POST['zip']) ? trim($_POST['zip']) : '';
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
    
    // Validation
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($state)) $errors[] = "State is required";
    if (empty($zip)) $errors[] = "ZIP code is required";
    if (empty($country)) $errors[] = "Country is required";
    if (empty($payment_method)) $errors[] = "Payment method is required";
    
    // Calculate total
    $subtotal = 0;
    foreach($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $shipping = 0; // Free shipping
    $total = $subtotal + $shipping;
    
    // If no errors, place order
    if (empty($errors)) {
        // Escape input
        $full_name = mysqli_real_escape_string($dbconnection, $full_name);
        $email = mysqli_real_escape_string($dbconnection, $email);
        $phone = mysqli_real_escape_string($dbconnection, $phone);
        $address = mysqli_real_escape_string($dbconnection, $address);
        $city = mysqli_real_escape_string($dbconnection, $city);
        $state = mysqli_real_escape_string($dbconnection, $state);
        $zip = mysqli_real_escape_string($dbconnection, $zip);
        $country = mysqli_real_escape_string($dbconnection, $country);
        $payment_method = mysqli_real_escape_string($dbconnection, $payment_method);
        $user_id = intval($_SESSION['user_id']);
        
        // Insert order
        $order_date = date('Y-m-d H:i:s');
        $status = 'pending';
        
        $order_query = "INSERT INTO orders (user_id, total_amount, shipping_address, city, state, zip, country, phone, email, full_name, payment_method, order_date, status) 
                        VALUES ($user_id, $total, '$address', '$city', '$state', '$zip', '$country', '$phone', '$email', '$full_name', '$payment_method', '$order_date', '$status')";
        
        if (mysqli_query($dbconnection, $order_query)) {
            $order_id = mysqli_insert_id($dbconnection);
            
            // Insert order items
            $order_items_success = true;
            foreach($_SESSION['cart'] as $item) {
                $product_id = intval($item['id']);
                $quantity = intval($item['quantity']);
                $price = floatval($item['price']);
                
                $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                               VALUES ($order_id, $product_id, $quantity, $price)";
                
                if (!mysqli_query($dbconnection, $item_query)) {
                    $order_items_success = false;
                    break;
                }
                
                // Update product stock
                $update_stock_query = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id";
                mysqli_query($dbconnection, $update_stock_query);
            }
            
            if ($order_items_success) {
                // Clear cart
                $_SESSION['cart'] = [];
                $order_placed = true;
                $_SESSION['last_order_id'] = $order_id;
            } else {
                $errors[] = "Error placing order. Please try again.";
            }
        } else {
            $errors[] = "Error placing order. Please try again.";
        }
    }
}

// Get user info if available
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';

// Calculate cart total
$subtotal = 0;
foreach($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 0;
$total = $subtotal + $shipping;

// Include header
include 'includes/header.php';
?>

<?php if($order_placed): ?>
    <!-- Order Success -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm text-center">
                        <div class="card-body py-5">
                            <div class="mb-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                            </div>
                            <h2 class="mb-3">Order Placed Successfully!</h2>
                            <p class="text-muted mb-4">
                                Thank you for your order. Your order ID is <strong>#<?php echo isset($_SESSION['last_order_id']) ? $_SESSION['last_order_id'] : ''; ?></strong>
                            </p>
                            <p class="text-muted mb-4">
                                You will receive an email confirmation shortly at <?php echo htmlspecialchars($user_email); ?>
                            </p>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="index.php" class="btn btn-primary">
                                    <i class="bi bi-house"></i> Back to Home
                                </a>
                                <a href="orders.php" class="btn btn-outline-primary">
                                    <i class="bi bi-receipt"></i> View Orders
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <!-- Page Header -->
    <section class="bg-primary text-white py-4">
        <div class="container">
            <h1 class="mb-0">
                <i class="bi bi-credit-card"></i> Checkout
            </h1>
            <p class="mb-0">Complete your order</p>
        </div>
    </section>

    <!-- Checkout Form -->
    <section class="py-5">
        <div class="container">
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> Please fix the following errors:</h5>
                    <ul class="mb-0">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Checkout Form -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-person"></i> Shipping Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="checkout.php" method="POST">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : htmlspecialchars($user_name); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user_email); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Street Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                           value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                               value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="state" name="state" 
                                               value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="zip" class="form-label">ZIP Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="zip" name="zip" 
                                               value="<?php echo isset($_POST['zip']) ? htmlspecialchars($_POST['zip']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="country" name="country" 
                                           value="<?php echo isset($_POST['country']) ? htmlspecialchars($_POST['country']) : 'United States'; ?>" required>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-3">
                                    <label class="form-label"><strong>Payment Method <span class="text-danger">*</span></strong></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="Cash on Delivery" 
                                               <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Cash on Delivery') ? 'checked' : 'checked'; ?> required>
                                        <label class="form-check-label" for="cod">
                                            <i class="bi bi-cash"></i> Cash on Delivery
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="card" value="Credit/Debit Card" 
                                               <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Credit/Debit Card') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="card">
                                            <i class="bi bi-credit-card"></i> Credit/Debit Card (Not implemented)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-circle"></i> Place Order
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach($_SESSION['cart'] as $item): ?>
                                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <strong>৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>৳<?php echo number_format($subtotal, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <strong>FREE</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span><strong>Total:</strong></span>
                                <strong class="text-primary fs-5">৳<?php echo number_format($total, 2); ?></strong>
                            </div>
                        </div>
                    </div>
                    
                    <a href="cart.php" class="btn btn-outline-secondary w-100 mt-3">
                        <i class="bi bi-arrow-left"></i> Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php
// Include footer
include 'includes/footer.php';
?>

