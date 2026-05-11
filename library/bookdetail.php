<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['login'])==0)
{ 
    header('location:index.php');
    exit;
}

$sid = $_SESSION['stdid'];
$bookid = isset($_GET['bookid']) ? (int)$_GET['bookid'] : 0;

if($bookid == 0) {
    header('location:dashboard.php');
    exit;
}

// ── Handle Borrow ──────────────────────────────────────────────
$borrowMsg = '';
$borrowMsgType = '';

if(isset($_POST['borrow'])) {
    // Check if student already has this book and hasn't returned it
    $chkSql = $dbh->prepare("SELECT id FROM tblissuedbookdetails 
        WHERE BookId=:bid AND StudentID=:sid 
        AND (RetrunStatus=0 OR RetrunStatus IS NULL OR RetrunStatus='')");
    $chkSql->bindParam(':bid', $bookid, PDO::PARAM_INT);
    $chkSql->bindParam(':sid', $sid, PDO::PARAM_STR);
    $chkSql->execute();

    if($chkSql->rowCount() > 0) {
        $borrowMsg = 'You already have this book borrowed and have not returned it yet.';
        $borrowMsgType = 'error';
    } else {
        // Check available qty
        $qtySql = $dbh->prepare("SELECT bookQty FROM tblbooks WHERE id=:bid");
        $qtySql->bindParam(':bid', $bookid, PDO::PARAM_INT);
        $qtySql->execute();
        $qtyRow = $qtySql->fetch(PDO::FETCH_OBJ);

        $issuedSql = $dbh->prepare("SELECT COUNT(id) as cnt FROM tblissuedbookdetails 
            WHERE BookId=:bid AND (RetrunStatus=0 OR RetrunStatus IS NULL OR RetrunStatus='')");
        $issuedSql->bindParam(':bid', $bookid, PDO::PARAM_INT);
        $issuedSql->execute();
        $issuedCount = $issuedSql->fetch(PDO::FETCH_OBJ)->cnt;

        $remaining = (int)$qtyRow->bookQty - (int)$issuedCount;

        if($remaining <= 0) {
            $borrowMsg = 'Sorry, this book is currently unavailable. All copies are reserved.';
            $borrowMsgType = 'error';
        } else {
            // Insert borrow record
            $insSql = $dbh->prepare("INSERT INTO tblissuedbookdetails (BookId, StudentID, RetrunStatus, remark) 
                VALUES (:bid, :sid, 0, 'NA')");
            $insSql->bindParam(':bid', $bookid, PDO::PARAM_INT);
            $insSql->bindParam(':sid', $sid, PDO::PARAM_STR);
            $insSql->execute();

            if($dbh->lastInsertId()) {
                $borrowMsg = 'Book borrowed successfully! Please return it on time.';
                $borrowMsgType = 'success';
            } else {
                $borrowMsg = 'Something went wrong. Please try again.';
                $borrowMsgType = 'error';
            }
        }
    }
}

// ── Fetch Book Details ─────────────────────────────────────────
$bookSql = $dbh->prepare("SELECT b.id, b.BookName, b.ISBNNumber, b.BookDetails, b.bookImage, b.bookQty, b.RegDate,
                                  a.AuthorName, c.CategoryName,
                                  (SELECT COUNT(*) FROM tblissuedbookdetails i 
                                   WHERE i.BookId = b.id 
                                   AND (i.RetrunStatus=0 OR i.RetrunStatus IS NULL OR i.RetrunStatus='')) AS issuedCount
                           FROM tblbooks b
                           LEFT JOIN tblauthors a ON b.AuthorId = a.id
                           LEFT JOIN tblcategory c ON b.CatId = c.id
                           WHERE b.id = :bid");
$bookSql->bindParam(':bid', $bookid, PDO::PARAM_INT);
$bookSql->execute();

if($bookSql->rowCount() == 0) {
    header('location:dashboard.php');
    exit;
}
$book = $bookSql->fetch(PDO::FETCH_OBJ);

$remaining = max(0, (int)$book->bookQty - (int)$book->issuedCount);
$available = $remaining > 0;
$imgSrc = 'admin/bookimg/' . $book->bookImage;

// ── Check if student currently has this book ───────────────────
$alreadyBorrowed = false;
$myChk = $dbh->prepare("SELECT id FROM tblissuedbookdetails 
    WHERE BookId=:bid AND StudentID=:sid 
    AND (RetrunStatus=0 OR RetrunStatus IS NULL OR RetrunStatus='')");
$myChk->bindParam(':bid', $bookid, PDO::PARAM_INT);
$myChk->bindParam(':sid', $sid, PDO::PARAM_STR);
$myChk->execute();
if($myChk->rowCount() > 0) $alreadyBorrowed = true;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Lim Library | <?php echo htmlentities($book->BookName); ?></title>

    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />

    <style>
     

        /* ── BREADCRUMB ───────────────────────────── */
        .breadcrumb-bar {
            padding-top: calc(var(--nav-h) + 20px);
            padding-left: 40px; padding-right: 40px;
            padding-bottom: 0;
        }
        .breadcrumb-bar nav {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--text-muted);
        }
        .breadcrumb-bar nav a { color: var(--blue); text-decoration: none; }
        .breadcrumb-bar nav a:hover { text-decoration: underline; }
        .breadcrumb-bar nav span { color: var(--border); }

        /* ── PAGE WRAPPER ─────────────────────────── */
        .page-wrap {
            max-width: 1000px;
            margin: 0 auto;
            padding: 28px 40px 60px;
        }

        /* ── BOOK DETAIL CARD ─────────────────────── */
        .book-detail-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            gap: 0;
        }

        /* Left: cover */
        .book-cover-col {
            width: 260px;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--blue-soft) 0%, #d0d0f8 100%);
            display: flex;
            align-items: stretch;
            position: relative;
        }
        .book-cover-col img {
            width: 100%;
            object-fit: cover;
            display: block;
        }
        .book-cover-col .cover-placeholder {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 30px 20px;
            min-height: 380px;
        }
        .cover-placeholder i { font-size: 56px; color: var(--blue); opacity: 0.3; }
        .cover-placeholder span {
            font-size: 14px; font-weight: 600; color: var(--blue);
            text-align: center; line-height: 1.4; opacity: 0.5;
        }

        /* Right: info */
        .book-info-col {
            flex: 1;
            padding: 36px 36px 32px;
            display: flex;
            flex-direction: column;
        }

        .book-category-badge {
            display: inline-block;
            background: var(--blue-soft);
            color: var(--blue);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 14px;
            align-self: flex-start;
        }

        .book-title-main {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            line-height: 1.25;
            color: var(--text);
            margin-bottom: 8px;
        }

        .book-author-main {
            font-size: 15px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }
        .book-author-main span { color: var(--blue); font-weight: 500; }

        .book-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 24px;
        }
        .meta-item { }
        .meta-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        .meta-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
        }

        .availability-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--bg);
        }
        .avail-dot {
            width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
        }
        .avail-dot.green { background: #2e7d32; }
        .avail-dot.red   { background: #e65100; }
        .avail-text { font-size: 14px; font-weight: 500; color: var(--text); }
        .avail-count { font-size: 13px; color: var(--text-muted); margin-left: auto; }

        .book-description {
            font-size: 14px;
            line-height: 1.7;
            color: var(--text-muted);
            margin-bottom: 28px;
            flex: 1;
        }

        /* Alert messages */
        .msg-box {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .msg-box.success {
            background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9;
        }
        .msg-box.error {
            background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2;
        }

        /* Borrow button */
        .btn-borrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 28px;
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background 0.2s, transform 0.15s;
            text-decoration: none;
        }
        .btn-borrow:hover { background: var(--blue-dark); transform: translateY(-1px); color: #fff; text-decoration: none; }
        .btn-borrow:active { transform: translateY(0); }
        .btn-borrow:disabled, .btn-borrow.disabled {
            background: #b0b0c8; cursor: not-allowed; transform: none;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 22px;
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-back:hover { border-color: var(--blue); color: var(--blue); text-decoration: none; }

        .action-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .already-borrowed-note {
            display: flex; align-items: center; gap: 8px;
            padding: 12px 16px; border-radius: 8px;
            background: var(--blue-soft); color: var(--blue);
            font-size: 13px; font-weight: 500;
            border: 1px solid #c0c0f0;
        }

        /* ── CONFIRM MODAL ────────────────────────── */
        .modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--white);
            border-radius: 16px;
            padding: 32px 28px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        .modal-icon {
            width: 56px; height: 56px; border-radius: 50%;
            background: var(--blue-soft);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
            font-size: 24px; color: var(--blue);
        }
        .modal-box h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px; margin-bottom: 10px; color: var(--text);
        }
        .modal-box p { font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 24px; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .btn-modal-confirm {
            padding: 11px 24px; background: var(--blue); color: #fff;
            border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; font-family: 'DM Sans', sans-serif;
        }
        .btn-modal-confirm:hover { background: var(--blue-dark); }
        .btn-modal-cancel {
            padding: 11px 24px; background: transparent; color: var(--text-muted);
            border: 1px solid var(--border); border-radius: 8px; font-size: 14px;
            cursor: pointer; font-family: 'DM Sans', sans-serif;
        }
        .btn-modal-cancel:hover { border-color: var(--text-muted); }

        /* ── RESPONSIVE ───────────────────────────── */
        @media (max-width: 720px) {
            .book-detail-card { flex-direction: column; }
            .book-cover-col { width: 100%; min-height: 260px; }
            .book-info-col { padding: 24px 20px; }
            .page-wrap { padding: 20px 16px 40px; }
            .breadcrumb-bar { padding-left: 16px; padding-right: 16px; }
            .book-title-main { font-size: 22px; }
            .book-meta-grid { grid-template-columns: 1fr; }
            .nav-brand-text { display: none; }
        }
    </style>
</head>
<body>

  <?php include('includes/header.php'); ?>
<!-- ══ BREADCRUMB ══════════════════════════════════════════════ -->
<div class="breadcrumb-bar">
    <nav>
        <a href="dashboard.php">Home</a>
        <span>/</span>
        <a href="dashboard.php">Books</a>
        <span>/</span>
        <?php echo htmlentities($book->BookName); ?>
    </nav>
</div>

<!-- ══ MAIN ════════════════════════════════════════════════════ -->
<div class="page-wrap">
    <div class="book-detail-card">

        <!-- Cover -->
        <div class="book-cover-col">
            <?php if(!empty($book->bookImage)): ?>
            <img src="<?php echo htmlentities($imgSrc); ?>"
                 alt="<?php echo htmlentities($book->BookName); ?>"
                 onerror="this.style.display='none'; document.getElementById('cover-placeholder').style.display='flex';" />
            <div class="cover-placeholder" id="cover-placeholder" style="display:none;">
                <i class="fa fa-book"></i>
                <span><?php echo htmlentities($book->BookName); ?></span>
            </div>
            <?php else: ?>
            <div class="cover-placeholder" id="cover-placeholder">
                <i class="fa fa-book"></i>
                <span><?php echo htmlentities($book->BookName); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="book-info-col">

            <?php if(!empty($book->CategoryName)): ?>
            <span class="book-category-badge"><?php echo htmlentities($book->CategoryName); ?></span>
            <?php endif; ?>

            <h1 class="book-title-main"><?php echo htmlentities($book->BookName); ?></h1>

            <p class="book-author-main">
                by <span><?php echo htmlentities(trim($book->AuthorName ?? 'Unknown Author')); ?></span>
            </p>

            <div class="book-meta-grid">
                <div class="meta-item">
                    <div class="meta-label">ISBN</div>
                    <div class="meta-value"><?php echo !empty($book->ISBNNumber) ? htmlentities($book->ISBNNumber) : '—'; ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Total Copies</div>
                    <div class="meta-value"><?php echo (int)$book->bookQty; ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Currently Issued</div>
                    <div class="meta-value"><?php echo (int)$book->issuedCount; ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Date Added</div>
                    <div class="meta-value"><?php echo date('d M Y', strtotime($book->RegDate)); ?></div>
                </div>
            </div>

            <!-- Availability indicator -->
            <div class="availability-row">
                <div class="avail-dot <?php echo $available ? 'green' : 'red'; ?>"></div>
                <span class="avail-text"><?php echo $available ? 'Available to Borrow' : 'Currently Unavailable'; ?></span>
                <span class="avail-count"><?php echo $remaining; ?> of <?php echo (int)$book->bookQty; ?> copies left</span>
            </div>

            <!-- Book description -->
            <?php if(!empty($book->BookDetails)): ?>
            <p class="book-description"><?php echo nl2br(htmlentities($book->BookDetails)); ?></p>
            <?php else: ?>
            <p class="book-description" style="font-style:italic;">No description available for this book.</p>
            <?php endif; ?>

            <!-- Borrow message -->
            <?php if($borrowMsg != ''): ?>
            <div class="msg-box <?php echo $borrowMsgType; ?>">
                <i class="fa <?php echo $borrowMsgType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo htmlentities($borrowMsg); ?>
            </div>
            <?php endif; ?>

            <!-- Action buttons -->
            <div class="action-row">
                <a href="dashboard.php" class="btn-back">
                    <i class="fa fa-arrow-left"></i> Back
                </a>

                <?php if($alreadyBorrowed || $borrowMsgType == 'success'): ?>
                <div class="already-borrowed-note">
                    <i class="fa fa-check-circle"></i>
                    You currently have this book borrowed.
                </div>
                <?php elseif($available): ?>
                <button type="button" class="btn-borrow" onclick="openConfirm()">
                    <i class="fa fa-book"></i> Borrow this Book
                </button>
                <?php else: ?>
                <button type="button" class="btn-borrow disabled" disabled>
                    <i class="fa fa-times-circle"></i> Unavailable
                </button>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- ══ CONFIRM MODAL ═══════════════════════════════════════════ -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <div class="modal-icon"><i class="fa fa-book"></i></div>
        <h3>Confirm Borrow</h3>
        <p>You are about to borrow <strong><?php echo htmlentities($book->BookName); ?></strong>. Please make sure to return it on time.</p>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeConfirm()">Cancel</button>
            <form method="post" style="display:inline;">
                <input type="hidden" name="borrow" value="1" />
                <button type="submit" class="btn-modal-confirm">Yes, Borrow</button>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script>
    function openConfirm()  { document.getElementById('confirmModal').classList.add('open'); }
    function closeConfirm() { document.getElementById('confirmModal').classList.remove('open'); }
    // Close modal clicking outside
    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if(e.target === this) closeConfirm();
    });
</script>
</body>
</html>