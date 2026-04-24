function openModal() {
  document.getElementById("editModal").style.display = "flex";
  updateEditSummary();
}

function closeModal() {
  document.getElementById("editModal").style.display = "none";
}

function updateEditSummary() {
  const total = parseFloat(document.getElementById("editSubmit").dataset.total);
  const inputs = document.querySelectorAll("input[name^='allocations']");
  const summary = document.getElementById("editSummary");
  const submit = document.getElementById("editSubmit");
  let totalAllocated = 0;

  inputs.forEach((i) => {
    totalAllocated += parseFloat(i.value || 0);
  });

  summary.textContent = `Allocated: ${totalAllocated.toFixed(
    2
  )} / ${total.toFixed(2)}`;
  summary.style.color = totalAllocated > total ? "red" : "green";
  submit.disabled = totalAllocated > total;
  submit.style.opacity = submit.disabled ? "0.5" : "1";
}

window.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("input[name^='allocations']").forEach((i) => {
    i.addEventListener("input", updateEditSummary);
  });
  updateEditSummary();
});
