/**
 * VALIDATORS
 * Form and data validation logic
 */
const Validators = {
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            throw new Error('Invalid email format.');
        }
        return true;
    },

    validateInternData(data) {
        if (!data.name || data.name.trim().length === 0) {
            throw new Error('Name is required.');
        }
        if (!data.email || data.email.trim().length === 0) {
            throw new Error('Email is required.');
        }
        this.validateEmail(data.email);
        return true;
    },

    validateTaskData(data) {
        if (!data.title || data.title.trim().length === 0) {
            throw new Error('Task title is required.');
        }
        if (!data.estTime || parseFloat(data.estTime) <= 0) {
            throw new Error('Estimated time must be greater than 0.');
        }
        return true;
    },

    checkEmailUniqueness(email, interns) {
        const emailExists = interns.some(i => i.email.toLowerCase() === email.toLowerCase());
        if (emailExists) {
            throw new Error(`Conflict: An intern with email ${email} already exists.`);
        }
        return true;
    }
};