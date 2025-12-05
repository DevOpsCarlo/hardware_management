<?php
require("auth.php");

requireRole("admin");

$pageTitle = 'Manage Users';

$errorMessage = $_SESSION['user_error'] ?? '';
$successMessage = $_SESSION['user_success'] ?? '';
$formData = $_SESSION['user_form_data'] ?? [];
$editMode = $_SESSION['user_edit_mode'] ?? false;

unset($_SESSION['user_error'], $_SESSION['user_success'], $_SESSION['user_form_data'], $_SESSION['user_edit_mode']);

// Fetch all users
$stmt = $pdo->query("SELECT id, username, role FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['inputUsername'] ?? '');
    $password = $_POST['inputPassword'] ?? '';
    $confirmPassword = $_POST['inputConfirmPassword'] ?? '';
    $role = $_POST['inputRole'] ?? 'user';
    $userId = $_POST['userId'] ?? 0;

    // Validation
    $error = null;

    if (empty($username)) {
        $error = "Username is required";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters";
    }

    if (empty($role) || !in_array($role, ['admin', 'user'])) {
        $error = "Invalid role selected";
    }

    // Check if editing or adding new user
    if (empty($error) && empty($userId)) {
        // Adding new user
        if (empty($password) || empty($confirmPassword)) {
            $error = "Password fields are required";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match";
        } else {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "Username already exists";
            }
        }
    }

    if (empty($error)) {
        if (empty($userId)) {
            // Create new user
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashedPassword, $role])) {
                $_SESSION['user_success'] = $username;
                header("Location: /user");
                exit();
            } else {
                $error = "Failed to create user";
            }
        } else {
            // Update existing user
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $error = "Password must be at least 6 characters";
                } elseif ($password !== $confirmPassword) {
                    $error = "Passwords do not match";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
                    $stmt->execute([$username, $hashedPassword, $role, $userId]);
                }
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $role, $userId]);
            }

            if (empty($error)) {
                $_SESSION['user_success'] = $username;
                header("Location: /user");
                exit();
            }
        }
    }

    if (!empty($error)) {
        $_SESSION['user_error'] = $error;
        $_SESSION['user_form_data'] = $_POST;
        header("Location: /user");
        exit();
    }
}

// Handle delete
if ($_SERVER["REQUEST_METHOD"] === "DELETE" && isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // Prevent deleting yourself
    if ($userId === $_SESSION["user"]["id"]) {
        http_response_code(400);
        echo json_encode(['error' => 'Cannot delete your own account']);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$userId])) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete user']);
    }
    exit();
}

require("views/user.views.php");
