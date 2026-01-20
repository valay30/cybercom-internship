/**
 * CENTRAL STATE MANAGEMENT
 * Single source of truth for all application data
 */
const State = {
    data: {
        user: {
            role: 'MANAGER',
            internId: null
        },
        interns: [],
        tasks: [],
        logs: [],
        currentView: 'dashboard',
        isLoading: false
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
            const saved = localStorage.getItem('internSystemState');
            if (saved) {
                this.data = { ...this.data, ...JSON.parse(saved) };
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