<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_POST['update'])){
  $bookid=intval($_GET['bookid']);
  $bookimg=$_FILES["bookpic"]["name"];
  $cimage=$_POST['curremtimage'];
  $cpath="bookimg/".$cimage;
  $extension=substr($bookimg,strlen($bookimg)-4,strlen($bookimg));
  $allowed_extensions=array(".jpg","jpeg",".png",".gif");
  $imgnewname=md5($bookimg.time()).$extension;
  if(!in_array($extension,$allowed_extensions)){
    echo "<script>alert('Invalid format. Only jpg / jpeg / png / gif allowed');</script>";
  } else {
    move_uploaded_file($_FILES["bookpic"]["tmp_name"],"bookimg/".$imgnewname);
    $sql="UPDATE tblbooks SET bookImage=:imgnewname WHERE id=:bookid";
    $query=$dbh->prepare($sql);
    $query->bindParam(':imgnewname',$imgnewname,PDO::PARAM_STR);
    $query->bindParam(':bookid',$bookid,PDO::PARAM_STR);
    $query->execute();
    unlink($cpath);
    echo "<script>alert('Book cover updated successfully'); window.location.href='manage-books.php';</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Change Book Cover</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Change Book Cover</h1>
      <p class="page-subtitle">Upload a new cover image for this book</p>
    </div>
    <a href="manage-books.php" class="btn btn-primary">← Back to Books</a>
  </div>

  <div class="card" style="max-width:500px;">
    <div class="card-header">
      <span class="card-title">Cover Image</span>
    </div>
    <div class="card-body">
      <form method="post" enctype="multipart/form-data">
        <?php
          $bookid=intval($_GET['bookid']);
          $sql="SELECT BookName,bookImage,id as bookid FROM tblbooks WHERE id=:bookid";
          $query=$dbh->prepare($sql); $query->bindParam(':bookid',$bookid,PDO::PARAM_STR); $query->execute();
          foreach($query->fetchAll(PDO::FETCH_OBJ) as $result): ?>
        <input type="hidden" name="curremtimage" value="<?php echo htmlentities($result->bookImage); ?>"/>

        <div style="display:flex;gap:16px;align-items:flex-start;margin-bottom:20px;">
          <img src="bookimg/<?php echo htmlentities($result->bookImage); ?>" class="book-thumb" style="width:80px;height:110px;" alt="Current Cover"/>
          <div>
            <div style="font-weight:600;font-size:15px;margin-bottom:4px;"><?php echo htmlentities($result->BookName); ?></div>
            <div style="font-size:12px;color:var(--text-muted);">Current cover image</div>
          </div>
        </div>

        <div class="form-group">
          <label>New Cover Image <span class="req">*</span></label>
          <input type="file" name="bookpic" required/>
          <span class="help-text">Accepted formats: jpg, jpeg, png, gif</span>
        </div>
        <?php endforeach; ?>

        <div class="form-actions">
          <button type="submit" name="update" class="btn btn-gold">Update Cover</button>
          <a href="manage-books.php" class="btn btn-primary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>
</body>
</html>
<?php } ?>
