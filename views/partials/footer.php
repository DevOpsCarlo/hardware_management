<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.js"></script>

<!-- Initialize DataTables -->
<script>
  // let inventoryTable = new DataTable('#inventoryTable');
  let detailedTable = new DataTable('#detailedTable', {
    scrollY: '590px',
    scrollCollapse: true,
  });
  // let categoryTable = new DataTable('#categoryTable');
  let assignAssetTable = new DataTable('#assignAssetTable');
  // let branchTable = new DataTable("#branchTable")
  let employeeTable = new DataTable("#employeeTable");
  let addEmployeeTable = new DataTable("#addEmployeeTable");
  let departmentEmployeeTable = new DataTable("#departmentEmployeeTable");
  let departmentTableSection = new DataTable('#branchDepartmentTable');
  let branchAssetTable = new DataTable('#branchAssetTable');
  let recentAssignmentTable = new DataTable('#recentAssignmentTable');
  let assetTable = new DataTable("#assetTable", {
    scrollY: '560px',
    scrollCollapse: true,
  });
  let assetDetailsTable = new DataTable("#assetDetailsTable");

  $('#branchTable').DataTable({
    columnDefs: [{
      targets: '_all',
      className: 'dt-left'
    }]
  });

  $('#inventoryTable').DataTable({
    columnDefs: [{
      targets: '_all',
      className: 'dt-left'
    }]

  });
  $('#categoryTable').DataTable({
    columnDefs: [{
      targets: '_all',
      className: 'dt-left'
    }]
  });
</script>



<!-- Sidebar  -->
<script src="/assets/js/sidebar.js"></script>



</body>

</html>