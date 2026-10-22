// Confirmación antes de acciones destructivas
document.addEventListener("click", e => {
  const b = e.target.closest("[data-confirm]");
  if (b && !confirm(b.dataset.confirm)) e.preventDefault();
});
// Filtro instantáneo de tablas
document.querySelectorAll("[data-filter]").forEach(inp => {
  inp.addEventListener("input", () => {
    const t = inp.value.toLowerCase();
    document.querySelectorAll(inp.dataset.filter + " tr:not(:first-child)").forEach(tr => {
      tr.hidden = !tr.textContent.toLowerCase().includes(t);
    });
  });
});

const shell = document.body;
const menuToggle = document.querySelector("[data-menu-toggle]");
const menuClose = document.querySelector("[data-menu-close]");
if (menuToggle) {
  const setMenuOpen = open => {
    shell.classList.toggle("menu-open", open);
    menuToggle.setAttribute("aria-expanded", String(open));
  };
  menuToggle.addEventListener("click", () => setMenuOpen(!shell.classList.contains("menu-open")));
  menuClose?.addEventListener("click", () => setMenuOpen(false));
  document.addEventListener("keydown", event => {
    if (event.key === "Escape") setMenuOpen(false);
  });
}
