
$(document).on("submit", "#signupForm", function (e) {
    e.preventDefault();

    let $form = $(this);
    let $saveBtn = $form.find('button[type="submit"]');

    // selectors must match the actual input ids/names
    let fields = [
        {
            id: '[name="name"]',
            condition: (v) => v === "",
            message: "Name is required",
        },
        {
            id: '[name="phone_number"]',
            condition: (v) => v === "",
            message: "Phone number is required",
        },
        {
            id: '[name="email"]',
            condition: (v) => v === "",
            message: "Email is required",
        },
        {
            id: '[name="password"]',
            condition: (v) => v === "",
            message: "Password is required",
        },
        {
            id: '[name="password_confirmation"]',
            condition: (v) => v === "",
            message: "Please confirm your password",
        },
    ];

    let isValid = true;
    for (const field of fields) {
        if (!validateField(field)) isValid = false;
    }
    if (!isValid) return;

    $saveBtn
        .prop("disabled", true)
        .removeClass("opacity-50 cursor-not-allowed")
        .text("Saving....");

    let formData = new FormData(this);

    sendRequest(
        $form.attr("action"), // uses the form's action route
        formData,
        "POST",
        function (res) {
            if (res.success) {
                showToast(res.message, "success", 2000);
                setTimeout(() => {
                    window.location.href = res.redirect;
                }, 500);
            } else {
                showToast(res.message, "error", 2000);
            }
            $saveBtn
                .prop("disabled", false)
                .removeClass("opacity-50 cursor-not-allowed")
                .text("Create Account");
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
            $saveBtn
                .prop("disabled", false)
                .removeClass("opacity-50 cursor-not-allowed")
                .text("Create Account");
        },
    );
});

$(document).on("submit", "#loginForm", function (e) {
    e.preventDefault();

    let $form = $(this);
    let $saveBtn = $form.find('button[type="submit"]');

    // selectors must match the actual input ids/names
    let fields = [
        {
            id: '[name="email"]',
            condition: (v) => v === "",
            message: "Email is required",
        },
        {
            id: '[name="password"]',
            condition: (v) => v === "",
            message: "Password is required",
        }
    ];

    let isValid = true;
    for (const field of fields) {
        if (!validateField(field)) isValid = false;
    }
    if (!isValid) return;

    $saveBtn
        .prop("disabled", true)
        .removeClass("opacity-50 cursor-not-allowed")
        .text("Saving....");

    let formData = new FormData(this);

    sendRequest(
        $form.attr("action"), // uses the form's action route
        formData,
        "POST",
        function (res) {
            if (res.success) {
                showToast(res.message, "success", 2000);
                setTimeout(() => {
                    window.location.href = res.redirect;
                }, 500);
            } else {
                showToast(res.message, "error", 2000);
            }
            $saveBtn
                .prop("disabled", false)
                .removeClass("opacity-50 cursor-not-allowed")
                .text("Create Account");
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
            $saveBtn
                .prop("disabled", false)
                .removeClass("opacity-50 cursor-not-allowed")
                .text("Create Account");
        },
    );
});
