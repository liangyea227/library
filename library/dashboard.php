<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['login'])==0)
{ 
    header('location:index.php');
    exit;
}

// ── Session student ID ─────────────────────────────────────────
$sid = $_SESSION['stdid'];

// ── Stats ──────────────────────────────────────────────────────
$sqlBooks = $dbh->prepare("SELECT COUNT(id) as total FROM tblbooks");
$sqlBooks->execute();
$listdbooks = $sqlBooks->fetch(PDO::FETCH_OBJ)->total;

$sqlNotRet = $dbh->prepare("SELECT COUNT(id) as total FROM tblissuedbookdetails 
    WHERE StudentID=:sid AND (RetrunStatus=0 OR RetrunStatus IS NULL OR RetrunStatus='')");
$sqlNotRet->bindParam(':sid', $sid, PDO::PARAM_STR);
$sqlNotRet->execute();
$returnedbooks = $sqlNotRet->fetch(PDO::FETCH_OBJ)->total;

$sqlIssued = $dbh->prepare("SELECT COUNT(id) as total FROM tblissuedbookdetails WHERE StudentID=:sid");
$sqlIssued->bindParam(':sid', $sid, PDO::PARAM_STR);
$sqlIssued->execute();
$totalissuedbook = $sqlIssued->fetch(PDO::FETCH_OBJ)->total;

// ── Categories from DB (active only) ──────────────────────────
$catSql = $dbh->prepare("SELECT id, CategoryName FROM tblcategory WHERE Status=1 ORDER BY CategoryName ASC");
$catSql->execute();
$categories = $catSql->fetchAll(PDO::FETCH_OBJ);

// ── Authors from DB (only authors who have books) ──────────────
$authSql = $dbh->prepare("SELECT a.id, a.AuthorName FROM tblauthors a 
    INNER JOIN tblbooks b ON b.AuthorId = a.id 
    GROUP BY a.id, a.AuthorName ORDER BY a.AuthorName ASC");
$authSql->execute();
$authors = $authSql->fetchAll(PDO::FETCH_OBJ);

// ── Filters from GET ───────────────────────────────────────────
$filterCat    = isset($_GET['cat'])    ? (int)$_GET['cat']    : 0;
$filterAuthor = isset($_GET['author']) ? (int)$_GET['author'] : 0;
$searchQuery  = isset($_GET['search']) ? trim($_GET['search']) : '';

// ── Books query with correct joins ─────────────────────────────
$bookSql = "SELECT b.id, b.BookName, b.ISBNNumber, b.bookImage, b.bookQty,
                   a.AuthorName, a.id AS AuthorId,
                   c.CategoryName, c.id AS CatId,
                   (SELECT COUNT(*) FROM tblissuedbookdetails i 
                    WHERE i.BookId = b.id 
                    AND (i.RetrunStatus = 0 OR i.RetrunStatus IS NULL OR i.RetrunStatus = '')) AS issuedCount
            FROM tblbooks b
            LEFT JOIN tblauthors a ON b.AuthorId = a.id
            LEFT JOIN tblcategory c ON b.CatId = c.id
            WHERE 1=1";

$params = [];

if($filterCat > 0) {
    $bookSql .= " AND b.CatId = :cat";
    $params[':cat'] = $filterCat;
}
if($filterAuthor > 0) {
    $bookSql .= " AND b.AuthorId = :author";
    $params[':author'] = $filterAuthor;
}
if($searchQuery != '') {
    $bookSql .= " AND (b.BookName LIKE :search OR a.AuthorName LIKE :search)";
    $params[':search'] = '%' . $searchQuery . '%';
}

$bookSql .= " ORDER BY b.RegDate DESC";

$bookQuery = $dbh->prepare($bookSql);
foreach($params as $k => $v) {
    $bookQuery->bindValue($k, $v, PDO::PARAM_STR);
}
$bookQuery->execute();
$books = $bookQuery->fetchAll(PDO::FETCH_OBJ);

// ── Resolve active filter labels for pills ─────────────────────
$activeCatName = '';
if($filterCat > 0) {
    foreach($categories as $c) {
        if($c->id == $filterCat) { $activeCatName = $c->CategoryName; break; }
    }
}
$activeAuthorName = '';
if($filterAuthor > 0) {
    foreach($authors as $a) {
        if($a->id == $filterAuthor) { $activeAuthorName = $a->AuthorName; break; }
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Lim Library | Dashboard</title>

    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue: #0000ff;
            --blue-dark: #0000cc;
            --blue-soft: #e8e8ff;
            --text: #1a1a2e;
            --text-muted: #6b6b80;
            --border: #e2e2ee;
            --bg: #f5f5fb;
            --white: #ffffff;
            --sidebar-w: 220px;
            --nav-h: 64px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── NAVBAR ───────────────────────────────── */
        .lim-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--nav-h);
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            z-index: 100;
            gap: 16px;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            min-width: 155px;
        }
        .nav-brand img { height: 36px; width: auto; }
        .nav-brand-text { line-height: 1.1; }
        .nav-brand-text span {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 12px;
            color: var(--text);
            letter-spacing: 0.5px;
        }
        .nav-brand-text small {
            font-size: 8px;
            letter-spacing: 2px;
            color: var(--text-muted);
            text-transform: uppercase;
        }
        .nav-search {
            position: relative;
            flex: 1;
            max-width: 340px;
        }
        .nav-search input {
            width: 100%;
            padding: 9px 80px 9px 38px;
            border: 1.5px solid var(--border);
            border-radius: 24px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            background: var(--bg);
            color: var(--text);
            transition: border 0.2s;
        }
        .nav-search input:focus { border-color: var(--blue); }
        .nav-search .srch-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
        }
        .nav-search button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--blue);
            border: none;
            border-radius: 20px;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 5px 14px;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
        }
        .nav-search button:hover { background: var(--blue-dark); }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 2px;
            margin-left: auto;
        }
        .nav-links a {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .nav-links a:hover, .nav-links a.active {
            background: var(--blue-soft);
            color: var(--blue);
            text-decoration: none;
        }
        .nav-links a i { font-size: 15px; }

        /* ── LAYOUT ───────────────────────────────── */
        .page-layout {
            display: flex;
            padding-top: var(--nav-h);
            min-height: 100vh;
        }

        /* ── SIDEBAR ──────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--white);
            border-right: 1px solid var(--border);
            padding: 24px 14px;
            position: sticky;
            top: var(--nav-h);
            height: calc(100vh - var(--nav-h));
            overflow-y: auto;
            flex-shrink: 0;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        .sidebar-title {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 18px;
        }
        .filter-section { margin-bottom: 24px; }
        .filter-section h4 {
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border);
        }
        .filter-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 6px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .filter-option:hover { background: var(--bg); }
        .filter-option input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--blue);
            cursor: pointer;
            flex-shrink: 0;
        }
        .filter-option label {
            font-size: 13px;
            color: var(--text);
            cursor: pointer;
            line-height: 1.3;
        }
        .btn-apply-filter {
            width: 100%;
            padding: 9px;
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            font-family: 'DM Sans', sans-serif;
            transition: background 0.2s;
        }
        .btn-apply-filter:hover { background: var(--blue-dark); }
        .btn-clear-filter {
            display: block;
            width: 100%;
            padding: 8px;
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
            margin-top: 6px;
            text-align: center;
            text-decoration: none;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.2s;
        }
        .btn-clear-filter:hover { border-color: var(--blue); color: var(--blue); text-decoration: none; }

        /* ── MAIN ─────────────────────────────────── */
        .main-content {
            flex: 1;
            padding: 24px 24px 40px;
            min-width: 0;
        }

        /* ── STATS ────────────────────────────────── */
        .stats-row {
            display: flex;
            gap: 14px;
            margin-bottom: 28px;
        }
        .stat-card {
            flex: 1;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .stat-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,255,0.08);
            transform: translateY(-2px);
            text-decoration: none;
        }
        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .stat-icon.blue  { background: var(--blue-soft); color: var(--blue); }
        .stat-icon.amber { background: #fff8e8; color: #c47e00; }
        .stat-icon.green { background: #e8f5e9; color: #2e7d32; }
        .stat-info h3 { font-size: 22px; font-weight: 600; color: var(--text); margin: 0; line-height: 1; }
        .stat-info p  { font-size: 12px; color: var(--text-muted); margin: 3px 0 0; }

        /* ── SECTION HEADER ───────────────────────── */
        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 14px;
        }
        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 20px; color: var(--text);
        }
        .result-count {
            font-size: 12px; color: var(--text-muted);
            background: var(--white); padding: 4px 12px;
            border-radius: 20px; border: 1px solid var(--border);
        }

        /* ── FILTER PILLS ─────────────────────────── */
        .active-filters { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
        .filter-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--blue-soft); color: var(--blue);
            font-size: 11px; font-weight: 600;
            padding: 4px 10px; border-radius: 20px;
        }
        .filter-pill a { color: var(--blue); text-decoration: none; font-size: 14px; line-height: 1; opacity: 0.7; }
        .filter-pill a:hover { opacity: 1; }

        /* ── BOOK GRID ────────────────────────────── */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
            gap: 16px;
        }
        .book-card {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: box-shadow 0.2s, transform 0.2s;
            text-decoration: none;
            display: block;
        }
        .book-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,255,0.10);
            transform: translateY(-3px);
            text-decoration: none;
        }
        .book-cover-wrap {
            width: 100%;
            aspect-ratio: 2/3;
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, var(--blue-soft) 0%, #d0d0f8 100%);
        }
        .book-cover-wrap img {
            width: 100%; height: 100%;
            object-fit: cover; display: block;
            transition: transform 0.3s;
        }
        .book-card:hover .book-cover-wrap img { transform: scale(1.04); }
        .book-cover-placeholder {
            width: 100%; height: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 8px; padding: 14px;
        }
        .book-cover-placeholder i { font-size: 30px; color: var(--blue); opacity: 0.4; }
        .book-cover-placeholder span {
            font-size: 11px; font-weight: 600; color: var(--blue);
            text-align: center; line-height: 1.3; opacity: 0.6;
        }
        .book-info { padding: 10px 10px 12px; }
        .book-title {
            font-size: 12px; font-weight: 600; color: var(--text);
            margin-bottom: 3px; line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .book-author {
            font-size: 11px; color: var(--text-muted); margin-bottom: 7px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .badge-row { display: flex; flex-wrap: wrap; gap: 4px; }
        .book-badge {
            display: inline-block; font-size: 9px; font-weight: 700;
            letter-spacing: 0.4px; padding: 2px 7px; border-radius: 20px; text-transform: uppercase;
        }
        .badge-available { background: #e8f5e9; color: #2e7d32; }
        .badge-reserved  { background: #fff3e0; color: #e65100; }
        .badge-cat       { background: var(--blue-soft); color: var(--blue); }
        .no-books {
            grid-column: 1 / -1; text-align: center;
            padding: 70px 20px; color: var(--text-muted);
        }
        .no-books i { font-size: 48px; margin-bottom: 14px; display: block; opacity: 0.25; }
        .no-books p { font-size: 14px; }
        .no-books a { color: var(--blue); }

        /* ── RESPONSIVE ───────────────────────────── */
        @media (max-width: 900px) { .sidebar { width: 190px; } }
        @media (max-width: 700px) {
            .sidebar { display: none; }
            .stats-row { flex-direction: column; gap: 10px; }
            .book-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
            .nav-brand-text { display: none; }
            .nav-links a span { display: none; }
        }
    </style>
</head>
<body>

<!-- ══ NAVBAR ══════════════════════════════════════════════════ -->
<nav class="lim-nav">
    <a href="dashboard.php" class="nav-brand">
        <img src="assets/img/limlibrary.png" alt="Lim Library" />
        <div class="nav-brand-text">
            <span>LIM LIBRARY</span>
            <small>Gain More Knowledge</small>
        </div>
    </a>

    <form method="get" action="dashboard.php" class="nav-search">
        <i class="fa fa-search srch-icon"></i>
        <input type="text" name="search" placeholder="Search books or authors…"
               value="<?php echo htmlentities($searchQuery); ?>" autocomplete="off" />
        <?php if($filterCat > 0)    echo '<input type="hidden" name="cat" value="'.(int)$filterCat.'">'; ?>
        <?php if($filterAuthor > 0) echo '<input type="hidden" name="author" value="'.(int)$filterAuthor.'">'; ?>
        <button type="submit">Search</button>
    </form>

    <div class="nav-links">
        <a href="dashboard.php" class="active"><i class="fa fa-home"></i><span> Home</span></a>
        <a href="listed-books.php"><i class="fa fa-book"></i><span> Browse</span></a>
        <a href="issued-books.php"><i class="fa fa-refresh"></i><span> Borrowing</span></a>
        <a href="user-profile.php"><i class="fa fa-user"></i><span> Profile</span></a>
        <a href="logout.php"><i class="fa fa-sign-out"></i><span> Logout</span></a>
    </div>
</nav>

<!-- ══ PAGE LAYOUT ═════════════════════════════════════════════ -->
<div class="page-layout">

    <!-- ── SIDEBAR ─────────────────────────────────────────── -->
    <aside class="sidebar">
        <p class="sidebar-title">Filters</p>

        <form method="get" action="dashboard.php" id="filterForm">
            <?php if($searchQuery != '') echo '<input type="hidden" name="search" value="'.htmlentities($searchQuery).'">'; ?>

            <div class="filter-section">
                <h4>Category</h4>
                <?php foreach($categories as $cat): ?>
                <div class="filter-option">
                    <input type="checkbox" name="cat"
                           id="cat_<?php echo $cat->id; ?>"
                           value="<?php echo $cat->id; ?>"
                           <?php echo ($filterCat == $cat->id) ? 'checked' : ''; ?>
                           onchange="singleCheck(this,'cat')" />
                    <label for="cat_<?php echo $cat->id; ?>"><?php echo htmlentities($cat->CategoryName); ?></label>
                </div>
                <?php endforeach; ?>
                <?php if(empty($categories)): ?>
                <p style="font-size:12px;color:var(--text-muted);padding:4px 0;">No categories found.</p>
                <?php endif; ?>
            </div>

            <div class="filter-section">
                <h4>Author</h4>
                <?php foreach($authors as $auth): ?>
                <div class="filter-option">
                    <input type="checkbox" name="author"
                           id="auth_<?php echo $auth->id; ?>"
                           value="<?php echo $auth->id; ?>"
                           <?php echo ($filterAuthor == $auth->id) ? 'checked' : ''; ?>
                           onchange="singleCheck(this,'author')" />
                    <label for="auth_<?php echo $auth->id; ?>"><?php echo htmlentities(trim($auth->AuthorName)); ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-apply-filter">Apply Filters</button>
            <a href="dashboard.php<?php echo $searchQuery != '' ? '?search='.urlencode($searchQuery) : ''; ?>"
               class="btn-clear-filter">Clear Filters</a>
        </form>
    </aside>

    <!-- ── MAIN ────────────────────────────────────────────── -->
    <main class="main-content">

        <!-- Stats -->
        <div class="stats-row">
            <a href="listed-books.php" class="stat-card">
                <div class="stat-icon blue"><i class="fa fa-book"></i></div>
                <div class="stat-info">
                    <h3><?php echo (int)$listdbooks; ?></h3>
                    <p>Books Listed</p>
                </div>
            </a>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fa fa-clock-o"></i></div>
                <div class="stat-info">
                    <h3><?php echo (int)$returnedbooks; ?></h3>
                    <p>Not Returned Yet</p>
                </div>
            </div>
            <a href="issued-books.php" class="stat-card">
                <div class="stat-icon green"><i class="fa fa-check-circle-o"></i></div>
                <div class="stat-info">
                    <h3><?php echo (int)$totalissuedbook; ?></h3>
                    <p>Total Issued Books</p>
                </div>
            </a>
        </div>

        <!-- Section header -->
        <div class="section-header">
            <h2>
                <?php
                if($searchQuery != '')           echo 'Results for &ldquo;'.htmlentities($searchQuery).'&rdquo;';
                elseif($filterCat > 0 || $filterAuthor > 0) echo 'Filtered Books';
                else                             echo 'Browse Books';
                ?>
            </h2>
            <span class="result-count"><?php echo count($books); ?> book<?php echo count($books) != 1 ? 's' : ''; ?></span>
        </div>

        <!-- Active filter pills -->
        <?php if($filterCat > 0 || $filterAuthor > 0 || $searchQuery != ''): ?>
        <div class="active-filters">
            <?php if($filterCat > 0): ?>
            <span class="filter-pill">
                <i class="fa fa-tag" style="font-size:10px;"></i>
                <?php echo htmlentities($activeCatName); ?>
                <a href="dashboard.php?<?php
                    echo $filterAuthor > 0 ? 'author='.$filterAuthor.'&' : '';
                    echo $searchQuery != '' ? 'search='.urlencode($searchQuery) : '';
                ?>">&times;</a>
            </span>
            <?php endif; ?>
            <?php if($filterAuthor > 0): ?>
            <span class="filter-pill">
                <i class="fa fa-user" style="font-size:10px;"></i>
                <?php echo htmlentities(trim($activeAuthorName)); ?>
                <a href="dashboard.php?<?php
                    echo $filterCat > 0 ? 'cat='.$filterCat.'&' : '';
                    echo $searchQuery != '' ? 'search='.urlencode($searchQuery) : '';
                ?>">&times;</a>
            </span>
            <?php endif; ?>
            <?php if($searchQuery != ''): ?>
            <span class="filter-pill">
                <i class="fa fa-search" style="font-size:10px;"></i>
                &ldquo;<?php echo htmlentities($searchQuery); ?>&rdquo;
                <a href="dashboard.php?<?php
                    echo $filterCat > 0    ? 'cat='.$filterCat.'&' : '';
                    echo $filterAuthor > 0 ? 'author='.$filterAuthor : '';
                ?>">&times;</a>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Book Grid -->
        <div class="book-grid">
            <?php if(empty($books)): ?>
            <div class="no-books">
                <i class="fa fa-book"></i>
                <p>No books found. <a href="dashboard.php">Clear all filters</a></p>
            </div>
            <?php else: ?>
            <?php foreach($books as $book):
                // Available if remaining qty > 0
                $remaining = max(0, (int)$book->bookQty - (int)$book->issuedCount);
                $available = $remaining > 0;
                // Book cover image — stored in admin/assets/img/bookImages or assets/img/bookImages
                $imgSrc = 'admin/bookimg/' . $book->bookImage;
            ?>
            <a href="bookdetail.php?bookid=<?php echo $book->id; ?>" class="book-card">
                <div class="book-cover-wrap">
                    <?php if(!empty($book->bookImage)): ?>
                    <img src="<?php echo htmlentities($imgSrc); ?>"
                         alt="<?php echo htmlentities($book->BookName); ?>"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" />
                    <div class="book-cover-placeholder" style="display:none;">
                        <i class="fa fa-book"></i>
                        <span><?php echo htmlentities($book->BookName); ?></span>
                    </div>
                    <?php else: ?>
                    <div class="book-cover-placeholder">
                        <i class="fa fa-book"></i>
                        <span><?php echo htmlentities($book->BookName); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="book-info">
                    <div class="book-title"><?php echo htmlentities($book->BookName); ?></div>
                    <div class="book-author"><?php echo htmlentities(trim($book->AuthorName ?? '')); ?></div>
                    <div class="badge-row">
                        <span class="book-badge <?php echo $available ? 'badge-available' : 'badge-reserved'; ?>">
                            <?php echo $available ? 'Available' : 'Reserved'; ?>
                        </span>
                        <?php if(!empty($book->CategoryName)): ?>
                        <span class="book-badge badge-cat"><?php echo htmlentities($book->CategoryName); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script>
// Radio-like behaviour: only one checkbox active per filter group
function singleCheck(clicked, groupName) {
    document.querySelectorAll('input[name="' + groupName + '"]').forEach(function(cb) {
        if (cb !== clicked) cb.checked = false;
    });
}
</script>
</body>
</html>
<?php  ?>