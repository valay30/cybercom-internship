const fruits = ["Banana", "Orange", "Apple", "Mango"];

fruits.sort()
console.log(fruits);

fruits.reverse();
console.log(fruits);

const months = ["Jan", "Feb", "Mar", "Apr"];
const sorted = months.toSorted();
console.log(sorted);

// toSorted() : creates a new array
// sort() : alters the original array

const month = ["Jan", "Feb", "Mar", "Apr"];
const reversed = month.toReversed();
console.log(reversed);

const points = [40, 100, 1, 5, 25, 10];
console.log(points.sort(function(a, b){return a - b}));
console.log(points.sort(function(a, b){return b - a}));