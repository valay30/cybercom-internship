/**
 * BUSINESS RULES ENGINE
 * Contains all business logic and permissions
 */
const RulesEngine = {
    permissions: {
        'MANAGER': {
            views: ['dashboard', 'interns', 'tasks', 'logs'],
            actions: ['CREATE_INTERN', 'UPDATE_STATUS', 'CREATE_TASK', 'ASSIGN_TASK', 'COMPLETE_TASK']
        },
        'INTERN': {
            views: ['dashboard', 'tasks'],
            actions: ['COMPLETE_TASK']
        }
    },

    canView(role, view) {
        return this.permissions[role]?.views.includes(view);
    },

    hasAction(role, action) {
        return this.permissions[role]?.actions.includes(action);
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