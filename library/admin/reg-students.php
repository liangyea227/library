<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_GET['inid'])){
  $id=$_GET['inid']; $status=0;
  $sql="UPDATE tblstudents SET Status=:status WHERE id=:id";
  $q=$dbh->prepare($sql); $q->bindParam(':id',$id,PDO::PARAM_STR); $q->bindParam(':status',$status,PDO::PARAM_STR); $q->execute();
  header('location:reg-students.php');
}
if(isset($_GET['id'])){
  $id=$_GET['id']; $status=1;
  $sql="UPDATE tblstudents SET Status=:status WHERE id=:id";
  $q=$dbh->prepare($sql); $q->bindParam(':id',$id,PDO::PARAM_STR); $q->bindParam(':status',$status,PDO::PARAM_STR); $q->execute();
  header('location:reg-students.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Registered Students</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
  <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Registered Students</h1>
      <p class="page-subtitle">Manage student accounts and their status</p>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Student Listing</span>
    </div>
    <div class="card-body">
      <div class="table-wrapper">
        <table id="dataTables-example" class="display" style="width:100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Student ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Mobile</th>
              <th>Reg Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
            $sql="SELECT * FROM tblstudents";
            $query=$dbh->prepare($sql); $query->execute();
            $cnt=1;
            foreach($query->fetchAll(PDO::FETCH_OBJ) as $result){ ?>
            <tr>
              <td><?php echo htmlentities($cnt); ?></td>
              <td><?php echo htmlentities($result->StudentId); ?></td>
              <td><?php echo htmlentities($result->FullName); ?></td>
              <td><?php echo htmlentities($result->EmailId); ?></td>
              <td><?php echo htmlentities($result->MobileNumber); ?></td>
              <td><?php echo htmlentities($result->RegDate); ?></td>
              <td>
                <?php if($result->Status==1): ?>
                  <span class="badge badge-success">Active</span>
                <?php else: ?>
                  <span class="badge badge-danger">Blocked</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="action-group">
                  <?php if($result->Status==1): ?>
                    <a href="reg-students.php?inid=<?php echo htmlentities($result->id); ?>"
                       onclick="return confirm('Block this student?')" class="btn btn-danger btn-sm">Block</a>
                  <?php else: ?>
                    <a href="reg-students.php?id=<?php echo htmlentities($result->id); ?>"
                       onclick="return confirm('Activate this student?')" class="btn btn-success btn-sm">Activate</a>
                  <?php endif; ?>
                  <a href="student-history.php?stdid=<?php echo htmlentities($result->StudentId); ?>" class="btn btn-primary btn-sm">Details</a>
                </div>
              </td>
            </tr>
            <?php $cnt++; } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/dataTables/jquery.dataTables.js"></script>
<script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
<script>
$(document).ready(function(){ $('#dataTables-example').DataTable(); });
</script>
</body>
</html>
<?php } ?>
