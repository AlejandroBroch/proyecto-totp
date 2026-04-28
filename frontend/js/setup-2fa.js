const form = document.querySelector("form");

form.addEventListener("submit", function (event) {
    const code = document.getElementById("code").value.trim();

    if (code.length !== 6 || !/^\d+$/.test(code)) {
        event.preventDefault();
        alert("El código de autenticación debe ser un número de 6 dígitos.");
    }
});