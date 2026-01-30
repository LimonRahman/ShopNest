<?php
/**
 * ShopNest - Admin Users Management
 * View and manage all users (change role, delete)
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
$pageTitle = "Manage Users";

// Handle actions
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($user_id > 0) {
        // Prevent admin from deleting/changing their own role accidentally
        $is_current_admin = ($user_id === intval($_SESSION['user_id']));

        if ($action === 'change_role') {
            $new_role = isset($_POST['role']) ? $_POST['role'] : '';
            $allowed_roles = ['customer', 'admin'];

            if (in_array($new_role, $allowed_roles)) {
                $role_esc = mysqli_real_escape_string($dbconnection, $new_role);
                $update_query = "UPDATE users SET role = '$role_esc', updated_at = NOW() WHERE id = $user_id";

                if (mysqli_query($dbconnection, $update_query)) {
                    $success = "User role updated successfully.";
                    // If current admin changed their own role, update session
                    if ($is_current_admin) {
                        $_SESSION['user_role'] = $new_role;
                    }
                } else {
                    $error = "Failed to update user role.";
                }
            } else {
                $error = "Invalid role selected.";
            }
        } elseif ($action === 'delete_user') {
            if ($is_current_admin) {
                $error = "You cannot delete your own account from here.";
            } else {
                $delete_query = "DELETE FROM users WHERE id = $user_id";
                if (mysqli_query($dbconnection, $delete_query)) {
                    $success = "User deleted successfully.";
                } else {
                    $error = "Failed to delete user.";
                }
            }
        }
    } else {
        $error = "Invalid user.";
    }
}

// Filters & search
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT * FROM users";
$conditions = [];

if (!empty($role_filter)) {
    $role_esc = mysqli_real_escape_string($dbconnection, $role_filter);
    $conditions[] = "role = '$role_esc'";
}

if (!empty($search)) {
    $search_esc = mysqli_real_escape_string($dbconnection, $search);
    $conditions[] = "(name LIKE '%$search_esc%' OR email LIKE '%$search_esc%' OR phone LIKE '%$search_esc%')";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY created_at DESC";

$users_result = mysqli_query($dbconnection, $query);

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">
                    <i class="bi bi-people"></i> Manage Users
                </h1>
                <p class="mb-0">View and manage all registered users</p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-light btn-sm">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Users Management -->
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
                <form class="row g-3" method="GET" action="users.php">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search"
                               placeholder="Search by name, email or phone"
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="role" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <option value="customer" <?php echo $role_filter === 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <a href="users.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Member Since</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($users_result && mysqli_num_rows($users_result) > 0): ?>
                                <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                                    <tr>
                                        <td>#<?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['updated_at'])); ?></td>
                                        <td>
                                            <div class="d-flex gap-2 align-items-center">
                                                <form method="POST" action="users.php" class="d-flex align-items-center">
                                                    <input type="hidden" name="action" value="change_role">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <select name="role" class="form-select me-2" style="font-size: 1rem; padding: 0.6rem 1rem; min-width: 120px;" onchange="this.form.submit()">
                                                        <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                    </select>
                                                </form>
                                                <?php if($user['id'] !== intval($_SESSION['user_id'])): ?>
                                                    <form method="POST" action="users.php" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete their orders.');">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-danger" style="font-size: 1rem; padding: 0.6rem 1rem;">
                                                            <i class="bi bi-trash me-1"></i> Delete
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0">No users found.</p>
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


