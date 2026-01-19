const students = [
  {
    name: "Rahul",
    marks: [80, 70, 90]
  },
  {
    name: "Priya",
    marks: [85, 95, 88]
  },
  {
    name: "Amit",
    marks: [60, 75, 70]
  }
];

students.forEach(student => {
  let total = 0;

  student.marks.forEach(mark => {
    total += mark;
  });

  const avg = total / student.marks.length;
  console.log(student.name, avg);
});
