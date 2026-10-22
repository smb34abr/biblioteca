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
