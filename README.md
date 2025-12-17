# Project Management Application

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white)](https://jquery.com/)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)

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
- **Frontend**: HTML/CSS with Bootstrap for styling; JavaScript/jQuery for interactivity (AJAX, drag-drop via Sortable.js).
- **Security**: Prepared statements for SQL; password hashing; session-based auth.

## Installation and Setup

### Prerequisites

- PHP 7.4+ with PDO extension.
- MySQL database server.
- Web server (e.g., Apache, or PHP built-in).

### Steps

1. **Clone the Repository**:

   ```bash
   git clone https://git.brainster.co/your-username/brainster-project-02.git
   cd brainster-project-02

   ```

2. Database Configuration:

- Edit includes/config.php with your MySQL credentials (DB_HOST, DB_NAME, etc.).
- Run db/create_db.php in a browser or via PHP CLI to create the database, tables, and seed sample data.

3. Run the Application:

- Start PHP server: php -S localhost:8000.
- Access http://localhost:8000 in your browser.

4. Default Credentials:

- Admin: admin@example.com / admin123
- Team Lead: lead@example.com / lead123
- Register new users for testing (approve via Admin panel).

## Usage

- Admin: Access Admin Panel to manage users/projects.
- Team Lead: View assigned projects; add/remove members; create/assign tasks.
- Senior/Mid/Junior: Interact with tasks/comments per role permissions.
- Test RBAC by logging in as different roles.

### ER Diagram

- (Created with draw.io; illustrates entity relationships for users, projects, tasks, and comments.)

### Development Notes

- Branching: Developed on dev branch with feature branches merged in.
- Testing: Multi-role testing for permissions; AJAX flows for UX.
- Specification Reference: Full implementation of provided spec (e.g., roles from pages 2-4, permissions hierarchy from page 7).

### Project Structure

```
.
├── controllers
│   ├── auth_controller.php
│   ├── project_controller.php
│   ├── task_controller.php
│   ├── user_controller.php
├── css
│   ├── bootstrap.min.css
│   ├── custom.css
│   └── kanban.css
├── db
│   ├── create_db_more_samples.php
│   └── create_db.php
├── includes
│   ├── api.php
│   ├── config.php
│   ├── footer.php
│   ├── header.php
│   └── helpers.php
├── js
│   ├── api.js
│   ├── auth.js
│   ├── bootstrap.bundle.min.js
│   ├── comment.js
│   ├── jquery.min.js
│   ├── kanban.js
│   ├── main.js
│   ├── Sortable.min.js
│   ├── taskModal.js
│   ├── ui.js
│   ├── utils.js
│   └── validation.js
├── models
│   ├── Comment.php
│   ├── Database.php
│   ├── Project.php
│   ├── Task.php
│   └── User.php
├── views
│   ├── partials
│   │   └── task_modal_content.php
│   ├── admin_panel.php
│   ├── admin_projects.php
│   ├── admin_users.php
│   ├── change_password.php
│   ├── dashboard.php
│   ├── login.php
│   ├── project_view.php
│   ├── register.php
│   └── task_view.php
├── autoload.php
├── credentials.txt
├── index.php
├── jobs.txt
├── js_refactor_log.txt
├── README.md
├── refactoring_plan.txt
├── Specification.pdf
├── specification.txt
└── test_db.php
```

### Contributing

- This is a student project. For feedback or improvements, contact the author.
