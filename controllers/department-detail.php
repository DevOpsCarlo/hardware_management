<?php

// Get query parameters (branch and department names)
$branchName = $_GET['branch_name'] ?? '';
$departmentName = $_GET['department_name'] ?? '';
$pageTitle = "{$departmentName} Department";

// Fetch branch, department, and employees details
$branch = getBranchByName($pdo, $branchName);
$department = getDepartmentByName($pdo, $branch['id'], $departmentName);

$employees = getEmployeesByDepartment($pdo, $department['id']);
$deparmentStats = fetchDepartmentsWithAssetStats($pdo, $branch['id']);
// Fetch all assets for this department
$deparmentAssets = getDepartmentAssets($pdo, $department['id']);

// dd($deparmentAssets);
require("views/department-detail.views.php");
