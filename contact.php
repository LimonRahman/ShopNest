<?php
/**
 * ShopNest - Contact Us Page
 * Basic contact form (no database, optional email send)
 */

// Start session
session_start();

// Set page title
$pageTitle = "Contact Us";

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // On a real server you can enable this to send email.
        // For XAMPP/local development this may not work without configuration.
        /*
        $to = "support@shopnest.com"; // change to your support email
        $headers = "From: " . $name . " <" . $email . ">\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        @mail($to, $subject, $body, $headers);
        */

        $success = "Thank you, your message has been received. We will get back to you soon.";
        // Clear form values after successful submission
        $name = $email = $subject = $message = '';
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <h1 class="mb-0">
            <i class="bi bi-envelope"></i> Contact Us
        </h1>
        <p class="mb-0">We would love to hear from you</p>
    </div>
</section>

<!-- Contact Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="mb-3"><i class="bi bi-chat-dots"></i> Send us a message</h4>

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

                        <form action="contact.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                                       required>
                                <div class="invalid-feedback">Please enter your name.</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                       required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject"
                                       value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>"
                                       required>
                                <div class="invalid-feedback">Please enter a subject.</div>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5"
                                          required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                                <div class="invalid-feedback">Please enter your message.</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send"></i> Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="mb-3"><i class="bi bi-geo-alt"></i> Our Office</h4>
                        <p class="mb-2">
                            <i class="bi bi-geo-alt-fill"></i>
                            Kuripara, Uttarkhan, Uttara, Dhaka-1230
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-telephone-fill"></i>
                            +8801931117253
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-envelope-fill"></i>
                            limon.rahman.09@gmailcom
                        </p>
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


