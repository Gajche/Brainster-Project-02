<?php

// Database credentials - edit these for your local setup (edit includes/config.php as well)
$host = 'localhost';
$dbname = 'nikolovski_project_management';
$user = 'root';
$pass = '';

try {
  // Connect to MySQL server (no DB specified)
  $pdo = new PDO("mysql:host=$host", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Drop DB if exists, then create
  $pdo->exec("DROP DATABASE IF EXISTS $dbname");
  $pdo->exec("CREATE DATABASE $dbname");
  echo "Database created successfully.<br>";

  // Now connect to the new DB
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Create tables
  $pdo->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            level ENUM('Admin', 'Senior', 'Mid', 'Junior') NOT NULL,
            is_team_lead TINYINT(1) DEFAULT 0,
            is_approved TINYINT(1) DEFAULT 0
        )
    ");

  $pdo->exec("
        CREATE TABLE projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            requirements TEXT,
            estimated_time VARCHAR(50),
            deadline DATE,
            team_lead_id INT,
            status ENUM('Active', 'Done') DEFAULT 'Active',
            FOREIGN KEY (team_lead_id) REFERENCES users(id)
        )
    ");

  $pdo->exec("
        CREATE TABLE project_users (
            project_id INT,
            user_id INT,
            PRIMARY KEY (project_id, user_id),
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

  $pdo->exec("
        CREATE TABLE tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            created_by INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('To Do', 'In Progress', 'QA', 'Done') DEFAULT 'To Do',
            assigned_to INT DEFAULT NULL,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_to) REFERENCES users(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        )
    ");

  $pdo->exec("
        CREATE TABLE comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            task_id INT,
            user_id INT,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            edited TINYINT(1) DEFAULT 0,
            FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

  echo "Tables created successfully.<br>";

  // Seed sample data
  function hashPassword($plain)
  {
    return password_hash($plain, PASSWORD_DEFAULT);
  }

  // USERS
  $stmtU = $pdo->prepare("INSERT INTO users (name, email, password, level, is_team_lead, is_approved) VALUES (?, ?, ?, ?, ?, ?)");

  $stmtU->execute(['Admin User', 'admin@example.com',   hashPassword('admin123'),  'Admin',  0, 1]);
  $stmtU->execute(['Team Lead Senior 1', 'lead1@example.com', hashPassword('lead123'), 'Senior', 1, 1]);
  $stmtU->execute(['Regular Senior 1', 'senior1@example.com', hashPassword('senior123'), 'Senior', 0, 1]);
  $stmtU->execute(['Mid Developer 1', 'mid1@example.com', hashPassword('mid123'), 'Mid', 0, 1]);
  $stmtU->execute(['Junior Developer 1', 'junior1@example.com', hashPassword('junior123'), 'Junior', 0, 1]);
  $stmtU->execute(['Team Lead Senior 2', 'lead2@example.com', hashPassword('lead456'), 'Senior', 1, 1]);
  $stmtU->execute(['Regular Senior 2', 'senior2@example.com', hashPassword('senior456'), 'Senior', 0, 1]);

  // PROJECTS
  $stmtP = $pdo->prepare("INSERT INTO projects (title, description, requirements, estimated_time, deadline, team_lead_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");

  // Project 1 (Active)
  $stmtP->execute([
    'Project Management System',
    'Internal tool to handle tasks, teams, and comments.',
    'PHP OOP, MySQL, JS, jQuery',
    '2 months',
    '2024-12-31',
    2,
    'Active'
  ]);

  // Project 2 (Active)
  $stmtP->execute([
    'E-Commerce Shop',
    'Full online shop with cart, orders, and admin panel.',
    'PHP MVC, JS, MySQL, Bootstrap',
    '3 months',
    '2026-02-15',
    2,
    'Active'
  ]);

  // Project 3 (Active)
  $stmtP->execute([
    'Learning Platform',
    'Video courses, quizzes, progress tracking.',
    'PHP, REST API, Vanilla JS',
    '4 months',
    '2026-03-10',
    2,
    'Active'
  ]);

  // New Project 4 (Done, assigned to new Team Lead ID 6)
  $stmtP->execute([
    'Mobile App Development',
    'Build a cross-platform mobile app for task management.',
    'React Native, Firebase, REST API',
    '5 months',
    '2024-06-30',  // Past deadline for Done status
    6,
    'Done'
  ]);

  // New Project 5 (In Progress/Active, assigned to original Team Lead ID 2)
  $stmtP->execute([
    'Website Redesign',
    'Redesign company website with modern UI/UX.',
    'HTML/CSS, JavaScript, Bootstrap',
    '6 months',
    '2026-06-15',
    2,
    'Active'
  ]);

  // ADD USERS TO PROJECTS
  $stmtTeam = $pdo->prepare("INSERT INTO project_users (project_id, user_id) VALUES (?, ?)");

  // Add to Projects 1-3 (as before, users 2-5)
  foreach ([1, 2, 3] as $projectId) {
    $stmtTeam->execute([$projectId, 2]);
    $stmtTeam->execute([$projectId, 3]);
    $stmtTeam->execute([$projectId, 4]);
    $stmtTeam->execute([$projectId, 5]);
  }

  // Add to Project 4 (new Lead 6 + users 3,4,5,7 for variety)
  $stmtTeam->execute([4, 6]);  // Team Lead 2
  $stmtTeam->execute([4, 3]);  // Regular Senior 1
  $stmtTeam->execute([4, 4]);  // Mid 1
  $stmtTeam->execute([4, 5]);  // Junior 1
  $stmtTeam->execute([4, 7]);  // New Regular Senior 2

  // Add to Project 5 (Lead 2 + users 3,4,5,6 for variety)
  $stmtTeam->execute([5, 2]);  // Team Lead 1
  $stmtTeam->execute([5, 3]);  // Regular Senior 1
  $stmtTeam->execute([5, 4]);  // Mid 1
  $stmtTeam->execute([5, 5]);  // Junior 1
  $stmtTeam->execute([5, 6]);  // New Team Lead 2 (as member)

  // TASKS - Use Senior IDs for created_by (2,3,6,7)
  $stmtT = $pdo->prepare("INSERT INTO tasks (project_id, title, description, created_by, status, assigned_to) VALUES (?, ?, ?, ?, ?, ?)");

  // Project 1 Tasks
  $stmtT->execute([1, 'Fix login bug', 'Users sometimes stay stuck in loop.', 2, 'In Progress', 3]);  // Created by Team Lead 1 (Senior)
  $stmtT->execute([1, 'Improve dashboard UI', 'Add charts and stats.', 3, 'To Do', 4]);  // Created by Regular Senior 1
  $stmtT->execute([1, 'Create task modal', 'Modal must load via AJAX.', 2, 'QA', 5]);  // Created by Team Lead 1

  // Project 2 Tasks
  $stmtT->execute([2, 'Implement cart system', 'Add-to-cart and cart API.', 3, 'In Progress', 3]);
  $stmtT->execute([2, 'Product filtering', 'Filter by category and price.', 2, 'To Do', 4]);
  $stmtT->execute([2, 'Orders page', 'Admin order management.', 3, 'To Do', null]);

  // Project 3 Tasks
  $stmtT->execute([3, 'Course list page', 'Display courses with search.', 2, 'Done', 3]);
  $stmtT->execute([3, 'Video player', 'Add speed controls and chapters.', 3, 'In Progress', 4]);
  $stmtT->execute([3, 'Quiz system', 'Multiple-choice quiz engine.', 2, 'To Do', 5]);

  // Project 4 Tasks (Done project - all tasks Done)
  $stmtT->execute([4, 'Implement login screen', 'Add secure authentication.', 6, 'Done', 3]);  // Created by Team Lead 2 (Senior)
  $stmtT->execute([4, 'Add push notifications', 'Real-time updates.', 7, 'Done', 4]);  // Created by Regular Senior 2
  $stmtT->execute([4, 'Optimize performance', 'Reduce load times.', 6, 'Done', 5]);

  // Project 5 Tasks (In Progress - mixed statuses)
  $stmtT->execute([5, 'Update homepage layout', 'Modern responsive design.', 2, 'In Progress', 3]);
  $stmtT->execute([5, 'Add blog section', 'CMS integration.', 3, 'QA', 4]);
  $stmtT->execute([5, 'SEO optimization', 'Meta tags and sitemap.', 2, 'To Do', 5]);

  // COMMENTS
  $stmtC = $pdo->prepare("INSERT INTO comments (task_id, user_id, content) VALUES (?, ?, ?)");

  // Existing comments for Projects 1-3
  $stmtC->execute([1, 2, 'Please provide more details about the login loop.']);
  $stmtC->execute([1, 3, 'Found that issue only happens on Safari.']);
  $stmtC->execute([2, 4, 'Started working on the chart layout.']);
  $stmtC->execute([3, 5, 'Modal loads but animations are missing.']);
  $stmtC->execute([4, 3, 'I implemented the cart logic. Review needed.']);
  $stmtC->execute([6, 2, 'Orders page must include pagination.']);
  $stmtC->execute([7, 4, 'Course list looks great!']);
  $stmtC->execute([9, 5, 'Quiz system design is ready.']);

  // New comments for Project 4 tasks
  $stmtC->execute([10, 6, 'Login screen implemented, testing complete.']);
  $stmtC->execute([11, 7, 'Push notifications working on iOS and Android.']);
  $stmtC->execute([12, 6, 'Performance optimized, load times reduced by 40%.']);

  // New comments for Project 5 tasks
  $stmtC->execute([13, 2, 'Homepage layout updated, review pending.']);
  $stmtC->execute([14, 3, 'Blog section integrated with WordPress CMS.']);
  $stmtC->execute([15, 2, 'SEO tags added, sitemap generated.']);

  echo "Sample data seeded successfully.<br>";
  echo "Setup complete!<br>Default logins:<br>";
  echo "admin@example.com / admin123<br>";
  echo "New users: lead2@example.com / lead456 (Team Lead)<br>";
  echo "senior2@example.com / senior456 (Senior)";
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}
