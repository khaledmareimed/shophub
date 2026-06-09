/* ===========================
   Orders.js - Order Management
   ========================== */

(function() {
  'use strict';

  // =======================
  // State
  // =======================
  let currentOrderId = null;

  // =======================
  // Initialization
  // =======================

  function init() {
    initFilterTabs();
    console.log('Orders list initialized');
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
        filterOrders(filter);
      });
    });
  }

  /**
   * Filter orders by status
   */
  function filterOrders(status) {
    const rows = document.querySelectorAll('#ordersTable tbody tr');
    rows.forEach(row => {
      const badge = row.querySelector('.badge');
      const rowStatus = badge.classList.contains('badge-active') ? 'active' :
                        badge.classList.contains('badge-pending') ? 'pending' :
                        badge.classList.contains('badge-processing') ? 'processing' :
                        badge.classList.contains('badge-completed') ? 'completed' :
                        badge.classList.contains('badge-cancelled') ? 'cancelled' : 'refunded';

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
   * View order details
   */
  window.viewOrder = function(orderId) {
    currentOrderId = orderId;
    console.log('Viewing order:', orderId);
    showModal('orderDetailsModal');
  };

  /**
   * Save order changes
   */
  window.saveOrderChanges = function() {
    console.log('Saving order:', currentOrderId);
    closeModal('orderDetailsModal');
    showToast('Order updated successfully', 'success');
  };

  /**
   * Cancel order
   */
  window.cancelOrder = function() {
    if (confirm('Are you sure you want to cancel this order?')) {
      console.log('Cancelling order:', currentOrderId);
      closeModal('orderDetailsModal');
      showToast('Order cancelled', 'warning');
    }
  };

  /**
   * Issue refund
   */
  window.refundOrder = function() {
    if (confirm('Are you sure you want to issue a refund for this order?')) {
      console.log('Refunding order:', currentOrderId);
      closeModal('orderDetailsModal');
      showToast('Refund processed successfully', 'success');
    }
  };

  /**
   * Print invoice
   */
  window.printInvoice = function() {
    console.log('Printing invoice for order:', currentOrderId);
    window.print();
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
