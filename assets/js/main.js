/**
 * ShopEase - Main JavaScript File
 * Handles common functionality across the website
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize tooltips (Bootstrap feature)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers (Bootstrap feature)
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Add to cart functionality - Forms will handle submission to cart.php
    // Keep for any dynamic add-to-cart buttons that might need JS handling

    // Quantity update functionality
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const cartItemId = this.getAttribute('data-cart-item-id');
            const newQuantity = this.value;
            
            // Validate quantity
            if (newQuantity < 1) {
                this.value = 1;
                showAlert('warning', 'Quantity must be at least 1');
                return;
            }
            
            // Update cart (will be handled by PHP later)
            updateCartItem(cartItemId, newQuantity);
        });
    });

    // Remove item from cart
    const removeCartItemButtons = document.querySelectorAll('.remove-cart-item');
    removeCartItemButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const cartItemId = this.getAttribute('data-cart-item-id');
            const productName = this.getAttribute('data-product-name');
            
            if (confirm(`Are you sure you want to remove ${productName} from your cart?`)) {
                removeCartItem(cartItemId);
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Search functionality
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (searchInput.value.trim() === '') {
                e.preventDefault();
                showAlert('warning', 'Please enter a search term');
            }
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});

/**
 * Show alert message
 * @param {string} type - Alert type (success, danger, warning, info)
 * @param {string} message - Alert message
 */
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlert = document.querySelector('.custom-alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show custom-alert`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Append to body
    document.body.appendChild(alertDiv);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

/**
 * Update cart count in navigation
 */
function updateCartCount() {
    // This will be implemented with PHP session later
    // For now, just reload the page to update count
    // In production, this would use AJAX
    console.log('Cart count updated');
}

/**
 * Update cart item quantity
 * @param {string} cartItemId - Cart item ID
 * @param {number} quantity - New quantity
 */
function updateCartItem(cartItemId, quantity) {
    // This will be implemented with PHP later
    console.log(`Updating cart item ${cartItemId} to quantity ${quantity}`);
    // Reload page to update cart
    window.location.reload();
}

/**
 * Remove item from cart
 * @param {string} cartItemId - Cart item ID
 */
function removeCartItem(cartItemId) {
    // This will be implemented with PHP later
    console.log(`Removing cart item ${cartItemId}`);
    // Reload page to update cart
    window.location.reload();
}

/**
 * Format currency
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return '৳' + new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

/**
 * Calculate total price
 * @param {number} price - Item price
 * @param {number} quantity - Item quantity
 * @returns {number} Total price
 */
function calculateTotal(price, quantity) {
    return price * quantity;
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showAlert,
        updateCartCount,
        updateCartItem,
        removeCartItem,
        formatCurrency,
        calculateTotal
    };
}

