<?php
session_start();
include('includes/config.php');
error_reporting(0);
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

$msg=""; $error="";
if(isset($_POST['change'])){
  $password=md5($_POST['password']);
  $newpassword=md5($_POST['newpassword']);
  $username=$_SESSION['alogin'];
  $sql="SELECT Password FROM admin WHERE UserName=:username AND Password=:password";
  $query=$dbh->prepare($sql);
  $query->bindParam(':username',$username,PDO::PARAM_STR);
  $query->bindParam(':password',$password,PDO::PARAM_STR);
  $query->execute();
  if($query->rowCount()>0){
    $con="UPDATE admin SET Password=:newpassword WHERE UserName=:username";
    $chng=$dbh->prepare($con);
    $chng->bindParam(':username',$username,PDO::PARAM_STR);
    $chng->bindParam(':newpassword',$newpassword,PDO::PARAM_STR);
    $chng->execute();
    $msg="Password changed successfully";
  } else {
    $error="Your current password is incorrect";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Change Password</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
  <script>
  function valid(){
    if(document.chngpwd.newpassword.value != document.chngpwd.confirmpassword.value){
      alert("New Password and Confirm Password do not match!");
      document.chngpwd.confirmpassword.focus();
      return false;
    }
    return true;
  }
  </script>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Change Password</h1>
      <p class="page-subtitle">Update your administrator password</p>
    </div>
  </div>

  <?php if($error): ?>
    <div class="alert alert-danger"><?php echo htmlentities($error); ?></div>
  <?php elseif($msg): ?>
    <div class="alert alert-success"><?php echo htmlentities($msg); ?></div>
  <?php endif; ?>

  <div class="card card-narrow">
    <div class="card-header">
      <span class="card-title">Update Credentials</span>
    </div>
    <div class="card-body">
      <form method="post" name="chngpwd" onsubmit="return valid();">
        <div class="form-group">
          <label>Current Password <span class="req">*</span></label>
          <input type="password" name="password" autocomplete="off" required/>
        </div>
        <div class="form-group mt-12">
          <label>New Password <span class="req">*</span></label>
          <input type="password" name="newpassword" autocomplete="off" required/>
        </div>
        <div class="form-group mt-12">
          <label>Confirm New Password <span class="req">*</span></label>
          <input type="password" name="confirmpassword" autocomplete="off" required/>
        </div>
        <div class="form-actions">
          <button type="submit" name="change" class="btn btn-gold">Change Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>
</body>
</html>
<?php } ?>
