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

    // Load from localStorage on init
    init() {
        const saved = localStorage.getItem('appData');
        if (saved) {
            try {
                const parsed = JSON.parse(saved);
                this.data = { ...this.data, ...parsed };
            } catch (e) {
                console.error('Failed to load state:', e);
            }
        }
    },

    // Save to localStorage
    save() {
        try {
            localStorage.setItem('appData', JSON.stringify(this.data));
        } catch (e) {
            console.error('Failed to save state:', e);
        }
    },

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
        this.save(); // Save after every update
        this.notify();
    },

    notify() {
        this.listeners.forEach(fn => fn(this.data));
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
        this.save(); // Save logs
        this.notify();
    }
};