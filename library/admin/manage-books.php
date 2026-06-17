<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));

if(isset($_GET['del'])){
  if(!isset($_GET['tok']) || $_GET['tok'] !== $_SESSION['csrf_token']){
    header('location:manage-books.php'); exit;
  }
  $id = (int)$_GET['del'];
  // FIX: Check for active loans before deleting
  $chkSql = "SELECT COUNT(id) as cnt FROM tblissuedbookdetails 
             WHERE BookId=:id AND (RetrunStatus=0 OR RetrunStatus IS NULL OR RetrunStatus='')";
  $chkQ = $dbh->prepare($chkSql);
  $chkQ->bindParam(':id', $id, PDO::PARAM_INT);
  $chkQ->execute();
  $chkRow = $chkQ->fetch(PDO::FETCH_OBJ);
  if($chkRow->cnt > 0){
    $_SESSION['delmsg_err'] = "Cannot delete: this book has " . $chkRow->cnt . " active loan(s). Return all copies first.";
  } else {
    $sql = "DELETE FROM tblbooks WHERE id=:id";
    $query = $dbh->prepare($sql); $query->bindParam(':id',$id,PDO::PARAM_INT); $query->execute();
    $_SESSION['delmsg'] = "Book deleted successfully";
  }
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
  <style>
    .avail-cell { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .avail-badge { display:inline-block; padding:2px 10px; border-radius:12px; font-size:11px; font-weight:700; letter-spacing:.4px; white-space:nowrap; }
    .badge-avail   { background:#d1fae5; color:#065f46; }
    .badge-low     { background:#fef3c7; color:#92400e; }
    .badge-unavail { background:#fee2e2; color:#991b1b; }
    .avail-count { font-size:12px; color:#6b7280; white-space:nowrap; }
  </style>
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
<?php if(!empty($_SESSION['delmsg_err'])): ?>
  <div class="alert alert-danger"><?php echo htmlentities($_SESSION['delmsg_err']); $_SESSION['delmsg_err']=""; ?></div>
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
              <th>Availability</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
            $sql = "SELECT tblbooks.BookName,tblcategory.CategoryName,tblauthors.AuthorName,tblbooks.ISBNNumber,tblbooks.BookDetails,tblbooks.id as bookid,tblbooks.bookImage,tblbooks.bookQty,
                    (SELECT COUNT(id) FROM tblissuedbookdetails
                     WHERE BookId=tblbooks.id AND (RetrunStatus=0 OR RetrunStatus IS NULL OR RetrunStatus='')) AS active_loans
                    FROM tblbooks
                    JOIN tblcategory ON tblcategory.id=tblbooks.CatId
                    JOIN tblauthors  ON tblauthors.id=tblbooks.AuthorId";
            $query = $dbh->prepare($sql); $query->execute();
            $cnt = 1;
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
                <?php
                  $qty  = (int)$result->bookQty;
                  $on_loan = (int)$result->active_loans;
                  $avail = max(0, $qty - $on_loan);
                  if ($avail === 0) {
                    $badge = 'badge-unavail';
                    $label = 'Unavailable';
                  } elseif ($avail <= 1) {
                    $badge = 'badge-low';
                    $label = 'Low Stock';
                  } else {
                    $badge = 'badge-avail';
                    $label = 'Available';
                  }
                ?>
                <div class="avail-cell">
                  <span class="avail-badge <?php echo $badge; ?>"><?php echo $label; ?></span>
                  <span class="avail-count"><?php echo $avail; ?> / <?php echo $qty; ?></span>
                </div>
              </td>
              <td>
                <div class="action-group">
                  <a href="edit-book.php?bookid=<?php echo htmlentities($result->bookid); ?>" class="btn btn-success btn-sm">✏️ Edit</a>
                  <a href="manage-books.php?del=<?php echo htmlentities($result->bookid); ?>&tok=<?php echo $_SESSION["csrf_token"]; ?>"
                     class="btn btn-danger btn-sm btn-delete"
                     data-loans="<?php echo (int)$result->active_loans; ?>"
                     data-title="<?php echo htmlspecialchars($result->BookName, ENT_QUOTES); ?>">🗑️ Delete</a>
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
$(document).ready(function(){
  $('#dataTables-example').DataTable();

  $(document).on('click', '.btn-delete', function(e){
    e.preventDefault();
    var loans = parseInt($(this).data('loans'));
    var title = $(this).data('title');
    var href  = $(this).attr('href');

    if(loans > 0){
      alert('Cannot delete: "' + title + '" has ' + loans + ' active loan(s). Return all copies first.');
      return false;
    }
    if(confirm('Delete "' + title + '"? This cannot be undone.')){
      window.location.href = href;
    }
  });
});
</script>
</body>
</html>
<?php } ?>