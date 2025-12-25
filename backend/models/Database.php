<?php

require_once __DIR__ . '/../../autoload.php';

class Database
{
  private static $instance = null;
  private $pdo;

  private function __construct()
  {
    try {
      $dsn = 'mysql:host=' . Config::DB_HOST .
        ';dbname=' . Config::DB_NAME .
        ';charset=utf8mb4';

      $this->pdo = new PDO(
        $dsn,
        Config::DB_USER,
        Config::DB_PASS
      );

      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
      // Log the real error for debugging (optional but recommended)
      error_log('[DATABASE CONNECTION ERROR] ' . $e->getMessage());

      // Throw your custom, human-friendly exception
      throw new DatabaseException();
    }
  }

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance->pdo;
  }

  // Prevent cloning
  private function __clone() {}

  // Prevent unserialization
  public function __wakeup()
  {
    throw new Exception('Cannot unserialize a singleton.');
  }
}
