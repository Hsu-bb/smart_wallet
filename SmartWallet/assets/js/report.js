document.addEventListener("DOMContentLoaded", function () {
  if (typeof budgetData === "undefined" || !budgetData.length) return;

  const labels = budgetData.map((item) => item.category);
  const allocated = budgetData.map((item) => parseFloat(item.allocated_amount));
  const spent = budgetData.map((item) => parseFloat(item.spent_amount));

  const ctx = document.getElementById("budgetChart");
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Allocated",
          data: allocated,
          backgroundColor: "rgba(54, 162, 235, 0.6)",
        },
        {
          label: "Spent",
          data: spent,
          backgroundColor: "rgba(255, 99, 132, 0.6)",
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "top" },
        tooltip: { mode: "index", intersect: false },
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: "Amount",
          },
        },
      },
    },
  });
});
