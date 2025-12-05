<?php

require("auth.php");

$pageTitle = 'Dashboard';

// Fetch dashboard statistics
$stats = getDashboardAssetStats($pdo);
$statsByCategory = getAssetStatsByCategory($pdo);
$recentAssignments = getRecentAssignments($pdo, 10);

require("views/dashboard.views.php");
