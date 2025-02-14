document.addEventListener("DOMContentLoaded", function () {
    function toggleForms(formType) {
        document.getElementById("login-form").style.display = formType === "login" ? "block" : "none";
        document.getElementById("register-form").style.display = formType === "register" ? "block" : "none";
    }

    document.querySelector("#register-form form").addEventListener("submit", function (event) {
        let name = document.getElementById("register-name").value.trim();
        let email = document.getElementById("register-email").value.trim();
        let password = document.getElementById("register-password").value.trim();
        let confirm_password = document.getElementById("register-confirm-password").value.trim();

        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let errors = [];

        console.log("Password:", password);
        console.log("Confirm Password:", confirm_password);
        console.log("Match:", password === confirm_password);

        if (name === "") {
            errors.push("Full name is required.");
        }

        if (!emailPattern.test(email)) {
            errors.push("Enter a valid email address.");
        }

        if (password.length < 8) {
            errors.push("Password must be at least 8 characters long.");
        }

        if (password !== confirm_password) {
            errors.push("Passwords do not match.");
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
            event.preventDefault();
            toggleForms("register"); // Keeps the register form open
        }
    });

    window.toggleForms = toggleForms;
});
