<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_POST['return'])){
  $rid=intval($_GET['rid']);
  $fine=$_POST['fine'];
  $rstatus=1;
  $bookid=$_POST['bookid'];
  $sql="UPDATE tblissuedbookdetails SET fine=:fine,RetrunStatus=:rstatus,ReturnDate=NOW() WHERE id=:rid";
  $query=$dbh->prepare($sql);
  $query->bindParam(':rid',$rid,PDO::PARAM_STR);
  $query->bindParam(':fine',$fine,PDO::PARAM_STR);
  $query->bindParam(':rstatus',$rstatus,PDO::PARAM_STR);
  $query->execute();
  $sql2="UPDATE tblbooks SET isIssued=0 WHERE id=:bookid";
  $query2=$dbh->prepare($sql2); $query2->bindParam(':bookid',$bookid,PDO::PARAM_STR); $query2->execute();
  $_SESSION['msg']="Book returned successfully";
  header('location:manage-issued-books.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Issue Book Details</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Issued Book Details</h1>
      <p class="page-subtitle">View and process book return</p>
    </div>
    <a href="manage-issued-books.php" class="btn btn-primary">← Back to Issued Books</a>
  </div>

  <?php
    $rid=intval($_GET['rid']);
    $sql="SELECT tblstudents.StudentId,tblstudents.FullName,tblstudents.EmailId,tblstudents.MobileNumber,
                 tblbooks.BookName,tblbooks.ISBNNumber,tblissuedbookdetails.IssuesDate,
                 tblissuedbookdetails.ReturnDate,tblissuedbookdetails.id as rid,tblissuedbookdetails.fine,
                 tblissuedbookdetails.RetrunStatus,tblbooks.id as bid,tblbooks.bookImage
          FROM tblissuedbookdetails
          JOIN tblstudents ON tblstudents.StudentId=tblissuedbookdetails.StudentId
          JOIN tblbooks    ON tblbooks.id=tblissuedbookdetails.BookId
          WHERE tblissuedbookdetails.id=:rid";
    $query=$dbh->prepare($sql); $query->bindParam(':rid',$rid,PDO::PARAM_STR); $query->execute();
    foreach($query->fetchAll(PDO::FETCH_OBJ) as $result):
  ?>
  <form method="post">
    <input type="hidden" name="bookid" value="<?php echo htmlentities($result->bid); ?>"/>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

      <!-- Student details -->
      <div class="card">
        <div class="card-header"><span class="card-title">Student Details</span></div>
        <div class="card-body">
          <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <tr><td style="padding:8px 0;color:var(--text-muted);width:140px;">Student ID</td><td style="font-weight:600;"><?php echo htmlentities($result->StudentId); ?></td></tr>
            <tr><td style="padding:8px 0;color:var(--text-muted);">Name</td><td style="font-weight:600;"><?php echo htmlentities($result->FullName); ?></td></tr>
            <tr><td style="padding:8px 0;color:var(--text-muted);">Email</td><td><?php echo htmlentities($result->EmailId); ?></td></tr>
            <tr><td style="padding:8px 0;color:var(--text-muted);">Mobile</td><td><?php echo htmlentities($result->MobileNumber); ?></td></tr>
          </table>
        </div>
      </div>

      <!-- Book details -->
      <div class="card">
        <div class="card-header"><span class="card-title">Book Details</span></div>
        <div class="card-body" style="display:flex;gap:16px;align-items:flex-start;">
          <img src="bookimg/<?php echo htmlentities($result->bookImage); ?>" class="book-thumb" style="width:70px;height:95px;" alt="cover"/>
          <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <tr><td style="padding:6px 0;color:var(--text-muted);width:100px;">Book</td><td style="font-weight:600;"><?php echo htmlentities($result->BookName); ?></td></tr>
            <tr><td style="padding:6px 0;color:var(--text-muted);">ISBN</td><td><?php echo htmlentities($result->ISBNNumber); ?></td></tr>
            <tr><td style="padding:6px 0;color:var(--text-muted);">Issued</td><td><?php echo htmlentities($result->IssuesDate); ?></td></tr>
            <tr><td style="padding:6px 0;color:var(--text-muted);">Returned</td><td>
              <?php echo empty($result->ReturnDate) ? '<span class="badge badge-warning">Not Returned</span>' : htmlentities($result->ReturnDate); ?>
            </td></tr>
          </table>
        </div>
      </div>

    </div>

    <?php if($result->RetrunStatus==0): ?>
    <div class="card" style="max-width:400px;">
      <div class="card-header"><span class="card-title">Process Return</span></div>
      <div class="card-body">
        <div class="form-group">
          <label>Fine Amount (if any)</label>
          <input type="text" name="fine" value="0" autocomplete="off"/>
        </div>
        <div class="form-actions">
          <button type="submit" name="return" class="btn btn-gold">Mark as Returned</button>
        </div>
      </div>
    </div>
    <?php else: ?>
      <div class="alert alert-success">✅ This book has already been returned.</div>
    <?php endif; ?>
  </form>
  <?php endforeach; ?>
</div>

<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>
</body>
</html>
<?php } ?>
