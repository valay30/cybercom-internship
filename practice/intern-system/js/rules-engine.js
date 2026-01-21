/**
 * BUSINESS RULES ENGINE
 */
const RulesEngine = {
    permissions: {
        'MANAGER': {
            views: ['dashboard', 'interns', 'tasks', 'logs'],
            actions: ['CREATE_INTERN', 'UPDATE_STATUS', 'UPDATE_INTERN', 'CREATE_TASK', 'UPDATE_TASK', 'DELETE_TASK', 'ASSIGN_TASK', 'COMPLETE_TASK']
        },
        'INTERN': {
            views: ['dashboard'],
            actions: ['COMPLETE_TASK']
        }
    },

    canView(role, view) {
        return this.permissions[role]?.views.includes(view);
    },

    hasAction(role, action) {
        return this.permissions[role]?.actions.includes(action);
    },

    // NEW: Check if all dependencies for a task are COMPLETED
    areDependenciesResolved(taskId, allTasks) {
        const task = allTasks.find(t => t.id === taskId);
        if (!task || !task.dependencies || task.dependencies.length === 0) return true;
        
        return task.dependencies.every(depId => {
            const depTask = allTasks.find(t => t.id === depId);
            return depTask && depTask.status === 'COMPLETED';
        });
    },

    canTransitionIntern(current, next) {
        if (current === next) return true;
        const allowed = {
            'ONBOARDING': ['ACTIVE'],
            'ACTIVE': ['EXITED'],
            'EXITED': []
        };
        return allowed[current]?.includes(next) || false;
    },

    generateInternId() {
        const year = new Date().getFullYear();
        return `${year}-${(State.data.interns.length + 1).toString().padStart(4, '0')}`;
    },

    generateTaskId() {
        const lastId = State.data.tasks.length > 0 ? parseInt(State.data.tasks[State.data.tasks.length - 1].id.split('-')[1]) : 100;
        return `T-${lastId + 1}`;
    }
};