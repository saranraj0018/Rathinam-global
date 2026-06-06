 $(document).on("submit", "#forgotPassForm", function (e) {
    e.preventDefault();
    let $form = $(this);
    let $saveBtn = $form.find('button[type="submit"]');
    let fields = [
        {
            id: '[name="email"]',
            condition: (v) => v === "",
            message: "Email is required",
        }
    ];
    let isValid = true;
    for (const field of fields) {
        if (!validateField(field)) isValid = false;
    }
    if (!isValid) return;
    $saveBtn.prop("disabled", true).text("Signing in....");
    let formData = new FormData(this);
    sendRequest(
        $form.attr("action"),
        formData,
        "POST",
        function (res) {
            if (res.success && res.showDeclaration) {
                openDeclaration();
            } else if (res.success) {
                showToast(res.message, "success", 2000);
                setTimeout(() => {
                    window.location.href = res.redirect;
                }, 500);
            } else {
                showToast(res.message, "error", 2000);
            }
            $saveBtn.prop("disabled", false).text("Sign In");
        },
        function (err) {
            if (err.errors) {
                let msg = "";
                $.each(err.errors, function (k, v) {
                    msg += v[0] + "<br>";
                });
                showToast(msg, "error", 2000);
            } else {
                showToast(err.message || "Unexpected error", "error", 2000);
            }
            $saveBtn.prop("disabled", false).text("Sign In");
        },
    );
});

$(document).on("submit", "#resetPassword", function (e) {
    e.preventDefault();
    let $form = $(this);
    let $saveBtn = $form.find('button[type="submit"]');
    let pwVal = $('[name="password"]').val();
    let pwConfirmVal = $('[name="password_confirmation"]').val();
    let fields = [
        {
            id: '[name="email"]',
            condition: (v) => v === "",
            message: "Email is required",
        },
        {
        id: '[name="password"]',
        condition: (v) => v === "" || v.length < 8,
        message: "Password is required and must be at least 8 characters",
    },
    {
        id: '[name="password_confirmation"]',
        condition: (v) => v === "" || v !== pwVal,
        message: "Passwords do not match",
    },
    ];
    let isValid = true;
    for (const field of fields) {
        if (!validateField(field)) isValid = false;
    }
    if (!isValid) return;
    $saveBtn.prop("disabled", true).text("Signing in....");
    let formData = new FormData(this);
    sendRequest(
        $form.attr("action"),
        formData,
        "POST",
        function (res) {
            if (res.success && res.showDeclaration) {
                openDeclaration();
            } else if (res.success) {
                showToast(res.message, "success", 2000);
                setTimeout(() => {
                    window.location.href = res.redirect;
                }, 500);
            } else {
                showToast(res.message, "error", 2000);
            }
            $saveBtn.prop("disabled", false).text("Sign In");
        },
        function (err) {
            if (err.errors) {
                let msg = "";
                $.each(err.errors, function (k, v) {
                    msg += v[0] + "<br>";
                });
                showToast(msg, "error", 2000);
            } else {
                showToast(err.message || "Unexpected error", "error", 2000);
            }
            $saveBtn.prop("disabled", false).text("Sign In");
        },
    );
});



