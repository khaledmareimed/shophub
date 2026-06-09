/**
 * ShopHub API client — Bearer JWT, refresh on 401.
 * Set window.SHOPHUB_API = '' for same-origin, or e.g. 'http://localhost/backend/public'
 */
(function (global) {
  'use strict';

  const STORAGE = 'shophub_auth';

  function base() {
    const b = (global.SHOPHUB_API || '').replace(/\/$/, '');
    return b;
  }

  function getAuth() {
    try { return JSON.parse(localStorage.getItem(STORAGE) || 'null'); } catch { return null; }
  }

  function setAuth(o) {
    if (o) localStorage.setItem(STORAGE, JSON.stringify(o));
    else localStorage.removeItem(STORAGE);
  }

  async function refreshTokens() {
    const a = getAuth();
    if (!a || !a.refresh_token) return null;
    const res = await fetch(base() + '/api/v1/auth/refresh', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ refresh_token: a.refresh_token }),
    });
    const j = await res.json().catch(() => ({}));
    if (!res.ok || !j.ok) return null;
    const d = j.data;
    const next = {
      ...a,
      access_token: d.access_token,
      refresh_token: d.refresh_token,
    };
    setAuth(next);
    return d.access_token;
  }

  async function request(method, path, body, retry) {
    const url = base() + (path.startsWith('/') ? path : '/' + path);
    const headers = { 'Accept': 'application/json' };
    const a = getAuth();
    if (a && a.access_token) headers['Authorization'] = 'Bearer ' + a.access_token;
    if (body !== undefined && body !== null && !(body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
    }
    const init = { method, headers, body: body instanceof FormData ? body : body !== undefined ? JSON.stringify(body) : undefined };
    let res = await fetch(url, init);
    if (res.status === 401 && !retry && a && a.refresh_token) {
      const tok = await refreshTokens();
      if (tok) return request(method, path, body, true);
    }
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch { json = { ok: false, error: { message: text } }; }
    if (!res.ok) {
      const msg = (json.error && json.error.message) || res.statusText;
      const err = new Error(msg);
      err.status = res.status;
      err.payload = json;
      throw err;
    }
    return json;
  }

  global.apiClient = {
    getAuth,
    setAuth,
    get: (p) => request('GET', p),
    post: (p, b) => request('POST', p, b),
    patch: (p, b) => request('PATCH', p, b),
    delete: (p, b) => request('DELETE', p, b),
    upload: (path, formData) => request('POST', path, formData),
  };
})(window);
