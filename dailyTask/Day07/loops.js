/*
Types of loops

1. for
2. while
3. do...while
4. for...of
5. for...in

*/
console.log("for loop");

for (let i = 1; i <= 5; i++){
    console.log(i);    
}

console.log("while loop");

let j = 1;
while(j <= 5){
    console.log(j);
    j++;
}

console.log("do while loop");

let k = 1;
do{
    console.log(k);
    k++
}while(k<=5);

//At least once execute

console.log(" ");

//for of loop

console.log("for of loop");

let arr = [10,20,30];
for(let value of arr){
    console.log(value);
}

let str = "JS";
for(let char of str){
    console.log(char);
}

let user = {name:"Valay", age:18};

for(key of Object.keys(user)){
    console.log(key, user[key]);
}

//for...of gives direct value of array

console.log(" ");

//for in loop
console.log("for in loop");

let user1 = {name:"Valay", age:18};

for(let key in user1){
    console.log(key, user1[key]);
}


let arr1 = ["a", "b", "c"];
for (let index in arr1) {
  console.log(index);
}

//for...in gives indexes of array in string form


