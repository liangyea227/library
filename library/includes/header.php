<?php

?>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
<style>
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
        font-family: 'DM Sans', sans-serif;
    }
    .nav-links a:hover, .nav-links a.active {
        background: var(--blue-soft);
        color: var(--blue);
        text-decoration: none;
    }
    .nav-links a i { font-size: 15px; }

    @media (max-width: 700px) {
        .nav-brand-text { display: none; }
        .nav-links a span { display: none; }
        .nav-search { max-width: 160px; }
    }
</style>

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
               value="<?php echo isset($_GET['search']) ? htmlentities(trim($_GET['search'])) : ''; ?>"
               autocomplete="off" />
        <?php if(!empty($_GET['cat']))    echo '<input type="hidden" name="cat" value="'.(int)$_GET['cat'].'">'; ?>
        <?php if(!empty($_GET['author'])) echo '<input type="hidden" name="author" value="'.(int)$_GET['author'].'">'; ?>
        <button type="submit">Search</button>
    </form>

    <div class="nav-links">
        <?php if(!empty($_SESSION['login'])): ?>
            <a href="dashboard.php"    <?php echo basename($_SERVER['PHP_SELF'])=='dashboard.php'    ? 'class="active"' : ''; ?>><i class="fa fa-home"></i><span> Home</span></a>
            <a href="borrowed-books.php" <?php echo basename($_SERVER['PHP_SELF'])=='borrowed-books.php' ? 'class="active"' : ''; ?>><i class="fa fa-refresh"></i><span> Borrowing</span></a>
            <a href="my-profile.php"   <?php echo basename($_SERVER['PHP_SELF'])=='my-profile.php'   ? 'class="active"' : ''; ?>><i class="fa fa-user"></i><span> Profile</span></a>
            <a href="logout.php"><i class="fa fa-sign-out"></i><span> Logout</span></a>
        <?php else: ?>
            <a href="index.php"  <?php echo basename($_SERVER['PHP_SELF'])=='index.php'  ? 'class="active"' : ''; ?>><i class="fa fa-home"></i><span> Home</span></a>
            <a href="index.php#ulogin"><i class="fa fa-sign-in"></i><span> Login</span></a>
            <a href="signup.php" <?php echo basename($_SERVER['PHP_SELF'])=='signup.php' ? 'class="active"' : ''; ?>><i class="fa fa-user-plus"></i><span> Sign Up</span></a>
        <?php endif; ?>
    </div>
</nav>