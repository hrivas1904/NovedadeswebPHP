document.addEventListener("DOMContentLoaded", () => {

    const btn = document.getElementById("mobile-btn");
    const overlay = document.getElementById("nav-overlay");
    const menu = document.querySelector(".nav-links");
    const dropdowns = document.querySelectorAll(".nav-dropdown > .dropbtn");

    function closeMenu(){
        menu.classList.remove("active");
        overlay.style.display = "none";
    }

    btn.addEventListener("click", () => {
        menu.classList.toggle("active");

        if(menu.classList.contains("active")){
            overlay.style.display = "block";
        }else{
            overlay.style.display = "none";
        }
    });

    overlay.addEventListener("click", closeMenu);

    dropdowns.forEach(dropdown => {
        dropdown.addEventListener("click", (e) => {
            if (window.innerWidth <= 1300) {
                e.preventDefault();

                const parent = dropdown.parentElement;

                document.querySelectorAll(".nav-dropdown")
                    .forEach(item => item !== parent && item.classList.remove("open"));

                parent.classList.toggle("open");
            }
        });
    });

});