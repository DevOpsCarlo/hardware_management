<?php
require("db.php");

$adminUsername = 'itsd';
$adminPassword = 'itsdadmin';

$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

try {
  $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
  $stmt->execute([$adminUsername, $hashedPassword]);
  echo "✅ Admin user created successfully!";
} catch (PDOException $e) {
  if ($e->getCode() == 23000) {
    echo "⚠️ Admin user already exists.";
  } else {
    echo "❌ Error creating admin: " . $e->getMessage();
  }
}
