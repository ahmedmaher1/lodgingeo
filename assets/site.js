/* Lodgingeo: small progressive enhancements. Every form works without this file. */
(function () {
  'use strict';
  var ss;
  try { ss = window.sessionStorage; } catch (e) { ss = null; }
  var get = function (k) { try { return ss ? ss.getItem('lg_' + k) || '' : ''; } catch (e) { return ''; } };
  var set = function (k, v) { try { if (ss && v) ss.setItem('lg_' + k, v); } catch (e) {} };

  // Persist UTM tags, landing page and external referrer for the session.
  var q = new URLSearchParams(location.search);
  ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term'].forEach(function (k) {
    if (q.get(k)) set(k, q.get(k).slice(0, 200));
  });
  if (!get('landing_page')) set('landing_page', (location.pathname + location.search).slice(0, 500));
  if (!get('referrer') && document.referrer && document.referrer.indexOf(location.host) === -1) set('referrer', document.referrer.slice(0, 500));
  document.querySelectorAll('input[data-persist]').forEach(function (el) {
    if (!el.value) el.value = get(el.getAttribute('data-persist'));
  });

  // Data dictionary filter.
  var f = document.querySelector('input[data-filter]');
  if (f) {
    f.closest('.filter').hidden = false;
    var rows = document.querySelectorAll(f.getAttribute('data-filter') + ' tbody tr');
    f.addEventListener('input', function () {
      var t = f.value.trim().toLowerCase();
      rows.forEach(function (r) { r.hidden = t !== '' && r.textContent.toLowerCase().indexOf(t) === -1; });
    });
  }

  // Optional GA4, loaded only after consent.
  var c = document.getElementById('consent');
  if (c) {
    var id = c.getAttribute('data-ga'), choice = '';
    try { choice = localStorage.getItem('lg_consent') || ''; } catch (e) {}
    var load = function () {
      var s = document.createElement('script');
      s.async = true; s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
      document.head.appendChild(s);
      window.dataLayer = window.dataLayer || [];
      window.gtag = function () { window.dataLayer.push(arguments); };
      window.gtag('js', new Date()); window.gtag('config', id, { anonymize_ip: true });
    };
    if (choice === 'yes') load(); else if (choice !== 'no') c.hidden = false;
    c.addEventListener('click', function (e) {
      var v = e.target.getAttribute && e.target.getAttribute('data-consent');
      if (!v) return;
      try { localStorage.setItem('lg_consent', v); } catch (err) {}
      c.hidden = true;
      if (v === 'yes') load();
    });
  }
})();
