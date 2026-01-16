// let h1 = document.querySelector("#title");
// h1.style.color = "red";

// const myPara = document.getElementById("para1");
// myPara.innerHTML = "Hello World!";

console.log(document.getElementById('title'));
console.log(document.getElementsByClassName('heading')[0].id);
console.log(document.getElementById("title").className);
console.log(document.getElementById("title").getAttribute('id'));
console.log(document.getElementById("title").getAttribute('class'));
console.log(document.getElementById("title").setAttribute('class','test heading'));


title.style.backgroundColor = 'green';
title.style.padding = "15px";
title.style.borderRadius = "15px";
console.log(title.innerText);
console.log(title.textContent);
console.log(title.innerHTML);

console.log(document.querySelector('h1'));
console.log(document.querySelector('h2'));
console.log(document.querySelector('#title'));
console.log(document.querySelector('.heading'));
console.log(document.querySelector('input[type="password"]'));

const myul = document.querySelector('ul');

const turnGreen = myul.querySelector('li');

// turnGreen.style.backgroundColor = 'green';
// turnGreen.style.padding = '10px';
// turnGreen.innerText = 'five';


let second = document.querySelector(".second");
console.log(second.previousElementSibling);
console.log(second.nextElementSibling);


console.log(document.querySelectorAll('li'));

const temlLiList = document.querySelectorAll('li');
temlLiList[0].style.color = 'black';

temlLiList.forEach(function (l) {
    l.style.backgroundColor = 'tomato';
})

const tempClassList = document.getElementsByClassName('list-item');
console.log(tempClassList);


const myCovertedArray = Array.from(tempClassList); //convert html collection to array
console.log(myCovertedArray);

myCovertedArray.forEach(function (li){
    li.style.color='aqua';
})

document.getElementById("demo").innerHTML = "Date : " + Date();

const clickButton = document.querySelector('button');
clickButton.addEventListener('click', ()=>{
    clickButton.style.color = 'white';
    clickButton.style.backgroundColor = 'blue';
    clickButton.textContent='Clicked';
})
