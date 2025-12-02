<?php
session_start();
// Security Check: Must be logged in AND have the 'admin' role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body class="page-content">
<?php include 'header.php'; ?>
    <div class="content-shell">
        <div class="page-header">
            <div>
                <div class="pill">Admin - Dashboard</div>
                <h2 style="margin: 6px 0; color: #0f172a;">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
                <p style="margin: 4px 0 0; color: #334155;">Quick admin links</p>
            </div>
        </div>

        <div class="cards-grid">
            <a href="manage_events.php" class="card" style="text-decoration:none;">
                <h3 style="margin-bottom: 6px;">Manage Events</h3>
                <p style="margin: 0; color: #475569;">Create, edit, or remove upcoming opportunities.</p>
            </a>
            <a href="manage_charities.php" class="card" style="text-decoration:none;">
                <h3 style="margin-bottom: 6px;">Manage Charities</h3>
                <p style="margin: 0; color: #475569;">Update partner profiles and details.</p>
            </a>
            <a href="manage_volunteers.php" class="card" style="text-decoration:none;">
                <h3 style="margin-bottom: 6px;">View Volunteers</h3>
                <p style="margin: 0; color: #475569;">Review volunteer roster and activity.</p>
            </a>
            <a href="logout.php" class="card" style="text-decoration:none;">
                <h3 style="margin-bottom: 6px; color: #b91c1c;">Logout</h3>
                <p style="margin: 0; color: #b91c1c;">Sign out of the admin portal.</p>
            </a>
        </div>
    </div>
</body>
</html>
