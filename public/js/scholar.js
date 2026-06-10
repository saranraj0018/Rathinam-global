/**
 * scholar.js — Ph.D. application wizard + draft persistence + payment gate.
 *
 * Flow:
 *   • On load        : GET /apply/draft → re-hydrate form → jump to last saved step.
 *   • Save & Continue: validate step → POST /apply/step/{id} → advance.
 *   • Declaration    : validate → POST /apply/initiate-payment → redirect to Cashfree.
 *   • After payment  : server sets payment_status=paid, current_step=preview → redirect back.
 *   • Re-login       : hydrate reads current_step=preview, payment_status=paid → unlocks preview.
 *   • Submit         : only allowed when payment_status=paid → POST /apply/submit.
 *
 * Requires: <meta name="csrf-token">, sendRequest(url, fd, method, onOk, onFail), showToast(msg, type, ms).
 */
(function () {
    "use strict";

    /* ─────────────────────────── constants ─────────────────────────── */

    var STEP_IDS = [
        "programme",
        "personal",
        "education",
        "eligibility",
        "experience",
        "research",
        "enclosures",
        "declaration",
        "preview",
    ];
    var STEP_DATA = [
        "programme",
        "personal",
        "education",
        "eligibility",
        "experience",
        "research",
        "enclosures",
        "declaration",
    ];

    // Cascade fields are handled ONLY by hydrateCascade(); the generic scalar
    // loop in applyDraft() must skip them so it can't assign a value to a
    // <select> whose <option> hasn't been built yet.
    var CASCADE_KEYS = [
        "school",
        "discipline",
        "specialization",
        "specialization_other",
    ];

    /* ══════════════════════════════════════════════════════════════════
       BOOT
    ══════════════════════════════════════════════════════════════════ */
    document.addEventListener("DOMContentLoaded", function () {
        initNavbar();
        initProfileMenu();

        var form = document.getElementById("scholar-form");
        if (!form) return;

        var steps = Array.prototype.slice.call(
            form.querySelectorAll(".wizard-step"),
        );

        initCascade(form);
        initUploads(form);
        initEduUploads(form);
        initEduSavedFiles(form);
        initReveals(form);
        initRepeatables(form);
        initLanguages(form);
        initServiceCalc(form);
        initEnclosures(form);
        initAge(form);
        initSignature(form);
        initEngineeringStream(form);
        initLiveErrorClearing(form);
        initWizard(form, steps);
        initPayNow(form);
        // re-fill from saved draft after wizard is ready
        hydrateDraft(form);
    });

    /* ══════════════════════════════════════════════════════════════════
       NAVBAR / PROFILE
    ══════════════════════════════════════════════════════════════════ */
    function initProfileMenu() {
        var toggle = document.querySelector("[data-profile-toggle]");
        var menu = document.querySelector("[data-profile-menu]");
        if (!toggle || !menu) return;

        function close() {
            menu.hidden = true;
            toggle.setAttribute("aria-expanded", "false");
        }

        toggle.addEventListener("click", function (e) {
            e.stopPropagation();
            var willOpen = menu.hidden;
            menu.hidden = !willOpen;
            toggle.setAttribute("aria-expanded", String(willOpen));
        });
        document.addEventListener("click", function (e) {
            if (
                !menu.hidden &&
                !menu.contains(e.target) &&
                !toggle.contains(e.target)
            )
                close();
        });
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") close();
        });
    }

    function initNavbar() {
        var nav = document.getElementById("navbar");
        if (nav) {
            var onScroll = function () {
                nav.classList.toggle("scrolled", window.scrollY > 20);
            };
            window.addEventListener("scroll", onScroll);
            onScroll();
        }
        var toggle = document.getElementById("mobile-menu-toggle");
        var menu = document.getElementById("mobile-menu");
        var iMenu = document.getElementById("icon-menu");
        var iClose = document.getElementById("icon-close");
        if (toggle && menu) {
            toggle.addEventListener("click", function () {
                var open = menu.classList.toggle("open");
                toggle.setAttribute("aria-expanded", open);
                if (iMenu) iMenu.style.display = open ? "none" : "";
                if (iClose) iClose.style.display = open ? "" : "none";
            });
            menu.querySelectorAll("a").forEach(function (a) {
                a.addEventListener("click", function () {
                    menu.classList.remove("open");
                });
            });
        }
    }

    /* ══════════════════════════════════════════════════════════════════
       ENCLOSURES SYNC
    ══════════════════════════════════════════════════════════════════ */
    function loadSavedFiles() {
        var el = document.getElementById("saved-files-data");
        if (el) {
            try {
                var parsed = JSON.parse(el.textContent || "{}");
                if (parsed && typeof parsed === "object") return parsed;
            } catch (e) {
                console.warn(
                    "[enclosures] saved-files-data is not valid JSON:",
                    e,
                );
            }
        }
        if (window.__DRAFT__ && window.__DRAFT__.files) {
            return window.__DRAFT__.files;
        }
        return {};
    }

    function initEnclosures(form) {
        var items = Array.prototype.slice.call(
            form.querySelectorAll("[data-encl-source]"),
        );
        if (!items.length) return;

        var savedFiles = loadSavedFiles();
        var savedKeys = Object.keys(savedFiles);

        function entryHasUrl(entry) {
            if (!entry) return false;
            if (typeof entry === "string") return entry.length > 0;
            return !!(entry.url || entry.path || entry.file_path);
        }

        function hasSaved(name) {
            if (!name) return false;
            if (entryHasUrl(savedFiles[name])) return true;
            if (entryHasUrl(savedFiles[name + "_certificate"])) return true;
            if (name.indexOf("_certificate") !== -1) {
                var base = name.replace("_certificate", "");
                if (entryHasUrl(savedFiles[base])) return true;
            }
            for (var i = 0; i < savedKeys.length; i++) {
                var k = savedKeys[i];
                if (
                    (k === name ||
                        k.indexOf(name + "_") === 0 ||
                        name.indexOf(k + "_") === 0) &&
                    entryHasUrl(savedFiles[k])
                ) {
                    return true;
                }
            }
            return false;
        }

        function sync() {
            items.forEach(function (li) {
                var name = li.getAttribute("data-encl-source");
                var input = form.querySelector(
                    'input[type="file"][name="' + name + '"]',
                );
                var freshlyPicked = !!(
                    input &&
                    input.files &&
                    input.files.length
                );
                var has = freshlyPicked || hasSaved(name);

                var cb = li.querySelector("[data-encl-auto]");
                var status = li.querySelector("[data-encl-status]");
                if (cb) cb.checked = has;
                if (status) {
                    status.textContent = has ? "✓ Uploaded" : "Upload pending";
                    status.className =
                        "encl-status " + (has ? "is-ok" : "is-pending");
                }
            });
        }

        form.addEventListener("change", function (e) {
            if (e.target && e.target.type === "file") sync();
        });
        form._syncEnclosures = sync;
        sync();
    }

    /* ══════════════════════════════════════════════════════════════════
       CASCADE (school → discipline → specialization)
    ══════════════════════════════════════════════════════════════════ */
    function initCascade(form) {
        var dataEl = document.getElementById("annexure-data");
        var schools = [];
        try {
            schools = JSON.parse(dataEl.textContent);
        } catch (e) {
            schools = [];
        }
        if (!schools || !schools.length) {
            console.warn(
                "[cascade] #annexure-data is empty — school/discipline/specialization dropdowns will have no options, so saved values cannot be reselected. Populate annexureData() in the controller.",
            );
        }

        var schoolSel = form.querySelector('[data-cascade="school"]');
        var discSel = form.querySelector('[data-cascade="discipline"]');
        var specSel = form.querySelector('[data-cascade="specialization"]');
        var specHint = form.querySelector("[data-spec-hint]");
        var otherWrap = form.querySelector("[data-spec-other-wrap]");
        var otherInput = document.getElementById("specialization_other");
        if (!schoolSel || !discSel || !specSel) return;

        var OTHER = "Other (please specify)";

        function fill(sel, items, placeholder) {
            sel.innerHTML = "";
            var o = document.createElement("option");
            o.value = "";
            o.disabled = true;
            o.selected = true;
            o.textContent = placeholder;
            sel.appendChild(o);
            items.forEach(function (it) {
                var opt = document.createElement("option");
                opt.value = it;
                opt.textContent = it;
                sel.appendChild(opt);
            });
        }

        function showOther(show) {
            if (!otherWrap) return;
            otherWrap.hidden = !show;
            if (otherInput) otherInput.required = show;
            if (!show && otherInput) otherInput.value = "";
        }

        schoolSel.addEventListener("change", function () {
            var s = schools.filter(function (x) {
                return x.name === schoolSel.value;
            })[0];
            discSel.disabled = false;
            fill(
                discSel,
                s
                    ? s.disciplines.map(function (d) {
                          return d.name;
                      })
                    : [],
                "— Select a Discipline —",
            );
            specSel.disabled = true;
            fill(specSel, [], "— Select a Discipline first —");
            if (specHint) specHint.hidden = true;
            showOther(false);
        });

        discSel.addEventListener("change", function () {
            var s = schools.filter(function (x) {
                return x.name === schoolSel.value;
            })[0];
            var d = s
                ? s.disciplines.filter(function (x) {
                      return x.name === discSel.value;
                  })[0]
                : null;
            var specs = d ? d.specializations.slice() : [];
            specSel.disabled = false;
            fill(specSel, specs.concat([OTHER]), "— Select Specialization —");
            if (specs.length === 0) {
                if (specHint) specHint.hidden = false;
                specSel.value = OTHER;
                showOther(true);
            } else {
                if (specHint) specHint.hidden = true;
                showOther(false);
            }
        });

        specSel.addEventListener("change", function () {
            showOther(specSel.value === OTHER);
        });
    }

    /* ══════════════════════════════════════════════════════════════════
       UPLOADS
    ══════════════════════════════════════════════════════════════════ */
    function initUploads(form) {
        form.querySelectorAll(".js-upload").forEach(function (group) {
            var input = group.querySelector(".js-upload-input");
            var zone = group.querySelector("[data-zone]");
            var fileBox = group.querySelector("[data-file]");
            var thumb = group.querySelector("[data-thumb]");
            var docIcon = group.querySelector("[data-doc]");
            var nameEl = group.querySelector("[data-name]");
            var sizeEl = group.querySelector("[data-size]");
            var removeBtn = group.querySelector("[data-remove]");
            var err = group.querySelector(".f-error");
            var maxKb = parseInt(
                group.getAttribute("data-max-kb") || "2048",
                10,
            );
            var isImage = group.getAttribute("data-image") === "1";

            function clearErr() {
                if (err) {
                    err.textContent = "";
                    err.classList.remove("show");
                }
                group.classList.remove("is-invalid");
            }
            function showErr(m) {
                if (err) {
                    err.textContent = m;
                    err.classList.add("show");
                }
                group.classList.add("is-invalid");
                toast(m, "error");
            }
            function reset() {
                input.value = "";
                group.classList.remove("has-file");
                if (fileBox) fileBox.hidden = true;
                if (thumb) {
                    thumb.hidden = true;
                    thumb.removeAttribute("src");
                }
                if (docIcon) docIcon.hidden = true;
            }

            if (!input) return;

            input.addEventListener("change", function () {
                var f = input.files && input.files[0];
                if (!f) {
                    reset();
                    return;
                }
                var okType = isImage
                    ? /\.(png|jpe?g)$/i.test(f.name)
                    : /\.(pdf|png|jpe?g)$/i.test(f.name);
                if (!okType) {
                    showErr(
                        isImage
                            ? "Only JPG, JPEG or PNG images are allowed."
                            : "Only PDF, JPG, JPEG or PNG files are allowed.",
                    );
                    reset();
                    return;
                }
                if (f.size > maxKb * 1024) {
                    showErr(
                        '"' +
                            f.name +
                            '" is ' +
                            formatSize(f.size) +
                            ". Maximum allowed is " +
                            maxKb / 1024 +
                            " MB.",
                    );
                    reset();
                    return;
                }
                clearErr();
                group.classList.add("has-file");
                if (fileBox) fileBox.hidden = false;
                if (nameEl) nameEl.textContent = f.name;
                if (sizeEl) sizeEl.textContent = formatSize(f.size);
                if (/\.(png|jpe?g)$/i.test(f.name)) {
                    if (thumb) {
                        thumb.src = URL.createObjectURL(f);
                        thumb.hidden = false;
                    }
                    if (docIcon) docIcon.hidden = true;
                } else {
                    if (docIcon) docIcon.hidden = false;
                    if (thumb) thumb.hidden = true;
                }
            });

            if (removeBtn)
                removeBtn.addEventListener("click", function () {
                    reset();
                    clearErr();
                });

            if (zone) {
                ["dragenter", "dragover"].forEach(function (ev) {
                    zone.addEventListener(ev, function (e) {
                        e.preventDefault();
                        zone.classList.add("is-drag");
                    });
                });
                ["dragleave", "drop"].forEach(function (ev) {
                    zone.addEventListener(ev, function (e) {
                        e.preventDefault();
                        zone.classList.remove("is-drag");
                    });
                });
                zone.addEventListener("drop", function (e) {
                    if (e.dataTransfer && e.dataTransfer.files.length) {
                        input.files = e.dataTransfer.files;
                        input.dispatchEvent(new Event("change"));
                    }
                });
            }
        });
    }

    /* ══════════════════════════════════════════════════════════════════
       ENGINEERING STREAM → PROGRAMME MODE FILTER
    ══════════════════════════════════════════════════════════════════ */
    function initEngineeringStream(form) {
        var ENG_ALLOWED_MODES = [
            "FT",
            "PT",
            "FT-Startup",
         ];

        var engRadios = form.querySelectorAll("[data-eng-stream]");
        var modeCards = Array.prototype.slice.call(
            form.querySelectorAll("[data-mode-card]"),
        );
        if (!engRadios.length || !modeCards.length) return;

        function isAllowedWhenEng(value) {
            return ENG_ALLOWED_MODES.indexOf(value) !== -1;
        }

        function applyFilter() {
            var sel = form.querySelector('input[name="engineering_stream"]:checked',);
            var answered = !!sel;
            var isEng = sel && sel.value === "Yes";

            modeCards.forEach(function (card) {
                var value = card.getAttribute("data-mode-value");
                var radio = card.querySelector('input[name="programme_mode"]');
                var allowed = answered && (!isEng || isAllowedWhenEng(value));
                card.hidden = !allowed;
                if (radio) {
                    radio.disabled = !allowed;
                    if (!allowed && radio.checked) radio.checked = false;
                }
            });

            updatePtOnly(form);
        }

        engRadios.forEach(function (r) {
            r.addEventListener("change", applyFilter);
        });

        // Expose so hydrate can re-apply after restoring saved values.
        form._applyEngFilter = applyFilter;

        applyFilter();
    }

    function initEduUploads(form) {
        form.querySelectorAll("[data-edu-row]").forEach(function (row) {
            var wrap = row.querySelector("[data-edu-upload]");
            if (!wrap) return;

            var input = wrap.querySelector(".edu-up__input");
            var fileBox = wrap.querySelector(".edu-up__file");
            var nameEl = wrap.querySelector(".edu-up__name");
            var removeBtn = wrap.querySelector(".edu-up__remove");
            var err = row.querySelector(".f-error");
            var fields = row.querySelectorAll(".edu-field");

            // The hidden flag that tells the server to delete a saved marksheet.
            var removedFlag = wrap.querySelector("[data-removed-flag]");

            function allFilled() {
                return Array.prototype.every.call(fields, function (f) {
                    return f.value.trim() !== "";
                });
            }
            function anyFilled() {
                return Array.prototype.some.call(fields, function (f) {
                    return f.value.trim() !== "";
                });
            }

            function hasLiveSavedFile() {
                if (wrap.getAttribute("data-has-saved") !== "1") return false;
                if (removedFlag && removedFlag.value === "1") return false;
                return true;
            }

            function clearErr() {
                if (err) {
                    err.textContent = "";
                    err.classList.remove("show");
                }
                wrap.classList.remove("is-invalid");
            }
            function showErr(m) {
                if (err) {
                    err.textContent = m;
                    err.classList.add("show");
                }
                wrap.classList.add("is-invalid");
                toast(m, "error");
            }
            function resetFile() {
                if (input) input.value = "";
                if (fileBox) fileBox.hidden = true;
                if (nameEl) nameEl.textContent = "";
            }

            function sync() {
                var any = anyFilled();

                // Enable the input the moment the user starts the row, NOT only
                // when every field is complete — otherwise validateStep() skips
                // it (disabled inputs are treated as inactive) and the "required"
                // error can never show.
                if (input) input.disabled = !any;
                wrap.classList.toggle("is-locked", !any);

                if (!any) {
                    resetFile();
                    clearErr();
                }

                // Decide whether the marksheet is required for this row.
                if (input) {
                    var needsFile = any && !hasLiveSavedFile();
                    if (needsFile) {
                        input.setAttribute("data-required", "true");
                    } else {
                        input.removeAttribute("data-required");
                    }
                }
            }

            fields.forEach(function (f) {
                f.addEventListener("input", sync);
                f.addEventListener("change", sync);
            });

            if (input) {
                input.addEventListener("change", function () {
                    var f = input.files && input.files[0];
                    if (!f) {
                        resetFile();
                        return;
                    }
                    if (!/\.(pdf|png|jpe?g)$/i.test(f.name)) {
                        showErr(
                            "Only PDF, JPG, JPEG or PNG files are allowed.",
                        );
                        resetFile();
                        return;
                    }
                    if (f.size > 2048 * 1024) {
                        showErr(
                            '"' +
                                f.name +
                                '" is ' +
                                formatSize(f.size) +
                                ". Maximum allowed is 2 MB.",
                        );
                        resetFile();
                        return;
                    }
                    clearErr();
                    if (fileBox) fileBox.hidden = false;
                    if (nameEl) nameEl.textContent = f.name;
                    // A freshly picked file satisfies the requirement; clear the flag.
                    input.removeAttribute("data-required");
                });
            }

            if (removeBtn)
                removeBtn.addEventListener("click", function () {
                    resetFile();
                    clearErr();
                    // Re-evaluate: removing a freshly picked file may re-require it.
                    sync();
                });

            sync();
        });
    }

    function initEduSavedFiles(form) {
        form.querySelectorAll("[data-removed-flag]").forEach(function (flag) {
            var docType = flag.getAttribute("data-removed-flag");
            var wrap = flag.closest("[data-edu-upload]");
            var input = wrap ? wrap.querySelector(".edu-up__input") : null;

            var removeBtn = form.querySelector(
                '[data-remove-saved="' + docType + '"]',
            );
            if (!removeBtn) return;

            removeBtn.addEventListener("click", function () {
                flag.value = "1";
                var box = document.getElementById("saved-" + docType);
                if (box) box.hidden = true;
                if (wrap) {
                    wrap.removeAttribute("data-has-saved");
                    if (
                        input &&
                        wrap.getAttribute("data-was-required") === "1"
                    ) {
                        input.setAttribute("data-required", "true");
                    }
                    var anyField = wrap
                        .closest("[data-edu-row]")
                        .querySelector(".edu-field");
                    if (anyField) {
                        anyField.dispatchEvent(
                            new Event("input", { bubbles: true }),
                        );
                    }
                }
            });
        });
    }

    /* ══════════════════════════════════════════════════════════════════
       REVEALS
    ══════════════════════════════════════════════════════════════════ */
    function initReveals(form) {
        form.querySelectorAll("[data-reveal-group]").forEach(function (group) {
            group.querySelectorAll('input[type="radio"]').forEach(function (r) {
                r.addEventListener("change", function () {
                    updateGroupReveals(group);
                });
            });
        });

        form.querySelectorAll(
            'input[type="checkbox"][data-reveal-target]',
        ).forEach(function (cb) {
            cb.addEventListener("change", function () {
                var t = document.querySelector(
                    cb.getAttribute("data-reveal-target"),
                );
                if (t) t.hidden = !cb.checked;
            });
        });

        var community = document.getElementById("community");
        var ccWrap = document.getElementById("community-cert-wrap");
        if (community && ccWrap) {
            community.addEventListener("change", function () {
                ccWrap.hidden = !community.value;
            });
        }

        var same = document.getElementById("address_same");
        var cur = document.getElementById("address_current");
        var perm = document.getElementById("address_permanent");
        if (same && cur && perm) {
            var syncAddr = function () {
                if (same.checked) {
                    perm.value = cur.value;
                    perm.readOnly = true;
                } else {
                    perm.readOnly = false;
                }
            };
            same.addEventListener("change", syncAddr);
            cur.addEventListener("input", function () {
                if (same.checked) perm.value = cur.value;
            });
        }

        form.querySelectorAll('input[name="programme_mode"]').forEach(
            function (r) {
                r.addEventListener("change", function () {
                    updatePtOnly(form);
                });
            },
        );
    }

    function updateGroupReveals(group) {
        group
            .querySelectorAll('input[type="radio"][data-reveal-target]')
            .forEach(function (r) {
                var t = document.querySelector(
                    r.getAttribute("data-reveal-target"),
                );
                if (t) t.hidden = !r.checked;
            });
    }

    function updatePtOnly(form) {
        var mode = form.querySelector('input[name="programme_mode"]:checked');
        var isPT = mode && (mode.value === "PT" || mode.value === "part_time");
        form.querySelectorAll("[data-pt-only]").forEach(function (el) {
            el.hidden = !isPT;
        });
    }

    /* ══════════════════════════════════════════════════════════════════
       REPEATABLES
    ══════════════════════════════════════════════════════════════════ */
    function initRepeatables(form) {
        form.querySelectorAll("[data-repeat]").forEach(function (container) {
            var body = container.querySelector("[data-repeat-body]");
            var tpl = container.querySelector("[data-repeat-template]");
            var addBtn = container.querySelector("[data-repeat-add]");
            if (!body || !tpl || !addBtn) return;

            var idx = body.querySelectorAll("[data-repeat-row]").length;

            function renumber() {
                body.querySelectorAll("[data-repeat-row]").forEach(
                    function (row, i) {
                        var n = row.querySelector("[data-repeat-num]");
                        if (n) n.textContent = i + 1;
                    },
                );
            }

            addBtn.addEventListener("click", function () {
                var html = tpl.innerHTML.replace(/__INDEX__/g, idx++);
                var tmp = document.createElement("div");
                tmp.innerHTML = html.trim();
                body.appendChild(tmp.firstElementChild);
                renumber();
            });

            body.addEventListener("click", function (e) {
                var btn = e.target.closest("[data-repeat-remove]");
                if (!btn) return;
                var rows = body.querySelectorAll("[data-repeat-row]");
                if (rows.length <= 1) {
                    btn.closest("[data-repeat-row]")
                        .querySelectorAll("input, select")
                        .forEach(function (i) {
                            i.value = "";
                        });
                    return;
                }
                btn.closest("[data-repeat-row]").remove();
                renumber();
            });

            renumber();
        });
    }

    function initLanguages(form) {
        var table = form.querySelector('[data-repeat="languages"]');
        if (!table) return;

        function sync(row) {
            if (!row) return;
            var name = row.querySelector("[data-lang-name]");
            var has = name && name.value.trim().length > 0;
            row.querySelectorAll(".skill-chip input").forEach(function (cb) {
                cb.disabled = !has;
                if (!has) cb.checked = false;
            });
            row.classList.toggle("lang-row--locked", !has);
        }

        function syncAll() {
            table
                .querySelectorAll(".lang-row:not(.lang-row--head)")
                .forEach(sync);
        }

        table.addEventListener("input", function (e) {
            if (e.target.matches("[data-lang-name]"))
                sync(e.target.closest(".lang-row"));
        });
        table.addEventListener("click", function (e) {
            if (e.target.closest("[data-repeat-add]")) setTimeout(syncAll, 0);
        });
        syncAll();
    }

    /* ══════════════════════════════════════════════════════════════════
       SERVICE CALC
    ══════════════════════════════════════════════════════════════════ */
    function initServiceCalc(form) {
        var table = form.querySelector("[data-service-calc]");
        if (!table) return;
        var yEl = form.querySelector("[data-svc-total-years]");
        var mEl = form.querySelector("[data-svc-total-months]");

        function diffMonths(from, to) {
            if (!from) return null;
            var f = from.split("-"),
                t = (to || currentYM()).split("-");
            if (f.length < 2 || t.length < 2) return null;
            var d =
                (parseInt(t[0], 10) - parseInt(f[0], 10)) * 12 +
                (parseInt(t[1], 10) - parseInt(f[1], 10));
            return d >= 0 ? d : null;
        }
        function fmt(m) {
            return Math.floor(m / 12) + "Y " + (m % 12) + "M";
        }

        function recalc() {
            var total = 0;
            table.querySelectorAll("[data-repeat-row]").forEach(function (row) {
                var fr = row.querySelector("[data-svc-from]");
                var to = row.querySelector("[data-svc-to]");
                var tt = row.querySelector("[data-svc-total]");
                var m = diffMonths(fr ? fr.value : "", to ? to.value : "");
                if (m == null) {
                    if (tt) tt.value = "";
                } else {
                    if (tt) tt.value = fmt(m);
                    total += m;
                }
            });
            if (yEl) yEl.value = Math.floor(total / 12);
            if (mEl) mEl.value = total % 12;
        }

        table.addEventListener("input", function (e) {
            if (e.target.matches("[data-svc-from], [data-svc-to]")) recalc();
        });
        table.addEventListener("click", function (e) {
            if (e.target.closest("[data-repeat-add], [data-repeat-remove]"))
                setTimeout(recalc, 0);
        });
        recalc();
    }

    /* ══════════════════════════════════════════════════════════════════
       MISC INITS
    ══════════════════════════════════════════════════════════════════ */
    function initAge(form) {
        var dob = document.getElementById("dob");
        var age = document.getElementById("age");
        if (!dob || !age) return;
        age.readOnly = true;
        dob.addEventListener("change", function () {
            if (!dob.value) {
                age.value = "";
                return;
            }
            var b = new Date(dob.value),
                n = new Date();
            var a = n.getFullYear() - b.getFullYear();
            var m = n.getMonth() - b.getMonth();
            if (m < 0 || (m === 0 && n.getDate() < b.getDate())) a--;
            age.value = a >= 0 ? String(a) : "";
        });
    }

    function initSignature(form) {
        var inp = form.querySelector("[data-signature-input]");
        var prev = form.querySelector("[data-signature-preview]");
        if (!inp || !prev) return;
        var update = function () {
            prev.textContent = inp.value;
        };
        inp.addEventListener("input", update);
        inp.addEventListener("change", update);
    }

    function initLiveErrorClearing(form) {
        form.addEventListener("input", function (e) {
            clearFieldError(e.target);
        });
        form.addEventListener("change", function (e) {
            clearFieldError(e.target);
        });
    }

    function clearFieldError(el) {
        if (!el || !el.name) return;
        el.classList.remove("is-invalid");
        var up = el.closest(".js-upload");
        if (up) up.classList.remove("is-invalid");
        var eu = el.closest("[data-edu-upload]");
        if (eu) eu.classList.remove("is-invalid");
        var grid = el.closest(
            "[data-validate-radio], [data-validate-checkbox]",
        );
        if (grid) grid.classList.remove("is-invalid");
        if (el.closest('[data-repeat="languages"]')) {
            var lrow = el.closest(".lang-row");
            if (lrow) lrow.classList.remove("lang-row--invalid");
            var lerr = document.querySelector(
                '.f-error[data-error-for="languages[0][name]"]',
            );
            if (lerr) {
                lerr.textContent = "";
                lerr.classList.remove("show");
            }
        }
        var name = el.name.replace("[]", "");
        var err = document.querySelector(
            '.f-error[data-error-for="' + name + '"]',
        );
        if (err) {
            err.textContent = "";
            err.classList.remove("show");
        }
    }

    /* ══════════════════════════════════════════════════════════════════
       WIZARD  (step tracking + payment gate)
    ══════════════════════════════════════════════════════════════════ */
    function initWizard(form, steps) {
        var prevBtn = form.querySelector("[data-prev]");
        var nextBtn = form.querySelector("[data-next]");
        var submitBtn = form.querySelector("[data-submit]");
        var tabs = document.querySelectorAll("[data-step-tab]");
        var bar = document.querySelector("[data-progress-bar]");
        var curEl = document.querySelector("[data-step-current]");
        var nameEl = document.querySelector("[data-step-name]");
        var current = 0;

        // State filled in by applyDraft()
        var completedSteps = [];
        var paymentDone = false;

        /* ── helpers ── */
        function labelOf(i) {
            return tabs[i]
                ? tabs[i].querySelector(".stepper__label").textContent
                : "";
        }
        function togglePayCta(showIt) {
            var cta = document.querySelector(".pay-cta");
            if (cta) cta.hidden = !showIt;
        }
        function stepIndex(stepId) {
            return STEP_IDS.indexOf(stepId);
        }

        /* ── show(i): navigate to step i ── */
        function show(i) {
            var targetId = steps[i]
                ? steps[i].getAttribute("data-step-id")
                : "";

            steps.forEach(function (s, k) {
                s.hidden = k !== i;
            });
            current = i;

            prevBtn.hidden = i === 0;
            var isLast = i === steps.length - 1;
            nextBtn.hidden = isLast;
            // On the preview step: show Submit only if paid; otherwise show Pay CTA.
            if (submitBtn) {
                submitBtn.hidden = !isLast || !paymentDone;
            }
            togglePayCta(targetId === "preview" && !paymentDone);

            // Update tab states (active = current, done = in completedSteps)
            tabs.forEach(function (t, k) {
                var sid = steps[k] ? steps[k].getAttribute("data-step-id") : "";
                var isDone = completedSteps.indexOf(sid) !== -1;
                var isActive = k === i;
                var locked =
                    sid === "preview" &&
                    completedSteps.indexOf("declaration") === -1;

                t.classList.toggle("is-active", isActive);
                t.classList.toggle("is-done", isDone && !isActive);
                t.classList.toggle("is-locked", locked);
                t.style.pointerEvents = locked ? "none" : "";
                t.style.opacity = locked ? "0.4" : "";
                t.setAttribute("aria-disabled", locked ? "true" : "false");
            });

            if (bar) bar.style.width = ((i + 1) / steps.length) * 100 + "%";
            if (curEl) curEl.textContent = i + 1;
            if (nameEl) nameEl.textContent = labelOf(i);

            if (targetId === "enclosures" && form._syncEnclosures)
                form._syncEnclosures();
            if (targetId === "preview") renderPreview(form);

            var card = document.querySelector(".wizard-card");
            if (card)
                card.scrollIntoView({ behavior: "smooth", block: "start" });
        }

        // Expose so hydrate can jump to the right step.
        form._hydrated = false;
        form._wizardShow = function (i) {
            form._hydrated = true;
            show(i);
        };

        // Expose so hydrate can set state
        form._setWizardState = function (completed, paymentStatus) {
            completedSteps = Array.isArray(completed) ? completed : [];
            paymentDone = paymentStatus === "paid";
        };

        form._refreshTabs = function () {
            show(current);
        };

        /* ── "Save & Continue" ── */
        nextBtn.addEventListener("click", function () {
            if (!validateStep(steps[current])) return;

            var stepId = steps[current].getAttribute("data-step-id");

            if (STEP_DATA.indexOf(stepId) === -1) {
                show(Math.min(current + 1, steps.length - 1));
                return;
            }

            var label = nextBtn.textContent;
            nextBtn.disabled = true;
            nextBtn.textContent = "Saving…";

            saveStep(
                form,
                stepId,
                function (res) {
                    nextBtn.disabled = false;
                    nextBtn.textContent = label;
                    if (res && res.completed_steps) {
                        completedSteps = res.completed_steps;
                    } else if (completedSteps.indexOf(stepId) === -1) {
                        completedSteps.push(stepId);
                    }
                    show(Math.min(current + 1, steps.length - 1));
                },
                function () {
                    nextBtn.disabled = false;
                    nextBtn.textContent = label;
                },
            );
        });

        /* ── Back button ── */
        prevBtn.addEventListener("click", function () {
            show(Math.max(current - 1, 0));
        });

        /* ── Tab clicks: only allow going back / completed tabs ── */
        tabs.forEach(function (t, k) {
            t.addEventListener("click", function () {
                var targetId = steps[k]
                    ? steps[k].getAttribute("data-step-id")
                    : "";
                if (
                    targetId === "preview" &&
                    completedSteps.indexOf("declaration") === -1
                ) {
                    toast("Complete the declaration first.", "error");
                    return;
                }
                if (k <= current || completedSteps.indexOf(targetId) !== -1) {
                    show(k);
                }
            });
        });

        /* ── Print button ── */
        var printBtn = form.querySelector("[data-print]");
        if (printBtn)
            printBtn.addEventListener("click", function () {
                window.print();
            });

        /* ── Final submit (preview tab) ── */
        /* ── Final submit (preview tab) → acknowledgement modal first ── */
        var ackModal = document.getElementById("ackModal");
        var ackConfirm = document.getElementById("ackConfirm");
        var ackCancel = document.getElementById("ackCancel");
        var ackOverlay = ackModal
            ? ackModal.querySelector("[data-ack-overlay]")
            : null;

        function openAck() {
            if (ackModal) ackModal.style.display = "flex";
        }
        function closeAck() {
            if (ackModal) ackModal.style.display = "none";
        }

        function doSubmit() {
            sendRequest(
                form.getAttribute("action"),
                withToken(new FormData()),
                "POST",
                function (res) {
                    if (res && res.success) {
                        showToast(res.message, "success", 2000);
                        setTimeout(function () {
                            window.location.href = res.redirect;
                        }, 600);
                    } else {
                        closeAck();
                        toast(
                            (res && res.message) || "Could not submit.",
                            "error",
                        );
                        submitBtn.disabled = false;
                        submitBtn.textContent = "Submit Application";
                        if (ackConfirm) {
                            ackConfirm.disabled = false;
                            ackConfirm.textContent = "Submit Application";
                        }
                    }
                },
                function (err) {
                    closeAck();
                    showServerErr(err);
                    submitBtn.disabled = false;
                    submitBtn.textContent = "Submit Application";
                    if (ackConfirm) {
                        ackConfirm.disabled = false;
                        ackConfirm.textContent = "Submit Application";
                    }
                },
            );
        }

        form.addEventListener("submit", function (e) {
            e.preventDefault();

            if (!paymentDone) {
                toast("Payment must be completed before submitting.", "error");
                return;
            }

            // Step 1: show the acknowledgement popup instead of submitting now.
            openAck();
        });

        if (ackConfirm) {
            ackConfirm.addEventListener("click", function () {
                // Step 2: user acknowledged → now actually submit.
                ackConfirm.disabled = true;
                ackConfirm.textContent = "Submitting…";
                submitBtn.disabled = true;
                submitBtn.textContent = "Submitting…";
                doSubmit();
            });
        }
        if (ackCancel) ackCancel.addEventListener("click", closeAck);
        if (ackOverlay) ackOverlay.addEventListener("click", closeAck);
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") closeAck();
        });

        // Fallback: if hydrate never drives a first paint (e.g. network error),
        // show step 0 so the form is still usable.
        setTimeout(function () {
            if (!form._hydrated) show(0);
        }, 1500);
    }

    /* ══════════════════════════════════════════════════════════════════
       STEP SAVE  (AJAX)
    ══════════════════════════════════════════════════════════════════ */
    function saveStep(form, stepId, onOk, onFail) {
        var fd = collectStep(form, stepId);

        var stepUrl = window.AppRoutes.step.replace(":step", stepId);

        if (
            stepUrl.indexOf(":step") !== -1 ||
            stepUrl.indexOf("STEP_PLACEHOLDER") !== -1
        ) {
            console.error(
                "Step URL placeholder not replaced:",
                stepUrl,
                "stepId:",
                stepId,
            );
            toast("Internal error building request URL.", "error");
            if (onFail) onFail();
            return;
        }

        sendRequest(
            stepUrl,
            fd,
            "POST",
            function (res) {
                if (res && res.success) {
                    if (onOk) onOk(res);
                } else {
                    toast((res && res.message) || "Could not save.", "error");
                    if (onFail) onFail();
                }
            },
            function (err) {
                showServerErr(err);
                if (onFail) onFail();
            },
        );
    }

    function collectStep(form, stepId) {
        var fd = new FormData();
        var section = form.querySelector('[data-step-id="' + stepId + '"]');
        if (!section) return withToken(fd);

        section
            .querySelectorAll("input, select, textarea")
            .forEach(function (el) {
                if (!el.name) return;
                if (el.type === "checkbox") {
                    if (el.checked) fd.append(el.name, el.value || "1");
                } else if (el.type === "radio") {
                    if (el.checked) fd.append(el.name, el.value);
                } else if (el.type === "file") {
                    if (el.files && el.files[0])
                        fd.append(el.name, el.files[0]);
                } else {
                    fd.append(el.name, el.value);
                }
            });

        return withToken(fd);
    }

    function withToken(fd) {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) fd.append("_token", meta.getAttribute("content"));
        return fd;
    }

    function showServerErr(err) {
        if (err && err.errors) {
            var msg = "";
            for (var k in err.errors) {
                if (err.errors.hasOwnProperty(k))
                    msg += err.errors[k][0] + "<br>";
            }
            toast(msg, "error");
        } else {
            toast((err && err.message) || "Unexpected error", "error");
        }
    }

    /* ══════════════════════════════════════════════════════════════════
       VALIDATION
    ══════════════════════════════════════════════════════════════════ */
    function isActive(el, stepEl) {
        if (el.disabled) return false;
        var n = el;
        while (n && n !== stepEl) {
            if (n.hidden) return false;
            n = n.parentElement;
        }
        return true;
    }

    function setError(stepEl, name, msg) {
        var err = stepEl.querySelector(
            '.f-error[data-error-for="' + name + '"]',
        );
        if (err) {
            err.textContent = msg;
            err.classList.add("show");
        }
    }

    function clearStepErrors(stepEl) {
        stepEl.querySelectorAll(".f-error").forEach(function (e) {
            e.textContent = "";
            e.classList.remove("show");
        });
        stepEl.querySelectorAll(".is-invalid").forEach(function (e) {
            e.classList.remove("is-invalid");
        });
    }

    function validateStep(stepEl) {
        clearStepErrors(stepEl);
        var ok = true,
            firstBad = null;

        stepEl.querySelectorAll("[data-validate-radio]").forEach(function (g) {
            if (!isActive(g, stepEl)) return;
            var name = g.getAttribute("data-validate-radio");
            if (!stepEl.querySelector('input[name="' + name + '"]:checked')) {
                setError(stepEl, name, "Please select an option.");
                g.classList.add("is-invalid");
                ok = false;
                firstBad = firstBad || g;
            }
        });

        stepEl
            .querySelectorAll("[data-validate-checkbox]")
            .forEach(function (g) {
                if (!isActive(g, stepEl)) return;
                var name = g.getAttribute("data-validate-checkbox");
                if (
                    !stepEl.querySelector(
                        'input[name="' +
                            name +
                            '[]"]:checked, input[name="' +
                            name +
                            '"]:checked',
                    )
                ) {
                    setError(
                        stepEl,
                        name,
                        "Please select at least one option.",
                    );
                    g.classList.add("is-invalid");
                    ok = false;
                    firstBad = firstBad || g;
                }
            });

        var langTable = stepEl.querySelector('[data-repeat="languages"]');
        if (langTable) {
            var langBad = false;
            langTable
                .querySelectorAll(".lang-row:not(.lang-row--head)")
                .forEach(function (row) {
                    var nm = row.querySelector("[data-lang-name]");
                    if (
                        nm &&
                        nm.value.trim() &&
                        !row.querySelector(".skill-chip input:checked")
                    ) {
                        row.classList.add("lang-row--invalid");
                        langBad = true;
                    } else {
                        row.classList.remove("lang-row--invalid");
                    }
                });
            if (langBad) {
                setError(
                    stepEl,
                    "languages[0][name]",
                    "Select at least one skill (R / W / S / U) for each language.",
                );
                ok = false;
                firstBad = firstBad || langTable;
            }
        }

        stepEl
            .querySelectorAll("input, select, textarea")
            .forEach(function (f) {
                if (f.type === "radio") return;
                if (f.type === "checkbox") {
                    if (f.required && isActive(f, stepEl) && !f.checked) {
                        setError(
                            stepEl,
                            f.name.replace("[]", ""),
                            "This is required.",
                        );
                        ok = false;
                        firstBad = firstBad || f;
                    }
                    return;
                }
                var required =
                    f.required || f.getAttribute("data-required") === "true";
                if (!required || !isActive(f, stepEl)) return;
                var val = (f.value || "").trim();
                if (!val) {
                    markInvalid(stepEl, f, "This field is required.");
                    ok = false;
                    firstBad = firstBad || f;
                    return;
                }
                if (
                    f.type === "email" &&
                    !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)
                ) {
                    markInvalid(stepEl, f, "Enter a valid e-mail address.");
                    ok = false;
                    firstBad = firstBad || f;
                }
            });

        if (firstBad) {
            var focusEl = firstBad.focus
                ? firstBad
                : firstBad.querySelector("input, select, textarea");
            try {
                (focusEl || firstBad).scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            } catch (e) {}
        }
        return ok;
    }

    function markInvalid(stepEl, f, msg) {
        f.classList.add("is-invalid");
        var up = f.closest(".js-upload");
        if (up) up.classList.add("is-invalid");
        var eu = f.closest("[data-edu-upload]");
        if (eu) eu.classList.add("is-invalid");
        setError(stepEl, f.name.replace("[]", ""), msg);
    }

    /* ══════════════════════════════════════════════════════════════════
       HYDRATE DRAFT  (on page load — resume last saved state)
    ══════════════════════════════════════════════════════════════════ */
    function hydrateDraft(form) {
        var draftUrl = window.AppRoutes.draft;

        function done(res) {
            applyDraft(form, res);
        }

        function failed(status, body) {
            // Surface the reason instead of silently dropping to step 0.
            console.error(
                "[hydrate] draft request failed. status:",
                status,
                body,
            );
            if (status === 401 || status === 419) {
                toast(
                    "Your session has expired on this device. Please log in again to resume your application.",
                    "error",
                );
            }
            if (form._wizardShow) form._wizardShow(0);
        }

        fetch(draftUrl, {
            method: "GET",
            credentials: "same-origin", // send the session cookie (key for other devices)
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        })
            .then(function (r) {
                return r
                    .json()
                    .catch(function () {
                        return null;
                    })
                    .then(function (body) {
                        if (!r.ok) {
                            failed(r.status, body);
                            return null;
                        }
                        return body;
                    });
            })
            .then(function (res) {
                if (res) done(res);
            })
            .catch(function (err) {
                failed("network", err && err.message);
            });
    }

    function applyDraft(form, res) {
        if (!res || !res.success) {
            if (res && res.message) toast(res.message, "error");
            if (form._wizardShow) form._wizardShow(0);
            return;
        }

        // ── 1. Restore wizard state (completed tabs + payment) FIRST ──
        if (form._setWizardState) {
            form._setWizardState(
                res.completed_steps || [],
                res.payment_status || "unpaid",
            );
        }

        // ── 2. Fill field values ──
        if (res.draft) {
            var d = res.draft;
            var SKIP = [
                "languages",
                "education",
                "service",
                "projects",
                "career_aspirations",
                "courses",
                "enclosures",
                "files",
            ].concat(CASCADE_KEYS);

            Object.keys(d).forEach(function (key) {
                if (SKIP.indexOf(key) !== -1) return;
                setField(form, key, d[key]);
            });

            // Cascade FIRST among structured hydrators (waits for options).
            hydrateCascade(form, d);

            hydrateLanguages(form, d.languages || []);
            hydrateEducation(form, d.education || {});
            hydrateService(form, d.service || []);
            hydrateProjects(form, d.projects || []);
            hydrateCheckGroup(
                form,
                "career_aspirations[]",
                d.career_aspirations || [],
            );
            hydrateCourses(form, d.courses || {});
            hydrateEnclosures(form, d.enclosures || {});
            hydrateFiles(form, d.files || {});

            form.querySelectorAll("[data-edu-row] .edu-field").forEach(
                function (f) {
                    f.dispatchEvent(new Event("input", { bubbles: true }));
                },
            );

            form.querySelectorAll("input, select, textarea").forEach(
                function (el) {
                    if (
                        el.matches('[data-cascade="school"]') ||
                        el.matches('[data-cascade="discipline"]') ||
                        el.matches('[data-cascade="specialization"]')
                    )
                        return;
                    el.dispatchEvent(new Event("change", { bubbles: true }));
                },
            );
            if (form._applyEngFilter) form._applyEngFilter();
        }

        // ── 3. Jump to last saved step (and make that tab active) ──
        var idx = 0;
        if (res.current_step) {
            idx = STEP_IDS.indexOf(res.current_step);
            if (idx === -1 && res.current_step === "payment") {
                idx = STEP_IDS.indexOf("preview");
            }
            if (idx < 0) idx = 0;
        }
        if (form._wizardShow) {
            // Let cascade/repeatable change events settle first.
            setTimeout(function () {
                form._wizardShow(idx);
            }, 200);
        }

        // ── 4. Payment success toast (when redirected back from gateway) ──
        if (res.payment_status === "paid") {
            var params = new URLSearchParams(window.location.search);
            if (params.has("payment_success") || params.has("order_id")) {
                toast(
                    "Payment confirmed! Please review and submit your application.",
                    "success",
                );
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(
                        {},
                        "",
                        window.location.pathname,
                    );
                }
            }
        }
    }

    /* ── field setters ── */
    function setField(form, name, value) {
        if (value === null || value === undefined) return;
        var els = form.querySelectorAll('[name="' + cssEsc(name) + '"]');
        if (!els.length) return;

        var first = els[0];

        if (first.type === "radio") {
            els.forEach(function (r) {
                r.checked =
                    r.value === String(value) ||
                    (value === true &&
                        (r.value === "1" || r.value === "Yes")) ||
                    (value === false && (r.value === "0" || r.value === "No"));
            });
        } else if (first.type === "checkbox") {
            first.checked =
                value === true ||
                value === 1 ||
                value === "1" ||
                value === "Yes";
        } else if (first.tagName === "SELECT") {
            // Only assign if the option exists — otherwise the browser silently
            // keeps the old value and the assignment looks like it "worked".
            var ok = Array.prototype.some.call(first.options, function (o) {
                return o.value === String(value);
            });
            if (ok) first.value = String(value);
            first.dispatchEvent(new Event("change", { bubbles: true }));
        } else if (first.type !== "file") {
            first.value = value;
        }
    }

    function hydrateCascade(form, d) {
        var school = form.querySelector('[data-cascade="school"]');
        var disc = form.querySelector('[data-cascade="discipline"]');
        var spec = form.querySelector('[data-cascade="specialization"]');
        if (!school) return;

        var OTHER = "Other (please specify)";

        function hasOption(sel, value) {
            return (
                sel &&
                Array.prototype.some.call(sel.options, function (o) {
                    return o.value === String(value);
                })
            );
        }

        function setSelectValue(sel, value) {
            if (!sel || value == null || value === "") return false;
            if (hasOption(sel, value)) {
                sel.value = String(value);
                sel.dispatchEvent(new Event("change", { bubbles: true }));
                return true;
            }
            return false;
        }

        // 1. School → its change handler fills the discipline dropdown.
        if (d.school && !setSelectValue(school, d.school)) {
            console.warn(
                "[cascade] saved school not found in options:",
                d.school,
            );
        }

        // 2. Discipline — options now exist (filled synchronously above).
        if (d.discipline) {
            if (!setSelectValue(disc, d.discipline)) {
                setTimeout(function () {
                    setSelectValue(disc, d.discipline);
                }, 0);
            }
        }

        // 3. Specialization (or the OTHER free-text path), deferred a tick.
        setTimeout(function () {
            if (d.specialization && setSelectValue(spec, d.specialization)) {
                // matched a real option
            } else if (d.specialization_other) {
                if (hasOption(spec, OTHER)) {
                    spec.value = OTHER;
                    spec.dispatchEvent(new Event("change", { bubbles: true }));
                }
                setField(form, "specialization_other", d.specialization_other);
            } else if (d.specialization) {
                setTimeout(function () {
                    setSelectValue(spec, d.specialization);
                }, 0);
            }
        }, 0);
    }

    function clickAdd(container, times) {
        var add = container.querySelector("[data-repeat-add]");
        for (var i = 0; i < times; i++) {
            if (add) add.click();
        }
    }

    function hydrateLanguages(form, list) {
        var table = form.querySelector('[data-repeat="languages"]');
        if (!table || !list.length) return;
        var rows = function () {
            return table.querySelectorAll(".lang-row:not(.lang-row--head)");
        };
        clickAdd(table, Math.max(0, list.length - rows().length));
        rows().forEach(function (row, i) {
            var item = list[i];
            if (!item) return;
            var nm = row.querySelector("[data-lang-name]");
            if (nm) {
                nm.value = item.name;
                nm.dispatchEvent(new Event("input", { bubbles: true }));
            }
            (item.skills || []).forEach(function (sk) {
                var cb = row.querySelector(
                    '.skill-chip input[value="' + sk + '"]',
                );
                if (cb) cb.checked = true;
            });
        });
    }

    function hydrateEducation(form, map) {
        Object.keys(map).forEach(function (level) {
            var row = map[level];
            setField(form, "education[" + level + "][subjects]", row.subjects);
            setField(
                form,
                "education[" + level + "][institution]",
                row.institution,
            );
            setField(form, "education[" + level + "][passing]", row.passing);
            setField(form, "education[" + level + "][marks]", row.marks);
        });
    }

    function hydrateService(form, list) {
        var c = form.querySelector('[data-repeat="service"]');
        if (!c || !list.length) return;
        var rows = function () {
            return c.querySelectorAll("[data-repeat-row]");
        };
        clickAdd(c, Math.max(0, list.length - rows().length));
        rows().forEach(function (row, i) {
            var s = list[i];
            if (!s) return;
            setRowField(row, "designation", s.designation);
            setRowField(row, "institution", s.institution);
            setRowField(row, "from", s.from);
            setRowField(row, "to", s.to);
            setRowField(row, "total", s.total);
        });
    }

    function hydrateProjects(form, list) {
        var c = form.querySelector('[data-repeat="projects"]');
        if (!c || !list.length) return;
        var rows = function () {
            return c.querySelectorAll("[data-repeat-row]");
        };
        clickAdd(c, Math.max(0, list.length - rows().length));
        rows().forEach(function (row, i) {
            var p = list[i];
            if (!p) return;
            var t = row.querySelector('input[type="text"]');
            if (t) t.value = p.title;
            var s = row.querySelector("select");
            if (s) s.value = p.status;
        });
    }

    function setRowField(row, suffix, val) {
        if (val == null) return;
        var el = row.querySelector('[name$="[' + suffix + ']"]');
        if (el) el.value = val;
    }

    function hydrateCheckGroup(form, name, values) {
        values.forEach(function (v) {
            var cb = form.querySelector(
                'input[name="' + cssEsc(name) + '"][value="' + cssEsc(v) + '"]',
            );
            if (cb) cb.checked = true;
        });
    }

    function hydrateCourses(form, map) {
        Object.keys(map).forEach(function (key) {
            setField(form, "course_" + key, map[key] ? "Yes" : "No");
        });
    }

    function hydrateEnclosures(form, map) {
        Object.keys(map).forEach(function (key) {
            var cb = form.querySelector(
                'input[name="enclosures[' + cssEsc(key) + ']"]',
            );
            if (cb && map[key]) cb.checked = true;
        });
    }

    function hydrateFiles(form, map) {
        Object.keys(map).forEach(function (docType) {
            var file = map[docType];
            if (!file || !file.url) return;

            // ── Show the saved-file preview box if it exists ──
            var previewBox = document.getElementById("saved-" + docType);
            if (previewBox) {
                var nameEl = previewBox.querySelector(
                    '[data-saved-name="' + docType + '"]',
                );
                if (nameEl) nameEl.textContent = file.name;

                var linkEl = previewBox.querySelector(
                    '[data-saved-url="' + docType + '"]',
                );
                if (linkEl) linkEl.href = file.url;

                var thumbEl = previewBox.querySelector(
                    '[data-saved-thumb="' + docType + '"]',
                );
                if (thumbEl) {
                    thumbEl.src = file.url;
                    thumbEl.hidden = false;
                }
                previewBox.hidden = false;
            }

            // ── Locate the matching file input ──
            var inp = form.querySelector(
                'input[type="file"][name="' + cssEsc(docType) + '"]',
            );
            if (!inp) return;

            if (inp.classList.contains("edu-up__input")) {
                inp.disabled = false;
                var euWrap = inp.closest("[data-edu-upload]");
                if (euWrap) {
                    euWrap.classList.remove("is-locked");

                    // Remember whether this row was required (check BOTH attributes,
                    // because data-required may not be set yet at hydrate time).
                    if (
                        inp.getAttribute("data-required") === "true" ||
                        inp.required
                    ) {
                        euWrap.setAttribute("data-was-required", "1");
                    }

                    // Always mark that a saved file exists for this row, regardless of
                    // the required state. sync() uses this to avoid re-requiring an
                    // upload for rows the server already has a file for.
                    euWrap.setAttribute("data-has-saved", "1");
                }
            }

            inp.required = false;
            inp.removeAttribute("required");
            inp.removeAttribute("data-required"); // <-- this clears it, so the block above must run first

            var group =
                inp.closest(".js-upload, [data-edu-upload], .f-group") ||
                inp.parentElement;

            // Clear any validation error already shown on the group
            if (group) {
                group.classList.remove("is-invalid");
                var ge = group.querySelector(".f-error");
                if (ge) {
                    ge.textContent = "";
                    ge.classList.remove("show");
                }
            }

            // ── Inject a hint below the file input (fallback) ──
            if (!group) return;

            var oldHint = group.querySelector("[data-saved-file]");
            if (oldHint) oldHint.remove();

            var hint = document.createElement("p");
            hint.className = "f-hint saved-file-hint";
            hint.setAttribute("data-saved-file", docType);
            hint.innerHTML =
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>' +
                'Saved: <a href="' +
                file.url +
                '" target="_blank" style="color:var(--primary)">' +
                esc(file.name) +
                "</a> " +
                '<span style="opacity:.6">(re-upload to replace)</span>';

            var zone = group.querySelector("[data-zone]");
            if (zone) {
                group.insertBefore(hint, zone);
            } else {
                group.appendChild(hint);
            }
        });
    }

    /* ══════════════════════════════════════════════════════════════════
       PREVIEW
    ══════════════════════════════════════════════════════════════════ */
    function renderPreview(form) {
        var body = document.querySelector("[data-preview-body]");
        if (!body) return;

        function val(name) {
            var nodes = form.querySelectorAll('[name="' + name + '"]');
            if (!nodes.length) return "";
            var first = nodes[0];
            if (first.type === "radio") {
                var c = form.querySelector('[name="' + name + '"]:checked');
                return c ? c.value : "";
            }
            if (first.type === "checkbox") {
                return first.checked
                    ? first.value === "1"
                        ? "Yes"
                        : first.value
                    : "";
            }
            return first.value || "";
        }

        function radioLabel(name) {
            var c = form.querySelector('[name="' + name + '"]:checked');
            if (!c) return "";
            var card = c.closest(".choice-card");
            return card
                ? card.querySelector(".choice-card__title").textContent.trim()
                : c.value;
        }

        function fileName(name) {
            var inp = form.querySelector(
                'input[type="file"][name="' + name + '"]',
            );
            return inp && inp.files && inp.files[0] ? inp.files[0].name : "";
        }

        function item(label, value, full) {
            var empty = value === undefined || value === null || value === "";
            return (
                '<div class="pv-item' +
                (full ? " pv-item--full" : "") +
                '">' +
                '<span class="pv-item__label">' +
                esc(label) +
                "</span>" +
                '<span class="pv-item__value' +
                (empty ? " is-empty" : "") +
                '">' +
                (empty ? "—" : esc(value)) +
                "</span></div>"
            );
        }

        function section(title, inner) {
            return (
                '<div class="pv-section"><h3 class="pv-section__title">' +
                esc(title) +
                "</h3>" +
                inner +
                "</div>"
            );
        }

        function grid(items) {
            return '<div class="pv-grid">' + items.join("") + "</div>";
        }

        var html = "";

        var photo = form.querySelector('input[type="file"][name="photo"]');
        if (photo && photo.files && photo.files[0]) {
            html +=
                '<img class="pv-photo" src="' +
                URL.createObjectURL(photo.files[0]) +
                '" alt="Applicant photo">';
        }

        var spec = val("specialization");
        if (spec === "Other (please specify)")
            spec = val("specialization_other");
        html += section(
            "Programme & School",
            grid([
                item("School", val("school")),
                item("Discipline", val("discipline")),
                item("Specialization", spec),
                item("Programme Mode", radioLabel("programme_mode")),
            ]),
        );

        var gender = radioLabel("gender");
        if (val("single_girl_child") === "Yes")
            gender += " · Single Girl Child";
        html += section(
            "Personal Details",
            grid([
                item("Full Name", val("full_name"), true),
                item("Date of Birth", val("dob")),
                item("Age", val("age")),
                item("Gender", gender),
                item("Nationality", val("nationality")),
                item("Religion", val("religion")),
                item("Community", val("community")),
                item("Differently Abled", radioLabel("differently_abled")),
                item("Father / Guardian", val("father_name")),
                item("Mother", val("mother_name")),
                item("Mobile", val("mobile")),
                item("E-mail", val("email")),
                item("Languages Known", languageSummary(form), true),
                item("Current Address", val("address_current"), true),
                item("Permanent Address", val("address_permanent"), true),
            ]),
        );

        html += section("Educational Qualification", eduTable(form));

        var eligItems = [
            item(
                "Qualified in National/State exam",
                radioLabel("eligibility_qualified"),
            ),
        ];
        if (val("eligibility_qualified") === "Yes")
            eligItems.push(item("Examination", val("eligibility_exam")));
        else if (val("eligibility_qualified") === "No")
            eligItems.push(
                item(
                    "Note",
                    "Must clear the RGU Common Eligibility Test (CET)",
                ),
            );
        html += section(
            "Eligibility",
            grid(eligItems) +
                filesList([
                    [
                        "Qualifying score copy",
                        fileName("eligibility_certificate"),
                    ],
                ]),
        );

        var svc = serviceTable(form);
        html += section(
            "Academic, Research & Industry Service",
            (svc ||
                '<p class="pv-item__value is-empty">No experience entered.</p>') +
                '<p style="margin-top:8px;font-size:.84rem">Total service: <strong>' +
                esc(val("total_service_years") || "0") +
                "</strong> years <strong>" +
                esc(val("total_service_months") || "0") +
                "</strong> months</p>",
        );

        html += section(
            "Projects, Courses & Aspirations",
            projectsTable(form) +
                grid([
                    item(
                        "Research Methodology completed",
                        radioLabel("course_research_methodology"),
                    ),
                    item(
                        "Research & Publication Ethics completed",
                        radioLabel("course_publication_ethics"),
                    ),
                    item("Career Aspirations", aspirations(form), true),
                ]) +
                filesList([
                    ["One-page focus summary", fileName("summary_document")],
                ]),
        );

        html += section(
            "Enclosures",
            enclosuresList(form) +
                filesList([
                    ["NOC (Part-Time)", fileName("noc_document")],
                    ["Service Certificate", fileName("service_certificate")],
                    [
                        "Community Certificate",
                        fileName("community_certificate"),
                    ],
                    [
                        "Disability Certificate",
                        fileName("disability_certificate"),
                    ],
                    [
                        "Equivalence Certificate",
                        fileName("equivalence_certificate"),
                    ],
                ]),
        );

        html += section(
            "Declaration",
            grid([
                item("Date", val("declaration_date")),
                item("Station", val("declaration_station")),
            ]) +
                '<div style="margin-top:12px"><span class="pv-item__label">Signature of the Applicant</span>' +
                '<div class="pv-sign">' +
                esc(val("declaration_signature")) +
                "</div></div>",
        );

        body.innerHTML = html;
    }

    /* ── preview helpers ── */
    function languageSummary(form) {
        var out = [];
        form.querySelectorAll(".lang-row:not(.lang-row--head)").forEach(
            function (row) {
                var nm = row.querySelector("[data-lang-name]");
                if (nm && nm.value.trim()) {
                    var skills = Array.prototype.map.call(
                        row.querySelectorAll(".skill-chip input:checked"),
                        function (c) {
                            return c.value;
                        },
                    );
                    out.push(
                        nm.value.trim() +
                            (skills.length
                                ? " (" + skills.join("/") + ")"
                                : ""),
                    );
                }
            },
        );
        return out.join(", ");
    }

    function eduTable(form) {
        var rows = "";
        var step = form.querySelector('[data-step-id="education"]');
        if (step) {
            step.querySelectorAll(".edu-row").forEach(function (r) {
                var label =
                    (r.querySelector(".edu-row__label") || {}).textContent ||
                    "";
                var fields = r.querySelectorAll(".edu-field");
                var vals = Array.prototype.map.call(fields, function (i) {
                    return i.value.trim();
                });
                var fileInp = r.querySelector(".edu-up__input");
                var fileNm =
                    fileInp && fileInp.files && fileInp.files[0]
                        ? fileInp.files[0].name
                        : "";
                if (vals.some(Boolean) || fileNm) {
                    rows +=
                        "<tr><td>" +
                        esc(label.replace("*", "").trim()) +
                        "</td>" +
                        "<td>" +
                        esc(vals[0] || "") +
                        "</td><td>" +
                        esc(vals[1] || "") +
                        "</td>" +
                        "<td>" +
                        esc(vals[2] || "") +
                        "</td><td>" +
                        esc(vals[3] || "") +
                        "</td>" +
                        "<td>" +
                        (fileNm ? esc(fileNm) : "—") +
                        "</td></tr>";
                }
            });
        }
        if (!rows)
            return '<p class="pv-item__value is-empty">No qualifications entered.</p>';
        return (
            '<div class="pv-scroll"><table class="pv-table"><thead><tr>' +
            "<th>Degree</th><th>Subjects</th><th>Institution</th><th>Month/Year</th><th>Marks</th><th>Mark sheet</th>" +
            "</tr></thead><tbody>" +
            rows +
            "</tbody></table></div>"
        );
    }

    function serviceTable(form) {
        var rows = "";
        var step = form.querySelector('[data-repeat="service"]');
        if (step) {
            step.querySelectorAll("[data-repeat-row]").forEach(function (r) {
                var ins = r.querySelectorAll("input");
                var vals = Array.prototype.map.call(ins, function (i) {
                    return i.value.trim();
                });
                if (vals.slice(0, 4).some(Boolean)) {
                    rows +=
                        "<tr><td>" +
                        esc(vals[0]) +
                        "</td><td>" +
                        esc(vals[1]) +
                        "</td>" +
                        "<td>" +
                        esc(vals[2]) +
                        "</td><td>" +
                        esc(vals[3]) +
                        "</td>" +
                        "<td>" +
                        esc(vals[4] || "") +
                        "</td></tr>";
                }
            });
        }
        if (!rows) return "";
        return (
            '<div class="pv-scroll"><table class="pv-table"><thead><tr>' +
            "<th>Designation</th><th>Institution</th><th>From</th><th>To</th><th>Total</th>" +
            "</tr></thead><tbody>" +
            rows +
            "</tbody></table></div>"
        );
    }

    function projectsTable(form) {
        var rows = "";
        var step = form.querySelector('[data-repeat="projects"]');
        if (step) {
            step.querySelectorAll("[data-repeat-row]").forEach(function (r) {
                var title =
                    (r.querySelector('input[type="text"]') || {}).value || "";
                var status = (r.querySelector("select") || {}).value || "";
                if (title.trim())
                    rows +=
                        "<tr><td>" +
                        esc(title) +
                        "</td><td>" +
                        esc(status) +
                        "</td></tr>";
            });
        }
        if (!rows) return "";
        return (
            '<div class="pv-scroll" style="margin-bottom:12px"><table class="pv-table"><thead><tr>' +
            "<th>Project / Start-up / Event</th><th>Outcome</th>" +
            "</tr></thead><tbody>" +
            rows +
            "</tbody></table></div>"
        );
    }

    function aspirations(form) {
        var arr = Array.prototype.map.call(
            form.querySelectorAll('input[name="career_aspirations[]"]:checked'),
            function (c) {
                return c.value;
            },
        );
        var other =
            (form.querySelector('[name="career_other"]') || {}).value || "";
        if (other)
            arr = arr
                .filter(function (a) {
                    return a !== "Any other";
                })
                .concat("Any other: " + other);
        return arr.join(", ");
    }

    function enclosuresList(form) {
        var items = [];
        form.querySelectorAll(".encl-list__item").forEach(function (li) {
            if (li.hidden) return;
            var cb = li.querySelector('input[type="checkbox"]');
            if (cb && cb.checked) {
                var span = li.querySelector(".inline-check span");
                items.push(span ? span.textContent.trim() : "");
            }
        });
        if (!items.length)
            return '<p class="pv-item__value is-empty">No enclosures ticked.</p>';
        return (
            '<ul class="pv-files">' +
            items
                .map(function (i) {
                    return "<li>" + esc(i) + "</li>";
                })
                .join("") +
            "</ul>"
        );
    }

    function filesList(pairs) {
        var rows = pairs.filter(function (p) {
            return p[1];
        });
        if (!rows.length) return "";
        return (
            '<ul class="pv-files" style="margin-top:12px">' +
            rows
                .map(function (p) {
                    return (
                        "<li>" +
                        esc(p[0]) +
                        ": <strong>" +
                        esc(p[1]) +
                        "</strong></li>"
                    );
                })
                .join("") +
            "</ul>"
        );
    }

    /* ══════════════════════════════════════════════════════════════════
       UTILS
    ══════════════════════════════════════════════════════════════════ */
    function toast(message, type) {
        if (window.showToast) {
            window.showToast(message, type, 2000);
            return;
        }
        if (window.rguToast) {
            window.rguToast(message, type);
            return;
        }
        console.warn("[scholar]", type, message);
    }

    function formatSize(b) {
        if (b < 1024) return b + " B";
        if (b < 1048576) return (b / 1024).toFixed(0) + " KB";
        return (b / 1048576).toFixed(2) + " MB";
    }

    function currentYM() {
        var d = new Date();
        return d.getFullYear() + "-" + ("0" + (d.getMonth() + 1)).slice(-2);
    }

    function cssEsc(s) {
        return String(s).replace(/(["\\\]\[])/g, "\\$1");
    }

    function esc(s) {
        return String(s == null ? "" : s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    /* ══════════════════════════════════════════════════════════════════
       PAY NOW (preview step) → initiatePayment → Cashfree redirect
    ══════════════════════════════════════════════════════════════════ */
    function initPayNow(form) {
        var btn = document.querySelector("[data-pay-now]");
        if (!btn) return;
        var initiatePayment = window.AppRoutes.initiatePayment;
        var INITIATE_URL =
            btn.getAttribute("data-initiate-url") || initiatePayment;

        function csrf() {
            var m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.getAttribute("content") : "";
        }

        function setError(msg) {
            var el = document.querySelector('[data-error-for="payment"]');
            if (el) el.textContent = msg || "";
        }

        btn.addEventListener("click", function () {
            if (btn.disabled) return;
            btn.disabled = true;
            var original = btn.textContent;
            btn.textContent = "Starting payment…";
            setError("");

            fetch(INITIATE_URL, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "X-CSRF-TOKEN": csrf(),
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { ok: res.ok, data: data };
                    });
                })
                .then(function (r) {
                    if (r.ok && r.data && r.data.success && r.data.redirect) {
                        window.location.href = r.data.redirect;
                        return;
                    }
                    throw new Error(
                        (r.data && r.data.message) ||
                            "Could not start payment.",
                    );
                })
                .catch(function (err) {
                    setError(
                        err.message ||
                            "Payment could not be started. Please try again.",
                    );
                    btn.disabled = false;
                    btn.textContent = original;
                });
        });
    }
})();
