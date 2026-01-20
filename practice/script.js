// arr = [10, 20, 30, 40];

// let sum = arr.reduce((a,b) => a+b,0);
// console.log(sum);

// let max = Math.max(...arr);
// console.log(max);

// let arr = [1, 2, 2, 3, 4, 4, 5];

// for (let i = 0; i < arr.length; i++) {
//     for (let j = i+1; j < arr.length; j++) {
//         if (arr[j] == arr[i]) {
//             arr.splice(j, 1); // remove duplicate
//             j--;
//         }
//     }
// }

// console.log(arr);



// let arr = [20, 60, 45, 80, 10];

// let result = arr.filter(n => n>50);

// console.log(result);



// let prices = [100, 200, 300];

// let gst = prices.map(n => n + 0.18*n);

// console.log(gst);



// let students = [
//   { name: "A", marks: 78 },
//   { name: "B", marks: 92 },
//   { name: "C", marks: 85 }
// ];

// let topper = students[0];

// students.forEach(student=>{
//     if (student.marks > topper.marks) {
//         topper = student;
//     }
// })

// console.log(topper);



// let arr = [1, 3, 7, 8, 10];

// let even = arr.find(n => n % 2 === 0);
// console.log(even);

// let arr = [1, 2, 2, 3, 4, 4, 5];

// let unique = [...new Set(arr)];
// console.log(unique);

// let arr = [1, 2, 3, 4, 5, 6];

// let count = arr.filter(n => n % 2 === 0).length;
// console.log(count);




//Reverse a string
// let str ="ABC";
// let a = "";

// for (let i = str.length - 1; i >= 0; i--) {
//     a = a+ str[i];
// }
// console.log(a);


//Largest number in array
// let arr = [10, 5, 20, 8];
// arr.sort(function (a, b) {
//   return a - b;
// });
// console.log(arr[arr.length -1]);


//Palindrome check
// let str = "madamm";
// let rev = "";

// for (let i = str.length - 1; i >= 0; i--) {
//     rev += str[i];
// }

// let isPal = true;

// for (let i = 0; i < str.length; i++) {
//     if (str[i] != rev[i]) {
//         isPal = false;
//         break;
//     }
// }

// if (isPal) {
//     console.log("palindrome");
// }else{
//     console.log("Not palindrome");
// }

// console.log(rev);




// Count vowels
// let str = "education";
// let count = 0;
// let vowels = "aeiou";

// for (let i = 0; i < str.length; i++) {
//     for (let j = 0; j < vowels.length; j++) {
//         if (str[i] == vowels[j]) {
//             count++;
//         }
//     }
// }
// console.log(count);


// Second largest number
// let arr = [10, 40, 20, 50, 30];

// arr.sort((a,b) => a - b);

// console.log(arr[arr.length - 2]);


// Remove duplicates
let arr = [1,2,2,3,4,4];
let unique = [];
let isunique;

for (let i = 0; i < arr.length; i++) {
    isunique = true;
    for (let j = i+1; j < arr.length; j++) {
        if (arr[i] == arr[j]) {
            isunique = false;
            break;
        }
    }
    if (isunique == true) {
        unique.push(arr[i]);
    }
}

console.log(unique);
