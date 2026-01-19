const ageInput = document.getElementById("age");
const result = document.getElementById("result");

document.getElementById("age").addEventListener("input", () => {
    let age = Number(ageInput.value);

    if (age === 0 || age < 0) {
        result.innerHTML = "Please enter valid age";
    }
    else if (age < 18) {
        result.innerHTML = "You are a Child";
    }
    else if (age >= 18 && age < 60) {
        result.innerHTML = "You are an Adult";
    }
    else {
        result.innerHTML = "You are a Senior Citizen";
    }
});
