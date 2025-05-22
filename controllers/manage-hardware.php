<?php
$pageTitle = "Manage Hardware";

$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
require("views/manage-hardware.views.php");
