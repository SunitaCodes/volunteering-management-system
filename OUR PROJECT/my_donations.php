<?php
session_start();
require_once "config.php";

// Check if user is logged in and is a volunteer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "volunteer"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$donations = [];
$error_message = "";

// Fetch donation history for this user
$sql = "SELECT d.Amount, d.DonationDate, c.Name AS CharityName, c.Description AS CharityDescription
        FROM donations d
        JOIN charities c ON d.CharityId = c.CharityId
        WHERE d.UserId = ?
        ORDER BY d.DonationDate DESC";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $donations[] = $row;
        }
        mysqli_free_result($result);
    } else {
        $error_message = "ERROR: Could not retrieve donation history. " . mysqli_error($link);
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Donations</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body class="page-content">
<?php include('header.php'); ?>
<div class="content-shell">
    <div class="page-header">
        <div>
            <div class="pill">Volunteer - Donations</div>
            <h2 style="margin:6px 0; color:#0f172a;">My Donation History</h2>
        </div>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="card" style="margin-bottom: 12px; color:#b91c1c;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <h3 style="margin: 10px 0; color:#0f172a;">Total Donations Recorded: <?php echo count($donations); ?></h3>

    <?php if (count($donations) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Charity</th>
                    <th>Amount (NPR)</th>
                    <th>Date</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($donations as $donation): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($donation['CharityName']); ?></strong></td>
                        <td><?php echo number_format($donation['Amount'], 2) . " NPR"; ?></td>
                        <td><?php echo date("F j, Y", strtotime($donation['DonationDate'])); ?></td>
                        <td><?php echo htmlspecialchars($donation['CharityDescription']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have not recorded any donations in the system yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
