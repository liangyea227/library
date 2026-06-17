<?php
// header.php  -  Shared navigation bar for all user-facing pages
// Includes:
//   - Text search bar (original)
//   - Camera button  (NEW: opens image search modal)
//   - Image search modal + JavaScript (NEW)
?>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
<style>
    /* ── CSS variables (shared across all pages) ─────────────────── */
    :root {
        --blue:       #0000ff;
        --blue-dark:  #0000cc;
        --blue-soft:  #e8e8ff;
        --text:       #1a1a2e;
        --text-muted: #6b6b80;
        --border:     #e2e2ee;
        --bg:         #f5f5fb;
        --white:      #ffffff;
        --sidebar-w:  220px;
        --nav-h:      64px;
    }

    /* ── Navigation bar ─────────────────────────────────────────── */
    .lim-nav {
        position: fixed;
        top: 0; left: 0; right: 0;
        height: var(--nav-h);
        background: var(--white);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        padding: 0 24px;
        z-index: 1000;
        gap: 16px;
    }

    /* Brand / logo */
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

    /* Search group: text pill + camera button side by side */
    .nav-search-group {
        display: flex;
        align-items: center;
        gap: 6px;
        flex: 1;
        max-width: 420px;
    }
    .nav-search { position: relative; flex: 1; }
    .nav-search input {
        width: 100%;
        padding: 9px 78px 9px 38px;
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
        left: 13px; top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 14px;
    }
    .nav-search button.txt-search-btn {
        position: absolute;
        right: 5px; top: 50%;
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
    .nav-search button.txt-search-btn:hover { background: var(--blue-dark); }

    /* Camera button */
    .img-search-btn {
        flex-shrink: 0;
        width: 38px; height: 38px;
        border-radius: 50%;
        background: var(--blue-soft);
        border: 1.5px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--blue);
        font-size: 15px;
        transition: background 0.2s, border-color 0.2s;
    }
    .img-search-btn:hover {
        background: var(--blue);
        color: #fff;
        border-color: var(--blue);
    }

    /* Nav links */
    .nav-links { display: flex; align-items: center; gap: 2px; margin-left: auto; }
    .nav-links a {
        display: flex; align-items: center; gap: 6px;
        padding: 8px 12px; border-radius: 8px;
        font-size: 13px; font-weight: 500;
        color: var(--text-muted);
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
        font-family: 'DM Sans', sans-serif;
    }
    .nav-links a:hover,
    .nav-links a.active {
        background: var(--blue-soft);
        color: var(--blue);
        text-decoration: none;
    }
    .nav-links a i { font-size: 15px; }

    /* ── Image search overlay modal ─────────────────────────────── */
    #imgSearchOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    #imgSearchOverlay.active { display: flex; }

    .ims-card {
        background: var(--white);
        border-radius: 18px;
        padding: 32px 36px 28px;
        width: 100%;
        max-width: 460px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
        font-family: 'DM Sans', sans-serif;
        position: relative;
    }
    .ims-close {
        position: absolute;
        top: 14px; right: 18px;
        font-size: 22px;
        cursor: pointer;
        color: var(--text-muted);
        background: none;
        border: none;
        line-height: 1;
    }
    .ims-close:hover { color: var(--text); }
    .ims-title { font-size: 17px; font-weight: 700; color: var(--text); margin: 0 0 4px; }
    .ims-sub   { font-size: 12px; color: var(--text-muted); margin: 0 0 20px; }

    /* Drop zone */
    .ims-drop {
        border: 2px dashed var(--border);
        border-radius: 12px;
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
        position: relative;
        background: var(--bg);
    }
    .ims-drop.drag-over { border-color: var(--blue); background: var(--blue-soft); }
    .ims-drop input[type="file"] {
        position: absolute; inset: 0;
        opacity: 0; cursor: pointer;
        width: 100%; height: 100%;
    }
    .ims-drop i { font-size: 36px; color: var(--blue); margin-bottom: 10px; display: block; }
    .ims-drop p { margin: 0; font-size: 13px; color: var(--text-muted); }
    .ims-drop p strong { color: var(--text); }

    /* Image preview */
    #imsPreviewWrap { display: none; margin-top: 16px; text-align: center; }
    #imsPreview {
        max-height: 130px; max-width: 100%;
        border-radius: 8px;
        border: 1.5px solid var(--border);
        object-fit: contain;
    }
    #imsFileName { font-size: 11px; color: var(--text-muted); margin-top: 6px; }

    /* Action buttons */
    .ims-actions { display: flex; gap: 10px; margin-top: 20px; }
    .ims-actions button {
        flex: 1; padding: 10px;
        border-radius: 24px;
        font-size: 13px; font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer; border: none;
        transition: opacity 0.2s;
    }
    #imsSearchBtn { background: var(--blue); color: #fff; }
    #imsSearchBtn:hover    { background: var(--blue-dark); }
    #imsSearchBtn:disabled { opacity: 0.5; cursor: not-allowed; }
    #imsCancelBtn {
        background: var(--bg);
        border: 1.5px solid var(--border) !important;
        color: var(--text-muted);
    }

    /* Spinner */
    .ims-spinner {
        display: none;
        text-align: center;
        padding: 10px 0 0;
        font-size: 13px;
        color: var(--text-muted);
    }
    .ims-spinner i { animation: ims-spin 1s linear infinite; }
    @keyframes ims-spin { to { transform: rotate(360deg); } }

    /* Result display */
    #imsResult { margin-top: 16px; }
    .ims-match-card {
        display: flex; gap: 14px; align-items: flex-start;
        background: var(--bg);
        border-radius: 12px; padding: 14px;
        border: 1.5px solid var(--border);
        text-decoration: none;
    }
    .ims-match-card:hover { border-color: var(--blue); background: var(--blue-soft); }
    .ims-match-card img {
        width: 60px; height: 80px;
        object-fit: cover; border-radius: 6px;
        flex-shrink: 0; border: 1px solid var(--border);
    }
    .ims-match-info { flex: 1; min-width: 0; }
    .ims-match-info h4 {
        font-size: 14px; font-weight: 700; color: var(--text);
        margin: 0 0 4px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .ims-match-info p { font-size: 12px; color: var(--text-muted); margin: 0 0 2px; }
    .ims-badge {
        display: inline-block;
        margin-top: 6px; margin-right: 4px;
        font-size: 11px; font-weight: 600;
        padding: 2px 10px; border-radius: 20px;
    }
    .ims-no-match {
        text-align: center; padding: 14px;
        background: #fff3cd; border-radius: 10px;
        font-size: 13px; color: #856404;
        border: 1px solid #ffc107;
    }
    .ims-error {
        text-align: center; padding: 14px;
        background: #f8d7da; border-radius: 10px;
        font-size: 13px; color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .ims-go-btn {
        display: block; margin-top: 10px; text-align: center;
        background: var(--blue); color: #fff;
        border-radius: 24px; padding: 9px 0;
        font-weight: 600; font-size: 13px;
        text-decoration: none;
        font-family: 'DM Sans', sans-serif;
    }
    .ims-go-btn:hover { background: var(--blue-dark); color: #fff; }

    /* ── Responsive ─────────────────────────────────────────────── */
    @media (max-width: 700px) {
        .nav-brand-text  { display: none; }
        .nav-links a span { display: none; }
        .nav-search-group { max-width: 200px; }
    }
</style>

<!-- ═══════════════════════════════════════════════════════════
     IMAGE SEARCH MODAL
     Opens when the camera button in the nav is clicked.
     Calls the Python API at localhost:5001/search-by-image
════════════════════════════════════════════════════════════ -->
<div id="imgSearchOverlay">
    <div class="ims-card">
        <button class="ims-close" onclick="closeImgSearch()" title="Close">&times;</button>
        <p class="ims-title"><i class="fa fa-camera"></i> Search by Book Cover</p>
        <p class="ims-sub">Upload a photo of a book cover and we will find it in the library.</p>

        <!-- Drop zone -->
        <div class="ims-drop" id="imsDrop">
            <input type="file" id="imsFileInput" accept="image/*"
                   onchange="handleImgFile(this.files[0])">
            <i class="fa fa-cloud-upload"></i>
            <p><strong>Click to upload</strong> or drag and drop here</p>
            <p>JPG, PNG, WEBP &mdash; max 8 MB</p>
        </div>

        <!-- Preview -->
        <div id="imsPreviewWrap">
            <img id="imsPreview" src="" alt="Preview">
            <p id="imsFileName"></p>
        </div>

        <!-- Result area -->
        <div id="imsResult"></div>

        <!-- Loading spinner -->
        <div class="ims-spinner" id="imsSpinner">
            <i class="fa fa-spinner"></i>&nbsp; Analysing cover image&hellip;
        </div>

        <!-- Buttons -->
        <div class="ims-actions">
            <button id="imsSearchBtn" onclick="runImageSearch()" disabled>
                <i class="fa fa-search"></i> Find Book
            </button>
            <button id="imsCancelBtn" onclick="closeImgSearch()">Cancel</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     NAVIGATION BAR
════════════════════════════════════════════════════════════ -->
<nav class="lim-nav">
    <!-- Logo / brand -->
    <a href="dashboard.php" class="nav-brand">
        <img src="assets/img/limlibrary.png" alt="Lim Library" />
        <div class="nav-brand-text">
            <span>LIM LIBRARY</span>
            <small>Gain More Knowledge</small>
        </div>
    </a>

    <?php if (!empty($_SESSION['login'])): ?>
    <!-- Search group (text search + camera button) - logged-in users only -->
    <div class="nav-search-group">
        <form method="get" action="dashboard.php" class="nav-search">
            <i class="fa fa-search srch-icon"></i>
            <input type="text" name="search"
                   placeholder="Search books or authors&hellip;"
                   value="<?php echo isset($_GET['search']) ? htmlentities(trim($_GET['search'])) : ''; ?>"
                   autocomplete="off" />
            <?php if (!empty($_GET['cat']))    echo '<input type="hidden" name="cat"    value="' . (int)$_GET['cat']    . '">'; ?>
            <?php if (!empty($_GET['author'])) echo '<input type="hidden" name="author" value="' . (int)$_GET['author'] . '">'; ?>
            <button type="submit" class="txt-search-btn">Search</button>
        </form>

        <!-- Camera button: opens image search modal -->
        <button class="img-search-btn" onclick="openImgSearch()"
                title="Search by book cover photo">
            <i class="fa fa-camera"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- Navigation links -->
    <div class="nav-links">
        <?php if (!empty($_SESSION['login'])): ?>
            <a href="dashboard.php"
               <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php'    ? 'class="active"' : ''; ?>>
               <i class="fa fa-home"></i><span> Home</span></a>
            <a href="borrowed-books.php"
               <?php echo basename($_SERVER['PHP_SELF']) == 'borrowed-books.php' ? 'class="active"' : ''; ?>>
               <i class="fa fa-refresh"></i><span> Borrowing</span></a>
            <a href="my-profile.php"
               <?php echo basename($_SERVER['PHP_SELF']) == 'my-profile.php'   ? 'class="active"' : ''; ?>>
               <i class="fa fa-user"></i><span> Profile</span></a>
            <a href="logout.php"><i class="fa fa-sign-out"></i><span> Logout</span></a>
        <?php else: ?>
            <a href="index.php"
               <?php echo basename($_SERVER['PHP_SELF']) == 'index.php'  ? 'class="active"' : ''; ?>>
               <i class="fa fa-home"></i><span> Home</span></a>
            <a href="index.php#ulogin"><i class="fa fa-sign-in"></i><span> Login</span></a>
            <a href="signup.php"
               <?php echo basename($_SERVER['PHP_SELF']) == 'signup.php' ? 'class="active"' : ''; ?>>
               <i class="fa fa-user-plus"></i><span> Sign Up</span></a>
        <?php endif; ?>
    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════
     IMAGE SEARCH JAVASCRIPT
════════════════════════════════════════════════════════════ -->
<script>
// URL of the Python image search API
var IMG_SEARCH_API  = 'http://localhost:5001/search-by-image';
// Base path for book cover images
var BOOK_IMG_BASE   = 'admin/bookimg/';
// Book detail page base URL
var BOOK_DETAIL_URL = 'bookdetail.php?bookid=';

var selectedFile = null;

// Open modal and reset state
function openImgSearch() {
    document.getElementById('imgSearchOverlay').classList.add('active');
    resetImgSearch();
}

// Close modal
function closeImgSearch() {
    document.getElementById('imgSearchOverlay').classList.remove('active');
}

// Close modal when clicking outside the card
document.getElementById('imgSearchOverlay').addEventListener('click', function (e) {
    if (e.target === this) closeImgSearch();
});

// Reset all modal state
function resetImgSearch() {
    selectedFile = null;
    document.getElementById('imsFileInput').value        = '';
    document.getElementById('imsPreviewWrap').style.display = 'none';
    document.getElementById('imsPreview').src            = '';
    document.getElementById('imsFileName').textContent   = '';
    document.getElementById('imsResult').innerHTML       = '';
    document.getElementById('imsSpinner').style.display  = 'none';
    document.getElementById('imsSearchBtn').disabled     = true;
}

// Handle a selected file: validate, store, show preview
function handleImgFile(file) {
    if (!file) return;
    if (file.size > 8 * 1024 * 1024) {
        alert('File is too large. Please use an image under 8 MB.');
        return;
    }
    selectedFile = file;
    document.getElementById('imsSearchBtn').disabled = false;
    document.getElementById('imsResult').innerHTML   = '';

    var reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('imsPreview').src          = e.target.result;
        document.getElementById('imsPreviewWrap').style.display = 'block';
        document.getElementById('imsFileName').textContent = file.name;
    };
    reader.readAsDataURL(file);
}

// Drag-and-drop support
(function () {
    var drop = document.getElementById('imsDrop');
    drop.addEventListener('dragover',  function (e) { e.preventDefault(); drop.classList.add('drag-over'); });
    drop.addEventListener('dragleave', function ()  { drop.classList.remove('drag-over'); });
    drop.addEventListener('drop', function (e) {
        e.preventDefault();
        drop.classList.remove('drag-over');
        if (e.dataTransfer.files.length) handleImgFile(e.dataTransfer.files[0]);
    });
}());

// Send the image to the Python API and display result
function runImageSearch() {
    if (!selectedFile) return;

    document.getElementById('imsResult').innerHTML      = '';
    document.getElementById('imsSpinner').style.display = 'block';
    document.getElementById('imsSearchBtn').disabled    = true;

    var fd = new FormData();
    fd.append('image', selectedFile);

    fetch(IMG_SEARCH_API, { method: 'POST', body: fd })
        .then(function (r) {
            if (!r.ok) throw new Error('Server error: HTTP ' + r.status);
            return r.json();
        })
        .then(renderImgResult)
        .catch(function (err) {
            document.getElementById('imsResult').innerHTML =
                '<div class="ims-error">' +
                '<i class="fa fa-exclamation-triangle"></i> ' +
                'Could not reach the image search service. ' +
                'Make sure <strong>start_ai.py</strong> is running.<br>' +
                '<small style="opacity:.7">' + err.message + '</small></div>';
        })
        .finally(function () {
            document.getElementById('imsSpinner').style.display = 'none';
            document.getElementById('imsSearchBtn').disabled    = false;
        });
}

// Render the API response inside the modal
function renderImgResult(data) {
    var el = document.getElementById('imsResult');

    if (!data.success) {
        el.innerHTML =
            '<div class="ims-no-match">' +
            '<i class="fa fa-search"></i> ' + escHtml(data.message) + '</div>';
        return;
    }

    var b          = data.book;
    var pct        = Math.round(data.confidence * 100);
    var isBestGuess = data.is_best_guess || false;
    var matchLabel  = data.match_label   || '';
    var avail      = (parseInt(b.bookQty) > 0 && b.isIssued != 1) ? 'Available' : 'Not Available';
    var aColor     = avail === 'Available' ? '#155724' : '#721c24';
    var aBg        = avail === 'Available' ? '#d4edda'  : '#f8d7da';
    var href       = BOOK_DETAIL_URL + encodeURIComponent(b.id);

    // Confidence badge colour: green >= 60%, orange >= 50%, red below
    var confBg, confColor;
    if (pct >= 60) { confBg = '#d4edda'; confColor = '#155724'; }
    else if (pct >= 50) { confBg = '#fff3cd'; confColor = '#856404'; }
    else { confBg = '#f8d7da'; confColor = '#721c24'; }

    // Best-guess warning banner shown for partial/cropped images
    var warningBanner = '';
    if (isBestGuess) {
        warningBanner =
            '<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;' +
            'padding:8px 12px;margin-bottom:10px;font-size:12px;color:#856404;">' +
            '<i class="fa fa-exclamation-triangle"></i> ' +
            '<strong>' + escHtml(matchLabel) + '</strong> &mdash; ' +
            'The image may be cropped or partially visible. This is the closest match found. ' +
            'Try uploading a clearer or more complete cover for a better result.' +
            '</div>';
    }

    el.innerHTML =
        warningBanner +
        '<a class="ims-match-card" href="' + href + '">' +
            '<img src="' + BOOK_IMG_BASE + escHtml(b.bookImage) + '" alt="cover"' +
                ' onerror="this.src=\'assets/img/limlibrary.png\'">' +
            '<div class="ims-match-info">' +
                '<h4>' + escHtml(b.BookName) + '</h4>' +
                '<p><i class="fa fa-user"    style="width:14px"></i> ' + escHtml(b.AuthorName)   + '</p>' +
                '<p><i class="fa fa-tag"     style="width:14px"></i> ' + escHtml(b.CategoryName) + '</p>' +
                '<p><i class="fa fa-barcode" style="width:14px"></i> ISBN: ' + escHtml(b.ISBNNumber) + '</p>' +
                '<span class="ims-badge" style="background:' + aBg + ';color:' + aColor + '">' + avail + '</span>' +
                '<span class="ims-badge" style="background:' + confBg + ';color:' + confColor + '">' +
                    '<i class="fa fa-bar-chart"></i> ' + pct + '% &mdash; ' + escHtml(matchLabel) +
                '</span>' +
            '</div>' +
        '</a>' +
        '<a class="ims-go-btn" href="' + href + '">' +
            '<i class="fa fa-book"></i> View Book Details' +
        '</a>';
}

// HTML escape helper (prevents XSS)
function escHtml(s) {
    if (s == null) return '';
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>