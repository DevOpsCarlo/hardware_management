<?php
$pageTitle = 'Hardware Management';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = $_POST['inputUsername'];
  $password = $_POST['inputPassword'];

  // fetch from users 
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username =? ");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user["password"])) {
    $_SESSION["user"] = [
      "id" => $user["id"],
      "username" => $user["username"],
      "role" => $user["role"]
    ];
    header("Location: /dashboard");
    exit();
  } else {
    $error = "Invalid username or password";
    require("views/index.views.php");
    exit();
  }
}


require("views/index.views.php");
