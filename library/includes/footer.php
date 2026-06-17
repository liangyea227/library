<footer class="lim-footer">
    <div class="lim-footer-inner">
        <div class="lim-footer-brand">
            <div class="lim-footer-logo-wrap">
                <span class="lim-footer-logo-icon"><i class="fa fa-book"></i></span>
            </div>
            <div>
                <div class="lim-footer-name">LIM Library</div>
                <div class="lim-footer-tagline">Management System</div>
            </div>
        </div>

        <div class="lim-footer-links">
            <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
            <a href="listed-books.php"><i class="fa fa-book"></i> Books</a>
            <a href="borrowed-books.php"><i class="fa fa-bookmark"></i> My Loans</a>
            <a href="my-profile.php"><i class="fa fa-user"></i> Profile</a>
        </div>

        <div class="lim-footer-copy">
            &copy; <?php echo date('Y'); ?> LIM Library &mdash; All rights reserved
        </div>
    </div>
</footer>

<style>
.lim-footer {
    background: var(--text);
    color: #fff;
    margin-top: auto;
}

.lim-footer-inner {
    max-width: 1000px;
    margin: 0 auto;
    padding: 32px 24px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

.lim-footer-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.lim-footer-logo-wrap {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: var(--blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fff;
    flex-shrink: 0;
}

.lim-footer-name {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 600;
    color: #fff;
    line-height: 1.1;
}

.lim-footer-tagline {
    font-size: 10px;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.45);
    margin-top: 2px;
}

.lim-footer-links {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: center;
}

.lim-footer-links a {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 500;
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    padding: 6px 14px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.12);
    transition: background 0.18s, color 0.18s;
}

.lim-footer-links a:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    text-decoration: none;
}

.lim-footer-links a i {
    font-size: 11px;
}

.lim-footer-divider {
    width: 100%;
    border: none;
    border-top: 1px solid rgba(255,255,255,0.1);
    margin: 0;
}

.lim-footer-copy {
    font-size: 11px;
    color: rgba(255,255,255,0.35);
    letter-spacing: 0.3px;
    padding-top: 4px;
}

@media (max-width: 600px) {
    .lim-footer-inner { padding: 24px 16px 20px; gap: 16px; }
    .lim-footer-name  { font-size: 16px; }
}
</style>
