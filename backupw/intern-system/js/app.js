/**
 * BOOTSTRAP & EVENT WIRING
 * Initializes the application and sets up event handlers
 */
const App = {
    init() {
        // Initialize state
        State.init();

        // Check authentication
        if (!Auth.isAuthenticated()) {
            this.showAuthPage();
            return;
        }

        // Set user role from logged in user
        const currentUser = Auth.getCurrentUser();
        State.update('user.role', currentUser.role);

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

        // Add logout functionality
        this.addLogoutButton();

        // Initial log and render
        State.addLog('SYSTEM', `User ${currentUser.name} logged in as ${currentUser.role}.`);
        Renderer.render(State.data);
    },

    addLogoutButton() {
        const footer = document.querySelector('.sidebar-footer');
        const logoutBtn = document.createElement('button');
        logoutBtn.className = 'btn btn-danger';
        logoutBtn.textContent = 'Logout';
        logoutBtn.style.width = '100%';
        logoutBtn.style.marginTop = '12px';
        logoutBtn.onclick = () => Auth.logout();
        footer.appendChild(logoutBtn);
    },

    showAuthPage() {
        document.getElementById('app').innerHTML = `
            <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 100%; max-width: 420px;">
                    <h1 style="text-align: center; margin-bottom: 8px; font-size: 2rem; color: #1e293b;">Intern System</h1>
                    
                    <div id="auth-tabs" style="display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 2px solid #e2e8f0;">
                        <button class="auth-tab active" data-tab="login">Login</button>
                        <button class="auth-tab" data-tab="signup">Sign Up</button>
                    </div>

                    <div id="login-form" class="auth-form-container">
                        <form id="login-form-element">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" required class="form-control" placeholder="your@email.com">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" required class="form-control" placeholder="••••••••">
                            </div>
                            <div id="login-error" style="color: #ef4444; font-size: 0.75rem; margin-bottom: 12px; display: none;"></div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                        </form>
                    </div>

                    <div id="signup-form" class="auth-form-container" style="display: none;">
                        <form id="signup-form-element">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" required class="form-control" placeholder="John Doe">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" required class="form-control" placeholder="your@email.com">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" required class="form-control" placeholder="Min 6 characters">
                            </div>
                            <div class="form-group">
                                <label>Register As</label>
                                <select name="role" required class="form-control">
                                    <option value="">Select Role</option>
                                    <option value="MANAGER">Manager</option>
                                    <option value="INTERN">Intern</option>
                                </select>
                            </div>
                            <div id="signup-error" style="color: #ef4444; font-size: 0.75rem; margin-bottom: 12px; display: none;"></div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
                        </form>
                    </div>
                </div>
            </div>

            <style>
                .auth-tab {
                    flex: 1;
                    padding: 12px;
                    border: none;
                    background: transparent;
                    color: #64748b;
                    font-weight: 600;
                    cursor: pointer;
                    border-bottom: 3px solid transparent;
                    transition: 0.2s;
                }
                .auth-tab.active {
                    color: #2563eb;
                    border-bottom-color: #2563eb;
                }
            </style>
        `;

        // Tab switching
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.onclick = () => {
                document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                document.getElementById('login-form').style.display = tab.dataset.tab === 'login' ? 'block' : 'none';
                document.getElementById('signup-form').style.display = tab.dataset.tab === 'signup' ? 'block' : 'none';
            };
        });

        // Login form
        document.getElementById('login-form-element').onsubmit = (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const errorDiv = document.getElementById('login-error');

            try {
                Auth.login(fd.get('email'), fd.get('password'));
                window.location.reload();
            } catch (err) {
                errorDiv.textContent = err.message;
                errorDiv.style.display = 'block';
            }
        };

        // Signup form
        document.getElementById('signup-form-element').onsubmit = (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const errorDiv = document.getElementById('signup-error');

            try {
                Auth.signup(
                    fd.get('email'),
                    fd.get('password'),
                    fd.get('role'),
                    fd.get('name')
                );
                alert('Account created! Please login.');
                document.querySelector('[data-tab="login"]').click();
                e.target.reset();
            } catch (err) {
                errorDiv.textContent = err.message;
                errorDiv.style.display = 'block';
            }
        };
    }
};

// Start application when DOM is ready
window.onload = () => App.init();