/**
 * ASYNC FAKE SERVER
 * Simulates server-side operations with delays (NO REAL APIs)
 */
const FakeServer = {
    async delay(ms = 400) { 
        return new Promise(r => setTimeout(r, ms)); 
    },

    async createIntern(data) {
        State.update('isLoading', true);
        await this.delay();

        try {
            // Validate data
            Validators.validateInternData(data);
            Validators.checkEmailUniqueness(data.email, State.data.interns);

            const newIntern = {
                ...data,
                id: RulesEngine.generateInternId(),
                createdAt: new Date().toISOString(),
                status: 'ONBOARDING'
            };
            State.update('interns', [...State.data.interns, newIntern]);

            if (!State.data.user.internId) {
                State.update('user.internId', newIntern.id);
            }

            State.addLog('CREATE_INTERN', `Intern ${newIntern.id} onboarded.`);
        } catch (err) {
            State.update('isLoading', false);
            throw err;
        }
        
        State.update('isLoading', false);
    },

    async updateStatus(id, status) {
        State.update('isLoading', true);
        await this.delay();

        try {
            const intern = State.data.interns.find(i => i.id === id);
            if (!intern) {
                throw new Error("Intern record not found.");
            }

            if (!RulesEngine.canTransitionIntern(intern.status, status)) {
                throw new Error(`Forbidden: Cannot transition from ${intern.status} to ${status}.`);
            }

            const updated = State.data.interns.map(i => i.id === id ? { ...i, status } : i);
            State.update('interns', updated);
            State.addLog('STATUS_CHANGE', `${id} status changed to ${status}`);
        } catch (err) {
            State.update('isLoading', false);
            throw err;
        }
        
        State.update('isLoading', false);
    },

    async createTask(data) {
        State.update('isLoading', true);
        await this.delay();

        try {
            Validators.validateTaskData(data);

            const newTask = {
                ...data,
                id: RulesEngine.generateTaskId(),
                status: 'OPEN',
                assignedTo: null
            };
            State.update('tasks', [...State.data.tasks, newTask]);
            State.addLog('TASK_CREATE', `New Task ${newTask.id} created.`);
        } catch (err) {
            State.update('isLoading', false);
            throw err;
        }
        
        State.update('isLoading', false);
    },

    async assignTask(taskId, internId) {
        State.update('isLoading', true);
        await this.delay();
        
        const updated = State.data.tasks.map(t => t.id === taskId ? { ...t, assignedTo: internId, status: 'IN_PROGRESS' } : t);
        State.update('tasks', updated);
        State.addLog('TASK_ASSIGN', `Task ${taskId} assigned to Intern ${internId}.`);
        
        State.update('isLoading', false);
    },

    async completeTask(taskId) {
        State.update('isLoading', true);
        await this.delay();
        
        const updated = State.data.tasks.map(t => t.id === taskId ? { ...t, status: 'COMPLETED' } : t);
        State.update('tasks', updated);
        State.addLog('TASK_COMPLETE', `Task ${taskId} marked as finished.`);
        
        State.update('isLoading', false);
    }
};