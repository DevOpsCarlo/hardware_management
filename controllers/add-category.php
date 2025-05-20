<?php
$pageTitle = 'Add Category';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  if (isset($_POST['inputCategoryName'])) {
    $categoryName = htmlspecialchars(trim($_POST['inputCategoryName']));
    if ($categoryName) {

      // Check if the categories is already exist in the database 
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(name) = LOWER(?) ");
      $stmt->execute([$categoryName]);
      $count = $stmt->fetchColumn();


      if ($count > 0) {
        $errorMessage = "Category already exist!";
      } else {
        // Insert new category
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?) ");
        $stmt->execute([$categoryName]);

        $_SESSION['category_added'] = $categoryName;
        $success = "Category added successfully";
        header("Location: /add-category");
        exit;
      }
    }
  } else {
    $categoryName = "";
  }
}

require("views/add-category.views.php");
