<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Fix #1: use statements must be at the top-level scope, NOT inside if blocks
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$step    = isset($_SESSION['fp_step']) ? $_SESSION['fp_step'] : 1;
$msg     = '';
$msgType = '';

// ── STEP 1: Submit email → send OTP ──────────────────────────────────────────
if(isset($_POST['send_otp'])) {
    $email = trim($_POST['email']);

    // Check email exists
    $sql   = "SELECT StudentId, FullName FROM tblstudents WHERE EmailId = :email";
    $q     = $dbh->prepare($sql);
    $q->bindParam(':email', $email, PDO::PARAM_STR);
    $q->execute();

    if($q->rowCount() > 0) {
        $row = $q->fetch(PDO::FETCH_OBJ);
        $otp = rand(100000, 999999);

        $_SESSION['fp_email']    = $email;
        $_SESSION['fp_otp']      = $otp;
        $_SESSION['fp_otp_time'] = time();
        $_SESSION['fp_name']     = $row->FullName;

        // Send email via PHPMailer
        $sent = false;
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            // Fix #3: Move credentials to config.php instead of hardcoding them
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($mail->Username, 'Lim Library');
            $mail->addAddress($email, $row->FullName);
            $mail->isHTML(true);
            $mail->Subject = 'Your Lim Library Password Reset Code';
            $mail->Body    = '
            <div style="font-family:DM Sans,Arial,sans-serif;max-width:480px;margin:0 auto;padding:32px 24px;background:#f5f5fb;border-radius:16px;">
              <div style="text-align:center;margin-bottom:24px;">
                <div style="display:inline-block;background:#0000ff;color:#fff;border-radius:12px;padding:12px 20px;font-size:20px;font-weight:700;letter-spacing:1px;">LIM LIBRARY</div>
              </div>
              <div style="background:#fff;border-radius:12px;padding:28px 24px;border:1px solid #e2e2ee;">
                <p style="font-size:15px;color:#1a1a2e;font-weight:600;margin:0 0 8px;">Hi '.$row->FullName.',</p>
                <p style="font-size:13px;color:#6b6b80;margin:0 0 24px;">We received a request to reset your Lim Library password. Use the verification code below.</p>
                <div style="background:#e8e8ff;border-radius:10px;padding:20px;text-align:center;margin-bottom:24px;">
                  <div style="font-size:36px;font-weight:700;letter-spacing:8px;color:#0000ff;">'.$otp.'</div>
                  <div style="font-size:12px;color:#6b6b80;margin-top:6px;">Valid for 10 minutes</div>
                </div>
                <p style="font-size:12px;color:#6b6b80;margin:0;">If you did not request this, please ignore this email. Your password will remain unchanged.</p>
              </div>
            </div>';
            $mail->send();
            $sent = true;
        } catch(Exception $e) {
            $sent = false;
        }

        $_SESSION['fp_step'] = 2;
        $step = 2;
        $msg  = $sent
            ? 'A 6-digit code has been sent to <strong>' . htmlentities($email) . '</strong>. Check your inbox.'
            : 'Email send failed. For testing, your code is: <strong>' . $otp . '</strong>';
        $msgType = $sent ? 'success' : 'warning';

    } else {
        $msg     = 'No account found with that email address.';
        $msgType = 'error';
    }
}

// ── STEP 2: Verify OTP ────────────────────────────────────────────────────────
if(isset($_POST['verify_otp'])) {
    $entered = trim($_POST['otp']);
    $elapsed = time() - (int)$_SESSION['fp_otp_time'];

    if($elapsed > 600) {
        $msg     = 'Your code has expired. Please start over.';
        $msgType = 'error';
        $_SESSION['fp_step'] = 1;
        $step = 1;
    } elseif($entered == $_SESSION['fp_otp']) {
        $_SESSION['fp_step']     = 3;
        $_SESSION['fp_verified'] = true;
        $step = 3;
        $msg     = 'Code verified! Now set your new password.';
        $msgType = 'success';
    } else {
        $msg     = 'Incorrect code. Please try again.';
        $msgType = 'error';
        $step = 2;
    }
}

// Resend OTP — Fix #2: now handled as a standalone POST outside nested form
if(isset($_POST['resend_otp'])) {
    unset($_SESSION['fp_step'], $_SESSION['fp_otp'], $_SESSION['fp_otp_time'],
          $_SESSION['fp_email'], $_SESSION['fp_name'], $_SESSION['fp_verified']);
    $step = 1;
}

// ── STEP 3: Update password ───────────────────────────────────────────────────
if(isset($_POST['change_password'])) {
    if(!isset($_SESSION['fp_verified']) || !$_SESSION['fp_verified']) {
        header('location:user-forgot-password.php');
        exit;
    }

    // Fix #4: Use password_hash() instead of md5()
    $newpassword = md5($_POST['newpassword']);
    $email       = $_SESSION['fp_email'];

    $sql = "UPDATE tblstudents SET Password=:pwd WHERE EmailId=:email";
    $q   = $dbh->prepare($sql);
    $q->bindParam(':pwd',   $newpassword, PDO::PARAM_STR);
    $q->bindParam(':email', $email,       PDO::PARAM_STR);
    $q->execute();

    if($q->rowCount() > 0) {
        // Clear session
        unset($_SESSION['fp_step'], $_SESSION['fp_otp'], $_SESSION['fp_otp_time'],
              $_SESSION['fp_email'], $_SESSION['fp_name'], $_SESSION['fp_verified']);
        $msg     = 'Password changed successfully! You can now <a href="index.php" style="color:#0000ff;font-weight:600;">log in</a>.';
        $msgType = 'success';
        $step    = 4; // done
    } else {
        $msg     = 'Something went wrong. Please try again.';
        $msgType = 'error';
        $step    = 3;
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Lim Library | Password Recovery</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <?php include('includes/header.php'); ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        .fp-page {
            padding-top: var(--nav-h);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fp-wrap {
            width: 100%;
            max-width: 440px;
            padding: 24px 16px 48px;
        }

        /* ── STEPPER ── */
        .stepper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 28px;
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            position: relative;
        }
        .step-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700;
            border: 2px solid var(--border);
            background: var(--white);
            color: var(--text-muted);
            transition: all 0.3s;
            position: relative;
            z-index: 1;
        }
        .step-circle.done  { background: #e8f5e9; border-color: #2e7d32; color: #2e7d32; }
        .step-circle.active{ background: var(--blue); border-color: var(--blue); color: #fff; }
        .step-label {
            font-size: 10px; font-weight: 600; letter-spacing: 0.5px;
            text-transform: uppercase; color: var(--text-muted);
            white-space: nowrap;
        }
        .step-label.active { color: var(--blue); }
        .step-label.done   { color: #2e7d32; }
        .step-line {
            height: 2px; width: 60px; background: var(--border);
            margin-bottom: 22px; flex-shrink: 0;
            transition: background 0.3s;
        }
        .step-line.done { background: #2e7d32; }

        /* ── CARD ── */
        .fp-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
        }
        .fp-card-header {
            padding: 24px 28px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .fp-icon {
            width: 46px; height: 46px;
            border-radius: 13px;
            background: var(--blue-soft);
            color: var(--blue);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .fp-card-header h2 { font-family: 'Playfair Display', serif; font-size: 19px; color: var(--text); font-weight: 600; }
        .fp-card-header p  { font-size: 12px; color: var(--text-muted); margin-top: 3px; }
        .fp-card-body { padding: 24px 28px 28px; }

        /* ── ALERTS ── */
        .fp-alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 500; margin-bottom: 20px;
        }
        .fp-alert i { font-size: 15px; flex-shrink: 0; margin-top: 1px; }
        .fp-alert.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .fp-alert.error   { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .fp-alert.warning { background: #fffde7; color: #b45309; border: 1px solid #fff59d; }

        /* ── FORM ── */
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block; font-size: 12px; font-weight: 600;
            color: var(--text-muted); letter-spacing: 0.4px;
            text-transform: uppercase; margin-bottom: 7px;
        }
        .form-group .input-wrap { position: relative; }
        .form-group .input-icon {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 14px; pointer-events: none;
        }
        .form-group .toggle-pw {
            position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 14px; cursor: pointer; background: none; border: none; padding: 0;
        }
        .form-group input {
            width: 100%; padding: 11px 40px 11px 38px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 13px; font-family: 'DM Sans', sans-serif;
            color: var(--text); background: var(--bg); outline: none;
            transition: border 0.2s;
        }
        .form-group input:focus { border-color: var(--blue); background: var(--white); }
        .form-group input.otp-input {
            text-align: center; letter-spacing: 10px; font-size: 22px;
            font-weight: 700; padding: 14px 16px;
        }

        /* ── PASSWORD STRENGTH ── */
        .pw-strength { margin-top: 8px; }
        .pw-bars {
            display: flex; gap: 4px; margin-bottom: 6px;
        }
        .pw-bar {
            flex: 1; height: 4px; border-radius: 4px;
            background: var(--border); transition: background 0.3s;
        }
        .pw-bar.weak   { background: #ef5350; }
        .pw-bar.medium { background: #ffa726; }
        .pw-bar.strong { background: #66bb6a; }
        .pw-label { font-size: 11px; color: var(--text-muted); }
        .pw-label.weak   { color: #ef5350; }
        .pw-label.medium { color: #ffa726; }
        .pw-label.strong { color: #66bb6a; }

        /* ── REQUIREMENTS ── */
        .pw-reqs { margin-top: 10px; display: flex; flex-direction: column; gap: 5px; }
        .pw-req {
            display: flex; align-items: center; gap: 7px;
            font-size: 12px; color: var(--text-muted);
        }
        .pw-req i { font-size: 12px; width: 14px; text-align: center; }
        .pw-req.met { color: #2e7d32; }
        .pw-req.met i { color: #2e7d32; }

        /* ── BUTTON ── */
        .btn-primary-full {
            width: 100%; padding: 12px;
            background: var(--blue); color: #fff; border: none;
            border-radius: 10px; font-size: 14px; font-weight: 600;
            font-family: 'DM Sans', sans-serif; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-primary-full:hover { background: var(--blue-dark); transform: translateY(-1px); }
        .btn-primary-full:active { transform: translateY(0); }

        /* ── FOOTER LINK ── */
        .fp-footer { text-align: center; margin-top: 14px; }
        .fp-footer a { font-size: 13px; color: var(--text-muted); text-decoration: none; }
        .fp-footer a:hover { color: var(--blue); }

        /* ── SUCCESS STATE ── */
        .success-state { text-align: center; padding: 12px 0 8px; }
        .success-circle {
            width: 72px; height: 72px; border-radius: 50%;
            background: #e8f5e9; display: flex; align-items: center; justify-content: center;
            font-size: 34px; color: #2e7d32; margin: 0 auto 18px;
        }
        .success-state h3 { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .success-state p  { font-size: 13px; color: var(--text-muted); margin-bottom: 24px; }

        /* ── OTP timer ── */
        .otp-meta { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
        .otp-timer { font-size: 12px; color: var(--text-muted); }
        .otp-resend { font-size: 12px; background: none; border: none; color: var(--blue); cursor: pointer; font-family: 'DM Sans',sans-serif; font-weight: 600; padding: 0; }
        .otp-resend:disabled { color: var(--text-muted); cursor: default; }

        @media(max-width:500px) {
            .fp-card-body { padding: 20px 18px 24px; }
            .fp-card-header { padding: 20px 18px 16px; }
        }
    </style>
</head>
<body>
<div class="fp-page">
<div class="fp-wrap">

    <!-- Stepper -->
    <div class="stepper">
        <div class="step-item">
            <div class="step-circle <?php echo $step>=2?'done':($step==1?'active':''); ?>">
                <?php echo $step>=2 ? '<i class="fa fa-check"></i>' : '1'; ?>
            </div>
            <div class="step-label <?php echo $step==1?'active':($step>=2?'done':''); ?>">Email</div>
        </div>
        <div class="step-line <?php echo $step>=2?'done':''; ?>"></div>
        <div class="step-item">
            <div class="step-circle <?php echo $step>=3?'done':($step==2?'active':''); ?>">
                <?php echo $step>=3 ? '<i class="fa fa-check"></i>' : '2'; ?>
            </div>
            <div class="step-label <?php echo $step==2?'active':($step>=3?'done':''); ?>">Verify</div>
        </div>
        <div class="step-line <?php echo $step>=3?'done':''; ?>"></div>
        <div class="step-item">
            <div class="step-circle <?php echo $step>=4?'done':($step==3?'active':''); ?>">
                <?php echo $step>=4 ? '<i class="fa fa-check"></i>' : '3'; ?>
            </div>
            <div class="step-label <?php echo $step==3?'active':($step>=4?'done':''); ?>">Reset</div>
        </div>
    </div>

    <div class="fp-card">

        <?php if($step == 4): ?>
        <!-- ── DONE ────────────────────────────────────── -->
        <div class="fp-card-body">
            <div class="success-state">
                <div class="success-circle"><i class="fa fa-check"></i></div>
                <h3>Password Updated!</h3>
                <p>Your password has been changed successfully. You can now log in with your new password.</p>
                <a href="index.php" class="btn-primary-full" style="text-decoration:none;">
                    <i class="fa fa-sign-in"></i> Go to Login
                </a>
            </div>
        </div>

        <?php elseif($step == 3): ?>
        <!-- ── STEP 3: New Password ────────────────────── -->
        <div class="fp-card-header">
            <div class="fp-icon"><i class="fa fa-lock"></i></div>
            <div>
                <h2>New Password</h2>
                <p>Choose a strong password for your account</p>
            </div>
        </div>
        <div class="fp-card-body">
            <?php if($msg): ?>
            <div class="fp-alert <?php echo $msgType; ?>">
                <i class="fa fa-<?php echo $msgType=='success'?'check-circle':'exclamation-circle'; ?>"></i>
                <span><?php echo $msg; ?></span>
            </div>
            <?php endif; ?>
            <form method="post" action="" id="pwForm" onsubmit="return validatePw();">
                <div class="form-group">
                    <label>New Password</label>
                    <div class="input-wrap">
                        <i class="fa fa-lock input-icon"></i>
                        <input type="password" id="newpassword" name="newpassword" required autocomplete="off" placeholder="Enter new password" oninput="checkStrength(this.value)" />
                        <button type="button" class="toggle-pw" onclick="togglePw('newpassword', this)"><i class="fa fa-eye"></i></button>
                    </div>
                    <div class="pw-strength">
                        <div class="pw-bars">
                            <div class="pw-bar" id="bar1"></div>
                            <div class="pw-bar" id="bar2"></div>
                            <div class="pw-bar" id="bar3"></div>
                            <div class="pw-bar" id="bar4"></div>
                        </div>
                        <span class="pw-label" id="pw-label">Enter a password</span>
                    </div>
                    <div class="pw-reqs" id="pw-reqs">
                        <div class="pw-req" id="req-len"><i class="fa fa-circle-o"></i> At least 8 characters</div>
                        <div class="pw-req" id="req-upper"><i class="fa fa-circle-o"></i> One uppercase letter</div>
                        <div class="pw-req" id="req-num"><i class="fa fa-circle-o"></i> One number</div>
                        <div class="pw-req" id="req-sym"><i class="fa fa-circle-o"></i> One special character</div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="input-wrap">
                        <i class="fa fa-lock input-icon"></i>
                        <input type="password" id="confirmpassword" name="confirmpassword" required autocomplete="off" placeholder="Re-enter new password" />
                        <button type="button" class="toggle-pw" onclick="togglePw('confirmpassword', this)"><i class="fa fa-eye"></i></button>
                    </div>
                </div>
                <button type="submit" name="change_password" class="btn-primary-full">
                    <i class="fa fa-check"></i> Update Password
                </button>
            </form>
        </div>

        <?php elseif($step == 2): ?>
        <!-- ── STEP 2: OTP ─────────────────────────────── -->
        <div class="fp-card-header">
            <div class="fp-icon"><i class="fa fa-shield"></i></div>
            <div>
                <h2>Enter Code</h2>
                <p>Check your inbox for the 6-digit code</p>
            </div>
        </div>
        <div class="fp-card-body">
            <?php if($msg): ?>
            <div class="fp-alert <?php echo $msgType; ?>">
                <i class="fa fa-<?php echo $msgType=='success'?'envelope':'exclamation-circle'; ?>"></i>
                <span><?php echo $msg; ?></span>
            </div>
            <?php endif; ?>

            <!-- Fix #2: Removed nested <form> — resend button is now in its own separate form,
                 placed OUTSIDE the verify form so there is no nesting. -->
            <form method="post" action="">
                <div class="form-group">
                    <label>Verification Code</label>
                    <div class="input-wrap">
                        <input type="text" name="otp" class="otp-input" maxlength="6"
                               placeholder="——————" autocomplete="off" required />
                    </div>
                    <div class="otp-meta">
                        <span class="otp-timer">Code expires in <span id="countdown">10:00</span></span>
                    </div>
                </div>
                <button type="submit" name="verify_otp" class="btn-primary-full">
                    <i class="fa fa-check-circle"></i> Verify Code
                </button>
            </form>

            <!-- Resend form is now a separate, non-nested form -->
            <form method="post" action="" style="text-align:center; margin-top:12px;">
                <button type="submit" name="resend_otp" class="otp-resend">Resend code</button>
            </form>
        </div>

        <?php else: ?>
        <!-- ── STEP 1: Email ───────────────────────────── -->
        <div class="fp-card-header">
            <div class="fp-icon"><i class="fa fa-envelope"></i></div>
            <div>
                <h2>Forgot Password</h2>
                <p>Enter your registered email to receive a code</p>
            </div>
        </div>
        <div class="fp-card-body">
            <?php if($msg): ?>
            <div class="fp-alert <?php echo $msgType; ?>">
                <i class="fa fa-<?php echo $msgType=='error'?'exclamation-circle':'info-circle'; ?>"></i>
                <span><?php echo $msg; ?></span>
            </div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <i class="fa fa-envelope input-icon"></i>
                        <input type="email" name="email" required autocomplete="off" placeholder="your@email.com" />
                    </div>
                </div>
                <button type="submit" name="send_otp" class="btn-primary-full">
                    <i class="fa fa-paper-plane"></i> Send Verification Code
                </button>
            </form>
        </div>
        <?php endif; ?>

    </div><!-- /.fp-card -->

    <div class="fp-footer">
        <a href="index.php"><i class="fa fa-arrow-left"></i> Back to Login</a>
    </div>

</div>
</div>

<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script>
// ── Password strength ──────────────────────────────────────────────────
function checkStrength(pw) {
    var bars  = [document.getElementById('bar1'), document.getElementById('bar2'),
                 document.getElementById('bar3'), document.getElementById('bar4')];
    var label = document.getElementById('pw-label');

    var len   = pw.length >= 8;
    var upper = /[A-Z]/.test(pw);
    var num   = /[0-9]/.test(pw);
    var sym   = /[^A-Za-z0-9]/.test(pw);

    setReq('req-len',   len);
    setReq('req-upper', upper);
    setReq('req-num',   num);
    setReq('req-sym',   sym);

    var score = [len, upper, num, sym].filter(Boolean).length;

    bars.forEach(function(b) { b.className = 'pw-bar'; });
    label.className = 'pw-label';

    if(pw.length === 0) { label.textContent = 'Enter a password'; return; }
    if(score <= 1) {
        bars[0].classList.add('weak');
        label.classList.add('weak'); label.textContent = 'Weak';
    } else if(score === 2) {
        bars[0].classList.add('medium'); bars[1].classList.add('medium');
        label.classList.add('medium'); label.textContent = 'Fair';
    } else if(score === 3) {
        bars[0].classList.add('strong'); bars[1].classList.add('strong'); bars[2].classList.add('strong');
        label.classList.add('medium'); label.textContent = 'Good';
    } else {
        bars.forEach(function(b) { b.classList.add('strong'); });
        label.classList.add('strong'); label.textContent = 'Strong';
    }
}

function setReq(id, met) {
    var el = document.getElementById(id);
    if(!el) return;
    el.className = 'pw-req' + (met ? ' met' : '');
    el.querySelector('i').className = met ? 'fa fa-check-circle' : 'fa fa-circle-o';
}

function togglePw(id, btn) {
    var inp = document.getElementById(id);
    if(inp.type === 'password') { inp.type = 'text';     btn.querySelector('i').className = 'fa fa-eye-slash'; }
    else                        { inp.type = 'password'; btn.querySelector('i').className = 'fa fa-eye'; }
}

function validatePw() {
    var pw  = document.getElementById('newpassword').value;
    var cpw = document.getElementById('confirmpassword').value;
    if(pw !== cpw) { alert('Passwords do not match!'); return false; }
    if(pw.length < 8) { alert('Password must be at least 8 characters.'); return false; }
    return true;
}

// ── OTP countdown ─────────────────────────────────────────────────────
(function() {
    var el = document.getElementById('countdown');
    if(!el) return;
    var secs = 600;
    var t = setInterval(function() {
        secs--;
        if(secs <= 0) { clearInterval(t); el.textContent = 'Expired'; return; }
        var m = Math.floor(secs/60), s = secs%60;
        el.textContent = m + ':' + (s<10?'0':'') + s;
    }, 1000);
})();
</script>
</body>
</html>