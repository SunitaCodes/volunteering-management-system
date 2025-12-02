<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: login.php");
    exit;
}

$volunteers = [];
$error_message = "";

$sql = "SELECT UserId, UserName, Email, CreatedDate FROM users WHERE Role = 'volunteer' ORDER BY CreatedDate DESC";

if ($result = mysqli_query($link, $sql)) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $volunteers[] = $row;
        }
        mysqli_free_result($result);
    }
} else {
    $error_message = "ERROR: Could not execute query. " . mysqli_error($link);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Volunteers</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body class="page-content">
    <?php include('header.php'); ?>
    <div class="content-shell">
        <div class="page-header">
            <div>
                <div class="pill">Admin - Users</div>
                <h2 style="margin: 6px 0; color: #0f172a;">Volunteer Roster Management</h2>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="card" style="margin-bottom: 16px; color: #b91c1c;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <h3 style="margin: 14px 0; color: #0f172a;">Total Registered Volunteers: <?php echo count($volunteers); ?></h3>

        <?php if (count($volunteers) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Date Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($volunteers as $volunteer): ?>
                        <tr>
                            <td><?php echo $volunteer['UserId']; ?></td>
                            <td><?php echo htmlspecialchars($volunteer['UserName']); ?></td>
                            <td><?php echo htmlspecialchars($volunteer['Email']); ?></td>
                            <td><?php echo date("Y-m-d", strtotime($volunteer['CreatedDate'])); ?></td>
                            <td class="action-links">
                                <a href="view_volunteer_hours.php?id=<?php echo $volunteer['UserId']; ?>">View Hours</a> |
                                <a href="delete_volunteer.php?id=<?php echo $volunteer['UserId']; ?>" onclick="return confirm('WARNING: Are you sure you want to delete this volunteer? This cannot be undone.');" style="color:#ef4444;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>There are currently no volunteers registered in the system.</p>
        <?php endif; ?>
    </div>
</body>
</html>
