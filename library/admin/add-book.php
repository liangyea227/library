<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_POST['add'])){
  $bookname = $_POST['bookname'];
  $category = $_POST['category'];
  $author   = $_POST['author'];
  $isbn     = $_POST['isbn'];
  $price    = $_POST['price'];
  $bookimg  = $_FILES["bookpic"]["name"];
  $bqty     = $_POST['bqty'];
  $extension = substr($bookimg, strlen($bookimg)-4, strlen($bookimg));
  $allowed_extensions = array(".jpg","jpeg",".png",".gif");
  $imgnewname = md5($bookimg.time()).$extension;
  if(!in_array($extension,$allowed_extensions)){
    echo "<script>alert('Invalid format. Only jpg / jpeg / png / gif allowed');</script>";
  } else {
    move_uploaded_file($_FILES["bookpic"]["tmp_name"],"bookimg/".$imgnewname);
    $sql="INSERT INTO tblbooks(BookName,CatId,AuthorId,ISBNNumber,BookPrice,bookImage,bookQty) VALUES(:bookname,:category,:author,:isbn,:price,:imgnewname,:bqty)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookname',$bookname,PDO::PARAM_STR);
    $query->bindParam(':category',$category,PDO::PARAM_STR);
    $query->bindParam(':author',$author,PDO::PARAM_STR);
    $query->bindParam(':isbn',$isbn,PDO::PARAM_STR);
    $query->bindParam(':price',$price,PDO::PARAM_STR);
    $query->bindParam(':imgnewname',$imgnewname,PDO::PARAM_STR);
    $query->bindParam(':bqty',$bqty,PDO::PARAM_STR);
    $query->execute();
    $lastInsertId = $dbh->lastInsertId();
    if($lastInsertId){
      echo "<script>alert('Book listed successfully'); window.location.href='manage-books.php';</script>";
    } else {
      echo "<script>alert('Something went wrong. Please try again'); window.location.href='manage-books.php';</script>";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Add Book</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
  <script>
  function checkisbnAvailability(){
    jQuery.ajax({
      url:"check_availability.php",
      data:'isbn='+jQuery("#isbn").val(),
      type:"POST",
      success:function(data){ jQuery("#isbn-availability-status").html(data); },
      error:function(){}
    });
  }
  </script>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Add Book</h1>
      <p class="page-subtitle">Add a new book to the library collection</p>
    </div>
    <a href="manage-books.php" class="btn btn-primary">← Back to Books</a>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Book Information</span>
    </div>
    <div class="card-body">
      <form method="post" enctype="multipart/form-data">
        <div class="form-grid">

          <div class="form-group">
            <label>Book Name <span class="req">*</span></label>
            <input type="text" name="bookname" autocomplete="off" required/>
          </div>

          <div class="form-group">
            <label>Category <span class="req">*</span></label>
            <select name="category" required>
              <option value="">Select Category</option>
              <?php
                $status=1;
                $sqlc="SELECT * FROM tblcategory WHERE Status=:status";
                $qc=$dbh->prepare($sqlc); $qc->bindParam(':status',$status,PDO::PARAM_STR); $qc->execute();
                foreach($qc->fetchAll(PDO::FETCH_OBJ) as $r){
                  echo '<option value="'.htmlentities($r->id).'">'.htmlentities($r->CategoryName).'</option>';
                }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Author <span class="req">*</span></label>
            <select name="author" required>
              <option value="">Select Author</option>
              <?php
                $sqla="SELECT * FROM tblauthors";
                $qa=$dbh->prepare($sqla); $qa->execute();
                foreach($qa->fetchAll(PDO::FETCH_OBJ) as $r){
                  echo '<option value="'.htmlentities($r->id).'">'.htmlentities($r->AuthorName).'</option>';
                }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>ISBN Number <span class="req">*</span></label>
            <input type="text" name="isbn" id="isbn" required autocomplete="off" onblur="checkisbnAvailability()"/>
            <span class="help-text">Must be unique — checked on blur</span>
            <span id="isbn-availability-status"></span>
          </div>

          <div class="form-group">
            <label>Price <span class="req">*</span></label>
            <input type="text" name="price" autocomplete="off" required/>
          </div>

          <div class="form-group">
            <label>Book Quantity <span class="req">*</span></label>
            <input type="text" name="bqty" autocomplete="off" required/>
          </div>

          <div class="form-group full">
            <label>Book Cover Image <span class="req">*</span></label>
            <input type="file" name="bookpic" required/>
            <span class="help-text">Accepted formats: jpg, jpeg, png, gif</span>
          </div>

        </div>

        <div class="form-actions">
          <button type="submit" name="add" class="btn btn-gold">Add Book</button>
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
