//short & modern way to write functions

const add = (a,b) => {
    return a+b;
}
console.log("a + b :", add(2,3));


const sub = (a,b) => a - b;
console.log("a - b :", sub(2,3));

//Argument keyword
const addNumbers = (...nums) => {
    let ans = 0;
    for(let i=0; i<nums.length; i++){
        ans += nums[i];
    }
    return ans;
}

console.log(addNumbers(1,2,3,4,5));

//This keyword 

const obj = {
    value : 20,
    myFunction: () => {
        console.log(this.value);
    }
};

// const obj = {
//     value : 20,
//     myFunction: function(){
//         console.log(this);
//     }
// };

obj.myFunction();
