/**
 * Optional: call AdminApi.loadUsers() from admin users page after auth.
 */
(function (w) {
  'use strict';
  w.AdminApi = {
    loadUsers: async function (q) {
      if (typeof apiClient === 'undefined') return;
      const qs = new URLSearchParams(q || {}).toString();
      return apiClient.get('/api/v1/admin/users' + (qs ? '?' + qs : ''));
    },
    loadProducts: function (status) {
      const s = status ? '?status=' + encodeURIComponent(status) : '';
      return apiClient.get('/api/v1/admin/products' + s);
    }
  };
})(window);
