<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_POST['update'])){
  $bookname=$_POST['bookname'];
  $category=$_POST['category'];
  $author=$_POST['author'];
  $isbn=$_POST['isbn'];
  $details=$_POST['details'];
  $bookid=intval($_GET['bookid']);
  $bqty=$_POST['bqty'];
  $sql="UPDATE tblbooks SET BookName=:bookname,CatId=:category,AuthorId=:author,BookDetails=:details,bookQty=:bqty WHERE id=:bookid";
  $query=$dbh->prepare($sql);
  $query->bindParam(':bookname',$bookname,PDO::PARAM_STR);
  $query->bindParam(':category',$category,PDO::PARAM_STR);
  $query->bindParam(':author',$author,PDO::PARAM_STR);
  $query->bindParam(':details',$details,PDO::PARAM_STR);
  $query->bindParam(':bookid',$bookid,PDO::PARAM_STR);
  $query->bindParam(':bqty',$bqty,PDO::PARAM_STR);
  $query->execute();
  echo "<script>alert('Book updated successfully'); window.location.href='manage-books.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Edit Book</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Edit Book</h1>
      <p class="page-subtitle">Update book details</p>
    </div>
    <a href="manage-books.php" class="btn btn-primary">← Back to Books</a>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Book Information</span>
    </div>
    <div class="card-body">
      <form method="post">
        <?php
          $bookid=intval($_GET['bookid']);
          $sql="SELECT tblbooks.BookName,tblcategory.CategoryName,tblcategory.id as cid,
                       tblauthors.AuthorName,tblauthors.id as athrid,tblbooks.ISBNNumber,
                       tblbooks.BookDetails,tblbooks.id as bookid,tblbooks.bookImage,bookQty
                FROM tblbooks
                JOIN tblcategory ON tblcategory.id=tblbooks.CatId
                JOIN tblauthors  ON tblauthors.id=tblbooks.AuthorId
                WHERE tblbooks.id=:bookid";
          $query=$dbh->prepare($sql); $query->bindParam(':bookid',$bookid,PDO::PARAM_STR); $query->execute();
          foreach($query->fetchAll(PDO::FETCH_OBJ) as $result):
            $catname=$result->CategoryName;
            $athrname=$result->AuthorName;
        ?>
        <div class="form-grid">

          <div class="form-group full" style="flex-direction:row;align-items:center;gap:20px;">
            <img src="bookimg/<?php echo htmlentities($result->bookImage); ?>" class="book-thumb" style="width:80px;height:110px;" alt="cover"/>
            <div>
              <div style="font-weight:600;color:var(--ink);margin-bottom:6px;"><?php echo htmlentities($result->BookName); ?></div>
              <a href="change-bookimg.php?bookid=<?php echo htmlentities($result->bookid); ?>" class="btn btn-primary btn-sm">Change Cover Image</a>
            </div>
          </div>

          <div class="form-group">
            <label>Book Name <span class="req">*</span></label>
            <input type="text" name="bookname" value="<?php echo htmlentities($result->BookName); ?>" required/>
          </div>

          <div class="form-group">
            <label>Category <span class="req">*</span></label>
            <select name="category" required>
              <option value="<?php echo htmlentities($result->cid); ?>"><?php echo htmlentities($catname); ?></option>
              <?php
                $sql1="SELECT * FROM tblcategory WHERE Status=1";
                $q1=$dbh->prepare($sql1); $q1->execute();
                foreach($q1->fetchAll(PDO::FETCH_OBJ) as $row){
                  if($catname==$row->CategoryName) continue;
                  echo '<option value="'.htmlentities($row->id).'">'.htmlentities($row->CategoryName).'</option>';
                }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Author <span class="req">*</span></label>
            <select name="author" required>
              <option value="<?php echo htmlentities($result->athrid); ?>"><?php echo htmlentities($athrname); ?></option>
              <?php
                $sql2="SELECT * FROM tblauthors";
                $q2=$dbh->prepare($sql2); $q2->execute();
                foreach($q2->fetchAll(PDO::FETCH_OBJ) as $ret){
                  if($athrname==$ret->AuthorName) continue;
                  echo '<option value="'.htmlentities($ret->id).'">'.htmlentities($ret->AuthorName).'</option>';
                }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>ISBN Number</label>
            <input type="text" name="isbn" value="<?php echo htmlentities($result->ISBNNumber); ?>" readonly style="background:#eee;cursor:not-allowed;"/>
            <span class="help-text">ISBN cannot be changed</span>
          </div>

          <div class="form-group">
            <label>Book Details</label>
            <input type="text" name="details" value="<?php echo htmlentities($result->BookDetails); ?>"/>
          </div>

          <div class="form-group">
            <label>Book Quantity <span class="req">*</span></label>
            <input type="text" name="bqty" value="<?php echo htmlentities($result->bookQty); ?>" required/>
          </div>

        </div>
        <?php endforeach; ?>

        <div class="form-actions">
          <button type="submit" name="update" class="btn btn-gold">Update Book</button>
          <a href="manage-books.php" class="btn btn-primary">Cancel</a>
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
