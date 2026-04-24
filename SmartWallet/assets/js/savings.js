document.addEventListener("DOMContentLoaded", () => {
  const goalModal = document.getElementById("goalModal");
  const addGoalBtn = document.getElementById("addGoalBtn");
  const cancelGoalModal = document.getElementById("cancelGoalModal");

  const deleteModal = document.getElementById("deleteModal");
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
  const cancelDeleteModal = document.getElementById("cancelDeleteModal");

  const saveModal = document.getElementById("saveModal");
  const saveForm = document.getElementById("saveForm");
  const saveAmountInput = document.getElementById("saveAmount");
  const saveDateInput = document.getElementById("historyDate");
  const saveNotesInput = document.getElementById("historyNotes");
  const closeSaveModal = document.getElementById("closeSaveModal");

  const historyModal = document.getElementById("historyModal");
  const closeHistoryBtn = document.getElementById("closeHistory");

  let currentGoalId = null;
  let deleteGoalId = null;

  function closeAllDropdowns() {
    document
      .querySelectorAll(".kebab-menu-wrapper")
      .forEach((w) => w.classList.remove("active"));
  }

  function showToast(message = "Success!") {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 2500);
  }

  function getHistoryKey(goalId) {
    return `savings_history_${goalId}`;
  }

  function getHistory(goalId) {
    const raw = localStorage.getItem(getHistoryKey(goalId));
    return raw ? JSON.parse(raw) : [];
  }

  function addToHistory(goalId, amount, date, note) {
    const history = getHistory(goalId);
    history.push({ amount, date, note });
    localStorage.setItem(getHistoryKey(goalId), JSON.stringify(history));
  }

  function ordinal(n) {
    const s = ["th", "st", "nd", "rd"];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
  }

  document.querySelectorAll(".kebab-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      closeAllDropdowns();
      btn.closest(".kebab-menu-wrapper").classList.toggle("active");
    });
  });

  document.addEventListener("click", closeAllDropdowns);

  addGoalBtn.addEventListener("click", () => {
    document.getElementById("modalTitle").textContent = "New Goal";
    const form = document.getElementById("goalForm");
    form.reset();
    form.querySelector('[name="action"]').value = "add";
    form.querySelector('[name="id"]').value = "";
    goalModal.classList.add("show");
  });

  cancelGoalModal.addEventListener("click", () =>
    goalModal.classList.remove("show")
  );
  goalModal.addEventListener(
    "click",
    (e) => e.target === goalModal && goalModal.classList.remove("show")
  );

  document.querySelectorAll(".edit").forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".card");
      const id = card.dataset.id;

      const form = document.getElementById("goalForm");
      form.querySelector('[name="action"]').value = "update";
      form.querySelector('[name="id"]').value = id;
      form.querySelector('[name="goal_name"]').value = card
        .querySelector("h2")
        .textContent.trim();
      form.querySelector('[name="target_amount"]').value = extractAmount(
        card.innerHTML,
        "Target"
      );
      form.querySelector('[name="current_amount"]').value = extractAmount(
        card.innerHTML,
        "Saved"
      );
      form.querySelector('[name="start_date"]').value = extractDate(
        card.innerHTML,
        "Start"
      );
      form.querySelector('[name="end_date"]').value = extractDate(
        card.innerHTML,
        "End"
      );
      form.querySelector('[name="type"]').value = card
        .querySelector(".type-label")
        .textContent.trim()
        .toLowerCase();
      form.querySelector('[name="status"]').value = card
        .querySelector(".status-label")
        .textContent.trim()
        .toLowerCase();

      document.getElementById("modalTitle").textContent = "Edit Goal";
      goalModal.classList.add("show");
    });
  });

  function extractAmount(html, label) {
    const match = html.match(new RegExp(`${label}: \\$([0-9.,]+)`));
    return match ? parseFloat(match[1].replace(/,/g, "")) : "";
  }

  function extractDate(html, label) {
    const match = html.match(new RegExp(`${label}: ([0-9\\-]+)`));
    return match ? match[1] : "";
  }

  document.querySelectorAll(".delete").forEach((btn) => {
    btn.addEventListener("click", () => {
      deleteGoalId = btn.closest(".card").dataset.id;
      deleteModal.classList.add("show");
    });
  });

  cancelDeleteModal.addEventListener("click", () => {
    deleteModal.classList.remove("show");
    deleteGoalId = null;
  });

  confirmDeleteBtn.addEventListener("click", async () => {
    if (!deleteGoalId) return;

    const res = await fetch("savings.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ action: "delete", id: deleteGoalId }),
    });

    const json = await res.json();
    if (json.success) {
      deleteModal.classList.remove("show");
      showToast("Goal deleted successfully!");
      setTimeout(() => location.reload(), 1200);
    } else {
      alert(json.error || "Failed to delete goal.");
    }
  });

  document.querySelectorAll(".save-money").forEach((btn) => {
    btn.addEventListener("click", () => {
      currentGoalId = btn.closest(".card").dataset.id;
      saveAmountInput.value = "";
      saveDateInput.value = new Date().toISOString().split("T")[0];

      const history = getHistory(currentGoalId);
      saveNotesInput.value = `${ordinal(history.length + 1)} Added`;

      saveModal.classList.add("show");
    });
  });

  closeSaveModal.addEventListener("click", () => {
    saveModal.classList.remove("show");
    saveForm.reset();
  });

  saveForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const amount = parseFloat(saveAmountInput.value);
    const selectedDate = saveDateInput.value;
    const note = saveNotesInput.value;

    if (!amount || amount <= 0 || !selectedDate) {
      alert("Please enter valid amount and date.");
      return;
    }

    const response = await fetch("savings.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        action: "add_to_savings",
        id: currentGoalId,
        amount,
      }),
    });

    const result = await response.json();
    if (result.success) {
      addToHistory(currentGoalId, amount, selectedDate, note);
      saveModal.classList.remove("show");
      saveForm.reset();
      showToast("Amount added to savings.");
      setTimeout(() => location.reload(), 1200);
    } else {
      alert(result.error || "Failed to add amount.");
    }
  });

  document.querySelectorAll(".history").forEach((btn) => {
    btn.addEventListener("click", () => {
      const goalId = btn.closest(".card").dataset.id;
      const history = getHistory(goalId);

      const list = history.length
        ? `<ul>${history
            .map(
              (entry) =>
                `<li>$${entry.amount.toFixed(2)} on ${entry.date} - ${
                  entry.note
                }</li>`
            )
            .join("")}</ul>`
        : "<ul><li>No history yet.</li></ul>";

      document.getElementById("historyList").innerHTML = list;
      historyModal.classList.add("show");
    });
  });

  closeHistoryBtn.addEventListener("click", () => {
    historyModal.classList.remove("show");
  });

  const goalForm = document.getElementById("goalForm");
  goalForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(goalForm);

    const response = await fetch("savings.php", {
      method: "POST",
      body: new URLSearchParams(formData),
    });

    const result = await response.json();
    if (result.success) {
      goalModal.classList.remove("show");
      goalForm.reset();
      showToast("Goal saved!");
      setTimeout(() => location.reload(), 1200);
    } else {
      alert(result.error || "Failed to save.");
    }
  });
});
