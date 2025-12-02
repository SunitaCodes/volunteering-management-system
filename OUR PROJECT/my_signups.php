<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "volunteer"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$signed_up_events = [];
$error_message = "";
$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$sql = "SELECT e.EventId, e.Title, e.Description, e.Date, e.Location, s.SignupDate
        FROM volunteer_signups s
        JOIN events e ON s.EventId = e.EventId
        WHERE s.UserId = ?
        ORDER BY e.Date ASC";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $signed_up_events[] = $row;
        }
        mysqli_free_result($result);
    } else {
        $error_message = "ERROR: Could not retrieve signed-up events. " . mysqli_error($link);
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Signups</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body class="page-content">
<?php include('header.php'); ?>
<div class="content-shell">
    <div class="page-header">
        <div>
            <div class="pill">Volunteer - My Signups</div>
            <h2 style="margin:6px 0; color:#0f172a;">My Signed-Up Volunteer Events</h2>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="card" style="margin-bottom: 12px; color:#1d4ed8; font-weight:600;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="card" style="margin-bottom: 12px; color:#b91c1c;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <h3 style="margin: 10px 0; color:#0f172a;">You are signed up for <?php echo count($signed_up_events); ?> event(s).</h3>

    <?php if (count($signed_up_events) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Event Title</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Signup Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($signed_up_events as $event):
                    $is_upcoming = $event['Date'] >= date('Y-m-d');
                    $row_style = $is_upcoming ? '' : 'style="color: #6b7280;"';
                ?>
                    <tr <?php echo $row_style; ?>>
                        <td><strong><?php echo htmlspecialchars($event['Title']); ?></strong></td>
                        <td><?php echo date("F j, Y", strtotime($event['Date'])); ?></td>
                        <td><?php echo htmlspecialchars($event['Location']); ?></td>
                        <td><?php echo date("M d, g:i A", strtotime($event['SignupDate'])); ?></td>
                        <td class="action-links">
                            <?php if ($is_upcoming): ?>
                                <a href="unregister_action.php?event_id=<?php echo $event['EventId']; ?>" style="color:#ef4444; font-weight:600;" onclick="return confirm('Cancel your registration for this event?');">Unregister</a>
                            <?php else: ?>
                                Event Completed
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You haven't signed up for any upcoming events yet. Check the available opportunities!</p>
    <?php endif; ?>
</div>
</body>
</html>
