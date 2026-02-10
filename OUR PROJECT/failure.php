<?php
$type = isset($_GET['type']) ? $_GET['type'] : '';
$txn = isset($_GET['txn']) ? $_GET['txn'] : '';
$label = $type === 'signup' ? 'signup' : 'payment';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Failed</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body class="page-content">
    <?php include('header.php'); ?>
    <div class="content-shell">
        <div class="page-header">
            <div>
                <div class="pill">Payment Status</div>
                <h2 style="margin: 6px 0; color: #b91c1c;">Esewa payment failed or canceled</h2>
            </div>
        </div>

        <div class="card" style="padding: 18px;">
            <p>Your <?php echo htmlspecialchars($label); ?> payment was not completed.</p>
            <?php if (!empty($txn)): ?>
                <p><strong>Transaction:</strong> <?php echo htmlspecialchars($txn); ?></p>
            <?php endif; ?>
            <a class="btn btn-primary" href="my_signups.php">Back to My Signups</a>
        </div>
    </div>
</body>
</html>
