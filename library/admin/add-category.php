<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_POST['create'])){
  $category = $_POST['category'];
  $status   = $_POST['status'];
  $sql="INSERT INTO tblcategory(CategoryName,Status) VALUES(:category,:status)";
  $query=$dbh->prepare($sql);
  $query->bindParam(':category',$category,PDO::PARAM_STR);
  $query->bindParam(':status',$status,PDO::PARAM_STR);
  $query->execute();
  if($dbh->lastInsertId()){
    $_SESSION['msg']="Category created successfully";
    header('location:manage-categories.php');
  } else {
    $_SESSION['error']="Something went wrong. Please try again";
    header('location:manage-categories.php');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Add Category</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Add Category</h1>
      <p class="page-subtitle">Create a new book category</p>
    </div>
    <a href="manage-categories.php" class="btn btn-primary">← Back to Categories</a>
  </div>

  <div class="card card-narrow">
    <div class="card-header">
      <span class="card-title">Category Information</span>
    </div>
    <div class="card-body">
      <form method="post">
        <div class="form-group">
          <label>Category Name <span class="req">*</span></label>
          <input type="text" name="category" autocomplete="off" required/>
        </div>
        <div class="form-group mt-12">
          <label>Status</label>
          <div class="radio-group">
            <label class="radio-label">
              <input type="radio" name="status" value="1" checked/> Active
            </label>
            <label class="radio-label">
              <input type="radio" name="status" value="0"/> Inactive
            </label>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="create" class="btn btn-gold">Create Category</button>
          <a href="manage-categories.php" class="btn btn-primary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>
</body>
</html>
<?php } ?>
