# Project Management Application

<div align="center">

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=flat-square&logo=jquery&logoColor=white)](https://jquery.com/)
[![AJAX](https://img.shields.io/badge/AJAX-007ACC?style=flat-square&logoColor=white)](https://api.jquery.com/jquery.ajax/)
[![SortableJS](https://img.shields.io/badge/SortableJS-2C3E50?style=flat-square&logo=javascript&logoColor=white)](https://sortablejs.github.io/Sortable/)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![Toastr](https://img.shields.io/badge/Toastr-FF9800?style=flat-square&logoColor=white)](https://codeseven.github.io/toastr/)
[![JSDoc](https://img.shields.io/badge/JSDoc-3B82F6?style=flat-square&logoColor=white)](https://jsdoc.app/)
[![PHPDoc](https://img.shields.io/badge/PHPDoc-8892BF?style=flat-square&logoColor=white)](https://phpdoc.org/)

</div>

## Project Description

This repository contains a student-developed project management software, created as part of the Brainster curriculum under the mentorship of Filip Manev. The application serves as a simplified task and project management system with a hierarchical user structure, including roles such as Admin, Senior (Team Lead or regular), Mid, and Junior. It demonstrates hands-on experience with role-based access control (RBAC), CRUD operations, database interactions, and core web development skills using PHP, SQL, and JavaScript.

The system is divided into a regular application for users and an Admin panel for system control. Projects are assigned to Senior Team Leads, who manage teams and tasks. Tasks (tickets) include statuses, assignments, and comments with full CRUD capabilities.

## Key Features

- **User Authentication**: Registration (with Admin approval), login (name/email), password change, and logout. Passwords are hashed for security.
- **Admin Panel**: Manage users (create, edit, approve, delete, reset passwords) and projects (create, update, assign to Team Leads, delete).
- **Project Management**: Create projects with details (title, description, requirements, estimated time, deadline); assign to Team Leads; add/remove team members (by Leads).
- **Task Management**: Create, assign, update status (To Do, In Progress, QA, Done), edit, delete tasks; Kanban view with drag-and-drop; auto-comments on status changes.
- **Comments**: Add, edit (marked as edited), delete comments on tasks; role-based restrictions.
- **RBAC**: Permissions enforced per role (e.g., Seniors manage tasks in assigned projects; Mids/Juniors limited).
- **UI Enhancements**: Bootstrap for responsive design; AJAX for forms/modals; toasts(toastr) and loaders(ajax) for feedback.

## Technology Stack

- **Backend**: PHP (OOP) for server logic, controllers, and models.
- **Database**: MySQL for data storage (users, projects, tasks, comments).
- **Frontend**: HTML/CSS with Bootstrap for styling; JavaScript/jQuery/toastr for interactivity (AJAX, drag-drop via Sortable.js, toastr for notifications).
- **Security**: Prepared statements for SQL; password hashing; session-based auth.

## Technologies Used

- **PHP** - Backend scripting and server-side logic
- **MySQL** - Relational database for data storage
- **JavaScript** - Client-side interactivity and dynamic features
- **Bootstrap** - Responsive front-end framework for fast UI development
- **jQuery** - Simplified DOM manipulation and AJAX handling
- **SortableJS** - Drag-and-drop sorting for tasks and UI elements
- **HTML5** - Semantic structure and modern web standards
- **CSS3** - Styling, animations, and responsive design
- **Toastr** - Elegant, non-blocking toast notifications
- **JSDoc** - Inline documentation generation for JavaScript code
- **PHPDoc** - Inline documentation generation for PHP code

## Installation and Setup

### Prerequisites

- PHP 7.4+ with PDO extension.
- MySQL database server.
- Web server (e.g., Apache, or PHP built-in).

### Steps

1. **Clone the Repository**:

   ```bash
   git clone https://git.brainster.co/Dejan.Nikolovski-FS21/Brainster-Project-2-PMA.git
   cd <name of the folder>

   ```

2. Database Configuration:

- Edit backend/includes/classes/Config.php with your MySQL credentials (DB_HOST, DB_NAME, etc.).

### First-Time Setup: Database Not Initialized

If the database does not exist or tables are missing (common on fresh setup):

1. Open XAMMP, start MySQL and Apache.
2. Open the application in your browser (e.g., `http://localhost:8000`).
3. A modal will automatically appear with the message:  
   _"The database is missing. Please initialize it below. This will create the database and seed sample data (DEV only)."_
4. Click the **Setup** (or **Initialize Database**) button in the modal.
5. Wait for the loading spinner ("Setting up...").
6. On success: A green toast notification will show "Database initialized successfully!", and the page will automatically reload after 1.5 seconds.
7. After reload: The app is fully ready. Log in with the default Admin credentials (listed below) or other seeded users.

> **Alternative 1 (CLI method)**: If preferred, run `php database/create_db.php` from the terminal in the project root - this bypasses the modal and directly initializes the DB.

#### Optional - 2.a (if the above methods doesn't work)

- Run db_sql/create_db_full_with_drop.php in a browser or via PHP CLI to create the database, tables, and seed sample data.
- (this will drop/delete any exisiting db with the same name and create a new one with all the tables and seed sample data)

#### Optional - 2.b Import database

- Open **phpMyAdmin**
- Create a new database (e.g., `nikolovski_project_management`)
- Import `db_sql/create_db.sql`
- Or Import via terminal (prerequisites: Database CLI Tools or SQL Server, SQLTools extension):

```bash
mysql -u root < db_sql/create_db.sql

```

3. Run the Application:

- Open XAMMP, start MySQL and Apache.
- Start PHP server: php -S localhost:8000.
- Access http://localhost:8000 in your browser.

4. Default Credentials:

```
- Admin User: admin@example.com / admin123
- Team Lead Senior 1: lead1@example.com / lead123
- Team Lead Senior 2: lead2@example.com / lead456
- Regular Senior 1: senior1@example.com / senior123
- Regular Senior 2: senior2@example.com / senior456
- Mid Developer 1: mid1@example.com / mid123
- Junior Developer 1: junior1@example.com / junior123
```

- Register new users for testing (approve via Admin panel).
- Create new users via admin panel.

## Usage

- Admin: Access Admin Panel to manage users/projects.
- Team Lead: View assigned projects; add/remove members; create/assign tasks.
- Senior/Mid/Junior: Interact with tasks/comments per role permissions.
- Test RBAC by logging in as different roles.

### ER Diagram

- The following diagram illustrates the entity relationships between users, projects, tasks, and comments.

![ER Diagram](docs/er-diagram.svg)

### Designer view (phpMyAdmin)

![Designer view](docs/designer_view_tables.png)

### Development Notes

- Branching: Developed on dev branch with feature branches merged in.
- Testing: Multi-role testing for permissions; AJAX flows for UX.
- Specification Reference: Full implementation of provided spec (e.g., roles from pages 2-4, permissions hierarchy from page 7).

### Project Structure

```
ProjectRootFolder/
├── backend
│   ├── api
│   │   └── system_health.php
│   ├── controllers
│   │   ├── admin_projects_controller.php
│   │   ├── admin_users_controller.php
│   │   ├── auth_controller.php
│   │   ├── dashboard_controller.php
│   │   ├── project_controller.php
│   │   ├── project_view_controller.php
│   │   ├── task_controller.php
│   │   ├── user_controller.php
│   ├── includes
│   │   ├── classes
│   │   │   ├── Config.php
│   │   │   ├── DatabaseHelper.php
│   │   │   ├── Exceptions.php
│   │   │   ├── ProjectHelper.php
│   │   │   ├── Router.php
│   │   │   └── Validator.php
│   │   ├── api.php
│   │   ├── helpers.php
│   │   └── session_guard.php
│   ├── models
│   │   ├── Comment.php
│   │   ├── Database.php
│   │   ├── Project.php
│   │   ├── Task.php
│   │   ├── User.php
│   └── views
│       ├── bootstrap
│       │   └── task_view_context.php
│       ├── partials
│       │   ├── confirm_modal.php
│       │   ├── footer.php
│       │   ├── header.php
│       │   ├── system_db_modal.php
│       │   └── task_modal_content.php
│       ├── admin_panel.php
│       ├── admin_projects.php
│       ├── admin_users.php
│       ├── change_password.php
│       ├── dashboard.php
│       ├── login.php
│       ├── project_view.php
│       ├── register.php
│       └── task_view.php
├── database
│   ├── classes
│   │   ├── DatabaseInitializer.php
│   │   ├── DatabaseSchema.php
│   │   └── DatabaseSeeder.php
│   └── create_db.php
├── db_sql
│   ├── create_db_full_with_drop.php
│   ├── create_db.sql
│   └── nikolovski_project_management.sql
├── docs
│   ├── designer_view_tables.png
│   ├── er-diagram.drawio
│   ├── er-diagram.png
│   ├── er-diagram.svg
│   └── nikolovski_project_management.svg
├── frontend
│   ├── css
│   │   ├── custom.css
│   │   └── kanban.css
│   ├── js
│   │   ├── admin_users.js
│   │   ├── api.js
│   │   ├── auth.js
│   │   ├── comment.js
│   │   ├── confirmDelete.js
│   │   ├── kanban.js
│   │   ├── main.js
│   │   ├── modules.js
│   │   ├── pageUtils.js
│   │   ├── systemHealth.js
│   │   ├── taskModal.js
│   │   ├── ui.js
│   │   ├── utils.js
│   │   └── validation.js
│   └── vendor
│       ├── css
│       │   ├── bootstrap.min.css
│       │   ├── bootstrap.min.css.map
│       │   └── toastr.min.css
│       ├── fontawesome
│       │   ├── css
│       │   │   ├── all.css
│       │   │   └── all.min.css
│       │   └── webfonts
│       │       └── fa-solid-900.woff2
│       └── js
│           ├── bootstrap.bundle.min.js
│           ├── bootstrap.bundle.min.js.map
│           ├── bootstrap.min.js.map
│           ├── jquery.min.js
│           ├── Sortable.min.js
│           ├── toastr.js.map
│           └── toastr.min.js
├── password_log
│   ├── admin_password_log.php
│   └── user_passwords.txt
├── tests
│   ├── global_test.php
│   └── permission_test.php
├── .gitignore
├── autoload.php
├── credentials.txt
├── index.php
├── README.md
├── Specification.pdf
└── test_db.php
```

## Live Test Deployment

A temporary test deployment of the application is available for demonstration purposes:

🔗 **URL:** https://pma.free.nf

> ⚠️ **Note:**  
> This deployment is intended **for testing and preview only**.  
> Data may be reset at any time, performance may vary, and the environment is not production-hardened.

You can use the default credentials listed to explore role-based access, project management flows, and task handling across different user roles.

### Contributing

- This is a student project. For feedback or improvements, contact the author.

## 👥 Authors

- **Nikolovski Dejan** - [@Dejan.Nikolovski-FS21](https://git.brainster.co/Dejan.Nikolovski-FS21/brainsterchallenges_nikolovskidejan_fs21)
