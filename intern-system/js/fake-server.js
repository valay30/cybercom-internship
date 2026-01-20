/**
 * ASYNC FAKE SERVER
 */
        const FakeServer = {
            async delay(ms = 400) { return new Promise(r => setTimeout(r, ms)); },

            async createIntern(data) {
                State.update('isLoading', true);
                await this.delay();

                // EMAIL UNIQUENESS ASYNC CHECK
                const emailExists = State.data.interns.some(i => i.email.toLowerCase() === data.email.toLowerCase());
                if (emailExists) {
                    State.update('isLoading', false);
                    throw new Error(`Conflict: An intern with email ${data.email} already exists.`);
                }

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
                State.update('isLoading', false);
            },

            async updateStatus(id, status) {
                State.update('isLoading', true);
                await this.delay();

                const intern = State.data.interns.find(i => i.id === id);
                if (!intern) {
                    State.update('isLoading', false);
                    throw new Error("Intern record not found.");
                }

                if (!RulesEngine.canTransitionIntern(intern.status, status)) {
                    State.update('isLoading', false);
                    throw new Error(`Forbidden: Cannot transition from ${intern.status} to ${status}.`);
                }

                const updated = State.data.interns.map(i => i.id === id ? { ...i, status } : i);
                State.update('interns', updated);
                State.addLog('STATUS_CHANGE', `${id} status changed to ${status}`);
                State.update('isLoading', false);
            },

            async createTask(data) {
                State.update('isLoading', true);
                await this.delay();
                const newTask = {
                    ...data,
                    id: RulesEngine.generateTaskId(),
                    status: 'OPEN',
                    assignedTo: null
                };
                State.update('tasks', [...State.data.tasks, newTask]);
                State.addLog('TASK_CREATE', `New Task ${newTask.id} created.`);
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
