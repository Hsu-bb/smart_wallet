document.addEventListener("DOMContentLoaded", function () {
  const ctx = document.getElementById("lineChart");

  if (!ctx || typeof chartData === "undefined") return;

  const dates = [...new Set(chartData.map((d) => d.date))].sort();
  const income = dates.map((date) =>
    chartData
      .filter((d) => d.date === date && d.type === "income")
      .reduce((sum, d) => sum + parseFloat(d.amount), 0)
  );
  const expense = dates.map((date) =>
    chartData
      .filter((d) => d.date === date && d.type === "expense")
      .reduce((sum, d) => sum + parseFloat(d.amount), 0)
  );

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: dates,
      datasets: [
        {
          label: "Income",
          data: income,
          backgroundColor: "rgba(0, 128, 0, 0.6)",
        },
        {
          label: "Expense",
          data: expense,
          backgroundColor: "rgba(255, 0, 0, 0.6)",
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "top",
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: "Amount",
          },
        },
        x: {
          title: {
            display: true,
            text: "Date",
          },
        },
      },
    },
  });
});
