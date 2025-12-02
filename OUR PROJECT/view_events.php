<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "volunteer"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$available_events = [];
$error_message = "";

$sql_events = "SELECT EventId, Title, Description, Date, Location FROM events WHERE Date >= CURDATE() ORDER BY Date ASC";
$result_events = mysqli_query($link, $sql_events);

if ($result_events) {
    while($row = mysqli_fetch_assoc($result_events)) {
        $row['is_signed_up'] = false;
        $available_events[] = $row;
    }
    mysqli_free_result($result_events);
} else {
    $error_message = "ERROR: Could not fetch events. " . mysqli_error($link);
}

// Check the signup status for each event
if (!empty($available_events)) {
    $event_ids = array_column($available_events, 'EventId');
    $placeholders = implode(',', array_fill(0, count($event_ids), '?'));
    $sql_check = "SELECT EventId FROM volunteer_signups WHERE UserId = ? AND EventId IN ($placeholders)";

    if ($stmt_check = mysqli_prepare($link, $sql_check)) {
        $types = str_repeat('i', count($event_ids) + 1);
        $params = array_merge([$types, $user_id], $event_ids);
        $refs = [];
        foreach($params as $key => $value) { $refs[$key] = &$params[$key]; }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt_check], $refs));

        if (mysqli_stmt_execute($stmt_check)) {
            $result_check = mysqli_stmt_get_result($stmt_check);
            $signed_up_ids = [];
            while ($row_check = mysqli_fetch_assoc($result_check)) {
                $signed_up_ids[$row_check['EventId']] = true;
            }
            foreach ($available_events as $key => $event) {
                if (isset($signed_up_ids[$event['EventId']])) {
                    $available_events[$key]['is_signed_up'] = true;
                }
            }
        }
        mysqli_stmt_close($stmt_check);
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upcoming Events</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body class="page-content">
<?php include('header.php'); ?>
<div class="content-shell">
    <div class="page-header">
        <div>
            <div class="pill">Volunteer - Events</div>
            <h2 style="margin:6px 0; color:#0f172a;">Upcoming Volunteer Opportunities</h2>
        </div>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="card" style="margin-bottom: 16px; color: #b91c1c;"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (count($available_events) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Event</th>
                <th>Date</th>
                <th>Location</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($available_events as $event): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($event['Title']); ?></strong></td>
                <td><?php echo date("M d, Y", strtotime($event['Date'])); ?></td>
                <td><?php echo htmlspecialchars($event['Location']); ?></td>
                <td>
                    <?php if ($event['is_signed_up']): ?>
                        <span class="badge green">Signed Up</span>
                    <?php else: ?>
                        <a href="signup_action.php?event_id=<?php echo $event['EventId']; ?>" style="color:#2563eb; text-decoration:none; font-weight:600;">Sign Up Now</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No upcoming volunteer opportunities available right now.</p>
    <?php endif; ?>
</div>
</body>
</html>
