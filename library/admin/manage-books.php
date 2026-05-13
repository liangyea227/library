<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_GET['del'])){
  $id=$_GET['del'];
  $sql="DELETE FROM tblbooks WHERE id=:id";
  $query=$dbh->prepare($sql); $query->bindParam(':id',$id,PDO::PARAM_STR); $query->execute();
  $_SESSION['delmsg']="Book deleted successfully";
  header('location:manage-books.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Manage Books</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
  <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Manage Books</h1>
      <p class="page-subtitle">All books in the library collection</p>
    </div>
    <a href="add-book.php" class="btn btn-gold">+ Add New Book</a>
  </div>

 <?php if(!empty($_SESSION['delmsg'])): ?>
  <div class="alert alert-success"><?php echo htmlentities($_SESSION['delmsg']); $_SESSION['delmsg']=""; ?></div>
<?php endif; ?>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Books Listing</span>
    </div>
    <div class="card-body">
      <div class="table-wrapper">
        <table id="dataTables-example" class="display" style="width:100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Cover &amp; Name</th>
              <th>Category</th>
              <th>Author</th>
              <th>ISBN</th>
              <th>Details</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
            $sql="SELECT tblbooks.BookName,tblcategory.CategoryName,tblauthors.AuthorName,tblbooks.ISBNNumber,tblbooks.BookDetails,tblbooks.id as bookid,tblbooks.bookImage
                  FROM tblbooks
                  JOIN tblcategory ON tblcategory.id=tblbooks.CatId
                  JOIN tblauthors  ON tblauthors.id=tblbooks.AuthorId";
            $query=$dbh->prepare($sql); $query->execute();
            $cnt=1;
            foreach($query->fetchAll(PDO::FETCH_OBJ) as $result){ ?>
            <tr>
              <td><?php echo htmlentities($cnt); ?></td>
              <td>
                <div class="book-name-cell">
                  <img src="bookimg/<?php echo htmlentities($result->bookImage); ?>" class="book-thumb" alt="cover"/>
                  <strong><?php echo htmlentities($result->BookName); ?></strong>
                </div>
              </td>
              <td><?php echo htmlentities($result->CategoryName); ?></td>
              <td><?php echo htmlentities($result->AuthorName); ?></td>
              <td><?php echo htmlentities($result->ISBNNumber); ?></td>
              <td><?php echo htmlentities($result->BookDetails); ?></td>
              <td>
                <div class="action-group">
                  <a href="edit-book.php?bookid=<?php echo htmlentities($result->bookid); ?>" class="btn btn-success btn-sm">✏️ Edit</a>
                  <a href="manage-books.php?del=<?php echo htmlentities($result->bookid); ?>"
                     onclick="return confirm('Delete this book?')" class="btn btn-danger btn-sm">🗑️ Delete</a>
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
