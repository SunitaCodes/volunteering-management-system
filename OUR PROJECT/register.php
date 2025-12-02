<?php
require_once "config.php";

$username = $email = $password = $username_err = $email_err = $password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate UserName
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else {
        $sql = "SELECT UserId FROM users WHERE UserName = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate Email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate Password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Insert user into database if no errors
    if(empty($username_err) && empty($email_err) && empty($password_err)){
        $sql = "INSERT INTO users (UserName, Email, Password, Role) VALUES (?, ?, ?, 'volunteer')";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_email, $param_password);

            $param_username = $username;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); 

            if(mysqli_stmt_execute($stmt)){
                header("location: login.php");
            } else{
                echo "ERROR: Registration failed. Please try again later.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMS Register</title>
    <link rel="stylesheet" href="login_style.css">
</head>
<body>
    <div class="wrapper">
        <a href="index.php">Home</a>
        <h2>Create Your Volunteer Account</h2>

        <?php if(!empty($username_err) || !empty($email_err) || !empty($password_err)): ?>
            <div class="alert">Please fix the highlighted fields.</div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input
                    type="text"
                    name="username"
                    class="<?php echo !empty($username_err) ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($username); ?>"
                    required
                >
                <?php if(!empty($username_err)): ?><div class="error-text"><?php echo $username_err; ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input
                    type="email"
                    name="email"
                    class="<?php echo !empty($email_err) ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($email); ?>"
                    required
                >
                <?php if(!empty($email_err)): ?><div class="error-text"><?php echo $email_err; ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    class="<?php echo !empty($password_err) ? 'input-error' : ''; ?>"
                    required
                >
                <?php if(!empty($password_err)): ?><div class="error-text"><?php echo $password_err; ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <input type="submit" value="Register">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>
</body>
</html>
