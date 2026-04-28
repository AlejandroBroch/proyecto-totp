const form = document.querySelector("form");

form.addEventListener("submit", function (event) {
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    if (email === "") {
        event.preventDefault();
        alert("Debes introducir un correo electrónico.");
    } else if (password === "") {
        event.preventDefault();
        alert("Debes introducir una contraseña.");
    } else if (password.length < 8) {
        event.preventDefault();
        alert("La contraseña debe tener al menos 8 caracteres.");
    }
});