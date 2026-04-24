document.addEventListener("DOMContentLoaded", () => {
  const addBtn = document.getElementById("addCategoryBtn");
  const addModal = document.getElementById("addCategoryModal");
  const editModal = document.getElementById("editModal");
  const closeModalBtns = document.querySelectorAll(".close-btn");
  const toast = document.getElementById("toast");

  addBtn.addEventListener("click", () => {
    addModal.style.display = "block";
  });

  closeModalBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      addModal.style.display = "none";
      editModal.style.display = "none";
    });
  });

  document
    .getElementById("addCategoryForm")
    .addEventListener("submit", async (e) => {
      e.preventDefault();

      const name = document.getElementById("categoryName").value.trim();
      const description = document
        .getElementById("categoryDescription")
        .value.trim();

      const response = await fetch("category.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=add&name=${encodeURIComponent(
          name
        )}&description=${encodeURIComponent(description)}`,
      });

      const result = await response.json();
      if (result.success) {
        showToast("Category added successfully");
        location.reload();
      }
    });

  // Submit edit form
  document
    .getElementById("editCategoryForm")
    .addEventListener("submit", async (e) => {
      e.preventDefault();

      const id = document.getElementById("edit_id").value;
      const name = document.getElementById("edit_name").value.trim();
      const description = document.getElementById("edit_desc").value.trim();

      const response = await fetch("category.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=edit&id=${id}&name=${encodeURIComponent(
          name
        )}&description=${encodeURIComponent(description)}`,
      });

      const result = await response.json();
      if (result.success) {
        showToast("Category updated successfully");
        location.reload();
      }
    });
});

document.addEventListener("DOMContentLoaded", function () {
  const msgDiv = document.getElementById("session-message");
  if (msgDiv) {
    const message = msgDiv.getAttribute("data-message");
    showToast(message);
  }
});

function showToast(message) {
  const toast = document.getElementById("toast");
  toast.innerText = message;
  toast.style.display = "block";
  toast.style.opacity = "1";

  setTimeout(() => {
    toast.style.opacity = "0";
  }, 3000);

  setTimeout(() => {
    toast.style.display = "none";
  }, 3500);
}

function showToast(message) {
  const toast = document.getElementById("toast");
  toast.innerText = message;

  toast.style.display = "block";
  toast.classList.add("show");

  setTimeout(() => {
    toast.classList.remove("show");
  }, 3000);

  setTimeout(() => {
    toast.style.display = "none";
  }, 3500);
}

function editCategory(id, name, desc) {
  document.getElementById("edit_id").value = id;
  document.getElementById("edit_name").value = name;
  document.getElementById("edit_desc").value = desc;
  document.getElementById("editModal").style.display = "block";
}

function deleteCategory(id) {
  if (confirm("Delete this category?")) {
    fetch("category.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `action=delete&id=${id}`,
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          showToast("Category deleted");
          location.reload();
        }
      });
  }
}
