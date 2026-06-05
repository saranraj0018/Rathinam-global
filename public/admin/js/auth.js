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

function openDeclaration() {
    // Reset state every time modal opens
    $("#declAgree").prop("checked", false);
    $("#declAgreeError").addClass("hidden");
    $("#declConfirmBtn").prop("disabled", false).text("Confirm & Continue");

    $("#declarationModal").removeClass("hidden").css("display", "flex");
}

function closeDeclaration() {
    $("#declarationModal").addClass("hidden").css("display", "");
}

// Confirm & Submit
$(document).on("click", "#declConfirmBtn", function () {
    // Validation — checkbox is required
    if (!$("#declAgree").is(":checked")) {
        $("#declAgreeError").removeClass("hidden");
        $("#declAgree")
            .closest("label")
            .addClass("border border-red-300 rounded-lg px-2 py-1 bg-red-50");
        return;
    }

    // Hide error if previously shown
    $("#declAgreeError").addClass("hidden");
    $("#declAgree")
        .closest("label")
        .removeClass("border border-red-300 rounded-lg px-2 py-1 bg-red-50");

    let $btn = $(this);
    $btn.prop("disabled", true).text("Please wait...");

    sendRequest(
        DECLARATION_CONFIRM_URL,
        { agreed: 1, _token: CSRF_TOKEN },
        "POST",
        function (res) {
            if (res.success) {
                showToast(res.message, "success", 1500);
                setTimeout(() => {
                    window.location.href = res.redirect; // ← redirect to application
                }, 500);
            } else {
                showToast(res.message, "error", 2000);
                $btn.prop("disabled", false).text("Confirm & Continue");
            }
        },
        function (err) {
            showToast(err.message || "Unexpected error", "error", 2000);
            $btn.prop("disabled", false).text("Confirm & Continue");
        },
    );
});

// Clear error when user checks the checkbox
$(document).on("change", "#declAgree", function () {
    if (this.checked) {
        $("#declAgreeError").addClass("hidden");
        $(this)
            .closest("label")
            .removeClass(
                "border border-red-300 rounded-lg px-2 py-1 bg-red-50",
            );
    }
});
