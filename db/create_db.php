<?php

// Database credentials - edit these for your local setup
$host = 'localhost';
$dbname = 'my_project_management';
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
  // Users table
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

  // Projects table
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

  // Project_Users (teams)
  $pdo->exec("
        CREATE TABLE project_users (
            project_id INT,
            user_id INT,
            PRIMARY KEY (project_id, user_id),
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

  // Tasks table
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

  // Comments table
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
  // Helper: Hash password (use same as in app)
  function hashPassword($plain)
  {
    return password_hash($plain, PASSWORD_DEFAULT);
  }

  // Admin (auto-approved)
  $adminPass = hashPassword('admin123');
  $stmt = $pdo->prepare("INSERT INTO users (name, email, password, level, is_approved) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute(['Admin User', 'admin@example.com', $adminPass, 'Admin', 1]);

  // Senior Team Lead
  $leadPass = hashPassword('lead123');
  $stmt = $pdo->prepare("INSERT INTO users (name, email, password, level, is_team_lead, is_approved) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute(['Team Lead Senior', 'lead@example.com', $leadPass, 'Senior', 1, 1]);

  // Regular Senior
  $seniorPass = hashPassword('senior123');
  $stmt->execute(['Regular Senior', 'senior@example.com', $seniorPass, 'Senior', 0, 1]);

  // Mid
  $midPass = hashPassword('mid123');
  $stmt = $pdo->prepare("INSERT INTO users (name, email, password, level, is_approved) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute(['Mid User', 'mid@example.com', $midPass, 'Mid', 1]);

  // Junior
  $juniorPass = hashPassword('junior123');
  $stmt->execute(['Junior User', 'junior@example.com', $juniorPass, 'Junior', 1]);

  // Sample Project assigned to Team Lead (user id 2)
  $stmt = $pdo->prepare("INSERT INTO projects (title, description, requirements, estimated_time, deadline, team_lead_id) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute(['Sample Project', 'A test project', 'Reqs here', '2 weeks', '2024-12-31', 2]);

  // Add team members to project (id 1): Team Lead (2), Regular Senior (3), Mid (4), Junior (5)
  $stmt = $pdo->prepare("INSERT INTO project_users (project_id, user_id) VALUES (?, ?)");
  $stmt->execute([1, 2]);
  $stmt->execute([1, 3]);
  $stmt->execute([1, 4]);
  $stmt->execute([1, 5]);

  // Sample Tasks in project 1
  $stmt = $pdo->prepare("INSERT INTO tasks (project_id, title, description, assigned_to) VALUES (?, ?, ?, ?)");
  $stmt->execute([1, 'Task 1', 'Fix bug', 3]); // Assigned to Regular Senior
  $stmt = $pdo->prepare("INSERT INTO tasks (project_id, title, description) VALUES (?, ?, ?)");
  $stmt->execute([1, 'Task 2', 'New feature']); // Unassigned

  // Sample Comments on Task 1
  $stmt = $pdo->prepare("INSERT INTO comments (task_id, user_id, content) VALUES (?, ?, ?)");
  $stmt->execute([1, 2, 'Team Lead comment']);
  $stmt->execute([1, 3, 'Senior comment']);

  echo "Sample data seeded successfully.<br>";
  echo "Setup complete! Default logins: admin@example.com / admin123, etc.";
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}
