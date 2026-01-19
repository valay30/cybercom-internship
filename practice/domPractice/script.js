const btn = document.getElementById('btn');
const input1 = document.getElementById('input1');
const para = document.getElementById('para');
const element = document.getElementById('element');

btn.addEventListener('click', ()=>{
    btn.innerText = "Clicked";
    btn.style.backgroundColor = "yellow";
})

input1.addEventListener('input', ()=>{
    para.innerText = input1.value;
})

btn.addEventListener('click', ()=>{
    let isVisible = element.style.display;
    if (isVisible === "none") {
        element.style.display = 'block'
    }else{
        element.style.display = 'none'
    }
})

let dynamicH1 = document.createElement('h1');
dynamicH1.innerText = 'Dynamic Change';
document.body.appendChild(dynamicH1);