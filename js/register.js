const form = document.querySelector("form");

form.addEventListener("submit", function (event) {
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;

    if (password.length < 8) {
        event.preventDefault();
        alert("La contraseña debe tener al menos 8 caracteres.");
    } else if (password !== confirmPassword) {
        event.preventDefault();
        alert("Las contraseñas no coinciden.");
    }
});