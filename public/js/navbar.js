document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("mobile-btn");
    const overlay = document.getElementById("nav-overlay");
    const menu = document.querySelector(".nav-links");
    const dropdowns = document.querySelectorAll(".nav-dropdown > .dropbtn");

    function closeMenu() {
        menu.classList.remove("active");
        overlay.style.display = "none";
    }

    btn.addEventListener("click", () => {
        menu.classList.toggle("active");

        if (menu.classList.contains("active")) {
            overlay.style.display = "block";
        } else {
            overlay.style.display = "none";
        }
    });

    overlay.addEventListener("click", closeMenu);

    dropdowns.forEach((dropdown) => {
        dropdown.addEventListener("click", (e) => {
            if (window.innerWidth <= 1300) {
                e.preventDefault();

                const parent = dropdown.parentElement;

                document
                    .querySelectorAll(".nav-dropdown")
                    .forEach(
                        (item) =>
                            item !== parent && item.classList.remove("open"),
                    );

                parent.classList.toggle("open");
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggleTheme");
    const icon = document.getElementById("themeIcon");

    const prefersDark = window.matchMedia(
        "(prefers-color-scheme: dark)",
    ).matches;
    const savedTheme = localStorage.getItem("theme");

    const theme = savedTheme || (prefersDark ? "dark" : "light");

    document.body.setAttribute("data-theme", theme);

    function updateIcon(theme) {
        if (theme === "dark") {
            icon.classList.remove("fa-moon");
            icon.classList.add("fa-sun");
        } else {
            icon.classList.remove("fa-sun");
            icon.classList.add("fa-moon");
        }
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
