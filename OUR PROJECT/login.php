<?php
session_start();
// Include the database configuration
require_once "config.php";

// Redirect if already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if ($_SESSION["role"] === "admin") {
        header("location: admin_dashboard.php");
    } else {
        header("location: index.php");
    }
    exit;
}

$username = $password = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Simple input validation
    if(empty(trim($_POST["username"])) || empty(trim($_POST["password"]))){
        $login_err = "Please enter username and password.";
    } else{
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);
    }

    if(empty($login_err)){
        $sql = "SELECT UserId, UserName, Password, Role FROM users WHERE UserName = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id; 
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;

                            if ($role === "admin") {
                                header("location: admin_dashboard.php");
                            } else {
                                header("location: index.php");
                            }
                        } else{
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    $login_err = "Invalid username or password.";
                }
            } 
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VMS Login</title>
    <link rel="stylesheet" href="login_style.css"> 
</head>
<body>
    <div class="wrapper">    
        <a href="index.php">Home</a>    
        <h2>Volunteer System Login</h2>
        
        <?php if(!empty($login_err)): ?>
            <div class="alert"><?php echo $login_err; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Login">
            </div>
        </form>
        <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
    </div>
</body>
</html>