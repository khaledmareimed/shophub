/* ===========================
   Users.js - User Management
   ========================== */

(function() {
  'use strict';

  // =======================
  // DOM Elements
  // =======================

  // =======================
  // State
  // =======================
  let selectedUsers = new Set();

  // =======================
  // Initialization
  // =======================

  function init() {
    initBulkSelection();
    initFilterTabs();
    initUserList();
  }

  /**
   * Initialize bulk selection
   */
  function initBulkSelection() {
    const selectAll = document.getElementById('selectAll');
    const bulkSelectionCount = document.getElementById('bulkSelectionCount');
    const applyBulkAction = document.getElementById('applyBulkAction');

    // Select all checkbox
    selectAll?.addEventListener('change', (e) => {
      const checkboxes = document.querySelectorAll('.user-select');
      checkboxes.forEach(cb => cb.checked = e.target.checked);

      if (e.target.checked) {
        document.querySelectorAll('.user-select').forEach(cb => selectedUsers.add(cb.closest('tr').dataset.userId || ''));
      } else {
        selectedUsers.clear();
      }

      updateBulkSelectionCount(bulkSelectionCount);
      updateBulkActionState(applyBulkAction);
    });

    // Individual user checkboxes
    document.querySelectorAll('.user-select').forEach(cb => {
      cb.addEventListener('change', (e) => {
        const row = e.target.closest('tr');
        const userId = row.dataset.userId || '';

        if (e.target.checked) {
          selectedUsers.add(userId);
        } else {
          selectedUsers.delete(userId);
        }

        updateBulkSelectionCount(bulkSelectionCount);
        updateBulkActionState(applyBulkAction);

        // Update select all checkbox
        const allChecked = Array.from(document.querySelectorAll('.user-select')).every(cb => cb.checked);
        selectAll.checked = allChecked && selectedUsers.size > 0;
      });
    });

    // Bulk action apply button
    applyBulkAction?.addEventListener('click', () => {
      const action = document.getElementById('bulkActionSelect').value;
      if (action && selectedUsers.size > 0) {
        handleBulkAction(action, selectedUsers);
      }
    });
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
        filterUsers(filter);
      });
    });
  }

  /**
   * Filter users by status
   */
  function filterUsers(status) {
    const rows = document.querySelectorAll('#usersTable tbody tr');
    rows.forEach(row => {
      const userStatus = row.querySelector('.badge').classList.contains(`badge-${status}`)
        ? status : row.querySelector('.badge').classList.contains('badge-active') ? 'active' : 'pending';
      if (status === 'all') {
        row.style.display = '';
      } else {
        row.style.display = userStatus === status ? '' : 'none';
      }
    });
  }

  /**
   * Initialize user list
   */
  function initUserList() {
    console.log('User list initialized');
  }

  // =======================
  // Action Handlers
  // =======================

  /**
   * Update bulk selection count display
   */
  function updateBulkSelectionCount(element) {
    if (element) {
      element.textContent = `${selectedUsers.size} selected`;
      element.style.display = selectedUsers.size > 0 ? 'block' : 'none';
    }
  }

  /**
   * Update bulk action button state
   */
  function updateBulkActionState(element) {
    if (element) {
      element.disabled = selectedUsers.size === 0;
    }
  }

  /**
   * Handle bulk action
   */
  function handleBulkAction(action, users) {
    console.log(`Bulk action: ${action} on users:`, users);

    switch (action) {
      case 'delete':
        if (confirm(`Are you sure you want to delete ${users.size} users?`)) {
          showToast(`Deleted ${users.size} users`, 'success');
          users.forEach(u => console.log('Deleted user:', u));
        }
        break;
      case 'ban':
        if (confirm(`Are you sure you want to ban ${users.size} users?`)) {
          showToast(`Banned ${users.size} users`, 'warning');
          users.forEach(u => console.log('Banned user:', u));
        }
        break;
      case 'activate':
        showToast(`Activated ${users.size} users`, 'success');
        users.forEach(u => console.log('Activated user:', u));
        break;
    }

    selectedUsers.clear();
    updateBulkSelectionCount(document.getElementById('bulkSelectionCount'));
    updateBulkActionState(document.getElementById('applyBulkAction'));
    document.getElementById('selectAll').checked = false;
  }

  /**
   * Create new user
   */
  window.createUser = function() {
    console.log('Creating user...');
    closeModal('addUserModal');
    showToast('User created successfully', 'success');
  };

  /**
   * Edit user
   */
  window.editUser = function(userId) {
    console.log('Editing user:', userId);
    showModal('editUserModal');
  };

  /**
   * Save user changes
   */
  window.saveUser = function() {
    console.log('Saving user changes...');
    closeModal('editUserModal');
    showToast('User updated successfully', 'success');
  };

  /**
   * View user details
   */
  window.viewUser = function(userId) {
    console.log('Viewing user:', userId);
    showModal('userDetailsModal');
  };

  /**
   * Ban user
   */
  window.banUser = function() {
    console.log('Banning user...');
    closeModal('userDetailsModal');
    showToast('User banned successfully', 'warning');
  };

  /**
   * Export users to CSV
   */
  window.exportUsers = function() {
    console.log('Exporting users...');
    showToast('Exporting users to CSV...', 'info');
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
