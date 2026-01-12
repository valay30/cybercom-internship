//Operators

//Arithmetic Operators

let x = 10;
let y = (5 + 5) * x; // addition & multiplication
let z = 2 ** 3; // exponential
let a = 10 / 3; // division
let b = 10 % 3; //modulus
let c = x++; //Incrementing
let d = x--; //Decrementing

let e = 100 + 50 * 3;; //Operator Precedence


console.log("x:", x);
console.log("y:", y);
console.log("z:", z);
console.log("a:", a);
console.log("b:", b);
console.log("c:", c);
console.log("d:", d);
console.log("e:", e);


//Assignment Operators
let text7 = 10;
text7 += 5; //+=
console.log("text7:", text7);

let text8 = 10;
text8 -= 5; //-=
console.log("text8:", text8);

//Logical Assignment Operators
let logassign = true;
logassign &&= 10;

console.log("logassign", logassign);

//Nullish coalescing assignment operator
let nullishassign = 15;
nullishassign ??= 10;
console.log("nullishassign: ", nullishassign);

let nullishassign2 = null;
nullishassign2 ??= 10;
console.log("nullishassign2: ", nullishassign2);



//Comparison Operators
let comp1 = 5;
let result = comp1 > 8;
console.log("result:", result);

let result1 = (comp1 == 5); //equal to
console.log("result1:", result1);

let comp2 = "5";
let result2 = (comp2 === 5); //equal value and equal type
console.log("result2:", result2);

let comp3 = "10";
let result3 = (comp3 != 5); //not equal
console.log("result3:", result3);

let age = 15;
if (age < 18)
    console.log("Too young to buy alcohol");

//String Comparison
let text9 = "A";
let text10 = "B";
let result5 = text9 < text10;
console.log("result5:", result5);


//Spread (...) Operator
let text = "12345";


let min = Math.min(...text);
let max = Math.max(...text);

console.log("min:", min);
console.log("max:", max);

//if else condition

let condition1 = 10;
if (condition1 < 10) {
    console.log("condition1 is less than 10");
} else if (condition1 > 10) {
    console.log("condition1 is greater than 10");
} else {
    console.log("condition1 is equal to 1");
}

//Ternary Operator
let age1 = 20;
let result4 = age1 >= 18 ? "adult" : "Minor";
console.log(result4);


//Switch Statement
let day = 3;

switch (day) {
    case 1:
        console.log("Monday");
        break;
    case 2:
        console.log("Tuesday");
        break;
    case 3:
        console.log("Wednesday");
        break;
    case 4:
        console.log("Thrusday");
        break;
    case 5:
        console.log("Friday");
        break;
    case 6:
        console.log("Saturday");
        break;
    case 7:
        console.log("Sunday");
        break;
    default:
        console.log("Invalid day");
        break;
}


//concatenate string
let text1 = "Valay";
let text2 = "Patel";
let text3 = text1 + " " + text2;
console.log("text3:", text3);

//Adding Strings and Numbers
let text4 = 10 + 10;
let text5 = "10" + 10;
let text6 = "hello" + 10;
console.log("text4:", text4);
console.log("text5:", text5);
console.log("text6:", text6);

//truthy & falsy
// false
// 0
// -0
// 0n        
// ""        
// null
// undefined
// NaN

let agee = "25";

if (!isNaN(agee)) {
    if (agee >=18) {
        console.log("Allowed");
    } else {
        console.log("Not Allowed");
    }
}