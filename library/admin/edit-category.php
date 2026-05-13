<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_POST['update'])){
  $category=$_POST['category'];
  $status=$_POST['status'];
  $catid=intval($_GET['catid']);
  $sql="UPDATE tblcategory SET CategoryName=:category,Status=:status WHERE id=:catid";
  $query=$dbh->prepare($sql);
  $query->bindParam(':category',$category,PDO::PARAM_STR);
  $query->bindParam(':status',$status,PDO::PARAM_STR);
  $query->bindParam(':catid',$catid,PDO::PARAM_STR);
  $query->execute();
  $_SESSION['updatemsg']="Category updated successfully";
  header('location:manage-categories.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Edit Category</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Edit Category</h1>
      <p class="page-subtitle">Update category details</p>
    </div>
    <a href="manage-categories.php" class="btn btn-primary">← Back to Categories</a>
  </div>

  <div class="card card-narrow">
    <div class="card-header">
      <span class="card-title">Category Information</span>
    </div>
    <div class="card-body">
      <form method="post">
        <?php
          $catid=intval($_GET['catid']);
          $sql="SELECT * FROM tblcategory WHERE id=:catid";
          $query=$dbh->prepare($sql); $query->bindParam(':catid',$catid,PDO::PARAM_STR); $query->execute();
          foreach($query->fetchAll(PDO::FETCH_OBJ) as $result): ?>
        <div class="form-group">
          <label>Category Name <span class="req">*</span></label>
          <input type="text" name="category" value="<?php echo htmlentities($result->CategoryName); ?>" required/>
        </div>
        <div class="form-group mt-12">
          <label>Status</label>
          <div class="radio-group">
            <label class="radio-label">
              <input type="radio" name="status" value="1" <?php echo ($result->Status==1)?'checked':''; ?>/> Active
            </label>
            <label class="radio-label">
              <input type="radio" name="status" value="0" <?php echo ($result->Status==0)?'checked':''; ?>/> Inactive
            </label>
          </div>
        </div>
        <?php endforeach; ?>
        <div class="form-actions">
          <button type="submit" name="update" class="btn btn-gold">Update Category</button>
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
