const toggleBtn = document.getElementById("toggleBtn");
const sidebar = document.getElementById("sidebar");

function toggleSidebar() {
    sidebar.classList.toggle("show");
    sidebar.classList.toggle("hidden");
}

toggleBtn.addEventListener("click", toggleSidebar);

function handleResize() {
    sidebar.classList.add("hidden");
}

window.addEventListener("load", handleResize);
