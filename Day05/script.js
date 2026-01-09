// alert(5 + 6);

//variable
let x = 5;
let y = 10;
let z = x + y;

let fruitName = "Mango";
const companyName = "Apple";

let person1 = "Valay" + " " + "Patel";
console.log(person1);

let abc = 5;
console.log(abc);
if (abc <10) {
    let abc = 10;
    console.log(abc);
} else {
    let abc = 0;
    console.log(abc);
}

function myFunction() {
  document.getElementById("demo1").innerHTML = "Hello Cybercom!";
  document.getElementById("demo2").innerHTML = "How are you?";
}

//comment
/*
Hello World
*/

let str1 = "5" + 2 + 3;
let str2 = 2 + 3 + "5";

console.log(str1);
console.log(str2);


//Primitive Data Types
//Number
let marks = 85;
let price = 99.99;

//String
let name = "Valay";
let city = 'Ahmedabad';

//Boolean
let isLoggedIn = true;

//Undefined
let result;

//Null
let data = null;

//BigInt
let bigNumber = 12345678901234567890n;

//Object
const phone = {company:"Apple", camera:50};
console.log(phone.company);
console.log(phone.camera);

//Symbol
const xyz = Symbol();
console.log(typeof xyz); 

console.log(typeof marks);
console.log(typeof name);
console.log(typeof result);
console.log(typeof isLoggedIn);
console.log(typeof bigNumber);
console.log(typeof data);

console.log(typeof 0)              
console.log(typeof "314")


let b = -1;

if (b === 0) {
    console.error("Division by zero");
  }

  if (b < 0) {
    console.warn("Negative divisor");
  }