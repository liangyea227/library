<?php 
require_once("includes/config.php");
if(!empty($_POST["bookid"])) {
  $bookid = $_POST["bookid"];
  // FIX: Use a bound parameter for the LIKE clause — no more direct string interpolation
  $like   = '%' . $bookid . '%';

  $sql = "SELECT tblbooks.BookName as BookName, tblcategory.CategoryName, tblauthors.AuthorName,
                 tblbooks.ISBNNumber, tblbooks.BookPrice, tblbooks.id as bookid,
                 tblbooks.bookImage, tblbooks.isIssued, tblbooks.bookQty,
                 COUNT(CASE WHEN (tblissuedbookdetails.RetrunStatus = 0 
                               OR tblissuedbookdetails.RetrunStatus IS NULL 
                               OR tblissuedbookdetails.RetrunStatus = '')
                       THEN 1 END) AS issuedBooks
          FROM tblbooks
          LEFT JOIN tblissuedbookdetails ON tblissuedbookdetails.BookId = tblbooks.id
          LEFT JOIN tblauthors ON tblauthors.id = tblbooks.AuthorId
          LEFT JOIN tblcategory on tblcategory.id = tblbooks.CatId
          WHERE (tblbooks.ISBNNumber = :bookid OR tblbooks.BookName LIKE :like)
          GROUP BY tblbooks.id";

  $query = $dbh->prepare($sql);
  $query->bindParam(':bookid', $bookid, PDO::PARAM_STR);
  $query->bindParam(':like',   $like,   PDO::PARAM_STR);
  $query->execute();
  $results = $query->fetchAll(PDO::FETCH_OBJ);

  if($query->rowCount() > 0){
?>
<table border="1">
  <tr>
<?php foreach ($results as $result) {
    $aqty = max(0, (int)$result->bookQty - (int)$result->issuedBooks);
?>
    <th style="padding-left:5%; width: 10%;">
      <img src="bookimg/<?php echo htmlentities($result->bookImage); ?>" width="120"><br />
      <?php echo htmlentities($result->BookName); ?><br />
      <?php echo htmlentities($result->AuthorName); ?><br />
      Book Quantity: <?php echo htmlentities($result->bookQty); ?><br />
      Available Book Quantity: <?php echo htmlentities($aqty); ?><br />
      <?php if($aqty == 0): ?>
        <p style="color:red;">Book not available for issue.</p>
      <?php else: ?>
        <input type="radio" name="bookid" value="<?php echo htmlentities($result->bookid); ?>" required>
        <input type="hidden" name="aqty" value="<?php echo htmlentities($aqty); ?>" required>
      <?php endif; ?>
    </th>
<?php
  echo "<script>$('#submit').prop('disabled',false);</script>";
}
?>
  </tr>
</table>
</div>
</div>

<?php  
  } else { ?>
<p>Record not found. Please try again.</p>
<?php
  echo "<script>$('#submit').prop('disabled',true);</script>";
  }
}
?>
