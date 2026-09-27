/* =========================================================
   SIDEBAR
========================================================= */

function initSidebar() {
    const app = document.getElementById("app");
    const sidebar = document.querySelector(".sidebar");
    const toggle = document.getElementById("sidebarToggle");
    const close = document.getElementById("sidebarClose");
    const overlay = document.getElementById("sidebarOverlay");

    if (!app || !sidebar || !toggle) {
        return;
    }

    const mobileBreakpoint = 760;

    function isMobile() {
        return window.innerWidth <= mobileBreakpoint;
    }

    /* =====================================================
       MOBILE SIDEBAR
    ===================================================== */

    function openMobileSidebar() {

        if (!isMobile()) {
            return;
        }

        sidebar.classList.add("open");

        if (overlay) {
            overlay.classList.add("open");
        }

        toggle.setAttribute(
            "aria-expanded",
            "true"
        );
    }

    function closeMobileSidebar() {

        sidebar.classList.remove("open");

        if (overlay) {
            overlay.classList.remove("open");
        }

        toggle.setAttribute(
            "aria-expanded",
            "false"
        );
    }


    /* =====================================================
       DESKTOP SIDEBAR
    ===================================================== */

    function toggleDesktopSidebar() {

        if (isMobile()) {
            return;
        }

        app.classList.toggle(
            "sidebar-collapsed"
        );

        const collapsed =
            app.classList.contains(
                "sidebar-collapsed"
            );

        toggle.setAttribute(
            "aria-expanded",
            collapsed ? "false" : "true"
        );

        localStorage.setItem(
            "crm_sidebar_collapsed",
            collapsed ? "true" : "false"
        );
    }


    /* =====================================================
       MAIN TOGGLE
    ===================================================== */

    function handleToggle() {

        if (isMobile()) {

            if (
                sidebar.classList.contains(
                    "open"
                )
            ) {

                closeMobileSidebar();

            } else {

                openMobileSidebar();

            }

            return;
        }

        toggleDesktopSidebar();
    }


    /* =====================================================
       TOGGLE BUTTON
    ===================================================== */

    toggle.addEventListener(
        "click",
        handleToggle
    );


    /* =====================================================
       MOBILE CLOSE BUTTON
    ===================================================== */

    if (close) {

        close.addEventListener(
            "click",
            () => {

                closeMobileSidebar();

            }
        );

    }


    /* =====================================================
       MOBILE OVERLAY
    ===================================================== */

    if (overlay) {

        overlay.addEventListener(
            "click",
            () => {

                closeMobileSidebar();

            }
        );

    }


    /* =====================================================
       RESTORE DESKTOP SIDEBAR STATE
    ===================================================== */

    const savedState =
        localStorage.getItem(
            "crm_sidebar_collapsed"
        );

    if (
        !isMobile() &&
        savedState === "true"
    ) {

        app.classList.add(
            "sidebar-collapsed"
        );

        toggle.setAttribute(
            "aria-expanded",
            "false"
        );

    }


    /* =====================================================
       ADD TOOLTIP LABEL
    ===================================================== */

    document
        .querySelectorAll(
            ".nav a"
        )
        .forEach((item) => {

            const text =
                item.textContent.trim();

            if (text) {

                item.setAttribute(
                    "data-label",
                    text
                );

            }

        });


    /* =====================================================
       HANDLE WINDOW RESIZE
    ===================================================== */

    window.addEventListener(
        "resize",
        () => {

            if (!isMobile()) {

                /*
                 * Desktop mode
                 */

                sidebar.classList.remove(
                    "open"
                );

                if (overlay) {

                    overlay.classList.remove(
                        "open"
                    );

                }

                const collapsed =
                    app.classList.contains(
                        "sidebar-collapsed"
                    );

                toggle.setAttribute(
                    "aria-expanded",
                    collapsed
                        ? "false"
                        : "true"
                );

            } else {

                /*
                 * Mobile mode
                 *
                 * Desktop collapsed state
                 * tidak boleh mempengaruhi
                 * mobile sidebar.
                 */

                app.classList.remove(
                    "sidebar-collapsed"
                );

            }

        }
    );
}


/* =========================================================
   ACTIVE NAVIGATION
========================================================= */

function setActive(key) {

    document
        .querySelectorAll(".nav a")
        .forEach((a) => {

            a.classList.remove(
                "active"
            );

        });

    const activeItem =
        document.querySelector(
            `[data-nav="${key}"]`
        );

    if (activeItem) {

        activeItem.classList.add(
            "active"
        );

    }
}


/* =========================================================
   NOTIFICATION
========================================================= */

function notify(msg) {

    const n =
        document.createElement(
            "div"
        );

    n.textContent = msg;

    n.style.cssText = `
        position: fixed;
        right: 22px;
        bottom: 22px;
        background: #17284f;
        color: #fff;
        padding: 12px 16px;
        border-radius: 10px;
        z-index: 99;
        font-size: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
    `;

    document.body.appendChild(n);

    setTimeout(
        () => {
            n.remove();
        },
        2200
    );
}


/* =========================================================
   MODAL
========================================================= */

function openForm(title) {

    const modalTitle =
        document.getElementById(
            "modalTitle"
        );

    const modal =
        document.getElementById(
            "modal"
        );

    if (modalTitle) {

        modalTitle.textContent =
            "Create " + title;

    }

    if (modal) {

        modal.classList.add(
            "open"
        );

    }
}


function closeModal() {

    document
        .getElementById("modal")
        ?.classList.remove(
            "open"
        );
}


/* =========================================================
   TABLE FILTER
========================================================= */

function filterTable(input) {

    const table =
        input
            .closest(".card-body")
            ?.querySelector("tbody") ||
        document.querySelector(
            "tbody"
        );

    if (!table) {
        return;
    }

    const q =
        input.value
            .toLowerCase()
            .trim();

    table
        .querySelectorAll("tr")
        .forEach((row) => {

            row.style.display =
                row.innerText
                    .toLowerCase()
                    .includes(q)
                    ? ""
                    : "none";

        });
}


/* =========================================================
   APPLICATION INITIALIZATION
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    () => {

        initSidebar();

    }
);