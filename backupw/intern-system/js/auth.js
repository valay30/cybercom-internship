/**
 * AUTHENTICATION MODULE
 * Handles login, signup, and session management
 */
const Auth = {
    users: JSON.parse(localStorage.getItem('users')) || [],
    currentUser: JSON.parse(localStorage.getItem('currentUser')) || null,

    signup(email, password, role, name) {
        // Validate
        if (!email || !password || !role || !name) {
            throw new Error('All fields are required');
        }
        
        if (this.users.find(u => u.email === email)) {
            throw new Error('Email already registered');
        }

        if (password.length < 6) {
            throw new Error('Password must be at least 6 characters');
        }

        const user = {
            id: Date.now().toString(),
            email,
            password, // In production, hash this!
            role,
            name,
            createdAt: new Date().toISOString()
        };

        this.users.push(user);
        localStorage.setItem('users', JSON.stringify(this.users));
        return user;
    },

    login(email, password) {
        const user = this.users.find(u => u.email === email && u.password === password);
        
        if (!user) {
            throw new Error('Invalid email or password');
        }

        this.currentUser = user;
        localStorage.setItem('currentUser', JSON.stringify(user));
        return user;
    },

    logout() {
        this.currentUser = null;
        localStorage.removeItem('currentUser');
        window.location.reload();
    },

    isAuthenticated() {
        return this.currentUser !== null;
    },

    getCurrentUser() {
        return this.currentUser;
    }
};