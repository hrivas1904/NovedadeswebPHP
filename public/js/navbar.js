/*document.addEventListener("DOMContentLoaded", function() {
    const mobileBtn = document.getElementById("mobile-btn");
    const navLinks = document.querySelector(".nav-links");

    if (mobileBtn) {
        mobileBtn.addEventListener("click", function() {
            navLinks.classList.toggle("active");
            // Aquí puedes animar el icono de menú a una X si deseas
        });
    }

    // Cerrar dropdowns si se hace click afuera
    window.addEventListener("click", function(e) {
        if (!e.target.matches('.dropbtn')) {
            const dropdowns = document.getElementsByClassName("dropdown-content");
            for (let i = 0; i < dropdowns.length; i++) {
                let openDropdown = dropdowns[i];
                if (openDropdown.style.display === "block") {
                    openDropdown.style.display = "none";
                }
            }
        }
    });
});*/
document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("mobile-btn");
    const menu = document.querySelector(".nav-links");

    btn.addEventListener("click", () => {
        menu.classList.toggle("active");
    });
});
