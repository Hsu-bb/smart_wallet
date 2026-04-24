function openModal() {
  document.getElementById("expenseModal").style.display = "flex";
}
function closeModal() {
  document.getElementById("expenseModal").style.display = "none";
}

document.addEventListener("DOMContentLoaded", () => {
  const anchor = window.location.hash;
  if (anchor === "#expense-history") {
    const el = document.getElementById("expense-history");
    if (el) el.scrollIntoView({ behavior: "smooth" });
  }

  document.querySelectorAll(".kebab-toggle").forEach((btn) => {
    btn.addEventListener("click", () => {
      const menu = btn.nextElementSibling;
      menu.classList.toggle("show");
    });
  });

  document.addEventListener("click", (e) => {
    if (!e.target.matches(".kebab-toggle")) {
      document
        .querySelectorAll(".kebab-menu")
        .forEach((menu) => menu.classList.remove("show"));
    }
  });

  document.querySelectorAll(".edit-expense").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      const form = document.getElementById("editExpenseForm");
      const row = btn.closest(".expense-row");
      form.expense_id.value = id;
      form.amount.value = row.dataset.amount;
      form.description.value = row.dataset.description;
      form.expense_date.value = row.dataset.date;
      document.getElementById("editModal").style.display = "flex";
    });
  });

  document.querySelectorAll(".delete-expense").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (confirm("Are you sure you want to delete this expense?")) {
        const id = btn.dataset.id;
        const url = new URL(window.location.href);
        url.searchParams.set("delete_id", id);
        window.location.href = url.toString();
      }
    });
  });

  document.querySelectorAll(".close-btn").forEach((x) => {
    x.onclick = () => (x.closest(".modal").style.display = "none");
  });
});
