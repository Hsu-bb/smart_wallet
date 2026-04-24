document.addEventListener("DOMContentLoaded", () => {
  const mobileToggle = document.getElementById("mobileToggle");
  const sidebar = document.querySelector(".sidebar");
  const content = document.querySelector(".content");
  const toggle = document.getElementById("sidebarToggle");
  const overlay = document.getElementById("overlay");

  if (mobileToggle && sidebar) {
    mobileToggle.addEventListener("click", () => {
      sidebar.classList.toggle("open");
    });

    document.addEventListener("click", (e) => {
      if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
        sidebar.classList.remove("open");
      }
    });
  }

  if (sidebar) {
    sidebar.classList.add("collapsed");
  }

  if (toggle && sidebar) {
    toggle.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      if (window.innerWidth <= 992) {
        document.body.classList.toggle("sidebar-open");
      }
    });
  }

  if (overlay) {
    overlay.addEventListener("click", () => {
      document.body.classList.remove("sidebar-open");
    });
  }

  if (window.innerWidth <= 992 && toggle) {
    toggle.onclick = function () {
      document.body.classList.toggle("sidebar-open");
    };
  }
});
