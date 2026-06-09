(function (w) {
  'use strict';
  w.SellerApi = {
    products: function (status) {
      const s = status && status !== 'all' ? '?status=' + encodeURIComponent(status) : '';
      return apiClient.get('/api/v1/seller/products' + s);
    },
    orders: function () {
      return apiClient.get('/api/v1/seller/orders');
    }
  };
})(window);
