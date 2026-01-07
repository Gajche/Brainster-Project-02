<?php

require_once __DIR__ . '/../../backend/includes/classes/Config.php';
require_once __DIR__ . '/DatabaseSchema.php';
require_once __DIR__ . '/DatabaseSeeder.php';

class DatabaseInitializer
{

  // Initialize the database schema and seed data
  public static function run(): void
  {
    $pdo = new PDO(
      "mysql:host=" . Config::DB_HOST,
      Config::DB_USER,
      Config::DB_PASS,
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    DatabaseSchema::create($pdo);
    DatabaseSeeder::seed($pdo);
  }
}
