const numbers = [45, 4, 9, 16, 25];
let txt = "";
numbers.forEach(myFunction);

function myFunction(value, index, array) {
  txt += value + " ";
}

console.log(txt);

console.log(" ");
const numbers1 = [45, 4, 9, 16, 25];
const numbers2 = numbers1.map(myFunction);

function myFunction(value, index, array) {
  return value * 2;
}
console.log(numbers2);



const myArr = [1, 2, 3, 4, 5, 6];
const newArr = myArr.flatMap((x) => x * 2);
console.log(newArr);


let allOver18 = numbers.every(myFunction1); //The every() method checks if all array values pass a test.

function myFunction1(value, index, array) {
  return value > 18;
}

console.log(allOver18);


const arr1 = [1, 2, 3];
const arr2 = [4, 5, 6];

const arr3 = [...arr1, ...arr2]; //Spread
console.log(arr3);

// The spread operator (...) can be used to copy an array:
const arr4 = [...arr1];
console.log(arr4);




