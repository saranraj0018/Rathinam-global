(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initPwToggles();
        var login = document.getElementById('login-form');
        var register = document.getElementById('register-form');
        if (login) wire(login, validateLogin);
        if (register) wire(register, validateRegister);
    });

    function initPwToggles() {
        document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = btn.parentNode.querySelector('input');
                if (!input) return;
                var reveal = input.type === 'password';
                input.type = reveal ? 'text' : 'password';
                btn.textContent = reveal ? 'Hide' : 'Show';
            });
        });
    }

    // Front-end validation only. Backend devs: set the form action/route and
    // replace the success branch below with the real request.
    function wire(form, validator) {
        form.addEventListener('input', function (e) { clearError(e.target); });
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var errors = validator(form);
            if (Object.keys(errors).length) {
                showErrors(form, errors);
                toast('Please fix the highlighted fields.', 'error');
                return;
            }
            toast('Looks good — ready to submit.', 'success');
        });
    }

    function value(form, name) {
        var el = form.querySelector('[name="' + name + '"]');
        return el ? el.value.trim() : '';
    }
    function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }

    function validateLogin(form) {
        var e = {};
        var email = value(form, 'email');
        if (!email) e.email = 'Email is required.';
        else if (!isEmail(email)) e.email = 'Enter a valid email address.';
        if (!value(form, 'password')) e.password = 'Password is required.';
        return e;
    }

    function validateRegister(form) {
        var e = {};
        var name = value(form, 'name');
        if (!name) e.name = 'Name is required.';
        else if (name.length < 2) e.name = 'Name is too short.';
        else if (!/^[a-zA-Z .'-]+$/.test(name)) e.name = 'Use letters only.';

        var phone = value(form, 'phone').replace(/[\s\-]/g, '');
        if (!phone) e.phone = 'Phone number is required.';
        else if (!/^(\+?91)?[6-9]\d{9}$/.test(phone)) e.phone = 'Enter a valid 10-digit mobile number.';

        var email = value(form, 'email');
        if (!email) e.email = 'Email is required.';
        else if (!isEmail(email)) e.email = 'Enter a valid email address.';

        var pw = value(form, 'password');
        if (!pw) e.password = 'Password is required.';
        else if (pw.length < 8) e.password = 'Use at least 8 characters.';
        else if (!/[A-Za-z]/.test(pw) || !/\d/.test(pw)) e.password = 'Include both letters and numbers.';

        var confirm = value(form, 'password_confirmation');
        if (!confirm) e.password_confirmation = 'Please re-enter your password.';
        else if (confirm !== pw) e.password_confirmation = 'Passwords do not match.';

        return e;
    }

    function showErrors(form, errors) {
        form.querySelectorAll('.f-error').forEach(function (p) { p.textContent = ''; p.classList.remove('show'); });
        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        var first = null;
        Object.keys(errors).forEach(function (name) {
            var input = form.querySelector('[name="' + name + '"]');
            if (input) { input.classList.add('is-invalid'); if (!first) first = input; }
            var p = form.querySelector('.f-error[data-error-for="' + name + '"]');
            if (p) { p.textContent = errors[name]; p.classList.add('show'); }
        });
        if (first) first.focus();
    }

    function clearError(el) {
        if (!el || !el.name) return;
        el.classList.remove('is-invalid');
        var group = el.closest('.f-group');
        var p = group ? group.querySelector('.f-error') : null;
        if (p) { p.textContent = ''; p.classList.remove('show'); }
    }

    function toast(message, type) { if (window.rguToast) window.rguToast(message, type); }
})();
