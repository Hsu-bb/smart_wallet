const bell = document.getElementById("notificationToggle");
const notifList = document.getElementById("notifList");
const notifCount = document.getElementById("notifCount");
const notifDropdown = document.getElementById("notifDropdown");

async function fetchNotifications() {
  try {
    const res = await fetch("api/notifications.php?limit=5");
    const json = await res.json();

    notifList.innerHTML = "";
    if (!json.length) {
      notifList.innerHTML = "<li>No new notifications</li>";
      notifCount.style.display = "none";
      return;
    }

    notifCount.textContent = json.length;
    notifCount.style.display = "inline-block";

    json.forEach((n) => {
      const li = document.createElement("li");
      li.classList.add("notif-item");
      if (!n.is_read) li.classList.add("unread");
      li.innerHTML = `
        <div><strong>${n.message}</strong></div>
        <small>${n.created_at}</small>
      `;
      notifList.appendChild(li);
    });
  } catch (err) {
    notifList.innerHTML = "<li>Failed to load</li>";
  }
}

bell.addEventListener("click", () => {
  notifDropdown.classList.toggle("show");
});

document.addEventListener("click", (e) => {
  if (!bell.contains(e.target) && !notifDropdown.contains(e.target)) {
    notifDropdown.classList.remove("show");
  }
});

fetchNotifications();
