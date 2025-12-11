<?php

// Database credentials - edit these for your local setup
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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('To Do', 'In Progress', 'QA', 'Done') DEFAULT 'To Do',
            assigned_to INT DEFAULT NULL,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_to) REFERENCES users(id)
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
  $stmtU->execute(['Team Lead Senior', 'lead@example.com', hashPassword('lead123'), 'Senior', 1, 1]);
  $stmtU->execute(['Regular Senior', 'senior@example.com', hashPassword('senior123'), 'Senior', 0, 1]);
  $stmtU->execute(['Mid Developer', 'mid@example.com', hashPassword('mid123'), 'Mid', 0, 1]);
  $stmtU->execute(['Junior Developer', 'junior@example.com', hashPassword('junior123'), 'Junior', 0, 1]);

  // PROJECTS
  $stmtP = $pdo->prepare("INSERT INTO projects (title, description, requirements, estimated_time, deadline, team_lead_id) VALUES (?, ?, ?, ?, ?, ?)");

  // Project 1
  $stmtP->execute([
    'Project Management System',
    'Internal tool to handle tasks, teams, and comments.',
    'PHP OOP, MySQL, JS, jQuery',
    '2 months',
    '2024-12-31',
    2
  ]);

  // Project 2
  $stmtP->execute([
    'E-Commerce Shop',
    'Full online shop with cart, orders, and admin panel.',
    'PHP MVC, JS, MySQL, Bootstrap',
    '3 months',
    '2025-02-15',
    2
  ]);

  // Project 3
  $stmtP->execute([
    'Learning Platform',
    'Video courses, quizzes, progress tracking.',
    'PHP, REST API, Vanilla JS',
    '4 months',
    '2025-03-10',
    2
  ]);

  // ADD USERS TO ALL PROJECTS
  $stmtTeam = $pdo->prepare("INSERT INTO project_users (project_id, user_id) VALUES (?, ?)");
  foreach ([1, 2, 3] as $projectId) {
    $stmtTeam->execute([$projectId, 2]);
    $stmtTeam->execute([$projectId, 3]);
    $stmtTeam->execute([$projectId, 4]);
    $stmtTeam->execute([$projectId, 5]);
  }

  // TASKS
  $stmtT = $pdo->prepare("INSERT INTO tasks (project_id, title, description, status, assigned_to) VALUES (?, ?, ?, ?, ?)");

  // Project 1 Tasks
  $stmtT->execute([1, 'Fix login bug', 'Users sometimes stay stuck in loop.', 'In Progress', 3]);
  $stmtT->execute([1, 'Improve dashboard UI', 'Add charts and stats.', 'To Do', 4]);
  $stmtT->execute([1, 'Create task modal', 'Modal must load via AJAX.', 'QA', 5]);

  // Project 2 Tasks
  $stmtT->execute([2, 'Implement cart system', 'Add-to-cart and cart API.', 'In Progress', 3]);
  $stmtT->execute([2, 'Product filtering', 'Filter by category and price.', 'To Do', 4]);
  $stmtT->execute([2, 'Orders page', 'Admin order management.', 'To Do', null]);

  // Project 3 Tasks
  $stmtT->execute([3, 'Course list page', 'Display courses with search.', 'Done', 3]);
  $stmtT->execute([3, 'Video player', 'Add speed controls and chapters.', 'In Progress', 4]);
  $stmtT->execute([3, 'Quiz system', 'Multiple-choice quiz engine.', 'To Do', 5]);

  // COMMENTS
  $stmtC = $pdo->prepare("INSERT INTO comments (task_id, user_id, content) VALUES (?, ?, ?)");

  // Task 1 comments
  $stmtC->execute([1, 2, 'Please provide more details about the login loop.']);
  $stmtC->execute([1, 3, 'Found that issue only happens on Safari.']);

  // Task 2 comments
  $stmtC->execute([2, 4, 'Started working on the chart layout.']);

  // Task 3 comments
  $stmtC->execute([3, 5, 'Modal loads but animations are missing.']);

  // Other comments
  $stmtC->execute([4, 3, 'I implemented the cart logic. Review needed.']);
  $stmtC->execute([6, 2, 'Orders page must include pagination.']);
  $stmtC->execute([7, 4, 'Course list looks great!']);
  $stmtC->execute([9, 5, 'Quiz system design is ready.']);

  echo "Sample data seeded successfully.<br>";
  echo "Setup complete!<br>Default logins:<br>";
  echo "admin@example.com / admin123";
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}
