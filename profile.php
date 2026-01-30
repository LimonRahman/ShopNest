<?php
/**
 * ShopNest - User Profile Page
 * View and edit user profile information
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'profile.php';
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'includes/DbConnection.php';

// Set page title
$pageTitle = "My Profile";

// Get user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$result = mysqli_query($dbconnection, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: logout.php");
    exit();
}

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action == 'update_profile') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        
        if (empty($name) || empty($email)) {
            $error = "Name and email are required fields.";
        } else {
            // Check if email is already taken by another user
            $email = mysqli_real_escape_string($dbconnection, $email);
            $check_query = "SELECT id FROM users WHERE email = '$email' AND id != $user_id LIMIT 1";
            $check_result = mysqli_query($dbconnection, $check_query);
            
            if ($check_result && mysqli_num_rows($check_result) > 0) {
                $error = "Email address is already registered to another account.";
            } else {
                // Update user profile
                $name = mysqli_real_escape_string($dbconnection, $name);
                $phone = mysqli_real_escape_string($dbconnection, $phone);
                
                $update_query = "UPDATE users SET name = '$name', email = '$email', phone = '$phone', updated_at = NOW() WHERE id = $user_id";
                
                if (mysqli_query($dbconnection, $update_query)) {
                    // Update session
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $success = "Profile updated successfully!";
                    // Refresh user data
                    $query = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
                    $result = mysqli_query($dbconnection, $query);
                    $user = mysqli_fetch_assoc($result);
                } else {
                    $error = "Error updating profile. Please try again.";
                }
            }
        }
    } elseif ($action == 'change_password') {
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All password fields are required.";
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect.";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = '$hashed_password', updated_at = NOW() WHERE id = $user_id";
            
            if (mysqli_query($dbconnection, $update_query)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Error changing password. Please try again.";
            }
        }
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <h1 class="mb-0">
            <i class="bi bi-person-circle"></i> My Profile
        </h1>
        <p class="mb-0">Manage your account information</p>
    </div>
</section>

<!-- Profile Content -->
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
        
        <div class="row">
            <!-- Profile Information -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-person"></i> Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="profile.php" method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Account Type</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo ucfirst($user['role']); ?>" disabled>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" disabled>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-lock"></i> Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form action="profile.php" method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-key"></i> Change Password
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
include 'includes/footer.php';
?>

