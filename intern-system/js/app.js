//Initializes the application and sets up event handlers

const App = {
    init() {
        // Load saved state from localStorage
        State.load();

        // Initialize renderer
        Renderer.init();

        // Subscribe to state changes
        State.subscribe(state => Renderer.render(state));

        // Role Switching
        document.getElementById('role-switcher').onchange = (e) => {
            State.update('user.role', e.target.value);
        };

        // Intern Profile Switching
        document.getElementById('intern-selector').onchange = (e) => {
            State.update('user.internId', e.target.value);
        };

        // Initial log and render
        State.addLog('SYSTEM', 'Operational Engine Started with modular architecture.');
        Renderer.render(State.data);
    }
};

// Start application when DOM is ready
window.onload = () => App.init();