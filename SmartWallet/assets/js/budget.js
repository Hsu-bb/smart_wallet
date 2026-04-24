document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("budgetForm");
  if (!form) return;

  const totalInput = form.querySelector('[name="total_amount"]');
  const allocInputs = form.querySelectorAll('input[name^="allocations"]');
  const summary = document.getElementById("allocationSummary");
  const submitBtn = form.querySelector('button[type="submit"]');

  const budgetNameSelect = document.getElementById("budget-name-select");
  const startInput = document.querySelector('input[name="start_date"]');
  const endInput = document.querySelector('input[name="end_date"]');

  function formatDateToYYYYMMDD(date) {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, "0");
    const day = date.getDate().toString().padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  function updateDatesFromBudgetName() {
    const value = budgetNameSelect.value;
    const parts = value.split("-");
    if (parts.length !== 2) return;

    const year = parseInt(parts[0]);
    const month = parseInt(parts[1]);
    if (isNaN(year) || isNaN(month) || month < 1 || month > 12) return;

    const startDate = new Date(year, month - 1, 1);
    const endDate = new Date(year, month, 0);

    startInput.value = formatDateToYYYYMMDD(startDate);
    endInput.value = formatDateToYYYYMMDD(endDate);
  }

  let warningEl = document.getElementById("allocation-warning");
  if (!warningEl) {
    warningEl = document.createElement("div");
    warningEl.id = "allocation-warning";
    warningEl.className = "error-box";
    warningEl.style.display = "none";
    warningEl.textContent = "Allocated amount exceeds total budget.";
    form.insertBefore(warningEl, summary.nextSibling);
  }

  function updateAllocationSummary() {
    const totalBudget = parseFloat(totalInput.value) || 0;
    let totalAllocated = 0;
    let hasAllocation = false;

    allocInputs.forEach((input) => {
      const value = parseFloat(input.value) || 0;
      totalAllocated += value;
      if (value > 0) hasAllocation = true;
    });

    summary.textContent = `Allocated: ${totalAllocated.toFixed(
      2
    )} / ${totalBudget.toFixed(2)}`;
    summary.style.color = totalAllocated > totalBudget ? "red" : "green";

    if (totalAllocated > totalBudget) {
      warningEl.style.display = "block";
    } else {
      warningEl.style.display = "none";
    }

    submitBtn.disabled =
      !hasAllocation || totalBudget <= 0 || totalAllocated > totalBudget;
  }

  totalInput.addEventListener("input", updateAllocationSummary);
  allocInputs.forEach((input) =>
    input.addEventListener("input", updateAllocationSummary)
  );

  if (budgetNameSelect) {
    budgetNameSelect.addEventListener("change", updateDatesFromBudgetName);
    updateDatesFromBudgetName();
  }

  updateAllocationSummary();

  document.querySelectorAll(".budget-progress-bar").forEach((el) => {
    const percent = parseFloat(el.dataset.percent);
    el.style.width = `${percent}%`;
    el.style.backgroundColor = percent > 100 ? "#dc3545" : "#28a745";
  });

  const toast = document.getElementById("toast");
  if (toast && toast.textContent.trim()) {
    toast.classList.add("show");
    setTimeout(() => {
      toast.classList.remove("show");
    }, 3000);
  }

  document.querySelectorAll(".btn-delete").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      if (!confirm("Are you sure you want to delete this budget?")) {
        e.preventDefault();
      }
    });
  });
});
