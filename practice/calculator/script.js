const num1 = document.getElementById("num1");
const num2 = document.getElementById("num2");
const result = document.getElementById("result");


document.getElementById("add").addEventListener("click", () => {
    let a = Number(num1.value);
    let b = Number(num2.value);
    result.innerText = a + b;
});

document.getElementById("sub").addEventListener("click", () => {
    let a = Number(num1.value);
    let b = Number(num2.value);
    result.innerText = a - b;
});

document.getElementById("mul").addEventListener("click", () => {
    let a = Number(num1.value);
    let b = Number(num2.value);
    result.innerText = a * b;
});

document.getElementById("div").addEventListener("click", () => {
    let a = Number(num1.value);
    let b = Number(num2.value);

    if (b === 0) {
        result.innerText = "Cannot divide by 0";
    } else {
        result.innerText = a / b;
    }
});
