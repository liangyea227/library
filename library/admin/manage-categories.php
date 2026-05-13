<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_GET['del'])){
  $id=$_GET['del'];
  $sql="DELETE FROM tblcategory WHERE id=:id";
  $query=$dbh->prepare($sql); $query->bindParam(':id',$id,PDO::PARAM_STR); $query->execute();
  $_SESSION['delmsg']="Category deleted successfully";
  header('location:manage-categories.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Manage Categories</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
  <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Manage Categories</h1>
      <p class="page-subtitle">View and manage all book categories</p>
    </div>
    <a href="add-category.php" class="btn btn-gold">+ Add Category</a>
  </div>

  <?php
    foreach(['error','msg','updatemsg','delmsg'] as $key){
      if(!empty($_SESSION[$key])){
        $type = ($key=='error') ? 'danger' : 'success';
        echo '<div class="alert alert-'.$type.'">'.htmlentities($_SESSION[$key]).'</div>';
        $_SESSION[$key]="";
      }
    }
  ?>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Categories Listing</span>
    </div>
    <div class="card-body">
      <div class="table-wrapper">
        <table id="dataTables-example" class="display" style="width:100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Category Name</th>
              <th>Status</th>
              <th>Created</th>
              <th>Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
            $sql="SELECT * FROM tblcategory";
            $query=$dbh->prepare($sql); $query->execute();
            $cnt=1;
            foreach($query->fetchAll(PDO::FETCH_OBJ) as $result){ ?>
            <tr>
              <td><?php echo htmlentities($cnt); ?></td>
              <td><?php echo htmlentities($result->CategoryName); ?></td>
              <td>
                <?php if($result->Status==1): ?>
                  <span class="badge badge-success">Active</span>
                <?php else: ?>
                  <span class="badge badge-danger">Inactive</span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlentities($result->CreationDate); ?></td>
              <td><?php echo htmlentities($result->UpdationDate); ?></td>
              <td>
                <div class="action-group">
                  <a href="edit-category.php?catid=<?php echo htmlentities($result->id); ?>" class="btn btn-success btn-sm">✏️ Edit</a>
                  <a href="manage-categories.php?del=<?php echo htmlentities($result->id); ?>"
                     onclick="return confirm('Delete this category?')" class="btn btn-danger btn-sm">🗑️ Delete</a>
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
