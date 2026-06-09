/* ===========================
   Products.js - Product Management
   ========================== */

(function() {
  'use strict';

  // =======================
  // State
  // =======================
  let currentProductId = null;

  // =======================
  // Initialization
  // =======================

  function init() {
    initFilterTabs();
    console.log('Products list initialized');
  }

  /**
   * Initialize filter tabs
   */
  function initFilterTabs() {
    const filterTabs = document.querySelectorAll('.filter-tab');

    filterTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        filterTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const filter = tab.dataset.filter;
        filterProducts(filter);
      });
    });
  }

  /**
   * Filter products by status
   */
  function filterProducts(status) {
    const rows = document.querySelectorAll('#productsTable tbody tr');
    rows.forEach(row => {
      const badge = row.querySelector('.badge');
      const rowStatus = badge.classList.contains('badge-active') ? 'active' :
                        badge.classList.contains('badge-pending') ? 'pending' :
                        badge.classList.contains('badge-draft') ? 'draft' :
                        badge.classList.contains('badge-rejected') ? 'rejected' : 'outofstock';

      if (status === 'all') {
        row.style.display = '';
      } else {
        row.style.display = rowStatus === status ? '' : 'none';
      }
    });
  }

  // =======================
  // Action Handlers
  // =======================

  /**
   * Create new product
   */
  window.createProduct = function() {
    console.log('Creating product...');
    closeModal('addProductModal');
    showToast('Product created successfully', 'success');
  };

  /**
   * Edit product
   */
  window.editProduct = function(productId) {
    currentProductId = productId;
    console.log('Editing product:', productId);
    showModal('editProductModal');
  };

  /**
   * Save product changes
   */
  window.saveProduct = function() {
    console.log('Saving product:', currentProductId);
    closeModal('editProductModal');
    showToast('Product updated successfully', 'success');
  };

  /**
   * View product details
   */
  window.viewProduct = function(productId) {
    console.log('Viewing product:', productId);
    showModal('productDetailsModal');
  };

  /**
   * Approve product
   */
  window.approveProduct = function(productId) {
    currentProductId = productId;
    if (confirm('Are you sure you want to approve this product?')) {
      console.log('Approving product:', productId);
      closeModal('productApprovalModal');
      showToast('Product approved successfully', 'success');
    }
  };

  /**
   * Reject product
   */
  window.rejectProduct = function(productId) {
    currentProductId = productId;
    showModal('productRejectionModal');
  };

  /**
   * Confirm product rejection
   */
  window.confirmRejectProduct = function() {
    console.log('Rejecting product:', currentProductId);
    closeModal('productRejectionModal');
    showToast('Product rejected', 'info');
  };

  /**
   * Delete product
   */
  window.deleteProduct = function(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
      console.log('Deleting product:', productId);
      showToast('Product deleted successfully', 'success');
    }
  };

  // =======================
  // Initialize on DOM ready
  // =======================

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
