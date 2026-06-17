<?php 
session_start();
include('includes/config.php');
error_reporting(0);
if(strlen($_SESSION['login'])==0) {   
    header('location:index.php');
    exit;
}

$profileMsg   = '';
$profileError = '';
$pwdMsg       = '';
$pwdError     = '';

// ── Handle Profile Update ──────────────────────────────────────
if(isset($_POST['update'])) {    
    $sid      = $_SESSION['stdid'];  
    $fname    = $_POST['fullanme'];
    $mobileno = $_POST['mobileno'];
    $sql = "UPDATE tblstudents SET FullName=:fname, MobileNumber=:mobileno WHERE StudentId=:sid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':sid',      $sid,      PDO::PARAM_STR);
    $query->bindParam(':fname',    $fname,    PDO::PARAM_STR);
    $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
    $query->execute();
    $profileMsg = 'Your profile has been updated successfully.';
}

// ── Handle Password Change ─────────────────────────────────────
if(isset($_POST['change'])) {
    $password    = md5($_POST['password']);
    $newpassword = md5($_POST['newpassword']);
    $email       = $_SESSION['login'];
    $sql = "SELECT Password FROM tblstudents WHERE EmailId=:email AND Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email',    $email,    PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    if($query->rowCount() > 0) {
        $con = "UPDATE tblstudents SET Password=:newpassword WHERE EmailId=:email";
        $chngpwd = $dbh->prepare($con);
        $chngpwd->bindParam(':email',       $email,       PDO::PARAM_STR);
        $chngpwd->bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
        $chngpwd->execute();
        $pwdMsg = 'Your password has been changed successfully.';
    } else {
        $pwdError = 'Your current password is incorrect.';
    }
}

// ── Fetch Student Data ─────────────────────────────────────────
$sid    = $_SESSION['stdid'];
$sql    = "SELECT StudentId, FullName, EmailId, MobileNumber, RegDate, UpdationDate, Status FROM tblstudents WHERE StudentId=:sid";
$query  = $dbh->prepare($sql);
$query->bindParam(':sid', $sid, PDO::PARAM_STR);
$query->execute();
$student = $query->fetch(PDO::FETCH_OBJ);

// Active tab: default profile, switch to password if pwd form submitted or error
$activeTab = (isset($_POST['change']) || $pwdError) ? 'password' : 'profile';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Lim Library | My Profile</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />

    <!-- Shared header CSS + nav -->
    <?php include('includes/header.php'); ?>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── PAGE WRAPPER ─────────────────────────── */
        .profile-page {
            padding-top: var(--nav-h);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-bottom: 48px;
        }

        .profile-container {
            width: 100%;
            max-width: 700px;
            padding: 36px 24px 0;
        }

        /* ── PAGE TITLE ───────────────────────────── */
        .page-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .page-title-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: var(--blue-soft);
            color: var(--blue);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .page-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--text);
            font-weight: 600;
        }
        .page-title p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── STUDENT META CARD ────────────────────── */
        .meta-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .meta-avatar {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-soft), #d0d0f8);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: var(--blue);
            flex-shrink: 0;
        }
        .meta-info { flex: 1; min-width: 0; }
        .meta-info h2 {
            font-size: 16px; font-weight: 600; color: var(--text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .meta-info p { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .meta-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
        .meta-badge {
            font-size: 10px; font-weight: 700; letter-spacing: 0.5px;
            padding: 3px 10px; border-radius: 20px; text-transform: uppercase;
        }
        .badge-active   { background: #e8f5e9; color: #2e7d32; }
        .badge-blocked  { background: #ffebee; color: #c62828; }
        .badge-id       { background: var(--blue-soft); color: var(--blue); }
        .meta-dates { text-align: right; flex-shrink: 0; }
        .meta-dates span { display: block; font-size: 11px; color: var(--text-muted); }
        .meta-dates strong { font-size: 11px; color: var(--text); }

        /* ── TABS ─────────────────────────────────── */
        .tab-bar {
            display: flex;
            gap: 4px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 20px;
        }
        .tab-btn {
            flex: 1;
            padding: 9px 16px;
            border: none;
            border-radius: 7px;
            background: transparent;
            font-size: 13px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }
        .tab-btn.active {
            background: var(--blue);
            color: #fff;
            font-weight: 600;
        }
        .tab-btn:hover:not(.active) {
            background: var(--blue-soft);
            color: var(--blue);
        }
        .tab-btn i { font-size: 14px; }

        /* ── PANELS ───────────────────────────────── */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ── CARD ─────────────────────────────────── */
        .form-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }
        .form-card-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-card-header i {
            font-size: 16px;
            color: var(--blue);
        }
        .form-card-header h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
        }
        .form-card-body { padding: 24px; }

        /* ── ALERTS ───────────────────────────────── */
        .alert-success, .alert-error {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .alert-success i, .alert-error i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

        /* ── FORM ELEMENTS ────────────────────────── */
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .field-row.full { grid-template-columns: 1fr; }
        .field-group { display: flex; flex-direction: column; gap: 6px; }
        .field-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: 0.3px;
        }
        .field-group input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            background: var(--bg);
            outline: none;
            transition: border 0.2s, background 0.2s;
        }
        .field-group input:focus {
            border-color: var(--blue);
            background: var(--white);
        }
        .field-group input[readonly] {
            background: #f0f0f8;
            color: var(--text-muted);
            cursor: not-allowed;
        }
        .field-hint {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── SUBMIT BTN ───────────────────────────── */
        .btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            margin-top: 8px;
        }
        .btn-submit:hover { background: var(--blue-dark); transform: translateY(-1px); }
        .btn-submit i { font-size: 14px; }

        /* ── PASSWORD TOGGLE ─────────────────────── */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrapper input {
            padding-right: 42px !important;
        }
        .toggle-pwd {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            font-size: 14px;
            padding: 0;
            line-height: 1;
            transition: color 0.2s;
            display: flex;
            align-items: center;
        }
        .toggle-pwd:hover { color: var(--blue); }

        /* ── PASSWORD STRENGTH ────────────────────── */
        .strength-bar {
            height: 4px;
            border-radius: 4px;
            background: var(--border);
            margin-top: 6px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 4px;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }
        .strength-label {
            font-size: 11px;
            margin-top: 4px;
            color: var(--text-muted);
        }

        /* ── RESPONSIVE ───────────────────────────── */
        @media (max-width: 600px) {
            .field-row { grid-template-columns: 1fr; }
            .meta-dates { display: none; }
            .profile-container { padding: 24px 16px 0; }
        }
    </style>
</head>
<body>

<!-- ══ PAGE ════════════════════════════════════════════════════ -->
<div class="profile-page">
    <div class="profile-container">

        <!-- Page title -->
        <div class="page-title">
            <div class="page-title-icon"><i class="fa fa-user"></i></div>
            <div>
                <h1>My Account</h1>
                <p>Manage your profile and security settings</p>
            </div>
        </div>

        <!-- Student meta card -->
        <?php if($student): ?>
        <div class="meta-card">
            <div class="meta-avatar"><i class="fa fa-user"></i></div>
            <div class="meta-info">
                <h2><?php echo htmlentities($student->FullName); ?></h2>
                <p><?php echo htmlentities($student->EmailId); ?></p>
                <div class="meta-badges">
                    <span class="meta-badge badge-id">ID: <?php echo htmlentities($student->StudentId); ?></span>
                    <?php if($student->Status == 1): ?>
                    <span class="meta-badge badge-active">Active</span>
                    <?php else: ?>
                    <span class="meta-badge badge-blocked">Blocked</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="meta-dates">
                <span>Registered</span>
                <strong><?php echo htmlentities($student->RegDate); ?></strong>
                <?php if(!empty($student->UpdationDate)): ?>
                <span style="margin-top:6px;">Last Updated</span>
                <strong><?php echo htmlentities($student->UpdationDate); ?></strong>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tab bar -->
        <div class="tab-bar">
            <button class="tab-btn <?php echo $activeTab=='profile' ? 'active' : ''; ?>"
                    onclick="switchTab('profile')">
                <i class="fa fa-id-card-o"></i> Edit Profile
            </button>
            <button class="tab-btn <?php echo $activeTab=='password' ? 'active' : ''; ?>"
                    onclick="switchTab('password')">
                <i class="fa fa-lock"></i> Change Password
            </button>
        </div>

        <!-- ── TAB: PROFILE ─────────────────────── -->
        <div class="tab-panel <?php echo $activeTab=='profile' ? 'active' : ''; ?>" id="panel-profile">
            <div class="form-card">
                <div class="form-card-header">
                    <i class="fa fa-pencil"></i>
                    <h3>Edit Profile Information</h3>
                </div>
                <div class="form-card-body">

                    <?php if($profileMsg): ?>
                    <div class="alert-success">
                        <i class="fa fa-check-circle"></i>
                        <?php echo htmlentities($profileMsg); ?>
                    </div>
                    <?php endif; ?>

                    <?php if($student): ?>
                    <form method="post" action="my-profile.php">
                        <div class="field-row">
                            <div class="field-group">
                                <label>Full Name</label>
                                <input type="text" name="fullanme"
                                       value="<?php echo htmlentities($student->FullName); ?>"
                                       autocomplete="off" required />
                            </div>
                            <div class="field-group">
                                <label>Mobile Number</label>
                                <input type="text" name="mobileno" maxlength="10"
                                       value="<?php echo htmlentities($student->MobileNumber); ?>"
                                       autocomplete="off" required />
                                <span class="field-hint">Max 10 digits</span>
                            </div>
                        </div>
                        <div class="field-row full">
                            <div class="field-group">
                                <label>Email Address</label>
                                <input type="email" name="email"
                                       value="<?php echo htmlentities($student->EmailId); ?>"
                                       readonly />
                                <span class="field-hint">Email cannot be changed</span>
                            </div>
                        </div>
                        <button type="submit" name="update" class="btn-submit">
                            <i class="fa fa-save"></i> Save Changes
                        </button>
                    </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- ── TAB: PASSWORD ────────────────────── -->
        <div class="tab-panel <?php echo $activeTab=='password' ? 'active' : ''; ?>" id="panel-password">
            <div class="form-card">
                <div class="form-card-header">
                    <i class="fa fa-lock"></i>
                    <h3>Change Password</h3>
                </div>
                <div class="form-card-body">

                    <?php if($pwdMsg): ?>
                    <div class="alert-success">
                        <i class="fa fa-check-circle"></i>
                        <?php echo htmlentities($pwdMsg); ?>
                    </div>
                    <?php endif; ?>

                    <?php if($pwdError): ?>
                    <div class="alert-error">
                        <i class="fa fa-exclamation-circle"></i>
                        <?php echo htmlentities($pwdError); ?>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="my-profile.php" name="chngpwd" onsubmit="return validatePwd()">
                        <div class="field-row full">
                            <div class="field-group">
                                <label>Current Password</label>
                                <div class="input-wrapper">
                                    <input type="password" name="password" id="pwd-current" autocomplete="off" required />
                                    <button type="button" class="toggle-pwd" onclick="togglePwd('pwd-current', this)" tabindex="-1" aria-label="Show password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label>New Password</label>
                                <div class="input-wrapper">
                                    <input type="password" name="newpassword" id="newpassword"
                                           autocomplete="off" required oninput="checkStrength(this.value)" />
                                    <button type="button" class="toggle-pwd" onclick="togglePwd('newpassword', this)" tabindex="-1" aria-label="Show password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                                <span class="strength-label" id="strengthLabel">Enter a new password</span>
                            </div>
                            <div class="field-group">
                                <label>Confirm New Password</label>
                                <div class="input-wrapper">
                                    <input type="password" name="confirmpassword" id="confirmpassword" autocomplete="off" required />
                                    <button type="button" class="toggle-pwd" onclick="togglePwd('confirmpassword', this)" tabindex="-1" aria-label="Show password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="change" class="btn-submit">
                            <i class="fa fa-key"></i> Update Password
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div><!-- /.profile-container -->
</div><!-- /.profile-page -->

<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script>
// Tab switching
function switchTab(tab) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    event.currentTarget.classList.add('active');
}

// Password match validation
function validatePwd() {
    var np = document.chngpwd.newpassword.value;
    var cp = document.chngpwd.confirmpassword.value;
    if(np !== cp) {
        alert('New Password and Confirm Password do not match!');
        document.chngpwd.confirmpassword.focus();
        return false;
    }
    return true;
}

// Password strength indicator
function checkStrength(val) {
    var fill  = document.getElementById('strengthFill');
    var label = document.getElementById('strengthLabel');
    var score = 0;
    if(val.length >= 8)          score++;
    if(/[A-Z]/.test(val))        score++;
    if(/[0-9]/.test(val))        score++;
    if(/[^A-Za-z0-9]/.test(val)) score++;

    var configs = [
        { w: '0%',   bg: 'transparent', text: 'Enter a new password' },
        { w: '25%',  bg: '#ef5350',     text: 'Weak' },
        { w: '50%',  bg: '#ffa726',     text: 'Fair' },
        { w: '75%',  bg: '#29b6f6',     text: 'Good' },
        { w: '100%', bg: '#66bb6a',     text: 'Strong' },
    ];
    var c = val.length === 0 ? configs[0] : configs[score];
    fill.style.width      = c.w;
    fill.style.background = c.bg;
    label.textContent     = c.text;
    label.style.color     = c.bg || 'var(--text-muted)';
}

// Show / hide password toggle
function togglePwd(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
        btn.setAttribute('aria-label', 'Hide password');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
        btn.setAttribute('aria-label', 'Show password');
    }
}
</script>
<script src="assets/js/librarybot.js"></script>
</body>
</html>