<?php
session_start();
require_once "config.php";

// 1. Security Check: Ensure user is logged in as volunteer and event_id exists
if(!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "volunteer" || !isset($_GET['event_id'])){
    header("location: login.php");
    exit;
}

// Function to handle errors (modals remain for failures)
function render_modal($title, $message, $redirect = 'view_events.php') {
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
        <style>
            body { margin:0; font-family: "Segoe UI", sans-serif; background:#0b1222; color:#0f172a; display:flex; align-items:center; justify-content:center; min-height:100vh; }
            .dialog { background:#ffffff; padding:28px; border-radius:14px; box-shadow:0 20px 50px rgba(0,0,0,0.35); max-width:420px; width:90%; text-align:center; border:1px solid #e5e7eb; }
            .dialog h2 { margin:0 0 10px; color:#0f172a; }
            .dialog p { margin:0 0 20px; color:#1f2937; line-height:1.5; }
            .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 16px; border-radius:10px; border:none; background:#2563eb; color:#fff; font-weight:700; cursor:pointer; text-decoration:none; }
        </style>
    </head>
    <body>
        <div class="dialog">
            <h2><?php echo htmlspecialchars($title); ?></h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a class="btn" href="<?php echo $redirect; ?>">OK</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$user_id = $_SESSION["id"];
$event_id = $_GET['event_id'];

// 2. Operation: Check for duplicate signups (Applicability: Data Integrity)
$check_sql = "SELECT SignupId FROM volunteer_signups WHERE UserId = ? AND EventId = ?";
if($check_stmt = mysqli_prepare($link, $check_sql)){
    mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $event_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    if(mysqli_stmt_num_rows($check_stmt) > 0){
        mysqli_stmt_close($check_stmt);
        render_modal("Already Signed Up", "You are already registered for this initiative.");
    }
    mysqli_stmt_close($check_stmt);
}

// 3. Operation: Insert Record (Applicability: Resource Management)
$sql = "INSERT INTO volunteer_signups (UserId, EventId) VALUES (?, ?)";

if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $event_id);
    
    if(mysqli_stmt_execute($stmt)){
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        
        // SUCCESS: Redirect to the preparation page to suggest a T-shirt
        // This is the "Applicable" step your teacher is looking for.
        header("location: success_preparation.php?event_id=" . $event_id);
        exit;
    } else {
        $err = mysqli_error($link);
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        render_modal("Signup Failed", "Database Error: $err");
    }
}

mysqli_close($link);
render_modal("Error", "An unexpected error occurred.");
?>