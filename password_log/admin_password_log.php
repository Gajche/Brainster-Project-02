<?php

require_once __DIR__ . '/../autoload.php';

// Only admins can view
if (!isAdmin()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

$logFile = Config::ROOT_DIR . '/password_log/user_passwords.txt';

echo '<h2>User Password Log</h2>';
echo '<div class="card"><div class="card-body">';

if (file_exists($logFile)) {
  $logContent = file_get_contents($logFile);
  echo '<pre style="padding: 15px;">';
  echo htmlspecialchars($logContent);
  echo '</pre>';

  // Show stats
  $lines = explode("\n", trim($logContent));
  echo '<p><strong>Total entries:</strong> ' . count(array_filter($lines)) . '</p>';
} else {
  echo '<p>No log file found yet.</p>';
}

echo '</div></div>';
