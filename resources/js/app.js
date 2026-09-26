function toggleSidebar() {
    document.getElementById("sidebar")?.classList.toggle("open");
}

function setActive(key) {
    document
        .querySelectorAll(".nav a")
        .forEach((a) => a.classList.remove("active"));
    const x = document.querySelector(`[data-nav="${key}"]`);
    if (x) x.classList.add("active");
}

function notify(msg) {
    const n = document.createElement("div");
    n.textContent = msg;
    n.style.cssText =
        "position:fixed;right:22px;bottom:22px;background:#17284f;color:#fff;padding:12px 16px;border-radius:10px;z-index:99;font-size:12px;box-shadow:0 8px 24px rgba(0,0,0,.2)";
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 2200);
}

function openForm(title) {
    document.getElementById("modalTitle").textContent = "Create " + title;
    document.getElementById("modal").classList.add("open");
}

function closeModal() {
    document.getElementById("modal")?.classList.remove("open");
}

function filterTable(input) {
    const table =
        input.closest(".card-body")?.querySelector("tbody") ||
        document.querySelector("tbody");
    if (!table) return;
    const q = input.value.toLowerCase();
    table
        .querySelectorAll("tr")
        .forEach(
            (r) =>
                (r.style.display = r.innerText.toLowerCase().includes(q)
                    ? ""
                    : "none"),
        );
}

document.addEventListener("DOMContentLoaded", () => {

    const sidebar = document.querySelector(".sidebar");
    const toggle = document.getElementById("sidebarToggle");
    const close = document.getElementById("sidebarClose");
    const overlay = document.getElementById("sidebarOverlay");

    if (!sidebar) {
        return;
    }

    function openSidebar() {
        sidebar.classList.add("open");

        if (overlay) {
            overlay.classList.add("open");
        }
    }

    function closeSidebar() {
        sidebar.classList.remove("open");

        if (overlay) {
            overlay.classList.remove("open");
        }
    }

    if (toggle) {
        toggle.addEventListener("click", () => {

            if (sidebar.classList.contains("open")) {
                closeSidebar();
            } else {
                openSidebar();
            }

        });
    }

    if (close) {
        close.addEventListener("click", () => {
            closeSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener("click", () => {
            closeSidebar();
        });
    }

});