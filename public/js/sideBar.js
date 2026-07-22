document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileSidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');

    const isDesktop = () => window.innerWidth > 991;

    // ===== DESKTOP: expandir al pasar el mouse, colapsar al sacarlo =====
    sidebar.addEventListener('mouseenter', function () {
        if (isDesktop()) {
            sidebar.classList.add('expanded');
        }
    });

    sidebar.addEventListener('mouseleave', function () {
        if (isDesktop()) {
            sidebar.classList.remove('expanded');

            // Cerrar cualquier submenú de bootstrap que haya quedado abierto
            sidebar.querySelectorAll('.submenu.show').forEach(function (submenuEl) {
                bootstrap.Collapse.getOrCreateInstance(submenuEl).hide();
            });
        }
    });

    // ===== MOBILE: sigue siendo con click (drawer + backdrop) =====
    mobileToggle?.addEventListener('click', function () {
        sidebar.classList.toggle('expanded');
        backdrop.classList.toggle('show');
    });

    backdrop?.addEventListener('click', function () {
        sidebar.classList.remove('expanded');
        backdrop.classList.remove('show');
    });

    // Cerrar sidebar mobile al navegar (links sin submenú)
    sidebar.querySelectorAll('.nav-link:not([data-bs-toggle="collapse"])').forEach(function (link) {
        link.addEventListener('click', function () {
            if (!isDesktop()) {
                sidebar.classList.remove('expanded');
                backdrop.classList.remove('show');
            }
        });
    });

    // Limpiar estado al pasar a mobile o volver a desktop
    window.addEventListener('resize', function () {
        if (isDesktop()) {
            backdrop.classList.remove('show');
        } else {
            sidebar.classList.remove('expanded');
        }
    });

});