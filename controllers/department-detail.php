<?php

$branchName = $_GET['branch_name'] ?? "";         // e.g. "Manila"
$departmentName = $_GET['department_name'] ?? "";



// dd($_SERVER);
$branch = getBranchByName($pdo, $branchName);
$department = getDepartmentByName($pdo, $branch['id'], $departmentName);
$employees = getEmployeesByDepartment($pdo, $department['id']);

$pageTitle = "{$departmentName} Department";


require("views/department.details.views.php");
