<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_POST['create'])){
  $author = $_POST['author'];
  $sql="INSERT INTO tblauthors(AuthorName) VALUES(:author)";
  $query=$dbh->prepare($sql);
  $query->bindParam(':author',$author,PDO::PARAM_STR);
  $query->execute();
  if($dbh->lastInsertId()){
    $_SESSION['msg']="Author listed successfully";
    header('location:manage-authors.php');
  } else {
    $_SESSION['error']="Something went wrong. Please try again";
    header('location:manage-authors.php');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Add Author</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Add Author</h1>
      <p class="page-subtitle">Register a new author in the system</p>
    </div>
    <a href="manage-authors.php" class="btn btn-primary">← Back to Authors</a>
  </div>

  <div class="card card-narrow">
    <div class="card-header">
      <span class="card-title">Author Information</span>
    </div>
    <div class="card-body">
      <form method="post">
        <div class="form-group">
          <label>Author Name <span class="req">*</span></label>
          <input type="text" name="author" autocomplete="off" required/>
        </div>
        <div class="form-actions">
          <button type="submit" name="create" class="btn btn-gold">Add Author</button>
          <a href="manage-authors.php" class="btn btn-primary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>
</body>
</html>
<?php } ?>
