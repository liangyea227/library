<!-- ── TOPBAR ── -->
<header class="topbar">
  <div class="topbar-brand">
    <img src="../assets/img/limlibrary.png" class="brand-logo" alt="Lim Library Logo" width="30" height="30"/>
    <div class="brand-divider"></div>
    <div class="brand-text-block">
      <span class="brand-name">LIM LIBRARY</span>
      <small class="brand-tagline">Gain More Knowledge</small>
    </div>
  </div>

  <div class="topbar-right">
    <div class="topbar-admin-badge">
      <div class="admin-avatar">A</div>
      <div class="admin-info">
        <span class="admin-name">Administrator</span>
        <span class="admin-role">System Admin</span>
      </div>
    </div>
    <a href="logout.php" class="btn-logout">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor" style="flex-shrink:0">
        <path d="M10 2H4a1 1 0 00-1 1v10a1 1 0 001 1h6v-2H5V4h5V2zm4 6l-3-3v2H7v2h4v2l3-3z"/>
      </svg>
      Log Out
    </a>
  </div>
</header>

<!-- ── NAVBAR ── -->
<nav class="navbar">
  <button class="nav-toggle" onclick="toggleNav()" aria-label="Toggle menu">
    <span></span><span></span><span></span>
  </button>

  <div class="navbar-inner" id="mainNav">

    <div class="nav-item">
      <a href="dashboard.php" class="nav-link">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M1 1h6v6H1zm8 0h6v6H9zM1 9h6v6H1zm8 0h6v6H9z"/></svg>
        Dashboard
      </a>
    </div>

    <div class="nav-item">
      <span class="nav-link">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><rect x="1" y="2" width="14" height="2" rx="1"/><rect x="1" y="7" width="14" height="2" rx="1"/><rect x="1" y="12" width="8" height="2" rx="1"/></svg>
        Categories
        <svg class="nav-arrow-icon" width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><path d="M2 3l3 3 3-3" stroke="currentColor" stroke-width="1.2" fill="none" stroke-linecap="round"/></svg>
      </span>
      <ul class="dropdown-menu">
        <li><a href="add-category.php"><svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1v14M1 8h14"/></svg> Add Category</a></li>
        <li><a href="manage-categories.php"><svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M1 4h14M1 8h10M1 12h7"/></svg> Manage Categories</a></li>
      </ul>
    </div>

    <div class="nav-item">
      <span class="nav-link">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/></svg>
        Authors
        <svg class="nav-arrow-icon" width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><path d="M2 3l3 3 3-3" stroke="currentColor" stroke-width="1.2" fill="none" stroke-linecap="round"/></svg>
      </span>
      <ul class="dropdown-menu">
        <li><a href="add-author.php"><svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1v14M1 8h14"/></svg> Add Author</a></li>
        <li><a href="manage-authors.php"><svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M1 4h14M1 8h10M1 12h7"/></svg> Manage Authors</a></li>
      </ul>
    </div>

    <div class="nav-item">
      <span class="nav-link">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2 1h9l3 3v11H2V1zm2 2v10h8V5h-2V3H4zm5 5H5v1h6V9H7z"/></svg>
        Books
        <svg class="nav-arrow-icon" width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><path d="M2 3l3 3 3-3" stroke="currentColor" stroke-width="1.2" fill="none" stroke-linecap="round"/></svg>
      </span>
      <ul class="dropdown-menu">
        <li><a href="add-book.php"><svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1v14M1 8h14"/></svg> Add Book</a></li>
        <li><a href="manage-books.php"><svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M1 4h14M1 8h10M1 12h7"/></svg> Manage Books</a></li>
      </ul>
    </div>

    <div class="nav-item">
      <span class="nav-link">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13 2H3a1 1 0 00-1 1v11l3-2 2 2 2-2 2 2 3 2V3a1 1 0 00-1-1zM5 9H4V8h1v1zm2 0H6V8h1v1zm2 0H8V8h1v1zm0-3H4V5h5v1z"/></svg>
        Issue Books
        <svg class="nav-arrow-icon" width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><path d="M2 3l3 3 3-3" stroke="currentColor" stroke-width="1.2" fill="none" stroke-linecap="round"/></svg>
      </span>
      <ul class="dropdown-menu">
        <li><a href="issue-book.php"><svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1v14M1 8h14"/></svg> Issue New Book</a></li>
        <li><a href="manage-issued-books.php"><svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M1 4h14M1 8h10M1 12h7"/></svg> Manage Issued Books</a></li>
        <li><a href="overdue-books.php" style="color:#c0533a;"><svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a7 7 0 100 14A7 7 0 008 1zm0 2v5l3 2-1 1.5L7 9.5V3h1z"/></svg> ⚠️ Overdue Books</a></li>
      </ul>
    </div>

    <div class="nav-item">
      <a href="reg-students.php" class="nav-link">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 1L1 4l7 3 7-3-7-3zM1 8l7 3 7-3M1 12l7 3 7-3"/></svg>
        Students
      </a>
    </div>

    <div class="nav-item">
      <a href="change-password.php" class="nav-link">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><rect x="3" y="7" width="10" height="8" rx="1"/><path d="M5 7V5a3 3 0 016 0v2"/></svg>
        Password
      </a>
    </div>

  </div>
</nav>

<script>
function toggleNav() {
  var n = document.getElementById('mainNav');
  var btn = document.querySelector('.nav-toggle');
  n.classList.toggle('open');
  btn.classList.toggle('open');
}
</script>