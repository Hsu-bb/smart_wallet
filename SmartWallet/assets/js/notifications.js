function timeAgo(dateStr) {
  const date = new Date(dateStr);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);
  const intervals = {
    year: 31536000,
    month: 2592000,
    day: 86400,
    hour: 3600,
    minute: 60,
    second: 1,
  };

  for (const [unit, secondsPer] of Object.entries(intervals)) {
    const count = Math.floor(seconds / secondsPer);
    if (count >= 1) {
      return `${count} ${unit}${count > 1 ? "s" : ""} ago`;
    }
  }
  return "just now";
}

function updateRelativeTime() {
  document.querySelectorAll(".relative-time").forEach((el) => {
    const timeStr = el.getAttribute("data-time");
    el.textContent = timeAgo(timeStr);
  });
}

updateRelativeTime();

setInterval(() => {
  const currentUrl = new URL(window.location.href);
  fetch(currentUrl.pathname + currentUrl.search, {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((res) => res.text())
    .then((html) => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");
      const newList = doc.getElementById("notiList");
      if (newList) {
        document.getElementById("notiList").innerHTML = newList.innerHTML;
        updateRelativeTime();
      }
    });
}, 30000);
