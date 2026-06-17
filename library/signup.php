<?php 
session_start();
include('includes/config.php');
error_reporting(0);

if(isset($_POST['signup']))
{
    // FIX: Generate StudentId from DB (auto-increment style) instead of file read
    // This avoids crash when studentid.txt is missing
    $sql_maxid = "SELECT MAX(CAST(SUBSTRING(StudentId, 4) AS UNSIGNED)) as maxnum FROM tblstudents";
    $q_maxid   = $dbh->prepare($sql_maxid);
    $q_maxid->execute();
    $row_maxid  = $q_maxid->fetch(PDO::FETCH_OBJ);
    $nextnum    = ($row_maxid && $row_maxid->maxnum) ? ((int)$row_maxid->maxnum + 1) : 1001;
    $StudentId  = 'SID' . str_pad($nextnum, 3, '0', STR_PAD_LEFT);

    $fname    = $_POST['fullanme'];   // kept as-is (form name matches)
    $mobileno = $_POST['mobileno'];
    $email    = $_POST['email']; 
    $password = md5($_POST['password']); 
    $status   = 1;

    // Check email not already registered
    $chk = $dbh->prepare("SELECT id FROM tblstudents WHERE EmailId=:email");
    $chk->bindParam(':email', $email, PDO::PARAM_STR);
    $chk->execute();
    if($chk->rowCount() > 0){
        echo "<script>alert('This email is already registered. Please use a different email.');</script>";
    } else {
        $sql   = "INSERT INTO tblstudents(StudentId,FullName,MobileNumber,EmailId,Password,Status) VALUES(:StudentId,:fname,:mobileno,:email,:password,:status)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':StudentId', $StudentId, PDO::PARAM_STR);
        $query->bindParam(':fname',     $fname,     PDO::PARAM_STR);
        $query->bindParam(':mobileno',  $mobileno,  PDO::PARAM_STR);
        $query->bindParam(':email',     $email,     PDO::PARAM_STR);
        $query->bindParam(':password',  $password,  PDO::PARAM_STR);
        $query->bindParam(':status',    $status,    PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();
        if($lastInsertId){
            echo '<script>alert("Your Registration successful and your student ID is ' . $StudentId . '")</script>';
        } else {
            echo "<script>alert('Something went wrong. Please try again');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Lim Library Management System | Student Signup</title>
 
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
 
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Open Sans', sans-serif;
            overflow-x: hidden;
        }
 
        .split-layout {
            display: flex;
            min-height: 100vh;
        }
 
        .left-half {
            flex: 6;
            background: url('assets/img/library_bg.jpg') no-repeat center center;
            background-size: cover;
        }
 
        .right-half {
            flex: 4;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            padding: 40px;
            overflow-y: auto;
        }
 
        .signup-container {
            width: 100%;
            max-width: 370px;
            text-align: center;
            padding: 20px 0;
        }
 
        .logo-area img {
            max-height: 70px;
            width: auto;
            margin-bottom: 10px;
        }
 
        .page-label {
            display: inline-block;
            background: #e8e8ff;
            color: #0000ff;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1.5px;
            padding: 5px 16px;
            border-radius: 20px;
            margin-bottom: 20px;
        }
 
        .input-group-custom {
            position: relative;
            margin-bottom: 14px;
            text-align: left;
        }
 
        .input-group-custom i.left-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #333;
            font-size: 17px;
        }
 
        .input-group-custom i.right-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #333;
            font-size: 17px;
            cursor: pointer;
        }
 
        .input-group-custom input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1.5px solid #0000ff;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            color: #333;
            font-family: 'Open Sans', sans-serif;
            box-sizing: border-box;
        }
 
        .input-group-custom input:focus {
            border-color: #0000cc;
            box-shadow: 0 0 0 3px rgba(0,0,255,0.08);
        }
 
        .email-status {
            font-size: 11px;
            margin-top: 4px;
            padding-left: 4px;
            display: block;
            text-align: left;
        }
 
        .btn-register {
            width: 100%;
            padding: 12px;
            background: #0000ff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            margin-top: 6px;
            margin-bottom: 14px;
            cursor: pointer;
            transition: background 0.3s;
            font-family: 'Open Sans', sans-serif;
        }
 
        .btn-register:hover {
            background: #0000cc;
        }
 
        .back-login {
            display: block;
            width: 100%;
            padding: 11px;
            background: white;
            color: #555;
            border: 1.5px solid #0000ff;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
            box-sizing: border-box;
            font-family: 'Open Sans', sans-serif;
        }
 
        .back-login:hover {
            background: #f0f0ff;
            text-decoration: none;
            color: #333;
        }
 
        @media (max-width: 768px) {
            .split-layout {
                flex-direction: column;
            }
            .left-half {
                min-height: 220px;
                flex: none;
            }
            .right-half {
                padding: 30px 20px;
            }
        }
    </style>
 
    <script type="text/javascript">
        function valid() {
            if (document.signup.password.value != document.signup.confirmpassword.value) {
                alert("Password and Confirm Password Field do not match !!");
                document.signup.confirmpassword.focus();
                return false;
            }
            return true;
        }
    </script>
 
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script>
        function checkAvailability() {
            $("#loaderIcon").show();
            jQuery.ajax({
                url: "check_availability.php",
                data: 'emailid=' + $("#emailid").val(),
                type: "POST",
                success: function(data) {
                    $("#user-availability-status").html(data);
                    $("#loaderIcon").hide();
                },
                error: function() {}
            });
        }
 
        function togglePassword(fieldId) {
            var field = document.getElementById(fieldId);
            field.type = field.type === "password" ? "text" : "password";
        }
    </script>
</head>
<body>
 
<div class="split-layout">
    <div class="left-half"></div>
 
    <div class="right-half">
        <div class="signup-container">
 
            <div class="logo-area">
                <img src="assets/img/limlibrary.png" alt="Library Logo" />
            </div>
 
            <div class="page-label">NEW STUDENT REGISTRATION</div>
 
            <form name="signup" method="post" onSubmit="return valid();">
 
                <!-- Full Name -->
                <div class="input-group-custom">
                    <i class="fa fa-user left-icon"></i>
                    <input type="text" name="fullanme" placeholder="Full Name" autocomplete="off" required />
                </div>
 
                <!-- Mobile Number -->
                <div class="input-group-custom">
                    <i class="fa fa-phone left-icon"></i>
                    <input type="text" name="mobileno" placeholder="Mobile Number" maxlength="10" autocomplete="off" required />
                </div>
 
                <!-- Email -->
                <div class="input-group-custom">
                    <i class="fa fa-envelope left-icon"></i>
                    <input type="email" name="email" id="emailid" placeholder="Email Address" onBlur="checkAvailability()" autocomplete="off" required />
                    <span id="user-availability-status" class="email-status"></span>
                    <span id="loaderIcon" style="display:none; font-size:11px; color:#888;">Checking...</span>
                </div>
 
                <!-- Password -->
                <div class="input-group-custom">
                    <i class="fa fa-unlock-alt left-icon"></i>
                    <input type="password" name="password" id="password-field" placeholder="Password" autocomplete="off" required />
                    <i class="fa fa-eye right-icon" onclick="togglePassword('password-field')"></i>
                </div>
 
                <!-- Confirm Password -->
                <div class="input-group-custom">
                    <i class="fa fa-lock left-icon"></i>
                    <input type="password" name="confirmpassword" id="confirm-field" placeholder="Confirm Password" autocomplete="off" required />
                    <i class="fa fa-eye right-icon" onclick="togglePassword('confirm-field')"></i>
                </div>
 
                <button type="submit" name="signup" class="btn-register">Register Now</button>
                <a href="index.php" class="back-login">&#8592; Back to Login</a>
 
            </form>
 
        </div>
    </div>
</div>
 
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/custom.js"></script>
</body>
</html>
