<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Dashboard</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Dashboard</h1>
      <p class="page-subtitle">Welcome back, Administrator</p>
    </div>
  </div>

  <?php
    $sql = "SELECT id from tblbooks";
    $q = $dbh->prepare($sql); $q->execute();
    $listdbooks = $q->rowCount();

    $sql2 = "SELECT id from tblissuedbookdetails where (RetrunStatus='' OR RetrunStatus IS NULL)";
    $q2 = $dbh->prepare($sql2); $q2->execute();
    $returnedbooks = $q2->rowCount();

    $sql3 = "SELECT id from tblstudents";
    $q3 = $dbh->prepare($sql3); $q3->execute();
    $regstds = $q3->rowCount();

    $sql4 = "SELECT id from tblauthors";
    $q4 = $dbh->prepare($sql4); $q4->execute();
    $listdathrs = $q4->rowCount();

    $sql5 = "SELECT id from tblcategory";
    $q5 = $dbh->prepare($sql5); $q5->execute();
    $listdcats = $q5->rowCount();
  ?>

  <div class="stats-grid">

    <a href="manage-books.php" class="stat-card books">
      <div class="stat-icon">📖</div>
      <div class="stat-number"><?php echo htmlentities($listdbooks); ?></div>
      <div class="stat-label">Books Listed</div>
      <span class="stat-arrow">→</span>
    </a>

    <a href="manage-issued-books.php" class="stat-card issued">
      <div class="stat-icon">🔄</div>
      <div class="stat-number"><?php echo htmlentities($returnedbooks); ?></div>
      <div class="stat-label">Books Not Returned</div>
      <span class="stat-arrow">→</span>
    </a>

    <a href="reg-students.php" class="stat-card students">
      <div class="stat-icon">🎓</div>
      <div class="stat-number"><?php echo htmlentities($regstds); ?></div>
      <div class="stat-label">Registered Students</div>
      <span class="stat-arrow">→</span>
    </a>

    <a href="manage-authors.php" class="stat-card authors">
      <div class="stat-icon">✍️</div>
      <div class="stat-number"><?php echo htmlentities($listdathrs); ?></div>
      <div class="stat-label">Authors Listed</div>
      <span class="stat-arrow">→</span>
    </a>

    <a href="manage-categories.php" class="stat-card categories">
      <div class="stat-icon">🗂️</div>
      <div class="stat-number"><?php echo htmlentities($listdcats); ?></div>
      <div class="stat-label">Categories</div>
      <span class="stat-arrow">→</span>
    </a>

  </div>

  <!-- Quick actions -->
  <div class="card" style="max-width:600px;">
    <div class="card-header">
      <span class="card-title">Quick Actions</span>
    </div>
    <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;">
      <a href="add-book.php"     class="btn btn-gold">+ Add Book</a>
      <a href="add-author.php"   class="btn btn-primary">+ Add Author</a>
      <a href="add-category.php" class="btn btn-primary">+ Add Category</a>
      <a href="issue-book.php"   class="btn btn-success">📋 Issue Book</a>
    </div>
  </div>

</div>

<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>
</body>
</html>
<?php } ?>
