<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Manage Issued Books</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
  <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Issued Books</h1>
      <p class="page-subtitle">Track all issued and returned books</p>
    </div>
    <a href="issue-book.php" class="btn btn-gold">+ Issue New Book</a>
  </div>

  <?php
    foreach(['error','msg','delmsg'] as $key){
      if(!empty($_SESSION[$key])){
        $type = ($key=='error') ? 'danger' : 'success';
        echo '<div class="alert alert-'.$type.'">'.htmlentities($_SESSION[$key]).'</div>';
        $_SESSION[$key]="";
      }
    }
  ?>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Issued Books Record</span>
    </div>
    <div class="card-body">
      <div class="table-wrapper">
        <table id="dataTables-example" class="display" style="width:100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Student Name</th>
              <th>Book Name</th>
              <th>ISBN</th>
              <th>Issued Date</th>
              <th>Return Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
            $sql="SELECT tblstudents.FullName,tblbooks.BookName,tblbooks.ISBNNumber,
                         tblissuedbookdetails.IssuesDate,tblissuedbookdetails.ReturnDate,tblissuedbookdetails.id as rid
                  FROM tblissuedbookdetails
                  JOIN tblstudents ON tblstudents.StudentId=tblissuedbookdetails.StudentId
                  JOIN tblbooks    ON tblbooks.id=tblissuedbookdetails.BookId
                  ORDER BY tblissuedbookdetails.id DESC";
            $query=$dbh->prepare($sql); $query->execute();
            $cnt=1;
            foreach($query->fetchAll(PDO::FETCH_OBJ) as $result){ ?>
            <tr>
              <td><?php echo htmlentities($cnt); ?></td>
              <td><?php echo htmlentities($result->FullName); ?></td>
              <td><?php echo htmlentities($result->BookName); ?></td>
              <td><?php echo htmlentities($result->ISBNNumber); ?></td>
              <td><?php echo htmlentities($result->IssuesDate); ?></td>
              <td>
                <?php if(empty($result->ReturnDate)): ?>
                  <span class="badge badge-warning">Not Returned</span>
                <?php else: ?>
                  <span class="badge badge-success"><?php echo htmlentities($result->ReturnDate); ?></span>
                <?php endif; ?>
              </td>
              <td>
                <a href="update-issue-bookdeails.php?rid=<?php echo htmlentities($result->rid); ?>" class="btn btn-success btn-sm">✏️ Update</a>
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
