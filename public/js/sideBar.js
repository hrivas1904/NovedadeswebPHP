document.addEventListener("DOMContentLoaded", function () {
    const DESKTOP_BREAKPOINT = 992;

    const sidebar = document.getElementById("sidebar");
    const mobileToggle = document.getElementById("mobileSidebarToggle");
    const closeButton = document.getElementById("sidebarClose");
    const backdrop = document.getElementById("sidebarBackdrop");

    if (!sidebar) return;

    const isDesktop = () => window.innerWidth >= DESKTOP_BREAKPOINT;

    const submenuItems = [...sidebar.querySelectorAll(".has-submenu")];

    function setExpanded(expanded) {
        sidebar.classList.toggle("expanded", expanded);

        if (mobileToggle) {
            mobileToggle.setAttribute(
                "aria-expanded",
                expanded ? "true" : "false",
            );
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

    function openSidebarMobile() {
        if (isDesktop()) return;

        setExpanded(true);

        backdrop.classList.add("show");

        document.body.classList.add("sidebar-mobile-open");
    }

    function closeSidebarMobile() {
        if (isDesktop()) return;

        setExpanded(false);

        backdrop.classList.remove("show");

        document.body.classList.remove("sidebar-mobile-open");

        closeAllSubmenus();
    }

    /* ============================
       DESKTOP
       ============================ */

    sidebar.addEventListener("mouseenter", () => {
        if (!isDesktop()) return;

        setExpanded(true);
    });

    sidebar.addEventListener("mouseleave", () => {
        if (!isDesktop()) return;

        setExpanded(false);

        closeAllSubmenus();
    });

    /* ============================
       MOBILE
       ============================ */

    if (mobileToggle) {
        mobileToggle.addEventListener("click", () => {
            if (sidebar.classList.contains("expanded")) {
                closeSidebarMobile();
            } else {
                openSidebarMobile();
            }
        });
    }

    if (closeButton) {
        closeButton.addEventListener("click", closeSidebarMobile);
    }

    if (backdrop) {
        backdrop.addEventListener("click", closeSidebarMobile);
    }

    /* ============================
       SUBMENUS
       ============================ */

    submenuItems.forEach((item) => {
        const link = item.querySelector(":scope > .nav-link");

        if (!link) return;

        // SOLO MOBILE
        link.addEventListener("click", function (e) {
            if (isDesktop()) return;

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
                if (!isDesktop()) {
                    closeSidebarMobile();
                }
            });
        });

    /* ============================
       ESC
       ============================ */

    document.addEventListener("keydown", function (e) {
        if (e.key !== "Escape") return;

        if (!isDesktop()) {
            if (sidebar.classList.contains("expanded")) {
                closeSidebarMobile();

                if (mobileToggle) {
                    mobileToggle.focus();
                }

                return;
            }
        }

        closeAllSubmenus();
    });

    /* ============================
       RESIZE
       ============================ */

    let desktopState = isDesktop();

    window.addEventListener("resize", function () {
        const current = isDesktop();

        if (current === desktopState) return;

        desktopState = current;

        closeAllSubmenus();

        backdrop.classList.remove("show");

        document.body.classList.remove("sidebar-mobile-open");

        setExpanded(false);
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
