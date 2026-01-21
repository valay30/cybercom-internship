/**
 * ASYNC FAKE SERVER
 */
const FakeServer = {
    async delay(ms = 400) { return new Promise(r => setTimeout(r, ms)); },

    _loadUsers() {
        try {
            return JSON.parse(localStorage.getItem('internSystemUsers') || '[]');
        } catch {
            return [];
        }
    },

    _saveUsers(users) {
        localStorage.setItem('internSystemUsers', JSON.stringify(users));
    },

    async signup({ name, email, password, role }) {
        State.update('isLoading', true);
        await this.delay();

        if (!email || !password || !role) {
            State.update('isLoading', false);
            throw new Error('All fields are required.');
        }
        Validators.validateEmail(email);

        const users = this._loadUsers();
        const exists = users.some(u => u.email.toLowerCase() === email.toLowerCase());
        if (exists) {
            State.update('isLoading', false);
            throw new Error('An account with this email already exists.');
        }

        let internId = null;
        if (role === 'INTERN') {
            const internEmailExists = State.data.interns.some(i => i.email.toLowerCase() === email.toLowerCase());
            if (internEmailExists) {
                State.update('isLoading', false);
                throw new Error(`Conflict: An intern with email ${email} already exists.`);
            }
            // Create an intern profile tied to this account
            const newIntern = {
                id: RulesEngine.generateInternId(),
                name: name || email.split('@')[0],
                email,
                skills: [],
                createdAt: new Date().toISOString(),
                status: 'ONBOARDING'
            };
            State.update('interns', [...State.data.interns, newIntern]);
            internId = newIntern.id;
        }

        users.push({
            id: Date.now(),
            name: name || '',
            email,
            password, // demo only
            role,
            internId
        });
        this._saveUsers(users);

        State.update('auth.isAuthenticated', true);
        State.update('auth.email', email);
        State.update('user.role', role);
        State.update('user.internId', internId);
        State.update('currentView', 'dashboard');
        State.addLog('SIGNUP', `${role} account created for ${email}`);
        State.update('isLoading', false);
    },

    async login({ email, password }) {
        State.update('isLoading', true);
        await this.delay();

        if (!email || !password) {
            State.update('isLoading', false);
            throw new Error('Email and password are required.');
        }

        const users = this._loadUsers();
        const user = users.find(u => u.email.toLowerCase() === email.toLowerCase() && u.password === password);
        if (!user) {
            State.update('isLoading', false);
            throw new Error('Invalid email or password.');
        }

        State.update('auth.isAuthenticated', true);
        State.update('auth.email', user.email);
        State.update('user.role', user.role);
        State.update('user.internId', user.internId || null);
        State.update('currentView', 'dashboard');
        State.addLog('LOGIN', `${user.role} logged in: ${user.email}`);
        State.update('isLoading', false);
    },

    async logout() {
        State.update('isLoading', true);
        await this.delay(150);
        State.addLog('LOGOUT', `${State.data.auth.email || 'user'} logged out.`);
        State.update('auth.isAuthenticated', false);
        State.update('auth.email', null);
        State.update('user.role', 'INTERN');
        State.update('user.internId', null);
        State.update('currentView', 'dashboard');
        State.update('isLoading', false);
    },

    async createIntern(data) {
        State.update('isLoading', true);
        await this.delay();

        Validators.validateEmail(data.email);
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

    async updateIntern(id, data) {
        State.update('isLoading', true);
        await this.delay();

        const intern = State.data.interns.find(i => i.id === id);
        if (!intern) {
            State.update('isLoading', false);
            throw new Error("Intern record not found.");
        }

        Validators.validateEmail(data.email);
        const emailExists = State.data.interns.some(i => i.id !== id && i.email.toLowerCase() === data.email.toLowerCase());
        if (emailExists) {
            State.update('isLoading', false);
            throw new Error(`Conflict: An intern with email ${data.email} already exists.`);
        }

        const updated = State.data.interns.map(i => i.id === id ? { ...i, ...data } : i);
        State.update('interns', updated);
        State.addLog('UPDATE_INTERN', `Intern ${id} profile updated.`);
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
        Validators.validateTaskData(data);
        Validators.checkCircularDependency('NEW_TASK', data.dependencies || [], State.data.tasks);
        
        const newTask = {
            ...data, // Correctly includes requiredSkills from the form data
            id: RulesEngine.generateTaskId(),
            status: 'OPEN',
            assignedTo: null
        };
        
        State.update('tasks', [...State.data.tasks, newTask]);
        State.addLog('TASK_CREATE', `New Task ${newTask.id} created.`);
        State.update('isLoading', false);
    },

    async updateTask(taskId, data) {
        State.update('isLoading', true);
        await this.delay();

        const task = State.data.tasks.find(t => t.id === taskId);
        if (!task) {
            State.update('isLoading', false);
            throw new Error('Task not found.');
        }

        Validators.validateTaskData(data);
        const otherTasks = State.data.tasks.filter(t => t.id !== taskId);
        Validators.checkCircularDependency(taskId, data.dependencies || [], otherTasks);

        const updated = State.data.tasks.map(t => t.id === taskId ? { ...t, ...data } : t);
        State.update('tasks', updated);
        State.addLog('UPDATE_TASK', `Task ${taskId} updated.`);
        State.update('isLoading', false);
    },

    async deleteTask(taskId) {
        State.update('isLoading', true);
        await this.delay();

        const task = State.data.tasks.find(t => t.id === taskId);
        if (!task) {
            State.update('isLoading', false);
            throw new Error('Task not found.');
        }

        const dependents = State.data.tasks.filter(t => (t.dependencies || []).includes(taskId));
        if (dependents.length > 0) {
            State.update('isLoading', false);
            throw new Error(`Cannot delete ${taskId}. It is a dependency for: ${dependents.map(d => d.id).join(', ')}`);
        }

        const updated = State.data.tasks.filter(t => t.id !== taskId);
        State.update('tasks', updated);
        State.addLog('DELETE_TASK', `Task ${taskId} deleted.`);
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
        const task = State.data.tasks.find(t => t.id === taskId);
        if (!task) {
            State.update('isLoading', false);
            throw new Error('Task not found.');
        }

        // Intern safety: can only complete tasks assigned to self
        if (State.data.user.role === 'INTERN') {
            if (!State.data.user.internId || task.assignedTo !== State.data.user.internId) {
                State.update('isLoading', false);
                throw new Error('Forbidden: You can only complete tasks assigned to you.');
            }
        }

        // Dependency safety
        if (!RulesEngine.areDependenciesResolved(taskId, State.data.tasks)) {
            State.update('isLoading', false);
            throw new Error('Blocked: Unresolved dependencies.');
        }

        const updated = State.data.tasks.map(t => t.id === taskId ? { ...t, status: 'COMPLETED' } : t);
        State.update('tasks', updated);
        State.addLog('TASK_COMPLETE', `Task ${taskId} marked as finished.`);
        State.update('isLoading', false);
    }
};