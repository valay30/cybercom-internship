/**
 * VALIDATORS
 */
const Validators = {
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            throw new Error('Invalid email format.');
        }
        return true;
    },

    // NEW: Detects if adding dependencies creates a circular loop
    checkCircularDependency(taskId, requestedDeps, allTasks) {
        const checkMap = new Map(allTasks.map(t => [t.id, t.dependencies || []]));
        
        const hasLoop = (currentId, path = new Set()) => {
            if (path.has(currentId)) return true;
            path.add(currentId);
            
            const deps = currentId === taskId ? requestedDeps : (checkMap.get(currentId) || []);
            for (const depId of deps) {
                if (hasLoop(depId, new Set(path))) return true;
            }
            return false;
        };

        if (hasLoop(taskId)) {
            throw new Error('Circular dependency detected! A task cannot depend on itself or its own descendants.');
        }
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