<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_POST['issue'])){
  $studentid=strtoupper($_POST['studentid']);
  $bookid=$_POST['bookid'];
  $aremark=$_POST['aremark'];
  $aqty=$_POST['aqty'];
  if($aqty>0){
    $sql="INSERT INTO tblissuedbookdetails(StudentID,BookId,remark) VALUES(:studentid,:bookid,:aremark)";
    $query=$dbh->prepare($sql);
    $query->bindParam(':studentid',$studentid,PDO::PARAM_STR);
    $query->bindParam(':bookid',$bookid,PDO::PARAM_STR);
    $query->bindParam(':aremark',$aremark,PDO::PARAM_STR);
    $query->execute();
    if($dbh->lastInsertId()){
      $_SESSION['msg']="Book issued successfully";
      header('location:manage-issued-books.php');
    } else {
      $_SESSION['error']="Something went wrong. Please try again";
      header('location:manage-issued-books.php');
    }
  } else {
    $_SESSION['error']="Book not available";
    header('location:manage-issued-books.php');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Issue New Book</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
  <script>
  function getstudent(){
    jQuery.ajax({ url:"get_student.php", data:'studentid='+jQuery("#studentid").val(), type:"POST",
      success:function(data){ jQuery("#get_student_name").html(data); }, error:function(){} });
  }
  function getbook(){
    jQuery.ajax({ url:"get_book.php", data:'bookid='+jQuery("#bookid").val(), type:"POST",
      success:function(data){ jQuery("#get_book_name").html(data); }, error:function(){} });
  }
  </script>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Issue a New Book</h1>
      <p class="page-subtitle">Assign a book to a registered student</p>
    </div>
    <a href="manage-issued-books.php" class="btn btn-primary">← Back to Issued Books</a>
  </div>

  <div class="card" style="max-width:680px; margin:0 auto;">
    <div class="card-header">
      <span class="card-title">Issue Details</span>
    </div>
    <div class="card-body">
      <form method="post">
        <div class="form-group">
          <label>Student ID <span class="req">*</span></label>
          <input type="text" name="studentid" id="studentid" onblur="getstudent()" autocomplete="off" required/>
          <div id="get_student_name" style="margin-top:6px;font-size:14px;color:var(--sage);font-weight:600;"></div>
        </div>

        <div class="form-group mt-12">
          <label>ISBN Number or Book Title <span class="req">*</span></label>
          <input type="text" name="booikid" id="bookid" onblur="getbook()" required/>
          <div id="get_book_name" style="margin-top:6px;font-size:14px;color:var(--sage);font-weight:600;"></div>
        </div>

        <div class="form-group mt-12">
          <label>Remark <span class="req">*</span></label>
          <textarea name="aremark" id="aremark" required></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" name="issue" class="btn btn-gold">Issue Book</button>
          <a href="manage-issued-books.php" class="btn btn-primary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>
</body>
</html>
<?php } ?>
