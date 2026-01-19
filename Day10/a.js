const form = document.getElementById("form");

form.addEventListener("submit", function (e) {
  e.preventDefault();

  let isValid = true;

  clearErrors();

  const name = document.getElementById("name");
  const email = document.getElementById("email");
  const phone = document.getElementById("phone");
  const dob = document.getElementById("dob");
  const course = document.getElementById("course");
  const address = document.getElementById("address");
  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirmPassword");
  const terms = document.getElementById("terms");

  // Name
  if (name.value.trim() === "") {
    showError(name, "Name is required");
    isValid = false;
  }

  // Email
  if (!email.value.match(/^\S+@\S+\.\S+$/)) {
    showError(email, "Enter valid email");
    isValid = false;
  }

  // Phone
  if (!phone.value.match(/^[6-9]\d{9}$/)) {
    showError(phone, "Enter valid Indian mobile number");
    isValid = false;
  }

  // Gender
  const gender = document.querySelector('input[name="gender"]:checked');
  if (!gender) {
    showErrorAfterGender("Select gender");
    isValid = false;
  }

  // DOB
  if (dob.value === "") {
    showError(dob, "Select date of birth");
    isValid = false;
  }

  // Course
  if (course.value === "") {
    showError(course, "Select course");
    isValid = false;
  }

  // Address
  if (address.value.trim().length < 10) {
    showError(address, "Address must be at least 10 characters");
    isValid = false;
  }

  // Password
  if (password.value.length < 6) {
    showError(password, "Password must be minimum 6 characters");
    isValid = false;
  }

  // Confirm password
  if (password.value !== confirmPassword.value) {
    showError(confirmPassword, "Passwords do not match");
    isValid = false;
  }

  // Terms
  if (!terms.checked) {
    showErrorAfterTerms("Please accept terms");
    isValid = false;
  }

  if (isValid) {
    alert("Registration Successful ✅");
    form.reset();
  }
});

function showError(input, message) {
  input.nextElementSibling.innerText = message;
}

function showErrorAfterGender(msg) {
  document.querySelectorAll(".error")[3].innerText = msg;
}

function showErrorAfterTerms(msg) {
  document.querySelectorAll(".error")[9].innerText = msg;
}

function clearErrors() {
  document.querySelectorAll(".error").forEach(e => e.innerText = "");
}
