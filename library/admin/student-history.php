<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

// FIX: Validate and sanitize $sid before use — use prepared statement
$sid = isset($_GET['stdid']) ? trim($_GET['stdid']) : '';
if($sid === ''){
  header('location:reg-students.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Student History</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
  <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<?php // FIX: sid displayed safely ?>
<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Book Issue History</h1>
      <p class="page-subtitle">Student ID: <strong><?php echo htmlentities($sid); ?></strong></p>
    </div>
    <a href="reg-students.php" class="btn btn-primary">← Back to Students</a>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title"><?php echo htmlentities($sid); ?> — Issued Book Records</span>
    </div>
    <div class="card-body">
      <div class="table-wrapper">
        <table id="dataTables-example" class="display" style="width:100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Student ID</th>
              <th>Name</th>
              <th>Book</th>
              <th>Issued Date</th>
              <th>Return Date</th>
              <th>Fine</th>
            </tr>
          </thead>
          <tbody>
          <?php
            // FIX: Use parameterized query — no more direct string interpolation
            $sql = "SELECT tblstudents.StudentId, tblstudents.FullName, tblbooks.BookName,
                           tblissuedbookdetails.IssuesDate, tblissuedbookdetails.ReturnDate,
                           tblissuedbookdetails.fine, tblissuedbookdetails.RetrunStatus
                    FROM tblissuedbookdetails
                    JOIN tblstudents ON tblstudents.StudentId = tblissuedbookdetails.StudentID
                    JOIN tblbooks    ON tblbooks.id = tblissuedbookdetails.BookId
                    WHERE tblstudents.StudentId = :sid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':sid', $sid, PDO::PARAM_STR);
            $query->execute();
            $cnt = 1;
            foreach($query->fetchAll(PDO::FETCH_OBJ) as $result): ?>
            <tr>
              <td><?php echo htmlentities($cnt); ?></td>
              <td><?php echo htmlentities($result->StudentId); ?></td>
              <td><?php echo htmlentities($result->FullName); ?></td>
              <td><?php echo htmlentities($result->BookName); ?></td>
              <td><?php echo htmlentities($result->IssuesDate); ?></td>
              <td>
                <?php if(empty($result->ReturnDate)): ?>
                  <span class="badge badge-warning">Not Returned</span>
                <?php else: ?>
                  <?php echo htmlentities($result->ReturnDate); ?>
                <?php endif; ?>
              </td>
              <td>
                <?php echo empty($result->ReturnDate) ? '—' : htmlentities($result->fine); ?>
              </td>
            </tr>
            <?php $cnt++; endforeach; ?>
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
