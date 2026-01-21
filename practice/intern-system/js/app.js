//Initializes the application and sets up event handlers

const App = {
    init() {
        // Load saved state from localStorage
        State.load();

        // Initialize renderer
        Renderer.init();

        // Subscribe to state changes
        State.subscribe(state => Renderer.render(state));

        // Intern Profile Switching
        document.getElementById('intern-selector').onchange = (e) => {
            State.update('user.internId', e.target.value);
        };

        // Logout
        document.getElementById('logout-btn').onclick = async () => {
            try {
                await FakeServer.logout();
            } catch (err) {
                console.error(err);
            }
        };

        // Auth UI
        const authScreen = document.getElementById('auth-screen');
        const appRoot = document.getElementById('app');
        const loginTab = document.getElementById('auth-tab-login');
        const signupTab = document.getElementById('auth-tab-signup');
        const loginForm = document.getElementById('login-form');
        const signupForm = document.getElementById('signup-form');
        const authError = document.getElementById('auth-error');

        const showError = (msg) => {
            authError.textContent = msg;
            authError.classList.toggle('hidden', !msg);
        };

        const setMode = (mode) => {
            const isLogin = mode === 'login';
            loginForm.classList.toggle('hidden', !isLogin);
            signupForm.classList.toggle('hidden', isLogin);
            loginTab.classList.toggle('btn-primary', isLogin);
            loginTab.classList.toggle('btn-ghost', !isLogin);
            signupTab.classList.toggle('btn-primary', !isLogin);
            signupTab.classList.toggle('btn-ghost', isLogin);
            if (!isLogin) signupTab.style.border = 'none';
            else signupTab.style.border = '1px solid var(--border-color)';
            if (!isLogin) loginTab.style.border = '1px solid var(--border-color)';
            else loginTab.style.border = 'none';
            showError('');
        };

        loginTab.onclick = () => setMode('login');
        signupTab.onclick = () => setMode('signup');

        loginForm.onsubmit = async (e) => {
            e.preventDefault();
            const fd = new FormData(loginForm);
            try {
                await FakeServer.login({
                    email: fd.get('email'),
                    password: fd.get('password')
                });
                showError('');
            } catch (err) {
                showError(err.message);
            }
        };

        signupForm.onsubmit = async (e) => {
            e.preventDefault();
            const fd = new FormData(signupForm);
            try {
                await FakeServer.signup({
                    name: fd.get('name'),
                    email: fd.get('email'),
                    password: fd.get('password'),
                    role: fd.get('role')
                });
                showError('');
            } catch (err) {
                showError(err.message);
            }
        };

        // Default to login screen
        setMode('login');

        // Initial log and render
        State.addLog('SYSTEM', 'Operational Engine Started with modular architecture.');
        Renderer.render(State.data);
    }
};

// Start application when DOM is ready
window.onload = () => App.init();