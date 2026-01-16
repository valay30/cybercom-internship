function greet(){
    console.log("Hello");
}

greet();
greet();

console.log(" ");
function add(a,b){
    console.log(a+b);
}

add(1,5);
// a,b ->parameters     1,5 ->arguments

console.log(" ");

//Function with return value   
function multiply(a,b){
    return a*b;
}

let result = multiply(2,5);
console.log(result);

console.log(" ");

//Anonymous Function

setTimeout(function () {
  console.log("Anonymous");
}, 1000);


console.log(" ");

//IIFE (Immediately Invoked Function Expression)

(function () {
  console.log("IIFE running");
})();

//IIFE with parameter
(function (name){
    console.log("Hello",name);
})("Valay");

//IIFE with return value
const res = (function(a,b){
    return a*b;
})(5,4);

console.log("Value of a*b:",res);


//default parameter
function greet(name = "Guest"){
    return "Hello "+ name;
}

console.log(greet());
console.log(greet("Valay"));

//Rest Parameter
function sum(...numbers){
    let total = 0;
    for(n of numbers){
        total += n;
    }
    return total;
}

console.log(sum(1,2));
console.log(sum(1,2,3));

//arguments object
function demo(a,b){
    console.log(arguments);
}

demo(1,2,3,4);


//multiple return
function checkAge(age){
    if(age < 18){
        return "Minor";
    }else{
        return "Adult";
    }
}

console.log(checkAge(20));

console.log(" ");

//Pass by Value
let x = 10;
function change(a){
    a = 20;
    return a;
}

console.log(change(x));
console.log(x);

console.log(" ");

//Pass by Reference

let obj = {val:10};

function newChange(o){
    o.val = 20; 
}

newChange(obj);
console.log(obj);

console.log(" ");

//Return Object
function createUser() {
  return {
    name: "Valay",
    age: 22
  };
}

console.log(createUser());

console.log(" ");

//Return Function
function outer(){
    return function(){
        return "Hello";
    };
}

const fn = outer();
console.log(fn());
