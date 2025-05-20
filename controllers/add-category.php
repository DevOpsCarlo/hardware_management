<?php
$pageTitle = 'Add Category';


// Handling the DELETE request for category deletion
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['delete_category_id'])) {
  $categoryIdToDelete = $_POST['delete_category_id'];

  // Check if category ID is valid
  if ($categoryIdToDelete > 0) {
    // DELETE CATEGORY
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$categoryIdToDelete]);

    if ($stmt->rowCount() > 0) {
      // Successfully deleted
      $_SESSION['category_deleted'] = "Category deleted successfully!";
    } else {
      // Failed to delete
      $_SESSION['category_error'] = "Failed to delete category.";
    }
  } else {
    $_SESSION['category_error'] = "Invalid category ID.";
  }

  header("Location: /add-category");
  exit;
}


if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $categoryName = isset($_POST['inputCategoryName']) ? htmlspecialchars(trim($_POST['inputCategoryName'])) : '';
  $categoryId = isset($_POST['categoryId']) ? intval($_POST['categoryId']) : 0;
  if ($categoryName) {

    // Check if the categories is already exist in the database 
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(name) = LOWER(?) AND id != ?");
    $stmt->execute([$categoryName, $categoryId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      $_SESSION['category_error'] = "Category already exists!";
      $_SESSION['category_form_data'] = ['name' => $categoryName, 'id' => $categoryId];
      $_SESSION['category_edit_mode'] = $categoryId > 0;
      header("Location: /add-category");
      exit;
    } else {
      if ($categoryId > 0) {
        //  UPDATE NEW CATEGORY
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$categoryName, $categoryId]);

        $_SESSION['category_updated'] = $categoryName;
        header("Location: /add-category");
        exit;
      } else {
        // Insert NEW CATEGORY
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$categoryName]);
        $_SESSION['category_added'] = $categoryName;
        header("Location: /add-category");
        exit;
      }
    }
  }
}


$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY created_at DESC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

require("views/add-category.views.php");
