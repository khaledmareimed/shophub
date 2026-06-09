/**
 * Usage: UploadWidget.send(productId, fileInput.files[0])
 */
(function (w) {
  'use strict';
  w.UploadWidget = {
    send: async function (productId, file) {
      const fd = new FormData();
      fd.append('file', file);
      const base = (w.SHOPHUB_API || '').replace(/\/$/, '');
      const auth = apiClient.getAuth();
      const headers = {};
      if (auth && auth.access_token) headers['Authorization'] = 'Bearer ' + auth.access_token;
      const res = await fetch(base + '/api/v1/seller/products/' + productId + '/images', {
        method: 'POST',
        headers: headers,
        body: fd
      });
      return res.json();
    }
  };
})(window);
