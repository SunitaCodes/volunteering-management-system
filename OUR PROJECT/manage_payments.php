<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: login.php");
    exit;
}

$payments = [];
$error_message = "";

$sql = "SELECT o.OrderId, o.TransactionCode, o.Amount, o.Status, o.UpdatedDate,
               u.UserName AS VolunteerName,
               e.Title AS EventName,
               vs.SignupDate
        FROM orders o
        inner JOIN users u ON o.UserId = u.UserId
        inner JOIN events e ON o.EventId = e.EventId
        inner JOIN volunteer_signups vs ON o.SignupId = vs.SignupId
        ORDER BY o.UpdatedDate DESC";

if ($result = mysqli_query($link, $sql)) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $payments[] = $row;
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
    <title>Manage Payments</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body class="page-content">
    <?php include('header.php'); ?>
    <div class="content-shell">
        <div class="page-header">
            <div>
                <div class="pill">Admin - Payments</div>
                <h2 style="margin: 6px 0; color: #0f172a;">Manage Payments</h2>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="card" style="margin-bottom: 16px; color: #b91c1c;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <h3 style="margin: 14px 0; color: #0f172a;">Total Orders: <?php echo count($payments); ?></h3>

        <?php if (count($payments) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Volunteer Name</th>
                        <th>Event Name</th>
                        <th>Sign Up Date</th>
                        <th>Transaction Code</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['VolunteerName'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($payment['EventName'] ?? 'N/A'); ?></td>
                            <td>
                                <?php
                                echo $payment['SignupDate']
                                    ? date("Y-m-d", strtotime($payment['SignupDate']))
                                    : 'N/A';
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($payment['TransactionCode'] ?? 'N/A'); ?></td>
                            <td><?php echo number_format((float) $payment['Amount'], 2); ?></td>
                            <td>
                                <?php
                                echo $payment['UpdatedDate']
                                    ? date("Y-m-d", strtotime($payment['UpdatedDate']))
                                    : 'N/A';
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($payment['Status'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>There are currently no payments recorded in the system.</p>
        <?php endif; ?>
    </div>
</body>
</html>
