<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Reset sessions if someone is already logged in and returns to the login page
if(isset($_SESSION['login']) && $_SESSION['login'] != ''){
    $_SESSION['login'] = '';
}
if(isset($_SESSION['alogin']) && $_SESSION['alogin'] != ''){
    $_SESSION['alogin'] = '';
}

$loginType = isset($_POST['loginType']) ? $_POST['loginType'] : 'student';

// ==========================================
// 1. STUDENT LOGIN
// ==========================================
if(isset($_POST['login']) && $loginType == 'student')
{
    $email = $_POST['emailid']; 
    $password = md5($_POST['password']); 

    $sql = "SELECT EmailId, Password, StudentId, Status FROM tblstudents WHERE EmailId=:email and Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if($query->rowCount() > 0)
    {
        if($result->Status == 1) {
            $_SESSION['stdid'] = $result->StudentId;
            $_SESSION['login'] = $_POST['emailid'];
            echo "<script type='text/javascript'> document.location = 'dashboard.php'; </script>";
        } else {
            echo "<script>alert('Your Account has been blocked. Please contact admin.');</script>";
        }
    } else {
        echo "<script>alert('Invalid Student Details');</script>";
    }
}

// ==========================================
// 2. ADMIN LOGIN
// ==========================================
if(isset($_POST['login']) && $loginType == 'admin')
{
    $username = trim($_POST['emailid']); // trim to remove accidental whitespace
    $password = md5(trim($_POST['password']));

    $sql = "SELECT UserName, Password FROM admin WHERE UserName=:username AND Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();

    if($query->rowCount() > 0)
    {
        $_SESSION['alogin'] = $username;
        echo "<script type='text/javascript'> document.location = 'admin/dashboard.php'; </script>";
    } else {
        echo "<script>alert('Invalid Admin Details');</script>";
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Lim Library Management System | Login</title>
    
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
        }

        .login-container {
            width: 100%;
            max-width: 350px;
            text-align: center;
        }

        .logo-area img {
            width: 80px;
            margin-bottom: 10px;
        }
        .logo-area h2 {
            font-size: 20px;
            letter-spacing: 2px;
            color: #555;
            margin: 0 0 5px 0;
        }
        .logo-area p {
            font-size: 10px;
            letter-spacing: 1px;
            color: #999;
            margin-bottom: 30px;
        }

        .role-toggle {
            display: flex;
            background: #e0e0e0;
            border-radius: 25px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .role-toggle div {
            flex: 1;
            padding: 10px 0;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            border-radius: 25px;
        }
        .role-toggle .active {
            background: #0000ff;
            color: #fff;
        }
        .role-toggle .inactive {
            background: transparent;
            color: #888;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 15px;
        }
        .input-group-custom i.left-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #333;
            font-size: 18px;
        }
        .input-group-custom i.right-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #333;
            font-size: 18px;
            cursor: pointer;
        }
        .input-group-custom input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1.5px solid #0000ff;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            color: #333;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #0000ff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #0000cc;
        }
        
        .btn-signup {
            display: block;
            width: 100%;
            padding: 12px;
            background: white;
            color: #666;
            border: 1.5px solid #0000ff;
            border-radius: 8px;
            font-size: 16px;
            text-decoration: none;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .btn-signup:hover {
            background: #f0f0ff;
            text-decoration: none;
        }

        .forgot-password {
            font-size: 12px;
            color: #4dc3ff;
            text-decoration: underline;
        }

        .student-only {
            display: block;
        }
        .admin-mode .student-only {
            display: none;
        }

        @media (max-width: 768px) {
            .split-layout {
                flex-direction: column;
            }
            .left-half {
                min-height: 300px;
                flex: none;
            }
            .right-half {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="split-layout">
        <div class="left-half"></div>

        <div class="right-half">
            <div class="login-container" id="login-container">
                
                <div class="logo-area">
                    <img src="assets/img/limlibrary.png" style="max-height: 70px; width: auto; margin-top: -10px;" alt="Library Logo" />
                </div>

                <div class="role-toggle">
                    <div id="student-tab" class="active" onclick="switchRole('student')">User / Student</div>
                    <div id="admin-tab" class="inactive" onclick="switchRole('admin')">Staff / Admin</div>
                </div>

                <form role="form" method="post">
                    <input type="hidden" name="loginType" id="loginType" value="student" />
                    
                    <div class="input-group-custom">
                        <i class="fa fa-user left-icon"></i>
                        <input type="text" name="emailid" id="emailid" placeholder="Username" required autocomplete="off" />
                    </div>
                    
                    <div class="input-group-custom">
                        <i class="fa fa-unlock-alt left-icon"></i>
                        <input type="password" name="password" id="password-field" placeholder="Password" required autocomplete="off" />
                        <i class="fa fa-eye right-icon" onclick="togglePassword()"></i>
                    </div>

                    <button type="submit" name="login" class="btn-login">Login</button>
                    <a href="signup.php" class="btn-signup student-only">Sign Up</a>
                    <a href="user-forgot-password.php" class="forgot-password student-only">Forgot Password</a>
                </form>

            </div>
        </div>
    </div>

    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script>
        function togglePassword() {
            var passField = document.getElementById("password-field");
            passField.type = passField.type === "password" ? "text" : "password";
        }

        function switchRole(role) {
            var studentTab = document.getElementById("student-tab");
            var adminTab = document.getElementById("admin-tab");
            var loginTypeField = document.getElementById("loginType");
            var container = document.getElementById("login-container");
            var emailField = document.getElementById("emailid");

            if (role === 'admin') {
                studentTab.className = "inactive";
                adminTab.className = "active";
                loginTypeField.value = "admin";
                container.classList.add("admin-mode");
                emailField.placeholder = "Admin Username";
            } else {
                studentTab.className = "active";
                adminTab.className = "inactive";
                loginTypeField.value = "student";
                container.classList.remove("admin-mode");
                emailField.placeholder = "Username";
            }
        }
    </script>
</body>
</html>