<?php 
// DB credentials.
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','library');

// Mail credentials (set these to your real SMTP credentials)
// These are used by bookdetail.php, user-forgot-password.php, send_overdue_warnings.php
define('MAIL_USER', 'liangyuel44@gmail.com');
define('MAIL_PASS', 'wsjy kkfr gdps qxmq');

// Establish database connection.
try
{
    $dbh = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
    );
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
    // FIX: Do not expose raw DB error to browser — log it instead
    error_log("Database connection error: " . $e->getMessage());
    exit("Service temporarily unavailable. Please try again later.");
}
?>
