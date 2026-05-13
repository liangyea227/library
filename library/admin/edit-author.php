<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_POST['update'])){
  $athrid=intval($_GET['athrid']);
  $author=$_POST['author'];
  $sql="UPDATE tblauthors SET AuthorName=:author WHERE id=:athrid";
  $query=$dbh->prepare($sql);
  $query->bindParam(':author',$author,PDO::PARAM_STR);
  $query->bindParam(':athrid',$athrid,PDO::PARAM_STR);
  $query->execute();
  $_SESSION['updatemsg']="Author updated successfully";
  header('location:manage-authors.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Edit Author</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Edit Author</h1>
      <p class="page-subtitle">Update author information</p>
    </div>
    <a href="manage-authors.php" class="btn btn-primary">← Back to Authors</a>
  </div>

  <div class="card card-narrow">
    <div class="card-header">
      <span class="card-title">Author Information</span>
    </div>
    <div class="card-body">
      <form method="post">
        <?php
          $athrid=intval($_GET['athrid']);
          $sql="SELECT * FROM tblauthors WHERE id=:athrid";
          $query=$dbh->prepare($sql); $query->bindParam(':athrid',$athrid,PDO::PARAM_STR); $query->execute();
          foreach($query->fetchAll(PDO::FETCH_OBJ) as $result): ?>
        <div class="form-group">
          <label>Author Name <span class="req">*</span></label>
          <input type="text" name="author" value="<?php echo htmlentities($result->AuthorName); ?>" required/>
        </div>
        <?php endforeach; ?>
        <div class="form-actions">
          <button type="submit" name="update" class="btn btn-gold">Update Author</button>
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
