let tasks = [];

const taskInput = document.getElementById("taskInput");
const taskList = document.getElementById("taskList");
const addBtn = document.getElementById("addBtn");

// render tasks
function renderTasks() {
    taskList.innerHTML = "";

    tasks.forEach((task, index) => {
        const li = document.createElement("li");

        const span = document.createElement("span");
        span.innerText = task.text;

        if (task.completed) {
            span.classList.add("completed");
        }

        span.addEventListener("click", () => {
            tasks[index].completed = !tasks[index].completed;
            renderTasks();
        });

        const delBtn = document.createElement("button");
        delBtn.innerText = "❌";

        delBtn.addEventListener("click", () => {
            tasks.splice(index, 1);
            renderTasks();
        });

        li.appendChild(span);
        li.appendChild(delBtn);
        taskList.appendChild(li);
    });
}

// add task
addBtn.addEventListener("click", () => {
    if (taskInput.value.trim() === "") return;

    tasks.push({
        text: taskInput.value,
        completed: false
    });

    taskInput.value = "";
    renderTasks();
});
