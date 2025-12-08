<?php

require_once __DIR__ . '/../includes/config.php';  // Relative for now; update to use ROOT_DIR in other files

class Database
{
  private static $instance = null;
  private $pdo;

  private function __construct()
  {
    $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME . ';charset=utf8mb4';
    $this->pdo = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  }

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance->pdo;
  }

  // Prevent cloning/instantiation from outside
  private function __clone() {}
  public function __wakeup()
  {
    throw new \Exception('Cannot unserialize a singleton.');
  }
}
