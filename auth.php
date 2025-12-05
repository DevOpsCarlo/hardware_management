<?php
// middleware/auth.php
// Include this at the top of ANY protected page

// Check if user is logged in
if (!isset($_SESSION["user"])) {
    header("Location: /");
    exit();
}

// Get current user role
function getUserRole()
{
    return $_SESSION["user"]["role"] ?? null;
}

// Check user role (returns true/false)
function hasRole($requiredRole)
{
    return getUserRole() === $requiredRole;
}

// Require specific role or redirect
function requireRole($requiredRole)
{
    if (!hasRole($requiredRole)) {
        header("Location: /dashboard");
        exit();
    }
}
