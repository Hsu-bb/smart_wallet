document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("payModal");
  const form = document.getElementById("payForm");
  const error = document.getElementById("formError");
  const cancelBtn = document.getElementById("cancelPay");

  const historyModal = document.getElementById("historyModal");
  const historyList = document.getElementById("historyList");
  const closeHistory = document.getElementById("closeHistory");

  const debtModal = document.getElementById("debtModal");
  const debtForm = document.getElementById("debtForm");
  const addDebtBtn = document.getElementById("addDebtBtn");
  const cancelDebtBtn = document.getElementById("cancelDebt");

  document.querySelectorAll(".pay").forEach((btn) => {
    btn.addEventListener("click", () => {
      form.debt_id.value = btn.dataset.id;
      form.payment_date.value = new Date().toISOString().split("T")[0];
      modal.classList.add("show");
    });
  });

  cancelBtn.addEventListener("click", () => {
    modal.classList.remove("show");
    form.reset();
    error.textContent = "";
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const res = await fetch("debt.php", {
      method: "POST",
      body: new URLSearchParams(formData),
    });
    const json = await res.json();
    if (json.error) {
      error.textContent = json.error;
    } else {
      modal.classList.remove("show");
      form.reset();
      location.reload();
    }
  });

  document.querySelectorAll(".history").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const res = await fetch("debt.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "history",
          debt_id: btn.dataset.id,
        }),
      });
      const data = await res.json();
      historyList.innerHTML = data.length
        ? data
            .map(
              (p) =>
                `<li>$${parseFloat(p.amount).toFixed(2)} on ${p.payment_date}${
                  p.note ? ` - ${p.note}` : ""
                }</li>`
            )
            .join("")
        : "<li>No payments recorded.</li>";

      historyModal.classList.add("show");
    });
  });

  closeHistory.addEventListener("click", () => {
    historyModal.classList.remove("show");
  });

  addDebtBtn.addEventListener("click", () => {
    debtForm.reset();
    debtForm.querySelector("input[name='action']").value = "create";
    debtForm.querySelector("input[name='id']").value = "";
    debtModal.classList.add("show");
  });

  document.querySelectorAll(".kebab-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      document
        .querySelectorAll(".kebab-dropdown")
        .forEach((el) => el.classList.remove("show"));
      btn.nextElementSibling.classList.toggle("show");
    });
  });

  document.addEventListener("click", () => {
    document
      .querySelectorAll(".kebab-dropdown")
      .forEach((el) => el.classList.remove("show"));
  });

  document.querySelectorAll(".edit").forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".card");
      const id = card.dataset.id;
      const title = card.querySelector("h2").textContent.trim();
      const amount = parseFloat(
        card
          .querySelector("p:nth-of-type(1)")
          .textContent.replace(/[^\d.]/g, "")
      );
      const due_date = card
        .querySelector("p:nth-of-type(3)")
        .textContent.trim();
      const status = card.querySelector(".type-label").textContent.trim();

      let idInput = debtForm.querySelector("input[name='id']");
      if (!idInput) {
        idInput = document.createElement("input");
        idInput.name = "id";
        idInput.type = "hidden";
        debtForm.appendChild(idInput);
      }
      idInput.value = id;

      debtForm.reset();
      debtForm.querySelector("input[name='action']").value = "update";
      debtForm.querySelector("input[name='id']").value = id;
      debtForm.title.value = title;
      debtForm.amount.value = amount;
      debtForm.due_date.value = due_date;
      debtForm.status.value = status;

      debtModal.classList.add("show");
    });
  });

  debtForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(debtForm);
    const title = formData.get("title").trim();
    const amount = parseFloat(formData.get("amount"));
    const dueDate = new Date(formData.get("due_date"));
    const today = new Date();

    if (!title || title.length < 3) {
      alert("Title is required and should be at least 3 characters.");
      return;
    }
    if (isNaN(amount) || amount <= 0) {
      alert("Amount must be a positive number.");
      return;
    }
    if (dueDate < today.setHours(0, 0, 0, 0)) {
      alert("Due date cannot be in the past.");
      return;
    }

    const response = await fetch("debt.php", {
      method: "POST",
      body: new URLSearchParams(formData),
    });

    const result = await response.json();
    if (result.success) {
      debtForm.reset();
      debtModal.classList.remove("show");

      if (formData.get("action") === "update") {
        showToast("Debt updated!");
      } else {
        showToast("Debt added successfully!");
      }

      setTimeout(() => location.reload(), 1000);
    }
  });

  cancelDebtBtn.addEventListener("click", () => {
    debtForm.reset();
    debtModal.classList.remove("show");
  });

  const deleteModal = document.getElementById("deleteModal");
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
  const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
  const deleteInfo = document.getElementById("deleteInfo");

  let deleteDebtId = null;

  document.querySelectorAll(".delete").forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".card");
      deleteDebtId = card.dataset.id;
      const title = card.querySelector("h2")?.textContent.trim();
      deleteInfo.textContent = `Are you sure you want to delete "${title}"?`;
      deleteModal.classList.add("show");
    });
  });

  confirmDeleteBtn.addEventListener("click", async () => {
    if (!deleteDebtId) return;

    const res = await fetch("debt.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ action: "delete", id: deleteDebtId }),
    });

    const json = await res.json();
    if (json.success) {
      deleteModal.classList.remove("show");
      deleteDebtId = null;
      showToast("Debt deleted!");
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast(json.error || "Delete failed.", true);
    }
  });

  cancelDeleteBtn.addEventListener("click", () => {
    deleteModal.classList.remove("show");
    deleteDebtId = null;
  });
});
