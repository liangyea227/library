<?php
session_start();
error_reporting(0);
include('includes/config.php');
if($_SESSION['login']!=''){
    $_SESSION['login']='';
}
if(isset($_POST['login']))
{
    // Note: Kept 'emailid' variable name to match your backend, though UI says 'Username'
    $email=$_POST['emailid'];
    $password=md5($_POST['password']);
    $sql ="SELECT EmailId,Password,StudentId,Status FROM tblstudents WHERE EmailId=:email and Password=:password";
    $query= $dbh -> prepare($sql);
    $query-> bindParam(':email', $email, PDO::PARAM_STR);
    $query-> bindParam(':password', $password, PDO::PARAM_STR);
    $query-> execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);

    if($query->rowCount() > 0)
    {
        foreach ($results as $result) {
            $_SESSION['stdid']=$result->StudentId;
            if($result->Status==1) {
                $_SESSION['login']=$_POST['emailid'];
                echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>";
            } else {
                echo "<script>alert('Your Account Has been blocked. Please contact admin');</script>";
            }
        }
    } else {
        echo "<script>alert('Invalid Details');</script>";
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
        /* Custom Split-Screen Layout CSS */
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

        /* Left Side: Image Background */
        .left-half {
            flex: 6; /* Takes up roughly 60% of the screen */
            /* UPDATE THE URL BELOW TO YOUR LIBRARY BACKGROUND IMAGE */
            background: url('assets/img/library_bg.jpg') no-repeat center center;
            background-size: cover;
        }

        /* Right Side: Login Form */
        .right-half {
            flex: 4; /* Takes up roughly 40% of the screen */
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

        /* Branding */
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

        /* User/Admin Toggle Styling */
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
            color: #fff;
            cursor: pointer;
            transition: 0.3s;
        }
        .role-toggle .active {
            background: #0000ff; /* Blue color matching your design */
            border-radius: 25px;
        }
        .role-toggle .inactive {
            color: #888;
        }

        /* Input Fields with Icons */
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

        /* Buttons & Links */
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
            color: #4dc3ff; /* Cyan color from design */
            text-decoration: underline;
        }

        /* Responsive behavior */
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
            <div class="login-container">
                
                <div class="logo-area">
                    <img src="assets/img/limlibrary.png" alt="Logo" />
                    <h2>LIM LIBRARY</h2>
                    <p>GAIN MORE KNOWLEDGE</p>
                </div>

                <div class="role-toggle">
                    <div class="active">User / Student</div>
                    <div class="inactive" onclick="window.location.href='adminlogin.php'">Staff / Admin</div>
                </div>

                <form role="form" method="post">
                    
                    <div class="input-group-custom">
                        <i class="fa fa-user left-icon"></i>
                        <input type="text" name="emailid" placeholder="Username" required autocomplete="off" />
                    </div>
                    
                    <div class="input-group-custom">
                        <i class="fa fa-unlock-alt left-icon"></i>
                        <input type="password" name="password" id="password-field" placeholder="Password" required autocomplete="off" />
                        <i class="fa fa-eye right-icon" onclick="togglePassword()"></i>
                    </div>

                    <button type="submit" name="login" class="btn-login">Login</button>
                    <a href="signup.php" class="btn-signup">Sign Up</a>
                    <a href="user-forgot-password.php" class="forgot-password">Forgot Password</a>

                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script>
        // Script to toggle password visibility (eye icon)
        function togglePassword() {
            var passField = document.getElementById("password-field");
            if (passField.type === "password") {
                passField.type = "text";
            } else {
                passField.type = "password";
            }
        }
    </script>
</body>
</html>