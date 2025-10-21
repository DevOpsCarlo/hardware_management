<?php
$pageTitle = 'Employee';
// if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['delete_employee_id'])) {
//   $employeeIdToDelete = $_POST['delete_employee_id'];

//   // Check if category ID is valid
//   if ($employeeIdToDelete > 0) {
//     // DELETE CATEGORY
//     $stmt = $pdo->prepare("DELETE FROM employee WHERE id = ?");
//     $stmt->execute([$employeeIdToDelete]);

//     if ($stmt->rowCount() > 0) {
//       // Successfully deleted
//       $_SESSION['employee_deleted'] = "employee deleted successfully!";
//     } else {
//       // Failed to delete
//       $_SESSION['employee_error'] = "Failed to delete branch.";
//     }
//   } else {
//     $_SESSION['employee_error'] = "Invalid employee ID.";
//   }

//   header("Location: /employee");
//   exit;
// }



// if ($_SERVER['REQUEST_METHOD'] === "POST") {
//   $employeeName = htmlspecialchars(trim($_POST['inputEmployeeName'] ?? ""));
//   $employeeId = htmlspecialchars(trim($_POST['inputEmployeeId'] ?? ""));
//   $employeeStatus = htmlspecialchars($_POST['optionStatus']); // Capture the selected status
//   $id = intval($_POST['id'] ?? 0);

//   if ($employeeName) {
//     $stmt = $pdo->prepare("SELECT COUNT(*) FROM employee WHERE LOWER(employee_name) = LOWER(?) AND id != ?");
//     $stmt->execute([$employeeName, $id]);
//     $count = $stmt->fetchColumn();

//     if ($count > 0) {
//       $_SESSION['error_message'] = "Employee name already exists.";
//       header("Location: /employee");
//       exit;
//     } else {
//       if ($id > 0) {
//         // Update existing employee, including status
//         $updateQuery = "UPDATE employee SET employee_name = ?, employee_id = ?, option_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
//         $stmt = $pdo->prepare($updateQuery);
//         $stmt->execute([$employeeName, $employeeId, $employeeStatus, $id]);

//         $_SESSION['success_message'] = ['text' => "$employeeName updated successfully!", 'action' => 'updated'];
//       } else {
//         // Insert new employee with status
//         $insertQuery = "INSERT INTO employee (employee_name, employee_id, option_status, created_at, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
//         $stmt = $pdo->prepare($insertQuery);
//         $stmt->execute([$employeeName, $employeeId, $employeeStatus]);

//         $_SESSION['success_message'] = ['text' => "Employee ($employeeName) added successfully!", 'action' => 'added'];
//       }
//       header("Location: /employee");
//       exit;
//     }
//   }
// }

if ($_SERVER['REQUEST_METHOD'] === "POST") {

  // Handle DELETE request
  if (isset($_POST['delete_employee_id'])) {
    $employeeIdToDelete = intval($_POST['delete_employee_id']);

    if ($employeeIdToDelete > 0) {
      $stmt = $pdo->prepare("DELETE FROM employee WHERE id = ?");
      $stmt->execute([$employeeIdToDelete]);

      if ($stmt->rowCount() > 0) {
        echo "success"; // Send response to JavaScript
      } else {
        http_response_code(400);
        echo "Failed to delete employee.";
      }
    } else {
      http_response_code(400);
      echo "Invalid employee ID.";
    }
    exit; // CRITICAL: Stop execution here
  }

  // Handle ADD/UPDATE request
  if (isset($_POST['inputEmployeeName'])) {
    $employeeName = htmlspecialchars(trim($_POST['inputEmployeeName'] ?? ""));
    $employeeId = htmlspecialchars(trim($_POST['inputEmployeeId'] ?? ""));
    $employeeStatus = htmlspecialchars($_POST['optionStatus']);
    $id = intval($_POST['id'] ?? 0);

    if ($employeeName) {
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM employee WHERE LOWER(employee_name) = LOWER(?) AND id != ?");
      $stmt->execute([$employeeName, $id]);
      $count = $stmt->fetchColumn();

      if ($count > 0) {
        $_SESSION['error_message'] = "Employee name already exists.";
        header("Location: /employee");
        exit;
      } else {
        if ($id > 0) {
          // Update existing employee
          $updateQuery = "UPDATE employee SET employee_name = ?, employee_id = ?, option_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
          $stmt = $pdo->prepare($updateQuery);
          $stmt->execute([$employeeName, $employeeId, $employeeStatus, $id]);

          $_SESSION['success_message'] = ['text' => "$employeeName updated successfully!", 'action' => 'updated'];
        } else {
          // Insert new employee
          $insertQuery = "INSERT INTO employee (employee_name, employee_id, option_status, created_at, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
          $stmt = $pdo->prepare($insertQuery);
          $stmt->execute([$employeeName, $employeeId, $employeeStatus]);

          $_SESSION['success_message'] = ['text' => "Employee ($employeeName) added successfully!", 'action' => 'added'];
        }
        header("Location: /employee");
        exit;
      }
    }
  }
}

$employees =  fetchEmployeeAssetSummary($pdo);


// dd($employees);


require("views/employee.views.php");
