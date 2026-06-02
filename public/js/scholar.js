(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initNavbar();

        var form = document.getElementById('scholar-form');
        if (!form) return;

        var steps = Array.prototype.slice.call(form.querySelectorAll('.wizard-step'));

        initCascade(form);
        initUploads(form);
        initEduUploads(form);
        initReveals(form);
        initRepeatables(form);
        initLanguages(form);
        initServiceCalc(form);
        initAge(form);
        initSignature(form);
        initLiveErrorClearing(form);
        initWizard(form, steps);
    });

    function initNavbar() {
        var nav = document.getElementById('navbar');
        if (nav) {
            var onScroll = function () { nav.classList.toggle('scrolled', window.scrollY > 20); };
            window.addEventListener('scroll', onScroll); onScroll();
        }
        var toggle = document.getElementById('mobile-menu-toggle');
        var menu = document.getElementById('mobile-menu');
        var iMenu = document.getElementById('icon-menu');
        var iClose = document.getElementById('icon-close');
        if (toggle && menu) {
            toggle.addEventListener('click', function () {
                var open = menu.classList.toggle('open');
                toggle.setAttribute('aria-expanded', open);
                if (iMenu) iMenu.style.display = open ? 'none' : '';
                if (iClose) iClose.style.display = open ? '' : 'none';
            });
            menu.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', function () { menu.classList.remove('open'); });
            });
        }
    }

    // School -> Discipline -> Specialization cascade
    function initCascade(form) {
        var dataEl = document.getElementById('annexure-data');
        var schools = [];
        try { schools = JSON.parse(dataEl.textContent); } catch (e) { schools = []; }

        var schoolSel = form.querySelector('[data-cascade="school"]');
        var discSel = form.querySelector('[data-cascade="discipline"]');
        var specSel = form.querySelector('[data-cascade="specialization"]');
        var specHint = form.querySelector('[data-spec-hint]');
        var otherWrap = form.querySelector('[data-spec-other-wrap]');
        var otherInput = document.getElementById('specialization_other');
        if (!schoolSel || !discSel || !specSel) return;

        var OTHER = 'Other (please specify)';

        function fill(sel, items, placeholder) {
            sel.innerHTML = '';
            var o = document.createElement('option');
            o.value = ''; o.disabled = true; o.selected = true; o.textContent = placeholder;
            sel.appendChild(o);
            items.forEach(function (it) {
                var opt = document.createElement('option');
                opt.value = it; opt.textContent = it; sel.appendChild(opt);
            });
        }
        function showOther(show) {
            otherWrap.hidden = !show;
            otherInput.required = show;
            if (!show) otherInput.value = '';
        }

        schoolSel.addEventListener('change', function () {
            var s = schools.filter(function (x) { return x.name === schoolSel.value; })[0];
            discSel.disabled = false;
            fill(discSel, s ? s.disciplines.map(function (d) { return d.name; }) : [], '— Select a Discipline —');
            specSel.disabled = true;
            fill(specSel, [], '— Select a Discipline first —');
            if (specHint) specHint.hidden = true;
            showOther(false);
        });

        discSel.addEventListener('change', function () {
            var s = schools.filter(function (x) { return x.name === schoolSel.value; })[0];
            var d = s ? s.disciplines.filter(function (x) { return x.name === discSel.value; })[0] : null;
            var specs = d ? d.specializations.slice() : [];
            specSel.disabled = false;
            fill(specSel, specs.concat([OTHER]), '— Select Specialization —');
            if (specs.length === 0) {
                if (specHint) specHint.hidden = false;
                specSel.value = OTHER;
                showOther(true);
            } else {
                if (specHint) specHint.hidden = true;
                showOther(false);
            }
        });

        specSel.addEventListener('change', function () {
            showOther(specSel.value === OTHER);
        });
    }

    // Full dropzone uploads (.js-upload component)
    function initUploads(form) {
        form.querySelectorAll('.js-upload').forEach(function (group) {
            var input = group.querySelector('.js-upload-input');
            var zone = group.querySelector('[data-zone]');
            var fileBox = group.querySelector('[data-file]');
            var thumb = group.querySelector('[data-thumb]');
            var docIcon = group.querySelector('[data-doc]');
            var nameEl = group.querySelector('[data-name]');
            var sizeEl = group.querySelector('[data-size]');
            var removeBtn = group.querySelector('[data-remove]');
            var err = group.querySelector('.f-error');
            var maxKb = parseInt(group.getAttribute('data-max-kb') || '2048', 10);
            var isImage = group.getAttribute('data-image') === '1';

            function clearErr() { if (err) { err.textContent = ''; err.classList.remove('show'); } group.classList.remove('is-invalid'); }
            function showErr(m) { if (err) { err.textContent = m; err.classList.add('show'); } }
            function reset() {
                input.value = '';
                group.classList.remove('has-file');
                fileBox.hidden = true;
                thumb.hidden = true; thumb.removeAttribute('src');
                docIcon.hidden = true;
            }

            input.addEventListener('change', function () {
                var f = input.files && input.files[0];
                if (!f) { reset(); return; }
                var okType = isImage ? /\.(png|jpe?g)$/i.test(f.name) : /\.(pdf|png|jpe?g)$/i.test(f.name);
                if (!okType) {
                    showErr(isImage ? 'Only JPG, JPEG or PNG images are allowed.' : 'Only PDF, JPG, JPEG or PNG files are allowed.');
                    reset(); return;
                }
                if (f.size > maxKb * 1024) {
                    showErr('“' + f.name + '” is ' + formatSize(f.size) + '. Maximum allowed is ' + (maxKb / 1024) + ' MB.');
                    reset(); return;
                }
                clearErr();
                group.classList.add('has-file');
                fileBox.hidden = false;
                nameEl.textContent = f.name;
                sizeEl.textContent = formatSize(f.size);
                if (/\.(png|jpe?g)$/i.test(f.name)) {
                    thumb.src = URL.createObjectURL(f); thumb.hidden = false; docIcon.hidden = true;
                } else {
                    docIcon.hidden = false; thumb.hidden = true;
                }
            });

            removeBtn.addEventListener('click', function () { reset(); clearErr(); });

            ['dragenter', 'dragover'].forEach(function (ev) {
                zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('is-drag'); });
            });
            ['dragleave', 'drop'].forEach(function (ev) {
                zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('is-drag'); });
            });
            zone.addEventListener('drop', function (e) {
                if (e.dataTransfer && e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                }
            });
        });
    }

    // Per-row education mark-sheet uploads (unlock when the row is fully filled)
    function initEduUploads(form) {
        form.querySelectorAll('[data-edu-row]').forEach(function (row) {
            var wrap = row.querySelector('[data-edu-upload]');
            if (!wrap) return;
            var input = wrap.querySelector('.edu-up__input');
            var fileBox = wrap.querySelector('.edu-up__file');
            var nameEl = wrap.querySelector('.edu-up__name');
            var removeBtn = wrap.querySelector('.edu-up__remove');
            var err = row.querySelector('.f-error');
            var fields = row.querySelectorAll('.edu-field');

            function complete() { return Array.prototype.every.call(fields, function (f) { return f.value.trim() !== ''; }); }
            function clearErr() { if (err) { err.textContent = ''; err.classList.remove('show'); } wrap.classList.remove('is-invalid'); }
            function showErr(m) { if (err) { err.textContent = m; err.classList.add('show'); } wrap.classList.add('is-invalid'); }
            function resetFile() { input.value = ''; fileBox.hidden = true; nameEl.textContent = ''; }
            function sync() {
                var ok = complete();
                input.disabled = !ok;
                wrap.classList.toggle('is-locked', !ok);
                if (!ok) { resetFile(); clearErr(); }
            }

            fields.forEach(function (f) { f.addEventListener('input', sync); });
            input.addEventListener('change', function () {
                var f = input.files && input.files[0];
                if (!f) { resetFile(); return; }
                if (!/\.(pdf|png|jpe?g)$/i.test(f.name)) { showErr('Only PDF, JPG, JPEG or PNG files are allowed.'); resetFile(); return; }
                if (f.size > 2048 * 1024) { showErr('“' + f.name + '” is ' + formatSize(f.size) + '. Maximum allowed is 2 MB.'); resetFile(); return; }
                clearErr(); fileBox.hidden = false; nameEl.textContent = f.name;
            });
            removeBtn.addEventListener('click', function () { resetFile(); clearErr(); });
            sync();
        });
    }

    // Conditional reveals
    function initReveals(form) {
        form.querySelectorAll('[data-reveal-group]').forEach(function (group) {
            group.querySelectorAll('input[type="radio"]').forEach(function (r) {
                r.addEventListener('change', function () { updateGroupReveals(group); });
            });
        });
        form.querySelectorAll('input[type="checkbox"][data-reveal-target]').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var t = document.querySelector(cb.getAttribute('data-reveal-target'));
                if (t) t.hidden = !cb.checked;
            });
        });
        var community = document.getElementById('community');
        var ccWrap = document.getElementById('community-cert-wrap');
        if (community && ccWrap) {
            community.addEventListener('change', function () { ccWrap.hidden = !community.value; });
        }
        var same = document.getElementById('address_same');
        var cur = document.getElementById('address_current');
        var perm = document.getElementById('address_permanent');
        if (same && cur && perm) {
            var sync = function () {
                if (same.checked) { perm.value = cur.value; perm.readOnly = true; }
                else { perm.readOnly = false; }
            };
            same.addEventListener('change', sync);
            cur.addEventListener('input', function () { if (same.checked) perm.value = cur.value; });
        }
        form.querySelectorAll('input[name="programme_mode"]').forEach(function (r) {
            r.addEventListener('change', function () { updatePtOnly(form); });
        });
    }
    function updateGroupReveals(group) {
        group.querySelectorAll('input[type="radio"][data-reveal-target]').forEach(function (r) {
            var t = document.querySelector(r.getAttribute('data-reveal-target'));
            if (t) t.hidden = !r.checked;
        });
    }
    function updatePtOnly(form) {
        var mode = form.querySelector('input[name="programme_mode"]:checked');
        var isPT = mode && mode.value === 'PT';
        form.querySelectorAll('[data-pt-only]').forEach(function (el) { el.hidden = !isPT; });
    }

    // Repeatable rows (service, projects, languages)
    function initRepeatables(form) {
        form.querySelectorAll('[data-repeat]').forEach(function (container) {
            var body = container.querySelector('[data-repeat-body]');
            var tpl = container.querySelector('[data-repeat-template]');
            var addBtn = container.querySelector('[data-repeat-add]');
            var idx = body.querySelectorAll('[data-repeat-row]').length;

            function renumber() {
                body.querySelectorAll('[data-repeat-row]').forEach(function (row, i) {
                    var n = row.querySelector('[data-repeat-num]');
                    if (n) n.textContent = i + 1;
                });
            }
            addBtn.addEventListener('click', function () {
                var html = tpl.innerHTML.replace(/__INDEX__/g, idx++);
                var tmp = document.createElement('div');
                tmp.innerHTML = html.trim();
                body.appendChild(tmp.firstElementChild);
                renumber();
            });
            body.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-repeat-remove]');
                if (!btn) return;
                var rows = body.querySelectorAll('[data-repeat-row]');
                if (rows.length <= 1) {
                    btn.closest('[data-repeat-row]').querySelectorAll('input, select').forEach(function (i) { i.value = ''; });
                    return;
                }
                btn.closest('[data-repeat-row]').remove();
                renumber();
            });
            renumber();
        });
    }

    // Languages: skills are selectable only after a language name is entered
    function initLanguages(form) {
        var table = form.querySelector('[data-repeat="languages"]');
        if (!table) return;
        function sync(row) {
            if (!row) return;
            var name = row.querySelector('[data-lang-name]');
            var has = name && name.value.trim().length > 0;
            row.querySelectorAll('.skill-chip input').forEach(function (cb) {
                cb.disabled = !has;
                if (!has) cb.checked = false;
            });
            row.classList.toggle('lang-row--locked', !has);
        }
        function syncAll() { table.querySelectorAll('.lang-row:not(.lang-row--head)').forEach(sync); }
        table.addEventListener('input', function (e) { if (e.target.matches('[data-lang-name]')) sync(e.target.closest('.lang-row')); });
        table.addEventListener('click', function (e) { if (e.target.closest('[data-repeat-add]')) setTimeout(syncAll, 0); });
        syncAll();
    }

    // Service: auto totals (per row "XY YM" + overall years/months)
    function initServiceCalc(form) {
        var table = form.querySelector('[data-service-calc]');
        if (!table) return;
        var yEl = form.querySelector('[data-svc-total-years]');
        var mEl = form.querySelector('[data-svc-total-months]');

        function diffMonths(from, to) {
            if (!from) return null;
            var f = from.split('-'), t = (to || currentYM()).split('-');
            if (f.length < 2 || t.length < 2) return null;
            var d = (parseInt(t[0], 10) - parseInt(f[0], 10)) * 12 + (parseInt(t[1], 10) - parseInt(f[1], 10));
            return d >= 0 ? d : null;
        }
        function fmt(m) { return Math.floor(m / 12) + 'Y ' + (m % 12) + 'M'; }
        function recalc() {
            var total = 0;
            table.querySelectorAll('[data-repeat-row]').forEach(function (row) {
                var fr = row.querySelector('[data-svc-from]');
                var to = row.querySelector('[data-svc-to]');
                var tt = row.querySelector('[data-svc-total]');
                var m = diffMonths(fr ? fr.value : '', to ? to.value : '');
                if (m == null) { if (tt) tt.value = ''; }
                else { if (tt) tt.value = fmt(m); total += m; }
            });
            if (yEl) yEl.value = Math.floor(total / 12);
            if (mEl) mEl.value = total % 12;
        }
        table.addEventListener('input', function (e) { if (e.target.matches('[data-svc-from], [data-svc-to]')) recalc(); });
        table.addEventListener('click', function (e) { if (e.target.closest('[data-repeat-add], [data-repeat-remove]')) setTimeout(recalc, 0); });
        recalc();
    }

    function initAge(form) {
        var dob = document.getElementById('dob');
        var age = document.getElementById('age');
        if (!dob || !age) return;
        age.readOnly = true;
        dob.addEventListener('change', function () {
            if (!dob.value) { age.value = ''; return; }
            var b = new Date(dob.value), n = new Date();
            var a = n.getFullYear() - b.getFullYear();
            var m = n.getMonth() - b.getMonth();
            if (m < 0 || (m === 0 && n.getDate() < b.getDate())) a--;
            age.value = a >= 0 ? String(a) : '';
        });
    }

    function initSignature(form) {
        var inp = form.querySelector('[data-signature-input]');
        var prev = form.querySelector('[data-signature-preview]');
        if (!inp || !prev) return;
        inp.addEventListener('input', function () { prev.textContent = inp.value; });
    }

    function initLiveErrorClearing(form) {
        form.addEventListener('input', function (e) { clearFieldError(e.target); });
        form.addEventListener('change', function (e) { clearFieldError(e.target); });
    }
    function clearFieldError(el) {
        if (!el || !el.name) return;
        el.classList.remove('is-invalid');
        var up = el.closest('.js-upload'); if (up) up.classList.remove('is-invalid');
        var eu = el.closest('[data-edu-upload]'); if (eu) eu.classList.remove('is-invalid');
        var grid = el.closest('[data-validate-radio], [data-validate-checkbox]');
        if (grid) grid.classList.remove('is-invalid');
        var name = el.name.replace('[]', '');
        var err = document.querySelector('.f-error[data-error-for="' + name + '"]');
        if (err) { err.textContent = ''; err.classList.remove('show'); }
    }

    function initWizard(form, steps) {
        var prevBtn = form.querySelector('[data-prev]');
        var nextBtn = form.querySelector('[data-next]');
        var submitBtn = form.querySelector('[data-submit]');
        var tabs = document.querySelectorAll('[data-step-tab]');
        var bar = document.querySelector('[data-progress-bar]');
        var curEl = document.querySelector('[data-step-current]');
        var nameEl = document.querySelector('[data-step-name]');
        var current = 0;

        function labelOf(i) { return tabs[i] ? tabs[i].querySelector('.stepper__label').textContent : ''; }
        function show(i) {
            steps.forEach(function (s, k) { s.hidden = k !== i; });
            current = i;
            prevBtn.hidden = i === 0;
            var last = i === steps.length - 1;
            nextBtn.hidden = last;
            submitBtn.hidden = !last;
            tabs.forEach(function (t, k) {
                t.classList.toggle('is-active', k === i);
                t.classList.toggle('is-done', k < i);
            });
            if (bar) bar.style.width = ((i + 1) / steps.length * 100) + '%';
            if (curEl) curEl.textContent = i + 1;
            if (nameEl) nameEl.textContent = labelOf(i);
            if (steps[i].getAttribute('data-step-id') === 'preview') renderPreview(form);
            var card = document.querySelector('.wizard-card');
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        nextBtn.addEventListener('click', function () {
            if (validateStep(steps[current])) show(Math.min(current + 1, steps.length - 1));
        });
        prevBtn.addEventListener('click', function () { show(Math.max(current - 1, 0)); });
        tabs.forEach(function (t, k) { t.addEventListener('click', function () { if (k < current) show(k); }); });

        var printBtn = form.querySelector('[data-print]');
        if (printBtn) printBtn.addEventListener('click', function () { window.print(); });

        form.addEventListener('submit', function (e) {
            for (var k = 0; k < steps.length - 1; k++) {
                if (!validateStep(steps[k])) { e.preventDefault(); show(k); return; }
            }
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting…';
        });

        show(0);
    }

    function isActive(el, stepEl) {
        if (el.disabled) return false;
        var n = el;
        while (n && n !== stepEl) { if (n.hidden) return false; n = n.parentElement; }
        return true;
    }
    function setError(stepEl, name, msg) {
        var err = stepEl.querySelector('.f-error[data-error-for="' + name + '"]');
        if (err) { err.textContent = msg; err.classList.add('show'); }
    }
    function clearStepErrors(stepEl) {
        stepEl.querySelectorAll('.f-error').forEach(function (e) { e.textContent = ''; e.classList.remove('show'); });
        stepEl.querySelectorAll('.is-invalid').forEach(function (e) { e.classList.remove('is-invalid'); });
    }
    function validateStep(stepEl) {
        clearStepErrors(stepEl);
        var ok = true, firstBad = null;

        stepEl.querySelectorAll('[data-validate-radio]').forEach(function (g) {
            if (!isActive(g, stepEl)) return;
            var name = g.getAttribute('data-validate-radio');
            if (!stepEl.querySelector('input[name="' + name + '"]:checked')) {
                setError(stepEl, name, 'Please select an option.');
                g.classList.add('is-invalid'); ok = false; firstBad = firstBad || g;
            }
        });
        stepEl.querySelectorAll('[data-validate-checkbox]').forEach(function (g) {
            if (!isActive(g, stepEl)) return;
            var name = g.getAttribute('data-validate-checkbox');
            if (!stepEl.querySelector('input[name="' + name + '[]"]:checked, input[name="' + name + '"]:checked')) {
                setError(stepEl, name, 'Please select at least one option.');
                g.classList.add('is-invalid'); ok = false; firstBad = firstBad || g;
            }
        });
        stepEl.querySelectorAll('input, select, textarea').forEach(function (f) {
            if (f.type === 'radio') return;
            if (f.type === 'checkbox') {
                if (f.required && isActive(f, stepEl) && !f.checked) {
                    setError(stepEl, f.name.replace('[]', ''), 'This is required.');
                    ok = false; firstBad = firstBad || f;
                }
                return;
            }
            var required = f.required || f.getAttribute('data-required') === 'true';
            if (!required || !isActive(f, stepEl)) return;

            var val = (f.value || '').trim();
            if (!val) { markInvalid(stepEl, f, 'This field is required.'); ok = false; firstBad = firstBad || f; return; }
            if (f.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                markInvalid(stepEl, f, 'Enter a valid e-mail address.'); ok = false; firstBad = firstBad || f;
            }
            if (f.type === 'tel' && !/^(\+?91[\-\s]?)?[6-9]\d{9}$/.test(val.replace(/[\s\-]/g, ''))) {
                markInvalid(stepEl, f, 'Enter a valid 10-digit mobile number.'); ok = false; firstBad = firstBad || f;
            }
        });

        if (firstBad) {
            var focusEl = firstBad.focus ? firstBad : firstBad.querySelector('input, select, textarea');
            try { (focusEl || firstBad).scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
        }
        return ok;
    }
    function markInvalid(stepEl, f, msg) {
        f.classList.add('is-invalid');
        var up = f.closest('.js-upload'); if (up) up.classList.add('is-invalid');
        var eu = f.closest('[data-edu-upload]'); if (eu) eu.classList.add('is-invalid');
        setError(stepEl, f.name.replace('[]', ''), msg);
    }

    // Live document-style preview
    function renderPreview(form) {
        var body = document.querySelector('[data-preview-body]');
        if (!body) return;

        function val(name) {
            var nodes = form.querySelectorAll('[name="' + name + '"]');
            if (!nodes.length) return '';
            var first = nodes[0];
            if (first.type === 'radio') { var c = form.querySelector('[name="' + name + '"]:checked'); return c ? c.value : ''; }
            if (first.type === 'checkbox') { return first.checked ? (first.value === '1' ? 'Yes' : first.value) : ''; }
            return first.value || '';
        }
        function radioLabel(name) {
            var c = form.querySelector('[name="' + name + '"]:checked');
            if (!c) return '';
            var card = c.closest('.choice-card');
            return card ? card.querySelector('.choice-card__title').textContent.trim() : c.value;
        }
        function fileName(name) {
            var inp = form.querySelector('input[type="file"][name="' + name + '"]');
            return (inp && inp.files && inp.files[0]) ? inp.files[0].name : '';
        }
        function item(label, value, full) {
            var empty = (value === undefined || value === null || value === '');
            return '<div class="pv-item' + (full ? ' pv-item--full' : '') + '">' +
                '<span class="pv-item__label">' + esc(label) + '</span>' +
                '<span class="pv-item__value' + (empty ? ' is-empty' : '') + '">' + (empty ? '—' : esc(value)) + '</span></div>';
        }
        function section(title, inner) { return '<div class="pv-section"><h3 class="pv-section__title">' + esc(title) + '</h3>' + inner + '</div>'; }
        function grid(items) { return '<div class="pv-grid">' + items.join('') + '</div>'; }

        var html = '';

        var photo = form.querySelector('input[type="file"][name="photo"]');
        if (photo && photo.files && photo.files[0]) {
            html += '<img class="pv-photo" src="' + URL.createObjectURL(photo.files[0]) + '" alt="photo">';
        }

        var spec = val('specialization');
        if (spec === 'Other (please specify)') spec = val('specialization_other');
        html += section('Programme & School', grid([
            item('School', val('school')),
            item('Discipline', val('discipline')),
            item('Specialization', spec),
            item('Programme Mode', radioLabel('programme_mode'))
        ]));

        var gender = radioLabel('gender');
        if (val('single_girl_child') === 'Yes') gender += ' · Single Girl Child';
        html += section('Personal Details', grid([
            item('Full Name', val('full_name'), true),
            item('Date of Birth', val('dob')),
            item('Age', val('age')),
            item('Gender', gender),
            item('Nationality', val('nationality')),
            item('Religion', val('religion')),
            item('Community', val('community')),
            item('Differently Abled', radioLabel('differently_abled')),
            item('Father / Guardian', val('father_name')),
            item('Mother', val('mother_name')),
            item('Mobile', val('mobile')),
            item('E-mail', val('email')),
            item('Languages Known', languageSummary(form), true),
            item('Current Address', val('address_current'), true),
            item('Permanent Address', val('address_permanent'), true)
        ]));

        html += section('Educational Qualification', eduTable(form));

        var eligItems = [item('Qualified in National/State exam', radioLabel('eligibility_qualified'))];
        if (val('eligibility_qualified') === 'Yes') eligItems.push(item('Examination', val('eligibility_exam')));
        else if (val('eligibility_qualified') === 'No') eligItems.push(item('Note', 'Must clear the RGU Common Eligibility Test (CET)'));
        html += section('Eligibility', grid(eligItems) + filesList([['Qualifying score copy', fileName('eligibility_cert')]]));

        var svc = serviceTable(form);
        html += section('Academic, Research & Industry Service',
            (svc || '<p class="pv-item__value is-empty">No experience entered.</p>') +
            '<p style="margin-top:8px;font-size:.84rem">Total service: <strong>' + esc(val('total_service_years') || '0') +
            '</strong> years <strong>' + esc(val('total_service_months') || '0') + '</strong> months</p>');

        html += section('Projects, Courses & Aspirations',
            projectsTable(form) + grid([
                item('Research Methodology completed', radioLabel('course_research_methodology')),
                item('Research & Publication Ethics completed', radioLabel('course_publication_ethics')),
                item('Career Aspirations', aspirations(form), true)
            ]) + filesList([['One-page focus summary', fileName('summary_document')]]));

        html += section('Enclosures', enclosuresList(form) + filesList([
            ['NOC (Part-Time)', fileName('noc_document')],
            ['Service Certificate (Part-Time)', fileName('service_certificate')],
            ['Community Certificate', fileName('community_cert')],
            ['Disability Certificate', fileName('disability_cert')],
            ['Equivalence Certificate', fileName('equivalence_cert')]
        ]));

        html += section('Declaration', grid([
            item('Date', val('declaration_date')),
            item('Station', val('declaration_station'))
        ]) + '<div style="margin-top:12px"><span class="pv-item__label">Signature of the Applicant</span>' +
            '<div class="pv-sign">' + esc(val('declaration_signature')) + '</div></div>');

        body.innerHTML = html;
    }

    function languageSummary(form) {
        var out = [];
        form.querySelectorAll('.lang-row:not(.lang-row--head)').forEach(function (row) {
            var nm = row.querySelector('[data-lang-name]');
            if (nm && nm.value.trim()) {
                var skills = Array.prototype.map.call(row.querySelectorAll('.skill-chip input:checked'), function (c) { return c.value; });
                out.push(nm.value.trim() + (skills.length ? ' (' + skills.join('/') + ')' : ''));
            }
        });
        return out.join(', ');
    }
    function eduTable(form) {
        var rows = '';
        var step = form.querySelector('[data-step-id="education"]');
        if (step) step.querySelectorAll('.edu-row').forEach(function (r) {
            var label = (r.querySelector('.edu-row__label') || {}).textContent || '';
            var fields = r.querySelectorAll('.edu-field');
            var vals = Array.prototype.map.call(fields, function (i) { return i.value.trim(); });
            var fileInp = r.querySelector('.edu-up__input');
            var fileNm = (fileInp && fileInp.files && fileInp.files[0]) ? fileInp.files[0].name : '';
            if (vals.some(Boolean) || fileNm) {
                rows += '<tr><td>' + esc(label.replace('*', '').trim()) + '</td><td>' + esc(vals[0] || '') +
                    '</td><td>' + esc(vals[1] || '') + '</td><td>' + esc(vals[2] || '') + '</td><td>' + esc(vals[3] || '') +
                    '</td><td>' + (fileNm ? esc(fileNm) : '—') + '</td></tr>';
            }
        });
        if (!rows) return '<p class="pv-item__value is-empty">No qualifications entered.</p>';
        return '<table class="pv-table"><thead><tr><th>Degree</th><th>Subjects</th><th>Institution</th><th>Month/Year</th><th>Marks</th><th>Mark sheet</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }
    function serviceTable(form) {
        var rows = '';
        var step = form.querySelector('[data-repeat="service"]');
        if (step) step.querySelectorAll('[data-repeat-row]').forEach(function (r) {
            var ins = r.querySelectorAll('input');
            var vals = Array.prototype.map.call(ins, function (i) { return i.value.trim(); });
            if (vals.slice(0, 4).some(Boolean)) {
                rows += '<tr><td>' + esc(vals[0]) + '</td><td>' + esc(vals[1]) + '</td><td>' + esc(vals[2]) +
                    '</td><td>' + esc(vals[3]) + '</td><td>' + esc(vals[4]) + '</td></tr>';
            }
        });
        if (!rows) return '';
        return '<table class="pv-table"><thead><tr><th>Designation</th><th>Institution</th><th>From</th><th>To</th><th>Total</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }
    function projectsTable(form) {
        var rows = '';
        var step = form.querySelector('[data-repeat="projects"]');
        if (step) step.querySelectorAll('[data-repeat-row]').forEach(function (r) {
            var title = (r.querySelector('input[type="text"]') || {}).value || '';
            var status = (r.querySelector('select') || {}).value || '';
            if (title.trim()) rows += '<tr><td>' + esc(title) + '</td><td>' + esc(status) + '</td></tr>';
        });
        if (!rows) return '';
        return '<table class="pv-table" style="margin-bottom:12px"><thead><tr><th>Project / Start-up / Event</th><th>Outcome</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }
    function aspirations(form) {
        var arr = Array.prototype.map.call(form.querySelectorAll('input[name="career_aspirations[]"]:checked'), function (c) { return c.value; });
        var other = (form.querySelector('[name="career_other"]') || {}).value || '';
        if (other) arr = arr.filter(function (a) { return a !== 'Any other'; }).concat('Any other: ' + other);
        return arr.join(', ');
    }
    function enclosuresList(form) {
        var items = [];
        form.querySelectorAll('.encl-list__item').forEach(function (li) {
            if (li.hidden) return;
            var cb = li.querySelector('input[type="checkbox"]');
            if (cb && cb.checked) {
                var span = li.querySelector('.inline-check span');
                items.push(span ? span.textContent.trim() : '');
            }
        });
        if (!items.length) return '<p class="pv-item__value is-empty">No enclosures ticked.</p>';
        return '<ul class="pv-files">' + items.map(function (i) { return '<li>' + esc(i) + '</li>'; }).join('') + '</ul>';
    }
    function filesList(pairs) {
        var rows = pairs.filter(function (p) { return p[1]; });
        if (!rows.length) return '';
        return '<ul class="pv-files" style="margin-top:12px">' +
            rows.map(function (p) { return '<li>' + esc(p[0]) + ': <strong>' + esc(p[1]) + '</strong></li>'; }).join('') + '</ul>';
    }

    function formatSize(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b / 1024).toFixed(0) + ' KB';
        return (b / 1048576).toFixed(2) + ' MB';
    }
    function currentYM() { var d = new Date(); return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2); }
    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
})();
