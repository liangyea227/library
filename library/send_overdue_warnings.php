<?php
/**
 * Run once daily via cron to email students with overdue books.
 *
 * Cron example (every day at 8 AM):
 *   0 8 * * * php /var/www/html/library/send_overdue_warnings.php
 *
 * Or run manually from terminal:
 *   php send_overdue_warnings.php
 */

define('OVERDUE_DAYS', 7);
// Deduplication: store last-emailed date per issue_id in a log file
// This prevents sending multiple emails per day if the cron runs more than once
define('WARN_LOG', __DIR__ . '/overdue_warn_log.json');

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$sql = "
    SELECT
        i.id AS issue_id,
        i.IssuesDate,
        DATEDIFF(NOW(), i.IssuesDate) AS days_held,
        s.FullName,
        s.EmailId,
        b.BookName,
        b.ISBNNumber
    FROM tblissuedbookdetails i
    JOIN tblstudents s ON s.StudentId = i.StudentID
    JOIN tblbooks    b ON b.id        = i.BookId
    WHERE
        (i.RetrunStatus = 0 OR i.RetrunStatus IS NULL OR i.RetrunStatus = '')
        AND i.ReturnDate IS NULL
        AND DATEDIFF(NOW(), i.IssuesDate) >= :days
    ORDER BY days_held DESC
";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':days', OVERDUE_DAYS, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_OBJ);

echo "[" . date('Y-m-d H:i:s') . "] Found " . count($rows) . " overdue record(s).\n";

foreach ($rows as $row) {

    if (empty($row->EmailId)) {
        echo "  SKIP  Issue #{$row->issue_id} — no email on file.\n";
        continue;
    }

    $daysBorrowed = (int)$row->days_held;
    $borrowDate   = date('d M Y', strtotime($row->IssuesDate));
    $dueDate      = date('d M Y', strtotime($row->IssuesDate . ' +' . OVERDUE_DAYS . ' days'));

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($mail->Username, 'Lim Library');
        $mail->addAddress($row->EmailId, $row->FullName);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = '⚠️ Return Reminder: "' . $row->BookName . '" is overdue';
        $mail->Body = '
        <div style="font-family:DM Sans,Arial,sans-serif;max-width:480px;margin:0 auto;padding:32px 24px;background:#f5f5fb;border-radius:16px;">
          <div style="text-align:center;margin-bottom:24px;">
            <div style="display:inline-block;background:#0000ff;color:#fff;border-radius:12px;padding:12px 20px;font-size:20px;font-weight:700;letter-spacing:1px;">LIM LIBRARY</div>
          </div>
          <div style="background:#fff;border-radius:12px;padding:28px 24px;border:1px solid #e2e2ee;">
            <p style="font-size:15px;color:#1a1a2e;font-weight:600;margin:0 0 8px;">Hi ' . htmlspecialchars($row->FullName) . ',</p>
            <p style="font-size:13px;color:#6b6b80;margin:0 0 20px;">
              This is a reminder that the book below is <strong style="color:#dc2626;">overdue</strong>.
              You have held it for <strong>' . $daysBorrowed . ' day(s)</strong>, past the ' . OVERDUE_DAYS . '-day limit.
              Please return it as soon as possible.
            </p>
            <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;padding:18px 20px;margin-bottom:20px;">
              <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#dc2626;margin-bottom:6px;">Overdue Book</div>
              <div style="font-size:17px;font-weight:700;color:#1a1a2e;margin-bottom:4px;">' . htmlspecialchars($row->BookName) . '</div>
              <div style="font-size:12px;color:#6b6b80;">ISBN: ' . htmlspecialchars($row->ISBNNumber ?? 'N/A') . '</div>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:20px;">
              <tr>
                <td style="padding:8px 0;color:#6b6b80;border-bottom:1px solid #f0f0f8;">Borrowed On</td>
                <td style="padding:8px 0;font-weight:600;color:#1a1a2e;text-align:right;border-bottom:1px solid #f0f0f8;">' . $borrowDate . '</td>
              </tr>
              <tr>
                <td style="padding:8px 0;color:#6b6b80;border-bottom:1px solid #f0f0f8;">Was Due By</td>
                <td style="padding:8px 0;font-weight:600;color:#dc2626;text-align:right;border-bottom:1px solid #f0f0f8;">' . $dueDate . '</td>
              </tr>
              <tr>
                <td style="padding:8px 0;color:#6b6b80;">Days Overdue</td>
                <td style="padding:8px 0;font-weight:700;color:#dc2626;text-align:right;">' . ($daysBorrowed - OVERDUE_DAYS) . ' day(s)</td>
              </tr>
            </table>
            <p style="font-size:12px;color:#6b6b80;margin:0;">Continued delay may result in a fine and account restrictions. If you have already returned this book, please ignore this notice.</p>
          </div>
        </div>';
        $mail->send();
        echo "  SENT  Issue #{$row->issue_id} → {$row->EmailId} ({$row->BookName}, {$daysBorrowed} days)\n";

    } catch (Exception $e) {
        echo "  ERROR Issue #{$row->issue_id} → {$row->EmailId} — " . $e->getMessage() . "\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Done.\n";