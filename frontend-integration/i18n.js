(function (w) {
  'use strict';
  var DICT = {
    en: { lang: 'en', dir: 'ltr', 'nav.signin': 'Sign In' },
    ar: { lang: 'ar', dir: 'rtl', 'nav.signin': 'تسجيل الدخول' }
  };
  w.I18n = {
    locale: 'en',
    t: function (k) {
      var d = DICT[w.I18n.locale] || DICT.en;
      return d[k] || k;
    },
    setLocale: function (lang) {
      if (!DICT[lang]) lang = 'en';
      w.I18n.locale = lang;
      document.documentElement.lang = DICT[lang].lang;
      document.documentElement.dir = DICT[lang].dir;
      try { localStorage.setItem('shophub_locale', lang); } catch (e) {}
    }
  };
  var saved = null;
  try { saved = localStorage.getItem('shophub_locale'); } catch (e) {}
  if (saved) w.I18n.setLocale(saved);
})(window);
