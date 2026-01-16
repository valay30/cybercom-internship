const fruits = ["Apple", "Orange", "Apple", "Mango"];
console.log(fruits.indexOf("Apple"));
console.log(fruits.indexOf("Apple",1));   

console.log(fruits.lastIndexOf("Apple"));

console.log(fruits.includes("Mango"));


const numbers = [4, 9, 16, 25, 29];
let first = numbers.find(myFunction);
let first1 = numbers.findIndex(myFunction);

function myFunction(value, index, array) {
  return value > 18;
}

console.log(first);
console.log(first1);

