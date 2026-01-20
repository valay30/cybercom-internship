/**
 * RENDERER ENGINE
 * Handles all DOM updates and UI rendering
 */
const Renderer = {
    sidebarNav: null,
    viewContainer: null,
    viewTitle: null,
    headerActions: null,
    loadingOverlay: null,
    modalPortal: null,
    modalBody: null,
    internSelector: null,
    internIdContainer: null,

    init() {
        this.sidebarNav = document.getElementById('sidebar-nav');
        this.viewContainer = document.getElementById('view-container');
        this.viewTitle = document.getElementById('view-title');
        this.headerActions = document.getElementById('header-actions');
        this.loadingOverlay = document.getElementById('loading');
        this.modalPortal = document.getElementById('modal-portal');
        this.modalBody = document.getElementById('modal-body');
        this.internSelector = document.getElementById('intern-selector');
        this.internIdContainer = document.getElementById('intern-identity-container');
    },

    render(state) {
        this.loadingOverlay.classList.toggle('hidden', !state.isLoading);
        const isInternRole = state.user.role === 'INTERN';
        document.getElementById('current-role-display').textContent = state.user.role === 'MANAGER' ? 'Operations Manager' : 'Intern User';
        this.internIdContainer.classList.toggle('hidden', !isInternRole);

        const idCtx = document.getElementById('intern-id-context');
        if (isInternRole) {
            idCtx.classList.remove('hidden');
            idCtx.textContent = `Acting as ID: ${state.user.internId || 'N/A'}`;
            this.updateInternSelector(state);
        } else {
            idCtx.classList.add('hidden');
        }

        this.renderSidebar(state);

        if (!RulesEngine.canView(state.user.role, state.currentView)) {
            State.update('currentView', 'dashboard');
            return;
        }

        this.viewTitle.textContent = state.currentView.charAt(0).toUpperCase() + state.currentView.slice(1);

        switch (state.currentView) {
            case 'dashboard': this.renderDashboard(state); break;
            case 'interns': this.renderInterns(state); break;
            case 'tasks': this.renderTasks(state); break;
            case 'logs': this.renderLogs(state); break;
        }
    },

    updateInternSelector(state) {
        const currentInternId = state.user.internId;
        this.internSelector.innerHTML = state.interns.length > 0
            ? state.interns.map(i => `<option value="${i.id}" ${i.id === currentInternId ? 'selected' : ''}>${i.name} (${i.id})</option>`).join('')
            : '<option value="">No Interns Available</option>';
    },

    renderSidebar(state) {
        const links = [
            { id: 'dashboard', label: 'Dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
            { id: 'interns', label: 'Intern Management', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
            { id: 'tasks', label: 'Task Board', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
            { id: 'logs', label: 'Audit Logs', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' }
        ];

        this.sidebarNav.innerHTML = links
            .filter(link => RulesEngine.canView(state.user.role, link.id))
            .map(link => `
                <div class="nav-link ${state.currentView === link.id ? 'active' : ''}" data-view="${link.id}">
                    <svg viewBox="0 0 24 24"><path d="${link.icon}" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>${link.label}</span>
                </div>
            `).join('');

        document.querySelectorAll('.nav-link').forEach(el => {
            el.onclick = () => State.update('currentView', el.dataset.view);
        });
    },

    renderDashboard(state) {
        this.headerActions.innerHTML = '';
        const activeCount = state.interns.filter(i => i.status === 'ACTIVE').length;
        const openTasks = state.tasks.filter(t => t.status !== 'COMPLETED').length;
        const totalEstimatedHours = state.tasks.reduce((sum, t) => sum + (parseFloat(t.estTime) || 0), 0);

        if (state.user.role === 'INTERN') {
            const currentIntern = state.interns.find(i => i.id === state.user.internId);
            const myTasks = state.tasks.filter(t => t.assignedTo === state.user.internId && t.status !== 'COMPLETED');
            const myHours = myTasks.reduce((sum, t) => sum + (parseFloat(t.estTime) || 0), 0);

            this.viewContainer.innerHTML = `
                <div style="background: linear-gradient(to right, #2563eb, #4338ca); color: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 20px rgba(37,99,235,0.2); margin-bottom: 32px;">
                    <h1 style="font-size: 2rem; margin-bottom: 8px;">Hello, ${currentIntern ? currentIntern.name : 'Intern'}!</h1>
                    <p style="opacity: 0.9;">You have <strong>${myTasks.length}</strong> active tasks, totaling <strong>${myHours} hours</strong> of work.</p>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active Profile</div>
                    <div style="font-size: 1rem; font-weight: 600;">ID: <span class="text-mono">${state.user.internId || 'No Record'}</span></div>
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 4px;">Email: ${currentIntern ? currentIntern.email : 'N/A'}</div>
                </div>
            `;
        } else {
            this.viewContainer.innerHTML = `
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Active Interns</div>
                        <div class="stat-value">${activeCount}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Open Tasks</div>
                        <div class="stat-value">${openTasks}</div>
                    </div>
                    <div class="stat-card highlight">
                        <div class="stat-label" style="color: var(--primary);">Total Project Hours</div>
                        <div class="stat-value">${totalEstimatedHours}h</div>
                    </div>
                </div>
            `;
        }
    },

    renderInterns(state) {
        this.headerActions.innerHTML = `<button id="add-intern-btn" class="btn btn-primary">+ Add Intern</button>`;

        this.viewContainer.innerHTML = `
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 80px;">ID</th>
                            <th>Name & Skills</th>
                            <th>Status</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${state.interns.length === 0 ? `<tr><td colspan="4" style="padding: 40px; text-align: center; color: var(--text-muted);">No interns onboarded yet.</td></tr>` : state.interns.map(i => `
                            <tr>
                                <td class="text-mono" style="text-align: center; font-size: 0.7rem; color: var(--text-muted);">${i.id}</td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-main);">${i.name}</div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 4px;">${i.email || ''}</div>
                                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                        ${(i.skills || []).map(s => `<span class="badge">${s}</span>`).join('') || '<span class="badge" style="color:#cbd5e1">None</span>'}
                                    </div>
                                </td>
                                <td>
                                    <span class="status-pill status-${i.status.toLowerCase()}">${i.status}</span>
                                </td>
                                <td>
                                    <select class="status-select-action" style="padding: 4px; border-radius: 4px; font-size: 0.75rem;" data-id="${i.id}">
                                        <option value="ONBOARDING" ${i.status === 'ONBOARDING' ? 'selected' : ''}>Onboarding</option>
                                        <option value="ACTIVE" ${i.status === 'ACTIVE' ? 'selected' : ''}>Active</option>
                                        <option value="EXITED" ${i.status === 'EXITED' ? 'selected' : ''}>Exited</option>
                                    </select>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        document.getElementById('add-intern-btn').onclick = () => this.showInternModal();
        document.querySelectorAll('.status-select-action').forEach(sel => {
            sel.onchange = async (e) => {
                try {
                    await FakeServer.updateStatus(e.target.dataset.id, e.target.value);
                } catch (err) {
                    this.showErrorModal(err.message);
                    this.render(State.data);
                }
            };
        });
    },

    renderTasks(state) {
        const canCreate = RulesEngine.hasAction(state.user.role, 'CREATE_TASK');
        const canAssign = RulesEngine.hasAction(state.user.role, 'ASSIGN_TASK');
        const tasksToShow = state.user.role === 'INTERN' ? state.tasks.filter(t => t.assignedTo === state.user.internId) : state.tasks;

        this.headerActions.innerHTML = canCreate ? `<button id="create-task-btn" class="btn btn-primary">+ New Task</button>` : '';

        this.viewContainer.innerHTML = `
            <div class="card-grid">
                ${tasksToShow.length === 0 ? `
                    <div style="grid-column: 1/-1; padding: 60px; text-align: center; background: white; border: 2px dashed var(--border-color); border-radius: 20px; color: var(--text-muted);">
                        No tasks available in this view.
                    </div>
                ` : tasksToShow.map(t => {
                    const isBlocked = t.status === 'BLOCKED' || !RulesEngine.areDependenciesResolved(t.id, state.tasks);
                    
                    return `
                    <div class="task-card ${isBlocked ? 'task-blocked' : ''}">
                        <div class="flex-between">
                            <h4 style="font-weight: 800; line-height: 1.2;">${t.title}</h4>
                            <span class="badge text-mono" style="font-size: 0.6rem;">${t.id}</span>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted); min-height: 40px;">${t.description || 'No description provided.'}</p>
                        
                        <div class="mt-2">
                             <div class="stat-label" style="font-size: 0.6rem; margin-bottom: 4px;">Required Skills</div>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 8px;">
                                ${(t.requiredSkills || []).map(s => `<span class="badge" style="background: #e0f2fe; color: #0369a1;">${s}</span>`).join('') || '<span class="badge">General</span>'}
                            </div>
                            <div class="stat-label" style="font-size: 0.6rem; margin-bottom: 4px;">Dependencies</div>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                ${(t.dependencies || []).map(depId => `<span class="badge" style="background: #fee2e2; color: #991b1b;">${depId}</span>`).join('') || '<span class="badge">None</span>'}
                            </div>
                        </div>

                        <div class="flex-between mt-4" style="border-top: 1px solid #f1f5f9; padding-top: 12px;">
                            <div class="stat-label" style="margin-bottom: 0;">Time: <span style="color: var(--text-main);">${t.estTime}h</span></div>
                            <span style="font-size: 0.7rem; font-weight: 800;" class="status-${t.status.toLowerCase()}">${t.status}</span>
                        </div>

                        <div style="font-size: 0.7rem; color: var(--text-muted);">
                            Assigned: <span style="font-weight: 700; color: var(--text-main);">${t.assignedTo || 'Unassigned'}</span>
                        </div>

                        <div class="flex-between gap-2 mt-4">
                            ${t.status !== 'COMPLETED' ? `
                                ${!t.assignedTo && canAssign && !isBlocked ? `<button class="btn btn-dark assign-task-btn" style="flex: 1; font-size: 0.65rem;" data-id="${t.id}">Assign</button>` : ''}
                                <button class="btn btn-success complete-task-btn" 
                                    style="flex: 1; font-size: 0.65rem;" 
                                    data-id="${t.id}"
                                    ${isBlocked ? 'disabled title="Unresolved dependencies"' : ''}>
                                    Mark Done
                                </button>
                            ` : `<div style="flex: 1; padding: 8px; background: #f8fafc; text-align: center; border-radius: 8px; font-size: 0.65rem; color: #94a3b8; font-weight: bold;">Completed</div>`}
                        </div>
                    </div>
                `}).join('')}
            </div>
        `;

        if (canCreate) document.getElementById('create-task-btn').onclick = () => this.showTaskModal();
        document.querySelectorAll('.complete-task-btn').forEach(btn => {
            btn.onclick = () => {
                if (!btn.disabled) FakeServer.completeTask(btn.dataset.id);
            };
        });
        document.querySelectorAll('.assign-task-btn').forEach(btn => btn.onclick = () => this.showAssignModal(btn.dataset.id));
    },

    renderLogs(state) {
        this.viewContainer.innerHTML = `
            <div style="background: #0f172a; color: #4ade80; padding: 24px; border-radius: 12px; font-family: monospace; font-size: 0.75rem; min-height: 400px; box-shadow: 0 10px 15px rgba(0,0,0,0.2);">
                <div style="color: #64748b; border-bottom: 1px solid #1e293b; padding-bottom: 8px; margin-bottom: 16px;"># System Audit History</div>
                ${state.logs.length === 0 ? '<div style="opacity: 0.5; font-style: italic;">Awaiting events...</div>' : state.logs.map(l => `
                    <div style="margin-bottom: 4px; display: flex; gap: 12px;">
                        <span style="color: #475569;">[${l.timestamp.split('T')[1].split('.')[0]}]</span>
                        <span style="color: #3b82f6; font-weight: bold;">${l.user}</span>
                        <span style="color: #e2e8f0;">${l.action}: ${l.details}</span>
                    </div>
                `).join('')}
            </div>
        `;
    },

    showErrorModal(message) {
        this.modalPortal.classList.remove('hidden');
        this.modalBody.innerHTML = `
            <div style="text-align: center;">
                <div style="width: 64px; height: 64px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 2rem; font-weight: bold;">!</div>
                <h3 style="font-weight: 800; margin-bottom: 8px;">Action Blocked</h3>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 24px;">${message}</p>
                <button class="btn btn-dark" style="width: 100%;" onclick="Renderer.closeModal()">Dismiss</button>
            </div>
        `;
    },

    showInternModal() {
        this.modalPortal.classList.remove('hidden');
        this.modalBody.innerHTML = `
            <h3 style="font-weight: 800; margin-bottom: 24px;">Onboard New Intern</h3>
            <form id="intern-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required class="form-control" placeholder="e.g. Valay Patel">
                </div>
                <div class="form-group">
                    <label>Email ID</label>
                    <input type="email" name="email" required class="form-control" placeholder="valay@gmail.com">
                </div>
                <div class="form-group">
                    <label>Skills (comma separated)</label>
                    <input type="text" name="skills" class="form-control" placeholder="React, SQL, Node">
                </div>
                <div class="flex-between gap-2 mt-4" style="justify-content: flex-end;">
                    <button type="button" class="btn btn-ghost" onclick="Renderer.closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Profile</button>
                </div>
            </form>
        `;
        const form = document.getElementById('intern-form');
        form.onsubmit = async (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            try {
                await FakeServer.createIntern({
                    name: fd.get('name'),
                    email: fd.get('email'),
                    skills: fd.get('skills').split(',').map(s => s.trim()).filter(s => s)
                });
                this.closeModal();
            } catch (err) {
                this.showErrorModal(err.message);
            }
        };
    },

    showTaskModal() {
        this.modalPortal.classList.remove('hidden');
        const tasks = State.data.tasks;

        this.modalBody.innerHTML = `
            <h3 style="font-weight: 800; margin-bottom: 24px;">Define New Task</h3>
            <form id="task-form">
                <div class="form-group">
                    <label>Task Name</label>
                    <input type="text" name="title" required class="form-control" placeholder="Project Feature Name">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" style="height: 60px; resize: none;" placeholder="Requirements..."></textarea>
                </div>
                <div class="form-group">
                    <label>Required Skills (comma separated)</label>
                    <input type="text" name="requiredSkills" class="form-control" placeholder="e.g. React, SQL, Node">
                </div>
                <div class="form-group">
                    <label>Dependencies (Select multiple with Ctrl/Cmd)</label>
                    <select name="dependencies" multiple class="form-control" style="height: 80px;">
                        ${tasks.map(t => `<option value="${t.id}">${t.id}: ${t.title}</option>`).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Est. Time (Hours)</label>
                    <input type="number" name="estTime" required class="form-control" placeholder="8">
                </div>
                <div class="flex-between gap-2 mt-4" style="justify-content: flex-end;">
                    <button type="button" class="btn btn-ghost" onclick="Renderer.closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        `;
        const form = document.getElementById('task-form');
        form.onsubmit = async (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            
            try {
                await FakeServer.createTask({
                    title: fd.get('title'),
                    description: fd.get('description'),
                    estTime: fd.get('estTime'),
                    dependencies: fd.getAll('dependencies'),
                    // NEW: Capture and parse required skills
                    requiredSkills: fd.get('requiredSkills').split(',').map(s => s.trim()).filter(s => s)
                });
                this.closeModal();
            } catch (err) {
                this.showErrorModal(err.message);
            }
        };
    },

    showAssignModal(taskId) {
        const task = State.data.tasks.find(t => t.id === taskId);
        const required = task.requiredSkills || [];

        const activeAndQualified = State.data.interns.filter(i => {
            const isActive = i.status === 'ACTIVE';
            // Logic: Intern must have ALL skills listed in the task requirements
            const hasSkills = required.every(skill => (i.skills || []).includes(skill));
            return isActive && hasSkills;
        });

        this.modalPortal.classList.remove('hidden');
        this.modalBody.innerHTML = `
            <h3 style="font-weight: 800; text-align: center; margin-bottom: 8px;">Assign Task ${taskId}</h3>
            <p class="stat-label" style="text-align: center; margin-bottom: 24px;">Required: ${required.join(', ') || 'General'}</p>
            
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 24px;">
                ${activeAndQualified.length === 0 ? `
                    <div style="padding: 24px; text-align: center; background: #f8fafc; border-radius: 12px; border: 1px dashed var(--border-color);">
                        <p style="font-size: 0.75rem; color: var(--danger); font-style: italic;">No ACTIVE interns meet the skill requirements.</p>
                    </div>
                ` : activeAndQualified.map(i => `
                    <div class="assign-action-row" data-intern="${i.id}" style="padding: 12px; border: 1px solid var(--border-color); border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; cursor: pointer; transition: 0.2s;">
                        <div>
                            <div style="font-size: 0.875rem; font-weight: 700;">${i.name}</div>
                            <div class="text-mono" style="font-size: 0.6rem; color: var(--text-muted);">${i.id}</div>
                        </div>
                        <span style="font-size: 0.65rem; font-weight: 800; color: var(--primary);">SELECT</span>
                    </div>
                `).join('')}
            </div>
            <button class="btn btn-ghost" style="width: 100%;" onclick="Renderer.closeModal()">Close</button>
        `;

        document.querySelectorAll('.assign-action-row').forEach(el => {
            el.onclick = () => {
                FakeServer.assignTask(taskId, el.dataset.intern);
                this.closeModal();
            };
            el.onmouseover = () => { el.style.backgroundColor = '#eff6ff'; el.style.borderColor = 'var(--primary)'; };
            el.onmouseout = () => { el.style.backgroundColor = 'transparent'; el.style.borderColor = 'var(--border-color)'; };
        });
    },

    closeModal() { 
        this.modalPortal.classList.add('hidden'); 
    }
};