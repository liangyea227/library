<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

define('OVERDUE_DAYS', 7);

/* ── Fetch all overdue records from DB ── */
$sql = "
  SELECT
    tblissuedbookdetails.id          AS rid,
    tblstudents.StudentId,
    tblstudents.FullName,
    tblstudents.EmailId,
    tblstudents.MobileNumber,
    tblbooks.BookName,
    tblbooks.ISBNNumber,
    tblbooks.id                      AS bid,
    tblissuedbookdetails.IssuesDate,
    DATEDIFF(NOW(), tblissuedbookdetails.IssuesDate) AS days_held
  FROM tblissuedbookdetails
  JOIN tblstudents ON tblstudents.StudentId = tblissuedbookdetails.StudentID
  JOIN tblbooks    ON tblbooks.id           = tblissuedbookdetails.BookId
  WHERE
    (tblissuedbookdetails.RetrunStatus = 0
      OR tblissuedbookdetails.RetrunStatus IS NULL
      OR tblissuedbookdetails.RetrunStatus = '')
    AND tblissuedbookdetails.ReturnDate IS NULL
    AND DATEDIFF(NOW(), tblissuedbookdetails.IssuesDate) >= :days
  ORDER BY days_held DESC
";
$query = $dbh->prepare($sql);
$query->bindValue(':days', OVERDUE_DAYS, PDO::PARAM_INT);
$query->execute();
$overdueBooks = $query->fetchAll(PDO::FETCH_OBJ);
$totalOverdue = count($overdueBooks);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Overdue Books</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
  <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
  <style>
    /* ── Overdue-specific styles ── */
    .overdue-banner {
      background: #fde8e4;
      border: 1px solid #f5b8ae;
      border-left: 4px solid var(--rust);
      border-radius: var(--radius);
      padding: 14px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
      font-size: 13px;
      color: #9e3526;
      font-weight: 500;
    }
    .overdue-banner .banner-icon { font-size: 20px; flex-shrink: 0; }

    .overdue-stat { background: var(--rust); }
    .overdue-stat::before { background: #a03020 !important; }
    .overdue-stat .stat-icon { background: #fde8e4 !important; }

    /* Days overdue pill — colour shifts by severity */
    .days-pill {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      white-space: nowrap;
    }
    .days-pill.mild   { background: #fef3e2; color: #8a5e1a; }   /* 7–13 days  */
    .days-pill.medium { background: #fde8e4; color: #c0533a; }   /* 14–29 days */
    .days-pill.severe { background: #f8d0d0; color: #8b1c1c; }   /* 30+ days   */

    tbody tr.overdue-row { background: #fffaf9; }
    tbody tr.overdue-row:hover { background: #fff3f1; }

    .no-overdue {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-muted);
    }
    .no-overdue .no-icon { font-size: 48px; margin-bottom: 12px; }
    .no-overdue p { font-size: 15px; margin: 0; }
  </style>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Overdue Books</h1>
      <p class="page-subtitle">
        Books not returned after <?php echo OVERDUE_DAYS; ?> days &mdash;
        as of <?php echo date('d M Y'); ?>
      </p>
    </div>
    <a href="manage-issued-books.php" class="btn btn-primary">← All Issued Books</a>
  </div>

  <?php if($totalOverdue > 0): ?>

  <!-- ── Alert banner ── -->
  <div class="overdue-banner">
    <span class="banner-icon">⚠️</span>
    <span>
      <strong><?php echo $totalOverdue; ?> overdue record<?php echo $totalOverdue > 1 ? 's' : ''; ?> found.</strong>
      These students have not returned their books within the <?php echo OVERDUE_DAYS; ?>-day limit.
      Use the Update button to process a return or apply a fine.
    </span>
  </div>

  <!-- ── Summary stat card ── -->
  <div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));margin-bottom:28px;">
    <div class="stat-card issued" style="position:relative;overflow:hidden;">
      <div class="stat-icon">⏰</div>
      <div class="stat-number"><?php echo $totalOverdue; ?></div>
      <div class="stat-label">Overdue Books</div>
    </div>
    <?php
      /* Count how many are severely overdue (30+ days) */
      $severe = 0; $medium = 0; $mild = 0;
      foreach($overdueBooks as $b){
        $d = (int)$b->days_held;
        if($d >= 30) $severe++;
        elseif($d >= 14) $medium++;
        else $mild++;
      }
    ?>
    <div class="stat-card" style="position:relative;overflow:hidden;border-top:4px solid #c0533a;">
      <div class="stat-icon" style="background:#fde8e4;">🔴</div>
      <div class="stat-number" style="font-family:'Playfair Display',serif;font-size:36px;"><?php echo $severe; ?></div>
      <div class="stat-label">30+ Days Overdue</div>
    </div>
    <div class="stat-card" style="position:relative;overflow:hidden;border-top:4px solid var(--gold);">
      <div class="stat-icon" style="background:#fef3e2;">🟡</div>
      <div class="stat-number" style="font-family:'Playfair Display',serif;font-size:36px;"><?php echo $mild + $medium; ?></div>
      <div class="stat-label">7–29 Days Overdue</div>
    </div>
  </div>

  <!-- ── Overdue table ── -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Overdue Book Records</span>
      <span style="font-size:12px;color:var(--text-muted);">
        Threshold: <?php echo OVERDUE_DAYS; ?> days without return
      </span>
    </div>
    <div class="card-body">
      <div class="table-wrapper">
        <table id="overdueTable" class="display" style="width:100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Student Name</th>
              <th>Student ID</th>
              <th>Book Name</th>
              <th>ISBN</th>
              <th>Issued Date</th>
              <th>Due Date</th>
              <th>Days Overdue</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php $cnt = 1; foreach($overdueBooks as $result):
            $daysHeld   = (int)$result->days_held;
            $daysOver   = $daysHeld - OVERDUE_DAYS;
            $dueDate    = date('d M Y', strtotime($result->IssuesDate . ' +' . OVERDUE_DAYS . ' days'));
            $issuedFmt  = date('d M Y', strtotime($result->IssuesDate));

            if($daysHeld >= 30)      $pillClass = 'severe';
            elseif($daysHeld >= 14)  $pillClass = 'medium';
            else                     $pillClass = 'mild';
          ?>
            <tr class="overdue-row">
              <td><?php echo $cnt; ?></td>
              <td>
                <strong><?php echo htmlentities($result->FullName); ?></strong><br>
                <span style="font-size:11px;color:var(--text-muted);">
                  <?php echo htmlentities($result->EmailId); ?>
                </span>
              </td>
              <td>
                <a href="student-history.php?stdid=<?php echo htmlentities($result->StudentId); ?>"
                   style="color:var(--blue-soft);font-size:13px;font-family:monospace;">
                  <?php echo htmlentities($result->StudentId); ?>
                </a>
              </td>
              <td><?php echo htmlentities($result->BookName); ?></td>
              <td style="font-family:monospace;font-size:12px;">
                <?php echo htmlentities($result->ISBNNumber); ?>
              </td>
              <td><?php echo $issuedFmt; ?></td>
              <td style="color:var(--rust);font-weight:600;"><?php echo $dueDate; ?></td>
              <td>
                <span class="days-pill <?php echo $pillClass; ?>">
                  <?php echo $daysOver; ?> day<?php echo $daysOver != 1 ? 's' : ''; ?> overdue
                </span>
              </td>
              <td>
                <a href="update-issue-bookdeails.php?rid=<?php echo htmlentities($result->rid); ?>"
                   class="btn btn-success btn-sm">✏️ Update</a>
              </td>
            </tr>
          <?php $cnt++; endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php else: ?>

  <!-- ── No overdue records ── -->
  <div class="card">
    <div class="card-body">
      <div class="no-overdue">
        <div class="no-icon">✅</div>
        <p><strong>No overdue books!</strong><br>
        All borrowed books have been returned within the <?php echo OVERDUE_DAYS; ?>-day limit.</p>
      </div>
    </div>
  </div>

  <?php endif; ?>

</div><!-- /.page-wrapper -->

<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/dataTables/jquery.dataTables.js"></script>
<script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
<script>
$(document).ready(function(){
  $('#overdueTable').DataTable({
    order: [[7, 'desc']]   /* sort by days overdue descending by default */
  });
});
</script>
</body>
</html>
<?php } ?>