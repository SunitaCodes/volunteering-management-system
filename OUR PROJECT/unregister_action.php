<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "volunteer" || !isset($_GET['event_id'])){
    header("location: login.php");
    exit;
}

function render_modal($title, $message, $redirect = 'my_signups.php') {
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
        <style>
            body { margin:0; font-family: "Segoe UI", system-ui, sans-serif; background:#0b1222; color:#0f172a; display:flex; align-items:center; justify-content:center; min-height:100vh; }
            .dialog { background:#ffffff; padding:28px; border-radius:14px; box-shadow:0 20px 50px rgba(0,0,0,0.35); max-width:420px; width:90%; text-align:center; border:1px solid #e5e7eb; }
            .dialog h2 { margin:0 0 10px; color:#0f172a; }
            .dialog p { margin:0 0 20px; color:#1f2937; line-height:1.5; }
            .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 16px; border-radius:10px; border:none; background:#2563eb; color:#fff; font-weight:700; cursor:pointer; transition:transform .1s ease, box-shadow .1s ease; }
            .btn:hover { transform:translateY(-1px); box-shadow:0 10px 25px rgba(37,99,235,0.25); }
        </style>
    </head>
    <body>
        <div class="dialog">
            <h2><?php echo htmlspecialchars($title); ?></h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            <button class="btn" onclick="window.location.href='<?php echo $redirect; ?>'">OK</button>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$user_id = $_SESSION["id"];
$event_id = $_GET['event_id'];

// Check if the event is upcoming (uses Date)
$check_date_sql = "SELECT Date FROM events WHERE EventId = ?";
$event_date = null;

if($stmt_date = mysqli_prepare($link, $check_date_sql)){
    mysqli_stmt_bind_param($stmt_date, "i", $event_id);
    mysqli_stmt_execute($stmt_date);
    mysqli_stmt_bind_result($stmt_date, $event_date);
    mysqli_stmt_fetch($stmt_date);
    mysqli_stmt_close($stmt_date);
}

if ($event_date && $event_date < date('Y-m-d')) {
    mysqli_close($link);
    render_modal("Unregister Failed", "Cannot unregister: This event has already occurred.");
}

// Delete the signup record (uses UserId and EventId)
$sql_delete = "DELETE FROM volunteer_signups WHERE UserId = ? AND EventId = ?";

if($stmt_delete = mysqli_prepare($link, $sql_delete)){
    mysqli_stmt_bind_param($stmt_delete, "ii", $user_id, $event_id);
    
    if(mysqli_stmt_execute($stmt_delete)){
        mysqli_stmt_close($stmt_delete);
        mysqli_close($link);
        render_modal("Unregistered", "Successfully unregistered from the event.");
    } else{
        $err = mysqli_error($link);
        mysqli_stmt_close($stmt_delete);
        mysqli_close($link);
        render_modal("Unregister Failed", "ERROR: Could not complete unregister action. $err");
    }
}

mysqli_close($link);
render_modal("Unregister Failed", "Unexpected error occurred. Please try again.");
?>
