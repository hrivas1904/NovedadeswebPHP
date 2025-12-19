window.onload = function(){
    const sidebar = document.querySelector(".sidebar");
    const closeBtn = document.querySelector("#btn");
    const searchBtn = document.querySelector(".bx-search");

    // Solo si existen el sidebar y el botón de cerrar
    if (sidebar && closeBtn) {
        closeBtn.addEventListener("click", function(){
            sidebar.classList.toggle("open");
            menuBtnChange();
        });
    }

    // Solo si existe el botón de búsqueda
    if (sidebar && searchBtn) {
        searchBtn.addEventListener("click", function(){
            sidebar.classList.toggle("open");
            menuBtnChange();
        });
    }

    function menuBtnChange(){
        if(sidebar && sidebar.classList.contains("open")){
            closeBtn.classList.replace("bx-menu","bx-menu-alt-right");
        } else if (closeBtn) {
            closeBtn.classList.replace("bx-menu-alt-right","bx-menu");
        }
    }

    // El error de toggleBtn que veías en consola era porque
    // intentabas usar 'toggleBtn' sin haberlo definido antes.
    const toggleBtn = document.querySelector('#toggleBtn'); // Ajusta el ID si es otro
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            if (!sidebar.classList.contains('open')) {
                document.querySelectorAll('.submenu input').forEach(i => i.checked = false);
            }
        });
    }
};

// Para los submenús
document.querySelectorAll('.submenu input').forEach(input => {
    input.addEventListener('change', () => {
        if (input.checked) {
            document.querySelectorAll('.submenu input').forEach(other => {
                if (other !== input) other.checked = false;
            });
        }
    });
});