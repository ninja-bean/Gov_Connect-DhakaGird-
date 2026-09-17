/* ==========================================================================
   DhakaGrid Design System — Core JS (assets/app/core.js)
   No alert()/confirm() anywhere: toasts + Promise-based modal confirm.
   Exposes window.DG for pages and declarative data-dg-* attributes.
   Spec: DESIGN.md §5 (modal/toast), §6 (motion), §7 (a11y).
   ========================================================================== */

(function (global) {
  'use strict';

  var REDUCED_MOTION = matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ------------------------------ Toasts ------------------------------ */

  var toastContainer = null;

  function ensureToastContainer() {
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'toasts';
      toastContainer.setAttribute('aria-live', 'polite');
      document.body.appendChild(toastContainer);
    }
    return toastContainer;
  }

  /**
   * @param {string} message
   * @param {'success'|'error'|'info'} [type]
   * @param {{duration?: number}} [options] duration=0 keeps the toast open.
   */
  function toast(message, type, options) {
    type = type || 'info';
    options = options || {};

    var el = document.createElement('div');
    el.className = 'toast toast-' + type;
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');

    var msg = document.createElement('span');
    msg.className = 'toast-msg';
    msg.textContent = message;

    var close = document.createElement('button');
    close.className = 'toast-x';
    close.setAttribute('aria-label', 'Dismiss notification');
    close.textContent = '\u00d7';
    close.addEventListener('click', function () { dismiss(el); });

    el.appendChild(msg);
    el.appendChild(close);
    ensureToastContainer().appendChild(el);

    var duration = typeof options.duration === 'number' ? options.duration : (type === 'error' ? 0 : 4000);
    if (duration > 0) {
      setTimeout(function () { dismiss(el); }, duration);
    }
    return el;
  }

  function dismiss(el) {
    if (!el.parentNode) return;
    el.parentNode.removeChild(el);
  }

  /* ------------------------------ Layers ------------------------------ */

  var activeLayers = [];

  function trapFocus(container) {
    var focusables = Array.prototype.slice.call(
      container.querySelectorAll('a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])')
    ).filter(function (n) { return n.offsetParent !== null; });

    if (focusables.length === 0) return;

    var first = focusables[0];
    var last = focusables[focusables.length - 1];

    container.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab') return;
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });
  }

  function topLayer() {
    return activeLayers[activeLayers.length - 1];
  }

  /**
   * Open a modal/drawer element (must be in the DOM).
   * @param {HTMLElement} el
   * @param {HTMLElement} [returnFocus]
   */
  function openLayer(el, returnFocus) {
    el.classList.add('open');
    el.hidden = false;
    el.setAttribute('aria-hidden', 'false');

    var entry = { el: el, returnFocus: returnFocus || document.activeElement };
    activeLayers.push(entry);

    trapFocus(el);

    var focusTarget = el.querySelector('[data-dg-autofocus], input, button, [tabindex]');
    if (focusTarget) focusTarget.focus();
    else el.focus();
  }

  function closeTopLayer() {
    var entry = activeLayers.pop();
    if (!entry) return;

    entry.el.classList.remove('open');
    entry.el.hidden = true;
    entry.el.setAttribute('aria-hidden', 'true');

    if (entry.returnFocus && entry.returnFocus.focus) {
      entry.returnFocus.focus();
    }
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && activeLayers.length > 0) {
      var layer = topLayer().el;
      var cancel = layer.querySelector('[data-dg-cancel]');
      if (cancel) cancel.click();
      else closeTopLayer();
    }
  });

  /* --------------------------- Promise confirm --------------------------- */

  /**
   * Replace window.confirm: resolves true/false via a themed modal.
   * @param {string} message
   * @param {{confirmLabel?: string, danger?: boolean, title?: string}} [options]
   * @returns {Promise<boolean>}
   */
  function confirmDialog(message, options) {
    options = options || {};

    return new Promise(function (resolve) {
      var backdrop = document.createElement('div');
      backdrop.className = 'modal-backdrop';
      backdrop.setAttribute('role', 'dialog');
      backdrop.setAttribute('aria-modal', 'true');
      backdrop.setAttribute('aria-labelledby', 'dg-confirm-title');

      var modal = document.createElement('div');
      modal.className = 'modal';
      modal.hidden = false;

      var title = document.createElement('h2');
      title.id = 'dg-confirm-title';
      title.className = 'modal-title';
      title.textContent = options.title || 'Are you sure?';

      var body = document.createElement('p');
      body.className = 'modal-body';
      body.textContent = message;

      var actions = document.createElement('div');
      actions.className = 'modal-actions';

      var cancel = document.createElement('button');
      cancel.className = 'btn btn-outline';
      cancel.type = 'button';
      cancel.textContent = 'Cancel';
      cancel.setAttribute('data-dg-cancel', '');

      var ok = document.createElement('button');
      ok.className = 'btn ' + (options.danger === false ? 'btn-primary' : 'btn-danger');
      ok.type = 'button';
      ok.textContent = options.confirmLabel || 'Confirm';

      actions.appendChild(cancel);
      actions.appendChild(ok);

      modal.appendChild(title);
      modal.appendChild(body);
      modal.appendChild(actions);
      backdrop.appendChild(modal);
      document.body.appendChild(backdrop);

      var done = function (value) {
        document.body.removeChild(backdrop);
        resolve(value);
      };
      cancel.addEventListener('click', function () { done(false); });
      ok.addEventListener('click', function () { done(true); });

      openLayer(modal, document.activeElement);
    });
  }

  /* ----------------------------- JSON helper ----------------------------- */

  /**
   * POST FormData/object expecting JSON, bodies errors into toasts.
   * @param {string} url
   * @param {FormData|object} data
   * @returns {Promise<any>}
   */
  function postJSON(url, data) {
    var body = data instanceof FormData ? data : objectToForm(data);
    return fetch(url, { method: 'POST', body: body })
      .then(function (res) { return res.json(); })
      .catch(function () {
        toast('Network error. Please try again.', 'error');
        throw new Error('network');
      });
  }

  function objectToForm(obj) {
    var form = new FormData();
    Object.keys(obj).forEach(function (k) { form.append(k, obj[k]); });
    return form;
  }

  /* ------------------------- Mobile drawer wiring ------------------------- */

  function wireDeclarative() {
    // [data-dg-toast="message"][data-dg-toast-type="success"]
    document.querySelectorAll('[data-dg-toast]').forEach(function (el) {
      el.addEventListener('click', function () {
        toast(el.getAttribute('data-dg-toast'), el.getAttribute('data-dg-toast-type') || 'info');
      });
    });

    // [data-dg-confirm="message"] on forms/links: intercepts submit/click.
    document.querySelectorAll('[data-dg-confirm]').forEach(function (el) {
      if (el.tagName === 'FORM') {
        el.addEventListener('submit', function (e) {
          e.preventDefault();
          confirmDialog(el.getAttribute('data-dg-confirm'), { danger: el.getAttribute('data-dg-danger') !== 'false' })
            .then(function (ok) { if (ok) el.submit(); });
        });
      } else {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          confirmDialog(el.getAttribute('data-dg-confirm')).then(function (ok) { if (ok) { window.location = el.getAttribute('href'); } });
        });
      }
    });

    // Drawer: mobile sidebar toggle.
    var sidebar = document.querySelector('.sidebar.floating');
    var backdrop = document.querySelector('.drawer-backdrop');

    document.querySelectorAll('[data-dg-drawer-open]').forEach(function (el) {
      el.addEventListener('click', function () {
        if (sidebar) { sidebar.classList.add('open'); activeLayers.push({ el: sidebar, returnFocus: el }); }
        if (backdrop) backdrop.classList.add('open');
      });
    });

    if (sidebar && backdrop) {
      var closeDrawer = function () {
        sidebar.classList.remove('open');
        backdrop.classList.remove('open');
        var idx = activeLayers.findIndex(function (l) { return l.el === sidebar; });
        if (idx !== -1) activeLayers.splice(idx, 1);
      };
      backdrop.addEventListener('click', closeDrawer);
      sidebar.querySelectorAll('[data-dg-drawer-close]').forEach(function (el) {
        el.addEventListener('click', closeDrawer);
      });
    }
  }

  /* ------------------------------ Exports ------------------------------ */

  global.DG = {
    toast: toast,
    dismissToast: dismiss,
    confirm: confirmDialog,
    postJSON: postJSON,
    modal: { open: function (el, rf) { openLayer(el, rf || null); }, close: closeTopLayer },
    closedOnReducedMotion: REDUCED_MOTION,
    _wire: wireDeclarative
  };

  document.addEventListener('DOMContentLoaded', wireDeclarative);
})(window);