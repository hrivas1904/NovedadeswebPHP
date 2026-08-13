document.addEventListener("DOMContentLoaded", function () {
    const DESKTOP_BREAKPOINT = 992;
    const HOVER_CLOSE_DELAY = 150;

    const sidebar = document.getElementById("sidebar");
    const mobileToggle = document.getElementById("mobileSidebarToggle");
    const closeButton = document.getElementById("sidebarClose");
    const backdrop = document.getElementById("sidebarBackdrop");

    if (!sidebar) return;

    const isDesktop = () => window.innerWidth >= DESKTOP_BREAKPOINT;

    const submenuItems = [...sidebar.querySelectorAll(".has-submenu")];

    let hoverCloseTimer = null;

    function cancelHoverClose() {
        clearTimeout(hoverCloseTimer);
    }

    function scheduleHoverClose() {
        if (!isDesktop()) return;

        clearTimeout(hoverCloseTimer);

        hoverCloseTimer = setTimeout(() => {
            closeSidebar();
        }, HOVER_CLOSE_DELAY);
    }

    function setExpanded(expanded) {
        sidebar.classList.toggle("expanded", expanded);

        if (mobileToggle) {
            mobileToggle.setAttribute(
                "aria-expanded",
                expanded ? "true" : "false",
            );

            const icon = mobileToggle.querySelector("i");

            if (icon) {
                icon.classList.toggle("fa-bars", !expanded);
                icon.classList.toggle("fa-xmark", expanded);
            }
        }
    }

    function closeAllSubmenus(except = null) {
        submenuItems.forEach((item) => {
            if (item === except) return;

            item.classList.remove("submenu-open");

            const link = item.querySelector(":scope > .nav-link");

            if (link) {
                link.setAttribute("aria-expanded", "false");
            }
        });
    }

    function toggleSubmenu(item) {
        const open = item.classList.contains("submenu-open");

        closeAllSubmenus(item);

        item.classList.toggle("submenu-open", !open);

        const link = item.querySelector(":scope > .nav-link");

        if (link) {
            link.setAttribute("aria-expanded", !open ? "true" : "false");
        }
    }

    function openSidebar() {
        setExpanded(true);

        backdrop.classList.add("show");

        document.body.classList.add("sidebar-mobile-open");
    }

    function closeSidebar() {
        setExpanded(false);

        backdrop.classList.remove("show");

        document.body.classList.remove("sidebar-mobile-open");

        closeAllSubmenus();
    }

    /* ============================
       TOGGLE (click)
       ============================ */

    if (mobileToggle) {
        mobileToggle.addEventListener("click", () => {
            if (sidebar.classList.contains("expanded")) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (closeButton) {
        closeButton.addEventListener("click", closeSidebar);
    }

    if (backdrop) {
        backdrop.addEventListener("click", closeSidebar);
    }

    /* ============================
       HOVER (solo desktop)
       ============================ */

    if (mobileToggle) {
        mobileToggle.addEventListener("mouseenter", () => {
            if (!isDesktop()) return;

            cancelHoverClose();
            openSidebar();
        });

        mobileToggle.addEventListener("mouseleave", scheduleHoverClose);
    }

    sidebar.addEventListener("mouseenter", cancelHoverClose);
    sidebar.addEventListener("mouseleave", scheduleHoverClose);

    /* ============================
       SUBMENUS
       ============================ */

    submenuItems.forEach((item) => {
        const link = item.querySelector(":scope > .nav-link");
        if (!link) return;

        link.addEventListener("click", function (e) {
            e.preventDefault();
            toggleSubmenu(item);
        });
    });

    /* ============================
       CERRAR DRAWER AL NAVEGAR
       ============================ */

    sidebar
        .querySelectorAll(
            ".nav-item:not(.has-submenu) > .nav-link, .submenu-link",
        )
        .forEach((link) => {
            link.addEventListener("click", () => {
                closeSidebar();
            });
        });

    /* ============================
       ESC
       ============================ */

    document.addEventListener("keydown", function (e) {
        if (e.key !== "Escape") return;

        if (sidebar.classList.contains("expanded")) {
            closeSidebar();

            if (mobileToggle) {
                mobileToggle.focus();
            }

            return;
        }

        closeAllSubmenus();
    });

    /* ============================
       TOOLTIPS AUTOMÁTICOS
       ============================ */

    sidebar
        .querySelectorAll(".sidebar-nav .nav-link, .sidebar-footer .nav-link")
        .forEach((link) => {
            const text = link.querySelector(".link-text");

            if (!text) return;

            const value = text.textContent.trim();

            if (value !== "" && !link.hasAttribute("aria-label")) {
                link.setAttribute("aria-label", value);
            }
        });

    /* ============================
       ESTADO INICIAL
       ============================ */

    setExpanded(false);

    closeAllSubmenus();
});

$("#divBtnHome").on("click",function(){
    window.location.href = "/index";
})