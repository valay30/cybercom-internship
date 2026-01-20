/**
 * ASYNC FAKE SERVER
 */
const FakeServer = {
    async delay(ms = 400) { 
        return new Promise(r => setTimeout(r, ms)); 
    },

    // ... createIntern and updateStatus remain same ...

    async createTask(data) {
        State.update('isLoading', true);
        await this.delay();

        try {
            Validators.validateTaskData(data);
            
            const taskId = RulesEngine.generateTaskId();
            // Check circularity before creating
            Validators.checkCircularDependency(taskId, data.dependencies || [], State.data.tasks);

            const hasUnresolvedDeps = data.dependencies && data.dependencies.length > 0;

            const newTask = {
                ...data,
                id: taskId,
                // Rule: If has dependencies, start as BLOCKED
                status: hasUnresolvedDeps ? 'BLOCKED' : 'OPEN',
                assignedTo: null
            };
            State.update('tasks', [...State.data.tasks, newTask]);
            State.addLog('TASK_CREATE', `Task ${newTask.id} created (${newTask.status}).`);
        } catch (err) {
            State.update('isLoading', false);
            throw err;
        }
        State.update('isLoading', false);
    },

    async completeTask(taskId) {
        State.update('isLoading', true);
        await this.delay();

        try {
            // Rule: Cannot move to DONE if dependencies are not resolved
            if (!RulesEngine.areDependenciesResolved(taskId, State.data.tasks)) {
                throw new Error("Cannot complete task: Dependencies are not finished.");
            }

            // Mark task as completed
            let updatedTasks = State.data.tasks.map(t => 
                t.id === taskId ? { ...t, status: 'COMPLETED' } : t
            );

            // Rule: Auto-update status of other tasks when dependencies are resolved
            updatedTasks = updatedTasks.map(t => {
                if (t.status === 'BLOCKED' && RulesEngine.areDependenciesResolved(t.id, updatedTasks)) {
                    return { ...t, status: 'OPEN' };
                }
                return t;
            });

            State.update('tasks', updatedTasks);
            State.addLog('TASK_COMPLETE', `Task ${taskId} finished. Dependent tasks updated.`);
        } catch (err) {
            State.update('isLoading', false);
            throw err;
        }
        
        State.update('isLoading', false);
    },

    async assignTask(taskId, internId) {
        State.update('isLoading', true);
        await this.delay();
        
        const task = State.data.tasks.find(t => t.id === taskId);
        if (task.status === 'BLOCKED') {
            State.update('isLoading', false);
            throw new Error("Cannot assign a blocked task.");
        }

        const updated = State.data.tasks.map(t => t.id === taskId ? { ...t, assignedTo: internId, status: 'IN_PROGRESS' } : t);
        State.update('tasks', updated);
        State.update('isLoading', false);
    }
};