document.getElementById("form").addEventListener("submit", function (e) {
  e.preventDefault();

  let name = document.getElementById("name").value;
  let email = document.getElementById("email").value;
  let password = document.getElementById("password").value;
  let age = document.getElementById("age").value;
  let terms = document.getElementById("terms").checked;
  let error = document.getElementById("error");

  if (name === "") {
    error.innerText = "Name is required";
    return;
  }

  if (email === "") {
    error.innerText = "Email is required";
    return;
  }

  if (password.length < 6) {
    error.innerText = "Password must be at least 6 characters";
    return;
  }

  if (isNaN(age)) {
    error.innerText = "Age must be a number";
    return;
  }

  if (!terms) {
    error.innerText = "Please accept terms and conditions";
    return;
  }

  error.style.color = "green";
  error.innerText = "Registration Successful";
});
