<?php

// Get query parameters (branch and department names)
$branchName = $_GET['branch_name'] ?? '';
$departmentName = $_GET['department_name'] ?? '';
$pageTitle = "{$departmentName} Department";

// Fetch branch, department, and employees details
$branch = getBranchByName($pdo, $branchName);
$department = getDepartmentByName($pdo, $branch['id'], $departmentName);
$employees = getEmployeesByDepartment($pdo, $department['id']);
$fetchEmployees = fetchEmployeeActive($pdo, 'Active');  // Fetch active employees

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['modalAddEmployee'])) {
  $employeeIds = $_POST['employee_ids'] ?? [];  // Get selected employee IDs
  $departmentId = $department['id'];  // The department ID to assign employees to

  // Check if at least one employee is selected
  if (!empty($employeeIds)) {
    try {
      // Loop through selected employee IDs and insert them into the department_employee relation table
      $pdo->beginTransaction();  // Start a transaction to ensure atomicity of the operation

      // Check if employee is already assigned to this department
      $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM department_employee WHERE department_id = ? AND employee_id = ?");
      $stmt = $pdo->prepare("INSERT INTO department_employee (department_id, employee_id, created_at, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");

      $addedCount = 0;
      foreach ($employeeIds as $employeeId) {
        // Check if employee is already in this department
        $checkStmt->execute([$departmentId, $employeeId]);
        $exists = $checkStmt->fetchColumn();

        if ($exists == 0) {
          // Execute the insert query for each employee
          $stmt->execute([$departmentId, $employeeId]);
          $addedCount++;
        }
      }

      // Commit the transaction
      $pdo->commit();

      // Set success message
      if ($addedCount > 0) {
        $_SESSION['success_message'] = $addedCount . ' employee(s) added successfully!';
      } else {
        $_SESSION['error_message'] = "Selected employees are already assigned to this department.";
      }
    } catch (PDOException $e) {
      // Rollback the transaction in case of error
      $pdo->rollBack();
      // Set error message
      $_SESSION['error_message'] = "Error adding employees: " . $e->getMessage();
    }
  } else {
    $_SESSION['error_message'] = "Please select at least one employee.";
  }

  // Redirect to prevent form resubmission
  header("Location: /branch/" . urlencode($branchName) . "/" . urlencode($departmentName));
  exit;
}

// Handle DELETE request for removing employees
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['delete_employee_id'])) {
  $employeeId = $_POST['delete_employee_id'];
  $departmentId = $department['id'];

  try {
    $stmt = $pdo->prepare("DELETE FROM department_employee WHERE department_id = ? AND employee_id = ?");
    $stmt->execute([$departmentId, $employeeId]);

    $_SESSION['success_message'] = "Employee removed from department successfully!";
  } catch (PDOException $e) {
    $_SESSION['error_message'] = "Error removing employee: " . $e->getMessage();
  }

  header("Location: /branch/" . urlencode($branchName) . "/" . urlencode($departmentName));
  exit;
}

// Fetch employees assigned to this department
$departmentId = $department['id']; // The department ID

// SQL Query to get employees associated with the department - FIXED: Added e.id
$query = "
  SELECT e.id, e.employee_name, e.employee_id, e.option_status
  FROM department_employee de
  JOIN employee e ON de.employee_id = e.id
  WHERE de.department_id = :department_id
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':department_id', $departmentId, PDO::PARAM_INT);
$stmt->execute();

// Fetching the results
$departmentEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter out employees that are already assigned to this department from the fetchEmployees
if (!empty($departmentEmployees)) {
  $assignedEmployeeIds = array_column($departmentEmployees, 'id');
  $fetchEmployees = array_filter($fetchEmployees, function ($employee) use ($assignedEmployeeIds) {
    return !in_array($employee['id'], $assignedEmployeeIds);
  });
}

require("views/department-detail.views.php");
