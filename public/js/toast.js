(function () {
    'use strict';
    function ensureWrap() {
        var w = document.querySelector('.toast-wrap');
        if (!w) { w = document.createElement('div'); w.className = 'toast-wrap'; document.body.appendChild(w); }
        return w;
    }
    window.rguToast = function (message, type) {
        var wrap = ensureWrap();
        var t = document.createElement('div');
        t.className = 'toast toast--' + (type || 'info');
        t.setAttribute('role', 'alert');
        var icon = type === 'error' ? '⚠' : (type === 'success' ? '✓' : 'ℹ');
        t.innerHTML = '<span class="toast__icon"></span><span class="toast__msg"></span><button class="toast__x" type="button" aria-label="Dismiss">&times;</button>';
        t.querySelector('.toast__icon').textContent = icon;
        t.querySelector('.toast__msg').textContent = message;
        wrap.appendChild(t);
        void t.offsetWidth; // force reflow so the slide-in transition always runs
        t.classList.add('show');
        var timer = setTimeout(close, 4500);
        function close() { clearTimeout(timer); t.classList.remove('show'); setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 300); }
        t.querySelector('.toast__x').addEventListener('click', close);
    };
})();
