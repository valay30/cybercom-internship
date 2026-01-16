let numbers = [10,20,30,40];
let mix = [1, "hello", true, null, undefined];

numbers[3] = 50;

console.log(numbers);
console.log(numbers.length);
console.log(typeof(numbers));

const car = new Array("Saab", "Volvo", "BMW");
console.log(car);

let arr = [10];
arr[5] = 50;
console.log(arr);

let arr1 = [1, 2, 3];
let a = arr1.push(10); // add element at the end
console.log(arr1);
console.log(a); // push() returns new length of array

let b = arr1.pop(); //remove element from end
console.log("hello");
console.log(b);
console.log(arr1); // pop() returns removed element

console.log("unshift");
let arr2 = [20, 30];
let c = arr2.unshift(1); //add elements to the beginning of array
console.log(c);
console.log(arr2);

console.log("shift");
let d = arr2.shift(); //remove elements from the beginning of array
console.log(d);
console.log(arr2);

console.log("slice");
let arr3 = [10, 20, 30, 40];
console.log(arr3.slice(1, 3)); //Does not change original array || Returns new array
console.log(arr3.slice(1)); 


console.log("splice");
arr3.splice(1, 2); //CHANGES original array 
console.log(arr3);

const day = ["mon", "tue", "wed", "thur"];
day.splice(2, 0, "fri", "sat");
console.log(day);

let num = [10,20,30,40,50,60];
num.splice(2,3);
console.log(num);


const months = ["Jan", "Feb", "Mar", "Apr"];
const spliced = months.toSpliced(0, 1);
console.log(spliced);



console.log(" ");
const fruits = ["Banana", "Orange", "Apple", "Mango"];
console.log(fruits.sort());

let stringFruits = fruits.toString(); //Converting an Array to a String
console.log(stringFruits);

fruits[6] = "Lemon"; // Creates undefined "holes" in fruits
console.log(fruits);

console.log("third element of fruits is",fruits.at(2));


// How to Recognize an Array
console.log(Array.isArray(fruits));
console.log(fruits instanceof Array);


//delete()
delete fruits[0]; //delete() leaves undefined holes in the array.
console.log(fruits);


const myGirls = ["Cecilie", "Lone"];
const myBoys = ["Emil", "Tobias", "Linus"];

const myChildren = myGirls.concat(myBoys);
console.log(myChildren);

const a1 = ["Cecilie", "Lone"];
const a2 = ["Emil", "Tobias", "Linus"];
const a3 = ["Robin", "Morgan"];
const concatThreeArray = arr1.concat(arr2, arr3);
console.log(concatThreeArray);


console.log("Flattening an Array");
const myArr = [[1,2],[3,4],[5,6]];
const newArr = myArr.flat(); //flat() method creates a new array
console.log(newArr);


const myArr1 = [1, 2, 3, 4, 5, 6];
const newArr2 = myArr1.flatMap(x => [x, x * 10]);
console.log(newArr2);





