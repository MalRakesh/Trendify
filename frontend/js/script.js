// script.js - Main Application Logic for Trendify
// Handles: Cart, LocalStorage, Product Actions, UI Updates
// Total Lines: ~350

/**
 * CART MANAGEMENT
 */

// script.js - Shared Functions for Trendify Frontend

// Get cart from localStorage
function getCart() {
    return JSON.parse(localStorage.getItem("trendify_cart")) || [];
}

// Save cart to localStorage
function saveCart(cart) {
    localStorage.setItem("trendify_cart", JSON.stringify(cart));
}

// Update cart badge (e.g. Cart (8)) - Shows total quantity of all items
function updateCartBadge() {
    const cart = getCart();
    // ✅ Total quantity = sum of all item quantities
    const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);

    // Select ALL cart links (header, footer, nav)
    const cartLinks = document.querySelectorAll('a[href="cart.html"], a[href="./cart.html"], a[href="../cart.html"]');

    cartLinks.forEach(link => {
        // Remove old badge like (3)
        const text = link.textContent.replace(/ \(.*\)/, '').trim();
        // Set new badge with total quantity
        link.textContent = `${text} (${totalItems})`;
    });
}

// Add to cart
function addToCart(productId) {
    const cart = getCart();
    const existing = cart.find(item => item.id === productId);
    
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ id: productId, qty: 1 });
    }

    saveCart(cart);
    updateCartBadge(); // ✅ Update badge
    showToast("Item added to cart!");
}

// Add to cart from product detail (with quantity selector)
function addToCartFromDetail(productId) {
    const qty = parseInt(document.getElementById("quantity").textContent);
    const cart = getCart();
    const existing = cart.find(item => item.id === productId);
    
    if (existing) {
        existing.qty += qty;
    } else {
        cart.push({ id: productId, qty: qty });
    }

    saveCart(cart);
    updateCartBadge();
    showToast(`${qty} item(s) added to cart!`);
}

// Update quantity
function updateQty(productId, newQty) {
    if (newQty < 1) return;
    const cart = getCart();
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.qty = newQty;
        saveCart(cart);
        if (typeof loadCart === "function") {
            loadCart(); // Refresh cart UI
        }
        updateCartBadge();
    }
}

// Remove item from cart
function removeFromCart(productId) {
    const cart = getCart().filter(item => item.id !== productId);
    saveCart(cart);
    if (typeof loadCart === "function") {
        loadCart();
    }
    updateCartBadge();
    showToast("Item removed from cart.");
}

// Clear entire cart
function clearCart() {
    localStorage.removeItem("trendify_cart");
    updateCartBadge();
    if (typeof loadCart === "function") {
        loadCart();
    }
}

// Show toast message
function showToast(message) {
    let toast = document.getElementById("toast");
    if (!toast) {
        toast = document.createElement("div");
        toast.id = "toast";
        toast.style.cssText = `
            position: fixed; bottom: 20px; left: 50%;
            transform: translateX(-50%);
            background: #333; color: white; padding: 12px 24px;
            border-radius: 6px; z-index: 1000; font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        `;
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.display = "block";
    setTimeout(() => {
        toast.style.opacity = 0;
        setTimeout(() => toast.style.display = "none", 300);
        toast.style.opacity = 1;
    }, 2000);
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
    updateCartBadge(); // ✅ Always run on every page
});

/**
 * UI & UX HELPERS
 */

// Show toast notification
function showToast(message, duration = 3000) {
  let toast = document.getElementById("trendify-toast");
  if (!toast) {
    toast = document.createElement("div");
    toast.id = "trendify-toast";
    toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 9999;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        `;
    document.body.appendChild(toast);
  }

  toast.textContent = message;
  toast.style.opacity = 1;
  toast.style.transform = "translateY(0)";

  setTimeout(() => {
    toast.style.opacity = 0;
    toast.style.transform = "translateY(20px)";
  }, duration);
}

// Confirm action
function confirmAction(message, onConfirm) {
  if (confirm(message)) {
    onConfirm();
  }
}

// Smooth scroll to top
function scrollToTop() {
  window.scrollTo({ top: 0, behavior: "smooth" });
}

// Back to top button
function initBackToTop() {
  const btn = document.createElement("button");
  btn.textContent = "↑";
  btn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: #2c3e50;
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 1.5rem;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 1000;
    `;
  document.body.appendChild(btn);

  window.addEventListener("scroll", () => {
    if (window.pageYOffset > 300) {
      btn.style.opacity = 1;
    } else {
      btn.style.opacity = 0;
    }
  });

  btn.addEventListener("click", scrollToTop);
}

// Initialize back to top button
document.addEventListener("DOMContentLoaded", initBackToTop);

/**
 * FORM VALIDATION
 */

// Validate email
function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// Validate password
function isValidPassword(password) {
  return password.length >= 6;
}

/**
 * SEARCH & FILTER
 */

// Search products (used in products.html)
function searchProducts(query) {
  const products = document.querySelectorAll(".product-card");
  query = query.toLowerCase();

  products.forEach((product) => {
    const name = product.querySelector("h3").textContent.toLowerCase();
    const desc =
      product.querySelector(".product-info p")?.textContent.toLowerCase() || "";
    if (name.includes(query) || desc.includes(query)) {
      product.style.display = "block";
    } else {
      product.style.display = "none";
    }
  });
}

/**
 * PAGE-SPECIFIC INIT
 */

// Run on DOM load
document.addEventListener("DOMContentLoaded", () => {
  // Update cart badge
  updateCartBadge();

  // Add tooltips or animations if needed
  console.log("Trendify Frontend App Loaded.");
});

/**
 * UTILITY FUNCTIONS
 */

// Get URL parameter
function getUrlParameter(name) {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(name);
}

// Format price
function formatPrice(price) {
  return "₹" + price.toLocaleString("en-IN");
}

// Debounce function
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Auto-hide alerts
function autoHideAlerts() {
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      if (alert.parentNode) {
        alert.style.opacity = 0;
        setTimeout(() => alert.remove(), 300);
      }
    }, 5000);
  });
}

// Initialize auto-hide alerts
document.addEventListener("DOMContentLoaded", autoHideAlerts);

// End of script.js
