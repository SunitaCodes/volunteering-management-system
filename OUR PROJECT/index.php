<?php
session_start();
require_once "config.php";

$total_volunteers = 0;
$total_donations_amount = 0.00;
$featured_events = [];
$error = "";

$is_volunteer_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION["role"] === "volunteer";
$user_id = $is_volunteer_logged_in ? $_SESSION["id"] : null;

$sql_volunteers = "SELECT COUNT(UserId) AS count FROM users WHERE Role = 'volunteer'";
if ($result = mysqli_query($link, $sql_volunteers)) {
    $row = mysqli_fetch_assoc($result);
    $total_volunteers = $row['count'];
    mysqli_free_result($result);
} else {
    $error .= "Error fetching volunteer count. ";
}

$sql_donations = "SELECT SUM(Amount) AS total FROM donations";
if ($result = mysqli_query($link, $sql_donations)) {
    $row = mysqli_fetch_assoc($result);
    $total_donations_amount = $row['total'] ?? 0.00;
    mysqli_free_result($result);
} else {
    $error .= "Error fetching donation total. ";
}

$my_total_signups = 0;
$my_total_donations = 0.00;

if ($is_volunteer_logged_in) {
    $sql_my_signups = "SELECT COUNT(SignupId) AS count FROM volunteer_signups WHERE UserId = ?";
    if ($stmt = mysqli_prepare($link, $sql_my_signups)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $my_total_signups = $row['count'];
            mysqli_free_result($result);
        }
        mysqli_stmt_close($stmt);
    }
    
    $sql_my_donations = "SELECT SUM(Amount) AS total FROM donations WHERE UserId = ?";
    if ($stmt = mysqli_prepare($link, $sql_my_donations)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $my_total_donations = $row['total'] ?? 0.00;
            mysqli_free_result($result);
        }
        mysqli_stmt_close($stmt);
    }
}

$sql_featured = "SELECT EventId, Title, Description, Date, Location 
                 FROM events 
                 WHERE IsFeatured = 1 AND Date >= CURDATE()
                 ORDER BY Date ASC LIMIT 3";

if ($result = mysqli_query($link, $sql_featured)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featured_events[] = $row;
    }
    mysqli_free_result($result);
} else {
    $error .= "Error fetching featured events. ";
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home | Volunteer Management System</title>
    <link rel="stylesheet" href="style.css?v=2"> 
</head>
<body class="page-content">
    <?php include('header.php'); ?>
    <div class="content-shell">
        <?php if (!empty($error)): ?>
            <div class="card" style="margin-bottom: 12px; color:#b91c1c;"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <div class="pill">Volunteer Portal</div>
                <h1 style="margin: 6px 0; color: #0f172a;">Welcome to Our Community VMS</h1>
                <p style="margin:0; color:#475569;">Find events, track your signups, and support charities.</p>
            </div>
        </div>

        <?php if ($is_volunteer_logged_in): ?>
        <div class="cards-grid" style="margin-bottom: 16px;">
            <div class="card">
                <h3 style="margin:0 0 6px;">My Total Sign-ups</h3>
                <div class="number" style="font-size: 2.2em; color:#0f172a;"><?php echo number_format($my_total_signups); ?></div>
                <p style="margin:6px 0 0; color:#475569;">Events you've committed to.</p>
            </div>
            <div class="card">
                <h3 style="margin:0 0 6px;">My Total Donations</h3>
                <div class="number" style="font-size: 2.2em; color:#0f172a;"><?php echo number_format($my_total_donations, 2); ?> NPR</div>
                <p style="margin:6px 0 0; color:#475569;">Your recorded contributions.</p>
            </div>
        </div>
        <?php endif; ?>

        <h2 class="featured-header">Featured Volunteer Opportunities</h2>
        <div class="featured-grid">
            <?php if (!empty($featured_events)): ?>
                <?php foreach ($featured_events as $event): ?>
                    <div class="event-card">
                        <div class="event-card-content">
                            <h4><?php echo htmlspecialchars($event['Title']); ?></h4>
                            <p class="date-location">
                                <?php echo date("F j, Y", strtotime($event['Date'])); ?> | 
                                <?php echo htmlspecialchars($event['Location']); ?>
                            </p>
                            <p><?php echo htmlspecialchars(substr($event['Description'], 0, 150)) . (strlen($event['Description']) > 150 ? '...' : ''); ?></p>
                            
                            <?php if ($is_volunteer_logged_in): ?>
                                <a href="signup_action.php?event_id=<?php echo $event['EventId']; ?>" class="signup-link">Sign Up to Help!</a>
                            <?php else: ?>
                                <a href="login.php" class="signup-link" style="background-color: #ccc;">Login to Sign Up</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%;">No featured upcoming events available at this time.</p>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; padding: 24px 0 30px;">
            <a href="view_events.php" style="color:#2563eb; font-weight:600; text-decoration:none;">View All Opportunities &rarr;</a>
        </div>
    </div>
</body>
</html>
