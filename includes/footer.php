    </main>
    <!-- End of main content -->

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <!-- About Section -->
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-bag-heart"></i> ShopNest
                    </h5>
                    <p>Your trusted online shopping destination. We offer a wide range of quality products at competitive prices.</p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-md-2 mb-4">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <?php
                        // Determine base path for footer links (works from both root and admin folders)
                        $footer_base = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
                        ?>
                        <li><a href="<?php echo $footer_base; ?>index.php" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="<?php echo $footer_base; ?>products.php" class="text-light text-decoration-none">Products</a></li>
                        <li><a href="<?php echo $footer_base; ?>cart.php" class="text-light text-decoration-none">Cart</a></li>
                        
                    </ul>
                </div>
                
                <!-- Customer Service -->
                <div class="col-md-3 mb-4">
                    <h5 class="fw-bold mb-3">Customer Service</h5>
                    <ul class="list-unstyled">
                        <li><a href="contact.php" class="text-light text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-md-3 mb-4">
                    <h5 class="fw-bold mb-3">Contact Info</h5>
                    <p class="mb-2">
                        <i class="bi bi-geo-alt"></i> Kuripara, Uttarkhan, Uttara, Dhaka-1230
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-telephone"></i> +8801931117253
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-envelope"></i> limon.rahman.09@gmailcom
                    </p>
                </div>
            </div>
            
            <hr class="bg-light">
            
            <!-- Copyright -->
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> ShopNest. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <?php
    // Determine base path for assets (works from both root and admin folders)
    $base_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
    ?>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo $base_path; ?>assets/js/main.js"></script>
</body>
</html>

