/* ===========================
   Sellers.js - Seller Management
   ========================== */

(function() {
  'use strict';

  // =======================
  // State
  // =======================
  let selectedSeller = null;
  let pendingAction = null;

  // =======================
  // Initialization
  // =======================

  function init() {
    initFilterTabs();
    console.log('Sellers list initialized');
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
        filterSellers(filter);
      });
    });
  }

  /**
   * Filter sellers by status
   */
  function filterSellers(status) {
    const rows = document.querySelectorAll('#sellersTable tbody tr');
    rows.forEach(row => {
      const badge = row.querySelector('.badge');
      const rowStatus = badge.classList.contains('badge-active') ? 'active' :
                        badge.classList.contains('badge-pending') ? 'pending' : 'suspended';

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
   * Approve seller
   */
  window.approveSeller = function(sellerId) {
    selectedSeller = sellerId;
    pendingAction = 'approve';
    showModal('approveModal');
  };

  /**
   * Confirm seller approval
   */
  window.confirmApprove = function() {
    console.log('Approving seller:', selectedSeller);
    closeModal('approveModal');
    showToast('Seller approved successfully', 'success');
    selectedSeller = null;
    pendingAction = null;
  };

  /**
   * Reject seller application
   */
  window.rejectSeller = function(sellerId) {
    selectedSeller = sellerId;
    pendingAction = 'reject';
    showModal('rejectModal');
  };

  /**
   * Confirm seller rejection
   */
  window.confirmReject = function() {
    console.log('Rejecting seller:', selectedSeller);
    closeModal('rejectModal');
    showToast('Seller application rejected', 'info');
    selectedSeller = null;
    pendingAction = null;
  };

  /**
   * View seller details
   */
  window.viewSeller = function(sellerId) {
    console.log('Viewing seller:', sellerId);
    showModal('sellerDetailsModal');
  };

  /**
   * Edit seller
   */
  window.editSeller = function(sellerId) {
    console.log('Editing seller:', sellerId);
    showModal('editSellerModal');
  };

  /**
   * Save seller changes
   */
  window.saveSeller = function() {
    console.log('Saving seller changes...');
    closeModal('editSellerModal');
    showToast('Seller updated successfully', 'success');
  };

  /**
   * View seller orders
   */
  window.viewSellerOrders = function(sellerId) {
    console.log('Viewing seller orders:', sellerId);
    window.location.href = `pages/sellers/seller-orders.html?sellerId=${sellerId}`;
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
