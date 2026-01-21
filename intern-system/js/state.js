/**
 * CENTRAL STATE MANAGEMENT
 * Single source of truth for all application data
 */
const State = {
    data: {
        auth: {
            isAuthenticated: false,
            email: null
        },
        user: {
            role: 'MANAGER',
            internId: null
        },
        interns: [],
        tasks: [],
        logs: [],
        currentView: 'dashboard',
        isLoading: false,
        filters: {
            internSearch: '',
            taskSearch: ''
        }
    },

    listeners: [],

    subscribe(fn) {
        this.listeners.push(fn);
    },

    update(path, value) {
        if (path.includes('.')) {
            const keys = path.split('.');
            this.data[keys[0]][keys[1]] = value;
        } else {
            this.data[path] = value;
        }
        this.notify();
        this.save();
    },

    notify() {
        this.listeners.forEach(fn => fn(this.data));
    },

    save() {
        try {
            localStorage.setItem('internSystemState', JSON.stringify(this.data));
        } catch (e) {
            console.warn('Failed to save state to localStorage:', e);
        }
    },

    load() {
        try {
            const savedStr = localStorage.getItem('internSystemState');
            if (savedStr) {
                const saved = JSON.parse(savedStr);
                this.data = { ...this.data, ...saved };
                this.data.filters = {
                    internSearch: '',
                    taskSearch: '',
                    ...(saved.filters || {})
                };
                this.data.auth = {
                    isAuthenticated: false,
                    email: null,
                    ...(saved.auth || {})
                };
            }
        } catch (e) {
            console.warn('Failed to load state from localStorage:', e);
        }
    },

    addLog(action, details) {
        const log = {
            id: Date.now(),
            timestamp: new Date().toISOString(),
            user: this.data.user.role,
            action,
            details
        };
        this.data.logs.unshift(log);
        this.notify();
        this.save();
    }
};