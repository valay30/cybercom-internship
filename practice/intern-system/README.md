# Intern System (Practice Project)

A single-page **Intern Management + Task Board** app built with **vanilla HTML/CSS/JavaScript** (no framework).  
It runs fully in the browser and uses `localStorage` for persistence (including demo auth).

## Features

- **Authentication**
  - **Login / Sign Up** screen
  - Roles:
    - **Manager**: full access (Dashboard, Intern Management, Task Board, Audit Logs)
    - **Intern**: **Dashboard only**
  - Logout button

- **Intern Management (Manager)**
  - Add intern
  - Edit intern
  - Search interns (name/email/ID/skills/status)
  - Change intern status: `ONBOARDING → ACTIVE → EXITED`

- **Task Board (Manager)**
  - Create task (skills + dependencies)
  - Edit task
  - Delete task (blocked if other tasks depend on it)
  - Assign task to qualified interns (skills-based)
  - Search tasks (ID/title/description/assignee/status/skills)

- **Intern Dashboard (Intern)**
  - Shows profile + status (ONBOARDING/ACTIVE)
  - Shows **My Assigned Tasks**
  - Intern can **Mark Done** only for tasks assigned to them (dependency-safe)

- **Audit Logs**
  - Tracks key actions (signup/login/logout/create/update/delete)

## Tech Stack

- **Frontend**: HTML + CSS + Vanilla JavaScript
- **Storage**: `localStorage`

## Project Structure

```
intern-system/
  index.html
  css/
    reset.css
    layout.css
    components.css
  js/
    app.js
    state.js
    rules-engine.js
    validators.js
    fake-server.js
    renderer.js
```

## Run Locally

Because this uses `localStorage`, it works best when served via a local web server.

### Option 1: VS Code Live Server

- Install the **Live Server** extension
- Right click `index.html` → **Open with Live Server**

### Option 2: Python

From the project folder:

```bash
python -m http.server 5173
```

Then open `http://localhost:5173`.

## Demo Notes / Data Storage

- App state is saved in:
  - `localStorage["internSystemState"]`
- Demo user accounts are saved in:
  - `localStorage["internSystemUsers"]`

To reset everything, clear those keys in the browser devtools (Application → Local Storage).

## Security Note

Passwords are stored **in plain text** in `localStorage` because this is a practice/demo project.  
Do **not** use this approach for real applications.

