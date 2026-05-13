<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['login'])==0) {   
    header('location:index.php');
    exit;
}

$sid = $_SESSION['stdid'];
$returnMsg   = '';
$returnError = '';

// Handle Return Book
if(isset($_POST['return_book'])) {
    $rid = (int)$_POST['rid'];
    $returnDate = date('Y-m-d');
    $sql = "UPDATE tblissuedbookdetails SET ReturnDate=:returnDate, RetrunStatus=1, fine=0 
            WHERE id=:rid AND StudentId=:sid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':returnDate', $returnDate, PDO::PARAM_STR);
    $query->bindParam(':rid',        $rid,         PDO::PARAM_INT);
    $query->bindParam(':sid',        $sid,         PDO::PARAM_STR);
    $query->execute();
    if($query->rowCount() > 0) {
        $returnMsg = 'Book returned successfully on ' . date('d M Y') . '.';
    } else {
        $returnError = 'Unable to return the book. Please try again.';
    }
}

// Fetch Issued Books
$sql = "SELECT tblbooks.BookName, tblbooks.ISBNNumber,
               tblissuedbookdetails.IssuesDate,
               tblissuedbookdetails.ReturnDate,
               tblissuedbookdetails.RetrunStatus,
               tblissuedbookdetails.id as rid,
               tblissuedbookdetails.fine
        FROM tblissuedbookdetails
        JOIN tblstudents ON tblstudents.StudentId = tblissuedbookdetails.StudentId
        JOIN tblbooks    ON tblbooks.id            = tblissuedbookdetails.BookId
        WHERE tblstudents.StudentId = :sid
        ORDER BY tblissuedbookdetails.id DESC";
$query = $dbh->prepare($sql);
$query->bindParam(':sid', $sid, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

$totalBorrowed = count($results);
$notReturned   = 0;
$returned      = 0;
foreach($results as $r) {
    if($r->ReturnDate == '' || $r->RetrunStatus == 0 || $r->RetrunStatus === null) $notReturned++;
    else $returned++;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Lim Library | My Borrowed Books</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <?php include('includes/header.php'); ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        .borrow-page { padding-top: var(--nav-h); min-height: 100vh; }
        .borrow-container { max-width: 1000px; margin: 0 auto; padding: 36px 24px 60px; }

        /* Page Title */
        .page-title { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
        .page-title-icon {
            width: 48px; height: 48px; border-radius: 14px;
            background: var(--blue-soft); color: var(--blue);
            display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;
        }
        .page-title h1 { font-family: 'Playfair Display', serif; font-size: 26px; color: var(--text); font-weight: 600; }
        .page-title p { font-size: 13px; color: var(--text-muted); margin-top: 3px; }

        /* Stat Cards */
        .stats-row { display: flex; gap: 14px; margin-bottom: 28px; }
        .stat-card {
            flex: 1; background: var(--white); border: 1px solid var(--border);
            border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 14px;
        }
        .stat-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .stat-icon.blue  { background: var(--blue-soft); color: var(--blue); }
        .stat-icon.amber { background: #fff8e1; color: #f59e0b; }
        .stat-icon.green { background: #e8f5e9; color: #2e7d32; }
        .stat-label { font-size: 11px; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
        .stat-value { font-size: 24px; font-weight: 700; color: var(--text); line-height: 1.1; }

        /* Alerts */
        .alert-success, .alert-error {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 13px 18px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 20px;
        }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .alert-error   { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .alert-success i, .alert-error i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

        /* Main Card */
        .main-card { background: var(--white); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }
        .card-header {
            padding: 18px 24px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .card-header-left { display: flex; align-items: center; gap: 10px; }
        .card-header-left i { font-size: 16px; color: var(--blue); }
        .card-header-left h3 { font-size: 14px; font-weight: 600; color: var(--text); }
        .card-header-badge {
            font-size: 11px; font-weight: 700; padding: 3px 10px;
            border-radius: 20px; background: var(--blue-soft); color: var(--blue); letter-spacing: 0.3px;
        }

        /* Table */
        .table-wrap { overflow-x: auto; }
        table.borrow-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .borrow-table thead tr { background: var(--bg); border-bottom: 1px solid var(--border); }
        .borrow-table thead th {
            padding: 12px 16px; font-size: 11px; font-weight: 700;
            letter-spacing: 0.8px; text-transform: uppercase; color: var(--text-muted); white-space: nowrap;
        }
        .borrow-table tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
        .borrow-table tbody tr:last-child { border-bottom: none; }
        .borrow-table tbody tr:hover { background: #fafafe; }
        .borrow-table td { padding: 14px 16px; vertical-align: middle; color: var(--text); }

        /* Book Cell */
        .book-cell { display: flex; align-items: center; gap: 12px; }
        .book-thumb {
            width: 36px; height: 36px; background: var(--blue-soft); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; color: var(--blue); font-size: 16px; flex-shrink: 0;
        }
        .book-name { font-weight: 600; font-size: 13px; color: var(--text); }
        .book-isbn { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

        /* Status Badges */
        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.3px; padding: 4px 10px; border-radius: 20px;
        }
        .status-badge.pending  { background: #fff8e1; color: #b45309; }
        .status-badge.returned { background: #e8f5e9; color: #2e7d32; }
        .status-badge i { font-size: 10px; }

        /* Return Button */
        .btn-return {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; background: var(--blue); color: #fff;
            border: none; border-radius: 8px; font-size: 12px; font-weight: 600;
            font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.2s, transform 0.1s;
        }
        .btn-return:hover { background: var(--blue-dark); transform: translateY(-1px); }
        .btn-return:active { transform: translateY(0); }
        .btn-return i { font-size: 12px; }

        /* Empty State */
        .empty-state { text-align: center; padding: 60px 24px; }
        .empty-icon {
            width: 72px; height: 72px; background: var(--blue-soft); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; color: var(--blue); margin: 0 auto 18px;
        }
        .empty-state h3 { font-size: 17px; font-weight: 600; color: var(--text); margin-bottom: 8px; }
        .empty-state p  { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; }
        .btn-browse {
            display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px;
            background: var(--blue); color: #fff; border-radius: 8px;
            font-size: 13px; font-weight: 600; text-decoration: none; transition: background 0.2s;
        }
        .btn-browse:hover { background: var(--blue-dark); text-decoration: none; color: #fff; }

        @media (max-width: 700px) {
            .stats-row { flex-direction: column; }
            .borrow-container { padding: 24px 14px 48px; }
            .page-title h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
<div class="borrow-page">
<div class="borrow-container">

    <!-- Page Title -->
    <div class="page-title">
        <div class="page-title-icon"><i class="fa fa-book"></i></div>
        <div>
            <h1>My Borrowed Books</h1>
            <p>Track and manage your book borrowing history</p>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa fa-book"></i></div>
            <div>
                <div class="stat-label">Total Borrowed</div>
                <div class="stat-value"><?php echo $totalBorrowed; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fa fa-clock-o"></i></div>
            <div>
                <div class="stat-label">Not Returned</div>
                <div class="stat-value"><?php echo $notReturned; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fa fa-check-circle"></i></div>
            <div>
                <div class="stat-label">Returned</div>
                <div class="stat-value"><?php echo $returned; ?></div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if($returnMsg): ?>
    <div class="alert-success">
        <i class="fa fa-check-circle"></i>
        <?php echo htmlentities($returnMsg); ?>
    </div>
    <?php endif; ?>
    <?php if($returnError): ?>
    <div class="alert-error">
        <i class="fa fa-exclamation-circle"></i>
        <?php echo htmlentities($returnError); ?>
    </div>
    <?php endif; ?>

    <!-- Table Card -->
    <div class="main-card">
        <div class="card-header">
            <div class="card-header-left">
                <i class="fa fa-list-alt"></i>
                <h3>Borrowing History</h3>
            </div>
            <span class="card-header-badge"><?php echo $totalBorrowed; ?> Records</span>
        </div>

        <?php if($totalBorrowed > 0): ?>
        <div class="table-wrap">
            <table class="borrow-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php $cnt = 1; foreach($results as $result): 
                    $isReturned = ($result->ReturnDate != '' && $result->RetrunStatus == 1);
                ?>
                    <tr>
                        <td style="color:var(--text-muted); font-size:12px;"><?php echo $cnt; ?></td>
                        <td>
                            <div class="book-cell">
                                <div class="book-thumb"><i class="fa fa-book"></i></div>
                                <div>
                                    <div class="book-name"><?php echo htmlentities($result->BookName); ?></div>
                                    <div class="book-isbn">ISBN: <?php echo htmlentities($result->ISBNNumber); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlentities($result->IssuesDate); ?></td>
                        <td>
                            <?php if(!$isReturned): ?>
                                <span class="status-badge pending"><i class="fa fa-clock-o"></i> Not Yet</span>
                            <?php else: ?>
                                <?php echo htmlentities($result->ReturnDate); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!$isReturned): ?>
                                <span class="status-badge pending"><i class="fa fa-refresh"></i> Borrowed</span>
                            <?php else: ?>
                                <span class="status-badge returned"><i class="fa fa-check"></i> Returned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!$isReturned): ?>
                                <form method="post" action="" style="margin:0;" onsubmit="return confirm('Return this book?');">
                                    <input type="hidden" name="rid" value="<?php echo (int)$result->rid; ?>" />
                                    <button type="submit" name="return_book" class="btn-return">
                                        <i class="fa fa-undo"></i> Return
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="font-size:12px; color:var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php $cnt++; endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-book"></i></div>
            <h3>No borrowed books yet</h3>
            <p>You haven't borrowed any books from the library yet.</p>
            <a href="dashboard.php" class="btn-browse"><i class="fa fa-search"></i> Browse Books</a>
        </div>
        <?php endif; ?>
    </div>

</div>
</div>

<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
</body>
</html>