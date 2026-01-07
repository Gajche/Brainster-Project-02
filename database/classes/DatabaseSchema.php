<?php

class DatabaseSchema
{

  // Create the database schema
  public static function create(PDO $pdo): void
  {
    $db = Config::DB_NAME;

    $pdo->exec("DROP DATABASE IF EXISTS `$db`");
    $pdo->exec("CREATE DATABASE `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");

    $pdo->exec("
      CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        level ENUM('Admin','Senior','Mid','Junior') NOT NULL,
        is_team_lead TINYINT(1) DEFAULT 0,
        is_approved TINYINT(1) DEFAULT 0,
        CHECK (is_team_lead = 0 OR level = 'Senior')
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
        status ENUM('Active','Done') DEFAULT 'Active',
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
        status ENUM('To Do','In Progress','QA','Done') DEFAULT 'To Do',
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
  }
}
