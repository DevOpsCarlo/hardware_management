const btnLogin = document.querySelector(".btn-login");
btnLogin.addEventListener("click", (e) => {
  const inputUsername = document.querySelector(".input-username");
  const inputPassword = document.querySelector(".input-password");
  const loginError = document.querySelector(".login-error");

  let hasError = false;
  const isEmptyUsername = inputUsername.value.trim() === "";
  const isEmptyPassword = inputPassword.value.trim() === "";
  loginError.classList.add("hidden");
  console.log("click");
  if (isEmptyUsername && isEmptyPassword) {
    loginError.textContent = " ⚠️ Please enter your username and password.";
    hasError = true;
    loginError.classList.remove("hidden");
  }
  if (isEmptyUsername && !isEmptyPassword) {
    loginError.textContent = "⚠️ Please enter your username.";
    hasError = true;
    loginError.classList.remove("hidden");
  }
  if (isEmptyPassword && !isEmptyUsername) {
    loginError.textContent = "⚠️ Please enter your password.";
    hasError = true;
    loginError.classList.remove("hidden");
  }

  if (hasError) {
    e.preventDefault();
  }
});
