document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("incomeModal");
  const form = document.getElementById("incomeForm");
  const toast = document.getElementById("toast");
  const deleteModal = document.getElementById("deleteIncomeModal");
  const confirmDeleteBtn = document.getElementById("confirmDeleteIncomeBtn");
  const cancelDeleteBtn = document.getElementById("cancelDeleteIncomeBtn");

  document.querySelectorAll(".kebab-toggle").forEach((toggle) => {
    toggle.addEventListener("click", (e) => {
      const menu = toggle.nextElementSibling;
      document.querySelectorAll(".kebab-menu").forEach((m) => {
        if (m !== menu) m.style.display = "none";
      });
      menu.style.display = menu.style.display === "flex" ? "none" : "flex";
    });
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".income-actions")) {
      document.querySelectorAll(".kebab-menu").forEach((menu) => {
        menu.style.display = "none";
      });
    }
  });

  const statusFilter = document.getElementById("statusFilter");
  const sortOrder = document.getElementById("sortOrder");
  const incomeList = document.getElementById("incomeList");

  function getStatus(card) {
    return card
      .querySelector(".badge-status")
      ?.textContent.trim()
      .toLowerCase();
  }

  function getDate(card) {
    const date = card.querySelector(".badge-date")?.textContent.trim();
    return new Date(date);
  }

  function getAmount(card) {
    return parseFloat(
      card.querySelector("strong").textContent.replace("$", "")
    );
  }

  function filterAndSort() {
    const status = statusFilter.value;
    const sort = sortOrder.value;
    const cards = Array.from(incomeList.querySelectorAll(".income-card"));

    cards.forEach((card) => card.classList.remove("hidden"));

    if (status !== "all") {
      cards.forEach((card) => {
        if (getStatus(card) !== status) {
          card.classList.add("hidden");
        }
      });
    }

    const visibleCards = cards.filter((c) => !c.classList.contains("hidden"));

    visibleCards.sort((a, b) => {
      const dateA = getDate(a),
        dateB = getDate(b);
      const amountA = getAmount(a),
        amountB = getAmount(b);
      switch (sort) {
        case "date_desc":
          return dateB - dateA;
        case "date_asc":
          return dateA - dateB;
        case "amount_desc":
          return amountB - amountA;
        case "amount_asc":
          return amountA - amountB;
        default:
          return 0;
      }
    });

    visibleCards.forEach((card) => incomeList.appendChild(card));
  }

  statusFilter.addEventListener("change", filterAndSort);
  sortOrder.addEventListener("change", filterAndSort);

  let deleteIncomeId = null;

  document
    .getElementById("addIncomeBtn")
    .addEventListener("click", () => openModal());

  document
    .getElementById("cancelIncomeModal")
    .addEventListener("click", closeModal);

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const res = await fetch("income.php", { method: "POST", body: formData });
    const json = await res.json();
    if (json.success) {
      showToast(json.success);
      setTimeout(() => location.reload(), 800);
    } else {
      showToast(json.error, true);
    }
  });

  document.querySelectorAll(".edit").forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".income-card");
      const data = {
        id: btn.dataset.id,
        amount: card.querySelector("strong").textContent.replace("$", ""),
        description: card.querySelector("p:last-of-type")?.textContent.trim(),
        date: card.querySelector(".badge-date")?.textContent.trim(),
        status: card.querySelector(".badge-status")?.textContent.trim(),
      };
      openModal(true, data);
    });
  });

  document.querySelectorAll(".delete").forEach((btn) => {
    btn.addEventListener("click", () => {
      deleteIncomeId = btn.dataset.id;
      deleteModal.classList.add("show");
    });
  });

  confirmDeleteBtn.addEventListener("click", async () => {
    if (!deleteIncomeId) return;
    const res = await fetch("income.php", {
      method: "POST",
      body: new URLSearchParams({
        action: "delete",
        income_id: deleteIncomeId,
      }),
    });
    const json = await res.json();
    if (json.success) {
      document
        .querySelector(`.income-card[data-id="${deleteIncomeId}"]`)
        ?.remove();
      showToast(json.success);
    } else {
      showToast(json.error, true);
    }
    deleteModal.classList.remove("show");
    deleteIncomeId = null;
  });

  cancelDeleteBtn.addEventListener("click", () => {
    deleteIncomeId = null;
    deleteModal.classList.remove("show");
  });

  deleteModal.addEventListener("click", (e) => {
    if (e.target === deleteModal) {
      deleteModal.classList.remove("show");
      deleteIncomeId = null;
    }
  });

  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  function openModal(isEdit = false, data = {}) {
    form.reset();
    modal.classList.add("show");
    form.action.value = isEdit ? "edit" : "add";
    form.income_id.value = data.id || "";
    form.amount.value = data.amount || "";
    form.description.value = data.description || "";
    form.date.value = data.date || new Date().toISOString().split("T")[0];
    form.status.value = data.status?.toLowerCase() || "pending";
    document.getElementById("modalTitle").textContent = isEdit
      ? "Edit Income"
      : "New Income";
  }

  function closeModal() {
    modal.classList.remove("show");
    form.reset();
  }

  function showToast(message, isError = false) {
    toast.textContent = message;
    toast.className = `toast ${isError ? "toast-error" : "toast-success"}`;
    toast.style.opacity = "1";
    toast.style.display = "block";
    setTimeout(() => {
      toast.style.opacity = "0";
      setTimeout(() => {
        toast.style.display = "none";
        toast.className = "toast";
      }, 400);
    }, 2500);
  }
});
