document.addEventListener("DOMContentLoaded", function () {

    const toggleBtn = document.getElementById("toggleTheme");
    const icon = document.getElementById("themeIcon");

    // 🔒 evitar errores si no existe
    if (!toggleBtn || !icon) return;

    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    const savedTheme = localStorage.getItem("theme");

    const theme = savedTheme || (prefersDark ? "dark" : "light");

    document.body.setAttribute("data-theme", theme);

    function updateIcon(theme) {
        icon.classList.toggle("fa-sun", theme === "dark");
        icon.classList.toggle("fa-moon", theme !== "dark");
    }

    updateIcon(theme);

    toggleBtn.addEventListener("click", function () {

        let currentTheme = document.body.getAttribute("data-theme");
        let newTheme = currentTheme === "light" ? "dark" : "light";

        document.body.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);

        updateIcon(newTheme);
    });

});

function swalThemeConfig() {
    const isDark = document.body.getAttribute("data-theme") === "dark";

    return {
        background: isDark ? "#1e293b" : "#ffffff",
        color: isDark ? "#e2e8f0" : "#1e293b",
        confirmButtonColor: "#00b18d",
        cancelButtonColor: "#004a7c"
    };
}