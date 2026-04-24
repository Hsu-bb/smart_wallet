window.onload = function () {
  const registerForm = document.querySelector("form[action*='register.php']");
  const loginForm = document.querySelector("form[action*='login.php']");

  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      const name = document.querySelector("input[name='name']").value.trim();
      const email = document.querySelector("input[name='email']").value.trim();
      const password = document
        .querySelector("input[name='password']")
        .value.trim();

      if (name.length < 3) {
        alert("Name must be at least 3 characters long.");
        e.preventDefault();
      } else if (!validateEmail(email)) {
        alert("Invalid email format.");
        e.preventDefault();
      } else if (password.length < 6) {
        alert("Password must be at least 6 characters long.");
        e.preventDefault();
      }
    });
  }

  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      const email = document.querySelector("input[name='email']").value.trim();
      const password = document
        .querySelector("input[name='password']")
        .value.trim();

      if (!validateEmail(email)) {
        alert("Invalid email format.");
        e.preventDefault();
      } else if (password.length < 6) {
        alert("Password must be at least 6 characters long.");
        e.preventDefault();
      }
    });
  }
};
